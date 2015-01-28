<?php

//  Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * This class does nothing but is required for ModularExtensions to behave as expected
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class Admin extends NAILS_Admin_Controller
{
    public function index()
    {
        /**
         * This method will never actally be reached; _admin.php handles the blank admin
         * route and will forcibly redirect to the admin dashboard if a blank route is used.
         */
    }
}
