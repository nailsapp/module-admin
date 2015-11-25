<?php

/**
 * This class handles setting of notification recipients
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

class Settings extends Base
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
        $oNavGroup->setLabel('Settings');
        $oNavGroup->setIcon('fa-wrench');

        if (userHasPermission('admin:admin:settings:admin:.*')) {

            $oNavGroup->addAction('Admin', 'admin');
        }

        if (userHasPermission('admin:admin:settings:site:.*')) {

            $oNavGroup->addAction('Site', 'site');
        }

        if (userHasPermission('admin:admin:settings:notifications')) {

            $oNavGroup->addAction('Notifications', 'notifications');
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

        $permissions['admin:branding']     = 'Configure Admin Branding';
        $permissions['admin:whitelist']    = 'Configure Admn Whitelist';
        $permissions['site:analytics']     = 'Configure Site analytics';
        $permissions['site:maintenance']   = 'Configure Maintenance Mode';
        $permissions['notifications']      = 'Configure Notifications';

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Admin settings
     * @return void
     */
    public function admin()
    {
        if (!userHasPermission('admin:admin:settings:admin:.*')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $settings = array();

            if (userHasPermission('admin:admin:settings:admin:branding')) {

                $settings['primary_colour']   = $this->input->post('primary_colour');
                $settings['secondary_colour'] = $this->input->post('secondary_colour');
                $settings['highlight_colour'] = $this->input->post('highlight_colour');
            }

            if (userHasPermission('admin:admin:settings:admin:branding')) {

                $settings['whitelist'] = $this->prepareWhitelist($this->input->post('whitelist'));
            }

            if (!empty($settings)) {

                if ($this->app_setting_model->set($settings, 'admin')) {

                    $this->data['success'] = 'Admin settings have been saved.';

                } else {

                    $this->data['error'] = 'There was a problem saving admin settings.';
                }

            } else {

                $this->data['message'] = 'No settings to save.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = appSetting(null, 'admin', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Admin';

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.settings.min.js', 'NAILS');

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('admin');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Site settings
     * @return void
     */
    public function site()
    {
        if (!userHasPermission('admin:admin:settings:site:.*')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $settings = array();

            if (userHasPermission('admin:admin:settings:site:analytics')) {

                $settings['google_analytics_account'] = $this->input->post('google_analytics_account');
            }

            if (userHasPermission('admin:admin:settings:site:maintenance')) {

                $rawIPs = $this->input->post('maintenance_mode_whitelist');
                $settings['maintenance_mode_enabled']   = (bool) $this->input->post('maintenance_mode_enabled');
                $settings['maintenance_mode_whitelist'] = $this->prepareWhitelist($rawIPs);
                $settings['maintenance_mode_title']     = $this->input->post('maintenance_mode_title');
                $settings['maintenance_mode_body']      = $this->input->post('maintenance_mode_body');
            }

            if (!empty($settings)) {

                if ($this->app_setting_model->set($settings, 'site')) {

                    $this->data['success'] = 'Site settings have been saved.';

                } else {

                    $this->data['error'] = 'There was a problem saving site settings.';
                }

            } else {

                $this->data['message'] = 'No settings to save.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = appSetting(null, 'app', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Site';

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.settings.min.js', 'NAILS');
        $this->asset->load('nails.admin.admin.settings.min.js', 'NAILS');

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('site');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage notifications
     * @return void
     */
    public function notifications()
    {
        if (!userHasPermission('admin:admin:settings:notifications')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oAppNotificationModel = Factory::model('AppNotification');

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = 'Manage Notifications';

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $notification = $this->input->post('notification');

            if (is_array($notification)) {

                $set = array();

                foreach ($notification as $grouping => $options) {

                    $set[$grouping] = array();

                    foreach ($options as $key => $emails) {

                        $emails = explode(',', $emails);
                        $emails = array_filter($emails);
                        $emails = array_unique($emails);

                        foreach ($emails as &$email) {

                            $email = trim($email) ;

                            if (!valid_email($email)) {

                                $error = '"<strong>' . $email . '</strong>" is not a valid email.';
                                break 3;
                            }
                        }

                        $set[$grouping][$key] = $emails;
                    }
                }

                if (empty($error)) {

                    foreach ($set as $grouping => $options) {

                        $oAppNotificationModel->set($options, $grouping);
                    }

                    $this->data['success'] = 'Notifications were updated successfully.';

                } else {

                    $this->data['error'] = $error;
                }
            }
        }

        // --------------------------------------------------------------------------

        $this->data['notifications'] = $oAppNotificationModel->getDefinitions();

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('notifications');
    }

    // --------------------------------------------------------------------------

    /**
     * Takes a multi line input and converts it into an array
     * @param  string $input The input string
     * @return array
     */
    protected function prepareWhitelist($input)
    {
        $whitelistRaw = $input;
        $whitelistRaw = str_replace("\n\r", "\n", $whitelistRaw);
        $whitelistRaw = explode("\n", $whitelistRaw);
        $whitelist    = array();

        foreach ($whitelistRaw as $line) {

            $whitelist = array_merge(explode(',', $line), $whitelist);
        }

        $whitelist = array_unique($whitelist);
        $whitelist = array_filter($whitelist);
        $whitelist = array_map('trim', $whitelist);
        $whitelist = array_values($whitelist);

        return $whitelist;
    }
}
