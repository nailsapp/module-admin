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

use Nails\Api\Controller\Base;
use Nails\Api\Exception\ApiException;
use Nails\Factory;

class Logs extends Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSite()
    {
        if (!isAdmin()) {
            $oHttpCodes = Factory::service('HttpCodes');
            throw new ApiException(
                'You do not have permission to access this resource',
                $oHttpCodes::STATUS_UNAUTHORIZED
            );
        }

        $oSiteLogModel = Factory::model('SiteLog', 'nailsapp/module-admin');
        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData($oSiteLogModel->getAll());
    }
}
