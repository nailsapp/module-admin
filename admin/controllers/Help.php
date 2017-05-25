<?php

/**
 * This class renders the admin help section
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

class Help extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $oHelpModel = Factory::model('Help', 'nailsapp/module-admin');

        if (userHasPermission('admin:admin:help:view') && $oHelpModel->countAll()) {

            $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
            $oNavGroup->setLabel('Dashboard');
            $oNavGroup->setIcon('fa-home');
            $oNavGroup->addAction('Help Videos');

            return $oNavGroup;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['view'] = 'Can view help videos';

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the admin help pagge
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:admin:help:view')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = 'Help Videos';

        // --------------------------------------------------------------------------

        //  Get data
        $oHelpModel   = Factory::model('Help', 'nailsapp/module-admin');
        $sTableAlias = $oHelpModel->getTableAlias();

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $sTableAlias . '.label';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'asc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $sTableAlias . '.label'    => 'Label',
            $sTableAlias . '.duration' => 'Duration',
            $sTableAlias . '.created'  => 'Added',
            $sTableAlias . '.modified' => 'Modified'
        );

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = array(
            'sort' => array(
                array($sortOn, $sortOrder)
            ),
            'keywords' => $keywords
        );

        //  Get the items for the page
        $totalRows            = $oHelpModel->countAll($data);
        $this->data['videos'] = $oHelpModel->getAll($page, $perPage, $data);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
        $this->data['pagination'] = Helper::paginationObject($page, $perPage, $totalRows);

        // --------------------------------------------------------------------------

        $oAsset = Factory::service('Asset');
        $oAsset->inline('$(\'a.video-button\').fancybox({ type : \'iframe\' });', 'JS');

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('index');
    }
}
