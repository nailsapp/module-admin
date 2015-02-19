<?php

/**
 * This class renders Admin Utilities
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
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
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Utilities', 'fa-sliders');

        if (userHasPermission('admin:admin:utilities:rewriteRoutes')) {

            $navGroup->addAction('Rewrite Routes', 'rewrite_routes');
        }

        if (userHasPermission('admin:admin:utilities:export')) {

            $navGroup->addAction('Export Data', 'export');
        }

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['rewriteRoutes'] = 'Can Rewrite Routes';
        $permissions['export']        = 'Can Export Data';

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
         * exportSource, using the above as an example, the method would be:
         *
         * exportSourcesourceMethod()
         *
         * This method should return an array where the indexes are the column
         * names and the values are not arrays, i.e stuff which would fit into
         * a single cell in Excel).
         */

        $this->exportSources = array();

        if (userHasPermission('admin:auth:accounts:browse')) {

            $this->exportSources[] = array(
                'Members: All',
                'Export a list of all the site\'s registered users and their meta data.',
                'UsersAll'
            );

            $this->exportSources[] = array(
                'Members: Names and Email',
                'Export a list of all the site\'s registered users and their email addresses.',
                'UsersEmail'
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
         *    2 => 'FormatMethod'
         * )
         *
         * The format method should be a callable method which is prefixed with
         * exportFormat, using the above as an example, the method would be:
         *
         * exportFormatFormatMethod($data, $returnData = false)
         *
         * Where $data is the values generated from a source method. The method
         * should handle generating the file and sending to the user, unless
         * $returnData is true, in which case it should return the file's content
         */

        $this->exportFormats   = array();
        $this->exportFormats[] = array(
            'CSV',
            'Easily imports to many software packages, including Microsoft Excel.',
            'Csv');

        $this->exportFormats[] = array(
            'HTML',
            'Produces an HTML table containing the data',
            'Html');

        $this->exportFormats[] = array(
            'PDF',
            'Saves a PDF using the data from the HTML export option',
            'Pdf');

        $this->exportFormats[] = array(
            'PHP Serialize',
            'Export as an object serialized using PHP\'s serialize() function',
            'Serialize');

        $this->exportFormats[] = array(
            'JSON',
            'Export as a JSON array',
            'Json');
    }

    // --------------------------------------------------------------------------

    /**
     * Rewrite the app's routes
     * @return void
     */
    public function rewrite_routes()
    {
        if (!userHasPermission('admin:admin:utilities:rewriteRoutes')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post('go')) {

            $this->load->model('routes_model');

            if ($this->routes_model->update()) {

                $this->data['success'] = 'Routes rewritten successfully.';

            } else {

                $this->data['error']  = 'There was a problem writing the routes. ';
                $this->data['error'] .= $this->routes_model->last_error();
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('rewriteRoutes');
    }

    // --------------------------------------------------------------------------

    /**
     * Export data
     * @return void
     */
    public function export()
    {
        if (!userHasPermission('admin:admin:utilities:export')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Form validation and update
            $this->load->library('form_validation');

            //  Define rules
            $this->form_validation->set_rules('source', '', 'xss_clean|required');
            $this->form_validation->set_rules('format', '', 'xss_clean|required');

            //  Set Messages
            $this->form_validation->set_message('required', lang('fv_required'));

            //  Execute
            if ($this->form_validation->run() && isset($this->exportSources[$this->input->post('source')]) && isset($this->exportFormats[$this->input->post('format')])) {

                $source = $this->exportSources[$this->input->post('source')];
                $format = $this->exportFormats[$this->input->post('format')];

                if (!method_exists($this, 'exportSource' . $source[2])) {

                    $this->data['error'] = 'That data source is not available.';

                } elseif (!method_exists($this, 'exportFormat' . $format[2])) {

                    $this->data['error'] = 'That format type is not available.';

                } else {

                    //  All seems well, export data!
                    $results = $this->{'exportSource' . $source[2]}();

                    //  Anything to report?
                    if (!empty($results)) {

                        //  if $results is an array then we need to write multiple files to a zip
                        if (is_array($results)) {

                            //  Load Zip class
                            $this->load->library('zip');

                            //  Process each file
                            foreach ($results as $result) {

                                $file = $this->{'exportFormat' . $format[2]}($result, true);

                                $this->zip->add_data($file[0], $file[1]);
                            }

                            $this->zip->download('data-export-' . $source[2] . '-' . date('Y-m-d_H-i-s'));

                        } else {

                            $this->{'exportFormat' . $format[2]}($results);
                        }
                    }

                    return;
                }


            } elseif (!isset($this->exportSources[ $this->input->post('source') ])) {

                $this->data['error'] = 'Invalid data source.';

            } elseif (!isset($this->exportFormats[ $this->input->post('format') ])) {

                $this->data['error'] = 'Invalid format type.';

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['page']->title = 'Export Data';
        $this->data['sources']     = $this->exportSources;
        $this->data['formats']     = $this->exportFormats;

        // --------------------------------------------------------------------------

        //  Load views
        \Nails\Admin\Helper::loadView('export/index');
    }

    // --------------------------------------------------------------------------

    /**
     * Export Source: Users
     * @param  array  $out array of data to include in the output
     * @return array
     */
    protected function exportSourceUsersAll($out = array())
    {
        if (!userHasPermission('admin:auth:accounts:browse')) {

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

        //  useR_email
        $out[$counter]           = new \stdClass();
        $out[$counter]->label    = 'User Email';
        $out[$counter]->filename = NAILS_DB_PREFIX . 'user_email';
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
        foreach ($out as &$file) {

            $fields = $this->db->query('DESCRIBE ' . $file->filename)->result();
            foreach ($fields as $field) {

                $file->fields[] = $field->Field;
            }

            $file->data  = $this->db->get($file->filename)->result_array();
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
    protected function exportSourceUsersEmail($out = array())
    {
        if (!userHasPermission('admin:auth:accounts:browse')) {

            return false;
        }

        // --------------------------------------------------------------------------

        //  Prepare our out array
        $out = $out;

        //  Fetch all users via the user_model
        $users = $this->user_model->get_all();

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
    protected function exportFormatCsv($data, $returnData = false)
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

            \Nails\Admin\Helper::loadView('export/csv', false);

        } else {

            $out   = array();
            $out[] = $data->filename . '.csv';
            $out[] = \Nails\Admin\Helper::loadView('export/csv', false, true);

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
    protected function exportFormatHtml($data, $returnData = false)
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

            \Nails\Admin\Helper::loadView('export/html', false);

        } else {

            $out    = array();
            $out[]  = $data->filename . '.html';
            $out[] = \Nails\Admin\Helper::loadView('export/html', false, true);

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
    protected function exportFormatPdf($data, $returnData = false)
    {
        $html = $this->exportFormatHtml($data, true);

        // --------------------------------------------------------------------------

        $this->load->library('pdf/pdf');
        $this->pdf->set_paper_size('A4', 'landscape');
        $this->pdf->load_html($html[1]);

        //  Load view
        if (!$returnData) {

            if (!$this->pdf->download($data->filename . '.pdf')) {

                $status  = 'error';
                $message = 'Failed to render PDF. ';
                $message .= $this->pdf->last_error() ? 'DOMPDF gave the following error: ' . $this->pdf->last_error() : '';

                $this->session->set_flashdata($status, $message);
                redirect('admin/shop/reports');
            }

        } else {

            try {

                $this->pdf->render();

                $out   = array();
                $out[] = $data->filename . '.pdf';
                $out[] = $this->pdf->output();

                $this->pdf->reset();

                return $out;

            } catch (Exception $e) {

                $status   = 'error';
                $message  = 'Failed to render PDF. The following exception was raised: ';
                $message .= $e->getMessage();

                $this->session->set_flashdata($status, $message);
                redirect('admin/shop/reports');
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Export Format: Serialize
     * @param  array   $data       The data to export
     * @param  boolean $returnData Whether or not to return the data, or output it to the browser
     * @return mixed
     */
    protected function exportFormatSerialize($data, $returnData = false)
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

            \Nails\Admin\Helper::loadView('export/serialize', false);

        } else {

            $out   = array();
            $out[] = $data->filename . '.txt';
            $out[] = \Nails\Admin\Helper::loadView('export/serialize', false, true);

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
    protected function exportFormatJson($data, $returnData = false)
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

            \Nails\Admin\Helper::loadView('export/json', false);

        } else {

            $out   = array();
            $out[] = $data->filename . '.json';
            $out[] = \Nails\Admin\Helper::loadView('export/json', false, true);

            return $out;
        }
    }
}
