<?php

class NAILS_Logs extends NAILS_Admin_Controller
{
    /**
     * Returns an array of extra permissions for this controller
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        $permissions = parent::permissions($classIndex);

        // --------------------------------------------------------------------------

        $permissions['can_browse_site_logs']    = 'Can browse site logs';
        $permissions['can_browse_event_logs']   = 'Can browse event logs';
        $permissions['can_download_event_logs'] = 'Can download event logs';
        $permissions['can_browse_change_logs']   = 'Can browse change logs';
        $permissions['can_download_change_logs'] = 'Can download change logs';

        // --------------------------------------------------------------------------

        return $permissions;
    }
}
