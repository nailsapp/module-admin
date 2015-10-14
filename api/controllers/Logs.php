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

            $this->load->model('admin/admin_sitelog_model');

            // --------------------------------------------------------------------------

            $out['logs'] = $this->admin_sitelog_model->getAll();
        }

        return $out;
    }
}
