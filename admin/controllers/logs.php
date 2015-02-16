<?php

/**
 * This class renders the log browsers
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Logs extends \AdminController
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Logs');

        if (userHasPermission('admin.logs:0.can_browse_site_logs')) {

            $navGroup->addMethod('Browse Site Logs', 'site');
        }

        if (userHasPermission('admin.logs:0.can_browse_event_logs')) {

            $navGroup->addMethod('Browse Event Logs', 'event');
        }

        if (userHasPermission('admin.logs:0.can_browse_admin_logs')) {

            $navGroup->addMethod('Browse Admin Logs', 'changelog');
        }

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    public function site()
    {
        if (!userHasPermission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->load->helper('string');
        $method = $this->uri->segment(5) ? $this->uri->segment(5) : 'index';
        $method = 'site' . underscore_to_camelcase(strtolower($method), false);

        if (method_exists($this, $method)) {

            $this->{$method}();

        } else {

            show_404('', true);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse site log files
     * @return void
     */
    protected function siteIndex()
    {
        if (!userHasPermission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Browse Logs';

        $this->asset->load('mustache.js/mustache.js', 'NAILS-BOWER');
        $this->asset->load('nails.admin.logs.site.min.js', 'NAILS');
        $this->asset->inline('logsSite = new NAILS_Admin_Logs_Site();','JS');

        \Nails\Admin\Helper::loadView('site/index');
    }

    // --------------------------------------------------------------------------

    protected function siteView()
    {
        if (!userHasPermission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------
        $file = $this->uri->segment(6);
        $this->data['page']->title = 'Browse Logs &rsaquo; ' . $file;

        $this->load->model('admin/admin_sitelog_model');
        $this->data['logs'] = $this->admin_sitelog_model->readLog($file);

        if (!$this->data['logs']) {

            show_404();
        }

        \Nails\Admin\Helper::loadView('site/view');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Site Events
     * @return void
     */
    public function event()
    {
        if (!userHasPermission('admin.logs:0.can_browse_event_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Events';

        // --------------------------------------------------------------------------

        $tablePrefix = $this->event->getTablePrefix();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $tablePrefix . '.created';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'desc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tablePrefix . '.created' => 'Created',
            $tablePrefix . '.type'    => 'Type'
        );

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = array(
            'sort' => array(
                array($sortOn, $sortOrder)
            ),
            'keywords' => $keywords
        );

        //  Are we downloading? Or viewing?
        if ($this->input->get('dl') && userHasPermission('admin.logs:0.can_download_event_logs')) {

            //  Get all items for the search, no need to paginate
            $data['RETURN_QUERY_OBJECT'] = true;

            $events = $this->event->get_all(null, null, $data);

            \Nails\Admin\Helper::loadCsv($events, 'export-events-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $totalRows            = $this->event->count_all($data);
            $this->data['events'] = $this->event->get_all($page, $perPage, $data);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = \Nails\Admin\Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
            $this->data['pagination'] = \Nails\Admin\Helper::paginationObject($page, $perPage, $totalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin.logs:0.can_download_event_logs')) {

                //  Build the query string, so that the same search is applies
                $params              = array();
                $params['dl']        = true;
                $params['sortOn']    = $this->input->get('sortOn');
                $params['sortOrder'] = $this->input->get('sortOrder');
                $params['keywords']  = $this->input->get('keywords');

                $params = array_filter($params);
                $params = http_build_query($params);

                \Nails\Admin\Helper::addHeaderButton('admin/admin/logs/event?' . $params, 'Download As CSV', 'orange');
            }

            \Nails\Admin\Helper::loadView('event/index');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Admin Changelog
     * @return void
     */
    public function changelog()
    {
        if (!userHasPermission('admin.logs:0.can_browse_change_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Changelog';

        // --------------------------------------------------------------------------

        $tablePrefix = $this->admin_changelog_model->getTablePrefix();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $tablePrefix . '.created';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'desc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tablePrefix . '.created' => 'Created',
            $tablePrefix . '.type'    => 'Type'
        );

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = array(
            'sort' => array(
                array($sortOn, $sortOrder)
            ),
            'keywords' => $keywords
        );

        //  Are we downloading? Or viewing?
        if ($this->input->get('dl') && userHasPermission('admin.logs:0.can_download_change_logs')) {

            //  Get all items for the search, no need to paginate
            $data['RETURN_QUERY_OBJECT'] = true;

            $changelog = $this->admin_changelog_model->get_all(null, null, $data);

            \Nails\Admin\Helper::loadCsv($changelog, 'export-changelog-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $totalRows               = $this->admin_changelog_model->count_all($data);
            $this->data['changelog'] = $this->admin_changelog_model->get_all($page, $perPage, $data);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = \Nails\Admin\Helper::searchObject(false, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
            $this->data['pagination'] = \Nails\Admin\Helper::paginationObject($page, $perPage, $totalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin.logs:0.can_download_change_logs')) {

                //  Build the query string, so that the same search is applies
                $params              = array();
                $params['dl']        = true;
                $params['sortOn']    = $this->input->get('sortOn');
                $params['sortOrder'] = $this->input->get('sortOrder');
                $params['keywords']  = $this->input->get('keywords');

                $params = array_filter($params);
                $params = http_build_query($params);

                \Nails\Admin\Helper::addHeaderButton('admin/admin/logs/changelog?' . $params, 'Download As CSV', 'orange');
            }

            \Nails\Admin\Helper::loadView('changelog/index');
        }
    }
}
