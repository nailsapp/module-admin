<?php

namespace Nails\Api\Admin;

/**
 * Admin API end points: Nav
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class Nav extends \ApiController
{
    public static $requiresAuthentication = true;

    // --------------------------------------------------------------------------

    private $isAuthorised;
    private $errorMsg;

    // --------------------------------------------------------------------------

    public function __construct() {

        parent::__construct();

        if (!$this->user_model->isAdmin()) {

            $this->isAuthorised = false;
            $this->errorMsg     = 'You must be an administrator.';

        } else {

            $this->isAuthorised = true;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Saves the user's admin nav preferences
     * @return array
     */
    public function postSave()
    {
        if (!$this->isAuthorised) {

            return array(
                'status' => 401,
                'error'  => $this->errorMsg
            );

        } else {

            $prefRaw = $this->input->post('preferences');
            $pref    = new \stdClass();

            foreach ($prefRaw as $module => $options) {

                $pref->{$module}       = new \stdClass();
                $pref->{$module}->open = stringToBoolean($options['open']);
            }

            $this->load->model('admin/admin_model');
            $this->admin_model->setAdminData('nav', $pref);

            return array();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Resets a user's admin nav preferences
     * @return void
     */
    public function postReset()
    {
        if (!$this->isAuthorised) {

            return array(
                'status' => 401,
                'error'  => $this->errorMsg
            );

        } else {

            $this->load->model('admin/admin_model');
            $this->admin_model->unsetAdminData('nav');

            return array();
        }
    }
}
