<?php

namespace Nails\Routes\Admin;

/**
 * Generates admin routes
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class Routes
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public function getRoutes()
    {
        $routes              = array();
        $routes['admin(.*)'] = 'admin/adminRouter/index$1';

        return $routes;
    }
}
