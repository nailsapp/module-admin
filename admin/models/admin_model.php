<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin Model
 *
 * Description:	This model contains some basic common admin functionality.
 *
 */

/**
 * OVERLOADING NAILS' MODELS
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Admin_Model extends NAILS_Model
{
	protected $_search_paths;


	// --------------------------------------------------------------------------


	/**
	 * Constructor; set the defaults
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		/**
		 * Set the search paths to look for modules within; paths listed first
		 * take priority over those listed after it.
		 *
		 **/

		$this->_search_paths[] = FCPATH . APPPATH . 'modules/admin/controllers/';	//	Admin controllers specific for this app only.
		$this->_search_paths[] = NAILS_PATH . 'module-admin/admin/controllers/';
	}


	// --------------------------------------------------------------------------


	/**
	 * Look for modules which reside within the search paths.
	 *
	 * @access	public
	 * @param	string	$module	The name of the module to search for
	 * @return	stdClass
	 **/
	public function find_module( $module )
	{
		$_out = array();

		// --------------------------------------------------------------------------

		//	Look in our search paths for a controller of the same name as the module.

		foreach ( $this->_search_paths AS $path ) :

			if ( file_exists( $path . $module . '.php' ) ) :

				require_once $path . $module . '.php';

				$_out = $module::announce();

				if ( ! is_array( $_out ) ) :

					$_out = array( $_out );

				endif;

				$_out = array_filter( $_out );

				if ( $_out ) :

					if ( ! is_array( $_out ) ) :

						$_out = array( $_out );

					endif;

					foreach ( $_out AS $index => &$out ) :

						//	If there're no methods then remove it
						if ( empty( $out->funcs ) ) :

							$out = NULL;

						else :

							//	Basics
							$out->class_name	= $module;
							$out->class_index	= $module . ':' . $index;

							//	Any extra permissions?
							$out->extra_permissions = $module::permissions( $out->class_index );

						endif;

					endforeach;

				endif;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		$_out = array_filter( $_out );
		$_out = array_values( $_out );

		return $_out;
	}


	// --------------------------------------------------------------------------


	/**
	 * Loop through the enabled modules and see if a controller exists for it; if
	 * it does load it up and execute the announce static method to see if we can
	 * display it to the active user.
	 * @return array
	 */
	public function get_active_modules()
	{
		$_cache_key	= 'available_admin_modules_' . active_user( 'id' );
		$_cache		= $this->_get_cache( $_cache_key );

		if ( $_cache ) :

			return $_cache;

		endif;

		// --------------------------------------------------------------------------

		$_modules_potential		= _NAILS_GET_POTENTIAL_MODULES();
		$_modules_unavailable	= _NAILS_GET_UNAVAILABLE_MODULES();
		$_modules_available		= array();

		// --------------------------------------------------------------------------

		//	Look for controllers
		//	[0] => Path to search
		//	[1] => Whether to test against $_modules_unavailable

		$_paths		= array();
		$_paths[]	= array( NAILS_PATH . 'module-admin/admin/controllers/',	TRUE );
		$_paths[]	= array( FCPATH . APPPATH . 'modules/admin/controllers/',	FALSE );

		//	Filter out non PHP files
		$_regex = '/^[^_][a-zA-Z_]+\.php$/';

		//	Load directory helper
		$this->load->helper( 'directory' );

		foreach ( $_paths AS $path ) :

			$_controllers = directory_map( $path[0] );

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

		//	Form the discovered modules into a more structured array
		$_loaded_modules = array();

		foreach( $_modules_available AS $module ) :

			$_module = $this->find_module( $module );

			if ( ! empty( $_module ) ) :

				foreach( $_module AS $module ) :

					$_loaded_modules[$module->class_index] = $module;

				endforeach;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		/**
		 * If the user has a custom order specified then use that, otherwise fall back to
		 * sort alphabetically by name.
		 */

		$_user_nav_pref = $this->admin_model->get_admin_data( 'nav' );
		$_out			= array();

		if ( ! empty( $_user_nav_pref ) ) :

			//	User's preference first
			foreach( $_user_nav_pref AS $module => $options ) :

				if ( ! empty( $_loaded_modules[$module] ) ) :

					$_out[$module] = $_loaded_modules[$module];

				endif;

			endforeach;

			//	Anything left over goes to the end.
			foreach( $_loaded_modules AS $module ) :

				if ( ! isset( $_out[$module->class_index] ) ) :

					$_out[$module->class_index] = $module;

				endif;

			endforeach;

		else :

			$this->load->helper( 'array' );
			array_sort_multi( $_loaded_modules, 'name' );

			foreach( $_loaded_modules AS $module ) :

				$_out[$module->class_index] = $module;

			endforeach;

		endif;

		// --------------------------------------------------------------------------

		/**
		 * Place the dashboard at the top of the list and settings & utilities at
		 * the end, always.
		 * Hit tip: http://stackoverflow.com/a/11276338/789224
		 */

		if ( isset( $_out['dashboard:0'] ) ) :

			$_out = array( 'dashboard:0' => $_out['dashboard:0'] ) + $_out;

		endif;

		if ( isset( $_out['settings:0'] ) ) :

			$_item = $_out['settings:0'];
			unset( $_out['settings:0'] );
			$_out = $_out + array( 'settings:0' => $_item );

		endif;

		if ( isset( $_out['utilities:0'] ) ) :

			$_item = $_out['utilities:0'];
			unset( $_out['utilities:0'] );
			$_out = $_out + array( 'utilities:0' => $_item );

		endif;

		$_out = array_values( $_out );

		// --------------------------------------------------------------------------

		//	Permissions
		//	===========

		/**
		 * Admin modules are opt in (i.e non super users must explicitly be granted
		 * access). Loop through all potential modules and remoe any which are not
		 * available to the currently active user. Super users can see everything.
		 */

		if ( ! $this->user_model->is_superuser() ) :

			/**
			 * Loop through each available module and remove any which don't feature
			 * in the user's ACL.
			 */

			$_acl = active_user( 'acl' );

			for ( $i = 0; $i < count( $_out ); $i++ ) :

				//	Dashboard is *always* allowed
				if ( $_out[$i]->class_name != 'dashboard' ) :

					/**
					 * Dealing with a module which is *not* the dashboard, is it
					 * featured in the user's ACL? If not, remove.
					 */

					if ( ! isset( $_acl['admin'][$_out[$i]->class_index] ) ) :

						//	See ya, bye.
						$_out[$i] = NULL;

					endif;

				endif;

			endfor;

		endif;

		// --------------------------------------------------------------------------

		$_out = array_filter( $_out );
		$_out = array_values( $_out );

		// --------------------------------------------------------------------------

		$this->_set_cache( $_cache_key, $_out );

		// --------------------------------------------------------------------------

		return $_out;
	}


	// --------------------------------------------------------------------------


	/**
	 * Returns the currently active admin module
	 * @return mixed	stdClass on success NULL failue or permission denied
	 */
	public function get_current_module()
	{
		$_modules		= $this->get_active_modules();
		$_cur_module	= $this->uri->segment( 2, 'admin' );
		$_current		= NULL;

		foreach ( $_modules AS $m ) :

			if ( $m->class_name == $_cur_module ) :

				$_current = $m;
				break;

			endif;

		endforeach;

		return $_current;
	}


	// --------------------------------------------------------------------------


	/**
	 * Sets a piece of admin data
	 * @param  string  $key     The key to set
	 * @param  mixed   $value   The value to set
	 * @param  mixed   $user_id The user's ID, if NULL active user is used.
	 * @return boolean
	 */
	public function set_admin_data( $key, $value, $user_id = NULL )
	{
		return $this->_setunset_admin_data( $key, $value, $user_id, TRUE );
	}


	// --------------------------------------------------------------------------


	/**
	 * Unsets a piece of admin data
	 * @param  string  $key     The key to set
	 * @param  mixed   $user_id The user's ID, if NULL active user is used.
	 * @return boolean
	 */
	public function unset_admin_data( $key, $user_id = NULL )
	{
		return $this->_setunset_admin_data( $key, NULL, $user_id, FALSE );
	}


	// --------------------------------------------------------------------------


	/**
	 * Handles the setting and unsetting of admin data
	 * @param  string  $key     The key to set
	 * @param  mixed   $value   The value to set
	 * @param  mixed   $user_id The user's ID, if NULL active user is used.
	 * @param  boolean $set     Whether the data is being set or unset
	 * @return boolean
	 */
	protected function _setunset_admin_data( $key, $value, $user_id, $set )
	{
		//	Get the user ID
		$_user_id = $this->_admindata_get_user_id( $user_id );

		//	Get the existing data for this user
		$_existing = $this->get_admin_data( NULL, $_user_id );

		if ( $set ) :

			//	Set the new key
			$_existing[$key] = $value;

		else :

			//	Unset the existing key
			unset( $_existing[$key] );

		endif;

		//	Save to the DB
		$_data = array( 'admin_data' => serialize( $_existing ) );
		if ( $this->user_model->update( $_user_id, $_data ) ) :

			//	Save to the cache
			$this->_set_cache( 'admin-data-' . $_user_id, $_existing );
			return TRUE;

		else :

			return FALSE;

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Gets items from the admin data, or the entire array of $key is NULL
	 * @param  string $key     The key to set
	 * @param  mixed  $user_id The user's ID, if NULL active user is used.
	 * @return mixed
	 */
	public function get_admin_data( $key = NULL, $user_id = NULL )
	{
		//	Get the user ID
		$_user_id = $this->_admindata_get_user_id( $user_id );

		//	Check if data is already in the cache
		$_cache_key	= 'admin-data-' . $_user_id;
		$_cache		= $this->_get_cache( $_cache_key );

		if ( $_cache ) :

			$_data = $_cache;

		else :

			$this->db->select( 'admin_data' );
			$this->db->where( 'id', $_user_id );
			$_result = $this->db->get( NAILS_DB_PREFIX . 'user' )->row();

			if ( ! isset( $_result->admin_data ) ) :

				$_data = array();

			else :

				$_data = unserialize( $_result->admin_data );

			endif;

			$this->_set_cache( $_cache_key, $_data );

		endif;

		// --------------------------------------------------------------------------

		/**
		 * If no key is returned, return the entire data array, alternatively return
		 * the key if it exists.
		 */

		if ( is_null( $key ) ) :

			return $_data;

		elseif ( isset( $_data[$key] ) ) :

			return $_data[$key];

		else :

			return NULL;

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Completely clears out the admin array
	 * @param  mixed   $user_id The user's ID, if NULL active user is used.
	 * @return boolean
	 */
	public function clear_admin_data( $user_id )
	{
		//	Get the user ID
		$_user_id = $this->_admindata_get_user_id( $user_id );

		$_data = array( 'admin_data' => NULL );
		if ( $this->user_model->update( $_user_id, $_data ) ) :

			$this->_unset_cache( 'admin-data-' . $_user_id );
			return TRUE;

		else :

			return FALSE;

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Extracts the user ID to use
	 * @param  int $user_id The User ID, or NULL for active user
	 * @return int
	 */
	protected function _admindata_get_user_id( $user_id )
	{
		if ( is_null( $user_id ) ) :

			$_user_id = active_user( 'id' );

		else :

			$_user_id = $user_id;

		endif;

		return $_user_id;
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' MODELS
 *
 * The following block of code makes it simple to extend one of the core Nails
 * models. Some might argue it's a little hacky but it's a simple 'fix'
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
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if ( ! defined( 'NAILS_ALLOW_EXTENSION_ADMIN_MODEL' ) ) :

	class Admin_model extends NAILS_Admin_model
	{
	}

endif;

/* End of file admin_model.php */
/* Location: ./modules/admin/models/admin_model.php */