<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin: Email
 * Description:	Admin Email module
 *
 **/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Email extends NAILS_Admin_Controller
{

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access	static
	 * @param	none
	 * @return	void
	 **/
	static function announce()
	{
		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Load the laguage file
		get_instance()->lang->load( 'admin_email' );

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = lang( 'email_module_name' );
		$d->icon = 'fa-paper-plane-o';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs = array();

		if ( user_has_permission( 'admin.email:0.can_browse_archive' ) ) :

			$d->funcs['index'] = lang( 'email_nav_index' );

		endif;

		if ( user_has_permission( 'admin.email:0.can_manage_campaigns' ) ) :

			$d->funcs['campaign'] = lang( 'email_nav_campaign' );

		endif;

		// --------------------------------------------------------------------------

		return $d;
	}


	// --------------------------------------------------------------------------


	static function permissions( $class_index = NULL )
	{
		$_permissions = parent::permissions( $class_index );

		// --------------------------------------------------------------------------

		$_permissions['can_browse_archive']		= 'Can browse email archive';
		$_permissions['can_resend']				= 'Can resend email';
		$_permissions['can_compose']			= 'Can compose email';
		$_permissions['can_manage_campaigns']	= 'Can manage campaigns';
		$_permissions['can_create_campaign']	= 'Can create draft campaigns';
		$_permissions['can_send_campaign']		= 'Can send campaigns';
		$_permissions['can_delete_campaign']	= 'Can delete campaigns';

		// --------------------------------------------------------------------------

		return $_permissions;
	}


	// --------------------------------------------------------------------------


	/**
	 * Email archive browser
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function index()
	{
		if (!user_has_permission('admin.email:0.can_browse_archive')) {

			unauthorised();
		}

		// --------------------------------------------------------------------------

		//	Page Title
		$this->data['page']->title = lang('email_index_title');

		// --------------------------------------------------------------------------

		//	Fetch emails from the archive
		$offset  = $this->input->get('offset');
		$perPage = $this->input->get('per_page') ? $this->input->get('per_page') : 25;

		$this->data['emails']		= new stdClass();
		$this->data['emails']->data	= $this->emailer->get_all(null, 'DESC', $offset, $perPage);

		//	Work out pagination
		$this->data['emails']->pagination					= new stdClass();
		$this->data['emails']->pagination->total_results	= $this->emailer->count_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view('structure/header', $this->data);
		$this->load->view('admin/email/index', $this->data);
		$this->load->view('structure/footer', $this->data);
	}


	// --------------------------------------------------------------------------


	public function resend()
	{
		if (!user_has_permission('admin.email:0.can_resend')) {

			unauthorised();
		}

		// --------------------------------------------------------------------------

		$emailId = $this->uri->segment(4);
		$return  = $this->input->get('return') ? $this->input->get('return') : 'admin/email/index';

		if ($this->emailer->resend($emailId)) {

			$status  = 'success';
			$message = 'Message was resent successfully.';

		} else {

			$status  = 'error';
			$message = 'Message failed to resend.';
		}

		$this->session->Set_flashdata($status, $message);
		redirect($return);
	}


	// --------------------------------------------------------------------------


	/**
	 * Manage email campaigns
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function campaign()
	{
		if ( ! user_has_permission( 'admin.email:0.can_manage_campaigns' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Page Title
		$this->data['page']->title = lang( 'email_campaign_title' );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/email/campaign/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * The following block of code makes it simple to extend one of the core admin
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if ( ! defined( 'NAILS_ALLOW_EXTENSION_DASHBOARD' ) ) :

	class Email extends NAILS_Email
	{
	}

endif;


/* End of file email.php */
/* Location: ./modules/admin/controllers/email.php */