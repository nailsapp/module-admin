<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage the CMS
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class NAILS_Cms extends NAILS_Admin_Controller
{
    /**
     * Announces this controller's navGroupings
     * @return stdClass
     */
    public static function announce()
    {
        if (!isModuleEnabled('nailsapp/module-cms')) {

            return false;
        }

        // --------------------------------------------------------------------------

        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = 'Content Management';
        $d->icon = 'fa-file-text';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs = array();

        if (user_has_permission('admin.cms:0.can_manage_menu')) {

            $d->funcs['menus'] = 'Manage Menus';
        }

        if (user_has_permission('admin.cms:0.can_manage_page')) {

            $d->funcs['pages'] = 'Manage Pages';
        }

        if (user_has_permission('admin.cms:0.can_manage_block')) {

            $d->funcs['blocks'] = 'Manage Blocks';
        }

        if (user_has_permission('admin.cms:0.can_manage_slider')) {

            $d->funcs['sliders'] = 'Manage Sliders';
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of notifications
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function notifications($classIndex = null)
    {
        $ci =& get_instance();
        $notifications = array();

        // --------------------------------------------------------------------------

        $notifications['pages']          = array();
        $notifications['pages']['title'] = 'Draft Pages';
        $notifications['pages']['type']  = 'neutral';

        $ci->db->where('is_published', false);
        $ci->db->where('is_deleted', false);
        $notifications['pages']['value'] = $ci->db->count_all_results(NAILS_DB_PREFIX . 'cms_page');

        // --------------------------------------------------------------------------

        return $notifications;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        $permissions = parent::permissions($classIndex);

        // --------------------------------------------------------------------------

        //  Menus
        $permissions['can_manage_menu']  = 'Can manage menus';
        $permissions['can_create_menu']  = 'Can create a new menu';
        $permissions['can_edit_menu']    = 'Can edit an existing menu';
        $permissions['can_delete_menu']  = 'Can delete an existing menu';
        $permissions['can_restore_menu'] = 'Can restore a deleted menu';

        //  Pages
        $permissions['can_manage_page']  = 'Can manage pages';
        $permissions['can_create_page']  = 'Can create a new page';
        $permissions['can_edit_page']    = 'Can edit an existing page';
        $permissions['can_delete_page']  = 'Can delete an existing page';
        $permissions['can_restore_page'] = 'Can restore a deleted page';
        $permissions['can_destroy_page'] = 'Can permenantly delete a page';

        //  Blocks
        $permissions['can_manage_block']  = 'Can manage blocks';
        $permissions['can_create_block']  = 'Can create a new block';
        $permissions['can_edit_block']    = 'Can edit an existing block';
        $permissions['can_delete_block']  = 'Can delete an existing block';
        $permissions['can_restore_block'] = 'Can restore a deleted block';

        //  Sliders
        $permissions['can_manage_slider']  = 'Can manage sliders';
        $permissions['can_create_slider']  = 'Can create a new slider';
        $permissions['can_edit_slider']    = 'Can edit an existing slider';
        $permissions['can_delete_slider']  = 'Can delete an existing slider';
        $permissions['can_restore_slider'] = 'Can restore a deleted slider';

        // --------------------------------------------------------------------------

        return $permissions;
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * The following block of code makes it simple to extend one of the core admin
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
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
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_CMS')) {

    /**
     * Proxy class for NAILS_Cms
     */
    class Cms extends NAILS_Cms
    {
    }
}
