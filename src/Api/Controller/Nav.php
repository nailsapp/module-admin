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

namespace Nails\Admin\Api\Controller;

use Nails\Api\Controller\Base;
use Nails\Api\Exception\ApiException;
use Nails\Factory;

class Nav extends Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    public function __construct($oApiRouter)
    {
        parent::__construct($oApiRouter);
        if (!isAdmin()) {
            $oHttpCodes = Factory::service('HttpCodes');
            throw new ApiException(
                'You do not have permission to access this resource',
                $oHttpCodes::STATUS_UNAUTHORIZED
            );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Saves the user's admin nav preferences
     * @return array
     */
    public function postSave()
    {
        $oInput   = Factory::service('Input');
        $aPrefRaw = array_filter((array) $oInput->post('preferences'));
        $oPref    = new \stdClass();

        foreach ($aPrefRaw as $sModule => $aOptions) {
            $oPref->{$sModule}       = new \stdClass();
            $oPref->{$sModule}->open = stringToBoolean($aOptions['open']);
        }

        $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
        $oAdminModel->setAdminData('nav_state', $oPref);
        return Factory::factory('ApiResponse', 'nailsapp/module-api');
    }

    // --------------------------------------------------------------------------

    /**
     * Resets a user's admin nav preferences
     * @return void
     */
    public function postReset()
    {
        $oAdminModel = Factory::model('Admin', 'nailsapp/module-admin');
        $oAdminModel->unsetAdminData('nav_state');
        return Factory::factory('ApiResponse', 'nailsapp/module-api');
    }
}
