<?php

/**
 * Admin changelog model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

class NAILS_Admin_Model extends NAILS_Model
{
    protected $searchPaths;

    // --------------------------------------------------------------------------

    /**
     * Constructs the model
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        /**
         * Set the search paths to look for modules within; paths listed first
         * take priority over those listed after it.
         *
         **/

        $this->searchPaths[] = FCPATH . APPPATH . 'modules/admin/controllers/';
        $this->searchPaths[] = NAILS_PATH . 'module-admin/admin/controllers/';
    }

    // --------------------------------------------------------------------------

    /**
     * Look for modules which reside within the search paths.
     * @param   string   $module The name of the module to search for
     * @return  stdClass
     **/
    public function find_module($module)
    {
        $moduleDetails = array();

        // --------------------------------------------------------------------------

        //  Look in our search paths for a controller of the same name as the module.
        foreach ($this->searchPaths as $path) {

            if (file_exists($path . $module . '.php')) {

                require_once $path . $module . '.php';

                $moduleDetails = $module::announce();

                if (!is_array($moduleDetails)) {

                    $moduleDetails = array($moduleDetails);
                }

                $moduleDetails = array_filter($moduleDetails);

                if ($moduleDetails) {

                    if (!is_array($moduleDetails)) {

                        $moduleDetails = array($moduleDetails);
                    }

                    foreach ($moduleDetails as $index => &$detail) {

                        //  If there're no methods then remove it
                        if (empty($detail->funcs)) {

                            $detail = null;

                        } else {

                            //  Basics
                            $detail->class_name    = $module;
                            $detail->class_index   = $module . ':' . $index;

                            //  Any extra permissions?
                            $detail->extra_permissions = $module::permissions($detail->class_index);
                        }
                    }
                }
            }
        }

        // --------------------------------------------------------------------------

        $moduleDetails = array_filter($moduleDetails);
        $moduleDetails = array_values($moduleDetails);

        return $moduleDetails;
    }

    // --------------------------------------------------------------------------

    /**
     * Loop through the enabled modules and see if a controller exists for it; if
     * it does load it up and execute the announce static method to see if we can
     * display it to the active user.
     * @return array
     */
    public function get_active_modules()
    {
        $cache_key = 'available_admin_modules_' . active_user('id');
        $cache     = $this->_get_cache($cache_key);

        if ($cache) {

            return $cache;
        }

        // --------------------------------------------------------------------------

        $modulesPotential   = _NAILS_GET_POTENTIAL_MODULES();
        $modulesUnavailable = _NAILS_GET_UNAVAILABLE_MODULES();
        $modulesAvailable   = array();

        // --------------------------------------------------------------------------

        /**
         * Look for controllers
         * [0] => Path to search
         * [1] => Whether to test against $modulesUnavailable
         */

        $paths   = array();
        $paths[] = array(NAILS_PATH . 'module-admin/admin/controllers/', true);
        $paths[] = array(FCPATH . APPPATH . 'modules/admin/controllers/', false);

        //  Filter out non PHP files
        $regex = '/^[^_][a-zA-Z_]+\.php$/';

        //  Load directory helper
        $this->load->helper('directory');

        foreach ($paths as $path) {

            if (is_dir($path[0])) {

                $controllers = directory_map($path[0]);

                if (is_array($controllers)) {

                    foreach ($controllers as $controller) {

                        if (preg_match($regex, $controller)) {

                            $module = pathinfo($controller);
                            $module = $module['filename'];

                            if (!empty($path[1])) {

                                //  Module looks valid, is it a potential module, and if so, is it available?
                                if (array_search('nailsapp/module-' . $module, $modulesPotential) !== false) {

                                    if (array_search('nailsapp/module-' . $module, $modulesUnavailable) !== false) {

                                        //  Not installed
                                        continue;
                                    }
                                }
                            }

                            // --------------------------------------------------------------------------

                            $modulesAvailable[] = $module;
                        }
                    }
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Form the discovered modules into a more structured array
        $loadedModules = array();

        foreach ($modulesAvailable as $module) {

            $module = $this->find_module($module);

            if (!empty($module)) {

                foreach ($module as $module) {

                    $loadedModules[$module->class_index] = $module;
                }
            }
        }

        // --------------------------------------------------------------------------

        /**
         * If the user has a custom order specified then use that, otherwise fall back to
         * sort alphabetically by name.
         */

        $userNavPref = $this->admin_model->get_admin_data('nav');
        $out         = array();

        if (!empty($userNavPref)) {

            //  User's preference first
            foreach ($userNavPref as $module => $options) {

                if (!empty($loadedModules[$module])) {

                    $out[$module] = $loadedModules[$module];
                }
            }

            //  Anything left over goes to the end.
            foreach ($loadedModules as $module) {

                if (!isset($out[$module->class_index])) {

                    $out[$module->class_index] = $module;
                }
            }

        } else {

            $this->load->helper('array');
            array_sort_multi($loadedModules, 'name');

            foreach ($loadedModules as $module) {

                $out[$module->class_index] = $module;
            }
        }

        // --------------------------------------------------------------------------

        /**
         * Place the dashboard at the top of the list and settings & utilities at
         * the end, always.
         * Hit tip: http://stackoverflow.com/a/11276338/789224
         */

        if (isset($out['dashboard:0'])) {

            $out = array('dashboard:0' => $out['dashboard:0']) + $out;
        }

        if (isset($out['settings:0'])) {

            $item = $out['settings:0'];
            unset($out['settings:0']);
            $out = $out + array('settings:0' => $item);
        }

        if (isset($out['utilities:0'])) {

            $item = $out['utilities:0'];
            unset($out['utilities:0']);
            $out = $out + array('utilities:0' => $item);
        }

        $out = array_values($out);

        // --------------------------------------------------------------------------

        //  Permissions
        //  ===========

        /**
         * Admin modules are opt in (i.e non super users must explicitly be granted
         * access). Loop through all potential modules and remoe any which are not
         * available to the currently active user. Super users can see everything.
         */

        if (!$this->user_model->is_superuser()) {

            /**
             * Loop through each available module and remove any which don't feature
             * in the user's ACL.
             */

            $acl = active_user('acl');

            for ($i = 0; $i < count($out); $i++) {

                //  Dashboard is *always* allowed
                if ($out[$i]->class_name != 'dashboard') {

                    /**
                     * Dealing with a module which is *not* the dashboard, is it
                     * featured in the user's ACL? If not, remove.
                     */

                    if (!isset($acl['admin'][$out[$i]->class_index])) {

                        //  See ya, bye.
                        $out[$i] = null;
                    }
                }
            }
        }

        // --------------------------------------------------------------------------

        $out = array_filter($out);
        $out = array_values($out);

        // --------------------------------------------------------------------------

        $this->_set_cache($cache_key, $out);

        // --------------------------------------------------------------------------

        return $out;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the currently active admin module
     * @return mixed stdClass on success null failue or permission denied
     */
    public function get_current_module()
    {
        $modules   = $this->get_active_modules();
        $curModule = $this->uri->segment(2, 'admin');
        $current   = null;

        foreach ($modules as $m) {

            if ($m->class_name == $curModule) {

                $current = $m;
                break;
            }
        }

        return $current;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets a piece of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $value  The value to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function set_admin_data($key, $value, $userId = null)
    {
        return $this->setUnsetAdminData($key, $value, $userId, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Unsets a piece of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function unset_admin_data($key, $userId = null)
    {
        return $this->setUnsetAdminData($key, null, $userId, false);
    }

    // --------------------------------------------------------------------------

    /**
     * Handles the setting and unsetting of admin data
     * @param  string  $key    The key to set
     * @param  mixed   $value  The value to set
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @param  boolean $set    Whether the data is being set or unset
     * @return boolean
     */
    protected function setUnsetAdminData($key, $value, $userId, $set)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        //  Get the existing data for this user
        $existing = $this->get_admin_data(null, $userId);

        if ($set) {

            //  Set the new key
            $existing[$key] = $value;

        } else {

            //  Unset the existing key
            unset($existing[$key]);
        }

        //  Save to the DB
        $data = array('admin_data' => serialize($existing));
        if ($this->user_model->update($userId, $data)) {

            //  Save to the cache
            $this->_set_cache('admin-data-' . $userId, $existing);
            return true;

        } else {

            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Gets items from the admin data, or the entire array of $key is null
     * @param  string $key     The key to set
     * @param  mixed  $userId The user's ID, if null active user is used.
     * @return mixed
     */
    public function get_admin_data($key = null, $userId = null)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        //  Check if data is already in the cache
        $cacheKey = 'admin-data-' . $userId;
        $cache    = $this->_get_cache($cacheKey);

        if ($cache) {

            $data = $cache;

        } else {

            $this->db->select('admin_data');
            $this->db->where('id', $userId);
            $result = $this->db->get(NAILS_DB_PREFIX . 'user')->row();

            if (!isset($result->admin_data)) {

                $data = array();

            } else {

                $data = unserialize($result->admin_data);
            }

            $this->_set_cache($cacheKey, $data);
        }

        // --------------------------------------------------------------------------

        /**
         * If no key is returned, return the entire data array, alternatively return
         * the key if it exists.
         */

        if (is_null($key)) {

            return $data;

        } elseif (isset($data[$key])) {

            return $data[$key];

        } else {

            return null;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Completely clears out the admin array
     * @param  mixed   $userId The user's ID, if null active user is used.
     * @return boolean
     */
    public function clear_admin_data($userId)
    {
        //  Get the user ID
        $userId = $this->adminDataGetUserId($userId);

        $data = array('admin_data' => null);
        if ($this->user_model->update($userId, $data)) {

            $this->_unset_cache('admin-data-' . $userId);
            return true;

        } else {

            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Extracts the user ID to use
     * @param  int $userId The User ID, or null for active user
     * @return int
     */
    protected function adminDataGetUserId($userId)
    {
        if (is_null($userId)) {

            $userId = active_user('id');

        } else {

            $userId = $userId;
        }

        return $userId;
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' MODELS
 *
 * The following block of code makes it simple to extend one of the core Nails
 * models. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_ADMIN_MODEL')) {

    class Admin_model extends NAILS_Admin_model
    {
    }
}
