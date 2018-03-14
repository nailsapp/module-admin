<?php

/**
 * Admin API end points: Nav
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Api\Admin;

use Nails\Factory;

class Nav extends \Nails\Api\Controller\Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    private $isAuthorised;
    private $errorMsg;

    // --------------------------------------------------------------------------

    public function __construct($apiRouter)
    {

        parent::__construct($apiRouter);

        if (!isAdmin()) {

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

            return [
                'status' => 401,
                'error'  => $this->errorMsg,
            ];

        } else {

            $oInput   = Factory::service('Input');
            $aPrefRaw = $oInput->post('preferences');
            $oPref    = new \stdClass();

            foreach ($aPrefRaw as $sModule => $aOptions) {
                $oPref->{$sModule}       = new \stdClass();
                $oPref->{$sModule}->open = stringToBoolean($aOptions['open']);
            }

            $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
            $oAdminModel->setAdminData('nav_state', $oPref);

            return [];
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

            return [
                'status' => 401,
                'error'  => $this->errorMsg,
            ];

        } else {

            $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
            $oAdminModel->unsetAdminData('nav_state');

            return [];
        }
    }
}
