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

class Base extends \MX_Controller
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
        $paths = array(
            FCPATH . APPPATH . 'config/admin.php',
            FCPATH . APPPATH . 'modules/admin/config/admin.php'
        );

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $this->config->load($path);
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
        $oAssetModel = Factory::service('Asset');
        $oAssetModel->clear();

        //  Bower assets
        $oAssetModel->load('jquery/dist/jquery.min.js', 'NAILS-BOWER');
        $oAssetModel->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER');
        $oAssetModel->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER');
        $oAssetModel->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER');
        $oAssetModel->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER');
        $oAssetModel->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER');
        $oAssetModel->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER');
        $oAssetModel->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER');
        $oAssetModel->load('fontawesome/css/font-awesome.min.css', 'NAILS-BOWER');
        $oAssetModel->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER');
        $oAssetModel->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER');
        $oAssetModel->load('retina.js/dist/retina.min.js', 'NAILS-BOWER');

        //  Libraries
        $oAssetModel->library('jqueryui');
        $oAssetModel->library('select2');
        $oAssetModel->library('ckeditor');
        $oAssetModel->library('uploadify');

        //  Local assets
        $oAssetModel->load('nails.admin.css', 'NAILS');
        $oAssetModel->load('nails.default.min.js', 'NAILS');
        $oAssetModel->load('nails.admin.min.js', 'NAILS');
        $oAssetModel->load('nails.forms.min.js', 'NAILS');
        $oAssetModel->load('nails.api.min.js', 'NAILS');

        //  See if installed components want to autoload anything
        $aComponents = _NAILS_GET_COMPONENTS();
        foreach ($aComponents as $oComponent) {
            if (!empty($oComponent->data->adminAutoLoad)) {

                $oAutoLoad = $oComponent->data->adminAutoLoad;

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
                if (!empty($oAutoLoad->js)) {
                    foreach ($oAutoLoad->js as $mAsset) {

                        if (is_string($mAsset)) {

                            $sAsset    = $mAsset;
                            $sLocation = null;

                        } else {

                            $sAsset    = !empty($mAsset[0]) ? $mAsset[0] : null;
                            $sLocation = !empty($mAsset[1]) ? $mAsset[1] : null;
                        }

                        $oAssetModel->load($sAsset, $sLocation, 'JS');
                    }
                }

                //  JS Inline
                if (!empty($oAutoLoad->jsInline)) {
                    foreach ($oAutoLoad->jsInline as $sAsset) {
                        $oAssetModel->inline($sAsset, 'JS');
                    }
                }

                //  CSS
                if (!empty($oAutoLoad->css)) {
                    foreach ($oAutoLoad->css as $mAsset) {

                        if (is_string($mAsset)) {

                            $sAsset    = $mAsset;
                            $sLocation = null;

                        } else {

                            $sAsset    = !empty($mAsset[0]) ? $mAsset[0] : null;
                            $sLocation = !empty($mAsset[1]) ? $mAsset[1] : null;
                        }

                        $oAssetModel->load($sAsset, $sLocation, 'CSS');
                    }
                }

                //  CSS Inline
                if (!empty($oAutoLoad->cssInline)) {
                    foreach ($oAutoLoad->cssInline as $sAsset) {
                        $oAssetModel->inline($sAsset, 'CSS');
                    }
                }
            }
        }

        //  Load app admin CSS & JS if it's there
        $sAdminCssPath = defined('APP_ADMIN_CSS_PATH') ? APP_ADMIN_CSS_PATH : FCPATH . 'assets/css/admin.css';
        $sAdminCssUrl  = defined('APP_ADMIN_CSS_URL') ? APP_ADMIN_CSS_URL : 'admin.css';
        if (file_exists($sAdminCssPath)) {
            $oAssetModel->load($sAdminCssUrl);
        }

        $sAdminJsPath = defined('APP_ADMIN_JS_PATH') ? APP_ADMIN_JS_PATH : FCPATH . 'assets/js/admin.min.js';
        $sAdminJsUrl  = defined('APP_ADMIN_JS_URL') ? APP_ADMIN_JS_URL : 'admin.min.js';
        if (file_exists($sAdminJsPath)) {
            $oAssetModel->load($sAdminJsUrl);
        }

        //  Load any additional admin assets
        $sAdminAssets = defined('APP_ADMIN_ASSETS') ? APP_ADMIN_ASSETS : '[]';
        $aAdminAssets = json_decode($sAdminAssets);
        if (!empty($aAdminAssets)) {
            foreach ($aAdminAssets as $sAsset) {
                $oAssetModel->load($sAsset);
            }
        }

        //  Inline assets
        $js  = 'var _nails,_nails_admin,_nails_forms,_nails_api;';

        $js .= 'if (typeof(NAILS_JS) === \'function\'){';
        $js .= '_nails = new NAILS_JS();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_Admin) === \'function\'){';
        $js .= '_nails_admin = new NAILS_Admin();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_Forms) === \'function\'){';
        $js .= '_nails_forms = new NAILS_Forms();';
        $js .= '}';

        $js .= 'if (typeof(NAILS_API) === \'function\'){';
        $js .= '_nails_api = new NAILS_API();';
        $js .= '}';


        $oAssetModel->inline($js, 'JS');
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
