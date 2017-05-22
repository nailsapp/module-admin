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

use Nails\Factory;
use Nails\Admin\Helper;
use Nails\Admin\Controller\Base;

class Logs extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
        $oNavGroup->setLabel('Logs');
        $oNavGroup->setIcon('fa-archive');

        if (userHasPermission('admin:admin:logs:site:browse')) {

            $oNavGroup->addAction('Browse Site Logs', 'site');
        }

        if (userHasPermission('admin:admin:logs:event:browse')) {

            $oNavGroup->addAction('Browse Event Logs', 'event');
        }

        if (userHasPermission('admin:admin:logs:change:browse')) {

            $oNavGroup->addAction('Browse Admin Logs', 'changelog');
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['site:browse']     = 'Can browse site logs';
        $permissions['event:browse']    = 'Can browse event logs';
        $permissions['event:download']  = 'Can download event logs';
        $permissions['change:browse']   = 'Can browse change logs';
        $permissions['change:download'] = 'Can download change logs';

        return $permissions;
    }

    // --------------------------------------------------------------------------

    public function site()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        Factory::helper('string');
        $method = $this->uri->segment(5) ? $this->uri->segment(5) : 'index';
        $method = 'site' . underscoreToCamelcase(strtolower($method), false);

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
        if (!userHasPermission('admin:admin:logs:site:browse')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Browse Logs';

        $oAsset = Factory::service('Asset');
        $oAsset->library('MUSTACHE');
        $oAsset->load('nails.admin.logs.site.min.js', 'NAILS');
        $oAsset->inline('logsSite = new NAILS_Admin_Logs_Site();', 'JS');

        Helper::loadView('site/index');
    }

    // --------------------------------------------------------------------------

    protected function siteView()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------
        $file = $this->uri->segment(6);
        $this->data['page']->title = 'Browse Logs &rsaquo; ' . $file;

        $oSiteLogModel = Factory::model('SiteLog', 'nailsapp/module-admin');
        $this->data['logs'] = $oSiteLogModel->readLog($file);

        if (!$this->data['logs']) {

            show_404();
        }

        Helper::loadView('site/view');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Site Events
     * @return void
     */
    public function event()
    {
        if (!userHasPermission('admin:admin:logs:event:browse')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Events';

        // --------------------------------------------------------------------------

        $tableAlias = $this->event->getTableAlias();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $tableAlias . '.created';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'desc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tableAlias . '.created' => 'Created',
            $tableAlias . '.type'    => 'Type'
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
        if ($this->input->get('dl') && userHasPermission('admin:admin:logs:event:download')) {

            //  Get all items for the search, the view will iterate over the resultset
            $events = $this->event->getAllRawQuery(null, null, $data);

            Helper::loadCsv($events, 'export-events-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $totalRows            = $this->event->countAll($data);
            $this->data['events'] = $this->event->getAll($page, $perPage, $data);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
            $this->data['pagination'] = Helper::paginationObject($page, $perPage, $totalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin:admin:logs:event:download')) {

                //  Build the query string, so that the same search is applies
                $params              = array();
                $params['dl']        = true;
                $params['sortOn']    = $this->input->get('sortOn');
                $params['sortOrder'] = $this->input->get('sortOrder');
                $params['keywords']  = $this->input->get('keywords');

                $params = array_filter($params);
                $params = http_build_query($params);

                Helper::addHeaderButton('admin/admin/logs/event?' . $params, 'Download As CSV');
            }

            Helper::loadView('event/index');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Admin Changelog
     * @return void
     */
    public function changelog()
    {
        if (!userHasPermission('admin:admin:logs:change:browse')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Changelog';

        // --------------------------------------------------------------------------

        $oChangeLogModel = Factory::model('ChangeLog', 'nailsapp/module-admin');
        $tableAlias     = $oChangeLogModel->getTableAlias();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $tableAlias . '.created';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'desc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tableAlias . '.created' => 'Created',
            $tableAlias . '.type'    => 'Type'
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
        if ($this->input->get('dl') && userHasPermission('admin:admin:logs:change:download')) {

            //  Get all items for the search, the view will iterate over the resultset
            $changelog = $oChangeLogModel->getAllRawQuery(null, null, $data);

            Helper::loadCsv($changelog, 'export-changelog-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $totalRows               = $oChangeLogModel->countAll($data);
            $this->data['changelog'] = $oChangeLogModel->getAll($page, $perPage, $data);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = Helper::searchObject(false, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
            $this->data['pagination'] = Helper::paginationObject($page, $perPage, $totalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin:admin:logs:change:download')) {

                //  Build the query string, so that the same search is applies
                $params              = array();
                $params['dl']        = true;
                $params['sortOn']    = $this->input->get('sortOn');
                $params['sortOrder'] = $this->input->get('sortOrder');
                $params['keywords']  = $this->input->get('keywords');

                $params = array_filter($params);
                $params = http_build_query($params);

                Helper::addHeaderButton('admin/admin/logs/changelog?' . $params, 'Download As CSV');
            }

            Helper::loadView('changelog/index');
        }
    }
}
