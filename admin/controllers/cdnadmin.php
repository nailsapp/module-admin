<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Admin: CDN
* Description:	CDN manager
*
*/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Cdnadmin extends NAILS_Admin_Controller
{

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access static
	 * @param none
	 * @return void
	 **/
	static function announce()
	{
		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = 'CDN';
		$d->icon = 'fa-cloud-upload';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs['browse']	= 'Browse Objects';

		if ( user_has_permission( 'admin.cdnadmin:0.can_browse_trash' ) ) :

			$d->funcs['trash']	= 'Browse Trash';

		endif;

		// --------------------------------------------------------------------------

		//	Only announce the controller if the user has permission to know about it
		return $d;
	}


	// --------------------------------------------------------------------------


	static function permissions( $class_index = NULL )
	{
		$_permissions = parent::permissions( $class_index );

		// --------------------------------------------------------------------------

		$_permissions['can_upload']			= 'Can upload items';
		$_permissions['can_edit']			= 'Can edit items';
		$_permissions['can_delete']			= 'Can delete items';
		$_permissions['can_browse_trash']	= 'Can browse trash';
		$_permissions['can_empty_trash']	= 'Can empty trash';

		// --------------------------------------------------------------------------

		return $_permissions;
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function browse()
	{
		$this->data['page']->title = 'Browse Objects';

		// --------------------------------------------------------------------------

		//	Define the $_data variable, this'll be passed to the get_all() and count_all() methods
		$_data = array( 'where' => array(), 'sort' => array() );

		// --------------------------------------------------------------------------

		//	Set useful vars
		$_page			= $this->input->get( 'page' )		? $this->input->get( 'page' )		: 0;
		$_per_page		= $this->input->get( 'per_page' )	? $this->input->get( 'per_page' )	: 25;
		$_sort_on		= $this->input->get( 'sort_on' )	? $this->input->get( 'sort_on' )	: 'o.id';
		$_sort_order	= $this->input->get( 'order' )		? $this->input->get( 'order' )		: 'desc';
		$_search		= $this->input->get( 'search' )		? $this->input->get( 'search' )		: '';

		//	Set sort variables for view and for $_data
		$this->data['sort_on']		= $_data['sort']['column']	= $_sort_on;
		$this->data['sort_order']	= $_data['sort']['order']	= $_sort_order;
		$this->data['search']		= $_data['search']			= $_search;

		//	Define and populate the pagination object
		$this->data['pagination']				= new stdClass();
		$this->data['pagination']->page			= $_page;
		$this->data['pagination']->per_page		= $_per_page;
		$this->data['pagination']->total_rows	= $this->cdn->count_all_objects( $_data );

		$this->data['objects'] = $this->cdn->get_objects( $_page, $_per_page, $_data );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/cdn/browse',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function trash()
	{
		if ( ! user_has_permission( 'admin.cdnadmin:0.can_browse_trash' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Browse Trash';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/cdn/trash',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function create()
	{
		if ( ! user_has_permission( 'admin.cdnadmin:0.can_upload' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Upload Items';

		// --------------------------------------------------------------------------

		$this->data['buckets'] = $this->cdn->get_buckets();

		// --------------------------------------------------------------------------

		if ( $this->input->get( 'is_fancybox' ) ) :

			$this->data['header_override'] = 'structure/header/nails-admin-blank';
			$this->data['footer_override'] = 'structure/header/nails-admin-blank';

		endif;

		// --------------------------------------------------------------------------

		$this->asset->load( 'nails.admin.cdn.upload.min.js', 'NAILS' );
		$this->asset->load( 'dropzone/downloads/css/dropzone.css', 'BOWER' );
		$this->asset->load( 'dropzone/downloads/css/basic.css', 'BOWER' );
		$this->asset->load( 'dropzone/downloads/dropzone.min.js', 'BOWER' );
		$this->asset->inline( 'var _upload = new NAILS_Admin_CDN_Upload();', 'JS' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/cdn/create',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function edit()
	{
		if ( ! user_has_permission( 'admin.cdnadmin:0.can_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Edit Object';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/cdn/edit',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function delete()
	{
		if ( ! user_has_permission( 'admin.cdnadmin:0.can_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_return = $this->input->get( 'return' ) ? $this->input->get( 'return' ) : 'admin/cdnadmin/browse';
		$this->session->set_flashdata( 'message', '<strong>TODO:</strong> Delete objects from admin' );
		redirect( $_return );
	}


	// --------------------------------------------------------------------------


	/**
	 * Browse CDN buckets and objects
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function purge()
	{
		if ( ! user_has_permission( 'admin.cdnadmin:0.can_empty_trash' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_return = $this->input->get( 'return' ) ? $this->input->get( 'return' ) : 'admin/cdnadmin/trash';
		$this->session->set_flashdata( 'message', '<strong>TODO:</strong> empty trash' );
		redirect( $_return );
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

if ( ! defined( 'NAILS_ALLOW_EXTENSION_CDN' ) ) :

	class Cdnadmin extends NAILS_Cdnadmin
	{
	}

endif;

/* End of file cdnadmin.php */
/* Location: ./modules/admin/controllers/cdn.php */