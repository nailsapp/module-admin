<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage email sent by the system
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class NAILS_Email extends NAILS_Admin_Controller
{
    /**
     * Announces this controller's details
     * @return stdClass
     */
    public static function announce()
    {
        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Load the laguage file
        get_instance()->lang->load('admin_email');

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = lang('email_module_name');
        $d->icon = 'fa-paper-plane-o';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs = array();

        if (user_has_permission('admin.email:0.can_browse_archive')) {

            $d->funcs['index'] = lang('email_nav_index');
        }

        if (user_has_permission('admin.email:0.can_manage_campaigns')) {

            $d->funcs['campaign'] = lang('email_nav_campaign');
        }

        // --------------------------------------------------------------------------

        return $d;
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

if (!defined('NAILS_ALLOW_EXTENSION_DASHBOARD')) {

    /**
     * Proxy class for NAILS_Email
     */
    class Email extends NAILS_Email
    {
    }
}
