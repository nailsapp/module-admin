<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin: Settings
 * Description:	A holder for all site settings
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

class NAILS_Settings extends NAILS_Admin_Controller
{
	/**
	 * Announces this module's details to anyone who asks.
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
		get_instance()->lang->load( 'admin_settings' );

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = lang( 'settings_module_name' );
		$d->icon = 'fa-wrench';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs			= array();
		$d->funcs['admin']	= lang( 'settings_nav_admin' );
		$d->funcs['site']	= lang( 'settings_nav_site' );

		if ( module_is_enabled( 'blog' ) ) :

			$d->funcs['blog'] = lang( 'settings_nav_blog' );

		endif;

		if ( module_is_enabled( 'email' ) ) :

			$d->funcs['email'] = lang( 'settings_nav_email' );

		endif;

		if ( module_is_enabled( 'shop' ) ) :

			$d->funcs['shop'] = lang( 'settings_nav_shop' );

		endif;

		// --------------------------------------------------------------------------

		//	Only announce the controller if the user has permisison to know about it
		return self::_can_access( $d, __FILE__ );
	}


	// --------------------------------------------------------------------------


	public function admin()
	{
		//	Set method info
		$this->data['page']->title = lang( 'settings_admin_title' );

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method =  $this->input->post( 'update' );

			if ( method_exists( $this, '_admin_update_' . $_method ) ) :

				$this->{'_admin_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings'] = app_setting( NULL, 'admin', TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/settings/admin',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _admin_update_branding()
	{
		//	Prepare update
		$_settings						= array();
		$_settings['primary_colour']	= $this->input->post( 'primary_colour' );
		$_settings['secondary_colour']	= $this->input->post( 'secondary_colour' );
		$_settings['highlight_colour']	= $this->input->post( 'highlight_colour' );

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'admin' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Admin branding settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin branding settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _admin_update_whitelist()
	{
		//	Prepare the whitelist
		$_whitelist_raw	= $this->input->post( 'whitelist' );
		$_whitelist_raw	= str_replace( "\n\r", "\n", $_whitelist_raw );
		$_whitelist_raw	= explode( "\n", $_whitelist_raw );
		$_whitelist		= array();

		foreach ( $_whitelist_raw AS $line ) :

			$_whitelist = array_merge( explode( ',', $line ), $_whitelist );

		endforeach;

		$_whitelist = array_unique( $_whitelist );
		$_whitelist = array_filter( $_whitelist );
		$_whitelist = array_map( 'trim', $_whitelist );
		$_whitelist = array_values( $_whitelist );

		//	Prepare update
		$_settings				= array();
		$_settings['whitelist']	= $_whitelist;

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'admin' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Admin whitelist settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin whitelist settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Configure Site settings
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function site()
	{
		//	Set method info
		$this->data['page']->title = lang( 'settings_site_title' );

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method =  $this->input->post( 'update' );

			if ( method_exists( $this, '_site_update_' . $_method ) ) :

				$this->{'_site_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings'] = app_setting( NULL, 'app', TRUE );

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->load( 'nails.admin.site.settings.min.js', TRUE );
		$this->asset->inline( '<script>_nails_settings = new NAILS_Admin_Site_Settings();</script>' );

		$this->load->library( 'auth/social_signon' );
		$this->data['providers'] = $this->social_signon->get_providers();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/settings/site',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _site_update_analytics()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['google_analytics_account']	= $this->input->post( 'google_analytics_account' );

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'app' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Site settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _site_update_auth()
	{
		$this->load->library( 'auth/social_signon' );
		$_providers = $this->social_signon->get_providers();

		// --------------------------------------------------------------------------

		//	Prepare update
		$_settings				= array();
		$_settings_encrypted	= array();

		$_settings['user_registration_enabled']	= $this->input->post( 'user_registration_enabled' );

		//	Disable social signon, if any providers are proeprly enabled it'll turn itself on again.
		$_settings['auth_social_signon_enabled'] = FALSE;

		foreach( $_providers AS $provider ) :

			$_settings['auth_social_signon_' . $provider['slug'] . '_enabled'] = (bool) $this->input->post( 'auth_social_signon_' . $provider['slug'] . '_enabled' );

			if ( $_settings['auth_social_signon_' . $provider['slug'] . '_enabled'] ) :

				//	NULL out each key
				if ( $provider['fields'] ) :

					foreach( $provider['fields'] AS $key => $label ) :

						if ( is_array( $label ) && ! isset( $label['label'] ) ) :

							foreach ( $label AS $key1 => $label1 ) :

								$_value = $this->input->post( 'auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1 );

								if ( ! empty( $label1['required'] ) && empty( $_value ) ) :

									$_error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label1['label'] . '".';
									break 3;

								endif;

								if (  empty( $label1['encrypted'] ) ) :

									$_settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $_value;

								else :

									$_settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $_value;

								endif;

							endforeach;

						else :

							$_value = $this->input->post( 'auth_social_signon_' . $provider['slug'] . '_' . $key );

							if ( ! empty( $label['required'] ) && empty( $_value ) ) :

								$_error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label['label'] . '".';
								break 2;

							endif;

							if (  empty( $label['encrypted'] ) ) :

								$_settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = $_value;

							else :

								$_settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key] = $_value;

							endif;

						endif;

					endforeach;

				endif;

				//	Turn on social signon
				$_settings['auth_social_signon_enabled'] = TRUE;

			else :

				//	NULL out each key
				if ( $provider['fields'] ) :

					foreach( $provider['fields'] AS $key => $label ) :

						/**
						 * Secondary conditional detects an actual array fo fields rather than
						 * just the label/required array. Design could probably be improved...
						 **/

						if ( is_array( $label ) && ! isset( $label['label'] ) ) :

							foreach ( $label AS $key1 => $label1 ) :

								$_settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = NULL;

							endforeach;

						else :

							$_settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = NULL;

						endif;

					endforeach;

				endif;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		//	Save
		if ( empty( $_error ) ) :

			$this->db->trans_begin();
			$_rollback = FALSE;

			if ( ! empty( $_settings ) ) :

				if ( ! $this->app_setting_model->set( $_settings, 'app' ) ) :

					$_error		= $this->app_setting_model->last_error();
					$_rollback	= TRUE;

				endif;

			endif;

			if ( ! empty( $_settings_encrypted ) ) :

				if ( ! $this->app_setting_model->set( $_settings_encrypted, 'app', NULL, TRUE ) ) :

					$_error		= $this->app_setting_model->last_error();
					$_rollback	= TRUE;

				endif;

			endif;

			if ( $_rollback ) :

				$this->db->trans_rollback();
				$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $_error;

			else :

				$this->db->trans_commit();
				$this->data['success'] = '<strong>Success!</strong> Authentication settings were saved.';

			endif;

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $_error;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _site_update_maintenance()
	{
		//	Prepare the whitelist
		$_whitelist_raw	= $this->input->post( 'maintenance_mode_whitelist' );
		$_whitelist_raw	= str_replace( "\n\r", "\n", $_whitelist_raw );
		$_whitelist_raw	= explode( "\n", $_whitelist_raw );
		$_whitelist		= array();

		foreach ( $_whitelist_raw AS $line ) :

			$_whitelist = array_merge( explode( ',', $line ), $_whitelist );

		endforeach;

		$_whitelist = array_unique( $_whitelist );
		$_whitelist = array_filter( $_whitelist );
		$_whitelist = array_map( 'trim', $_whitelist );
		$_whitelist = array_values( $_whitelist );

		//	Prepare update
		$_settings									= array();
		$_settings['maintenance_mode_enabled']		= (bool) $this->input->post( 'maintenance_mode_enabled' );
		$_settings['maintenance_mode_whitelist']	= $_whitelist;

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'app' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Maintenance settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving maintenance settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Configure the blog
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function blog()
	{
		if ( ! module_is_enabled( 'blog' ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = lang( 'settings_blog_title' );

		// --------------------------------------------------------------------------

		//	Load models
		$this->load->model( 'blog/blog_model' );
		$this->load->model( 'blog/blog_skin_model' );

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method =  $this->input->post( 'update' );

			if ( method_exists( $this, '_blog_update_' . $_method ) ) :

				$this->{'_blog_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings'] = app_setting( NULL, 'blog', TRUE );
		$this->data['skins']	= $this->blog_skin_model->get_available();

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->load( 'nails.admin.blog.settings.min.js', TRUE );
		$this->asset->inline( '<script>_nails_settings = new NAILS_Admin_Blog_Settings();</script>' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/settings/blog',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blog_update_settings()
	{
		//	Prepare update
		$_settings							= array();
		$_settings['name']					= $this->input->post( 'name' );
		$_settings['url']					= $this->input->post( 'url' );
		$_settings['use_excerpts']			= (bool) $this->input->post( 'use_excerpts' );
		$_settings['gallery_enabled']		= (bool) $this->input->post( 'gallery_enabled' );
		$_settings['categories_enabled']	= (bool) $this->input->post( 'categories_enabled' );
		$_settings['tags_enabled']			= (bool) $this->input->post( 'tags_enabled' );
		$_settings['rss_enabled']			= (bool) $this->input->post( 'rss_enabled' );

		// --------------------------------------------------------------------------

		//	Sanitize blog url
		$_settings['url'] .= substr( $_settings['url'], -1 ) != '/' ? '/' : '';

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'blog' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Blog settings have been saved.';

			$this->load->model( 'system/routes_model' );
			if ( ! $this->routes_model->update( 'shop' ) ) :

				$this->data['warning'] = '<strong>Warning:</strong> while the blog settings were updated, the routes file could not be updated. The blog may not behave as expected,';

			endif;

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _blog_update_skin()
	{
		//	Prepare update
		$_settings			= array();
		$_settings['skin']	= $this->input->post( 'skin' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'blog' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _blog_update_commenting()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['comments_enabled']			= $this->input->post( 'comments_enabled' );
		$_settings['comments_engine']			= $this->input->post( 'comments_engine' );
		$_settings['comments_disqus_shortname']	= $this->input->post( 'comments_disqus_shortname' );

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'blog' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Blog commenting settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving commenting settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _blog_update_social()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['social_facebook_enabled']	= (bool) $this->input->post( 'social_facebook_enabled' );
		$_settings['social_twitter_enabled']	= (bool) $this->input->post( 'social_twitter_enabled' );
		$_settings['social_twitter_via']		= $this->input->post( 'social_twitter_via' );
		$_settings['social_googleplus_enabled']	= (bool) $this->input->post( 'social_googleplus_enabled' );
		$_settings['social_pinterest_enabled']	= (bool) $this->input->post( 'social_pinterest_enabled' );
		$_settings['social_skin']				= $this->input->post( 'social_skin' );
		$_settings['social_layout']				= $this->input->post( 'social_layout' );
		$_settings['social_layout_single_text']	= $this->input->post( 'social_layout_single_text' );
		$_settings['social_counters']			= (bool) $this->input->post( 'social_counters' );

		//	If any of the above are enabled, then social is enabled.
		$_settings['social_enabled'] = $_settings['social_facebook_enabled'] || $_settings['social_twitter_enabled'] || $_settings['social_googleplus_enabled'] || $_settings['social_pinterest_enabled'];

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'blog' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Blog social settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving social settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _blog_update_sidebar()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['sidebar_latest_posts']		= (bool) $this->input->post( 'sidebar_latest_posts' );
		$_settings['sidebar_categories']		= (bool) $this->input->post( 'sidebar_categories' );
		$_settings['sidebar_tags']				= (bool) $this->input->post( 'sidebar_tags' );
		$_settings['sidebar_popular_posts']		= (bool) $this->input->post( 'sidebar_popular_posts' );

		//	TODO: Associations

		// --------------------------------------------------------------------------

		//	Save
		if ( $this->app_setting_model->set( $_settings, 'blog' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Blog sidebar settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving sidebar settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Configure the blog
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function email()
	{
		//	Set method info
		$this->data['page']->title = lang( 'settings_email_title' );

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method =  $this->input->post( 'update' );

			if ( method_exists( $this, '_email_update_' . $_method ) ) :

				$this->{'_email_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings'] = app_setting( NULL, 'email', TRUE );

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->load( 'nails.admin.email.settings.min.js', TRUE );
		$this->asset->inline( '<script>_nails_settings = new NAILS_Admin_Email_Settings();</script>' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/settings/email',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _email_update_customise()
	{
		//	Prepare update
		$_settings					= array();
		$_settings['from_name']		= $this->input->post( 'from_name' );
		$_settings['from_email']	= $this->input->post( 'from_email' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'email' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Email customisation settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _email_update_driver()
	{
		//	Prepare update
		$_settings					= array();
		$_settings_encrypted		= array();

		$_environments		= array();
		$_environments[]	= 'DEVELOPMENT';
		$_environments[]	= 'STAGING';
		$_environments[]	= 'PRODUCTION';

		foreach ( $_environments AS $environment ) :

			$_settings[$environment . '_driver']		= $this->input->post( $environment . '_driver' );
			$_settings[$environment . '_smtp_host']		= $this->input->post( $environment . '_smtp_host' );
			$_settings[$environment . '_smtp_username']	= $this->input->post( $environment . '_smtp_username' );
			$_settings[$environment . '_smtp_port']		= $this->input->post( $environment . '_smtp_port' );

			$_settings_encrypted[$environment . '_smtp_password']		= $this->input->post( $environment . '_smtp_password' );
			$_settings_encrypted[$environment . '_mandrill_api_key']	= $this->input->post( $environment . '_mandrill_api_key' );

		endforeach;

		$this->db->trans_begin();
		$_rollback = FALSE;

		if ( ! $this->app_setting_model->set( $_settings, 'email' ) ) :

			$_error		= $this->app_setting_model->last_error();
			$_rollback	= TRUE;

		endif;

		if ( ! $this->app_setting_model->set( $_settings_encrypted, 'email', NULL, TRUE ) ) :

			$_error		= $this->app_setting_model->last_error();
			$_rollback	= TRUE;

		endif;

		if ( $_rollback ) :

			$this->db->trans_rollback();
			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving email driver settings. ' . $_error;

		else :

			$this->db->trans_commit();
			$this->data['success'] = '<strong>Success!</strong> Email driver settings were saved.';

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Configure the shop
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function shop()
	{
		if ( ! module_is_enabled( 'shop' ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = lang( 'settings_shop_title' );

		// --------------------------------------------------------------------------

		//	Load models
		$this->load->model( 'shop/shop_model' );
		$this->load->model( 'shop/shop_currency_model' );
		$this->load->model( 'shop/shop_shipping_driver_model' );
		$this->load->model( 'shop/shop_payment_gateway_model' );
		$this->load->model( 'shop/shop_tax_rate_model' );
		$this->load->model( 'shop/shop_skin_model' );
		$this->load->model( 'system/country_model' );

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method =  $this->input->post( 'update' );

			if ( method_exists( $this, '_shop_update_' . $_method ) ) :

				$this->{'_shop_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings']					= app_setting( NULL, 'shop', TRUE );
		$this->data['payment_gateways']			= $this->shop_payment_gateway_model->get_available();
		$this->data['shipping_drivers']			= $this->shop_shipping_driver_model->get_available();
		$this->data['skins']					= $this->shop_skin_model->get_available();
		$this->data['skin_selected']			= $_selected_skin = app_setting( 'skin', 'shop' ) ? app_setting( 'skin', 'shop' ) : 'skin-shop-gettingstarted';
		$this->data['skin_current']				= $this->shop_skin_model->get( $this->data['skin_selected'] );
		$this->data['currencies']				= $this->shop_currency_model->get_all( );
		$this->data['tax_rates']				= $this->shop_tax_rate_model->get_all();
		$this->data['tax_rates_flat']			= $this->shop_tax_rate_model->get_all_flat();
		$this->data['countries_flat']			= $this->country_model->get_all_flat();
		$this->data['continents_flat']			= $this->country_model->get_all_continents_flat();
		array_unshift( $this->data['tax_rates_flat'], 'No Tax');

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->load( 'nails.admin.shop.settings.min.js',	TRUE );
		$this->asset->load( 'mustache.js/mustache.js',				'BOWER' );
		$this->asset->inline( '<script>_nails_settings = new NAILS_Admin_Shop_Settings();</script>' );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/settings/shop',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_settings()
	{
		//	Prepare update
		$_settings											= array();
		$_settings['name']									= $this->input->post( 'name' );
		$_settings['url']									= $this->input->post( 'url' );
		$_settings['price_exclude_tax']						= $this->input->post( 'price_exclude_tax' );
		$_settings['invoice_company']						= $this->input->post( 'invoice_company' );
		$_settings['invoice_company']						= $this->input->post( 'invoice_company' );
		$_settings['invoice_address']						= $this->input->post( 'invoice_address' );
		$_settings['invoice_vat_no']						= $this->input->post( 'invoice_vat_no' );
		$_settings['invoice_company_no']					= $this->input->post( 'invoice_company_no' );
		$_settings['warehouse_collection_enabled']			= (bool) $this->input->post( 'warehouse_collection_enabled' );
		$_settings['warehouse_addr_addressee']				= $this->input->post( 'warehouse_addr_addressee' );
		$_settings['warehouse_addr_line1']					= $this->input->post( 'warehouse_addr_line1' );
		$_settings['warehouse_addr_line2']					= $this->input->post( 'warehouse_addr_line2' );
		$_settings['warehouse_addr_town']					= $this->input->post( 'warehouse_addr_town' );
		$_settings['warehouse_addr_postcode']				= $this->input->post( 'warehouse_addr_postcode' );
		$_settings['warehouse_addr_state']					= $this->input->post( 'warehouse_addr_state' );
		$_settings['warehouse_addr_country']				= $this->input->post( 'warehouse_addr_country' );
		$_settings['warehouse_collection_delivery_enquiry']	= (bool) $this->input->post( 'warehouse_collection_delivery_enquiry' );
		$_settings['page_brand_listing']					= $this->input->post( 'page_brand_listing' );
		$_settings['page_category_listing']					= $this->input->post( 'page_category_listing' );
		$_settings['page_collection_listing']				= $this->input->post( 'page_collection_listing' );
		$_settings['page_range_listing']					= $this->input->post( 'page_range_listing' );
		$_settings['page_sale_listing']						= $this->input->post( 'page_sale_listing' );
		$_settings['page_tag_listing']						= $this->input->post( 'page_tag_listing' );


		// --------------------------------------------------------------------------

		//	Sanitize shop url
		$_settings['url'] .= substr( $_settings['url'], -1 ) != '/' ? '/' : '';

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Store settings have been saved.';

			// --------------------------------------------------------------------------

			//	Rewrite routes
			$this->load->model( 'system/routes_model' );
			if ( ! $this->routes_model->update( 'shop' ) ) :

				$this->data['warning'] = '<strong>Warning:</strong> while the shop settings were updated, the routes file could not be updated. The shop may not behave as expected,';

			endif;

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_browse()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['expand_variants']			= (bool) $this->input->post( 'expand_variants' );
		$_settings['default_product_per_page']	= $this->input->post( 'default_product_per_page' );
		$_settings['default_product_per_page']	= is_numeric( $_settings['default_product_per_page'] ) ? (int) $_settings['default_product_per_page'] : $_settings['default_product_per_page'];
		$_settings['default_product_sort']		= $this->input->post( 'default_product_sort' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Browsing settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_skin()
	{
		//	Prepare update
		$_settings			= array();
		$_settings['skin']	= $this->input->post( 'skin' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_skin_config()
	{
		//	Prepare update
		$_settings	= array();
		$_configs	= (array) $this->input->post( 'skin_config' );
		$_configs	= array_filter( $_configs );

		foreach( $_configs AS $key => $value ) :

			$_settings[$key] = $value;

		endforeach;

		// --------------------------------------------------------------------------

		//	Clear out the grouping
		$this->app_setting_model->delete_group( 'shop-' . $this->input->post( 'skin_slug' ) );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop-' . $this->input->post( 'skin_slug' ) ) ) :

			$this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_payment_gateway()
	{
		//	Prepare update
		$_settings								= array();
		$_settings['enabled_payment_gateways']	= array_filter( (array) $this->input->post( 'enabled_payment_gateways' ) );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Payment Gateway settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_currencies()
	{
		//	Prepare update
		$_settings							= array();
		$_settings['base_currency']			= $this->input->post( 'base_currency' );
		$_settings['additional_currencies']	= $this->input->post( 'additional_currencies' );

		$_settings_encrypted								= array();
		$_settings_encrypted['openexchangerates_app_id']	= $this->input->post( 'openexchangerates_app_id' );

		// --------------------------------------------------------------------------

		$this->db->trans_begin();
		$_rollback = FALSE;

		if ( ! $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$_error		= $this->app_setting_model->last_error();
			$_rollback	= TRUE;

		endif;

		if ( ! $this->app_setting_model->set( $_settings_encrypted, 'shop', NULL, TRUE ) ) :

			$_error		= $this->app_setting_model->last_error();
			$_rollback	= TRUE;

		endif;

		if ( $_rollback ) :

			$this->db->trans_rollback();
			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving currency settings. ' . $_error;

		else :

			$this->db->trans_commit();
			$this->data['success'] = '<strong>Success!</strong> Currency settings were saved.';

			// --------------------------------------------------------------------------

			//	If there are multiple currencies and an Open Exchange Rates App ID provided
			//	then attempt a sync

			if ( ! empty( $_settings['additional_currencies'] ) && ! empty( $_settings_encrypted['openexchangerates_app_id'] ) ) :

				$this->load->model( 'shop/shop_currency_model' );

				if ( ! $this->shop_currency_model->sync() ) :

					$this->data['message'] = '<strong>Warning:</strong> an attempted sync with Open Exchange Rates service failed with the following reason: ' . $this->shop_currency_model->last_error();

				else :

					$this->data['notice'] = '<strong>Currency Sync Complete.</strong><br />The system successfully synced with the Open Exchange Rates service.';

				endif;

			endif;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_update_shipping()
	{
		//	Prepare update
		$_settings								= array();
		// $_settings['domicile']					= $this->input->post( 'domicile' );
		// $_settings['ship_to_continents']		= array_filter( (array) $this->input->post( 'ship_to_continents' ) );
		// $_settings['ship_to_countries']			= array_filter( (array) $this->input->post( 'ship_to_countries' ) );
		// $_settings['ship_to_exclude']			= array_filter( (array) $this->input->post( 'ship_to_exclude' ) );
		$_settings['enabled_shipping_driver']	= $this->input->post( 'enabled_shipping_driver' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Shipping settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	public function shop_pg()
	{
		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : '';

		if ( method_exists( $this, '_shop_pg_' . strtolower( $_method ) ) ) :

			$this->{'_shop_pg_' . strtolower( $_method )}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _shop_pg_worldpay()
	{
		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'omnipay_WorldPay_installationId',	'', 'xss_clean|required' );
			$this->form_validation->set_rules( 'omnipay_WorldPay_accountId',		'', 'xss_clean|required' );
			$this->form_validation->set_rules( 'omnipay_WorldPay_secretWord',		'', 'xss_clean' );
			$this->form_validation->set_rules( 'omnipay_WorldPay_callbackPassword',	'', 'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_settings_encrypted										= array();
				$_settings_encrypted['omnipay_WorldPay_installationId']		= $this->input->post( 'omnipay_WorldPay_installationId' );
				$_settings_encrypted['omnipay_WorldPay_accountId']			= $this->input->post( 'omnipay_WorldPay_accountId' );
				$_settings_encrypted['omnipay_WorldPay_secretWord']			= $this->input->post( 'omnipay_WorldPay_secretWord' );
				$_settings_encrypted['omnipay_WorldPay_callbackPassword']	= $this->input->post( 'omnipay_WorldPay_callbackPassword' );

				if ( $this->app_setting_model->set( $_settings_encrypted, 'shop', NULL, TRUE ) ) :

					$this->data['success'] = '<strong>Success!</strong> Shipping settings have been saved.';

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Shop Payment Gateway Configuration &rsaquo; WorldPay';

		// --------------------------------------------------------------------------

		if ( $this->input->get( 'is_fancybox' ) ) :

			$this->data['header_override'] = 'structure/header/nails-admin-blank';
			$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

		endif;

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/settings/shop_pg/worldpay',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
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
 * CodeIgniter instantiates a class with the same name as the file, therefore
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

if ( ! defined( 'NAILS_ALLOW_EXTENSION_SETTINGS' ) ) :

	class Settings extends NAILS_Settings
	{
	}

endif;


/* End of file settings.php */
/* Location: ./modules/admin/controllers/settings.php */