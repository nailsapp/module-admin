<?php

class AdminController extends MX_Controller
{
    protected $data;

    // --------------------------------------------------------------------------

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
        $this->load->library('cdn/cdn');

        //  Languages
        $this->lang->load('admin/admin_generic');

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        $this->asset->clear();

        //  CSS
        $this->asset->load('fancybox/source/jquery.fancybox.css', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/css/toggles.css', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/css/themes/toggles-modern.css', 'NAILS-BOWER');
        $this->asset->load('tipsy/src/stylesheets/tipsy.css', 'NAILS-BOWER');
        $this->asset->load('fontawesome/css/font-awesome.min.css', 'NAILS-BOWER');
        $this->asset->load('nails.admin.css', true);

        //  JS
        $this->asset->load('jquery/dist/jquery.min.js', 'NAILS-BOWER');
        $this->asset->load('fancybox/source/jquery.fancybox.pack.js', 'NAILS-BOWER');
        $this->asset->load('jquery-toggles/toggles.min.js', 'NAILS-BOWER');
        $this->asset->load('tipsy/src/javascripts/jquery.tipsy.js', 'NAILS-BOWER');
        $this->asset->load('jquery.scrollTo/jquery.scrollTo.min.js', 'NAILS-BOWER');
        $this->asset->load('jquery-cookie/jquery.cookie.js', 'NAILS-BOWER');
        $this->asset->load('retina.js/dist/retina.min.js', 'NAILS-BOWER');
        $this->asset->load('nails.default.min.js', true);
        $this->asset->load('nails.admin.min.js', true);
        $this->asset->load('nails.forms.min.js', true);
        $this->asset->load('nails.api.min.js', true);

        //  Libraries
        $this->asset->library('jqueryui');
        $this->asset->library('select2');
        $this->asset->library('ckeditor');
        $this->asset->library('uploadify');

        //  Look for any Admin styles provided by the app
        if (file_exists(FCPATH . 'assets/css/admin.css')) {
            $this->asset->load('admin.css');
        }

        //  Inline assets
        $_js  = 'var _nails,_nails_admin,_nails_forms;';
        $_js .= '$(function(){';

        $_js .= 'if (typeof(NAILS_JS) === \'function\'){';
        $_js .= '_nails = new NAILS_JS();';
        $_js .= '_nails.init();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_Admin) === \'function\'){';
        $_js .= '_nails_admin = new NAILS_Admin();';
        $_js .= '_nails_admin.init();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_Forms) === \'function\'){';
        $_js .= '_nails_forms = new NAILS_Forms();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_API) === \'function\'){';
        $_js .= '_nails_api = new NAILS_API();';
        $_js .= '}';

        $_js .= '});';

        $this->asset->inline('<script>' . $_js . '</script>');

        // --------------------------------------------------------------------------

        //  Initialise the admin models
        $this->load->model('admin_changelog_model');
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
     * Returns any notifications which the suer should know about
     * @param  string $classIndex The classIndex value, used when multiple admin instances are available
     * @return array
     */
    public static function notifications($classIndex = null)
    {
        return array();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @param  string $classIndex The classIndex value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        return array();
    }
}
