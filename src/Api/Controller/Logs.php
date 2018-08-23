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

use Nails\Admin\Controller\BaseApi;
use Nails\Api\Exception\ApiException;
use Nails\Factory;

class Logs extends BaseApi
{
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Determines whether the user is authenticated or not
     *
     * @param string $sHttpMethod The HTTP Method protocol being used
     * @param string $sMethod     The controller method being executed
     *
     * @return bool
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        return parent::isAuthenticated($sHttpMethod, $sMethod) && isAdmin();
    }

    // --------------------------------------------------------------------------

    /**
     * Fetches site logs
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getSite()
    {
        $oSiteLogModel = Factory::model('SiteLog', 'nailsapp/module-admin');
        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData($oSiteLogModel->getAll());
    }
}
