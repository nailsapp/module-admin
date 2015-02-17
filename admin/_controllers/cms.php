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
}
