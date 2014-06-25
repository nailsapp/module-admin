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
		$_ip_whitelist = json_decode( APP_ADMIN_IP_WHITELIST );

		if ( $_ip_whitelist ) :

			if ( ! ip_in_range( $this->input->ip_address(), $_ip_whitelist ) ) :

				show_404();

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Admins only please
		if ( ! $this->user_model->is_admin() ) :

			unauthorised();

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
		if ( file_exists( FCPATH . APPPATH . 'config/admin.php' ) ) :

			$this->config->load( 'admin' );

		endif;

		// --------------------------------------------------------------------------

		//	Load up the modules which have been enabled for this installation and the
		//	user has permission to see.

		$this->_loaded_modules			= array();
		$this->data['loaded_modules']	=& $this->_loaded_modules;
		$this->_load_active_modules();

		// --------------------------------------------------------------------------

		//	Check the user has permission to view this module (skip the dashboard
		//	we need to show them _something_)

		$_active_module	= $this->uri->segment( 2 );
		$_active_method	= $this->uri->segment( 3, 'index' );
		$_acl			= active_user( 'acl' );

		if ( ! $this->user_model->is_superuser() && ! isset( $this->_loaded_modules[$_active_module] ) ) :

			//	If this is the dashboard, we should see if the user has permission to
			//	access any other modules before we 404 their ass.

			if ( $_active_module == 'dashboard' || $_active_module == '' ) :

				//	Look at the user's ACL
				if ( isset( $_acl['admin'] )  ) :

					//	If they have other modules defined, loop them until one is found
					//	which appears in the loaded modules list. If this doesn't happen
					//	then they'll fall back to the 'no loaded modules' page.

					foreach( $_acl['admin'] AS $module => $methods ) :

						if ( isset( $this->_loaded_modules[$module] ) ) :

							redirect( 'admin/' . $module );
							break;

						endif;

					endforeach;

				endif;

			else :

				// Oh well, it's not, 404 bitches!
				show_404();

			endif;

		elseif ( ! $this->user_model->is_superuser() ) :

			//	Module is OK, check to make sure they can access this method
			if ( ! isset( $_acl['admin'][$_active_module][$_active_method] ) ) :

				unauthorised();

			endif;

		elseif ( $this->user_model->is_superuser() ) :

			//	OK, this user is a super user and has been given a free ticket
			//	thus far. However, if the module isn't available then even these
			//	guys are bang out of luck.

			$_modules_potential		= get_potential_modules();
			$_modules_unavailable	= get_unavailable_modules();

			if ( array_search( 'module-' . $_active_module, $_modules_potential ) !== FALSE ) :

				if ( array_search( 'module-' . $_active_module, $_modules_unavailable ) !== FALSE ) :

					show_404();

				endif;

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load libraries
		$this->load->library( 'cdn/cdn' );

		// --------------------------------------------------------------------------

		//	Add the current module to the $page variable (for convenience)
		$this->data['page'] = new stdClass();

		if ( isset( $this->_loaded_modules[ $this->uri->segment( 2 ) ] ) ) :

			$this->data['page']->module = $this->_loaded_modules[ $this->uri->segment( 2 ) ];

		else :

			$this->data['page']->moduled = FALSE;

		endif;

		// --------------------------------------------------------------------------

		//	Unload any previously loaded assets, admin handles it's own assets
		$this->asset->clear_all();

		//	CSS
		$this->asset->load( 'fancybox/source/jquery.fancybox.css',		'BOWER' );
		$this->asset->load( 'jquery-toggles/toggles.css',				'BOWER' );
		$this->asset->load( 'jquery-toggles/themes/toggles-modern.css',	'BOWER' );
		$this->asset->load( 'tipsy/src/stylesheets/tipsy.css',			'BOWER' );
		$this->asset->load( 'ionicons/css/ionicons.min.css',			'BOWER' );
		$this->asset->load( 'nails.admin.css',							TRUE );

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
		$_acl		= active_user( 'acl' );
		$_module	= basename( $file, '.php' );

		// --------------------------------------------------------------------------

		//	Super users can see what they like
		if ( get_userobject()->is_superuser() ) :

			return $module;

		endif;

		// --------------------------------------------------------------------------

		//	Everyone else needs to have the correct ACL
		if ( isset( $_acl['admin'][$_module] ) ) :

			return $module;

		else :

			return NULL;

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Loop through the enabled modules and see if a controller exists for it; if
	 * it does load it up and execute the announce static method to see if we can
	 * display it to the active user.
	 *
	 * @access	public
	 * @return	void
	 *
	 **/
	private function _load_active_modules()
	{
		$_modules_potential		= get_potential_modules();
		$_modules_unavailable	= get_unavailable_modules();
		$_modules_available		= array();

		// --------------------------------------------------------------------------

		//	Look for controllers
		//	[0] => Path to search
		//	[1] => Whether to test against $_modules_unavailable

		$_paths		= array();
		$_paths[]	= array( NAILS_PATH . 'module-admin/admin/controllers/',	TRUE );
		$_paths[]	= array( FCPATH . APPPATH . 'modules/admin/controllers/',	FALSE );

		//	Filter out non PHP files
		$_regex = '/^[a-zA-Z]+\.php$/';

		foreach ( $_paths AS $path ) :

			$_controllers = @scandir( $path[0] );

			if ( is_array( $_controllers ) ) :

				foreach ( $_controllers AS $controller ) :

					if ( preg_match( $_regex, $controller ) ) :

						$_module = pathinfo( $controller );
						$_module = $_module['filename'];

						if ( ! empty( $path[1] ) ) :

							//	Module looks valid, is it a potential module, and if so, is it available?
							if ( array_search( 'nailsapp/module-' . $_module, $_modules_potential ) !== FALSE ) :

								if ( array_search( 'nailsapp/module-' . $_module, $_modules_unavailable ) !== FALSE ) :

									//	Not installed
									continue;

								endif;

							endif;

						endif;

						// --------------------------------------------------------------------------

						$_modules_available[] = $_module;

					endif;

				endforeach;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		$_loaded_modules = array();

		foreach( $_modules_available AS $module ) :

			$_module =  $this->admin_model->find_module( $module );

			if ( ! empty( $_module ) ) :

				$_loaded_modules[$module] = $_module;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		//	If the user has a custom order specified then use that, otherwise fall back to
		//	APP_ADMIN_NAV if it's defined. Failing that, sort alphabetically by name.

		$_user_nav_pref = @unserialize( active_user( 'admin_nav' ) );

		if ( defined( 'APP_ADMIN_NAV' ) && APP_ADMIN_NAV ) :

			//	Can set a default app wide preference if needed,
			//	takes the same form as the user's preference

			$_app_nav_pref = @unserialize( APP_ADMIN_NAV );

		endif;

		if ( ! empty( $_user_nav_pref ) ) :

			//	User's preference first
			foreach( $_user_nav_pref AS $module => $options ) :

				if ( ! empty( $_loaded_modules[$module] ) ) :

					$this->_loaded_modules[$module] = $_loaded_modules[$module];

				endif;

			endforeach;

			//	Anything left over goes to the end.
			foreach( $_loaded_modules AS $module ) :

				if ( ! isset( $this->_loaded_modules[$module->class_name] ) ) :

					$this->_loaded_modules[$module->class_name] = $module;

				endif;

			endforeach;

		elseif ( ! empty( $_app_nav_pref ) ) :

			foreach( $_app_nav_pref AS $module => $options ) :

				if ( ! empty( $_loaded_modules[$module] ) ) :

					$this->_loaded_modules[$module] = $_loaded_modules[$module];

				endif;

			endforeach;

			//	Anything left over goes to the end.
			foreach( $_loaded_modules AS $module ) :

				if ( ! isset( $this->_loaded_modules[$module->class_name] ) ) :

					$this->_loaded_modules[$module->class_name] = $module;

				endif;

			endforeach;

		else :

			$this->load->helper( 'array' );
			array_sort_multi( $_loaded_modules, 'name' );

			foreach( $_loaded_modules AS $module ) :

				$this->_loaded_modules[$module->class_name] = $module;

			endforeach;

		endif;

		// --------------------------------------------------------------------------

		//	Place the dashboard at the top of the list, always
		//	Hit tip: http://stackoverflow.com/a/11276338/789224

		$this->_loaded_modules = array( 'dashboard' => $this->_loaded_modules['dashboard']) + $this->_loaded_modules;
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the announce() static method
	 *
	 * @access	public
	 * @return	NULL
	 *
	 **/
	static function announce()
	{
		return NULL;
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the notifications() static method
	 *
	 * @access	public
	 * @return	array
	 *
	 **/
	static function notifications()
	{
		return array();
	}


	// --------------------------------------------------------------------------


	/**
	 * Basic definition of the permissions() static method
	 *
	 * @access	public
	 * @return	array
	 *
	 **/
	static function permissions()
	{
		return array();
	}
}