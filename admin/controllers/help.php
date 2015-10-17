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
        get_instance()->load->model('admin_help_model');

        if (userHasPermission('admin:admin:help:view') && get_instance()->admin_help_model->count_all()) {

            $navGroup = new \Nails\Admin\Nav('Dashboard', 'fa-home');
            $navGroup->addAction('Help Videos');

            return $navGroup;
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
        $this->data['videos'] = $this->admin_help_model->get_all();

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('index');
    }
}
