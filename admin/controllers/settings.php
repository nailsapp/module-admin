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

class Settings extends \AdminController
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Settings', 'fa-wrench');

        if (userHasPermission('admin:admin:settings:admin:.*')) {

            $navGroup->addAction('Admin', 'admin');
        }

        if (userHasPermission('admin:admin:settings:site:.*')) {

            $navGroup->addAction('Site', 'site');
        }

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['admin:branding']   = 'Configure Admin Branding';
        $permissions['admin:whitelist']  = 'Configure Admn Whitelist';
        $permissions['site:analytics']   = 'Configure Site analytics';
        $permissions['site:maintenance'] = 'Configure Maintenance Mode';

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
        $this->data['settings'] = app_setting(null, 'admin', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Admin';

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.settings.min.js', 'NAILS');

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('admin');
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
        $this->data['settings'] = app_setting(null, 'app', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Site';

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.settings.min.js', 'NAILS');
        $this->asset->load('nails.admin.admin.settings.min.js', 'NAILS');

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('site');
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
