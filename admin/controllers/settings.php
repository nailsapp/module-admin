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
        $navGroup = new \Nails\Admin\Nav('Settings');

        if (userHasPermission('admin:admin:settings:admin:.*')) {

            $navGroup->addMethod('Admin', 'admin');
        }

        if (userHasPermission('admin:admin:settings:site:.*')) {

            $navGroup->addMethod('Site', 'site');
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

        $permissions['admin:branding']  = 'Update Admin Branding';
        $permissions['admin:whitelist'] = 'Update Admn Whitelist';
        $permissions['site:analytics']  = 'Set site analytics';

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

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_admin_update_' . $method)) {

                $this->{'_admin_update_' . $method}();

            } else {

                $this->data['error'] = 'I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'admin', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Admin';

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('admin');
    }

    // --------------------------------------------------------------------------

    /**
     * Set Admin branding settings
     * @return void
     */
    protected function _admin_update_branding()
    {
        //  Prepare update
        $settings                     = array();
        $settings['primary_colour']   = $this->input->post('primary_colour');
        $settings['secondary_colour'] = $this->input->post('secondary_colour');
        $settings['highlight_colour'] = $this->input->post('highlight_colour');

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'admin')) {

            $this->data['success'] = 'Admin branding settings have been saved.';

        } else {

            $this->data['error'] = 'There was a problem saving admin branding settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Admin IP whitelist settings
     * @return void
     */
    protected function _admin_update_whitelist()
    {
        //  Prepare the whitelist
        $whitelistRaw = $this->input->post('whitelist');
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

        //  Prepare update
        $settings              = array();
        $settings['whitelist'] = $whitelist;

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'admin')) {

            $this->data['success'] = 'Admin whitelist settings have been saved.';

        } else {

            $this->data['error'] = 'There was a problem saving admin whitelist settings.';
        }
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

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_site_update_' . $method)) {

                $this->{'_site_update_' . $method}();

            } else {

                $this->data['error'] = 'I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'app', true);

        // --------------------------------------------------------------------------

        //  Set page title
        $this->data['page']->title = 'Settings &rsaquo; Site';

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('site');
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Analytics settings
     * @return void
     */
    protected function _site_update_analytics()
    {
        //  Prepare update
        $settings                             = array();
        $settings['google_analytics_account'] = $this->input->post('google_analytics_account');

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'app')) {

            $this->data['success'] = 'Site settings have been saved.';

        } else {

            $this->data['error'] = 'There was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Maintenance settings
     * @return void
     */
    protected function _site_update_maintenance()
    {
        //  Prepare the whitelist
        $whitelistRaw = $this->input->post('maintenance_mode_whitelist');
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

        //  Prepare update
        $settings                               = array();
        $settings['maintenance_mode_enabled']   = (bool) $this->input->post('maintenance_mode_enabled');
        $settings['maintenance_mode_whitelist'] = $whitelist;

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'app')) {

            $this->data['success'] = 'Maintenance settings have been saved.';

        } else {

            $this->data['error'] = 'There was a problem saving maintenance settings.';
        }
    }
}
