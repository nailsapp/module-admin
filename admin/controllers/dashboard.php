<?php

/**
 * This class renders the admin dashboard
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Dashboard extends \AdminController
{
   /**
     * Announces this controller's details
     * @return stdClass
     */
    public static function announce()
    {
        $d     = parent::announce();
        $d[''] = array('Dashboard', 'Site Overview');
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
        \Nails\Admin\Helper::loadView('index');
    }
}
