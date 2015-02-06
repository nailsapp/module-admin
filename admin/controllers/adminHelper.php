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
     * Load a single view taking into account the module being accessed.
     * @param  string  $viewFile   The view to load
     * @param  array   $viewData   The data to pass to the view
     * @param  boolean $returnView Whether to return the view or send it to the Output class
     * @return mixed               String when $returnView is true, void otherwise
     */
    public static function loadInlineView($viewFile, $viewData, $returnView = false)
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
     * @return string
     */
    public static function loadSearch($searchObject)
    {
        $data = array(
            'sortColumns' => isset($searchObject->sortColumns) ? $searchObject->sortColumns : array(),
            'sortOn'      => isset($searchObject->sortOn) ? $searchObject->sortOn : null,
            'sortOrder'   => isset($searchObject->sortOrder) ? $searchObject->sortOrder : null,
            'perPage'     => isset($searchObject->perPage) ? $searchObject->perPage : 50,
            'keywords'    => isset($searchObject->keywords) ? $searchObject->keywords : '',
            'filters'     => isset($searchObject->filters) ? $searchObject->filters : array()
        );

        return get_instance()->load->view('admin/_utilities/search', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadSearch()
     * @param  array    $sortColumns An array of columns to sort results by
     * @param  string   $sortOn      The column to sort on
     * @param  string   $sortOrder   The order to sort results in
     * @param  integer  $perPage     The number of results to show per page
     * @param  string   $keywords    Keywords to apply to the search result
     * @param  array    $filters     An array of filters to filter the results by
     * @return stdClass
     */
    public static function searchObject($sortColumns, $sortOn, $sortOrder, $perPage, $keywords = '', $filters = array())
    {
        $searchObject              = new \stdClass();
        $searchObject->sortColumns = $sortColumns;
        $searchObject->sortOn      = $sortOn;
        $searchObject->sortOrder   = $sortOrder;
        $searchObject->perPage     = $perPage;
        $searchObject->keywords    = $keywords;
        $searchObject->filters     = $filters;

        return $searchObject;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "pagination" component
     * @param  stdClass $paginationObject An object as created by self::paginationObject();
     * @return string
     */
    public static function loadPagination($paginationObject)
    {
        $data = array(
            'page'      => isset($paginationObject->page) ? $paginationObject->page : null,
            'perPage'   => isset($paginationObject->perPage) ? $paginationObject->perPage : null,
            'totalRows' => isset($paginationObject->totalRows) ? $paginationObject->totalRows : null
        );

        return get_instance()->load->view('admin/_utilities/pagination', $data, true);
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
