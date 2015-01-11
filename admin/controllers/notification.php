<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage app notifications
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */
class NAILS_Notification extends NAILS_Admin_Controller
{
    /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Load the laguage file
        get_instance()->lang->load('admin_notification');

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = lang('notification_module_name');
        $d->icon = 'fa-dot-circle-o';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs          = array();
        $d->funcs['index'] = lang('notification_nav_index');

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Constructs the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        $this->load->model('system/app_notification_model');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage who receives notifications
     * @return void
     */
    public function index()
    {
        //  Page Title
        $this->data['page']->title = lang('notification_index_title');

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $notification = $this->input->post('notification');

            if (is_array($notification)) {

                $set = array();

                foreach ($notification as $grouping => $options) {

                    $set[$grouping] = array();

                    foreach ($options as $key => $emails) {

                        $emails = explode(',', $emails);
                        $emails = array_filter($emails);
                        $emails = array_unique($emails);

                        foreach ($emails as &$email) {

                            $email = trim($email) ;

                            if (!valid_email($email)) {

                                $error = '"<strong>' . $email . '</strong>" is not a valid email.';
                                break 3;
                            }
                        }

                        $set[$grouping][$key] = $emails;
                    }
                }

                if (empty($error)) {

                    foreach ($set as $grouping => $options) {

                        $this->app_notification_model->set($options, $grouping);
                    }

                    $this->data['success'] = '<strong>Success!</strong> Notifications were updated successfully.';

                } else {

                    $this->data['error'] = $error;
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Conditionally set this as this method may be overridden by the app to add
        //  custom notification types

        $this->data['notifications'] = $this->app_notification_model->get_definitions();

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/notification/index', $this->data);
        $this->load->view('structure/footer', $this->data);
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
     * Proxy class for NAILS_Notification
     */
    class Notification extends NAILS_Notification
    {
    }
}
