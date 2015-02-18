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

class Helper
{
    protected static $headerButtons = array();

    // --------------------------------------------------------------------------

    /**
     * Loads a view in admin taking into account the module being accessed. Passes controller
     * data and optionally loads the header and footer views.
     * @param  string  $viewFile      The view to load
     * @param  boolean $loadStructure Whether or not to include the header and footers in the output
     * @param  boolean $returnView    Whether to return the view or send it to the Output class
     * @return mixed                  String when $returnView is true, void otherwise
     */
    public static function loadView($viewFile, $loadStructure = true, $returnView = false)
    {
        $controllerData =& getControllerData();

        //  Get the CI super object
        $ci =& get_instance();

        //  Are we in a modal?
        if ($ci->input->get('isModal')) {

            if (!isset($controllerData['headerOverride'])) {

                $controllerData['isModal'] = true;
            }

            if (empty($controllerData['headerOverride'])) {

                $controllerData['headerOverride'] = 'structure/headerBlank';
            }

            if (empty($controllerData['footerOverride'])) {

                $controllerData['footerOverride'] = 'structure/footerBlank';
            }
        }

        //  Hey presto!
        if ($returnView) {

            $return = '';

            if ($loadStructure) {

                if (!empty($controllerData['headerOverride'])) {

                    $return .= $ci->load->view($controllerData['headerOverride'], $controllerData, true);

                } else {

                    $return .= $ci->load->view('structure/header', $controllerData, true);
                }
            }

            $return .= self::loadInlineView($viewFile, $controllerData, true);

            if ($loadStructure) {

                if (!empty($controllerData['footerOverride'])) {

                    $return .= $ci->load->view($controllerData['footerOverride'], $controllerData, true);

                } else {

                    $return .= $ci->load->view('structure/footer', $controllerData, true);
                }
            }

            return $return;

        } else {

            if ($loadStructure) {

                if (!empty($controllerData['headerOverride'])) {

                    $ci->load->view($controllerData['headerOverride'], $controllerData);

                } else {

                    $ci->load->view('structure/header', $controllerData);
                }
            }

            self::loadInlineView($viewFile, $controllerData);

            if ($loadStructure) {

                if (!empty($controllerData['footerOverride'])) {

                    $ci->load->view($controllerData['footerOverride'], $controllerData);

                } else {

                    $ci->load->view('structure/footer', $controllerData);
                }
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Fenerates a CSV and sends to the browser, if a filename is given then it's
     * sent as a download
     * @param  mixed  $data     The data to render, either an array or a DB query object
     * @param  string $filename The filename to give the file if downloading
     * @return void
     */
    public static function loadCsv($data, $filename = '')
    {
        //  Determine what type of data has been supplied
        if (is_array($data) || get_class($data) == 'CI_DB_mysqli_result') {

            //  If filename has been specified then set some additional headers
            if (!empty($filename)) {

                $ci = get_instance();

                //  Common headers
                $ci->output->set_content_type('text/csv');
                $ci->output->set_header('Content-Disposition: attachment; filename="' . $filename . '"');
                $ci->output->set_header('Expires: 0');
                $ci->output->set_header("Content-Transfer-Encoding: binary");

                //  Handle IE, classic.
                $userAgent = $ci->input->server('HTTP_USER_AGENT');

                if (strpos($userAgent, "MSIE") !== false) {

                    $ci->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    $ci->output->set_header('Pragma: public');

                } else {

                    $ci->output->set_header('Pragma: no-cache');
                }
            }

            //  Not using self::loadInlineView() as this may be called from many contexts
            if (is_array($data)) {

                return get_instance()->load->view('admin/_utilities/csv/array', array('data' => $data));

            } elseif (get_class($data) == 'CI_DB_mysqli_result') {

                return get_instance()->load->view('admin/_utilities/csv/dbResult', array('data' => $data));
            }

        } else {

            $subject  = 'Unsupported object type passed to \Nails\Admin\Helper::loadCSV';
            $message  = 'An unsupported object was passed to \Nails\Admin\Helper::loadCSV. A CSV ';
            $message .= 'file could not be generated. Setails are show below:<br /><br />' . print_r($data, true);

            showFatalError($subject, $message);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load a single view taking into account the module being accessed.
     * @param  string  $viewFile   The view to load
     * @param  array   $viewData   The data to pass to the view
     * @param  boolean $returnView Whether to return the view or send it to the Output class
     * @return mixed               String when $returnView is true, void otherwise
     */
    public static function loadInlineView($viewFile, $viewData = array(), $returnView = false)
    {
        $controllerData =& getControllerData();
        $controllerPath = !empty($controllerData['currentRequest']['path']) ? $controllerData['currentRequest']['path'] : '';

        //  Work out where the controller's view folder is
        $viewPath  = dirname($controllerPath);
        $viewPath .= '/../views/';

        //  And get the directory name which is the same as the controller's filename
        $basename  = basename($controllerPath);
        $basename  = substr($basename, 0, strrpos($basename, '.'));
        $viewPath .= $basename . '/';

        //  Glue the requested view onto the end and add .php
        $viewPath .= $viewFile . '.php';

        //  Get the CI super object
        $ci =& get_instance();

        //  Hey presto!
        if ($returnView) {

            return $ci->load->view($viewPath, $viewData, true);

        } else {

            $ci->load->view($viewPath, $viewData);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "search" component
     * @param  stdClass $searchObject An object as created by self::searchObject();
     * @param  boolean  $returnView   Whether to return the view to the caller, or output to the browser
     * @return mixed                  String when $retrunView is true, void otherwise
     */
    public static function loadSearch($searchObject, $returnView = false)
    {
        $data = array(
            'searchable'     => isset($searchObject->searchable) ? $searchObject->searchable : true,
            'sortColumns'    => isset($searchObject->sortColumns) ? $searchObject->sortColumns : array(),
            'sortOn'         => isset($searchObject->sortOn) ? $searchObject->sortOn : null,
            'sortOrder'      => isset($searchObject->sortOrder) ? $searchObject->sortOrder : null,
            'perPage'        => isset($searchObject->perPage) ? $searchObject->perPage : 50,
            'keywords'       => isset($searchObject->keywords) ? $searchObject->keywords : '',
            'checkboxFilter' => isset($searchObject->checkboxFilter) ? $searchObject->checkboxFilter : array(),
            'dropdownFilter' => isset($searchObject->dropdownFilter) ? $searchObject->dropdownFilter : array()
        );

        //  Not using self::loadInlineView() as this may be called from many contexts
        return get_instance()->load->view('admin/_utilities/search', $data, $returnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadSearch()
     * @param  boolean  $searchable     Whether the result set is keyword searchable
     * @param  array    $sortColumns    An array of columns to sort results by
     * @param  string   $sortOn         The column to sort on
     * @param  string   $sortOrder      The order to sort results in
     * @param  integer  $perPage        The number of results to show per page
     * @param  string   $keywords       Keywords to apply to the search result
     * @param  array    $checkboxFilter An array of filters to filter the results by, presented as checkboxes
     * @param  array    $dropdownFilter An array of filters to filter the results by, presented as a dropdown
     * @return stdClass
     */
    public static function searchObject($searchable, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords = '', $checkboxFilter = array(), $dropdownFilter = array())
    {
        $searchObject                 = new \stdClass();
        $searchObject->searchable     = $searchable;
        $searchObject->sortColumns    = $sortColumns;
        $searchObject->sortOn         = $sortOn;
        $searchObject->sortOrder      = $sortOrder;
        $searchObject->perPage        = $perPage;
        $searchObject->keywords       = $keywords;
        $searchObject->checkboxFilter = $checkboxFilter;
        $searchObject->dropdownFilter = $dropdownFilter;

        return $searchObject;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::searchObject()'s
     * $checkboxFilter and $dropdownFilter parameters
     * @param  string $column  The name of the column to filter on, leave blank if you do not wish to use Nails's automatic filtering
     * @param  string $label   The label to give the filter group
     * @param  array  $options An array of options for the dropdown, either key => value pairs or a 3 element array: 0 = label, 1 = value, 2 = default check status
     * @return void
     */
    public static function searchFilterObject($column, $label, $options)
    {
        $filterObject          = new \stdClass();
        $filterObject->column  = $column;
        $filterObject->label   = $label;
        $filterObject->options = array();

        foreach ($options as $index => $option) {

            $temp = new \stdClass();

            if (is_array($option)) {

                $temp->label   = isset($option[0]) ? $option[0] : null;
                $temp->value   = isset($option[1]) ? $option[1] : null;
                $temp->checked = isset($option[2]) ? $option[2] : false;

            } else {

                $temp->label   = $option;
                $temp->value   = $index;
                $temp->checked = false;
            }

            $filterObject->options[] = $temp;
        }

        return $filterObject;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a value from a filter object at a specific key
     * @param  stdClass $filterObject The filter object to search
     * @param  integer  $key          The key to inspect
     * @return mixed                  Mixed on success, null on failure
     */
    public static function searchFilterGetValueAtKey($filterObject, $key)
    {
        return isset($filterObject->options[$key]->value) ? $filterObject->options[$key]->value : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "pagination" component
     * @param  stdClass $paginationObject An object as created by self::paginationObject();
     * @param  boolean  $returnView       Whether to return the view to the caller, or output to the browser
     * @return mixed                      String when $retrunView is true, void otherwise
     */
    public static function loadPagination($paginationObject, $returnView = false)
    {
        $data = array(
            'page'      => isset($paginationObject->page) ? $paginationObject->page : null,
            'perPage'   => isset($paginationObject->perPage) ? $paginationObject->perPage : null,
            'totalRows' => isset($paginationObject->totalRows) ? $paginationObject->totalRows : null
        );

        //  Not using self::loadInlineView() as this may be called from many contexts
        return get_instance()->load->view('admin/_utilities/pagination', $data, $returnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadPagination();
     * @param  integer $page      The current page number
     * @param  integer $perPage   The number of results per page
     * @param  integer $totalRows The total number of results in the result set
     * @return stdClass
     */
    public static function paginationObject($page, $perPage, $totalRows)
    {
        $paginationObject            = new \stdClass();
        $paginationObject->page      = $page;
        $paginationObject->perPage   = $perPage;
        $paginationObject->totalRows = $totalRows;

        return $paginationObject;
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "user" table cell component
     * @param  stdClass $user The user object
     * @return string
     */
    public static function loadUserCell($user)
    {
        return get_instance()->load->view('admin/_utilities/table-cell-user', $user, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "date" table cell component
     * @param  string $date   The date to render
     * @param  string $noData What to render if the date is invalid or empty
     * @return string
     */
    public static function loadDateCell($date, $noData = '&mdash;')
    {
        $data = array(
            'date'   => $date,
            'noData' => $noData
        );

        return get_instance()->load->view('admin/_utilities/table-cell-date', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "dateTime" table cell component
     * @param  string $dateTime The dateTime to render
     * @param  string $noData   What to render if the datetime is invalid or empty
     * @return string
     */
    public static function loadDateTimeCell($dateTime, $noData = '&mdash;')
    {
        $data = array(
            'dateTime' => $dateTime,
            'noData'   => $noData
        );

        return get_instance()->load->view('admin/_utilities/table-cell-datetime', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "boolean" table cell component
     * @param  string $value    The value to 'truthy' test
     * @param  string $dateTime A datetime to show (for truthy values only)
     * @return string
     */
    public static function loadBoolCell($value, $dateTime = null)
    {
        $data = array(
            'value'    => $value,
            'dateTime' => $dateTime
        );

        return get_instance()->load->view('admin/_utilities/table-cell-boolean', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a button to Admin's header area
     * @param string $url   The button's URL
     * @param string $label The button's label
     * @param string $class The class(es) to apply to the button
     */
    public static function addHeaderButton($url, $label, $color = 'green', $confirmTitle = '', $confirmBody = '')
    {
        if ($confirmTitle || $confirmBody) {

            $color .= ' confirm';
        }

        self::$headerButtons[] = array(
            'url'          => $url,
            'label'        => $label,
            'color'        => $color,
            'confirmTitle' => $confirmTitle,
            'confirmBody'  => $confirmBody
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the admin header bttons
     * @return array
     */
    public static function getHeaderButtons()
    {
        return self::$headerButtons;
    }
}
