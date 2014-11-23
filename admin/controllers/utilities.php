<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin: Utilities
 * Description:	Various admin utilities
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

class NAILS_Utilities extends NAILS_Admin_Controller
{
	protected $_export_sources;
	protected $_export_formats;


	// --------------------------------------------------------------------------


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
		get_instance()->lang->load( 'admin_utilities' );

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = lang( 'utilities_module_name' );
		$d->icon = 'fa-sliders';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs					= array();
		$d->funcs['test_email']		= lang( 'utilities_nav_test_email' );
		$d->funcs['rewrite_routes']	= lang( 'utilities_nav_rewrite_routes' );
		$d->funcs['export']			= lang( 'utilities_nav_export' );

		if ( module_is_enabled( 'cdn' ) ) :

			$d->funcs['cdn/orphans']	= 'CDN: Find orphaned objects';

		endif;

		// --------------------------------------------------------------------------

		return $d;
	}


	// --------------------------------------------------------------------------


	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		//	Default export sources
		$this->_export_sources = array();

		if ( user_has_permission( 'admin.accounts:0' ) ) :

			$this->_export_sources[] = array( 'Members: All', 'Export a list of all the site\'s registered users and their meta data.', 'users_all' );

		endif;

		// --------------------------------------------------------------------------

		//	Default export formats
		$this->_export_formats		= array();
		$this->_export_formats[]	= array( 'CSV', 'Easily imports to many software packages, including Microsoft Excel.', 'csv' );
		$this->_export_formats[]	= array( 'HTML', 'Produces an HTML table containing the data', 'html' );
		$this->_export_formats[]	= array( 'PDF', 'Saves a PDF using the data from the HTML export option', 'pdf' );
		$this->_export_formats[]	= array( 'PHP Serialize', 'Export as an object serialized using PHP\'s serialize() function', 'serialize' );
		$this->_export_formats[]	= array( 'JSON', 'Export as a JSON array', 'json' );
	}

	// --------------------------------------------------------------------------


	/**
	 * Send test email
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function test_email()
	{
		//	Page Title
		$this->data['page']->title = lang ( 'utilities_test_email_title' );

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	Form validation and update
			$this->load->library( 'form_validation' );

			//	Define rules
			$this->form_validation->set_rules( 'recipient',	lang( 'utilities_test_email_field_name' ), 'xss_clean|required|valid_email' );

			//	Set Messages
			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'valid_email',	lang( 'fv_valid_email' ) );

			//	Execute
			if ( $this->form_validation->run() ) :

				//	Prepare date
				$_email				= new stdClass();
				$_email->to_email	= $this->input->post( 'recipient' );
				$_email->type		= 'test_email';
				$_email->data		= array();

				//	Send the email
				if ( $this->emailer->send( $_email ) ) :

					$this->data['success'] = lang( 'utilities_test_email_success', array( $_email->to_email, date( 'Y-m-d H:i:s' ) ) );

				else:

					echo '<h1>' . lang( 'utilities_test_email_error' ) . '</h1>';
					echo $this->email->print_debugger();
					return;

				endif;

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/utilities/send_test',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	public function rewrite_routes()
	{
		if ( $this->input->post( 'go' ) ) :

			$this->load->model( 'system/routes_model' );

			if ( $this->routes_model->update() ) :

				$this->data['success'] = '<strong>Success!</strong> Routes rewritten successfully.';

			else :

				$this->data['error'] = '<strong>Sorry,</strong> there was a problem writing the routes. ' . $this->routes_model->last_error();

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/utilities/rewrite_routes',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	public function export()
	{
		if ( $this->input->post() ) :

			//	Form validation and update
			$this->load->library( 'form_validation' );

			//	Define rules
			$this->form_validation->set_rules( 'source',	lang( 'utilities_export_field_source' ), 'xss_clean|required' );
			$this->form_validation->set_rules( 'format',	lang( 'utilities_export_field_format' ), 'xss_clean|required' );

			//	Set Messages
			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );

			//	Execute
			if ( $this->form_validation->run() && isset( $this->_export_sources[$this->input->post( 'source' )] ) && isset( $this->_export_formats[$this->input->post( 'format' )] ) ) :

				$_source = $this->_export_sources[$this->input->post( 'source' )];
				$_format = $this->_export_formats[$this->input->post( 'format' )];

				if ( ! method_exists( $this, '_export_source_' . $_source[2] ) ) :

					$this->data['error'] = lang( 'utilities_export_error_source_notexist' );

				elseif ( ! method_exists( $this, '_export_format_' . $_format[2] ) ) :

					$this->data['error'] = lang( 'utilities_export_error_format_notexist' );

				else :

					//	All seems well, export data!
					$_data = $this->{'_export_source_' . $_source[2]}();

					//	Anything to report?
					if ( ! empty( $_data ) ) :

						//	if $_data is an array then we need to write multiple files to a zip
						if ( is_array( $_data ) ) :

							//	Load Zip class
							$this->load->library( 'zip' );

							//	Process each file
							foreach( $_data AS $data ) :

								$_file = $this->{'_export_format_' . $_format[2]}( $data, TRUE );

								$this->zip->add_data( $_file[0], $_file[1] );

							endforeach;

							$this->zip->download( 'data-export-' . $_source[2] . '-' . date( 'Y-m-d_H-i-s' ) );

						else :

							$this->{'_export_format_' . $_format[2]}( $_data );

						endif;

					endif;

					return;

				endif;


			elseif ( ! isset( $this->_export_sources[ $this->input->post( 'source' ) ] ) ) :

				$this->data['error'] = lang( 'utilities_export_error_source' );

			elseif ( ! isset( $this->_export_formats[ $this->input->post( 'format' ) ] ) ) :

				$this->data['error'] = lang( 'utilities_export_error_format' );

			else:

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Set view data
		$this->data['page']->title	= lang( 'utilities_export_title' );
		$this->data['sources']		= $this->_export_sources;
		$this->data['formats']		= $this->_export_formats;

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/utilities/export/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _export_source_users_all( $out = array() )
	{
		if ( ! user_has_permission( 'admin.accounts:0' ) ) :

			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		//	Prepare our out array
		$_out		= $out;
		$_counter	= count( $_out );

		//	User
		$_out[$_counter]			= new stdClass();
		$_out[$_counter]->label		= 'Users';
		$_out[$_counter]->filename	= NAILS_DB_PREFIX . 'user';
		$_out[$_counter]->fields	= array();
		$_out[$_counter]->data		= array();
		$_counter++;

		//	user_group
		$_out[$_counter]			= new stdClass();
		$_out[$_counter]->label		= 'User Groups';
		$_out[$_counter]->filename	= NAILS_DB_PREFIX . 'user_group';
		$_out[$_counter]->fields	= array();
		$_out[$_counter]->data		= array();
		$_counter++;

		//	user_meta
		$_out[$_counter]			= new stdClass();
		$_out[$_counter]->label		= 'User Meta';
		$_out[$_counter]->filename	= NAILS_DB_PREFIX . 'user_meta';
		$_out[$_counter]->fields	= array();
		$_out[$_counter]->data		= array();
		$_counter++;

		//	Nails user_meta_* tables
		$_tables = $this->db->query( 'SHOW TABLES LIKE \'' . NAILS_DB_PREFIX . 'user_meta_%\'' )->result();
		foreach( $_tables AS $table ) :

			$_table = array_values( (array) $table );

			$_out[$_counter]			= new stdClass();
			$_out[$_counter]->label		= 'Table: ' . $_table[0];
			$_out[$_counter]->filename	= $_table[0];
			$_out[$_counter]->fields	= array();
			$_out[$_counter]->data		= array();

			$_counter++;

		endforeach;

		//	All other user_meta_* tables
		$_tables = $this->db->query( 'SHOW TABLES LIKE \'user_meta_%\'' )->result();
		foreach( $_tables AS $table ) :

			$_table = array_values( (array) $table );

			$_out[$_counter]			= new stdClass();
			$_out[$_counter]->label		= 'Table: ' . $_table[0];
			$_out[$_counter]->filename	= $_table[0];
			$_out[$_counter]->fields	= array();
			$_out[$_counter]->data		= array();

			$_counter++;

		endforeach;

		// --------------------------------------------------------------------------

		//	Fetch data
		foreach( $_out AS &$out ) :

			$_fields = $this->db->query( 'DESCRIBE ' . $out->filename )->result();
			foreach ( $_fields AS $field ) :

				$out->fields[] = $field->Field;

			endforeach;

			$out->data	= $this->db->get( $out->filename )->result_array();

		endforeach;

		// --------------------------------------------------------------------------

		return $_out;
	}


	// --------------------------------------------------------------------------


	protected function _export_format_csv( $data, $return_data = FALSE )
	{
		//	Send header
		if ( ! $return_data ) :

			$this->output->set_content_type( 'application/octet-stream' );
			$this->output->set_header( 'Pragma: public' );
			$this->output->set_header( 'Expires: 0' );
			$this->output->set_header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			$this->output->set_header( 'Cache-Control: private', FALSE );
			$this->output->set_header( 'Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date( 'Y-m-d_H-i-s' ) . '.csv;' );
			$this->output->set_header( 'Content-Transfer-Encoding: binary' );

		endif;

		// --------------------------------------------------------------------------

		//	Set view data
		$this->data['label']	= $data->label;
		$this->data['fields']	= $data->fields;
		$this->data['data']		= $data->data;

		// --------------------------------------------------------------------------

			//	Load view
		if ( ! $return_data ) :

			$this->load->view( 'admin/utilities/export/csv', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.csv';
			$_out[]	= $this->load->view( 'admin/utilities/export/csv', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _export_format_html( $data, $return_data = FALSE )
	{
		//	Send header
		if ( ! $return_data ) :

			$this->output->set_content_type( 'application/octet-stream' );
			$this->output->set_header( 'Pragma: public' );
			$this->output->set_header( 'Expires: 0' );
			$this->output->set_header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			$this->output->set_header( 'Cache-Control: private', FALSE );
			$this->output->set_header( 'Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date( 'Y-m-d_H-i-s' ) . '.html;' );
			$this->output->set_header( 'Content-Transfer-Encoding: binary' );

		endif;

		// --------------------------------------------------------------------------

		//	Set view data
		$this->data['label']	= $data->label;
		$this->data['fields']	= $data->fields;
		$this->data['data']		= $data->data;

		// --------------------------------------------------------------------------

		//	Load view
		if ( ! $return_data ) :

			$this->load->view( 'admin/utilities/export/html', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.html';
			$_out[]	= $this->load->view( 'admin/utilities/export/html', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _export_format_pdf( $data, $return_data = FALSE )
	{
		$_html = $this->_export_format_html( $data, TRUE );

		// --------------------------------------------------------------------------

		$this->load->library( 'pdf/pdf' );
		$this->pdf->set_paper_size( 'A4', 'landscape' );
		$this->pdf->load_html( $_html[1] );

		//	Load view
		if ( ! $return_data ) :

			$this->pdf->download( $data->filename . '.pdf' );

		else :

			$this->pdf->render();

			$_out	= array();
			$_out[]	= $data->filename . '.pdf';
			$_out[]	= $this->pdf->output();

			$this->pdf->reset();

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _export_format_serialize( $data, $return_data = FALSE )
	{
		//	Send header
		if ( ! $return_data ) :

			$this->output->set_content_type( 'application/octet-stream' );
			$this->output->set_header( 'Pragma: public' );
			$this->output->set_header( 'Expires: 0' );
			$this->output->set_header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			$this->output->set_header( 'Cache-Control: private', FALSE );
			$this->output->set_header( 'Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date( 'Y-m-d_H-i-s' ) . '.txt;' );
			$this->output->set_header( 'Content-Transfer-Encoding: binary' );

		endif;

		// --------------------------------------------------------------------------

		//	Set view data
		$this->data['data'] = $data;

		// --------------------------------------------------------------------------

		//	Load view
		if ( ! $return_data ) :

			$this->load->view( 'admin/utilities/export/serialize', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.txt';
			$_out[]	= $this->load->view( 'admin/utilities/export/serialize', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _export_format_json( $data, $return_data = FALSE )
	{
		//	Send header
		if ( ! $return_data ) :

			$this->output->set_content_type( 'application/octet-stream' );
			$this->output->set_header( 'Pragma: public' );
			$this->output->set_header( 'Expires: 0' );
			$this->output->set_header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			$this->output->set_header( 'Cache-Control: private', FALSE );
			$this->output->set_header( 'Content-Disposition: attachment; filename=data-export-' . $data->filename . '-' . date( 'Y-m-d_H-i-s' ) . '.json;' );
			$this->output->set_header( 'Content-Transfer-Encoding: binary' );

		endif;

		// --------------------------------------------------------------------------

		//	Set view data
		$this->data['data'] = $data;

		// --------------------------------------------------------------------------

		//	Load view
		if ( ! $return_data ) :

			$this->load->view( 'admin/utilities/export/json', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.json';
			$_out[]	= $this->load->view( 'admin/utilities/export/json', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	public function cdn()
	{
		switch ( $this->uri->segment( 4 ) ) :

			case 'orphans' :	$this->_cdn_orphans();	break;
			default :			show_404();				break;

		endswitch;
	}


	// --------------------------------------------------------------------------


	protected function _cdn_orphans()
	{
		if ( $this->input->is_cli_request() ) :

			return $this->_cdn_orphans_cli();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	A little form validation
			$_type		= $this->input->post( 'type' );
			$_parser	= $this->input->post( 'parser' );
			$_pass		= TRUE;

			if ( $_type == 'db' && $_parser == 'create' ) :

				$_pass	= FALSE;
				$_error	= 'Cannot use "Add to database" results parser when finding orphaned database objects.';

			endif;


			if ( $_pass ) :

				switch( $_type ) :

					case 'db'	:	$this->data['orphans']	= $this->cdn->find_orphaned_objects();				break;

					//	TODO
					case 'file'	:	$this->data['message']	= '<strong>TODO:</strong> find orphaned files.';	break;

					//	Invalid request
					default		:	$this->data['error']	= '<strong>Sorry,</strong> invalid search type.';	break;

				endswitch;

				if ( isset( $this->data['orphans'] ) ) :

					switch( $_parser ) :

						case 'list'		:	$this->data['success'] = '<strong>Search complete!</strong> your results are show below.';								break;

						//	TODO: keep the unset(), it prevents the table from rendering
						case 'purge'	:	$this->data['message']	= '<strong>TODO:</strong> purge results.'; unset( $this->data['orphans'] );						break;
						case 'create'	:	$this->data['message']	= '<strong>TODO:</strong> create objects using results.'; unset( $this->data['orphans'] );		break;

						//	Invalid request
						default			:	$this->data['error']	= '<strong>Sorry,</strong> invalid result parse selected.'; unset( $this->data['orphans'] );	break;

					endswitch;

				endif;

			else :

				$this->data['error'] = '<strong>Sorry,</strong> an error occurred. ' . $_error;

			endif;

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'CDN: Find Orphaned Objects';

		// --------------------------------------------------------------------------

		$this->asset->load( 'nails.admin.utilities.cdn.orphans.min.js', TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/utilities/cdn/orphans',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _cdn_orphans_cli()
	{
		//	TODO: Complete CLI functionality for report generating
		echo 'Sorry, this functionality is not complete yet. If you are experiencing timeouts please increase the timeout limit for PHP.';
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

if ( ! defined( 'NAILS_ALLOW_EXTENSION_UTILITIES' ) ) :

	class Utilities extends NAILS_Utilities
	{
	}

endif;


/* End of file utilities.php */
/* Location: ./modules/admin/controllers/utilities.php */