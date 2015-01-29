<?php

/**
 * This class renders the admin dashboard and the help section
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Admin extends \AdminController
{
   /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = parent::announce();

        // --------------------------------------------------------------------------

        //  Load the laguage file
        get_instance()->lang->load('admin_dashboard');

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = lang('dashboard_module_name');
        $d->icon = 'fa-home';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs          = array();
        $d->funcs['index'] = lang('dashboard_nav_index');

        //  Only show the help option if there are videos available
        get_instance()->load->model('admin_help_model');

        if (get_instance()->admin_help_model->count_all()) {
            $d->funcs['help'] = lang('dashboard_nav_help');
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * The admin homepage/dashbaord
     * @return void
     */
    public function index()
    {
        //  Page Data
        $this->data['page']->title = lang('dashboard_welcome_title');

        // --------------------------------------------------------------------------

        /**
         * Fetch recent admin changelog events
         * @TODO: widgitize this and use the API
         */

        $this->data['changelog'] = $this->admin_changelog_model->get_recent();

        // --------------------------------------------------------------------------

        //  Choose a hello phrase
        $this->load->helper('array');

        $phrases   = array();
        $phrases[] = 'Be awesome.';
        $phrases[] = 'You look nice!';
        $phrases[] = 'What are we doing today?';
        $phrases[] = active_user('first_name') ?  : 'Hey!';

        if (active_user('first_name')) {
            $phrases[] = 'Today is gonna be a good day, ' . active_user('first_name') . '.';
            $phrases[] = 'Hey, ' . active_user('first_name') . '!';
        } else {
            $phrases[] = 'Today is gonna be a good day.';
            $phrases[] = 'Hey!';
        }

        $this->data['phrase'] = random_element($phrases);

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.dashboard.min.js', true);

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/dashboard/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * The help section for admin
     * @return void
     */
    public function help()
    {
        //  Page Title
        $this->data['page']->title = lang('dashboard_help_title');

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['videos'] = $this->admin_help_model->get_all();

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/dashboard/help/overview', $this->data);
        $this->load->view('structure/footer', $this->data);
    }
}
