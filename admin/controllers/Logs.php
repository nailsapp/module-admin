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

use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Factory;

class Logs extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nails/module-admin');
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
        $aPermissions = parent::permissions();

        $aPermissions['site:browse']     = 'Can browse site logs';
        $aPermissions['event:browse']    = 'Can browse event logs';
        $aPermissions['event:download']  = 'Can download event logs';
        $aPermissions['change:browse']   = 'Can browse change logs';
        $aPermissions['change:download'] = 'Can download change logs';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Route site log pages
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function site()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        Factory::helper('string');
        $oUri    = Factory::service('Uri');
        $sMethod = $oUri->segment(5) ? $oUri->segment(5) : 'index';
        $sMethod = 'site' . underscoreToCamelcase(strtolower($sMethod), false);

        if (method_exists($this, $sMethod)) {
            $this->{$sMethod}();
        } else {
            show404('', true);
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

    /**
     * View a single log file
     */
    protected function siteView()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oUri                      = Factory::service('Uri');
        $sFile                     = $oUri->segment(6);
        $this->data['page']->title = 'Browse Logs &rsaquo; ' . $sFile;

        $oSiteLogModel      = Factory::model('SiteLog', 'nails/module-admin');
        $this->data['logs'] = $oSiteLogModel->readLog($sFile);

        if (!$this->data['logs']) {
            show404();
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

        $sTableAlias = $this->event->getTableAlias();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $oInput     = Factory::service('Input');
        $iPage      = (int) $oInput->get('page') ?: 0;
        $iPerPage   = (int) $oInput->get('perPage') ?: 50;
        $sSortOn    = $oInput->get('sortOn') ?: $sTableAlias . '.created';
        $sSortOrder = $oInput->get('sortOrder') ?: 'desc';
        $sKeywords  = $oInput->get('keywords') ?: '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $aSortColumns = [
            $sTableAlias . '.created' => 'Created',
            $sTableAlias . '.type'    => 'Type',
        ];

        // --------------------------------------------------------------------------

        //  Define the $aData variable for the queries
        $aData = [
            'sort'     => [
                [$sSortOn, $sSortOrder],
            ],
            'keywords' => $sKeywords,
        ];

        //  Are we downloading? Or viewing?
        if ($oInput->get('dl') && userHasPermission('admin:admin:logs:event:download')) {

            //  Get all items for the search, the view will iterate over the resultset
            $oEvents = $this->event->getAllRawQuery(null, null, $aData);

            Helper::loadCsv($oEvents, 'export-events-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $iTotalRows           = $this->event->countAll($aData);
            $this->data['events'] = $this->event->getAll($iPage, $iPerPage, $aData);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = Helper::searchObject(true, $aSortColumns, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
            $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin:admin:logs:event:download')) {

                //  Build the query string, so that the same search is applies
                $oInput               = Factory::service('Input');
                $aParams              = [];
                $aParams['dl']        = true;
                $aParams['sortOn']    = $oInput->get('sortOn');
                $aParams['sortOrder'] = $oInput->get('sortOrder');
                $aParams['keywords']  = $oInput->get('keywords');

                $aParams = array_filter($aParams);
                $aParams = http_build_query($aParams);

                Helper::addHeaderButton('admin/admin/logs/event?' . $aParams, 'Download As CSV');
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

        $oInput          = Factory::service('Input');
        $oChangeLogModel = Factory::model('ChangeLog', 'nails/module-admin');
        $sTableAlias     = $oChangeLogModel->getTableAlias();

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $iPage      = (int) $oInput->get('page') ?: 0;
        $iPerPage   = (int) $oInput->get('perPage') ?: 50;
        $sSortOn    = $oInput->get('sortOn') ?: $sTableAlias . '.created';
        $sSortOrder = $oInput->get('sortOrder') ?: 'desc';
        $sKeywords  = $oInput->get('keywords') ?: '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $aSortColumns = [
            $sTableAlias . '.created' => 'Created',
            $sTableAlias . '.type'    => 'Type',
        ];

        // --------------------------------------------------------------------------

        //  Define the $aData variable for the queries
        $aData = [
            'sort'     => [
                [$sSortOn, $sSortOrder],
            ],
            'keywords' => $sKeywords,
        ];

        //  Are we downloading? Or viewing?
        if ($oInput->get('dl') && userHasPermission('admin:admin:logs:change:download')) {

            //  Get all items for the search, the view will iterate over the resultset
            $oChangelog = $oChangeLogModel->getAllRawQuery(null, null, $aData);

            Helper::loadCsv($oChangelog, 'export-changelog-' . toUserDatetime(null, 'Y-m-d_h-i-s') . '.csv');

        } else {

            //  Get the items for the page
            $iTotalRows              = $oChangeLogModel->countAll($aData);
            $this->data['changelog'] = $oChangeLogModel->getAll($iPage, $iPerPage, $aData);

            //  Set Search and Pagination objects for the view
            $this->data['search']     = Helper::searchObject(false, $aSortColumns, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
            $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);

            //  Add the header button for downloading
            if (userHasPermission('admin:admin:logs:change:download')) {

                //  Build the query string, so that the same search is applies
                $aParams              = [];
                $aParams['dl']        = true;
                $aParams['sortOn']    = $oInput->get('sortOn');
                $aParams['sortOrder'] = $oInput->get('sortOrder');
                $aParams['keywords']  = $oInput->get('keywords');

                $aParams = array_filter($aParams);
                $aParams = http_build_query($aParams);

                Helper::addHeaderButton('admin/admin/logs/changelog?' . $aParams, 'Download As CSV');
            }

            Helper::loadView('changelog/index');
        }
    }
}
