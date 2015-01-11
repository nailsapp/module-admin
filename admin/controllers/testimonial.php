<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Manage testimonials
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */
class NAILS_Testimonial extends NAILS_Admin_Controller
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
        get_instance()->lang->load('admin_testimonials');

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = lang('testimonials_module_name');
        $d->icon = 'fa-comments';

        // --------------------------------------------------------------------------

        //  Navigation options
        $d->funcs          = array();
        $d->funcs['index'] = lang('testimonials_nav_index');

        // --------------------------------------------------------------------------

        return $d;
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

        $permissions['can_manage'] = 'Can manage testimonials';
        $permissions['can_create'] = 'Can create testimonials';
        $permissions['can_edit']   = 'Can edit testimonials';
        $permissions['can_delete'] = 'Can delete testimonials';

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

        $this->load->model('testimonial/testimonial_model');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse testimonials
     * @return void
     */
    public function index()
    {
        if (!user_has_permission('admin.testimonial:0.can_manage')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = lang('testimonials_index_title');

        // --------------------------------------------------------------------------

        $this->data['testimonials'] = $this->testimonial_model->get_all();

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/testimonial/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new testimonial
     * @return void
     */
    public function create()
    {
        if (!user_has_permission('admin.testimonial:0.can_create')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = lang('testimonials_create_title');

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $this->load->library('form_validation');

            $this->form_validation->set_rules('quote', '', 'xss_clean|required');
            $this->form_validation->set_rules('quote_by', '', 'xss_clean|required');
            $this->form_validation->set_rules('order', '', 'xss_clean');

            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                $data             = array();
                $data['quote']    = $this->input->post('quote');
                $data['quote_by'] = $this->input->post('quote_by');
                $data['order']    = (int) $this->input->post('order');

                if ($this->testimonial_model->create($data)) {

                    $this->session->set_flashdata('success', lang('testimonials_create_ok'));
                    redirect('admin/testimonial');

                } else {

                    $this->data['error'] = lang('testimonials_create_fail');
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/testimonial/create', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a testimonial
     * @return void
     */
    public function edit()
    {
        if (!user_has_permission('admin.testimonial:0.can_edit')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['testimonial'] = $this->testimonial_model->get_by_id($this->uri->segment(4));

        if (!$this->data['testimonial']) {

            $this->session->set_flashdata('error', lang('testimonials_common_bad_id'));
            redirect('admin/testimonial');
        }

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = lang('testimonials_edit_title');

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $this->load->library('form_validation');

            $this->form_validation->set_rules('quote', '', 'xss_clean|required');
            $this->form_validation->set_rules('quote_by', '', 'xss_clean|required');
            $this->form_validation->set_rules('order', '', 'xss_clean');

            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                $data             = array();
                $data['quote']    = $this->input->post('quote');
                $data['quote_by'] = $this->input->post('quote_by');
                $data['order']    = (int) $this->input->post('order');

                if ($this->testimonial_model->update($this->data['testimonial']->id, $data)) {

                    $this->session->set_flashdata('success', lang('testimonials_edit_ok'));
                    redirect('admin/testimonial');

                } else {

                    $this->data['error'] = lang('testimonials_edit_fail');
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/testimonial/edit', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a testimonial
     * @return void
     */
    public function delete()
    {
        if (!user_has_permission('admin.testimonial:0.can_delete')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $testimonial = $this->testimonial_model->get_by_id($this->uri->segment(4));

        if (!$testimonial) {

            $this->session->set_flashdata('error', lang('testimonials_common_bad_id'));
            redirect('admin/testimonial');
        }

        // --------------------------------------------------------------------------

        if ($this->testimonial_model->delete($testimonial->id)) {

            $this->session->set_flashdata('success', lang('testimonials_delete_ok'));

        } else {

            $this->session->set_flashdata('error', lang('testimonials_delete_fail'));
        }

        // --------------------------------------------------------------------------

        redirect('admin/testimonial');
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

if (!defined('NAILS_ALLOW_EXTENSION_TESTIMONIAL')) {

    /**
     * Proxy class for NAILS_Testimonial
     */
    class Testimonial extends NAILS_Testimonial
    {
    }
}
