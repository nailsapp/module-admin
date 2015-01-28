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

class Admin extends NAILS_Controller
{
    protected $adminControllers;

    // --------------------------------------------------------------------------

    public function index()
    {
        //  Determine what admin controllers are available to the system
        $this->findAdminControllers();

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

        //  Make the interface available
        require_once NAILS_PATH . 'module-admin/interfaces/AdminController.php';

        //  Look in the admin module
        $this->loadAdminControllers(
            'admin',
            NAILS_PATH . 'module-admin/admin/controllers/',
            FCPATH . APPPATH . 'modules/admin/controllers/'
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

        // @todo
    }

    // --------------------------------------------------------------------------

    protected function loadAdminControllers($moduleName, $controllerPath, $appPath)
    {
        //  Does a path exist? don't pollute the array with empty modules
        if (is_dir($controllerPath)) {

            //  Look for controllers
            $files = directory_map($controllerPath, 1);

            foreach ($files as $file) {

                $this->loadAdminController($file, $moduleName, $controllerPath, $appPath);
            }
        }
    }

    // --------------------------------------------------------------------------

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

        //  Does it implement the AdminController interface?
        $reflect = new ReflectionClass($className);

        if (!$reflect->implementsInterface('\Nails\Admin\Interfaces\AdminController')) {

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
            'details'   => $className::announce()
        );

        return true;
    }

    // --------------------------------------------------------------------------

    protected function routeRequest()
    {
        //  What are we trying to access?
        $module     = $this->uri->rsegment(3) ? $this->uri->rsegment(3) : 'admin';
        $controller = $this->uri->rsegment(4) ? $this->uri->rsegment(4) : 'index';
        $method     = $this->uri->rsegment(5) ? $this->uri->rsegment(5) : 'index';

        //  Special case for the



        dump($this->uri->rsegments);
        dump('module: ' . $module);
        dump('controller: ' . $controller);
        dump('method: ' . $method);
        dump('$this->adminControllers[' . $module . ']->controllers[' . $controller . ']->' . $method . '();');

        /**
         * Firstly, is the request valid? i.e., does it route to an adminController
         * we know about?
         */

        dump($this->adminControllers);
    }
}
