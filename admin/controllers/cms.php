<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage the CMS
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */
class NAILS_Cms extends NAILS_Admin_Controller
{
    /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        if (!isModuleEnabled('cms')) {

            return false;
        }

        // --------------------------------------------------------------------------

        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = 'Content Management';
        $d->icon = 'fa-file-text';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs = array();

        if (user_has_permission('admin.cms:0.can_manage_menu')) {

            $d->funcs['menus'] = 'Manage Menus';
        }

        if (user_has_permission('admin.cms:0.can_manage_page')) {

            $d->funcs['pages'] = 'Manage Pages';
        }

        if (user_has_permission('admin.cms:0.can_manage_block')) {

            $d->funcs['blocks'] = 'Manage Blocks';
        }

        if (user_has_permission('admin.cms:0.can_manage_slider')) {

            $d->funcs['sliders'] = 'Manage Sliders';
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of notifications
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function notifications($classIndex = null)
    {
        $ci =& get_instance();
        $notifications = array();

        // --------------------------------------------------------------------------

        $notifications['pages']          = array();
        $notifications['pages']['title'] = 'Draft Pages';
        $notifications['pages']['type']  = 'neutral';

        $ci->db->where('is_published', false);
        $ci->db->where('is_deleted', false);
        $notifications['pages']['value'] = $ci->db->count_all_results(NAILS_DB_PREFIX . 'cms_page');

        // --------------------------------------------------------------------------

        return $notifications;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        $permissions = parent::permissions($classIndex);

        // --------------------------------------------------------------------------

        //  Menus
        $permissions['can_manage_menu']  = 'Can manage menus';
        $permissions['can_create_menu']  = 'Can create a new menu';
        $permissions['can_edit_menu']    = 'Can edit an existing menu';
        $permissions['can_delete_menu']  = 'Can delete an existing menu';
        $permissions['can_restore_menu'] = 'Can restore a deleted menu';

        //  Pages
        $permissions['can_manage_page']  = 'Can manage pages';
        $permissions['can_create_page']  = 'Can create a new page';
        $permissions['can_edit_page']    = 'Can edit an existing page';
        $permissions['can_delete_page']  = 'Can delete an existing page';
        $permissions['can_restore_page'] = 'Can restore a deleted page';
        $permissions['can_destroy_page'] = 'Can permenantly delete a page';

        //  Blocks
        $permissions['can_manage_block']  = 'Can manage blocks';
        $permissions['can_create_block']  = 'Can create a new block';
        $permissions['can_edit_block']    = 'Can edit an existing block';
        $permissions['can_delete_block']  = 'Can delete an existing block';
        $permissions['can_restore_block'] = 'Can restore a deleted block';

        //  Sliders
        $permissions['can_manage_slider']  = 'Can manage sliders';
        $permissions['can_create_slider']  = 'Can create a new slider';
        $permissions['can_edit_slider']    = 'Can edit an existing slider';
        $permissions['can_delete_slider']  = 'Can delete an existing slider';
        $permissions['can_restore_slider'] = 'Can restore a deleted slider';

        // --------------------------------------------------------------------------

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Constructs the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  Load helpers
        $this->load->helper('cms');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage CMS Pages
     * @return void
     */
    public function pages()
    {
        if (!user_has_permission('admin.cms:0.can_manage_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Load common blocks items
        $this->load->model('cms/cms_page_model');
        $this->load->model('routes_model');

        // --------------------------------------------------------------------------

        $method = $this->uri->segment(4) ? $this->uri->segment(4) : 'index';
        $method = ucfirst(strtolower($method));

        if (method_exists($this, 'pages' . $method)) {

            if (!$this->routes_model->canWriteRoutes()) {

                $this->data['message'] = '<strong>Hey!</strong> There\'s a problem with the routing system. ';
                $this->data['message'] .= $this->routes_model->cant_write_reason;
            }

            // --------------------------------------------------------------------------

            $this->{'pages' . $method}();

        } else {

            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Pages
     * @return void
     */
    protected function pagesIndex()
    {
        //  Page Title
        $this->data['page']->title = 'Manage Pages';

        // --------------------------------------------------------------------------

        //  Fetch all the pages in the DB
        $this->data['pages'] = $this->cms_page_model->get_all();

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('mustache.js/mustache.js', 'BOWER');
        $this->asset->load('nails.admin.cms.pages.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/pages/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Page
     * @return void
     */
    public function pagesCreate()
    {
        if (!user_has_permission('admin.cms:0.can_create_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['pages_nested_flat'] = $this->cms_page_model->get_all_nested_flat(' &rsaquo; ', false);

        //  Set method info
        $this->data['page']->title  = 'Create New Page';

        //  Get available templates & widgets
        $this->data['templates'] = $this->cms_page_model->get_available_templates('EDITOR');
        $this->data['widgets']  = $this->cms_page_model->get_available_widgets('EDITOR');

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->library('jqueryui');
        $this->asset->load('mustache.js/mustache.js', 'BOWER');
        $this->asset->load('nails.admin.cms.pages.create_edit.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/pages/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Page
     * @return void
     */
    protected function pagesEdit()
    {
        if (!user_has_permission('admin.cms:0.can_edit_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['cmspage'] = $this->cms_page_model->get_by_id($this->uri->segment(5), true);

        if (!$this->data['cmspage']) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> no page found by that ID');
            redirect('admin/cms/pages');
        }

        // --------------------------------------------------------------------------

        //  Get data
        $this->data['pages_nested_flat'] = $this->cms_page_model->get_all_nested_flat(' &rsaquo; ', false);

        //  Set method info
        $this->data['page']->title = 'Edit Page "' . $this->data['cmspage']->draft->title . '"';

        //  Get available templates & widgets
        $this->data['templates'] = $this->cms_page_model->get_available_templates('EDITOR');
        $this->data['widgets']   = $this->cms_page_model->get_available_widgets('EDITOR');

        //  Get children of this page
        $this->data['page_children'] = $this->cms_page_model->get_ids_of_children($this->data['cmspage']->id);

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->library('jqueryui');
        $this->asset->load('mustache.js/mustache.js', 'BOWER');
        $this->asset->load('nails.admin.cms.pages.create_edit.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/pages/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Publish a CMS Page
     * @return void
     */
    protected function pagesPublish()
    {
        if (!user_has_permission('admin.cms:0.can_edit_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $id   = $this->uri->segment(5);
        $page = $this->cms_page_model->get_by_id($id);

        if ($page && !$page->is_deleted) {

            if ($this->cms_page_model->publish($id)) {

                $this->session->set_flashdata('success', '<strong>Success!</strong> Page was published successfully.');

            } else {

                $this->session->set_flashdata('error', '<strong>Sorry,</strong> Could not publish page. ' . $this->cms_page_model->last_error());
            }

        } else {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid page ID.');
        }

        redirect('admin/cms/pages');
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a CMS Page
     * @return void
     */
    protected function pagesDelete()
    {
        if (!user_has_permission('admin.cms:0.can_delete_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $id   = $this->uri->segment(5);
        $page = $this->cms_page_model->get_by_id($id);

        if ($page && !$page->is_deleted) {

            if ($this->cms_page_model->delete($id)) {

                $this->session->set_flashdata('success', '<strong>Success!</strong> Page was deleted successfully.');

            } else {

                $this->session->set_flashdata('error', '<strong>Sorry,</strong> Could not delete page. ' . $this->cms_page_model->last_error());
            }

        } else {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid page ID.');
        }

        redirect('admin/cms/pages');
    }

    // --------------------------------------------------------------------------

    /**
     * Restore a CMS Page
     * @return void
     */
    protected function pagesRestore()
    {
        if (!user_has_permission('admin.cms:0.can_restore_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $id   = $this->uri->segment(5);
        $page = $this->cms_page_model->get_by_id($id);

        if ($page && $page->is_deleted) {

            if ($this->cms_page_model->restore($id)) {

                $this->session->set_flashdata('success', '<strong>Success!</strong> Page was restored successfully. ');

            } else {

                $this->session->set_flashdata('error', '<strong>Sorry,</strong> Could not restore page. ' . $this->cms_page_model->last_error());
            }

        } else {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid page ID.');
        }

        redirect('admin/cms/pages');
    }

    // --------------------------------------------------------------------------

    /**
     * Destroy a CMS Page
     * @return void
     */
    protected function pagesDestroy()
    {
        if (!user_has_permission('admin.cms:0.can_destroy_page')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $id   = $this->uri->segment(5);
        $page = $this->cms_page_model->get_by_id($id);

        if ($page) {

            if ($this->cms_page_model->destroy($id)) {

                $this->session->set_flashdata('success', '<strong>Success!</strong> Page was destroyed successfully. ');

            } else {

                $this->session->set_flashdata('error', '<strong>Sorry,</strong> Could not destroy page. ' . $this->cms_page_model->last_error());
            }

        } else {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid page ID.');
        }

        redirect('admin/cms/pages');
    }

    // --------------------------------------------------------------------------

    /**
     * Form Validation Callback: Validates a Page's slug
     * @param  srtring $str The slug to validate
     * @return boolean
     */
    public function _callback_slug($str)
    {
        $str = trim($str);

        //  Check is valid
        if (preg_match('/[^a-zA-Z0-9\-_\/\.]+/', $str)) {

            $this->form_validation->set_message('_callback_slug', 'Contains invalid characters (A-Z, 0-9, -, _ and / only).');
            return false;
        }

        // --------------------------------------------------------------------------

        //  Prepare the slug
        $str = explode('/', trim($str));
        foreach ($str as &$value) {

            $value = url_title($value, 'dash', true);

        }
        $str = implode('/', $str);

        // --------------------------------------------------------------------------

        $this->db->where('id !=', $this->uri->segment(5));
        $this->db->where('slug', $str);

        if ($this->db->count_all_results(NAILS_DB_PREFIX . 'cms_page')) {

            $this->form_validation->set_message('_callback_slug', 'Slug must be unique.');
            return false;
        }

        // --------------------------------------------------------------------------

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Manage CMS Blocks
     * @return void
     */
    public function blocks()
    {
        if (!user_has_permission('admin.cms:0.can_manage_block')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Load common blocks items
        $this->load->model('cms/cms_block_model');
        $this->asset->load('mustache.js/mustache.js', 'BOWER');
        $this->asset->load('nails.admin.cms.blocks.min.js', true);

        // --------------------------------------------------------------------------

        //  Define block types; block types allow for proper validation
        $this->data['block_types']              = array();
        $this->data['block_types']['plaintext'] = 'Plain Text';
        $this->data['block_types']['richtext']  = 'Rich Text';

        // @TODO: Support these other types of block
        //$this->data['block_types']['image']     = 'Image (*.jpg, *.png, *.gif)';
        //$this->data['block_types']['file']      = 'File (*.*)';
        //$this->data['block_types']['number']    = 'Number';
        //$this->data['block_types']['url']       = 'URL';

        // --------------------------------------------------------------------------

        $method = $this->uri->segment(4) ? $this->uri->segment(4) : 'index';
        $method = ucfirst(strtolower($method));

        if (method_exists($this, 'blocks' . $method)) {

            $this->{'blocks' . $method}();

        } else {

            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Blocks
     * @return void
     */
    protected function blocksIndex()
    {
        //  Set method info
        $this->data['page']->title = 'Manage Blocks';

        // --------------------------------------------------------------------------

        $this->data['blocks']    = $this->cms_block_model->get_all();
        $this->data['languages'] = $this->language_model->get_all_enabled_flat();

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/blocks/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Block
     * @return [type] [description]
     */
    protected function blocksEdit()
    {
        if (!user_has_permission('admin.cms:0.can_edit_block')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['block'] = $this->cms_block_model->get_by_id($this->uri->segment(5), true);

        if (!$this->data['block']) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> no block found by that ID');
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Loop through and update translations, keep track of translations which have been updated
            $updated = array();

            if ($this->input->post('translation')) {

                foreach ($this->input->post('translation') as $translation) {

                    $this->cms_block_model->update_translation($this->data['block']->id, $translation['language'], $translation['value']);
                    $updated[] = $translation['language'];
                }
            }

            //  Delete translations that weren't updated (they have been removed)
            if ($updated) {

                $this->db->where('block_id', $this->data['block']->id);
                $this->db->where_not_in('language', $updated);
                $this->db->delete(NAILS_DB_PREFIX . 'cms_block_translation');
            }

            //  Loop through and add new translations
            if ($this->input->post('new_translation')) {

                foreach ($this->input->post('new_translation') as $translation) {

                    $this->cms_block_model->create_translation($this->data['block']->id, $translation['language'], $translation['value']);
                }
            }

            // --------------------------------------------------------------------------

            //  Send the user on their merry way
            $this->session->set_flashdata('success', '<strong>Success!</strong> The block was updated successfully!');
            redirect('admin/cms/blocks');
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Edit Block "' . $this->data['block']->title . '"';

        // --------------------------------------------------------------------------

        //  Fetch data
        $this->data['languages']    = $this->language_model->get_all_enabled_flat();
        $this->data['default_code'] = $this->language_model->get_default_code();

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/blocks/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Block
     * @return void
     */
    protected function blocksCreate()
    {
        if (!user_has_permission('admin.cms:0.can_create_block')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Form Validation
            $this->load->library('form_validation');

            $this->form_validation->set_rules('slug', '', 'xss_clean|required|callback__callback_block_slug');
            $this->form_validation->set_rules('title', '', 'xss_clean|required');
            $this->form_validation->set_rules('description', '', 'xss_clean');
            $this->form_validation->set_rules('located', '', 'xss_clean');
            $this->form_validation->set_rules('type', '', 'xss_clean|required|callback__callback_block_type');
            $this->form_validation->set_rules('value', '', 'xss_clean');

            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run($this)) {

                $type  = $this->input->post('type');
                $slug  = $this->input->post('slug');
                $title = $this->input->post('title');
                $desc  = $this->input->post('description');
                $loc   = $this->input->post('located');
                $val   = $this->input->post('value');

                if ($this->cms_block_model->create_block($type, $slug, $title, $desc, $loc, $val)) {

                    $this->session->set_flashdata('success', '<strong>Success!</strong> Block created successfully.');
                    redirect('admin/cms/blocks');

                } else {

                    $this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the new block.';
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        $this->data['languages'] = $this->language_model->get_all_enabled_flat();

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/blocks/create', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Form Validation Callback: Validates a block's slug
     * @param  string $slug The slug to validate
     * @return boolean
     */
    public function _callback_block_slug($slug)
    {
        $slug = trim($slug);

        //  Check slug's characters are ok
        if (!preg_match('/[^a-zA-Z0-9\-\_]/', $slug)) {

            $block = $this->cms_block_model->get_by_slug($slug);

            if (!$block) {

                //  OK!
                return true;

            } else {

                $this->form_validation->set_message('_callback_block_slug', 'Must be unique');
                return false;
            }

        } else {

            $this->form_validation->set_message('_callback_block_slug', 'Invalid characters');
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Form Validation Callback: Validates a block's type
     * @param  string $type The type to validate
     * @return boolean
     */
    public function _callback_block_type($type)
    {
        $type = trim($type);

        if ($type) {

            if (isset($this->data['block_types'][$type])) {

                return true;

            } else {

                $this->form_validation->set_message('_callback_block_type', 'Block type not supported.');
                return false;
            }

        } else {

            $this->form_validation->set_message('_callback_block_type', lang('fv_required'));
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage CMS Sliders
     * @return void
     */
    public function sliders()
    {
        if (!user_has_permission('admin.cms:0.can_manage_slider')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Load common slider items
        $this->load->model('cms/cms_slider_model');

        // --------------------------------------------------------------------------

        $method = $this->uri->segment(4) ? $this->uri->segment(4) : 'index';
        $method = ucfirst(strtolower($method));

        if (method_exists($this, 'sliders' . $method)) {

            $this->{'sliders' . $method}();

        } else {

            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Sliders
     * @return void
     */
    protected function slidersIndex()
    {
        $this->data['page']->title = 'Manage Sliders';

        // --------------------------------------------------------------------------

        //  Fetch all the menus in the DB
        $this->data['sliders'] = $this->cms_slider_model->get_all();

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.cms.sliders.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/sliders/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Slider
     * @return void
     */
    protected function slidesCreate()
    {
        if (!user_has_permission('admin.cms:0.can_create_slider')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Create Slider';

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.cms.sliders.create_edit.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/sliders/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Slider
     * @return void
     */
    protected function slidersEdit()
    {
        if (!user_has_permission('admin.cms:0.can_edit_slider')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['slider'] = $this->cms_slider_model->get_by_id($this->uri->segment(5), true);

        if (!$this->data['slider']) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid slider ID.');
            redirect('admin/cms/menus');
        }

        $this->data['page']->title = 'Edit Slider "' . $this->data['slider']->label . '"';

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.cms.sliders.create_edit.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/sliders/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a CMS Slider
     * @return void
     */
    protected function slidersDelete()
    {
        if (!user_has_permission('admin.cms:0.can_delete_slider')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->session->set_flashdata('error', '<strong>Sorry,</strong> slider deletion is a TODO just now.');
        redirect('admin/cms/sliders');
    }

    // --------------------------------------------------------------------------

    /**
     * Manage CMS Menus
     * @return void
     */
    public function menus()
    {
        if (!user_has_permission('admin.cms:0.can_manage_menu')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Load common menu items
        $this->load->model('cms/cms_menu_model');

        // --------------------------------------------------------------------------

        $method = $this->uri->segment(4) ? $this->uri->segment(4) : 'index';
        $method = ucfirst(strtolower($method));

        if (method_exists($this, 'menus' . $method)) {

            $this->{'menus' . $method}();

        } else {

            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Menus
     * @return void
     */
    protected function menusIndex()
    {
        $this->data['page']->title = 'Manage Menus';

        // --------------------------------------------------------------------------

        //  Fetch all the menus in the DB
        $this->data['menus'] = $this->cms_menu_model->get_all();

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('nails.admin.cms.menus.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/menus/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Menu
     * @return void
     */
    protected function menusCreate()
    {
        if (!user_has_permission('admin.cms:0.can_create_menu')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Create Menu';

        // --------------------------------------------------------------------------

        $post = $this->input->post();

        if (isset($post['menu_item'])) {

            //  Validate
            $errors = false;
            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|required');
            $this->form_validation->set_rules('description', '', 'xss_clean|required');

            $this->form_validation->set_message('required', lang('fv_required'));

            foreach ($post['menu_item'] as $item) {

                if (empty($item['label']) || empty($item['url'])) {

                    $errors = 'All menu items are required to have a label and a URL.';
                    break;
                }
            }

            //  Execute
            if ($this->form_validation->run() && !$errors) {

                if ($this->cms_menu_model->create($post)) {

                    $status = 'success';
                    $msg    = '<strong>Success!</strong> Menu was created successfully.';
                    $this->session->set_flashdata($status, $msg);

                    redirect('admin/cms/menus');

                } else {

                    $this->data['error']  = '<strong>Sorry,</strong> there were errors. ';
                    $this->data['error'] .= $this->cms_menu_model->last_error();
                }

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> there were errors. ' . $errors;
            }
        }

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->library('jqueryui');
        $this->asset->load('nails.admin.cms.menus.create_edit.min.js', true);
        $this->asset->load('nestedSortable/jquery.ui.nestedSortable.js', 'BOWER');
        $this->asset->load('mustache.js/mustache.js', 'BOWER');

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/menus/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Menu
     * @return void
     */
    protected function menusEdit()
    {
        if (!user_has_permission('admin.cms:0.can_edit_menu')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['menu'] = $this->cms_menu_model->get_by_id($this->uri->segment(5), true, false);

        if (!$this->data['menu']) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid menu ID.');
            redirect('admin/cms/menus');
        }

        $this->data['page']->title = 'Edit Menu "' . $this->data['menu']->label . '"';

        $post = $this->input->post();

        if (isset($post['menu_item'])) {

            //  Validate
            $errors = false;
            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|required');
            $this->form_validation->set_rules('description', '', 'xss_clean|required');

            $this->form_validation->set_message('required', lang('fv_required'));

            foreach ($post['menu_item'] as $item) {

                if (empty($item['label']) || empty($item['url'])) {

                    $errors = 'All menu items are required to have a label and a URL.';
                    break;
                }
            }

            //  Execute
            if ($this->form_validation->run() && !$errors) {

                if ($this->cms_menu_model->update($this->data['menu']->id, $post)) {

                    $status = 'success';
                    $msg    = '<strong>Success!</strong> Menu was updated successfully.';
                    $this->session->set_flashdata($status, $msg);

                    redirect('admin/cms/menus');

                } else {

                    $this->data['error']  = '<strong>Sorry,</strong> there were errors. ';
                    $this->data['error'] .= $this->cms_menu_model->last_error();
                }

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> there were errors. ' . $errors;
            }
        }

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->library('jqueryui');
        $this->asset->load('nails.admin.cms.menus.create_edit.min.js', true);
        $this->asset->load('nestedSortable/jquery.ui.nestedSortable.js', 'BOWER');
        $this->asset->load('mustache.js/mustache.js', 'BOWER');

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/cms/menus/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a CMS Menu
     * @return void
     */
    protected function menusDelete()
    {
        if (!user_has_permission('admin.cms:0.can_delete_menu')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $menu = $this->cms_menu_model->get_by_id($this->uri->segment(5));

        if (!$menu) {

            $this->session->set_flashdata('error', '<strong>Sorry,</strong> invalid menu ID.');
            redirect('admin/cms/menus');
        }

        // --------------------------------------------------------------------------

        if ($this->cms_menu_model->delete($menu->id)) {

            $status = 'success';
            $msg    = '<strong>Sorry,</strong> failed to delete menu. ';
            $msg   .= $this->cms_menu_model->last_error();
            $this->session->set_flashdata($status, $msg);

        } else {

            $status = 'error';
            $msg    = '<strong>Sorry,</strong> failed to delete menu. ';
            $msg   .= $this->cms_menu_model->last_error();
            $this->session->set_flashdata($status, $msg);
        }

        redirect('admin/cms/menus');
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

if (!defined('NAILS_ALLOW_EXTENSION_CMS')) {

    /**
     * Proxy class for NAILS_Cms
     */
    class Cms extends NAILS_Cms
    {
    }
}
