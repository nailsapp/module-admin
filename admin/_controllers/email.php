<?php

class NAILS_Email extends NAILS_Admin_Controller
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

        $permissions['can_browse_archive']   = 'Can browse email archive';
        $permissions['can_resend']           = 'Can resend email';
        $permissions['can_compose']          = 'Can compose email';
        $permissions['can_manage_campaigns'] = 'Can manage campaigns';
        $permissions['can_create_campaign']  = 'Can create draft campaigns';
        $permissions['can_send_campaign']    = 'Can send campaigns';
        $permissions['can_delete_campaign']  = 'Can delete campaigns';

        // --------------------------------------------------------------------------

        return $permissions;
    }
}
