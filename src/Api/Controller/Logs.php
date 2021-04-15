<?php

/**
 * Admin API end points: logs
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
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Factory;

/**
 * Class Logs
 *
 * @package Nails\Admin\Api\Controller
 */
class Logs extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /**
     * Fetches site logs
     *
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getSite()
    {
        $oSiteLogModel = Factory::model('SiteLog', Constants::MODULE_SLUG);
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData($oSiteLogModel->getAll());

        return $oApiResponse;
    }
}
