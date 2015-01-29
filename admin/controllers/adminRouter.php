<?php

/**
 * This class routes all requests in admin to the appropriate place
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class AdminRouter extends NAILS_Controller
{
    protected $adminControllers;
    protected $adminControllersNav;
    protected $admincontrollersNavNotSortable;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

        //  An array of Nav groupings which shouldn't be sortable

        /**
         * Admin nav groupings are sortable by default, if you wish to make any
         * "sticky" then define them here. The order here will be respected i.e.,
         * The first item will stick to the very top, the next beneath it and so
         * forth.
         */

        $this->admincontrollersNavSticky = array(
            'Dashboard',
            'Utilities',
            'Settings'
        );

        /**
         * Sticky items by default stick to the top of the nav. If you wish for
         * any to stick to the bottom define them here. The order defined above
         * will be respected, i.e., the order below doesn't matter.
         */

        $this->admincontrollersNavStickyBottom = array(
            'Utilities',
            'Settings'
        );

        /**
         * If you wish to give groupings
         */
    }

    // --------------------------------------------------------------------------

    /**
     * Initial touchpoint for admin, all requests are routed through here.
     * @return void
     */
    public function index()
    {
        //  Is there an AdminIP whitelist?
        $whitelistIp = (array) app_setting('whitelist', 'admin');

        if ($whitelistIp) {
            if (!isIpInRange($this->input->ip_address(), $whitelistIp)) {
                show_404();
            }
        }

        //  Before we do anything, is the user an admin?
        if (!$this->user_model->is_admin()) {
            unauthorised();
        }

        //  Determine which admin controllers are available to the system
        $this->findAdminControllers();

        /**
         * Sort the controlelrs into a view friendly array, taking into
         * consideration the user's order and state preferences.
         */

        $this->prepAdminControllersNav();

        //  Save adminControllers to controller data so everyone can use it
        $this->data['adminControllers']    = $this->adminControllers;
        $this->data['adminControllersNav'] = $this->adminControllersNav;

        //  Route the user's request
        $this->routeRequest();
    }

    // --------------------------------------------------------------------------

    /**
     * Searches modules and the app for valid admin controllers which the active
     * user has permission to access.
     * @return void
     */
    protected function findAdminControllers()
    {
        //  Set things up
        $this->load->helper('directory');

        //  Fetch the admincontroller base class
        require_once NAILS_PATH . 'module-admin/admin/controllers/adminController.php';

        //  Look in the admin module
        $this->loadAdminControllers(
            'admin',
            NAILS_PATH . 'module-admin/admin/controllers/',
            FCPATH . APPPATH . 'modules/admin/controllers/',
            array(
                'adminRouter.php'
            )
        );

        //  Look in all enabled modules
        $modules = _NAILS_GET_MODULES();

        foreach ($modules as $module) {
            /**
             * Skip the admin module. We use the moduleName rather than the component name
             * so that we don't inadvertantly load up the admin module (or any module identifying
             * itself as admin) and listing all the files contained therein; we only want
             * admin/controllers.
             */

            if ($module->moduleName == 'admin') {
                continue;
            }

            $this->loadAdminControllers(
                $module->moduleName,
                $module->path . 'admin/controllers/',
                FCPATH . APPPATH . 'modules/' . $module->moduleName . '/admin/controllers/'
            );
        }

        /**
         * Ok! So we have all available AdminControllers and the classes are all loaded. Let's
         * see which the user has permission to access.
         */

        if (!$this->user->is_superuser()) {
            dumpanddie('not a superuser, do the checks');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for admin controllers in specific locations
     * @param  string $moduleName     The name of the module currently being searched
     * @param  string $controllerPath The full path to the controllers
     * @param  string $appPath        If the app can extend these controllers, this is where it will place the file
     * @param  array  $ignore         An array of filenames to ignore
     * @return void
     */
    protected function loadAdminControllers($moduleName, $controllerPath, $appPath, $ignore = array())
    {
        //  Does a path exist? don't pollute the array with empty modules
        if (is_dir($controllerPath)) {
            //  Look for controllers
            $files = directory_map($controllerPath, 1);

            foreach ($files as $file) {
                if (in_array($file, $ignore)) {
                    continue;
                }

                $this->loadAdminController($file, $moduleName, $controllerPath, $appPath);
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Loads a specific admin controller
     * @param  string $file           The file to load
     * @param  string $moduleName     The name of the module currently being searched
     * @param  string $controllerPath The full path to the controller
     * @param  string $appPath        If the app can extend this controllers, this is where it will place the file
     * @return void
     */
    protected function loadAdminController($file, $moduleName, $controllerPath, $appPath)
    {
        $fileName = substr($file, 0, strpos($file, '.php'));

        //  PHP file, no leading underscore
        if (!preg_match('/^[^_][a-zA-Z_]+\.php$/', $file)) {
            return false;
        }

        //  Valid file, load it up and define the full class path and name
        require_once $controllerPath . $file;
        $classPath = $controllerPath . $file;
        $className = 'Nails\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);

        //  If there's an app version of this controller than we'll use that one instead.
        if (is_file($appPath . $file)) {
            require_once $appPath . $file;
            $classPath = $appPath . $file;
            $className = 'App\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);

            //  Does the expected class exist? If it doesn't fall back to the previous one
            if (!class_exists($className)) {
                $classPath = $controllerPath . $file;
                $className = 'Nails\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);
            }
        }

        //  Does the expected class exist?
        if (!class_exists($className)) {
            return false;
        }

        //  Does it have an announce method?
        if (!method_exists($className, 'announce')) {
            return false;
        }

        //  Cool! We have a controller which is valid, Add it to the stack!
        if (!isset($this->adminControllers[$moduleName])) {
            $this->adminControllers[$moduleName]              = new stdClass();
            $this->adminControllers[$moduleName]->controllers = array();
        }

        $this->adminControllers[$moduleName]->controllers[$fileName] = array(
            'className' => $className,
            'path'      => $classPath,
            'methods'   => $className::announce()
        );

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a "view friendly" array of the admin controllers, takes into
     * consideration the user's order and state preferences
     * @return void
     */
    public function prepAdminControllersNav()
    {
        $adminControllersNav = array();

        foreach ($this->adminControllers as $module => $moduleDetails) {
            foreach($moduleDetails->controllers as $controller => $controllerDetails) {
                foreach($controllerDetails['methods'] as $method => $methodDetails) {

                    $methodUrl  = $module . '/' . $controller;
                    $methodUrl .= empty($method) ? '' : '/';
                    $methodUrl .= $method;

                    $methodGroup = !empty($methodDetails[0]) ? $methodDetails[0] : 'Undefined Group';
                    $methodLabel = !empty($methodDetails[1]) ? $methodDetails[1] : 'Undefined Action';

                    if (!isset($adminControllersNav[md5($methodGroup)])) {
                        $adminControllersNav[md5($methodGroup)]           = new \stdClass();
                        $adminControllersNav[md5($methodGroup)]->label    = $methodGroup;
                        $adminControllersNav[md5($methodGroup)]->icon     = 'fa-cog';
                        $adminControllersNav[md5($methodGroup)]->sortable = true;
                        $adminControllersNav[md5($methodGroup)]->actions  = array();
                    }

                    $adminControllersNav[md5($methodGroup)]->actions[$methodUrl] = $methodLabel;
                }
            }
        }

        //  Reset the indexes
        $adminControllersNav = array_values($adminControllersNav);

        foreach($adminControllersNav as $group) {

            //  Set the icons
            //  @todo

            //  Set sortable
            if (in_array($group->label, $this->admincontrollersNavSticky)) {
                $group->sortable = false;
            }
        }

        //  Split the groups
        $stickyTop    = array();
        $out          = array();
        $stickyBottom = array();

        foreach ($this->admincontrollersNavSticky as $sticky) {
            foreach($adminControllersNav as $group) {
                if ($group->label == $sticky) {
                    if (in_array($sticky, $this->admincontrollersNavStickyBottom)) {
                        $stickyBottom[] = $group;
                    } else {
                        $stickyTop[] = $group;
                    }
                }
            }
        }

        foreach($adminControllersNav as $group) {

            if (!in_array($group->label, $this->admincontrollersNavSticky)) {
                $out[] = $group;
            }
        }

        //  Order the non-sticky items as per the user's pref, alphabetically if no prefs
        //  @todo

        //  Save to the class
        $this->adminControllersNav = array_merge($stickyTop, $out, $stickyBottom);
    }

    // --------------------------------------------------------------------------

    /**
     * Routes the request to the appropriate controller
     * @return void
     */
    protected function routeRequest()
    {
        //  Check that admin is running on the SECURE_BASE_URL url
        if (APP_SSL_ROUTING) {
            $host1 = $this->input->server('HTTP_HOST');
            $host2 = parse_url(SECURE_BASE_URL);

            if (!empty($host2['host']) && $host2['host'] != $host1) {
                //  Not on the secure URL, redirect with message
                $redirect = $this->input->server('REQUEST_URI');

                if ($redirect) {
                    $this->session->set_flashdata('message', lang('admin_not_secure'));
                    redirect($redirect);
                }
            }
        }

        // --------------------------------------------------------------------------

        //  What are we trying to access?
        $module     = $this->uri->rsegment(3) ? $this->uri->rsegment(3) : '';
        $controller = $this->uri->rsegment(4) ? $this->uri->rsegment(4) : $module;
        $method     = $this->uri->rsegment(5) ? $this->uri->rsegment(5) : 'index';

        if (empty($module)) {

            redirect('admin/admin/dashboard');

        } elseif (isset($this->adminControllers[$module]->controllers[$controller])) {
            $requestController = $this->adminControllers[$module]->controllers[$controller];
            $className         = $requestController['className'];
            $requestPage       = new $className();

            if (is_callable(array($requestPage, $method))) {
                return $requestPage->$method();
            } else {
                show_404();
            }

        } else {
            show_404();
        }
    }
}
