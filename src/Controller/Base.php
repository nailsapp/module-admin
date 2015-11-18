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

        //  Models
        $this->routes_model = Factory::model('Routes');

        //  Libraries
        $this->cdn = Factory::service('Cdn', 'nailsapp/module-cdn');

        //  Helpers
        Factory::helper('admin', 'nailsapp/module-admin');

        //  Languages
        $this->lang->load('admin/admin_generic');

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        $this->asset->clear();

        //  Bower assets
        $this->asset->load('jquery/dist/jquery.min.js', 'NAILS-BOWER');
        $this->asset->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER');
        $this->asset->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER');
        $this->asset->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER');
        $this->asset->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER');
        $this->asset->load('fontawesome/css/font-awesome.min.css', 'NAILS-BOWER');
        $this->asset->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER');
        $this->asset->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER');
        $this->asset->load('retina.js/dist/retina.min.js', 'NAILS-BOWER');

        //  Libraries
        $this->asset->library('jqueryui');
        $this->asset->library('select2');
        $this->asset->library('ckeditor');
        $this->asset->library('uploadify');

        //  Local assets
        $this->asset->load('nails.admin.css', 'NAILS');
        $this->asset->load('nails.default.min.js', 'NAILS');
        $this->asset->load('nails.admin.min.js', 'NAILS');
        $this->asset->load('nails.forms.min.js', 'NAILS');
        $this->asset->load('nails.api.min.js', 'NAILS');

        //  Load admin CSS & JS if it's there
        $sAdminCssPath = defined('APP_ADMIN_CSS_PATH') ? APP_ADMIN_CSS_PATH : FCPATH . 'assets/css/admin.css';
        $sAdminCssUrl  = defined('APP_ADMIN_CSS_URL') ? APP_ADMIN_CSS_URL : 'admin.css';
        if (file_exists($sAdminCssPath)) {
            $this->asset->load($sAdminCssUrl);
        }

        $sAdminJsPath = defined('APP_ADMIN_JS_PATH') ? APP_ADMIN_JS_PATH : FCPATH . 'assets/js/admin.min.js';
        $sAdminJsUrl  = defined('APP_ADMIN_JS_URL') ? APP_ADMIN_JS_URL : 'admin.min.js';
        if (file_exists($sAdminJsPath)) {
            $this->asset->load($sAdminJsUrl);
        }

        //  Load any additional admin assets
        $sAdminAssets = defined('APP_ADMIN_ASSETS') ? APP_ADMIN_ASSETS : '[]';
        $aAdminAssets = json_decode($sAdminAssets);
        if (!empty($aAdminAssets)) {
            foreach ($aAdminAssets as $sAsset) {
                $this->asset->load($sAsset);
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


        $this->asset->inline($js, 'JS');
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
