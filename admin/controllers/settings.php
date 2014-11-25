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

		return $d;
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

		//	Load models
		$this->load->model( 'blog/blog_model' );
		$this->load->model( 'blog/blog_skin_model' );

		// --------------------------------------------------------------------------

		//	Catch blog adding/editing
		switch ( $this->uri->segment( 4 ) ) :

			case 'index' :

				$this->_blog_index();
				return;

			break;

			case 'create' :

				$this->_blog_create();
				return;

			break;

			case 'edit' :

				$this->_blog_edit();
				return;

			break;

			case 'delete' :

				$this->_blog_delete();
				return;

			break;

		endswitch;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = lang( 'settings_blog_title' );

		// --------------------------------------------------------------------------

		$this->data['blogs'] = $this->blog_model->get_all_flat();

		if ( empty( $this->data['blogs'] ) ) :

			if ( $this->user_model->is_superuser() ) :

				$this->session->set_flashdata( 'message', '<strong>You don\'t have a blog!</strong> Create a new blog in order to configure blog settings.' );
				redirect( 'admin/settings/blog/create' );

			else :

				show_404();

			endif;

		endif;

		if ( count( $this->data['blogs'] ) == 1 ) :

			reset( $this->data['blogs'] );
			$this->data['selected_blog'] = key( $this->data['blogs'] );

		elseif ( $this->input->get( 'blog_id' ) ) :

			if ( ! empty( $this->data['blogs'][$this->input->get( 'blog_id' )] ) ) :

				$this->data['selected_blog'] = $this->input->get( 'blog_id' );

			endif;

			if ( empty( $this->data['selected_blog'] ) ) :

				$this->data['error'] = '<strong>Sorry,</strong> there is no blog by that ID.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$_method = $this->input->post( 'update' );

			if ( method_exists( $this, '_blog_update_' . $_method ) ) :

				$this->{'_blog_update_' . $_method}();

			else :

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['skins'] = $this->blog_skin_model->get_available();

		if ( ! empty( $this->data['selected_blog'] ) ) :

			$this->data['settings'] = app_setting( NULL, 'blog-' . $this->data['selected_blog'], TRUE );

		endif;

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


	protected function _blog_index()
	{
		if ( ! $this->user_model->is_superuser() ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Manage Blogs';

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['blogs'] = $this->blog_model->get_all();

		if ( empty( $this->data['blogs'] ) ) :

			if ( $this->user_model->is_superuser() ) :

				$this->session->set_flashdata( 'message', '<strong>You don\'t have a blog!</strong> Create a new blog in order to configure blog settings.' );
				redirect( 'admin/settings/blog/create' );

			else :

				show_404();

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/settings/blog/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blog_create()
	{
		if ( ! $this->user_model->is_superuser() ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Manage Blogs &rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Handle POST
		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );
			$this->form_validation->set_rules( 'label', '', 'xss_clean|required' );
			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data			= new stdClass();
				$_data->label	= $this->input->post( 'label' );

				$_id = $this->blog_model->create( $_data );

				if ( $_id ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Blog was created successfully, now please confirm blog settings.' );
					redirect( 'admin/settings/blog?blog_id=' . $_id );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> failed to create blog. ' . $this->blog_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/settings/blog/edit',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blog_edit()
	{
		if ( ! $this->user_model->is_superuser() ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['blog'] = $this->blog_model->get_by_id( $this->uri->segment( 5 ) );

		if ( empty( $this->data['blog'] ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you specified an invalid Blog ID.' );
			redirect( 'admin/settings/blog/index' );

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Manage Blogs &rsaquo; Edit "' . $this->data['blog']->label . '"';

		// --------------------------------------------------------------------------

		//	Handle POST
		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );
			$this->form_validation->set_rules( 'label', '', 'xss_clean|required' );
			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data			= new stdClass();
				$_data->label	= $this->input->post( 'label' );

				if ( $this->blog_model->update( $this->uri->Segment( 5 ), $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Blog was updated successfully.' );
					redirect( 'admin/settings/blog/index' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> failed to create blog. ' . $this->blog_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/settings/blog/edit',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blog_delete()
	{
		if ( ! $this->user_model->is_superuser() ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_blog = $this->blog_model->get_by_id( $this->uri->segment( 5 ) );

		if ( empty( $_blog ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you specified an invalid Blog ID.' );
			redirect( 'admin/settings/blog/index' );

		endif;

		// --------------------------------------------------------------------------

		if ( $this->blog_model->delete( $_blog->id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> blog was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> failed to delete blog. ' . $this->blog_model->last_error() );

		endif;

		redirect( 'admin/settings/blog/index' );
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
		if ( $this->app_setting_model->set( $_settings, 'blog-' . $this->input->get( 'blog_id' ) ) ) :

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

		if ( $this->app_setting_model->set( $_settings, 'blog-' . $this->input->get( 'blog_id' ) ) ) :

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
		if ( $this->app_setting_model->set( $_settings, 'blog-' . $this->input->get( 'blog_id' ) ) ) :

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
		if ( $this->app_setting_model->set( $_settings, 'blog-' . $this->input->get( 'blog_id' ) ) ) :

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
		if ( $this->app_setting_model->set( $_settings, 'blog-' . $this->input->get( 'blog_id' ) ) ) :

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
		$this->data['page']->title = lang('settings_email_title');

		// --------------------------------------------------------------------------

		//	Process POST
		if ($this->input->post()) {

			$method = $this->input->post('update');
			if (method_exists($this, '_email_update_' . $method)) {

				$this->{'_email_update_' . $method}();

			} else {

				$this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
			}
		}

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['settings'] = app_setting(null, 'email', true);

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->load('nails.admin.email.settings.min.js', true);
		$this->asset->inline('<script>_nails_settings = new NAILS_Admin_Email_Settings();</script>');

		// --------------------------------------------------------------------------

		$this->load->view('structure/header', $this->data);
		$this->load->view('admin/settings/email', $this->data);
		$this->load->view('structure/footer', $this->data);
	}


	// --------------------------------------------------------------------------


	protected function _email_update_general()
	{
		//	Prepare update
		$settings					= array();
		$settings['from_name']		= $this->input->post('from_name');
		$settings['from_email']	= $this->input->post('from_email');

		// --------------------------------------------------------------------------

		if ($this->app_setting_model->set($settings, 'email')) {

			$this->data['success'] = '<strong>Success!</strong> General email settings have been saved.';

		} else {

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
		}
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
		$this->load->model( 'shop/shop_skin_front_model' );
		$this->load->model( 'shop/shop_skin_checkout_model' );
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
		$this->data['currencies']				= $this->shop_currency_model->get_all( );
		$this->data['tax_rates']				= $this->shop_tax_rate_model->get_all();
		$this->data['tax_rates_flat']			= $this->shop_tax_rate_model->get_all_flat();
		$this->data['countries_flat']			= $this->country_model->get_all_flat();
		$this->data['continents_flat']			= $this->country_model->get_all_continents_flat();
		array_unshift( $this->data['tax_rates_flat'], 'No Tax');

		//	"Front of house" skins
		$this->data['skins_front']				= $this->shop_skin_front_model->get_available();
		$this->data['skin_front_selected']		= app_setting( 'skin_front', 'shop' ) ? app_setting( 'skin_front', 'shop' ) : 'shop-skin-front-classic';
		$this->data['skin_front_current']		= $this->shop_skin_front_model->get( $this->data['skin_front_selected'] );

		//	"Checkout" skins
		$this->data['skins_checkout']			= $this->shop_skin_checkout_model->get_available();
		$this->data['skin_checkout_selected']	= app_setting( 'skin_checkout', 'shop' ) ? app_setting( 'skin_checkout', 'shop' ) : 'shop-skin-checkout-classic';
		$this->data['skin_checkout_current']	= $this->shop_skin_checkout_model->get( $this->data['skin_checkout_selected'] );

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
		$_settings['enable_external_products']				= (bool) $this->input->post( 'enable_external_products' );
		$_settings['invoice_company']						= $this->input->post( 'invoice_company' );
		$_settings['invoice_company']						= $this->input->post( 'invoice_company' );
		$_settings['invoice_address']						= $this->input->post( 'invoice_address' );
		$_settings['invoice_vat_no']						= $this->input->post( 'invoice_vat_no' );
		$_settings['invoice_company_no']					= $this->input->post( 'invoice_company_no' );
		$_settings['invoice_footer']						= $this->input->post( 'invoice_footer' );
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
		$_settings					= array();
		$_settings['skin_front']	= $this->input->post( 'skin_front' );
		$_settings['skin_checkout']	= $this->input->post( 'skin_checkout' );

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
		$_configs	= (array) $this->input->post( 'skin_config' );
		$_configs	= array_filter( $_configs );
		$_success	= TRUE;

		foreach( $_configs AS $slug => $configs ) :

			//	Clear out the grouping; booleans not specified should be assumed FALSE
			$this->app_setting_model->delete_group( 'shop-' . $slug );

			//	New settings
			$_settings = array();
			foreach( $configs AS $key => $value ) :

				$_settings[$key] = $value;

			endforeach;

			if ( $_settings ) :

				if ( ! $this->app_setting_model->set( $_settings, 'shop-' . $slug ) ) :

					$_success = FALSE;
					break;

				endif;

			endif;

		endforeach;

		// --------------------------------------------------------------------------

		if ( $_success ) :

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
		$_settings['enabled_shipping_driver']	= $this->input->post( 'enabled_shipping_driver' );

		// --------------------------------------------------------------------------

		if ( $this->app_setting_model->set( $_settings, 'shop' ) ) :

			$this->data['success'] = '<strong>Success!</strong> Shipping settings have been saved.';

		else :

			$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Configure the Payment Gateways
	 * @return void
	 */
	public function shop_pg()
	{
		//	Check if valid gateway
		$this->load->model( 'shop/shop_payment_gateway_model' );

		$_gateway	= $this->uri->segment( 4 ) ? strtolower( $this->uri->segment( 4 ) ) : '';
		$_available = $this->shop_payment_gateway_model->is_available( $_gateway );

		if ( $_available ) :

			$_params = $this->shop_payment_gateway_model->get_default_params( $_gateway );

			$this->data['params']		= $_params;
			$this->data['gateway_name']	= ucwords( str_replace( '_', ' ', $_gateway ) );
			$this->data['gateway_slug']	= $this->shop_payment_gateway_model->get_correct_casing( $_gateway );

			//	Handle POST
			if ( $this->input->post() ) :

				$this->load->library( 'form_validation' );

				foreach ( $_params AS $key => $value ) :

					if ( $key == 'testMode' ) :

						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_' . $key, '', 'xss_clean' );

					else :

						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_' . $key, '', 'xss_clean|required' );

					endif;

				endforeach;

				//	Additional params
				switch( $_gateway ) :

					case 'paypal_express' :

						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_brandName',			'', 'xss_clean' );
						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_headerImageUrl',	'', 'xss_clean' );
						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_logoImageUrl',		'', 'xss_clean' );
						$this->form_validation->set_rules( 'omnipay_' . $this->data['gateway_slug'] . '_borderColor',		'', 'xss_clean' );

					break;

				endswitch;

				$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

				if ( $this->form_validation->run() ) :

					$_settings				= array();
					$_settings_encrypted	= array();

					//	Customisation params
					$_settings['omnipay_' . $this->data['gateway_slug'] . '_customise_label']	= $this->input->post( 'omnipay_' . $this->data['gateway_slug'] . '_customise_label' );
					$_settings['omnipay_' . $this->data['gateway_slug'] . '_customise_img']		= $this->input->post( 'omnipay_' . $this->data['gateway_slug'] . '_customise_img' );

					//	Gateway params
					foreach ( $_params AS $key => $value ) :

						$_settings_encrypted['omnipay_' . $this->data['gateway_slug'] . '_' . $key] = $this->input->post( 'omnipay_' . $this->data['gateway_slug'] . '_' . $key );

					endforeach;

					//	Additional params
					switch( $_gateway ) :

						case 'stripe' :

							$_settings_encrypted['omnipay_' . $this->data['gateway_slug'] . '_publishableKey'] = $this->input->post( 'omnipay_' . $this->data['gateway_slug'] . '_publishableKey' );

						break;

					endswitch;

					$this->db->trans_begin();

					$_result			= $this->app_setting_model->set( $_settings, 'shop', NULL, FALSE );
					$_result_encrypted	= $this->app_setting_model->set( $_settings_encrypted, 'shop', NULL, TRUE );

					if ( $this->db->trans_status() !== FALSE && $_result && $_result_encrypted ) :

						$this->db->trans_commit();
						$this->data['success'] = '<strong>Success!</strong> ' . $this->data['gateway_name'] . ' Payment Gateway settings have been saved.';


					else :

						$this->db->trans_rollback();
						$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the ' . $this->data['gateway_name'] . ' Payment Gateway settings.';

					endif;

				else :

					$this->data['error'] = lang( 'fv_there_were_errors' );

				endif;

			endif;

			//	Handle modal viewing
			if ( $this->input->get( 'is_fancybox' ) ) :

				$this->data['header_override'] = 'structure/header/nails-admin-blank';
				$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

			endif;

			//	Render the interface
			$this->data['page']->title = 'Shop Payment Gateway Configuration &rsaquo; ' . $this->data['gateway_name'];

			if ( method_exists( $this, '_shop_pg_' . $_gateway ) ) :

				//	Specific configuration form available
				$this->{'_shop_pg_' . $_gateway}();

			else :

				//	Show the generic gateway configuration form
				$this->_shop_pg_generic( $_gateway );

			endif;

		else :

			//	Bad gateway name
			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Renders a generic Payment Gateway configuration interface
	 * @return void
	 */
	protected function _shop_pg_generic()
	{
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/settings/shop_pg/generic',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Renders an interface specific for WorldPay
	 * @return void
	 */
	protected function _shop_pg_worldpay()
	{
		$this->asset->load( 'nails.admin.shop.settings.paymentgateway.worldpay.min.js', 'NAILS' );
		$this->asset->inline( '<script>_worldpay_config = new NAILS_Admin_Shop_Settings_PaymentGateway_WorldPay();</script>' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/settings/shop_pg/worldpay',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Renders an interface specific for Stripe
	 * @return void
	 */
	protected function _shop_pg_stripe()
	{
		//	Additional params
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/settings/shop_pg/stripe',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Renders an interface specific for PayPal_Express
	 * @return void
	 */
	protected function _shop_pg_paypal_express()
	{
		//	Additional params
		$this->load->view( 'structure/header',						$this->data );
		$this->load->view( 'admin/settings/shop_pg/paypal_express',	$this->data );
		$this->load->view( 'structure/footer',						$this->data );
	}


	// --------------------------------------------------------------------------


	public function shop_sd()
	{
		$this->load->model( 'shop/shop_shipping_driver_model' );

		$_body = $this->shop_shipping_driver_model->configure( $this->input->get( 'driver' ) );

		if ( empty( $_body ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Shop Shipping Driver Configuration &rsaquo; ';

		// --------------------------------------------------------------------------

		if ( $this->input->get( 'is_fancybox' ) ) :

			$this->data['header_override'] = 'structure/header/nails-admin-blank';
			$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

		endif;

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/settings/shop_sd',	array( 'body' => $_body ) );
		$this->load->view( 'structure/footer',			$this->data );
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