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

use Nails\Factory;

class Helper
{
    protected static $aHeaderButtons = [];

    // --------------------------------------------------------------------------

    /**
     * Loads a view in admin taking into account the module being accessed. Passes controller
     * data and optionally loads the header and footer views.
     *
     * @param  string  $sViewFile      The view to load
     * @param  boolean $bLoadStructure Whether or not to include the header and footers in the output
     * @param  boolean $bReturnView    Whether to return the view or send it to the Output class
     *
     * @return mixed                  String when $bReturnView is true, void otherwise
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
                $aData['headerOverride'] = 'structure/header/blank';
            }

            if (empty($aData['footerOverride'])) {
                $aData['footerOverride'] = 'structure/footer/blank';
            }
        }


        //  Hey presto!
        if ($bReturnView) {

            $sReturn = '';

            if ($bLoadStructure) {
                if (!empty($aData['headerOverride'])) {
                    $sReturn .= $oView->load($aData['headerOverride'], $aData, true);
                } else {
                    $sReturn .= $oView->load('_components/structure/header', $aData, true);
                }
            }

            $sReturn .= self::loadInlineView($sViewFile, $aData, true);

            if ($bLoadStructure) {
                if (!empty($aData['footerOverride'])) {
                    $sReturn .= $oView->load($aData['footerOverride'], $aData, true);
                } else {
                    $sReturn .= $oView->load('_components/structure/footer', $aData, true);
                }
            }

            return $sReturn;

        } else {

            if ($bLoadStructure) {
                if (!empty($aData['headerOverride'])) {
                    $oView->load($aData['headerOverride'], $aData);
                } else {
                    $oView->load('_components/structure/header', $aData);
                }
            }

            self::loadInlineView($sViewFile, $aData);

            if ($bLoadStructure) {
                if (!empty($aData['footerOverride'])) {
                    $oView->load($aData['footerOverride'], $aData);
                } else {
                    $oView->load('_components/structure/footer', $aData);
                }
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a CSV and sends to the browser, if a filename is given then it's
     * sent as a download
     *
     * @param  mixed   $mData      The data to render, either an array or a DB query object
     * @param  string  $sFilename  The filename to give the file if downloading
     * @param  boolean $bHeaderRow The first element in the $mData resultset is a header row
     *
     * @return void
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

            $subject = 'Unsupported object type passed to ' . get_class() . '::loadCSV';
            $message = 'An unsupported object was passed to ' . get_class() . '::loadCSV. A CSV ';
            $message .= 'file could not be generated. Details are shown below:<br /><br />' . print_r($mData, true);

            showFatalError($subject, $message);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load a single view taking into account the module being accessed.
     *
     * @param  string  $sViewFile   The view to load
     * @param  array   $aViewData   The data to pass to the view
     * @param  boolean $bReturnView Whether to return the view or send it to the Output class
     *
     * @throws \Exception
     *
     * @return mixed               String when $bReturnView is true, void otherwise
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
        } catch (\Exception $e) {
            //  If it fails, and the controller is a default admin controller then
            //  load up that view
            $aParentClasses = class_parents($aCtrlData['currentRequest']['className']);
            if (!in_array('Nails\\Admin\\Controller\\DefaultController', $aParentClasses)) {
                throw new \Exception(
                    $e->getMessage(),
                    $e->getCode()
                );
            }

            return $oView->load('admin/defaultcontroller/' . $sViewFile, $aViewData, $bReturnView);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "search" component
     *
     * @param  \stdClass $oSearchObj  An object as created by self::searchObject();
     * @param  boolean   $bReturnView Whether to return the view to the caller, or output to the browser
     *
     * @return mixed                  String when $bReturnView is true, void otherwise
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
     * @param  boolean $bSearchable     Whether the result set is keyword searchable
     * @param  array   $aSortColumns    An array of columns to sort results by
     * @param  string  $sSortOn         The column to sort on
     * @param  string  $sSortOrder      The order to sort results in
     * @param  integer $iPerPage        The number of results to show per page
     * @param  string  $sKeywords       Keywords to apply to the search result
     * @param  array   $aCheckboxFilter An array of filters to filter the results by, presented as checkboxes
     * @param  array   $aDropdownFilter An array of filters to filter the results by, presented as a dropdown
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
     * @param  string $sColumn  The name of the column to filter on, leave blank if you do not wish to use Nails's
     *                          automatic filtering
     * @param  string $sLabel   The label to give the filter group
     * @param  array  $aOptions An array of options for the dropdown, either key => value pairs or a 3 element array: 0
     *                          = label, 1 = value, 2 = default check status
     *
     * @return \stdClass
     */
    public static function searchFilterObject($sColumn, $sLabel, $aOptions)
    {
        $oFilterObject = (object) [
            'column'  => $sColumn,
            'label'   => $sLabel,
            'options' => [],
        ];

        foreach ($aOptions as $sIndex => $mOption) {

            if (is_array($mOption)) {

                $sLabel   = isset($mOption[0]) ? $mOption[0] : null;
                $mValue   = isset($mOption[1]) ? $mOption[1] : null;
                $bChecked = isset($mOption[2]) ? $mOption[2] : false;

            } else {

                $sLabel   = $mOption;
                $mValue   = $sIndex;
                $bChecked = false;
            }

            $oFilterObject->options[] = self::searchFilterObjectOption($sLabel, $mValue, $bChecked);
        }

        return $oFilterObject;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object which is an option for self::searchFilterObject()
     *
     * @param  string  $sLabel   The label to give the option
     * @param  string  $sValue   The value to give the option (filters self::searchFilterObject's $sColumn parameter)
     * @param  boolean $bChecked Whether the value si checked by default
     *
     * @return \stdClass
     */
    public static function searchFilterObjectOption($sLabel = '', $sValue = '', $bChecked = false)
    {
        return (object) [
            'label'   => $sLabel,
            'value'   => $sValue,
            'checked' => $bChecked,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a value from a filter object at a specific key
     *
     * @param  \stdClass $oFilterObj The filter object to search
     * @param  integer   $iKey       The key to inspect
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
     * @param  \stdClass $oPaginationObject An object as created by self::paginationObject();
     * @param  boolean   $bReturnView       Whether to return the view to the caller, or output to the browser
     *
     * @return mixed                      String when $bReturnView is true, void otherwise
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
     * @param  integer $iPage      The current page number
     * @param  integer $iPerPage   The number of results per page
     * @param  integer $iTotalRows The total number of results in the result set
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
     * Load the admin "user" table cell component
     *
     * @param  mixed $mUser The user object or the User's ID/email/username
     *
     * @return string
     */
    public static function loadUserCell($mUser)
    {
        if (is_numeric($mUser)) {

            $oUserModel = Factory::model('User', 'nailsapp/module-auth');
            $oUser      = $oUserModel->getById($mUser);

        } elseif (is_string($mUser)) {

            $oUserModel = Factory::model('User', 'nailsapp/module-auth');
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
     * @param  string $sDate   The date to render
     * @param  string $sNoData What to render if the date is invalid or empty
     *
     * @return string
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
     * @param  string $sDateTime The dateTime to render
     * @param  string $sNoData   What to render if the datetime is invalid or empty
     *
     * @return string
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
     * @param  string $value     The value to 'truthy' test
     * @param  string $sDateTime A datetime to show (for truthy values only)
     *
     * @return string
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
     * @param  string $sModel         The model to use
     * @param  array  $sProvider      The model provider
     * @param  string $sComponentType The type of component being loaded
     *
     * @return string
     */
    public static function loadSettingsComponentTable($sModel, $sProvider, $sComponentType = 'component')
    {
        $oModel          = Factory::model($sModel, $sProvider);
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
     * @param  string $sModel    The model to use
     * @param  array  $sProvider The model provider
     *
     * @return string
     */
    public static function loadSettingsDriverTable($sModel, $sProvider)
    {
        return self::loadSettingsComponentTable($sModel, $sProvider, 'driver');
    }

    // --------------------------------------------------------------------------

    /**
     * Alias to loadSettingsComponentTable()
     *
     * @param  string $sModel    The model to use
     * @param  array  $sProvider The model provider
     *
     * @return string
     */
    public static function loadSettingsSkinTable($sModel, $sProvider)
    {
        return self::loadSettingsComponentTable($sModel, $sProvider, 'skin');
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
     * @return array
     */
    public static function getHeaderButtons()
    {
        return self::$aHeaderButtons;
    }
}
