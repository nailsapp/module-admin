<?php

/**
 * This class provides a number of reuseable static methods which Admin Controllers can make use of
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Helper
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

use Nails\Auth;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ViewNotFoundException;
use Nails\Common\Service\Input;
use Nails\Factory;

class Helper
{
    protected static $aHeaderButtons = [];

    // --------------------------------------------------------------------------

    /**
     * Loads a view in admin taking into account the module being accessed. Passes controller
     * data and optionally loads the header and footer views.
     *
     * @param string  $sViewFile      The view to load
     * @param boolean $bLoadStructure Whether or not to include the header and footers in the output
     * @param boolean $bReturnView    Whether to return the view or send it to the Output class
     *
     * @return mixed                  String when $bReturnView is true, void otherwise
     * @throws FactoryException
     */
    public static function loadView($sViewFile, $bLoadStructure = true, $bReturnView = false)
    {
        $aData  =& getControllerData();
        $oInput = Factory::service('Input');
        $oView  = Factory::service('View');

        //  Are we in a modal?
        if ($oInput->get('isModal')) {

            if (!isset($aData['headerOverride']) && !isset($aData['isModal'])) {
                $aData['isModal'] = true;
            }

            if (empty($aData['headerOverride'])) {
                $aData['headerOverride'] = '_components/structure/headerBlank';
            }

            if (empty($aData['footerOverride'])) {
                $aData['footerOverride'] = '_components/structure/footerBlank';
            }
        }

        $aHeaderViews = array_filter([
            $bLoadStructure && !empty($aData['headerOverride']) ? $aData['headerOverride'] : '',
            $bLoadStructure && empty($aData['headerOverride']) ? '_components/structure/header' : '',
        ]);
        $sHeaderView  = reset($aHeaderViews);

        $aFooterViews = array_filter([
            $bLoadStructure && !empty($aData['footerOverride']) ? $aData['footerOverride'] : '',
            $bLoadStructure && empty($aData['footerOverride']) ? '_components/structure/footer' : '',
        ]);
        $sFooterView  = reset($aFooterViews);

        if ($bReturnView) {
            $sOut = $oView->load($sHeaderView, $aData, true);
            $sOut .= static::loadInlineView($sViewFile, $aData, true);
            $sOut .= $oView->load($sFooterView, $aData, true);
            return $sOut;
        } else {
            $oView->load($sHeaderView, $aData);
            static::loadInlineView($sViewFile, $aData);
            $oView->load($sFooterView, $aData);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a CSV and sends to the browser, if a filename is given then it's
     * sent as a download
     *
     * @param mixed   $mData      The data to render, either an array or a DB query object
     * @param string  $sFilename  The filename to give the file if downloading
     * @param boolean $bHeaderRow The first element in the $mData resultset is a header row
     *
     * @return void
     * @throws FactoryException
     */
    public static function loadCsv($mData, $sFilename = '', $bHeaderRow = true)
    {
        //  Determine what type of data has been supplied
        if (is_array($mData) || get_class($mData) == 'CI_DB_mysqli_result') {

            //  If filename has been specified then set some additional headers
            if (!empty($sFilename)) {

                $oInput  = Factory::service('Input');
                $oOutput = Factory::service('Output');

                //  Common headers
                $oOutput->set_content_type('text/csv');
                $oOutput->set_header('Content-Disposition: attachment; filename="' . $sFilename . '"');
                $oOutput->set_header('Expires: 0');
                $oOutput->set_header("Content-Transfer-Encoding: binary");

                //  Handle IE, classic.
                $userAgent = $oInput->server('HTTP_USER_AGENT');

                if (strpos($userAgent, "MSIE") !== false) {

                    $oOutput->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    $oOutput->set_header('Pragma: public');

                } else {

                    $oOutput->set_header('Pragma: no-cache');
                }
            }

            //  Not using self::loadInlineView() as this may be called from many contexts
            $oView = Factory::service('View');
            if (is_array($mData)) {
                $oView->load('admin/_components/csv/array', ['data' => $mData, 'header' => $bHeaderRow]);
            } elseif (get_class($mData) == 'CI_DB_mysqli_result') {
                $oView->load('admin/_components/csv/dbResult', ['data' => $mData, 'header' => $bHeaderRow]);
            }

        } else {
            throw new NailsException('Unsupported object type passed to ' . get_class() . '::loadCSV');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load a single view taking into account the module being accessed.
     *
     * @param string  $sViewFile   The view to load
     * @param array   $aViewData   The data to pass to the view
     * @param boolean $bReturnView Whether to return the view or send it to the Output class
     *
     * @return mixed               String when $bReturnView is true, void otherwise
     * @throws \Exception
     *
     */
    public static function loadInlineView($sViewFile, $aViewData = [], $bReturnView = false)
    {
        $aCtrlData   =& getControllerData();
        $sCtrlPath   = !empty($aCtrlData['currentRequest']['path']) ? $aCtrlData['currentRequest']['path'] : '';
        $sCtrlName   = basename($sCtrlPath, '.php');
        $aCtrlPath   = explode(DIRECTORY_SEPARATOR, $sCtrlPath);
        $aCtrlPath   = array_splice($aCtrlPath, 0, count($aCtrlPath) - 2);
        $aCtrlPath[] = 'views';
        $aCtrlPath[] = $sCtrlName;
        $aCtrlPath[] = $sViewFile;
        $sViewPath   = implode(DIRECTORY_SEPARATOR, $aCtrlPath) . '.php';

        $oView = Factory::service('View');

        //  Load the view
        try {
            return $oView->load($sViewPath, $aViewData, $bReturnView);
        } catch (ViewNotFoundException $e) {
            //  If it fails, and the controller is a default admin controller then load up that view
            $sClassName = $aCtrlData['currentRequest']['className'];
            if (!classExtends($sClassName, 'Nails\\Admin\\Controller\\DefaultController')) {
                throw new ViewNotFoundException(
                    $e->getMessage(),
                    $e->getCode()
                );
            }

            //  Step through the class hierarchy and look there
            $aParents = class_parents($sClassName);
            foreach ($aParents as $sParent) {
                try {

                    if ($sParent !== 'Nails\\Admin\\Controller\\DefaultController') {

                        $oReflection = new \ReflectionClass('\\' . $sParent);
                        $sViewPath   = realpath(dirname($oReflection->getFileName()) . '/../views') . '/';
                        $aClassBits  = explode('\\', $oReflection->getName());
                        $sViewPath   .= end($aClassBits) . '/';

                    } else {
                        $sViewPath     = 'admin/DefaultController/';
                        $bTriedDefault = true;
                    };

                    return $oView->load(
                        $sViewPath . $sViewFile,
                        $aViewData,
                        $bReturnView
                    );
                } catch (ViewNotFoundException $e) {
                    //  Allow the loop to continue, unless we've already tried the default views
                    if (!empty($bTriedDefault)) {
                        throw $e;
                    }
                }
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "search" component
     *
     * @param \stdClass $oSearchObj  An object as created by self::searchObject();
     * @param boolean   $bReturnView Whether to return the view to the caller, or output to the browser
     *
     * @return mixed                  String when $bReturnView is true, void otherwise
     * @throws FactoryException
     */
    public static function loadSearch($oSearchObj, $bReturnView = true)
    {
        $aData = [
            'searchable'     => isset($oSearchObj->searchable) ? $oSearchObj->searchable : true,
            'sortColumns'    => isset($oSearchObj->sortColumns) ? $oSearchObj->sortColumns : [],
            'sortOn'         => isset($oSearchObj->sortOn) ? $oSearchObj->sortOn : null,
            'sortOrder'      => isset($oSearchObj->sortOrder) ? $oSearchObj->sortOrder : null,
            'perPage'        => isset($oSearchObj->perPage) ? $oSearchObj->perPage : 50,
            'keywords'       => isset($oSearchObj->keywords) ? $oSearchObj->keywords : '',
            'checkboxFilter' => isset($oSearchObj->checkboxFilter) ? $oSearchObj->checkboxFilter : [],
            'dropdownFilter' => isset($oSearchObj->dropdownFilter) ? $oSearchObj->dropdownFilter : [],
        ];

        //  Not using self::loadInlineView() as this may be called from many contexts
        $oView = Factory::service('View');
        return $oView->load('admin/_components/search', $aData, $bReturnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadSearch()
     *
     * @param boolean $bSearchable     Whether the result set is keyword searchable
     * @param array   $aSortColumns    An array of columns to sort results by
     * @param string  $sSortOn         The column to sort on
     * @param string  $sSortOrder      The order to sort results in
     * @param integer $iPerPage        The number of results to show per page
     * @param string  $sKeywords       Keywords to apply to the search result
     * @param array   $aCheckboxFilter An array of filters to filter the results by, presented as checkboxes
     * @param array   $aDropdownFilter An array of filters to filter the results by, presented as a dropdown
     *
     * @return \stdClass
     */
    public static function searchObject(
        $bSearchable,
        $aSortColumns,
        $sSortOn,
        $sSortOrder,
        $iPerPage,
        $sKeywords = '',
        $aCheckboxFilter = [],
        $aDropdownFilter = []
    ) {
        return (object) [
            'searchable'     => $bSearchable,
            'sortColumns'    => $aSortColumns,
            'sortOn'         => $sSortOn,
            'sortOrder'      => $sSortOrder,
            'perPage'        => $iPerPage,
            'keywords'       => $sKeywords,
            'checkboxFilter' => $aCheckboxFilter,
            'dropdownFilter' => $aDropdownFilter,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::searchObject()'s
     * $checkboxFilter and $dropdownFilter parameters
     *
     * @param string $sColumn   The name of the column to filter on, leave blank if you do not wish to use Nails's
     *                          automatic filtering
     * @param string $sLabel    The label to give the filter group
     * @param array  $aOptions  An array of options for the dropdown, either key => value pairs or a 3 element array: 0
     *                          = label, 1 = value, 2 = default check status
     *
     * @return \stdClass
     * @throws FactoryException
     */
    public static function searchFilterObject($sColumn, $sLabel, $aOptions)
    {
        //  @todo (Pablo - 2018-04-10) - DonRemove this helper and use factories directly
        $oFilter = Factory::factory('IndexFilter', 'nails/module-admin')
            ->setLabel($sLabel)
            ->setColumn($sColumn);

        foreach ($aOptions as $sIndex => $mOption) {

            if (is_array($mOption)) {
                $sLabel   = getFromArray(0, $mOption, null);
                $mValue   = getFromArray(1, $mOption, null);
                $bChecked = getFromArray(2, $mOption, false);
                $bQuery   = getFromArray(3, $mOption, false);
            } else {
                $sLabel   = $mOption;
                $mValue   = $sIndex;
                $bChecked = false;
                $bQuery   = false;
            }

            $oFilter->addOption($sLabel, $mValue, $bChecked, $bQuery);
        }

        return $oFilter;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object which is an option for self::searchFilterObject()
     *
     * @param string  $sLabel   The label to give the option
     * @param string  $sValue   The value to give the option (filters self::searchFilterObject's $sColumn parameter)
     * @param boolean $bChecked Whether the value is checked by default
     * @param bool    $bQuery   Whether the supplied value is an SQL query
     *
     * @return \stdClass
     */
    public static function searchFilterObjectOption($sLabel = '', $sValue = '', $bChecked = false, $bQuery = false)
    {
        return (object) [
            'label'   => $sLabel,
            'value'   => $sValue,
            'checked' => $bChecked,
            'query'   => $bQuery,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a value from a filter object at a specific key
     *
     * @param \stdClass $oFilterObj The filter object to search
     * @param integer   $iKey       The key to inspect
     *
     * @return mixed                  Mixed on success, null on failure
     */
    public static function searchFilterGetValueAtKey($oFilterObj, $iKey)
    {
        return isset($oFilterObj->options[$iKey]->value) ? $oFilterObj->options[$iKey]->value : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "pagination" component
     *
     * @param \stdClass $oPaginationObject An object as created by self::paginationObject();
     * @param boolean   $bReturnView       Whether to return the view to the caller, or output to the browser
     *
     * @return mixed                      String when $bReturnView is true, void otherwise
     * @throws FactoryException
     */
    public static function loadPagination($oPaginationObject, $bReturnView = true)
    {
        $aData = [
            'page'      => isset($oPaginationObject->page) ? $oPaginationObject->page : null,
            'perPage'   => isset($oPaginationObject->perPage) ? $oPaginationObject->perPage : null,
            'totalRows' => isset($oPaginationObject->totalRows) ? $oPaginationObject->totalRows : null,
        ];

        //  Not using self::loadInlineView() as this may be called from many contexts
        $oView = Factory::service('View');
        return $oView->load('admin/_components/pagination', $aData, $bReturnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadPagination();
     *
     * @param integer $iPage      The current page number
     * @param integer $iPerPage   The number of results per page
     * @param integer $iTotalRows The total number of results in the result set
     *
     * @return \stdClass
     */
    public static function paginationObject($iPage, $iPerPage, $iTotalRows)
    {
        return (object) [
            'page'      => $iPage,
            'perPage'   => $iPerPage,
            'totalRows' => $iTotalRows,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Automatically decides what type of cell to load
     *
     * @param mixed  $mValue          The value of the cell
     * @param string $sCellClass      Any classes to add to the cell
     * @param string $sCellAdditional Any additional HTML to add to the cell (after the value)
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadCellAuto($mValue, $sCellClass = '', $sCellAdditional = '')
    {
        //  @todo - handle more field types
        if (is_bool($mValue)) {
            return Helper::loadBoolCell($mValue);
        } elseif (preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $mValue)) {
            return Helper::loadDateTimeCell($mValue);
        } elseif (preg_match('/\d\d\d\d-\d\d-\d\d/', $mValue)) {
            return Helper::loadDateCell($mValue);
        } else {
            return '<td class="' . $sCellClass . '">' . $mValue . $sCellAdditional . '</td>';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "user" table cell component
     *
     * @param mixed $mUser The user object or the User's ID/email/username
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadUserCell($mUser)
    {
        if (is_numeric($mUser)) {

            $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);
            $oUser      = $oUserModel->getById($mUser);

        } elseif (is_string($mUser)) {

            $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);
            $oUser      = $oUserModel->getByEmail($mUser);

            if (empty($oUser)) {
                $oUser = $oUserModel->getByUsername($mUser);
            }

        } else {
            $oUser = $mUser;
        }

        $aUser = [
            'id'          => !empty($oUser->id) ? $oUser->id : null,
            'profile_img' => !empty($oUser->profile_img) ? $oUser->profile_img : null,
            'gender'      => !empty($oUser->gender) ? $oUser->gender : null,
            'first_name'  => !empty($oUser->first_name) ? $oUser->first_name : null,
            'last_name'   => !empty($oUser->last_name) ? $oUser->last_name : null,
            'email'       => !empty($oUser->email) ? $oUser->email : null,
        ];

        $oView = Factory::service('View');
        return $oView->load('admin/_components/table-cell-user', $aUser, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "date" table cell component
     *
     * @param string $sDate   The date to render
     * @param string $sNoData What to render if the date is invalid or empty
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadDateCell($sDate, $sNoData = '&mdash;')
    {
        $aData = [
            'date'   => $sDate,
            'noData' => $sNoData,
        ];

        $oView = Factory::service('View');
        return $oView->load('admin/_components/table-cell-date', $aData, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "dateTime" table cell component
     *
     * @param string $sDateTime The dateTime to render
     * @param string $sNoData   What to render if the datetime is invalid or empty
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadDateTimeCell($sDateTime, $sNoData = '&mdash;')
    {
        $aData = [
            'dateTime' => $sDateTime,
            'noData'   => $sNoData,
        ];

        $oView = Factory::service('View');
        return $oView->load('admin/_components/table-cell-datetime', $aData, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "boolean" table cell component
     *
     * @param string $value     The value to 'truthy' test
     * @param string $sDateTime A datetime to show (for truthy values only)
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadBoolCell($value, $sDateTime = null)
    {
        $aData = [
            'value'    => $value,
            'dateTime' => $sDateTime,
        ];

        $oView = Factory::service('View');
        return $oView->load('admin/_components/table-cell-boolean', $aData, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "settings component table" component
     *
     * @param string $sComponentService The component service to use
     * @param array  $sProvider         The model provider
     * @param string $sComponentType    The type of component being loaded
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadSettingsComponentTable($sComponentService, $sProvider, $sComponentType = 'component')
    {
        $oModel          = Factory::service($sComponentService, $sProvider);
        $sKey            = $oModel->getSettingKey();
        $aComponents     = $oModel->getAll();
        $aEnabled        = (array) $oModel->getEnabledSlug();
        $bEnableMultiple = $oModel->isMultiple();

        $aData = [
            'key'               => $sKey,
            'components'        => $aComponents,
            'enabled'           => $aEnabled,
            'canSelectMultiple' => $bEnableMultiple,
            'componentType'     => $sComponentType,
        ];

        $oView = Factory::service('View');
        return $oView->load('admin/_components/settings-component-table', $aData, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Alias to loadSettingsComponentTable()
     *
     * @param string $sDriverService The driver service to use
     * @param array  $sProvider      The model provider
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadSettingsDriverTable($sDriverService, $sProvider)
    {
        return self::loadSettingsComponentTable($sDriverService, $sProvider, 'driver');
    }

    // --------------------------------------------------------------------------

    /**
     * Alias to loadSettingsComponentTable()
     *
     * @param string $sSkinService The skin service to use
     * @param array  $sProvider    The model provider
     *
     * @return string
     * @throws FactoryException
     */
    public static function loadSettingsSkinTable($sSkinService, $sProvider)
    {
        return self::loadSettingsComponentTable($sSkinService, $sProvider, 'skin');
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a button to Admin's header area
     *
     * @param string $sUrl          The button's URL
     * @param string $sLabel        The button's label
     * @param string $sContext      The button's context
     * @param string $sConfirmTitle If a confirmation is required, the title to use
     * @param string $sConfirmBody  If a confirmation is required, the body to use
     */
    public static function addHeaderButton(
        $sUrl,
        $sLabel,
        $sContext = null,
        $sConfirmTitle = null,
        $sConfirmBody = null
    ) {
        $sContext = empty($sContext) ? 'primary' : $sContext;

        self::$aHeaderButtons[] = [
            'url'          => $sUrl,
            'label'        => $sLabel,
            'context'      => $sContext,
            'confirmTitle' => $sConfirmTitle,
            'confirmBody'  => $sConfirmBody,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the admin header buttons
     *
     * @return array
     */
    public static function getHeaderButtons()
    {
        return self::$aHeaderButtons;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a dynamic table
     *
     * @param string $sKey    The key to give items in the table
     * @param array  $aFields The fields to render
     * @param array  $aData   Data to populate the table with
     *
     * @return string
     * @throws FactoryException
     */
    public static function dynamicTable($sKey, array $aFields, array $aData = [])
    {
        return Factory::service('View')
            ->load(
                'admin/_components/dynamic-table',
                [
                    'sKey'    => $sKey,
                    'aFields' => $aFields,
                    'aData'   => $aData,
                ],
                true
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Convinience method for generating tabbed views
     *
     * @param array  $aTabs  The tab config ['label' => '', 'content' => 'string|callable()']
     * @param string $sGroup The group name, useful if more than one tab group appears on a page
     *
     * @return string
     * @throws FactoryException
     */
    public static function tabs(array $aTabs = [], $sGroup = ''): string
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $i      = 0;
        $sGroup = $sGroup ? 'tab-group-' . $sGroup : 'tab-group';

        foreach ($aTabs as &$aTab) {

            $aTab['label']   = getFromArray('label', $aTab);
            $aTab['slug']    = url_title($aTab['label'], '-', true);
            $aTab['content'] = getFromArray('content', $aTab);

            if ($oInput->post($sGroup) == 'tab-' . $aTab['slug']) {
                $aTab['active'] = 'active';
            } elseif ($i === 0 && !$oInput->post($sGroup)) {
                $aTab['active'] = 'active';
            } else {
                $aTab['active'] = '';
            }

            $i++;
        }

        ob_start();
        ?>
        <input type="hidden" name="<?=$sGroup?>" value="<?=set_value($sGroup)?>" id="<?=$sGroup?>"/>
        <ul class="tabs" data-tabgroup="<?=$sGroup?>" data-active-tab-input="#<?=$sGroup?>">
            <?php
            foreach ($aTabs as &$aTab) {
                ?>
                <li class="tab <?=$aTab['active']?>">
                    <a href="#" data-tab="tab-<?=$aTab['slug']?>">
                        <?=$aTab['label']?>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>
        <section class="tabs" data-tabgroup="<?=$sGroup?>">
            <?php
            foreach ($aTabs as &$aTab) {
                ?>
                <div class="tab-page tab-<?=$aTab['slug']?> <?=$aTab['active']?> fieldset">
                    <?php
                    if (is_callable($aTab['content'])) {
                        echo $aTab['content']();
                    } else {
                        echo $aTab['content'];
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </section>
        <?php
        return ob_get_clean();
    }
}
