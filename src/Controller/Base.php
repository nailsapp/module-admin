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

use Nails\Factory;

abstract class Base extends \MX_Controller
{
    protected $data;
    protected $cdn;

    // --------------------------------------------------------------------------

    /**
     * Construct the controller, load all the admin assets, etc
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  Get the controller data
        $this->data =& getControllerData();

        // --------------------------------------------------------------------------

        /**
         * Load a bunch of things which are useful/needed in admin
         */

        //  Configs
        $oConfig = Factory::service('Config');
        $paths   = array(
            FCPATH . APPPATH . 'config/admin.php',
            FCPATH . APPPATH . 'modules/admin/config/admin.php'
        );

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $oConfig->load($path);
            }
        }

        //  Libraries
        $this->cdn = Factory::service('Cdn', 'nailsapp/module-cdn');

        //  Helpers
        Factory::helper('admin', 'nailsapp/module-admin');

        //  Languages
        $this->lang->load('admin/admin_generic');

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        $oAsset = Factory::service('Asset');
        $oAsset->clear();

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
        $oAsset->load('nails.default.min.js', 'NAILS');
        $oAsset->load('nails.admin.min.js', 'NAILS');
        $oAsset->load('nails.forms.min.js', 'NAILS');
        $oAsset->load('nails.api.min.js', 'NAILS');

        //  See if installed components want to autoload anything
        $aComponents = _NAILS_GET_COMPONENTS();
        foreach ($aComponents as $oComponent) {
            if (!empty($oComponent->data->{'nailsapp/module-admin'}->autoload)) {

                $oAutoLoad = $oComponent->data->{'nailsapp/module-admin'}->autoload;

                //  Libraries
                //  @todo: maybe?

                //  Models
                //  @todo: maybe?

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
        $js  = 'var _nails,_nails_admin,_nails_api, _nails_forms;';

        $js .= 'if (typeof(NAILS_JS) === \'function\'){';
        $js .= '_nails = new NAILS_JS();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_Admin) === \'function\'){';
        $js .= '_nails_admin = new NAILS_Admin();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_API) === \'function\'){';
        $js .= '_nails_api = new NAILS_API();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_Forms) === \'function\'){';
        $js .= '_nails_forms = new NAILS_Forms();';
        $js .= '}';

        $oAsset->inline($js, 'JS');
    }

    // --------------------------------------------------------------------------

    /**
     * Defines the admin controller
     * @return array
     */
    public static function announce()
    {
        return array();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        return array();
    }
}
