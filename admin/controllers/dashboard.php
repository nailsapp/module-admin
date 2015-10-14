<?php

/**
 * This class renders the admin dashboard
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\Base;

class Dashboard extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Dashboard', 'fa-home');
        $navGroup->addAction('Site Overview');

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * The admin homepage/dashboard
     * @return void
     */
    public function index()
    {
        //  Page Data
        $this->data['page']->title = 'Welcome';

        // --------------------------------------------------------------------------

        //  Choose a hello phrase
        \Nails\Factory::helper('array');

        $phrases   = array();
        $phrases[] = 'Be awesome.';
        $phrases[] = 'You look nice!';
        $phrases[] = 'What are we doing today?';

        if (activeUser('first_name')) {

            $phrases[] = 'Today is gonna be a good day, ' . activeUser('first_name') . '.';
            $phrases[] = 'Hey, ' . activeUser('first_name') . '!';

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
