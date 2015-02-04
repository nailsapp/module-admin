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
    public static function navGrouping($label, $icon = 'fa-cog')
    {
        $navGrouping        = new \stdClass();
        $navGrouping->label = $label;
        $navGrouping->icon  = 'fa-cog';

        return $navGrouping;
    }

    // --------------------------------------------------------------------------

    public static function navOption($url, $label)
    {

        $navOption           = new \stdClass();
        $navOption->url      = $url;
        $navOption->label    = $label;

        return $navOption;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads a view in admin taking into account the module being accessed.
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

                $return .= $ci->load->view('structure/header', $controllerData, true);
            }

            $return .= self::loadInlineView($viewFile, $controllerData, true);

            if ($loadStructure) {

                $return .= $ci->load->view('structure/footer', $controllerData, true);
            }

            return $return;

        } else {

            if ($loadStructure) {

                $ci->load->view('structure/header', $controllerData);
            }

            self::loadInlineView($viewFile, $controllerData);

            if ($loadStructure) {

                $ci->load->view('structure/footer', $controllerData);
            }
        }
    }

    // --------------------------------------------------------------------------

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

    public static function loadSearch($sortOn = array(), $filters = array())
    {
        $data = array(
            'sortOn'  => $sortOn,
            'filters' => $filters
        );
        return get_instance()->load->view('admin/_utilities/search', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns pagination markup for the current page.
     * @param  integer $totalRows The total number of rows in the resultset
     * @param  integer $perPage   the number of results to show per page
     * @return string
     */
    public static function loadPagination($totalRows = 0, $perPage = 50)
    {
        $data = array(
            'totalRows' => $totalRows,
            'perPage'   => $perPage
        );
        return get_instance()->load->view('admin/_utilities/pagination', $data, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the markup for a 'user' table cell
     * @param  stdClass $user The user object
     * @return string
     */
    public static function loadUserCell($user)
    {
        return get_instance()->load->view('admin/_utilities/table-cell-user', $user, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the markup for a 'date' table cell
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
     * Returns the markup for a 'dateTime' table cell
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
     * Returns the markup for a 'boolean' table cell
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
}
