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

class Logs extends Base
{
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
