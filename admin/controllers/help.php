<?php

/**
 * This class renders the admin help section
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Help extends \AdminController
{
   /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = parent::announce();
        get_instance()->load->model('admin_help_model');

        if (get_instance()->admin_help_model->count_all()) {

            $d[''] = array('Dashboard', 'Help Videos');
        }

        return $d;
    }
}
