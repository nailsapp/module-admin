<?php


class NAILS_Cms extends NAILS_Admin_Controller
{
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
