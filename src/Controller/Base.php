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

use Nails\Admin\Events;
use Nails\Components;
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
        $oEventService = Factory::service('Event');

        //  Call the ADMIN:STARTUP event, admin is constructing
        $oEventService->trigger(Events::ADMIN_STARTUP, Events::getEventNamespace());

        // --------------------------------------------------------------------------

        //  Provide access to the main controller's data property
        $this->data =& getControllerData();

        // --------------------------------------------------------------------------

        $this->loadConfigs();
        $this->loadHelpers();
        $this->loadLanguages();

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        $oAsset = Factory::service('Asset');
        $oAsset->clear();

        //  @todo (Pablo - 2017-06-08) - Try and reduce the number of things being loaded, or theme it
        $this->loadLibraries();
        $this->loadJs();
        $this->loadCss();
        $this->autoLoad();

        // --------------------------------------------------------------------------

        \Nails\Common\Controller\Base::populateUserFeedback($this->data);

        // --------------------------------------------------------------------------

        //  Call the ADMIN:READY event, admin is all geared up and ready to go
        $oEventService->trigger(Events::ADMIN_READY, Events::getEventNamespace());
    }

    // --------------------------------------------------------------------------

    protected function loadConfigs()
    {
        $oConfig = Factory::service('Config');

        $aPaths = [
            NAILS_APP_PATH . 'application/config/admin.php',
            NAILS_APP_PATH . 'application/modules/admin/config/admin.php',
        ];

        foreach ($aPaths as $sPath) {
            if (file_exists($sPath)) {
                $oConfig->load($sPath);
            }
        }
    }

    // --------------------------------------------------------------------------

    protected function loadHelpers()
    {
        Factory::helper('admin', 'nails/module-admin');
    }

    // --------------------------------------------------------------------------

    protected function loadLanguages()
    {
        //  @todo (Pablo - 2018-09-24) - Remove this
        get_instance()->lang->load('admin/admin_generic');
    }

    // --------------------------------------------------------------------------

    /**
     * Load all Admin orientated JS
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadJs()
    {
        \Nails\Common\Controller\Base::setNailsJs();

        $oAsset = Factory::service('Asset');

        //  Module assets
        $oAsset->load('admin.min.js', 'nails/module-admin');
        $oAsset->load('nails.default.min.js', 'NAILS');
        $oAsset->load('nails.admin.min.js', 'NAILS');
        $oAsset->load('nails.forms.min.js', 'NAILS');
        $oAsset->load('nails.api.min.js', 'NAILS');

        //  Component assets
        foreach (Components::available() as $oComponent) {

            if (!empty($oComponent->data->{'nails/module-admin'}->autoload)) {

                $oAutoLoad = $oComponent->data->{'nails/module-admin'}->autoload;
                if (!empty($oAutoLoad->assets->js)) {
                    foreach ($oAutoLoad->assets->js as $mAsset) {

                        if (is_string($mAsset)) {
                            $sAsset    = $mAsset;
                            $sLocation = $oComponent->slug;
                        } else {
                            $sAsset    = !empty($mAsset[0]) ? $mAsset[0] : null;
                            $sLocation = !empty($mAsset[1]) ? $mAsset[1] : null;
                        }

                        $oAsset->load($sAsset, $sLocation, 'JS');
                    }
                }

                //  JS Inline
                if (!empty($oAutoLoad->assets->jsInline)) {
                    foreach ($oAutoLoad->assets->jsInline as $sAsset) {
                        $oAsset->inline($sAsset, 'JS');
                    }
                }
            }
        }

        //  Global JS
        $sAdminJsPath = defined('APP_ADMIN_JS_PATH') ? APP_ADMIN_JS_PATH : NAILS_APP_PATH . 'assets/build/js/admin.min.js';
        $sAdminJsUrl  = defined('APP_ADMIN_JS_URL') ? APP_ADMIN_JS_URL : 'admin.min.js';
        if (file_exists($sAdminJsPath)) {
            $oAsset->load($sAdminJsUrl);
        }

        //  Inline assets
        $aJs = [

            //  @todo (Pablo - 2019-12-05) - Remove these items (move into module-admin/admin.js as components)
            'var _nails,_nails_admin,_nails_api, _nails_forms;',

            'if (typeof(NAILS_JS) === "function"){',
            '_nails = new NAILS_JS();',
            '}',

            'if (typeof(NAILS_API) === "function"){',
            '_nails_api = new NAILS_API();',
            '}',

            'if (typeof(NAILS_Admin) === "function"){',
            '_nails_admin = new NAILS_Admin();',
            '}',

            'if (typeof(NAILS_Forms) === "function"){',
            '_nails_forms = new NAILS_Forms();',
            '}',

            //  Trigger a UI Refresh, most JS components should use this to bind ti and render items
            'window.NAILS.ADMIN.refreshUi();',
        ];

        $oAsset->inline(implode(PHP_EOL, $aJs), 'JS');
    }

    // --------------------------------------------------------------------------

    /**
     * Load all Admin orientated CSS
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadCss()
    {
        $oAsset = Factory::service('Asset');

        //  Module assets
        $oAsset->load('nails.admin.css', 'NAILS');
        $oAsset->load('admin.css', 'nails/module-admin');

        //  Component assets
        foreach (Components::available() as $oComponent) {
            if (!empty($oComponent->data->{'nails/module-admin'}->autoload)) {

                $oAutoLoad = $oComponent->data->{'nails/module-admin'}->autoload;

                if (!empty($oAutoLoad->assets->css)) {
                    foreach ($oAutoLoad->assets->css as $mAsset) {

                        if (is_string($mAsset)) {
                            $sAsset    = $mAsset;
                            $sLocation = $oComponent->slug;
                        } else {
                            $sAsset    = !empty($mAsset[0]) ? $mAsset[0] : null;
                            $sLocation = !empty($mAsset[1]) ? $mAsset[1] : null;
                        }

                        $oAsset->load($sAsset, $sLocation, 'CSS');
                    }
                }

                //  CSS Inline
                if (!empty($oAutoLoad->assets->cssInline)) {
                    foreach ($oAutoLoad->assets->cssInline as $sAsset) {
                        $oAsset->inline($sAsset, 'CSS');
                    }
                }
            }
        }

        //  Global CSS
        $sAdminCssPath = defined('APP_ADMIN_CSS_PATH') ? APP_ADMIN_CSS_PATH : NAILS_APP_PATH . 'assets/build/css/admin.min.css';
        $sAdminCssUrl  = defined('APP_ADMIN_CSS_URL') ? APP_ADMIN_CSS_URL : 'admin.min.css';
        if (file_exists($sAdminCssPath)) {
            $oAsset->load($sAdminCssUrl);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load services required by admin
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadLibraries()
    {
        $oAsset = Factory::service('Asset');

        //  jQuery
        $oAsset->load('jquery/dist/jquery.min.js', 'NAILS-BOWER');

        //  Fancybox
        $oAsset->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER');
        $oAsset->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER');

        //  jQuery Toggles
        $oAsset->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER');
        $oAsset->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER');
        $oAsset->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER');

        //  Tipsy
        $oAsset->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER');
        $oAsset->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER');

        //  scrollTo
        $oAsset->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER');

        //  jQuery Cookies
        $oAsset->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER');

        //  Retina.js
        $oAsset->load('retina.js/dist/retina.min.js', 'NAILS-BOWER');

        //  Bootstrap
        $oAsset->load('bootstrap/js/dropdown.js', 'NAILS-BOWER');

        //  Fontawesome
        $oAsset->load('fontawesome/css/fontawesome.css', 'NAILS-BOWER');
        $oAsset->load('fontawesome/css/solid.css', 'NAILS-BOWER');

        //  Asset libraries
        $oAsset->library('jqueryui');
        $oAsset->library('select2');
        $oAsset->library('ckeditor');
        $oAsset->library('uploadify');
        $oAsset->library('knockout');
        $oAsset->library('moment');
        $oAsset->library('mustache');
    }

    // --------------------------------------------------------------------------

    /**
     * Autoload component items
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function autoLoad()
    {
        foreach (Components::available() as $oComponent) {
            if (!empty($oComponent->data->{'nails/module-admin'}->autoload)) {

                $oAutoLoad = $oComponent->data->{'nails/module-admin'}->autoload;

                //  Libraries
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
            }
        }
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
