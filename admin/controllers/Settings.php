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

use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Components;
use Nails\Factory;

class Settings extends Base
{
    /**
     * @var array
     */
    protected $aFields;

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     *
     * @return \stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nails/module-admin');
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
     *
     * @return array
     */
    public static function permissions()
    {
        $aPermissions = parent::permissions();

        $aPermissions['admin:branding']   = 'Configure Admin Branding';
        $aPermissions['admin:whitelist']  = 'Configure Admn Whitelist';
        $aPermissions['site:customjscss'] = 'Configure Site Custom JS and CSS';
        $aPermissions['site:analytics']   = 'Configure Site analytics';
        $aPermissions['site:maintenance'] = 'Configure Maintenance Mode';
        $aPermissions['notifications']    = 'Configure Notifications';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Admin settings
     *
     * @return void
     */
    public function admin()
    {
        if (!userHasPermission('admin:admin:settings:admin:.*')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            $aSettings = [];

            if (userHasPermission('admin:admin:settings:admin:branding')) {
                $aSettings['primary_colour']   = $oInput->post('primary_colour');
                $aSettings['secondary_colour'] = $oInput->post('secondary_colour');
                $aSettings['highlight_colour'] = $oInput->post('highlight_colour');
            }

            if (userHasPermission('admin:admin:settings:admin:branding')) {
                $aSettings['whitelist'] = $this->prepareWhitelist($oInput->post('whitelist'));
            }

            if (!empty($aSettings)) {

                $oAppSettingModel = Factory::model('AppSetting');
                if ($oAppSettingModel->set($aSettings, 'admin')) {
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
     *
     * @return void
     */
    public function site()
    {
        if (!userHasPermission('admin:admin:settings:site:.*')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            $aSettings = [];

            if (userHasPermission('admin:admin:settings:site:customjscss')) {
                $aSettings['site_custom_js']     = $oInput->post('site_custom_js');
                $aSettings['site_custom_css']    = $oInput->post('site_custom_css');
                $aSettings['site_custom_markup'] = $oInput->post('site_custom_markup');
            }

            if (userHasPermission('admin:admin:settings:site:analytics')) {
                $aSettings['google_analytics_account'] = $oInput->post('google_analytics_account');
            }

            if (userHasPermission('admin:admin:settings:site:maintenance')) {
                $sRawIPs                                 = $oInput->post('maintenance_mode_whitelist');
                $aSettings['maintenance_mode_enabled']   = (bool) $oInput->post('maintenance_mode_enabled');
                $aSettings['maintenance_mode_whitelist'] = $this->prepareWhitelist($sRawIPs);
                $aSettings['maintenance_mode_title']     = $oInput->post('maintenance_mode_title');
                $aSettings['maintenance_mode_body']      = $oInput->post('maintenance_mode_body');
            }

            if (!empty($aSettings)) {

                $oAppSettingModel = Factory::model('AppSetting');
                if ($oAppSettingModel->set($aSettings, 'site')) {
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
     *
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

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            $aNotification = $oInput->post('notification');

            if (is_array($aNotification)) {

                $aSet = [];

                foreach ($aNotification as $sGroup => $aOptions) {

                    $aSet[$sGroup] = [];

                    foreach ($aOptions as $sKey => $sEmails) {

                        $aEmails = explode(',', $sEmails);
                        $aEmails = array_filter($aEmails);
                        $aEmails = array_unique($aEmails);

                        foreach ($aEmails as &$sEmail) {
                            $sEmail = trim($sEmail);
                            if (!valid_email($sEmail)) {
                                $error = '"<strong>' . $sEmail . '</strong>" is not a valid email.';
                                break 3;
                            }
                        }

                        $aSet[$sGroup][$sKey] = $aEmails;
                    }
                }

                if (empty($error)) {

                    foreach ($aSet as $sGroup => $aOptions) {
                        $oAppNotificationModel->set($aOptions, $sGroup);
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
     *
     * @param  string $sInput The input string
     *
     * @return array
     */
    protected function prepareWhitelist($sInput)
    {
        $sWhitelistRaw = $sInput;
        $sWhitelistRaw = str_replace("\n\r", "\n", $sWhitelistRaw);
        $aWhitelistRaw = explode("\n", $sWhitelistRaw);
        $aWhitelist    = [];

        foreach ($aWhitelistRaw as $sLine) {
            $aWhitelist = array_merge(explode(',', $sLine), $aWhitelist);
        }

        $aWhitelist = array_unique($aWhitelist);
        $aWhitelist = array_filter($aWhitelist);
        $aWhitelist = array_map('trim', $aWhitelist);
        $aWhitelist = array_values($aWhitelist);

        return $aWhitelist;
    }

    // --------------------------------------------------------------------------

    /**
     * Configure modules which have settings described in their composer.json/config.json file
     *
     * @return void
     */
    public function module()
    {
        $this->component('Module');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure skins which have settings described in their composer.json/config.json file
     *
     * @return void
     */
    public function skin()
    {
        $this->component('Skin');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure drivers which have settings described in their composer.json/config.json file
     *
     * @return void
     */
    public function driver()
    {
        $this->component('Driver');
    }

    // --------------------------------------------------------------------------

    /**
     * Configure components which have settings described in their composer.json/config.json file
     *
     * @param string $sType The type of component
     *
     * @return void
     */
    public function component($sType = 'component')
    {
        $oInput             = Factory::service('Input');
        $this->data['slug'] = $oInput->get('slug');

        $oComponent = Components::getBySlug($this->data['slug']);

        if (empty($oComponent->data->settings)) {
            show404();
        }

        //  Move all the settings which aren't already in fieldsets/groups into groups
        $this->data['fieldsets'] = [];
        $this->aFields           = [];
        $this->extractFieldsets($oComponent->slug, $oComponent->data->settings);
        $this->data['fieldsets'] = array_values($this->data['fieldsets']);

        if ($oInput->post()) {

            //  Validate
            $oFormValidation = Factory::service('FormValidation');
            $aRules          = [];

            foreach ($this->aFields as $oField) {

                $aFieldRule   = ['trim'];
                $aFieldRule[] = !empty($oField->required) ? 'required' : '';

                if (!empty($oField->validation_rules)) {
                    $aFieldRule = array_merge($aFieldRule, explode('|', $oField->validation_rules));
                }

                $aFieldRule = array_filter($aFieldRule);
                $aFieldRule = array_unique($aFieldRule);

                $aRules[] = [
                    'field' => $oField->key,
                    'label' => $oField->label,
                    'rules' => implode('|', $aFieldRule),
                ];
            }

            $oFormValidation->set_rules($aRules);

            if ($oFormValidation->run()) {

                $aSettings          = [];
                $aSettingsEncrypted = [];

                foreach ($this->aFields as $oField) {

                    //  @todo respect data types

                    //  Encrypted or not?
                    if (!empty($oField->encrypted)) {
                        $aSettingsEncrypted[$oField->key] = $oInput->post($oField->key);
                    } else {
                        $aSettings[$oField->key] = $oInput->post($oField->key);
                    }
                }

                //  Begin transaction
                $oAppSettingModel = Factory::model('AppSetting');
                $oDb              = Factory::service('Database');
                $oDb->trans_begin();

                //  Normal settings
                if (!$oAppSettingModel->set($aSettings, $oComponent->slug)) {
                    $sError = $oAppSettingModel->lastError();
                }

                //  Encrypted settings
                if (!$oAppSettingModel->set($aSettingsEncrypted, $oComponent->slug, null, true)) {
                    $sError = $oAppSettingModel->lastError();
                }

                if (empty($sError)) {
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

    // --------------------------------------------------------------------------

    /**
     * Recursively gets all the settings from the settings array
     *
     * @param string $sComponentSlug The component's slug
     * @param array  $aSettings      The array of fieldsets and/or settings
     * @param int    $fieldSetIndex  The index of the fieldset
     */
    protected function extractFieldsets($sComponentSlug, $aSettings, $fieldSetIndex = 0)
    {
        foreach ($aSettings as $oSetting) {

            //  If the object contains a `fields` property then consider this a fieldset and inception
            if (isset($oSetting->fields)) {

                $fieldSetIndex++;

                if (!isset($this->data['fieldsets'][$fieldSetIndex])) {
                    $this->data['fieldsets'][$fieldSetIndex] = [
                        'legend' => $oSetting->legend,
                        'fields' => [],
                    ];
                }

                $this->extractFieldsets($sComponentSlug, $oSetting->fields, $fieldSetIndex);

            } else {

                $sValue = appSetting($oSetting->key, $sComponentSlug);
                if (!is_null($sValue)) {
                    $oSetting->default = $sValue;
                }

                if (!isset($this->data['fieldsets'][$fieldSetIndex])) {
                    $this->data['fieldsets'][$fieldSetIndex] = [
                        'legend' => '',
                        'fields' => [],
                    ];
                }

                $this->data['fieldsets'][$fieldSetIndex]['fields'][] = $oSetting;
                $this->aFields[]                                     = $oSetting;
            }
        }
    }
}
