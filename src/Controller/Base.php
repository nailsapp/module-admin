<?php

/**
 * This class is the base class of all Admin controllers, it defines some basic
 * methods which should exist.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Events;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Asset;
use Nails\Common\Service\Event;
use Nails\Components;
use Nails\Config;
use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Allow the app to add functionality, if needed
 */
if (class_exists('\App\Admin\Controller\Base')) {
    abstract class BaseMiddle extends \App\Admin\Controller\Base
    {
    }
} else {
    abstract class BaseMiddle
    {
        public function __construct()
        {
        }
    }
}

// --------------------------------------------------------------------------

/**
 * Class Base
 *
 * @package Nails\Admin\Controller
 */
abstract class Base extends BaseMiddle
{
    public $data;

    /**
     * Construct the controller, load all the admin assets, etc
     */
    public function __construct()
    {
        parent::__construct();

        //  Setup Events
        /** @var Event $oEventService */
        $oEventService = Factory::service('Event');

        //  Call the ADMIN:STARTUP event, admin is constructing
        $oEventService->trigger(Events::ADMIN_STARTUP, Events::getEventNamespace());

        // --------------------------------------------------------------------------

        //  Provide access to the main controller's data property
        $this->data =& getControllerData();

        // --------------------------------------------------------------------------

        $this
            ->loadConfigs()
            ->loadHelpers()
            ->loadLanguages();

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->clear()
            ->compileGlobalData();

        //  @todo (Pablo - 2017-06-08) - Try and reduce the number of things being loaded, or theme it
        $this
            ->loadLibraries()
            ->loadJs()
            ->loadCss()
            ->autoLoad();

        // --------------------------------------------------------------------------

        \Nails\Common\Controller\Base::populateUserFeedback($this->data);

        // --------------------------------------------------------------------------

        //  Call the ADMIN:READY event, admin is all geared up and ready to go
        $oEventService->trigger(Events::ADMIN_READY, Events::getEventNamespace());
    }

    // --------------------------------------------------------------------------

    /**
     * Load admin configs
     *
     * @return $this
     * @throws FactoryException
     */
    protected function loadConfigs(): self
    {
        $oConfig = Factory::service('Config');

        $aPaths = [
            Config::get('NAILS_APP_PATH') . 'application/config/admin.php',
            Config::get('NAILS_APP_PATH') . 'application/modules/admin/config/admin.php',
        ];

        foreach ($aPaths as $sPath) {
            if (file_exists($sPath)) {
                $oConfig->load($sPath);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load admin helpers
     *
     * @return $this
     * @throws FactoryException
     */
    protected function loadHelpers(): self
    {
        Factory::helper('admin', Constants::MODULE_SLUG);
        Factory::helper('form', Constants::MODULE_SLUG);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load admin languages
     *
     * @return $this
     * @deprecated
     */
    protected function loadLanguages(): self
    {
        //  @todo (Pablo - 2018-09-24) - Remove this
        get_instance()->lang->load('admin/admin_generic');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load all Admin orientated JS
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadJs(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->load('admin.min.js', Constants::MODULE_SLUG)
            ->load('nails.admin.min.js', 'NAILS')
            ->load('nails.forms.min.js', 'NAILS');

        //  Inline assets
        $aJs = [

            //  @todo (Pablo - 2019-12-05) - Remove these items (move into module-admin/admin.js as components)
            'var _nails_admin, _nails_forms;',

            'if (typeof(NAILS_Admin) === "function"){',
            '_nails_admin = new NAILS_Admin();',
            '}',

            'if (typeof(NAILS_Forms) === "function"){',
            '_nails_forms = new NAILS_Forms();',
            '}',

            //  Trigger a UI Refresh, most JS components should use this to bind to and render items
            'window.NAILS.ADMIN.refreshUi();',
        ];

        $oAsset->inline(implode(PHP_EOL, $aJs), 'JS');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load all Admin orientated CSS
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadCss(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->load('nails.admin.css', 'NAILS')
            ->load('admin.css', Constants::MODULE_SLUG);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load services required by admin
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadLibraries(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            //  jQuery
            ->load('jquery/dist/jquery.min.js', 'NAILS-BOWER')
            //  Fancybox
            ->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER')
            ->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER')
            //  jQuery Toggles
            ->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER')
            ->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER')
            ->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER')
            //  jQuery serializeObject
            ->load('jquery-serialize-object/dist/jquery.serialize-object.min.js', 'NAILS-BOWER')
            //  Tipsy
            ->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER')
            ->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER')
            //  scrollTo
            ->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER')
            //  jQuery Cookies
            ->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER')
            //  Retina.js
            ->load('retina.js/dist/retina.min.js', 'NAILS-BOWER')
            //  Bootstrap
            ->load('bootstrap/js/dropdown.js', 'NAILS-BOWER')
            //  Fontawesome
            ->load('fontawesome/css/fontawesome.css', 'NAILS-BOWER')
            ->load('fontawesome/css/solid.css', 'NAILS-BOWER')
            //  Asset libraries
            ->library('jqueryui')
            ->library('select2')
            ->library('ckeditor')
            ->library('uploadify')
            ->library('knockout')
            ->library('moment')
            ->library('mustache');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Autoload component items
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function autoLoad(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');

        foreach (Components::available() as $oComponent) {
            if (!empty($oComponent->data->{Constants::MODULE_SLUG}->autoload)) {

                $oAutoLoad = $oComponent->data->{Constants::MODULE_SLUG}->autoload;

                //  Services
                if (!empty($oAutoLoad->services)) {
                    foreach ($oAutoLoad->services as $sService) {
                        Factory::service($sService, $oComponent->slug);
                    }
                }

                //  Models
                if (!empty($oAutoLoad->models)) {
                    foreach ($oAutoLoad->models as $sModel) {
                        Factory::model($sModel, $oComponent->slug);
                    }
                }

                //  Helpers
                if (!empty($oAutoLoad->helpers)) {
                    foreach ($oAutoLoad->helpers as $sHelper) {
                        Factory::helper($sHelper, $oComponent->slug);
                    }
                }

                //  Javascript
                if (!empty($oAutoLoad->assets->js)) {
                    foreach ($oAutoLoad->assets->js as $sAsset) {
                        $oAsset->load($sAsset, $oComponent->slug, 'JS');
                    }
                }

                //  Inline Javascript
                if (!empty($oAutoLoad->assets->jsInline)) {
                    foreach ($oAutoLoad->assets->jsInline as $sAsset) {
                        $oAsset->inline($sAsset, 'JS');
                    }
                }

                //  CSS
                if (!empty($oAutoLoad->assets->css)) {
                    foreach ($oAutoLoad->assets->css as $sAsset) {
                        $oAsset->load($sAsset, $oComponent->slug, 'CSS');
                    }
                }

                //  Inline CSS
                if (!empty($oAutoLoad->assets->cssInline)) {
                    foreach ($oAutoLoad->assets->cssInline as $sAsset) {
                        $oAsset->inline($sAsset, 'CSS');
                    }
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Defines the admin controller
     *
     * @return array
     */
    public static function announce()
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     *
     * @return array
     */
    public static function permissions(): array
    {
        return [];
    }
}
