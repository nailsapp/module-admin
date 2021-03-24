<?php

namespace Nails\Admin\Admin;

use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Factory\Component;
use Nails\Common\Factory\Model\Field;
use Nails\Common\Service\AppSetting;
use Nails\Common\Service\FormValidation;
use Nails\Common\Service\Input;
use Nails\Common\Service\Session;
use Nails\Components;
use Nails\Factory;
use Nails\Common\Interfaces;

/**
 * Class UnifiedSettings
 *
 * @package Nails\Admin\Admin
 */
class UnifiedSettings extends Base
{
    /**
     * @var \stdClass[]
     */
    protected static $aSettings = [];

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     *
     * @return \stdClass
     */
    public static function announce()
    {
        /** @var Nav $oNav */
        $oNav = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNav
            ->setLabel('Settings')
            ->setIcon('fa-wrench');

        static::discoverSettings();

        foreach (static::$aSettings as $sSlug => $oSetting) {

            //  @todo (Pablo 2021-03-24) - check for permissions

            $oNav->addAction(
                $oSetting->label,
                'index?setting=' . $sSlug
            );
        }

        return $oNav;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     *
     * @return array
     */
    public static function permissions(): array
    {
        $aPermissions = parent::permissions();

        //  @todo (Pablo 2021-03-24) - offer permissions for each setting

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Discovers component settings classes
     *
     * @throws NailsException
     */
    protected static function discoverSettings(): void
    {
        if (empty(static::$aSettings)) {
            foreach (Components::available() as $oComponent) {

                $aClasses = $oComponent
                    ->findClasses('Settings')
                    ->whichImplement(Interfaces\Component\Settings::class);

                foreach ($aClasses as $sClass) {

                    /** @var Interfaces\Component\Settings $oClass */
                    $oClass = new $sClass();
                    $sSlug  = md5($sClass);

                    static::$aSettings[$sSlug] = (object) [
                        'label'     => $oClass->getLabel(),
                        'slug'      => md5($sClass),
                        'instance'  => $oClass,
                        'component' => $oComponent,
                    ];
                }
            }

            arraySortMulti(static::$aSettings, 'label');
        }
    }

    // --------------------------------------------------------------------------

    public function index()
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var FormValidation $oFormValidation */
        $oFormValidation = Factory::service('FormValidation');
        /** @var Session $oSession */
        $oSession = Factory::service('Session');

        static::discoverSettings();

        $oSetting = static::$aSettings[$oInput->get('setting')] ?? null;
        if (empty($oSetting)) {
            show404();

        } elseif ($oInput->post()) {
            try {

                $oFormValidation
                    ->buildValidator(array_combine(
                        array_map(function (Field $oField) {
                            return $oField->getKey();
                        }, $oSetting->instance->get()),
                        array_map(function (Field $oField) {
                            return $oField->getValidation();
                        }, $oSetting->instance->get())
                    ))
                    ->run();

                /** @var Field $aField */
                foreach ($oSetting->instance->get() as $oField) {
                    setAppSetting(
                        $oField->getKey(),
                        $oSetting->component->slug,
                        $oInput->post($oField->getKey()),
                        $oField->isEncrypted()
                    );
                }

                $oSession->setFlashData('success', sprintf(
                    '%s settings saved',
                    $oSetting->label
                ));

                redirect($this->compileFormUrl($oSetting));

            } catch (ValidationException $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        $this->data['page']->title = 'Manage Settings &rsaquo; ' . $oSetting->label;
        $this->data['sFormUrl']    = $this->compileFormUrl($oSetting);
        $this->data['oComponent']  = $oSetting->component;
        $this->data['oSetting']    = $oSetting->instance;
        $this->data['aFieldSets']  = $this->compileFieldSets(
            $this->getSettingsWithDefaults(
                $oSetting->instance,
                $oSetting->component
            )
        );

        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    protected function compileFormUrl(\stdClass $oSetting)
    {
        return siteUrl(uri_String() . '?setting=' . $oSetting->slug);
    }

    // --------------------------------------------------------------------------

    /**
     * Compiles the fields into their relevant fieldsets
     *
     * @param array $aSettings
     *
     * @return array
     */
    protected function compileFieldSets(array $aSettings): array
    {
        $aFieldSets = [];

        /** @var Field $oSetting */
        foreach ($aSettings as $oSetting) {

            $sFieldSet = $oSetting->getFieldset();
            if (!array_key_exists($sFieldSet, $aFieldSets)) {
                $aFieldSets[$sFieldSet] = [];
            }

            $aFieldSets[$sFieldSet][] = $oSetting;
        }

        return $aFieldSets;
    }

    // --------------------------------------------------------------------------

    /**
     * @param Interfaces\Component\Settings $oSettings
     * @param Component                     $oComponent
     *
     * @return array
     * @throws FactoryException
     */
    protected function getSettingsWithDefaults(Interfaces\Component\Settings $oSettings, Component $oComponent): array
    {
        $aSettings = $oSettings->get();
        foreach ($aSettings as $oSetting) {
            $oSetting->setDefault(appSetting($oSetting->getKey(), $oComponent->slug));
        }

        return $aSettings;
    }
}
