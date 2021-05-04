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

use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Model\Admin;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Service\Input;
use Nails\Factory;

/**
 * Class Nav
 *
 * @package Nails\Admin\Api\Controller
 */
class Nav extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /**
     * Saves the user's admin nav preferences
     *
     * @return Api\Factory\ApiResponse
     */
    public function postSave()
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Admin $oAdminModel */
        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        $aData    = $this->getRequestData();
        $aPrefRaw = array_filter((array) ($aData['preferences'] ?? []));

        $aPref = [];
        foreach ($aPrefRaw as $sModule => $aOptions) {
            $aPref[$sModule] = (object) [
                'order' => (int) ($aOptions['order'] ?? 0),
                'open'  => stringToBoolean($aOptions['open']),
            ];
        }

        $oAdminModel->setAdminData('nav_state', $aPref);

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Resets a user's admin nav preferences
     *
     * @return Api\Factory\ApiResponse
     */
    public function postReset()
    {
        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        $oAdminModel->unsetAdminData('nav_state');

        return $oApiResponse;
    }
}
