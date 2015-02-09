<?php

/**
 * This class handles setting of notification recipients
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Notification extends \AdminController
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Notifications');
        $navGroup->addMethod('Manage Notifications');

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Constructs the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        $this->load->model('app_notification_model');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage who receives notifications
     * @return void
     */
    public function index()
    {
        //  Page Title
        $this->data['page']->title = 'Manage Notifications';

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

                    $this->data['success'] = 'Notifications were updated successfully.';

                } else {

                    $this->data['error'] = $error;
                }
            }
        }

        // --------------------------------------------------------------------------

        /**
         * Conditionally set this as this method may be overridden by the app to add
         * custom notification types
         */

        $this->data['notifications'] = $this->app_notification_model->getDefinitions();

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('index');
    }
}
