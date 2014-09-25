<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:			Admin_Controller
 *
 * Description:	This controller executes various bits of common admin functionality
 *
 **/


class NAILS_Admin_Controller extends NAILS_Controller
{
	protected $_loaded_modules;


	// --------------------------------------------------------------------------


	/**
	 * Common constructor for all admin pages
	 *
	 * @access	public
	 * @return	void
	 *
	 **/
	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		//	IP whitelist?
		$_ip_whitelist = (array) app_setting( 'whitelist', 'admin' );

		if ( $_ip_whitelist ) :

			if ( ! ip_in_range( $this->input->ip_address(), $_ip_whitelist ) ) :

				show_404();

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Admins only please, log in or bog off.
		if ( ! $this->user_model->is_admin() ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		/**
		 * Handle the blank admin route, redirect to the dashboard which will always
		 * be available.
		 */

		if ( $this->uri->segment( 2, 'BLANKADMINROUTE' ) == 'BLANKADMINROUTE' ) :

			$this->session->keep_flashdata();
			redirect( 'admin/dashboard' );

		endif;

		// --------------------------------------------------------------------------

		//	Load up the generic admin langfile
		$this->lang->load( 'admin_generic' );

		// --------------------------------------------------------------------------

		//	Check that admin is running on the SECURE_BASE_URL url
		if ( APP_SSL_ROUTING ) :

			$_host1 = $this->input->server( 'HTTP_HOST' );
			$_host2 = parse_url( SECURE_BASE_URL );

			if ( ! empty( $_host2['host'] ) && $_host2['host'] != $_host1 ) :

				//	Not on the secure URL, redirect with message
				$_redirect = $this->input->server( 'REQUEST_URI' );

				if ( $_redirect ) :

					$this->session->set_flashdata( 'message', lang( 'admin_not_secure' ) );
					redirect( $_redirect );

				endif;

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load admin helper and config
		$this->load->model( 'admin_model' );
		$this->config->load( 'admin/admin' );

		//	App admin config
		if ( file_exists( FCPATH . APPPATH . 'config/admin.php' ) ) :

			$this->config->load( FCPATH . APPPATH . 'config/admin.php' );

		endif;

		// --------------------------------------------------------------------------

		/**
		 * Fetch all available modules for this installation and get the user's ACL.
		 * Make sure the user has permission to access this module.
		 */

		$this->_loaded_modules			= array();
		$this->data['loaded_modules']	=& $this->_loaded_modules;

		//	Fetch all available modules for this installation and user
		$this->_loaded_modules		= $this->admin_model->get_active_modules();
		$this->data['has_modules']	= count( $this->_loaded_modules ) ? TRUE : FALSE;

		//	Fetch the current module, if this is NULL then it means no access
		$this->_current_module = $this->admin_model->get_current_module();

		if ( is_null( $this->_current_module ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load libraries
		$this->load->library( 'cdn/cdn' );

		// --------------------------------------------------------------------------

		//	Add the current module to the $page variable (for convenience)
		$this->data['page']->module	= $this->_current_module;

		// --------------------------------------------------------------------------

		//	Unload any previously loaded assets, admin handles its own assets
		$this->asset->clear();

		//	CSS
		$this->asset->load( 'fancybox/source/jquery.fancybox.css',			'BOWER' );
		$this->asset->load( 'jquery-toggles/css/toggles.css',				'BOWER' );
		$this->asset->load( 'jquery-toggles/css/themes/toggles-modern.css',	'BOWER' );
		$this->asset->load( 'tipsy/src/stylesheets/tipsy.css',				'BOWER' );
		$this->asset->load( 'fontawesome/css/font-awesome.min.css',			'BOWER' );
		$this->asset->load( 'nails.admin.css',								TRUE );

		//	JS
		$this->asset->load( 'jquery/dist/jquery.min.js',				'BOWER' );
		$this->asset->load( 'fancybox/source/jquery.fancybox.pack.js',	'BOWER' );
		$this->asset->load( 'jquery-toggles/toggles.min.js',			'BOWER' );
		$this->asset->load( 'tipsy/src/javascripts/jquery.tipsy.js',	'BOWER' );
		$this->asset->load( 'jquery.scrollTo/jquery.scrollTo.min.js',	'BOWER' );
		$this->asset->load( 'jquery-cookie/jquery.cookie.js',			'BOWER' );
		$this->asset->load( 'nails.default.min.js',						TRUE );
		$this->asset->load( 'nails.admin.min.js',						TRUE );
		$this->asset->load( 'nails.forms.min.js',						TRUE );
		$this->asset->load( 'nails.api.min.js',							TRUE );

		//	Libraries
		$this->asset->library( 'jqueryui' );
		$this->asset->library( 'select2' );
		$this->asset->library( 'ckeditor' );
		$this->asset->library( 'uploadify' );

		//	Look for any Admin styles provided by the app
		if ( file_exists( FCPATH . 'assets/css/admin.css' ) ) :

			$this->asset->load( 'admin.css' );

		endif;

		//	Inline assets
		$_js  = 'var _nails,_nails_admin,_nails_forms;';
		$_js .= '$(function(){';

		$_js .= 'if ( typeof( NAILS_JS ) === \'function\' ){';
		$_js .= '_nails = new NAILS_JS();';
		$_js .= '_nails.init();';
		$_js .= '}';

		$_js .= 'if ( typeof( NAILS_Admin ) === \'function\' ){';
		$_js .= '_nails_admin = new NAILS_Admin();';
		$_js .= '_nails_admin.init();';
		$_js .= '}';

		$_js .= 'if ( typeof( NAILS_Forms ) === \'function\' ){';
		$_js .= '_nails_forms = new NAILS_Forms();';
		$_js .= '}';

		$_js .= 'if ( typeof( NAILS_API ) === \'function\' ){';
		$_js .= '_nails_api = new NAILS_API();';
		$_js .= '}';

		$_js .= '});';

		$this->asset->inline( '<script>' . $_js . '</script>' );

		// --------------------------------------------------------------------------

		//	Initialise the admin models
		$this->load->model( 'admin_help_model' );
		$this->load->model( 'admin_changelog_model' );
	}


	// --------------------------------------------------------------------------


	/**
	 * Determines whether the active_user() can access the specified module
	 *
	 * @access	static
	 * @param	$module A reference to the module definition
	 * @param	$file The file we're checking
	 * @return	mixed
	 *
	 **/
	static function _can_access( &$module, $file )
	{
		/**
		 * Backwards compatability; this method is deprecated, the appropriate admin
		 * models will determine if the module is accessible.
		 */

		return $module;
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the announce() static method
	 * @return NULL
	 */
	static function announce()
	{
		return NULL;
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the notifications() static method
	 * @param  string $class_index The class_index value, used when multiple admin instances are available
	 * @return array
	 */
	static function notifications( $class_index = NULL )
	{
		return array();
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the permissions() static method
	 * @param  string $class_index The class_index value, used when multiple admin instances are available
	 * @return array
	 */
	static function permissions( $class_index = NULL )
	{
		return array();
	}
}