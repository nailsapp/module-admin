<?php

/**
 * This class renders Admin Utilities
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Utilities extends \AdminController
{
    protected $exportSources;
    protected $exportFormats;

    // --------------------------------------------------------------------------

    /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = parent::announce();
        $d['test_email']     = array('Utilities', 'Send Test Email');
        $d['rewrite_routes'] = array('Utilities', 'Rewrite Routes');
        $d['export']         = array('Utilities', 'Export Data');

        if (isModuleEnabled('nailsapp/module-cdn')) {

            $d['cdn/orphans'] = array('Utilities', 'CDN: Find orphaned objects');
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Constructs the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        /**
         * Define the export sources
         *
         * Each item in this array is an array which defines the source, in the
         * following format:
         *
         * array(
         *    0 => 'Source Title',
         *    1 => 'Source Description',
         *    2 => 'sourceMethod'
         * )
         *
         * The source method should be a callable method which is prefixed with
         * _export_source_, using the above as an example, the method would be:
         *
         * _export_source_sourceMethod()
         *
         * This method should return an array where the indexes are the column
         * names and the values are not arrays, i.e stuff which would fit into
         * a single cell in Excel).
         */

        $this->exportSources = array();

        if (user_has_permission('admin.accounts:0')) {

            $this->exportSources[] = array(
                'Members: All',
                'Export a list of all the site\'s registered users and their meta data.',
                'users_all'
            );

            $this->exportSources[] = array(
                'Members: Names and Email',
                'Export a list of all the site\'s registered users and their email addresses.',
                'users_email'
            );
        }

        // --------------------------------------------------------------------------

        /**
         * Define the export formats
         *
         * Each item in this array is an array which defines the formats, in the
         * following format:
         *
         * array(
         *    0 => 'Format Title',
         *    1 => 'Format Description',
         *    2 => 'formatMethod'
         * )
         *
         * The format method should be a callable method which is prefixed with
         * _export_format_, using the above as an example, the method would be:
         *
         * _export_format_formatMethod($data, $returnData = false)
         *
         * Where $data is the values generated from a source method. The method
         * should handle generating the file and sending to the user, unless
         * $returnData is true, in which case it should return the file's content
         */

        $this->exportFormats   = array();
        $this->exportFormats[] = array(
            'CSV',
            'Easily imports to many software packages, including Microsoft Excel.',
            'csv');

        $this->exportFormats[] = array(
            'HTML',
            'Produces an HTML table containing the data',
            'html');

        $this->exportFormats[] = array(
            'PDF',
            'Saves a PDF using the data from the HTML export option',
            'pdf');

        $this->exportFormats[] = array(
            'PHP Serialize',
            'Export as an object serialized using PHP\'s serialize() function',
            'serialize');

        $this->exportFormats[] = array(
            'JSON',
            'Export as a JSON array',
            'json');
    }

    // --------------------------------------------------------------------------

    /**
     * Send a test email
     * @return void
     */
    public function test_email()
    {
        //  Page Title
        $this->data['page']->title = lang ('utilities_test_email_title');

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Form validation and update
            $this->load->library('form_validation');

            //  Define rules
            $this->form_validation->set_rules('recipient', '', 'xss_clean|required|valid_email');

            //  Set Messages
            $this->form_validation->set_message('required', lang('fv_required'));
            $this->form_validation->set_message('valid_email', lang('fv_valid_email'));

            //  Execute
            if ($this->form_validation->run()) {

                //  Prepare date
                $email           = new \stdClass();
                $email->to_email = $this->input->post('recipient');
                $email->type     = 'test_email';
                $email->data     = array();

                //  Send the email
                if ($this->emailer->send($email)) {

                    $this->data['success'] = lang('utilities_test_email_success', array($email->to_email, date('Y-m-d H:i:s')));

                } else {

                    echo '<h1>' . lang('utilities_test_email_error') . '</h1>';
                    echo $this->email->print_debugger();
                    return;
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/utilities/send_test', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Rewrite the app's routes
     * @return void
     */
    public function rewrite_routes()
    {
        if ($this->input->post('go')) {

            $this->load->model('routes_model');

            if ($this->routes_model->update()) {

                $this->data['success'] = '<strong>Success!</strong> Routes rewritten successfully.';

            } else {

                $this->data['error']  = '<strong>Sorry,</strong> there was a problem writing the routes. ';
                $this->data['error'] .= $this->routes_model->last_error();
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/utilities/rewrite_routes', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Export data
     * @return void
     */
    public function export()
    {
        if ($this->input->post()) {

            //  Form validation and update
            $this->load->library('form_validation');

            //  Define rules
            $this->form_validation->set_rules('source', lang('utilities_export_field_source'), 'xss_clean|required');
            $this->form_validation->set_rules('format', lang('utilities_export_field_format'), 'xss_clean|required');

            //  Set Messages
            $this->form_validation->set_message('required', lang('fv_required'));

            //  Execute
            if ($this->form_validation->run() && isset($this->exportSources[$this->input->post('source')]) && isset($this->exportFormats[$this->input->post('format')])) {

                $source = $this->exportSources[$this->input->post('source')];
                $format = $this->exportFormats[$this->input->post('format')];

                if (!method_exists($this, '_export_source_' . $source[2])) {

                    $this->data['error'] = lang('utilities_export_error_source_notexist');

                } elseif (!method_exists($this, '_export_format_' . $format[2])) {

                    $this->data['error'] = lang('utilities_export_error_format_notexist');

                } else {

                    //  All seems well, export data!
                    $data = $this->{'_export_source_' . $source[2]}();

                    //  Anything to report?
                    if (!empty($data)) {

                        //  if $data is an array then we need to write multiple files to a zip
                        if (is_array($data)) {

                            //  Load Zip class
                            $this->load->library('zip');

                            //  Process each file
                            foreach ($data as $data) {

                                $file = $this->{'_export_format_' . $format[2]}($data, true);

                                $this->zip->add_data($file[0], $file[1]);
                            }

                            $this->zip->download('data-export-' . $source[2] . '-' . date('Y-m-d_H-i-s'));

                        } else {

                            $this->{'_export_format_' . $format[2]}($data);
                        }
                    }

                    return;
                }


            } elseif (!isset($this->exportSources[ $this->input->post('source') ])) {

                $this->data['error'] = lang('utilities_export_error_source');

            } elseif (!isset($this->exportFormats[ $this->input->post('format') ])) {

                $this->data['error'] = lang('utilities_export_error_format');

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['page']->title = lang('utilities_export_title');
        $this->data['sources']     = $this->exportSources;
        $this->data['formats']     = $this->exportFormats;

        // --------------------------------------------------------------------------

        //  Load views
        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/utilities/export/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Export Source: Users
     * @param  array  $out array of data to include in the output
     * @return array
     */
    protected function _export_source_users_all($out = array())
    {
        if (!user_has_permission('admin.accounts:0')) {

            return false;
        }

        // --------------------------------------------------------------------------

        //  Prepare our out array
        $out     = $out;
        $counter = count($out);

        //  User
        $out[$counter]           = new \stdClass();
        $out[$counter]->label    = 'Users';
        $out[$counter]->filename = NAILS_DB_PREFIX . 'user';
        $out[$counter]->fields   = array();
        $out[$counter]->data     = array();
        $counter++;

        //  user_group
        $out[$counter]           = new \stdClass();
        $out[$counter]->label    = 'User Groups';
        $out[$counter]->filename = NAILS_DB_PREFIX . 'user_group';
        $out[$counter]->fields   = array();
        $out[$counter]->data     = array();
        $counter++;

        //  user_meta
        $out[$counter]           = new \stdClass();
        $out[$counter]->label    = 'User Meta';
        $out[$counter]->filename = NAILS_DB_PREFIX . 'user_meta';
        $out[$counter]->fields   = array();
        $out[$counter]->data     = array();
        $counter++;

        //  Nails user_meta_* tables
        $tables = $this->db->query('SHOW TABLES LIKE \'' . NAILS_DB_PREFIX . 'user_meta_%\'')->result();
        foreach ($tables as $table) {

            $table = array_values((array) $table);

            $out[$counter]           = new \stdClass();
            $out[$counter]->label    = 'Table: ' . $table[0];
            $out[$counter]->filename = $table[0];
            $out[$counter]->fields   = array();
            $out[$counter]->data     = array();

            $counter++;
        }

        //  All other user_meta_* tables
        $tables = $this->db->query('SHOW TABLES LIKE \'user_meta_%\'')->result();
        foreach ($tables as $table) {

            $table = array_values((array) $table);

            $out[$counter]           = new \stdClass();
            $out[$counter]->label    = 'Table: ' . $table[0];
            $out[$counter]->filename = $table[0];
            $out[$counter]->fields   = array();
            $out[$counter]->data     = array();

            $counter++;
        }

        // --------------------------------------------------------------------------

        //  Fetch data
        foreach ($out as &$out) {

            $fields = $this->db->query('DESCRIBE ' . $out->filename)->result();
            foreach ($fields as $field) {

                $out->fields[] = $field->Field;
            }

            $out->data  = $this->db->get($out->filename)->result_array();
        }

        // --------------------------------------------------------------------------

        return $out;
    }

    // --------------------------------------------------------------------------

    /**
     * Export Source: Users (email)
     * @param  array  $out array of data to include in the output
     * @return array
     */
    protected function _export_source_users_email($out = array())
    {
        if (!user_has_permission('admin.accounts:0')) {

            return false;
        }

        // --------------------------------------------------------------------------

        //  Prepare our out array
        $out = $out;

        //  Fetch all users via the user_model
        $users = $this->user->get_all();

        //  Set column headings
        $out           = new \stdClass();
        $out->label    = 'Users';
        $out->filename = NAILS_DB_PREFIX . 'user';
        $out->fields   = array(
            'first_name',
            'last_name',
            'email'
        );
        $out->data = array();

        // --------------------------------------------------------------------------

        //  Add each user to the output array
        foreach ($users as $u) {

            $out->data[] = array(
                $u->first_name,
                $u->last_name,
                $u->email
            );
        }

        // --------------------------------------------------------------------------

        return $out;
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: CSV
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function _export_format_csv($data, $returnData = false)
    {
        //  Send header
        if (!$returnData) {

            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date('Y-m-d_H-i-s') . '.csv;');
            $this->output->set_header('Content-Transfer-Encoding: binary');
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['label']  = $data->label;
        $this->data['fields'] = $data->fields;
        $this->data['data']   = $data->data;

        // --------------------------------------------------------------------------

            //  Load view
        if (!$returnData) {

            $this->load->view('admin/utilities/export/csv', $this->data);

        } else {

            $out   = array();
            $out[] = $data->filename . '.csv';
            $out[] = $this->load->view('admin/utilities/export/csv', $this->data, true);

            return $out;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: HTML
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function _export_format_html($data, $returnData = false)
    {
        //  Send header
        if (!$returnData) {

            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date('Y-m-d_H-i-s') . '.html;');
            $this->output->set_header('Content-Transfer-Encoding: binary');
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['label']  = $data->label;
        $this->data['fields'] = $data->fields;
        $this->data['data']   = $data->data;

        // --------------------------------------------------------------------------

        //  Load view
        if (!$returnData) {

            $this->load->view('admin/utilities/export/html', $this->data);

        } else {

            $out    = array();
            $out[]  = $data->filename . '.html';
            $out[]  = $this->load->view('admin/utilities/export/html', $this->data, true);

            return $out;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: PDF
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function _export_format_pdf($data, $returnData = false)
    {
        $html = $this->_export_format_html($data, true);

        // --------------------------------------------------------------------------

        $this->load->library('pdf/pdf');
        $this->pdf->set_paper_size('A4', 'landscape');
        $this->pdf->load_html($html[1]);

        //  Load view
        if (!$returnData) {

            $this->pdf->download($data->filename . '.pdf');

        } else {

            $this->pdf->render();

            $out   = array();
            $out[] = $data->filename . '.pdf';
            $out[] = $this->pdf->output();

            $this->pdf->reset();

            return $out;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: Serialize
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function _export_format_serialize($data, $returnData = false)
    {
        //  Send header
        if (!$returnData) {

            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date('Y-m-d_H-i-s') . '.txt;');
            $this->output->set_header('Content-Transfer-Encoding: binary');
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['data'] = $data;

        // --------------------------------------------------------------------------

        //  Load view
        if (!$returnData) {

            $this->load->view('admin/utilities/export/serialize', $this->data);

        } else {

            $out   = array();
            $out[] = $data->filename . '.txt';
            $out[] = $this->load->view('admin/utilities/export/serialize', $this->data, true);

            return $out;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: JSON
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function _export_format_json($data, $returnData = false)
    {
        //  Send header
        if (!$returnData) {

            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date('Y-m-d_H-i-s') . '.json;');
            $this->output->set_header('Content-Transfer-Encoding: binary');
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['data'] = $data;

        // --------------------------------------------------------------------------

        //  Load view
        if (!$returnData) {

            $this->load->view('admin/utilities/export/json', $this->data);

        } else {

            $out   = array();
            $out[] = $data->filename . '.json';
            $out[] = $this->load->view('admin/utilities/export/json', $this->data, true);

            return $out;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Manage CDN utilities
     * @return void
     */
    public function cdn()
    {
        $method = $this->uri->segment(5) ? $this->uri->segment(5) : 'index';
        $method = ucfirst(strtolower($method));

        if (method_exists($this, 'cdn' . $method)) {

            //  Call method
            $this->{'cdn' . $method}();

        } else {

            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Find orphaned CDN objects
     * @return void
     */
    protected function cdnOrphans()
    {
        if ($this->input->is_cli_request()) {

            return $this->cdnOrphansCli();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  A little form validation
            $type   = $this->input->post('type');
            $parser = $this->input->post('parser');
            $pass   = true;

            if ($type == 'db' && $parser == 'create') {

                $pass   = false;
                $error  = 'Cannot use "Add to database" results parser when finding orphaned database objects.';
            }


            if ($pass) {

                switch ($type) {

                    case 'db':

                        $this->data['orphans']  = $this->cdn->find_orphaned_objects();
                        break;

                    //  @TODO
                    case 'file':

                        $this->data['message']  = '<strong>TODO:</strong> find orphaned files.';
                        break;

                    //  Invalid request
                    default:

                        $this->data['error']    = '<strong>Sorry,</strong> invalid search type.';
                        break;
                }

                if (isset($this->data['orphans'])) {

                    switch ($parser) {

                        case 'list':

                            $this->data['success'] = '<strong>Search complete!</strong> your results are show below.';
                            break;

                        //  TODO: keep the unset(), it prevents the table from rendering
                        case 'purge':

                            $this->data['message'] = '<strong>TODO:</strong> purge results.'; unset($this->data['orphans']);
                            break;

                        case 'create':

                            $this->data['message'] = '<strong>TODO:</strong> create objects using results.'; unset($this->data['orphans']);
                            break;

                        //  Invalid request
                        default:

                            $this->data['error'] = '<strong>Sorry,</strong> invalid result parse selected.'; unset($this->data['orphans']);
                            break;
                    }
                }

            } else {

                $this->data['error'] = '<strong>Sorry,</strong> an error occurred. ' . $error;
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'CDN: Find Orphaned Objects';

        // --------------------------------------------------------------------------

        $this->asset->load('nails.admin.utilities.cdn.orphans.min.js', true);

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/utilities/cdn/orphans', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Find orphaned CDN objects (command line)
     * @return void
     */
    protected function cdnOrphansCli()
    {
        //  @TODO: Complete CLI functionality for report generating
        echo 'Sorry, this functionality is not complete yet. If you are experiencing timeouts please increase the timeout limit for PHP.';
    }
}
