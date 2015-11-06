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
    public static $requiresAuthentication = true;

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSite()
    {
        $out = array();

        if (!$this->user_model->isAdmin()) {

            return array(
                'status' => 401,
                'error'  => 'You must be an administrator.'
            );

        } else {

            $oSiteLogModel = Factory::model('SiteLog', 'nailsapp/module-admin');
            $out['logs']   = $oSiteLogModel->getAll();
        }

        return $out;
    }
}
