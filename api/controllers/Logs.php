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

namespace Nails\Api\Admin;

use Nails\Factory;

class Logs extends \Nails\Api\Controller\Base
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
        $out = [];

        if (!isAdmin()) {

            return [
                'status' => 401,
                'error'  => 'You must be an administrator.',
            ];

        } else {

            $oSiteLogModel = Factory::model('SiteLog', 'nailsapp/module-admin');
            $out['logs']   = $oSiteLogModel->getAll();
        }

        return $out;
    }
}
