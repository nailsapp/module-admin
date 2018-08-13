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
        $oEventService->trigger(Events::ADMIN_STARTUP, 'nailsapp/module-admin');

        // --------------------------------------------------------------------------

        //  Provide access to the main controller's data property
        $this->data =& getControllerData();
        //  @todo (Pablo - 2017-06-08) - Try and remove these dependencies
        $this->load =& get_instance()->load;
        $this->lang =& get_instance()->lang;

        // --------------------------------------------------------------------------

        /**
         * Load a bunch of things which are useful/needed in admin
         */

        //  Configs
        $oConfig = Factory::service('Config');

        $aPaths = [
            APPPATH . 'config/admin.php',
            APPPATH . 'modules/admin/config/admin.php',
        ];

        foreach ($aPaths as $sPath) {
            if (file_exists($sPath)) {
                $oConfig->load($sPath);
            }
        }

        //  Helpers
        Factory::helper('admin', 'nailsapp/module-admin');

        //  Languages
        get_instance()->lang->load('admin/admin_generic');

        // --------------------------------------------------------------------------

        $oAsset = Factory::service('Asset');

        //  Unload any previously loaded assets, admin handles its own assets
        $oAsset->clear();

        //  @todo (Pablo - 2017-06-08) - Try and reduce the number of things being loaded, or theme it

        //  Bower assets
        $oAsset->load('jquery/dist/jquery.min.js', 'NAILS-BOWER');
        $oAsset->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER');
        $oAsset->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER');
        $oAsset->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER');
        $oAsset->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER');
        $oAsset->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER');
        $oAsset->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER');
        $oAsset->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER');
        $oAsset->load('fontawesome/css/font-awesome.min.css', 'NAILS-BOWER');
        $oAsset->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER');
        $oAsset->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER');
        $oAsset->load('retina.js/dist/retina.min.js', 'NAILS-BOWER');
        $oAsset->load('bootstrap/js/dropdown.js', 'NAILS-BOWER');

        //  Libraries
        $oAsset->library('jqueryui');
        $oAsset->library('select2');
        $oAsset->library('ckeditor');
        $oAsset->library('uploadify');
        $oAsset->library('knockout');
        $oAsset->library('moment');
        $oAsset->library('mustache');

        //  Local assets
        $oAsset->load('nails.admin.css', 'NAILS');
        $oAsset->load('admin.css', 'nailsapp/module-admin');
        $oAsset->load('admin.min.js', 'nailsapp/module-admin');
        $oAsset->load('nails.default.min.js', 'NAILS');
        $oAsset->load('nails.admin.js', 'NAILS');
        $oAsset->load('nails.forms.min.js', 'NAILS');
        $oAsset->load('nails.api.min.js', 'NAILS');

        //  See if installed components want to autoload anything
        $aComponents = _NAILS_GET_COMPONENTS();
        foreach ($aComponents as $oComponent) {
            if (!empty($oComponent->data->{'nailsapp/module-admin'}->autoload)) {

                $oAutoLoad = $oComponent->data->{'nailsapp/module-admin'}->autoload;

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

                //  JS
                if (!empty($oAutoLoad->assets->js)) {
                    foreach ($oAutoLoad->assets->js as $mAsset) {

                        if (is_string($mAsset)) {
                            $sAsset    = $mAsset;
                            $sLocation = null;
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

                //  CSS
                if (!empty($oAutoLoad->assets->css)) {
                    foreach ($oAutoLoad->assets->css as $mAsset) {

                        if (is_string($mAsset)) {
                            $sAsset    = $mAsset;
                            $sLocation = null;
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

        //  Load app admin CSS & JS if it's there
        $sAdminCssPath = defined('APP_ADMIN_CSS_PATH') ? APP_ADMIN_CSS_PATH : FCPATH . 'assets/css/admin.css';
        $sAdminCssUrl  = defined('APP_ADMIN_CSS_URL') ? APP_ADMIN_CSS_URL : 'admin.css';
        if (file_exists($sAdminCssPath)) {
            $oAsset->load($sAdminCssUrl);
        }

        $sAdminJsPath = defined('APP_ADMIN_JS_PATH') ? APP_ADMIN_JS_PATH : FCPATH . 'assets/js/admin.min.js';
        $sAdminJsUrl  = defined('APP_ADMIN_JS_URL') ? APP_ADMIN_JS_URL : 'admin.min.js';
        if (file_exists($sAdminJsPath)) {
            $oAsset->load($sAdminJsUrl);
        }

        //  Load any additional admin assets
        $sAdminAssets = defined('APP_ADMIN_ASSETS') ? APP_ADMIN_ASSETS : '[]';
        $aAdminAssets = json_decode($sAdminAssets);
        if (!empty($aAdminAssets)) {
            foreach ($aAdminAssets as $sAsset) {
                $oAsset->load($sAsset);
            }
        }

        //  Inline assets
        $sJs = 'var _nails,_nails_admin,_nails_api, _nails_forms;';

        $sJs .= 'if (typeof(NAILS_JS) === \'function\'){';
        $sJs .= '_nails = new NAILS_JS();';
        $sJs .= '}';

        $sJs .= 'if (typeof(NAILS_API) === \'function\'){';
        $sJs .= '_nails_api = new NAILS_API();';
        $sJs .= '}';

        $sJs .= 'if (typeof(NAILS_Admin) === \'function\'){';
        $sJs .= '_nails_admin = new NAILS_Admin();';
        $sJs .= '}';

        $sJs .= 'if (typeof(NAILS_Forms) === \'function\'){';
        $sJs .= '_nails_forms = new NAILS_Forms();';
        $sJs .= '}';

        $oAsset->inline($sJs, 'JS');

        // --------------------------------------------------------------------------

        \Nails\Common\Controller\Base::populateUserFeedback($this->data);

        // --------------------------------------------------------------------------

        //  @todo (Pablo - 2017-06-08) - Remove this
        \Nails\Common\Controller\Base::backwardsCompatibility($this);

        // --------------------------------------------------------------------------

        //  Call the ADMIN:READY event, admin is all geared up and ready to go
        $oEventService->trigger(Events::ADMIN_STARTUP, 'nailsapp/module-admin');
    }

    // --------------------------------------------------------------------------

    /**
     * Defines the admin controller
     * @return array
     */
    public static function announce()
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        return [];
    }
}
