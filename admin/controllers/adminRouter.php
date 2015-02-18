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
    protected $adminControllersPermissions;

    // --------------------------------------------------------------------------

    /**
     * Cosntruct the adminRouter, define the sticky groupings
     */
    public function __construct()
    {
        parent::__construct();


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
         * Load helpers we'll need
         */

        $this->load->helper('directory');

        /**
         * Fetch the base classes we'll be using
         * @todo: autoload these
         */

        require_once NAILS_PATH . 'module-admin/admin/controllers/adminController.php';
        require_once NAILS_PATH . 'module-admin/admin/controllers/adminHelper.php';
        require_once NAILS_PATH . 'module-admin/admin/controllers/adminNav.php';
    }

    // --------------------------------------------------------------------------

    /**
     * Initial touchpoint for admin, all requests are routed through here.
     * @return void
     */
    public function index()
    {
        //  When executing on the CLI we don't need to perform a few bit's of sense checking
        if (!$this->input->is_cli_request()) {

            //  Is there an AdminIP whitelist?
            $whitelistIp = (array) app_setting('whitelist', 'admin');

            if ($whitelistIp) {
                if (!isIpInRange($this->input->ip_address(), $whitelistIp)) {
                    show_404();
                }
            }

            //  Before we do anything, is the user an admin?
            if (!$this->user_model->isAdmin()) {

                unauthorised();
            }
        }

        //  Determine which admin controllers are available to the system
        $this->findAdminControllers();

        // --------------------------------------------------------------------------

        /**
         * Sort the controllers into a view friendly array, taking into
         * consideration the user's order and state preferences.
         */

        $this->load->model('admin_model');
        $this->prepAdminControllersNav();

        //  Save adminControllers to controller data so everyone can use it
        $this->data['adminControllers']    = $this->adminControllers;
        $this->data['adminControllersNav'] = $this->adminControllersNav;

        // --------------------------------------------------------------------------

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
        //  Look in the admin module
        $this->loadAdminControllers(
            'admin',
            NAILS_PATH . 'module-admin/admin/controllers/',
            FCPATH . APPPATH . 'modules/admin/controllers/',
            array(
                'adminController.php',
                'adminHelper.php',
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
            $this->adminControllers[$moduleName]              = new \stdClass();
            $this->adminControllers[$moduleName]->controllers = array();
        }

        $navGroupings = $className::announce();

        if (!empty($navGroupings) && !is_array($navGroupings) && !($navGroupings instanceof \Nails\Admin\Nav)) {

            /**
             * @todo Use an admin specific exception class, and autoload it.
             */

            throw new Exception('Admin Nav groupings returned by ' . $className . '::announce() were invalid', 1);

        } elseif (!is_array($navGroupings)) {

            $navGroupings = array_filter(array($navGroupings));
        }

        $this->adminControllers[$moduleName]->controllers[$fileName] = array(
            'className' => (string) $className,
            'path'      => (string) $classPath,
            'groupings' => $navGroupings
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
            foreach ($moduleDetails->controllers as $controller => $controllerDetails) {
                foreach ($controllerDetails['groupings'] as $grouping) {

                    //  Begin by defining which group this action belongs in
                    $groupLabel = $grouping->getLabel();

                    if (!isset($adminControllersNav[md5($groupLabel)])) {
                        $adminControllersNav[md5($groupLabel)]           = new \stdClass();
                        $adminControllersNav[md5($groupLabel)]->label    = $groupLabel;
                        $adminControllersNav[md5($groupLabel)]->sortable = true;
                        $adminControllersNav[md5($groupLabel)]->open     = true;
                        $adminControllersNav[md5($groupLabel)]->actions  = array();
                    }

                    //  Group icon
                    $adminControllersNav[md5($groupLabel)]->icon = $grouping->getIcon();

                    //  Group methods
                    $groupMethods = $grouping->getMethods();

                    foreach ($groupMethods as $methodUrl => $methodLabel) {

                        $url  = $module . '/' . $controller;
                        $url .= empty($methodUrl) ? '' : '/';
                        $url .= $methodUrl;

                        $adminControllersNav[md5($groupLabel)]->actions[$url]        = new \stdClass();
                        $adminControllersNav[md5($groupLabel)]->actions[$url]->label = $methodLabel;
                        $adminControllersNav[md5($groupLabel)]->actions[$url]->class = $controllerDetails['className'];
                        $adminControllersNav[md5($groupLabel)]->actions[$url]->path  = $controllerDetails['path'];
                    }
                }
            }
        }

        //  Reset the indexes
        $adminControllersNav = array_values($adminControllersNav);

        //  Remove navGroups with no actions and set as sortable
        $numGroups = count($adminControllersNav);
        for ($i=0; $i < $numGroups; $i++) {

            //  Remove if empty
            if (empty($adminControllersNav[$i]->actions)) {

                $adminControllersNav[$i] = null;
                continue;
            }

            //  Set sortable
            if (in_array($adminControllersNav[$i]->label, $this->admincontrollersNavSticky)) {

                $adminControllersNav[$i]->sortable = false;
            }
        }

        //  Filter and reset the indexes, again
        $adminControllersNav = array_filter($adminControllersNav);
        $adminControllersNav = array_values($adminControllersNav);

        //  Split the groups
        $stickyTop    = array();
        $middle       = array();
        $stickyBottom = array();

        foreach ($this->admincontrollersNavSticky as $sticky) {
            foreach ($adminControllersNav as $group) {
                if ($group->label == $sticky) {
                    if (in_array($sticky, $this->admincontrollersNavStickyBottom)) {
                        $stickyBottom[] = $group;
                    } else {
                        $stickyTop[] = $group;
                    }
                }
            }
        }

        foreach ($adminControllersNav as $group) {

            if (!in_array($group->label, $this->admincontrollersNavSticky)) {
                $middle[] = $group;
            }
        }

        /**
         * Sort everything alphabetically, then loop through the user's prefs and sort
         * by their preferences. If any modules are left over they'll appear at the end,
         * in alphabetical order. Also, set the open state of the module.
         */

        //  Sort alphabetically
        $this->load->helper('array');
        array_sort_multi($middle, 'label');

        //  Get user's prefs
        $userNavPref = $this->admin_model->getAdminData('nav');

        if (!empty($userNavPref)) {

            $temp = array();

            foreach ($userNavPref as $groupMd5 => $state) {

                for ($i=0; $i < count($middle); $i++) {

                    if (empty($middle[$i])) {

                        continue;
                    }

                    if ($groupMd5 == md5($middle[$i]->label)) {

                        if (!in_array($middle[$i]->label, $this->admincontrollersNavSticky)) {

                            $temp[] = $middle[$i];
                            $middle[$i] = null;
                        }
                    }
                }
            }

            //  Filter out the blanks
            $middle = array_filter($middle);
            $middle = array_values($middle);

            //  Merge
            $middle = array_merge($temp, $middle);
        }

        //  Save to the class
        $this->adminControllersNav = array_merge($stickyTop, $middle, $stickyBottom);

        //  Set the open states of the modules
        if (!empty($userNavPref)) {

            foreach ($userNavPref as $groupMd5 => $state) {

                for ($i=0; $i < count($this->adminControllersNav); $i++) {

                    if ($groupMd5 == md5($this->adminControllersNav[$i]->label)) {

                        $this->adminControllersNav[$i]->open = $state->open;
                    }
                }
            }
        }

        /**
         * Finally, now that everything has been sorted, go through all the groupings
         * and sort their emthods alphabetically so there's some feeling of order in
         * amongst all this chaos.
         */

        foreach ($this->adminControllersNav as $grouping) {

            array_sort_multi($grouping->actions, 'label');
        }
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
            $this->data['currentRequest'] = $requestController;
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
