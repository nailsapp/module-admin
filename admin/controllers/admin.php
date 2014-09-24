<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin
 * Description:	Exists purely to redirect users to the dashboard. Must exist and cannot be overloaded
 *
 **/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

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

/* End of file admin.php */
/* Location: ./modules/admin/controllers/admin.php */