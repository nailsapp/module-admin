<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage app settings
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */
class NAILS_Settings extends NAILS_Admin_Controller
{
    /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Load the laguage file
        get_instance()->lang->load('admin_settings');

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = lang('settings_module_name');
        $d->icon = 'fa-wrench';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs          = array();
        $d->funcs['admin'] = lang('settings_nav_admin');
        $d->funcs['site']  = lang('settings_nav_site');

        if (isModuleEnabled('blog')) {

            $d->funcs['blog'] = lang('settings_nav_blog');
        }

        if (isModuleEnabled('email')) {

            $d->funcs['email'] = lang('settings_nav_email');
        }

        if (isModuleEnabled('shop')) {

            $d->funcs['shop'] = lang('settings_nav_shop');
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Admin settings
     * @return void
     */
    public function admin()
    {
        //  Set method info
        $this->data['page']->title = lang('settings_admin_title');

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_admin_update_' . $method)) {

                $this->{'_admin_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'admin', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/admin', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Admin branding settings
     * @return void
     */
    protected function _admin_update_branding()
    {
        //  Prepare update
        $settings                     = array();
        $settings['primary_colour']   = $this->input->post('primary_colour');
        $settings['secondary_colour'] = $this->input->post('secondary_colour');
        $settings['highlight_colour'] = $this->input->post('highlight_colour');

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'admin')) {

            $this->data['success'] = '<strong>Success!</strong> Admin branding settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin branding settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Admin IP whitelist settings
     * @return void
     */
    protected function _admin_update_whitelist()
    {
        //  Prepare the whitelist
        $whitelistRaw = $this->input->post('whitelist');
        $whitelistRaw = str_replace("\n\r", "\n", $whitelistRaw);
        $whitelistRaw = explode("\n", $whitelistRaw);
        $whitelist     = array();

        foreach ($whitelistRaw as $line) {

            $whitelist = array_merge(explode(',', $line), $whitelist);
        }

        $whitelist = array_unique($whitelist);
        $whitelist = array_filter($whitelist);
        $whitelist = array_map('trim', $whitelist);
        $whitelist = array_values($whitelist);

        //  Prepare update
        $settings              = array();
        $settings['whitelist'] = $whitelist;

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'admin')) {

            $this->data['success'] = '<strong>Success!</strong> Admin whitelist settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving admin whitelist settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Site settings
     * @return void
     */
    public function site()
    {
        //  Set method info
        $this->data['page']->title = lang('settings_site_title');

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_site_update_' . $method)) {

                $this->{'_site_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'app', true);

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.site.settings.min.js', true);
        $this->asset->inline('<script>_nails_settings = new NAILS_Admin_Site_Settings();</script>');

        $this->load->library('auth/social_signon');
        $this->data['providers'] = $this->social_signon->get_providers();

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/site', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Analytics settings
     * @return void
     */
    protected function _site_update_analytics()
    {
        //  Prepare update
        $settings                             = array();
        $settings['google_analytics_account'] = $this->input->post('google_analytics_account');

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'app')) {

            $this->data['success'] = '<strong>Success!</strong> Site settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Auth settings
     * @return void
     */
    protected function _site_update_auth()
    {
        $this->load->library('auth/social_signon');
        $providers = $this->social_signon->get_providers();

        // --------------------------------------------------------------------------

        //  Prepare update
        $settings            = array();
        $settings_encrypted = array();

        $settings['user_registration_enabled'] = $this->input->post('user_registration_enabled');

        //  Disable social signon, if any providers are proeprly enabled it'll turn itself on again.
        $settings['auth_social_signon_enabled'] = false;

        foreach ($providers as $provider) {

            $settings['auth_social_signon_' . $provider['slug'] . '_enabled'] = (bool) $this->input->post('auth_social_signon_' . $provider['slug'] . '_enabled');

            if ($settings['auth_social_signon_' . $provider['slug'] . '_enabled']) {

                //  null out each key
                if ($provider['fields']) {

                    foreach ($provider['fields'] as $key => $label) {

                        if (is_array($label) && !isset($label['label'])) {

                            foreach ($label as $key1 => $label1) {

                                $value = $this->input->post('auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1);

                                if (!empty($label1['required']) && empty($value)) {

                                    $error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label1['label'] . '".';
                                    break 3;

                                }

                                if ( empty($label1['encrypted'])) {

                                    $settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $value;

                                } else {

                                    $settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = $value;
                                }
                            }

                        } else {

                            $value = $this->input->post('auth_social_signon_' . $provider['slug'] . '_' . $key);

                            if (!empty($label['required']) && empty($value)) {

                                $error = 'Provider "' . $provider['label'] . '" was enabled, but was missing required field "' . $label['label'] . '".';
                                break 2;
                            }

                            if ( empty($label['encrypted'])) {

                                $settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = $value;

                            } else {

                                $settings_encrypted['auth_social_signon_' . $provider['slug'] . '_' . $key] = $value;
                            }
                        }
                    }
                }

                //  Turn on social signon
                $settings['auth_social_signon_enabled'] = true;

            } else {

                //  null out each key
                if ($provider['fields']) {

                    foreach ($provider['fields'] as $key => $label) {

                        /**
                         * Secondary conditional detects an actual array fo fields rather than
                         * just the label/required array. Design could probably be improved...
                         **/

                        if (is_array($label) && !isset($label['label'])) {

                            foreach ($label as $key1 => $label1) {

                                $settings['auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1] = null;
                            }

                        } else {

                            $settings['auth_social_signon_' . $provider['slug'] . '_' . $key] = null;
                        }
                    }
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Save
        if (empty($error)) {

            $this->db->trans_begin();
            $rollback = false;

            if (!empty($settings)) {

                if (!$this->app_setting_model->set($settings, 'app')) {

                    $error    = $this->app_setting_model->last_error();
                    $rollback = true;
                }
            }

            if (!empty($settings_encrypted)) {

                if (!$this->app_setting_model->set($settings_encrypted, 'app', null, true)) {

                    $error    = $this->app_setting_model->last_error();
                    $rollback = true;
                }
            }

            if ($rollback) {

                $this->db->trans_rollback();
                $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $error;

            } else {

                $this->db->trans_commit();
                $this->data['success'] = '<strong>Success!</strong> Authentication settings were saved.';

            }

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving authentication settings. ' . $error;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Site Maintenance settings
     * @return void
     */
    protected function _site_update_maintenance()
    {
        //  Prepare the whitelist
        $whitelistRaw = $this->input->post('maintenance_mode_whitelist');
        $whitelistRaw = str_replace("\n\r", "\n", $whitelistRaw);
        $whitelistRaw = explode("\n", $whitelistRaw);
        $whitelist    = array();

        foreach ($whitelistRaw as $line) {

            $whitelist = array_merge(explode(',', $line), $whitelist);
        }

        $whitelist = array_unique($whitelist);
        $whitelist = array_filter($whitelist);
        $whitelist = array_map('trim', $whitelist);
        $whitelist = array_values($whitelist);

        //  Prepare update
        $settings                               = array();
        $settings['maintenance_mode_enabled']   = (bool) $this->input->post('maintenance_mode_enabled');
        $settings['maintenance_mode_whitelist'] = $whitelist;

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'app')) {

            $this->data['success'] = '<strong>Success!</strong> Maintenance settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving maintenance settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Blog settings
     * @return void
     */
    public function blog()
    {
        if (!isModuleEnabled('blog')) {

            show_404();
        }

        // --------------------------------------------------------------------------

        //  Load models
        $this->load->model('blog/blog_model');
        $this->load->model('blog/blog_skin_model');

        // --------------------------------------------------------------------------

        //  Catch blog adding/editing
        switch ($this->uri->segment(4)) {

            case 'index':

                $this->_blog_index();
                return;
                break;

            case 'create':

                $this->_blog_create();
                return;
                break;

            case 'edit':

                $this->_blog_edit();
                return;
                break;

            case 'delete':

                $this->_blog_delete();
                return;
                break;
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = lang('settings_blog_title');

        // --------------------------------------------------------------------------

        $this->data['blogs'] = $this->blog_model->get_all_flat();

        if (empty($this->data['blogs'])) {

            if ($this->user_model->is_superuser()) {

                $this->session->set_flashdata('message', '<strong>You don\'t have a blog!</strong> Create a new blog in order to configure blog settings.');
                redirect('admin/settings/blog/create');

            } else {

                show_404();
            }
        }

        if (count($this->data['blogs']) == 1) {

            reset($this->data['blogs']);
            $this->data['selected_blog'] = key($this->data['blogs']);

        } elseif ($this->input->get('blog_id')) {

            if (!empty($this->data['blogs'][$this->input->get('blog_id')])) {

                $this->data['selected_blog'] = $this->input->get('blog_id');
            }

            if (empty($this->data['selected_blog'])) {

                $this->data['error'] = '<strong>Sorry,</strong> there is no blog by that ID.';
            }
        }

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method = $this->input->post('update');

            if (method_exists($this, '_blog_update_' . $method)) {

                $this->{'_blog_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['skins'] = $this->blog_skin_model->get_available();

        if (!empty($this->data['selected_blog'])) {

            $this->data['settings'] = app_setting(null, 'blog-' . $this->data['selected_blog'], true);
        }

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.blog.settings.min.js', true);
        $this->asset->inline('<script>_nails_settings = new NAILS_Admin_Blog_Settings();</script>');

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/blog', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog settings
     * @return void
     */
    protected function _blog_index()
    {
        if (!$this->user_model->is_superuser()) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Manage Blogs';

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['blogs'] = $this->blog_model->get_all();

        if (empty($this->data['blogs'])) {

            if ($this->user_model->is_superuser()) {

                $this->session->set_flashdata('message', '<strong>You don\'t have a blog!</strong> Create a new blog in order to configure blog settings.');
                redirect('admin/settings/blog/create');

            } else {

                show_404();
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/blog/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new blog
     * @return void
     */
    protected function _blog_create()
    {
        if (!$this->user_model->is_superuser()) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Manage Blogs &rsaquo; Create';

        // --------------------------------------------------------------------------

        //  Handle POST
        if ($this->input->post()) {

            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|required');
            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                $data        = new stdClass();
                $data->label = $this->input->post('label');

                $id = $this->blog_model->create($data);

                if ($id) {

                    $this->session->set_flashdata('success', '<strong>Success!</strong> Blog was created successfully, now please confirm blog settings.');
                    redirect('admin/settings/blog?blog_id=' . $id);

                } else {

                    $this->data['error'] = '<strong>Sorry,</strong> failed to create blog. ' . $this->blog_model->last_error();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/blog/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing blog
     * @return void
     */
    protected function _blog_edit()
    {
        if (!$this->user_model->is_superuser()) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['blog'] = $this->blog_model->get_by_id($this->uri->segment(5));

        if (empty($this->data['blog'])) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> you specified an invalid Blog ID.');
            redirect('admin/settings/blog/index');
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Manage Blogs &rsaquo; Edit "' . $this->data['blog']->label . '"';

        // --------------------------------------------------------------------------

        //  Handle POST
        if ($this->input->post()) {

            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|required');
            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                $data        = new stdClass();
                $data->label = $this->input->post('label');

                if ($this->blog_model->update($this->uri->Segment(5), $data)) {

                    $this->session->set_flashdata('success', '<strong>Success!</strong> Blog was updated successfully.');
                    redirect('admin/settings/blog/index');

                } else {

                    $this->data['error']  = '<strong>Sorry,</strong> failed to create blog. ';
                    $this->data['error'] .= $this->blog_model->last_error();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/blog/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an existing blog
     * @return void
     */
    protected function _blog_delete()
    {
        if (!$this->user_model->is_superuser()) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $blog = $this->blog_model->get_by_id($this->uri->segment(5));

        if (empty($blog)) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> you specified an invalid Blog ID.');
            redirect('admin/settings/blog/index');
        }

        // --------------------------------------------------------------------------

        if ($this->blog_model->delete($blog->id)) {

            $this->session->set_flashdata('success', '<strong>Success!</strong> blog was deleted successfully.');

        } else {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> failed to delete blog. ' . $this->blog_model->last_error());
        }

        redirect('admin/settings/blog/index');
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog settings
     * @return void
     */
    protected function _blog_update_settings()
    {
        //  Prepare update
        $settings                       = array();
        $settings['name']               = $this->input->post('name');
        $settings['url']                = $this->input->post('url');
        $settings['use_excerpts']       = (bool) $this->input->post('use_excerpts');
        $settings['gallery_enabled']    = (bool) $this->input->post('gallery_enabled');
        $settings['categories_enabled'] = (bool) $this->input->post('categories_enabled');
        $settings['tags_enabled']       = (bool) $this->input->post('tags_enabled');
        $settings['rss_enabled']        = (bool) $this->input->post('rss_enabled');

        // --------------------------------------------------------------------------

        //  Sanitize blog url
        $settings['url'] .= substr($settings['url'], -1) != '/' ? '/' : '';

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'blog-' . $this->input->get('blog_id'))) {

            $this->data['success'] = '<strong>Success!</strong> Blog settings have been saved.';

            $this->load->model('common/routes_model');

            if (!$this->routes_model->update('shop')) {

                $this->data['warning']  = '<strong>Warning:</strong> while the blog settings were updated, the routes ';
                $this->data['warning'] .= 'file could not be updated. The blog may not behave as expected,';
            }

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog Skin settings
     * @return void
     */
    protected function _blog_update_skin()
    {
        //  Prepare update
        $settings         = array();
        $settings['skin'] = $this->input->post('skin');

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'blog-' . $this->input->get('blog_id'))) {

            $this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog Commenting settings
     * @return void
     */
    protected function _blog_update_commenting()
    {
        //  Prepare update
        $settings                              = array();
        $settings['comments_enabled']          = $this->input->post('comments_enabled');
        $settings['comments_engine']           = $this->input->post('comments_engine');
        $settings['comments_disqus_shortname'] = $this->input->post('comments_disqus_shortname');

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'blog-' . $this->input->get('blog_id'))) {

            $this->data['success'] = '<strong>Success!</strong> Blog commenting settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving commenting settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog Social settings
     * @return void
     */
    protected function _blog_update_social()
    {
        //  Prepare update
        $settings                              = array();
        $settings['social_facebook_enabled']   = (bool) $this->input->post('social_facebook_enabled');
        $settings['social_twitter_enabled']    = (bool) $this->input->post('social_twitter_enabled');
        $settings['social_twitter_via']        = $this->input->post('social_twitter_via');
        $settings['social_googleplus_enabled'] = (bool) $this->input->post('social_googleplus_enabled');
        $settings['social_pinterest_enabled']  = (bool) $this->input->post('social_pinterest_enabled');
        $settings['social_skin']               = $this->input->post('social_skin');
        $settings['social_layout']             = $this->input->post('social_layout');
        $settings['social_layout_single_text'] = $this->input->post('social_layout_single_text');
        $settings['social_counters']           = (bool) $this->input->post('social_counters');

        //  If any of the above are enabled, then social is enabled.
        $settings['social_enabled'] = $settings['social_facebook_enabled'] || $settings['social_twitter_enabled'] || $settings['social_googleplus_enabled'] || $settings['social_pinterest_enabled'];

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'blog-' . $this->input->get('blog_id'))) {

            $this->data['success'] = '<strong>Success!</strong> Blog social settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving social settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Blog Sidebar settings
     * @return void
     */
    protected function _blog_update_sidebar()
    {
        //  Prepare update
        $settings                          = array();
        $settings['sidebar_latest_posts']  = (bool) $this->input->post('sidebar_latest_posts');
        $settings['sidebar_categories']    = (bool) $this->input->post('sidebar_categories');
        $settings['sidebar_tags']          = (bool) $this->input->post('sidebar_tags');
        $settings['sidebar_popular_posts'] = (bool) $this->input->post('sidebar_popular_posts');

        //  @TODO: Associations

        // --------------------------------------------------------------------------

        //  Save
        if ($this->app_setting_model->set($settings, 'blog-' . $this->input->get('blog_id'))) {

            $this->data['success'] = '<strong>Success!</strong> Blog sidebar settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving sidebar settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Email settings
     * @return void
     */
    public function email()
    {
        //  Set method info
        $this->data['page']->title = lang('settings_email_title');

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method = $this->input->post('update');
            if (method_exists($this, '_email_update_' . $method)) {

                $this->{'_email_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings'] = app_setting(null, 'email', true);

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.email.settings.min.js', true);
        $this->asset->inline('<script>_nails_settings = new NAILS_Admin_Email_Settings();</script>');

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/email', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Email settings
     * @return void
     */
    protected function _email_update_general()
    {
        //  Prepare update
        $settings               = array();
        $settings['from_name']  = $this->input->post('from_name');
        $settings['from_email'] = $this->input->post('from_email');

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'email')) {

            $this->data['success'] = '<strong>Success!</strong> General email settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage Shop settings
     * @return void
     */
    public function shop()
    {
        if (!isModuleEnabled('shop')) {

            show_404();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = lang('settings_shop_title');

        // --------------------------------------------------------------------------

        //  Load models
        $this->load->model('shop/shop_model');
        $this->load->model('shop/shop_currency_model');
        $this->load->model('shop/shop_shipping_driver_model');
        $this->load->model('shop/shop_payment_gateway_model');
        $this->load->model('shop/shop_tax_rate_model');
        $this->load->model('shop/shop_skin_front_model');
        $this->load->model('shop/shop_skin_checkout_model');
        $this->load->model('common/country_model');

        // --------------------------------------------------------------------------

        //  Process POST
        if ($this->input->post()) {

            $method =  $this->input->post('update');

            if (method_exists($this, '_shop_update_' . $method)) {

                $this->{'_shop_update_' . $method}();

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> I can\'t determine what type of update you are trying to perform.';
            }
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['settings']         = app_setting(null, 'shop', true);
        $this->data['payment_gateways'] = $this->shop_payment_gateway_model->get_available();
        $this->data['shipping_drivers'] = $this->shop_shipping_driver_model->getAvailable();
        $this->data['currencies']       = $this->shop_currency_model->get_all();
        $this->data['tax_rates']        = $this->shop_tax_rate_model->get_all();
        $this->data['tax_rates_flat']   = $this->shop_tax_rate_model->get_all_flat();
        $this->data['countries_flat']   = $this->country_model->get_all_flat();
        $this->data['continents_flat']  = $this->country_model->get_all_continents_flat();
        array_unshift($this->data['tax_rates_flat'], 'No Tax');

        //  "Front of house" skins
        $this->data['skins_front']         = $this->shop_skin_front_model->get_available();
        $this->data['skin_front_selected'] = app_setting('skin_front', 'shop') ? app_setting('skin_front', 'shop') : 'shop-skin-front-classic';
        $this->data['skin_front_current']  = $this->shop_skin_front_model->get($this->data['skin_front_selected']);

        //  "Checkout" skins
        $this->data['skins_checkout']         = $this->shop_skin_checkout_model->get_available();
        $this->data['skin_checkout_selected'] = app_setting('skin_checkout', 'shop') ? app_setting('skin_checkout', 'shop') : 'shop-skin-checkout-classic';
        $this->data['skin_checkout_current']  = $this->shop_skin_checkout_model->get($this->data['skin_checkout_selected']);

        // --------------------------------------------------------------------------

        //  Load assets
        $this->asset->load('nails.admin.shop.settings.min.js', true);
        $this->asset->load('mustache.js/mustache.js', 'BOWER');
        $this->asset->inline('<script>_nails_settings = new NAILS_Admin_Shop_Settings();</script>');

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header',       $this->data);
        $this->load->view('admin/settings/shop',    $this->data);
        $this->load->view('structure/footer',       $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop settings
     * @return void
     */
    protected function _shop_update_settings()
    {
        //  Prepare update
        $settings                                          = array();
        $settings['name']                                  = $this->input->post('name');
        $settings['url']                                   = $this->input->post('url');
        $settings['price_exclude_tax']                     = $this->input->post('price_exclude_tax');
        $settings['enable_external_products']              = (bool) $this->input->post('enable_external_products');
        $settings['invoice_company']                       = $this->input->post('invoice_company');
        $settings['invoice_company']                       = $this->input->post('invoice_company');
        $settings['invoice_address']                       = $this->input->post('invoice_address');
        $settings['invoice_vat_no']                        = $this->input->post('invoice_vat_no');
        $settings['invoice_company_no']                    = $this->input->post('invoice_company_no');
        $settings['invoice_footer']                        = $this->input->post('invoice_footer');
        $settings['warehouse_collection_enabled']          = (bool) $this->input->post('warehouse_collection_enabled');
        $settings['warehouse_addr_addressee']              = $this->input->post('warehouse_addr_addressee');
        $settings['warehouse_addr_line1']                  = $this->input->post('warehouse_addr_line1');
        $settings['warehouse_addr_line2']                  = $this->input->post('warehouse_addr_line2');
        $settings['warehouse_addr_town']                   = $this->input->post('warehouse_addr_town');
        $settings['warehouse_addr_postcode']               = $this->input->post('warehouse_addr_postcode');
        $settings['warehouse_addr_state']                  = $this->input->post('warehouse_addr_state');
        $settings['warehouse_addr_country']                = $this->input->post('warehouse_addr_country');
        $settings['warehouse_collection_delivery_enquiry'] = (bool) $this->input->post('warehouse_collection_delivery_enquiry');
        $settings['page_brand_listing']                    = $this->input->post('page_brand_listing');
        $settings['page_category_listing']                 = $this->input->post('page_category_listing');
        $settings['page_collection_listing']               = $this->input->post('page_collection_listing');
        $settings['page_range_listing']                    = $this->input->post('page_range_listing');
        $settings['page_sale_listing']                     = $this->input->post('page_sale_listing');
        $settings['page_tag_listing']                      = $this->input->post('page_tag_listing');

        // --------------------------------------------------------------------------

        //  Sanitize shop url
        $settings['url'] .= substr($settings['url'], -1) != '/' ? '/' : '';

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'shop')) {

            $this->data['success'] = '<strong>Success!</strong> Store settings have been saved.';

            // --------------------------------------------------------------------------

            //  Rewrite routes
            $this->load->model('common/routes_model');
            if (!$this->routes_model->update('shop')) {

                $this->data['warning'] = '<strong>Warning:</strong> while the shop settings were updated, the routes file could not be updated. The shop may not behave as expected,';
            }

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop Browse settings
     * @return void
     */
    protected function _shop_update_browse()
    {
        //  Prepare update
        $settings                             = array();
        $settings['expand_variants']          = (bool) $this->input->post('expand_variants');
        $settings['default_product_per_page'] = $this->input->post('default_product_per_page');
        $settings['default_product_per_page'] = is_numeric($settings['default_product_per_page']) ? (int) $settings['default_product_per_page'] : $settings['default_product_per_page'];
        $settings['default_product_sort']     = $this->input->post('default_product_sort');

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'shop')) {

            $this->data['success'] = '<strong>Success!</strong> Browsing settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop skin settings
     * @return void
     */
    protected function _shop_update_skin()
    {
        //  Prepare update
        $settings                  = array();
        $settings['skin_front']    = $this->input->post('skin_front');
        $settings['skin_checkout'] = $this->input->post('skin_checkout');

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'shop')) {

            $this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop skin config
     * @return void
     */
    protected function _shop_update_skin_config()
    {
        //  Prepare update
        $configs = (array) $this->input->post('skin_config');
        $configs = array_filter($configs);
        $success = true;

        foreach ($configs as $slug => $configs) {

            //  Clear out the grouping; booleans not specified should be assumed false
            $this->app_setting_model->deleteGroup('shop-' . $slug);

            //  New settings
            $settings = array();
            foreach ($configs as $key => $value) {

                $settings[$key] = $value;
            }

            if ($settings) {

                if (!$this->app_setting_model->set($settings, 'shop-' . $slug)) {

                    $success = false;
                    break;
                }
            }
        }

        // --------------------------------------------------------------------------

        if ($success) {

            $this->data['success'] = '<strong>Success!</strong> Skin settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';

        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop Payment Gateway settings
     * @return [type] [description]
     */
    protected function _shop_update_payment_gateway()
    {
        //  Prepare update
        $settings                             = array();
        $settings['enabled_payment_gateways'] = array_filter((array) $this->input->post('enabled_payment_gateways'));

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'shop')) {

            $this->data['success'] = '<strong>Success!</strong> Payment Gateway settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop Currency settings
     * @return void
     */
    protected function _shop_update_currencies()
    {
        //  Prepare update
        $settings                          = array();
        $settings['base_currency']         = $this->input->post('base_currency');
        $settings['additional_currencies'] = $this->input->post('additional_currencies');

        $settings_encrypted                             = array();
        $settings_encrypted['openexchangerates_app_id'] = $this->input->post('openexchangerates_app_id');

        // --------------------------------------------------------------------------

        $this->db->trans_begin();
        $rollback = false;

        if (!$this->app_setting_model->set($settings, 'shop')) {

            $error      = $this->app_setting_model->last_error();
            $rollback   = true;
        }

        if (!$this->app_setting_model->set($settings_encrypted, 'shop', null, true)) {

            $error      = $this->app_setting_model->last_error();
            $rollback   = true;
        }

        if ($rollback) {

            $this->db->trans_rollback();
            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving currency settings. ' . $error;

        } else {

            $this->db->trans_commit();
            $this->data['success'] = '<strong>Success!</strong> Currency settings were saved.';

            // --------------------------------------------------------------------------

            /**
             * If there are multiple currencies and an Open Exchange Rates App ID provided
             * then attempt a sync
             */

            if (!empty($settings['additional_currencies']) && !empty($settings_encrypted['openexchangerates_app_id'])) {

                $this->load->model('shop/shop_currency_model');

                if (!$this->shop_currency_model->sync()) {

                    $this->data['message'] = '<strong>Warning:</strong> an attempted sync with Open Exchange Rates service failed with the following reason: ' . $this->shop_currency_model->last_error();

                } else {

                    $this->data['notice'] = '<strong>Currency Sync Complete.</strong><br />The system successfully synced with the Open Exchange Rates service.';
                }
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shop shipping settings
     * @return void
     */
    protected function _shop_update_shipping()
    {
        //  Prepare update
        $settings                            = array();
        $settings['enabled_shipping_driver'] = $this->input->post('enabled_shipping_driver');

        // --------------------------------------------------------------------------

        if ($this->app_setting_model->set($settings, 'shop')) {

            $this->data['success'] = '<strong>Success!</strong> Shipping settings have been saved.';

        } else {

            $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving settings.';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Set Payment Gateway settings
     * @return void
     */
    public function shop_pg()
    {
        //  Check if valid gateway
        $this->load->model('shop/shop_payment_gateway_model');

        $gateway    = $this->uri->segment(4) ? strtolower($this->uri->segment(4)) : '';
        $available = $this->shop_payment_gateway_model->is_available($gateway);

        if ($available) {

            $params = $this->shop_payment_gateway_model->get_default_params($gateway);

            $this->data['params']       = $params;
            $this->data['gateway_name'] = ucwords(str_replace('_', ' ', $gateway));
            $this->data['gateway_slug'] = $this->shop_payment_gateway_model->get_correct_casing($gateway);

            //  Handle POST
            if ($this->input->post()) {

                $this->load->library('form_validation');

                foreach ($params as $key => $value) {

                    if ($key == 'testMode') {

                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_' . $key, '', 'xss_clean');

                    } else {

                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_' . $key, '', 'xss_clean|required');
                    }
                }

                //  Additional params
                switch ($gateway) {

                    case 'paypal_express':

                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_brandName', '', 'xss_clean');
                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_headerImageUrl', '', 'xss_clean');
                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_logoImageUrl', '', 'xss_clean');
                        $this->form_validation->set_rules('omnipay_' . $this->data['gateway_slug'] . '_borderColor', '', 'xss_clean');
                        break;
                }

                $this->form_validation->set_message('required', lang('fv_required'));

                if ($this->form_validation->run()) {

                    $settings           = array();
                    $settings_encrypted = array();

                    //  Customisation params
                    $settings['omnipay_' . $this->data['gateway_slug'] . '_customise_label'] = $this->input->post('omnipay_' . $this->data['gateway_slug'] . '_customise_label');
                    $settings['omnipay_' . $this->data['gateway_slug'] . '_customise_img']   = $this->input->post('omnipay_' . $this->data['gateway_slug'] . '_customise_img');

                    //  Gateway params
                    foreach ($params as $key => $value) {

                        $settings_encrypted['omnipay_' . $this->data['gateway_slug'] . '_' . $key] = $this->input->post('omnipay_' . $this->data['gateway_slug'] . '_' . $key);
                    }

                    //  Additional params
                    switch ($gateway) {

                        case 'stripe':

                            $settings_encrypted['omnipay_' . $this->data['gateway_slug'] . '_publishableKey'] = $this->input->post('omnipay_' . $this->data['gateway_slug'] . '_publishableKey');
                            break;
                    }

                    $this->db->trans_begin();

                    $result           = $this->app_setting_model->set($settings, 'shop', null, false);
                    $result_encrypted = $this->app_setting_model->set($settings_encrypted, 'shop', null, true);

                    if ($this->db->trans_status() !== false && $result && $result_encrypted) {

                        $this->db->trans_commit();
                        $this->data['success'] = '<strong>Success!</strong> ' . $this->data['gateway_name'] . ' Payment Gateway settings have been saved.';

                    } else {

                        $this->db->trans_rollback();
                        $this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the ' . $this->data['gateway_name'] . ' Payment Gateway settings.';
                    }

                } else {

                    $this->data['error'] = lang('fv_there_were_errors');
                }
            }

            //  Handle modal viewing
            if ($this->input->get('is_fancybox')) {

                $this->data['headerOverride'] = 'structure/header/nails-admin-blank';
                $this->data['footerOverride'] = 'structure/footer/nails-admin-blank';
            }

            //  Render the interface
            $this->data['page']->title = 'Shop Payment Gateway Configuration &rsaquo; ' . $this->data['gateway_name'];

            if (method_exists($this, '_shop_pg_' . $gateway)) {

                //  Specific configuration form available
                $this->{'_shop_pg_' . $gateway}();

            } else {

                //  Show the generic gateway configuration form
                $this->_shop_pg_generic($gateway);
            }

        } else {

            //  Bad gateway name
            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Renders a generic Payment Gateway configuration interface
     * @return void
     */
    protected function _shop_pg_generic()
    {
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/shop_pg/generic', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Renders an interface specific for WorldPay
     * @return void
     */
    protected function _shop_pg_worldpay()
    {
        $this->asset->load('nails.admin.shop.settings.paymentgateway.worldpay.min.js', 'NAILS');
        $this->asset->inline('<script>_worldpay_config = new NAILS_Admin_Shop_Settings_PaymentGateway_WorldPay();</script>');

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/shop_pg/worldpay', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Renders an interface specific for Stripe
     * @return void
     */
    protected function _shop_pg_stripe()
    {
        //  Additional params
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/shop_pg/stripe', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Renders an interface specific for PayPal_Express
     * @return void
     */
    protected function _shop_pg_paypal_express()
    {
        //  Additional params
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/shop_pg/paypal_express', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Set Shipping Driver settings
     * @return void
     */
    public function shop_sd()
    {
        $this->load->model('shop/shop_shipping_driver_model');

        $body = $this->shop_shipping_driver_model->configure($this->input->get('driver'));

        if (empty($body)) {

            show_404();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Shop Shipping Driver Configuration &rsaquo; ';

        // --------------------------------------------------------------------------

        if ($this->input->get('is_fancybox')) {

            $this->data['headerOverride'] = 'structure/header/nails-admin-blank';
            $this->data['footerOverride'] = 'structure/footer/nails-admin-blank';

        }

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/settings/shop_sd', array('body' => $body));
        $this->load->view('structure/footer', $this->data);
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

if (!defined('NAILS_ALLOW_EXTENSION_SETTINGS')) {

    /**
     * Proxy class for NAILS_Settings
     */
    class Settings extends NAILS_Settings
    {
    }
}
