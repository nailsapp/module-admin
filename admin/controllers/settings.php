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
        $navGroup
            ->addMethod('Admin', 'admin')
            ->addMethod('Site', 'site');

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Admin settings
     * @return void
     */
    public function admin()
    {
        //  Set method info
        $this->data['page']->title = 'Admin';

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_admin_update_' . $method)) {

                $this->{'_admin_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'admin', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/admin', $this->data);
        $this->load->view('structure/footer', $this->data);
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

            $this->data['success'] = '<strong>Success!</strong> Admin branding settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin branding settings.';
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
        $whitelist     = array();

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

            $this->data['success'] = '<strong>Success!</strong> Admin whitelist settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin whitelist settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Site settings
     * @return void
     */
    public function site()
    {
        //  Set method info
        $this->data['page']->title = 'Site';

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_site_update_' . $method)) {

                $this->{'_site_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'app', true);

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.site.settings.min.js', true);
        $this->asset->inline('<script>_nails_settings = new NAILS_Admin_Site_Settings();</script>');

        $this->load->library('auth/social_signon');
        $this->data['providers'] = $this->social_signon->get_providers();

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/site', $this->data);
        $this->load->view('structure/footer', $this->data);
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

            $this->data['success'] = '<strong>Success!</strong> Site settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Auth settings
     * @return void
     */
    protected function _site_update_auth()
    {
        $this->load->library('auth/social_signon');
        $providers = $this->social_signon->get_providers();

        // --------------------------------------------------------------------------

        //  Prepare update
        $settings            = array();
        $settings_encrypted = array();

        $settings['user_registration_enabled'] = $this->input->post('user_registration_enabled');

        //  Disable social signon, if any providers are proeprly enabled it'll turn itself on again.
        $settings['auth_social_signon_enabled'] = false;

        foreach ($providers as $provider) {

            $settings['auth_social_signon_' . $provider['slug'] . '_enabled'] = (bool) $this->input->post('auth_social_signon_' . $provider['slug'] . '_enabled');

            if ($settings['auth_social_signon_' . $provider['slug'] . '_enabled']) {

                //  null out each key
                if ($provider['fields']) {

                    foreach ($provider['fields'] as $key => $label) {

                        if (is_array($label) && !isset($label['label'])) {

                            foreach ($label as $key1 => $label1) {

                                $value = $this->input->post('auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1);

                                if (!empty($label1['required']) && empty($value)) {

                                    $error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label1['label'] . '".';
                                    break 3;

                                }

                                if ( empty($label1['encrypted'])) {

                                    $settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $value;

                                } else {

                                    $settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $value;
                                }
                            }

                        } else {

                            $value = $this->input->post('auth_social_signon_' . $provider['slug'] . '_' . $key);

                            if (!empty($label['required']) && empty($value)) {

                                $error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label['label'] . '".';
                                break 2;
                            }

                            if ( empty($label['encrypted'])) {

                                $settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = $value;

                            } else {

                                $settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key] = $value;
                            }
                        }
                    }
                }

                //  Turn on social signon
                $settings['auth_social_signon_enabled'] = true;

            } else {

                //  null out each key
                if ($provider['fields']) {

                    foreach ($provider['fields'] as $key => $label) {

                        /**
                         * Secondary conditional detects an actual array fo fields rather than
                         * just the label/required array. Design could probably be improved...
                         **/

                        if (is_array($label) && !isset($label['label'])) {

                            foreach ($label as $key1 => $label1) {

                                $settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = null;
                            }

                        } else {

                            $settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = null;
                        }
                    }
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Save
        if (empty($error)) {

            $this->db->trans_begin();
            $rollback = false;

            if (!empty($settings)) {

                if (!$this->app_setting_model->set($settings, 'app')) {

                    $error    = $this->app_setting_model->last_error();
                    $rollback = true;
                }
            }

            if (!empty($settings_encrypted)) {

                if (!$this->app_setting_model->set($settings_encrypted, 'app', null, true)) {

                    $error    = $this->app_setting_model->last_error();
                    $rollback = true;
                }
            }

            if ($rollback) {

                $this->db->trans_rollback();
                $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $error;

            } else {

                $this->db->trans_commit();
                $this->data['success'] = '<strong>Success!</strong> Authentication settings were saved.';

            }

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $error;
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

            $this->data['success'] = '<strong>Success!</strong> Maintenance settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving maintenance settings.';
        }
    }
}
