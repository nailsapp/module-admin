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
        $permissions['site:customjscss']   = 'Configure Site Custom JS and CSS';
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

                $oAppSettingModel = Factory::model('AppSetting');
                if ($oAppSettingModel->set($settings, 'admin')) {

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
        $oAsset = Factory::service('Asset');
        $oAsset->load('nails.admin.settings.min.js', 'NAILS');

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

            if (userHasPermission('admin:admin:settings:site:customjscss')) {

                $settings['site_custom_js']  = $this->input->post('site_custom_js');
                $settings['site_custom_css'] = $this->input->post('site_custom_css');
            }

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

                $oAppSettingModel = Factory::model('AppSetting');
                if ($oAppSettingModel->set($settings, 'site')) {

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
        $oAsset = Factory::service('Asset');
        $oAsset->load('nails.admin.settings.min.js', 'NAILS');
        $oAsset->load('nails.admin.admin.settings.min.js', 'NAILS');

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

    // --------------------------------------------------------------------------

    /**
     * Configure modules which have settings described in their composer.json/config.json file
     * @return void
     */
    public function module()
    {
        $this->component('Module');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure skins which have settings described in their composer.json/config.json file
     * @return void
     */
    public function skin()
    {
        $this->component('Skin');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure drivers which have settings described in their composer.json/config.json file
     * @return void
     */
    public function driver()
    {
        $this->component('Driver');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure components which have settings described in their composer.json/config.json file
     * @return void
     */
    public function component($sType = 'component')
    {
        $this->data['slug'] = $this->input->get('slug');

        $oComponent = _NAILS_GET_COMPONENTS_BY_SLUG($this->data['slug']);

        if (empty($oComponent->data->settings)) {
            show_404();
        }

        //  Move all the settings which aren't already in fieldsets/groups into groups
        $this->data['fieldsets'] = array();
        $this->fields            = array();
        $this->extractFieldsets($oComponent->slug, $oComponent->data->settings);
        $this->data['fieldsets'] = array_values($this->data['fieldsets']);

        if ($this->input->post()) {

            //  Validate
            $oFormValidation = Factory::service('FormValidation');
            $aRules          = array();

            foreach ($this->fields as $oField) {

                $aFieldRule   = array();
                $aFieldRule[] = !empty($oField->required) ? 'required' : '';

                if (!empty($oField->validation_rules)) {
                    $aFieldRule = array_merge($aFieldRule, explode('|', $oField->validation_rules));
                }

                $aFieldRule = array_filter($aFieldRule);
                $aFieldRule = array_unique($aFieldRule);

                $aRules[] = array(
                    'field' => $oField->key,
                    'label' => $oField->label,
                    'rules' => implode('|', $aFieldRule)
                );
            }

            $oFormValidation->set_rules($aRules);

            if ($oFormValidation->run()) {

                $aSettings          = array();
                $aSettingsEncrypted = array();

                foreach ($this->fields as $oField) {

                    //  @todo respect data types

                    //  Encrypted or not?
                    if (!empty($oField->encrypted)) {

                        $aSettingsEncrypted[$oField->key] = $this->input->post($oField->key);

                    } else {

                        $aSettings[$oField->key] = $this->input->post($oField->key);
                    }
                }

                //  Begin transaction
                $oAppSettingModel = Factory::model('AppSetting');
                $oDb              = Factory::service('Database');
                $oDb->trans_begin();
                $bRollback = false;

                //  Normal settings
                if (!$oAppSettingModel->set($aSettings, $oComponent->slug)) {

                    $sError    = $oAppSettingModel->lastError();
                    $bRollback = true;
                }

                //  Encrypted settings
                if (!$oAppSettingModel->set($aSettingsEncrypted, $oComponent->slug, null, true)) {

                    $sError    = $oAppSettingModel->lastError();
                    $bRollback = true;
                }

                if (empty($bRollback)) {

                    $oDb->trans_commit();
                    $this->data['success'] = $sType . ' settings were saved.';

                } else {

                    $oDb->trans_rollback();
                    $this->data['error'] = 'There was a problem saving shop settings. ' . $sError;
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Get all the settings for this component
        $this->data['settings'] = appSetting(null, $oComponent->slug);

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Configure ' . $sType . ' &rsaquo; ' . $oComponent->name;

        // --------------------------------------------------------------------------

        Helper::loadView('component');
    }


    /**
     * Recursively gets all the settings from the settings array
     * @param  array $aSettings The array of fieldsets and/or settings
     * @return array
     */
    protected function extractFieldsets($sComponentSlug, $aSettings, $fieldSetIndex = 0)
    {
        foreach ($aSettings as $oSetting) {

            //  If the object contains a `fields` property then consider this a fieldset and inception
            if (isset($oSetting->fields)) {

                $fieldSetIndex++;

                if (!isset($this->data['fieldsets'][$fieldSetIndex])) {
                    $this->data['fieldsets'][$fieldSetIndex] = array(
                        'legend' => $oSetting->legend,
                        'fields' => array()
                    );
                }

                $this->extractFieldsets($sComponentSlug, $oSetting->fields, $fieldSetIndex);

            } else {

                $sValue = appSetting($oSetting->key, $sComponentSlug);
                if (!is_null($sValue)) {
                    $oSetting->default = $sValue;
                }

                if (!isset($this->data['fieldsets'][$fieldSetIndex])) {
                    $this->data['fieldsets'][$fieldSetIndex] = array(
                        'legend' => '',
                        'fields' => array()
                    );
                }

                $this->data['fieldsets'][$fieldSetIndex]['fields'][] = $oSetting;
                $this->fields[] = $oSetting;
            }
        }
    }
}
