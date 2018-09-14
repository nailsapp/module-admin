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

use Nails\Factory;
use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;

class Dashboard extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nails/module-admin');
        $oNavGroup->setLabel('Dashboard');
        $oNavGroup->setIcon('fa-home');
        $oNavGroup->addAction('Site Overview');

        return $oNavGroup;
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
        $oAsset = Factory::service('Asset');
        $oAsset->load('nails.admin.dashboard.min.js', true);

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('index');
    }
}
