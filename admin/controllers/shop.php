<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Admin: Shop
* Description:	Shop Manager
*
*/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Shop extends NAILS_Admin_Controller
{
	protected $_report_sources;
	protected $_report_formats;

	// --------------------------------------------------------------------------

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access static
	 * @param none
	 * @return void
	 **/
	static function announce()
	{
		if ( ! module_is_enabled( 'shop' ) ) :

			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = 'Shop';
		$d->icon = 'fa-shopping-cart';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs				= array();

		if ( user_has_permission( 'admin.shop:0.inventory_manage' ) ) :

			$d->funcs['inventory'] = 'Manage Inventory';

		endif;

		if ( user_has_permission( 'admin.shop:0.orders_manage' ) ) :

			$d->funcs['orders'] = 'Manage Orders';

		endif;

		if ( user_has_permission( 'admin.shop:0.vouchers_manage' ) ) :

			$d->funcs['vouchers'] = 'Manage Vouchers';

		endif;

		if ( user_has_permission( 'admin.shop:0.sale_manage' ) ) :

			$d->funcs['sales'] = 'Manage Sales';

		endif;

		//	TODO: Handle permissions here?
		$d->funcs['manage'] = 'Other Managers';

		if ( user_has_permission( 'admin.shop:0.can_generate_reports' ) ) :

			$d->funcs['reports'] = 'Generate Reports';

		endif;

		if ( user_has_permission( 'admin.shop:0.notifications_manage' ) ) :

			$d->funcs['product_availability_notifications'] = 'Product Availability Notifications';

		endif;

		// --------------------------------------------------------------------------

		return $d;
	}


	// --------------------------------------------------------------------------


	/**
	 * Returns an array of notifications for various methods
	 *
	 * @access	static
	 * @param	none
	 * @return	void
	 **/
	static function notifications()
	{
		$_ci =& get_instance();
		$_notifications = array();

		// --------------------------------------------------------------------------

		get_instance()->load->model( 'shop/shop_order_model' );

		$_notifications['orders']			= array();
		$_notifications['orders']['type']	= 'alert';
		$_notifications['orders']['title']	= 'Unfulfilled orders';
		$_notifications['orders']['value']	= get_instance()->shop_order_model->count_unfulfilled_orders();

		// --------------------------------------------------------------------------

		return $_notifications;
	}


	// --------------------------------------------------------------------------


	static function permissions( $class_index = NULL )
	{
		$_permissions = parent::permissions( $class_index );

		// --------------------------------------------------------------------------

		//	Inventory
		$_permissions['inventory_manage']		= 'Inventory: Manage';
		$_permissions['inventory_create']		= 'Inventory: Create';
		$_permissions['inventory_edit']			= 'Inventory: Edit';
		$_permissions['inventory_delete']		= 'Inventory: Delete';
		$_permissions['inventory_restore']		= 'Inventory: Restore';

		//	Orders
		$_permissions['orders_manage']			= 'Orders: Manage';
		$_permissions['orders_view']			= 'Orders: View';
		$_permissions['orders_reprocess']		= 'Orders: Reprocess';
		$_permissions['orders_process']			= 'Orders: Process';

		//	Vouchers
		$_permissions['vouchers_manage']		= 'Vouchers: Manage';
		$_permissions['vouchers_create']		= 'Vouchers: Create';
		$_permissions['vouchers_activate']		= 'Vouchers: Activate';
		$_permissions['vouchers_deactivate']	= 'Vouchers: Deactivate';

		//	Attributes
		$_permissions['attribute_create']		= 'Attribute: Create';
		$_permissions['attribute_create']		= 'Attribute: Create';
		$_permissions['attribute_edit']			= 'Attribute: Edit';
		$_permissions['attribute_delete']		= 'Attribute: Delete';

		//	Brands
		$_permissions['brand_manage']			= 'Brand: Manage';
		$_permissions['brand_create']			= 'Brand: Create';
		$_permissions['brand_edit']				= 'Brand: Edit';
		$_permissions['brand_delete']			= 'Brand: Delete';

		//	Categories
		$_permissions['category_manage']		= 'Category: Manage';
		$_permissions['category_create']		= 'Category: Create';
		$_permissions['category_edit']			= 'Category: Edit';
		$_permissions['category_delete']		= 'Category: Delete';

		//	Collections
		$_permissions['collection_manage']		= 'Collection: Manage';
		$_permissions['collection_create']		= 'Collection: Create';
		$_permissions['collection_edit']		= 'Collection: Edit';
		$_permissions['collection_delete']		= 'Collection: Delete';

		//	Ranges
		$_permissions['range_manage']			= 'Range: Manage';
		$_permissions['range_create']			= 'Range: Create';
		$_permissions['range_edit']				= 'Range: Edit';
		$_permissions['range_delete']			= 'Range: Delete';

		//	Sales
		$_permissions['sale_manage']			= 'Sale: Manage';
		$_permissions['sale_create']			= 'Sale: Create';
		$_permissions['sale_edit']				= 'Sale: Edit';
		$_permissions['sale_delete']			= 'Sale: Delete';

		//	Tags
		$_permissions['tag_manage']				= 'Tag: Manage';
		$_permissions['tag_create']				= 'Tag: Create';
		$_permissions['tag_edit']				= 'Tag: Edit';
		$_permissions['tag_delete']				= 'Tag: Delete';

		//	Tax Rates
		$_permissions['tax_rate_manage']		= 'Tax Rate: Manage';
		$_permissions['tax_rate_create']		= 'Tax Rate: Create';
		$_permissions['tax_rate_edit']			= 'Tax Rate: Edit';
		$_permissions['tax_rate_delete']		= 'Tax Rate: Delete';

		//	Product Types
		$_permissions['product_type_manage']	= 'Product Type: Manage';
		$_permissions['product_type_create']	= 'Product Type: Create';
		$_permissions['product_type_edit']		= 'Product Type: Edit';
		$_permissions['product_type_delete']	= 'Product Type: Delete';

		//	Product Type Meta Fields
		$_permissions['product_type_meta_manage']	= 'Product Type Meta: Manage';
		$_permissions['product_type_meta_create']	= 'Product Type Meta: Create';
		$_permissions['product_type_meta_edit']		= 'Product Type Meta: Edit';
		$_permissions['product_type_meta_delete']	= 'Product Type Meta: Delete';

		//	Reports
		$_permissions['can_generate_reports']	= 'Can generate Reports';

		//	Notifications
		$_permissions['notifications_manage']	= 'Can manage Product notifications';
		$_permissions['notifications_create']	= 'Can create Product notifications';
		$_permissions['notifications_edit']		= 'Can edit Product notifications';
		$_permissions['notifications_delete']	= 'Can delete Product notifications';

		// --------------------------------------------------------------------------

		return $_permissions;
	}


	// --------------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		//	Defaults defaults
		$this->shop_inventory_group			= FALSE;
		$this->shop_inventory_where			= array();
		$this->shop_inventory_actions		= array();
		$this->shop_inventory_sortfields	= array();

		$this->shop_orders_group			= FALSE;
		$this->shop_orders_where			= array();
		$this->shop_orders_actions			= array();
		$this->shop_orders_sortfields		= array();

		$this->shop_vouchers_group			= FALSE;
		$this->shop_vouchers_where			= array();
		$this->shop_vouchers_actions		= array();
		$this->shop_vouchers_sortfields		= array();

		// --------------------------------------------------------------------------

		$this->shop_inventory_sortfields[] = array( 'label' => 'ID',				'col' => 'p.id' );
		$this->shop_inventory_sortfields[] = array( 'label' => 'Title',				'col' => 'p.label' );
		$this->shop_inventory_sortfields[] = array( 'label' => 'Type',				'col' => 'pt.label' );
		$this->shop_inventory_sortfields[] = array( 'label' => 'Active',			'col' => 'p.is_active' );
		$this->shop_inventory_sortfields[] = array( 'label' => 'Modified',			'col' => 'p.modified' );

		$this->shop_orders_sortfields[] = array( 'label' => 'ID',				'col' => 'o.id' );
		$this->shop_orders_sortfields[] = array( 'label' => 'Date Placed',		'col' => 'o.created' );
		$this->shop_orders_sortfields[] = array( 'label' => 'Last Modified',	'col' => 'o.modified' );
		$this->shop_orders_sortfields[] = array( 'label' => 'Value',			'col' => 'o.grand_total' );

		$this->shop_vouchers_sortfields[] = array( 'label' => 'ID',				'col' => 'v.id' );
		$this->shop_vouchers_sortfields[] = array( 'label' => 'Code',			'col' => 'v.code' );

		// --------------------------------------------------------------------------

		//	Load models which this controller depends on
		$this->load->model( 'shop/shop_model' );
		$this->load->model( 'shop/shop_currency_model' );
		$this->load->model( 'shop/shop_product_model' );
		$this->load->model( 'shop/shop_product_type_model' );
		$this->load->model( 'shop/shop_tax_rate_model' );
		$this->load->model( 'shop/shop_product_type_meta_model' );

		// --------------------------------------------------------------------------

		$this->data['shop_url'] = app_setting( 'url', 'shop' ) ? app_setting( 'url', 'shop' ) : 'shop/';
	}


	// --------------------------------------------------------------------------


	/**
	 * Manage the inventory
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function inventory()
	{
		if ( ! user_has_permission( 'admin.shop:0.inventory_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_inventory_' . $_method ) ) :

			$this->{'_inventory_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _inventory_index()
	{
		//	Set method info
		$this->data['page']->title = 'Manage Inventory';

		//	Define the $_data variable, this'll be passed to the get_all() and count_all() methods
		$_data = array( 'where' => array(), 'sort' => array(), 'include_inactive' => TRUE );

		// --------------------------------------------------------------------------

		//	Set useful vars
		$_page			= $this->input->get( 'page' )		? $this->input->get( 'page' )		: 0;
		$_per_page		= $this->input->get( 'per_page' )	? $this->input->get( 'per_page' )	: 50;
		$_sort_on		= $this->input->get( 'sort_on' )	? $this->input->get( 'sort_on' )	: 'p.label';
		$_sort_order	= $this->input->get( 'order' )		? $this->input->get( 'order' )		: 'desc';
		$_search		= $this->input->get( 'search' )		? $this->input->get( 'search' )		: '';

		//	Set sort variables for view and for $_data
		$this->data['sort_on']		= $_data['sort']['column']	= $_sort_on;
		$this->data['sort_order']	= $_data['sort']['order']	= $_sort_order;
		$this->data['search']		= $_data['search']			= $_search;

		//	Define and populate the pagination object
		$this->data['pagination']				= new stdClass();
		$this->data['pagination']->page			= $_page;
		$this->data['pagination']->per_page		= $_per_page;
		$this->data['pagination']->total_rows	= $this->shop_product_model->count_all( $_data );

		//	Fetch all the items for this page
		$this->data['products']			= $this->shop_product_model->get_all( $_page, $_per_page, $_data );
		$this->data['product_types']	= $this->shop_product_type_model->get_all();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/inventory/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _inventory_create()
	{
		$this->data['page']->title = 'Add new Inventory Item';

		// --------------------------------------------------------------------------

		//	Fetch data, this data is used in both the view and the form submission
		$this->data['currencies']		= $this->shop_currency_model->get_all_supported();
		$this->data['product_types']	= $this->shop_product_type_model->get_all();

		if ( ! $this->data['product_types'] ) :

			//	No Product types, some need added, yo!
			$this->session->set_flashdata( 'message', '<strong>Hey!</strong> No product types have been defined. You must set some before you can add inventory items.' );
			redirect( 'admin/shop/manage/product_type/create' );

		endif;

		// --------------------------------------------------------------------------

		//	Fetch product type meta fields
		$this->data['product_types_meta'] = array();
		$this->load->model( 'shop/shop_product_type_meta_model' );

		foreach ( $this->data['product_types'] AS $type ) :

			$this->data['product_types_meta'][$type->id] = $this->shop_product_type_meta_model->get_by_product_type_id( $type->id );

		endforeach;

		// --------------------------------------------------------------------------

		//	Fetch shipping data, used in form validation
		$this->load->model( 'shop/shop_shipping_driver_model' );
		$this->data['shipping_driver']			= $this->shop_shipping_driver_model->get_enabled();
		$this->data['shipping_options_variant'] = $this->shop_shipping_driver_model->options_variant();

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			//	Form validation, this'll be fun...
			$this->load->library( 'form_validation' );

			//	Define all the rules
			$this->__inventory_create_edit_validation_rules( $this->input->post() );

			// --------------------------------------------------------------------------

			if ( $this->form_validation->run( $this ) ) :

				//	Validated! Create the product
				$_product = $this->shop_product_model->create( $this->input->post() );

				if ( $_product ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product was created successfully.' );
					redirect( 'admin/shop/inventory' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Product. ' . $this->shop_product_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load additional models
		$this->load->model( 'shop/shop_attribute_model' );
		$this->load->model( 'shop/shop_brand_model' );
		$this->load->model( 'shop/shop_category_model' );
		$this->load->model( 'shop/shop_collection_model' );
		$this->load->model( 'shop/shop_range_model' );
		$this->load->model( 'shop/shop_tag_model' );

		// --------------------------------------------------------------------------

		//	Fetch additional data
		$this->data['product_types_flat']		= $this->shop_product_type_model->get_all_flat();
		$this->data['tax_rates']				= $this->shop_tax_rate_model->get_all_flat();
		$this->data['attributes']				= $this->shop_attribute_model->get_all_flat();
		$this->data['brands']					= $this->shop_brand_model->get_all_flat();
		$this->data['categories']				= $this->shop_category_model->get_all_nested_flat();
		$this->data['collections']				= $this->shop_collection_model->get_all();
		$this->data['ranges']					= $this->shop_range_model->get_all();
		$this->data['tags']						= $this->shop_tag_model->get_all_flat();

		$this->data['tax_rates'] = array( 'No Tax' ) + $this->data['tax_rates'];

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->library( 'uploadify' );
		$this->asset->load( 'jquery-serialize-object/jquery.serialize-object.min.js',	'BOWER' );
		$this->asset->load( 'mustache.js/mustache.js',									'BOWER' );
		$this->asset->load( 'nails.admin.shop.inventory.create_edit.min.js',			TRUE );

		// --------------------------------------------------------------------------

		//	Libraries
		$this->load->library( 'mustache' );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/inventory/edit',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function __inventory_create_edit_validation_rules( $_post )
	{
		//	Product Info
		//	============
		$this->form_validation->set_rules( 'type_id',		'',	'xss_clean|required' );
		$this->form_validation->set_rules( 'label',			'',	'xss_clean|required' );
		$this->form_validation->set_rules( 'is_active',		'',	'xss_clean' );
		$this->form_validation->set_rules( 'brands',		'',	'xss_clean' );
		$this->form_validation->set_rules( 'categories',	'',	'xss_clean' );
		$this->form_validation->set_rules( 'tags',			'',	'xss_clean' );
		$this->form_validation->set_rules( 'tax_rate_id',	'',	'xss_clean|required' );

		// --------------------------------------------------------------------------

		//	External product
		if ( app_setting( 'enable_external_products', 'shop' ) ) :

			$this->form_validation->set_rules( 'is_external',			'',	'xss_clean' );

			if ( ! empty( $_post['is_external'] ) ) :

				$this->form_validation->set_rules( 'external_vendor_label',	'',	'xss_clean|required' );
				$this->form_validation->set_rules( 'external_vendor_url',	'',	'xss_clean|required' );

			else :

				$this->form_validation->set_rules( 'external_vendor_label',	'',	'xss_clean' );
				$this->form_validation->set_rules( 'external_vendor_url',	'',	'xss_clean' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Description
		//	===========
		$this->form_validation->set_rules( 'description',	'',	'required' );

		// --------------------------------------------------------------------------

		//	Variants - Loop variants
		//	========================
		if ( ! empty( $_post['variation'] ) && is_array( $_post['variation'] ) ) :

			foreach ( $_post['variation'] AS $index => $v ) :

				//	Details
				//	-------

				$this->form_validation->set_rules( 'variation[' . $index . '][label]', '', 'xss_clean|trim|required' );

				$_v_id = ! empty( $v['id'] ) ? $v['id'] : '';
				$this->form_validation->set_rules( 'variation[' . $index . '][sku]', '', 'xss_clean|trim|callback__callback_inventory_valid_sku[' . $_v_id . ']' );

				//	Stock
				//	-----

				$this->form_validation->set_rules( 'variation[' . $index . '][stock_status]',		'',	'xss_clean|callback__callback_inventory_valid_stock_status|required' );

				$_stock_status = isset( $v['stock_status'] ) ? $v['stock_status'] : '';

				switch( $_stock_status ) :

					case 'IN_STOCK' :

						$this->form_validation->set_rules( 'variation[' . $index . '][quantity_available]',	'',	'xss_clean|trim|callback__callback_inventory_valid_quantity' );
						$this->form_validation->set_rules( 'variation[' . $index . '][lead_time]',			'',	'xss_clean|trim' );

					break;

					case 'OUT_OF_STOCK' :

						$this->form_validation->set_rules( 'variation[' . $index . '][quantity_available]',	'',	'xss_clean|trim' );
						$this->form_validation->set_rules( 'variation[' . $index . '][lead_time]',			'',	'xss_clean|trim' );

					break;

				endswitch;

				//	Pricing
				//	-------
				if ( isset( $v['pricing'] ) ) :

					foreach( $v['pricing'] AS $price_index => $price ) :

						$_required = $price['currency'] == SHOP_BASE_CURRENCY_CODE ? '|required' : '';

						$this->form_validation->set_rules( 'variation[' . $index . '][pricing][' . $price_index . '][price]',		'',	'xss_clean|callback__callback_inventory_valid_price' . $_required );
						$this->form_validation->set_rules( 'variation[' . $index . '][pricing][' . $price_index . '][sale_price]',	'',	'xss_clean|callback__callback_inventory_valid_price' . $_required );

					endforeach;

				endif;

				//	Gallery Associations
				//	--------------------
				if ( isset( $v['gallery'] ) ) :

					foreach( $v['gallery'] AS $gallery_index => $image ) :

						$this->form_validation->set_rules( 'variation[' . $index . '][gallery][' . $gallery_index . ']',	'',	'xss_clean' );

					endforeach;

				endif;

				//	Shipping
				//	--------

				//	Collect only switch
				$this->form_validation->set_rules( 'variation[' . $index . '][shipping][collection_only]',	'',	'xss_clean' );

				//	Foreach of the driver's settings and apply any rules, but if collect only is on then don't bother
				$_shipping_options = $this->shop_shipping_driver_model->options_variant();
				foreach( $_shipping_options AS $option ) :

					$_rules		= array();
					$_rules[]	= 'xss_clean';

					if ( empty( $_post['variation'][$index]['shipping']['collection_only'] ) ) :

						if ( ! empty( $option['validation'] ) ) :

							$_option_validation	= explode( '|', $option['validation'] );
							$_rules				= array_merge( $_rules, $_option_validation );

						endif;

						if ( ! empty( $option['required'] ) ) :

							$_rules[] = 'required';

						endif;

					endif;

					$_rules = array_filter( $_rules );
					$_rules = array_unique( $_rules );
					$_rules = implode( '|', $_rules );

					$this->form_validation->set_rules( 'variation[' . $index . '][shipping][driver_data][' . $this->data['shipping_driver']->slug . '][' . $option['key'] . ']', $option['label'], $_rules );

				endforeach;

			endforeach;

		endif;

		// --------------------------------------------------------------------------

		//	Gallery
		$this->form_validation->set_rules( 'gallery',			'',	'xss_clean' );

		// --------------------------------------------------------------------------

		//	Attributes
		$this->form_validation->set_rules( 'attributes',		'',	'xss_clean' );

		// --------------------------------------------------------------------------

		//	Ranges & Collections
		$this->form_validation->set_rules( 'ranges',			'',	'xss_clean' );
		$this->form_validation->set_rules( 'collections',		'',	'xss_clean' );

		// --------------------------------------------------------------------------

		//	SEO
		$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
		$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
		$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

		// --------------------------------------------------------------------------

		//	Set messages
		$this->form_validation->set_message( 'required',			lang( 'fv_required' ) );
		$this->form_validation->set_message( 'numeric',				lang( 'fv_numeric' ) );
		$this->form_validation->set_message( 'is_natural',			lang( 'fv_is_natural' ) );
		$this->form_validation->set_message( 'max_length',			lang( 'fv_max_length' ) );
	}


	// --------------------------------------------------------------------------


	protected function _inventory_import()
	{
		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_inventory_import_' . $_method ) ) :

			$this->{'_inventory_import_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _inventory_import_index()
	{
		$this->data['page']->title = 'Import Inventory Items';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/inventory/import',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _inventory_import_download()
	{
		echo 'TODO: Generate the spreadsheet.';
	}


	// --------------------------------------------------------------------------


	protected function _inventory_edit()
	{
		//	Fetch item
		$this->data['item'] = $this->shop_product_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $this->data['item'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I could not find a product by that ID.' );
			redirect( 'admin/shop/inventory' );

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Edit Inventory Item "' . $this->data['item']->label . '"';

		// --------------------------------------------------------------------------

		//	Fetch data, this data is used in both the view and the form submission
		$this->data['product_types'] = $this->shop_product_type_model->get_all();

		if ( ! $this->data['product_types'] ) :

			//	No Product types, some need added, yo!
			$this->session->set_flashdata( 'message', '<strong>Hey!</strong> No product types have been defined. You must set some before you can add inventory items.' );
			redirect( 'admin/shop/manage/product_type/create' );

		endif;

		$this->data['currencies'] = $this->shop_currency_model->get_all_supported();

		//	Fetch product type meta fields
		$this->data['product_types_meta'] = array();
		$this->load->model( 'shop/shop_product_type_meta_model' );

		foreach ( $this->data['product_types'] AS $type ) :

			$this->data['product_types_meta'][$type->id] = $this->shop_product_type_meta_model->get_by_product_type_id( $type->id );

		endforeach;

		// --------------------------------------------------------------------------

		//	Fetch shipping data, used in form validation
		$this->load->model( 'shop/shop_shipping_driver_model' );
		$this->data['shipping_driver']			= $this->shop_shipping_driver_model->get_enabled();
		$this->data['shipping_options_variant'] = $this->shop_shipping_driver_model->options_variant();

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			//	Form validation, this'll be fun...
			$this->load->library( 'form_validation' );

			//	Define all the rules
			$this->__inventory_create_edit_validation_rules( $this->input->post() );

			// --------------------------------------------------------------------------

			if ( $this->form_validation->run( $this ) ) :

				//	Validated! Create the product
				$_product = $this->shop_product_model->update( $this->data['item']->id, $this->input->post() );

				if ( $_product ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product was updated successfully.' );
					redirect( 'admin/shop/inventory' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem updating the Product. ' . $this->shop_product_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load additional models
		$this->load->model( 'shop/shop_attribute_model' );
		$this->load->model( 'shop/shop_brand_model' );
		$this->load->model( 'shop/shop_category_model' );
		$this->load->model( 'shop/shop_collection_model' );
		$this->load->model( 'shop/shop_range_model' );
		$this->load->model( 'shop/shop_tag_model' );

		// --------------------------------------------------------------------------

		//	Fetch additional data
		$this->data['product_types_flat']	= $this->shop_product_type_model->get_all_flat();
		$this->data['tax_rates']			= $this->shop_tax_rate_model->get_all_flat();
		$this->data['attributes']			= $this->shop_attribute_model->get_all_flat();
		$this->data['brands']				= $this->shop_brand_model->get_all_flat();
		$this->data['categories']			= $this->shop_category_model->get_all_nested_flat();
		$this->data['collections']			= $this->shop_collection_model->get_all();
		$this->data['ranges']				= $this->shop_range_model->get_all();
		$this->data['tags']					= $this->shop_tag_model->get_all_flat();

		$this->data['tax_rates'] = array( 'No Tax' ) + $this->data['tax_rates'];

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->library( 'uploadify' );
		$this->asset->load( 'jquery-serialize-object/jquery.serialize-object.min.js',	'BOWER' );
		$this->asset->load( 'mustache.js/mustache.js',										'BOWER' );
		$this->asset->load( 'nails.admin.shop.inventory.create_edit.min.js',			TRUE );

		// --------------------------------------------------------------------------

		//	Libraries
		$this->load->library( 'mustache' );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/inventory/edit',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _inventory_delete()
	{
		$_product = $this->shop_product_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $_product ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> a product with that ID could not be found.' );
			redirect( 'admin/shop/inventory/index' );

		endif;

		// --------------------------------------------------------------------------

		if ( $this->shop_product_model->delete( $_product->id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product successfully deleted! You can restore this product by ' . anchor( '/admin/shop/inventory/restore/' . $_product->id, 'clicking here' ) . '.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> that product could not be deleted. ' . $this->shop_product_model->last_error() );

		endif;

		// --------------------------------------------------------------------------

		redirect( 'admin/shop/inventory/index' );
	}


	// --------------------------------------------------------------------------


	protected function _inventory_restore()
	{
		if ( $this->shop_product_model->restore( $this->uri->segment( 5 ) ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product successfully restored.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> that product could not be restored.' );

		endif;

		// --------------------------------------------------------------------------

		redirect( 'admin/shop/inventory/index' );
	}


	// --------------------------------------------------------------------------


	public function orders()
	{
		if ( ! user_has_permission( 'admin.shop:0.orders_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_orders_' . $_method ) ) :

			$this->{'_orders_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Manage orders
	 *
	 * @access protected
	 * @param none
	 * @return void
	 **/
	protected function _orders_index()
	{
		//	Set method info
		$this->data['page']->title = 'Manage Orders';

		// --------------------------------------------------------------------------

		//	Searching, sorting, ordering and paginating.
		$_hash = 'search_' . md5( uri_string() ) . '_';

		if ( $this->input->get( 'reset' ) ) :

			$this->session->unset_userdata( $_hash . 'per_page' );
			$this->session->unset_userdata( $_hash . 'sort' );
			$this->session->unset_userdata( $_hash . 'order' );

		endif;

		$_default_per_page	= $this->session->userdata( $_hash . 'per_page' ) ? $this->session->userdata( $_hash . 'per_page' ) : 50;
		$_default_sort		= $this->session->userdata( $_hash . 'sort' ) ? 	$this->session->userdata( $_hash . 'sort' ) : 'o.id';
		$_default_order		= $this->session->userdata( $_hash . 'order' ) ? 	$this->session->userdata( $_hash . 'order' ) : 'desc';

		//	Define vars
		$_search = array( 'keywords' => $this->input->get( 'search' ), 'columns' => array() );

		foreach ( $this->shop_orders_sortfields AS $field ) :

			$_search['columns'][strtolower( $field['label'] )] = $field['col'];

		endforeach;

		$_limit		= array(
						$this->input->get( 'per_page' ) ? $this->input->get( 'per_page' ) : $_default_per_page,
						$this->input->get( 'offset' ) ? $this->input->get( 'offset' ) : 0
					);
		$_order		= array(
						$this->input->get( 'sort' ) ? $this->input->get( 'sort' ) : $_default_sort,
						$this->input->get( 'order' ) ? $this->input->get( 'order' ) : $_default_order
					);

		//	Set sorting and ordering info in session data so it's remembered for when user returns
		$this->session->set_userdata( $_hash . 'per_page', $_limit[0] );
		$this->session->set_userdata( $_hash . 'sort', $_order[0] );
		$this->session->set_userdata( $_hash . 'order', $_order[1] );

		//	Set values for the page
		$this->data['search']				= new stdClass();
		$this->data['search']->per_page		= $_limit[0];
		$this->data['search']->sort			= $_order[0];
		$this->data['search']->order		= $_order[1];
		$this->data['search']->show			= $this->input->get( 'show' );
		$this->data['search']->fulfilled	= $this->input->get( 'fulfilled' );

		// --------------------------------------------------------------------------

		//	Prepare the where
		if ( $this->data['search']->show || $this->data['search']->fulfilled ) :

			$_where = '( ';

			if ( $this->data['search']->show ) :

				$_where .= '`o`.`status` IN (';

					$_statuses = array_keys( $this->data['search']->show );
					foreach ( $_statuses AS &$stat ) :

						$stat = strtoupper( $stat );

					endforeach;
					$_where .= "'" . implode( "','", $_statuses ) . "'";

				$_where .= ')';

			endif;

			// --------------------------------------------------------------------------

			if ( $this->data['search']->show && $this->data['search']->fulfilled ) :

				$_where .= ' AND ';

			endif;

			// --------------------------------------------------------------------------

			if ( $this->data['search']->fulfilled ) :

				$_where .= '`o`.`fulfilment_status` IN (';

					$_statuses = array_keys( $this->data['search']->fulfilled );
					foreach ( $_statuses AS &$stat ) :

						$stat = strtoupper( $stat );

					endforeach;
					$_where .= "'" . implode( "','", $_statuses ) . "'";

				$_where .= ')';

			endif;

			$_where .= ')';

		else :

			$_where = NULL;

		endif;

		// --------------------------------------------------------------------------

		//	Pass any extra data to the view
		$this->data['actions']		= $this->shop_orders_actions;
		$this->data['sortfields']	= $this->shop_orders_sortfields;

		// --------------------------------------------------------------------------

		//	Fetch orders
		$this->load->model( 'shop/shop_order_model' );

		$this->data['orders']		= new stdClass();
		$this->data['orders']->data = $this->shop_order_model->get_all( $_order, $_limit, $_where, $_search );

		//	Work out pagination
		$this->data['orders']->pagination					= new stdClass();
		$this->data['orders']->pagination->total_results	= $this->shop_order_model->count_orders( $_where, $_search );

		// --------------------------------------------------------------------------

		$this->asset->load( 'nails.admin.shop.order.browse.min.js', TRUE );
		$this->asset->inline( 'var _SHOP_ORDER_BROWSE = new NAILS_Admin_Shop_Order_Browse()', 'JS' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/orders/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * View order
	 *
	 * @access protected
	 * @param none
	 * @return void
	 **/
	protected function _orders_view()
	{
		if ( ! user_has_permission( 'admin.shop:0.orders_view' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to view order details.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	Fetch and check order
		$this->load->model( 'shop/shop_order_model' );

		$this->data['order'] = $this->shop_order_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $this->data['order'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> no order exists by that ID.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	Get associated payments
		$this->load->model( 'shop/shop_order_payment_model' );
		$this->data['payments'] = $this->shop_order_payment_model->get_for_order( $this->data['order']->id );

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = 'View Order &rsaquo; ' . $this->data['order']->ref;

		// --------------------------------------------------------------------------

		if ( $this->input->get( 'is_fancybox' ) ) :

			$this->data['header_override'] = 'structure/header/nails-admin-blank';
			$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

		endif;

		// --------------------------------------------------------------------------

		$this->asset->load( 'nails.admin.shop.order.view.min.js', TRUE );
		$this->asset->inline( 'var _SHOP_ORDER_VIEW = new NAILS_Admin_Shop_Order_View()', 'JS' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/orders/view',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}

	// --------------------------------------------------------------------------

	protected function _orders_reprocess()
	{
		if ( ! user_has_permission( 'admin.shop:0.orders_reprocess' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to reprocess orders.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	Check order exists
		$this->load->model( 'shop/shop_order_model' );
		$_order = $this->shop_order_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $_order ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I couldn\'t find an order by that ID.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	PROCESSSSSS...
		$this->shop_order_model->process( $_order );

		// --------------------------------------------------------------------------

		//	Send a receipt to the customer
		$this->shop_order_model->send_receipt( $_order );

		// --------------------------------------------------------------------------

		//	Send a notification to the store owner(s)
		$this->shop_order_model->send_order_notification( $_order );

		// --------------------------------------------------------------------------

		if ( $_order->voucher ) :

			//	Redeem the voucher, if it's there
			$this->load->model( 'shop/shop_voucher_model' );
			$this->shop_voucher_model->redeem( $_order->voucher->id, $_order );

		endif;

		// --------------------------------------------------------------------------

		$this->session->set_flashdata( 'success', '<strong>Success!</strong> Order was processed succesfully. The user has been sent a receipt.' );
		redirect( 'admin/shop/orders' );
	}


	// --------------------------------------------------------------------------


	protected function _orders_process()
	{
		if ( ! user_has_permission( 'admin.shop:0.orders_process' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to process order items.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		$_order_id		= $this->uri->segment( 5 );
		$_product_id	= $this->uri->segment( 6 );
		$_is_fancybox	= $this->input->get( 'is_fancybox' ) ? '?is_fancybox=true' : '';

		// --------------------------------------------------------------------------

		//	Update item
		if ( $this->uri->segment( 7 ) == 'processed' ) :

			$this->db->set( 'processed', TRUE );

		else :

			$this->db->set( 'processed', FALSE );

		endif;

		$this->db->where( 'order_id',	$_order_id );
		$this->db->where( 'id',			$_product_id );

		$this->db->update( NAILS_DB_PREFIX . 'shop_order_product' );

		if ( $this->db->affected_rows() ) :

			//	Product updated, check if order has been fulfilled
			$this->db->where( 'order_id', $_order_id );
			$this->db->where( 'processed', FALSE );

			if ( ! $this->db->count_all_results( NAILS_DB_PREFIX . 'shop_order_product' ) ) :

				//	No unprocessed items, consider order FULFILLED
				$this->load->model( 'shop/shop_order_model' );
				$this->shop_order_model->fulfil( $_order_id );

			else :

				//	Still some unprocessed items, mark as unfulfilled (in case it was already fulfilled)
				$this->load->model( 'shop/shop_order_model' );
				$this->shop_order_model->unfulfil( $_order_id );

			endif;

			// --------------------------------------------------------------------------

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product\'s status was updated successfully.' );
			redirect( 'admin/shop/orders/view/' . $_order_id . $_is_fancybox );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I was not able to update the status of that product.' );
			redirect( 'admin/shop/orders/view/' . $_order_id . $_is_fancybox );

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _orders_download_invoice()
	{
		if ( ! user_has_permission( 'admin.shop:0.orders_download' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to download orders.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	Fetch and check order
		$this->load->model( 'shop/shop_order_model' );

		$this->data['order'] = $this->shop_order_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $this->data['order'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> no order exists by that ID.' );
			redirect( 'admin/shop/orders' );

		endif;

		// --------------------------------------------------------------------------

		//	Load up the shop's skin
		$_skin = app_setting( 'skin_checkout', 'shop' ) ? app_setting( 'skin_checkout', 'shop' ) : 'shop-skin-checkout-classic';

		$this->load->model( 'shop/shop_skin_checkout_model' );
		$_skin = $this->shop_skin_checkout_model->get( $_skin );

		if ( ! $_skin ) :

			show_fatal_error( 'Failed to load shop skin "' . $_skin . '"', 'Shop skin "' . $_skin . '" failed to load at ' . APP_NAME . ', the following reason was given: ' . $this->shop_skin_checkout_model->last_error() );

		endif;

		// --------------------------------------------------------------------------

		//	Views
		$this->load->library( 'pdf/pdf' );
		$this->pdf->set_paper_size( 'A4', 'landscape' );
		$this->pdf->load_view( $_skin->path . 'views/order/invoice', $this->data );
		$this->pdf->download( 'INVOICE-' . $this->data['order']->ref . '.pdf' );
	}


	// --------------------------------------------------------------------------


	/**
	 * Manage vouchers
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function vouchers()
	{
		if ( ! user_has_permission( 'admin.shop:0.vouchers_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load voucher model
		$this->load->model( 'shop/shop_voucher_model' );

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_vouchers_' . $_method ) ) :

			$this->{'_vouchers_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _vouchers_index()
	{
		//	Set method info
		$this->data['page']->title = 'Manage Vouchers';

		// --------------------------------------------------------------------------

		//	Searching, sorting, ordering and paginating.
		$_hash = 'search_' . md5( uri_string() ) . '_';

		if ( $this->input->get( 'reset' ) ) :

			$this->session->unset_userdata( $_hash . 'per_page' );
			$this->session->unset_userdata( $_hash . 'sort' );
			$this->session->unset_userdata( $_hash . 'order' );

		endif;

		$_default_per_page	= $this->session->userdata( $_hash . 'per_page' ) ? $this->session->userdata( $_hash . 'per_page' ) : 50;
		$_default_sort		= $this->session->userdata( $_hash . 'sort' ) ? 	$this->session->userdata( $_hash . 'sort' ) : 'v.id';
		$_default_order		= $this->session->userdata( $_hash . 'order' ) ? 	$this->session->userdata( $_hash . 'order' ) : 'desc';

		//	Define vars
		$_search = array( 'keywords' => $this->input->get( 'search' ), 'columns' => array() );

		foreach ( $this->shop_vouchers_sortfields AS $field ) :

			$_search['columns'][strtolower( $field['label'] )] = $field['col'];

		endforeach;

		$_limit		= array(
						$this->input->get( 'per_page' ) ? $this->input->get( 'per_page' ) : $_default_per_page,
						$this->input->get( 'offset' ) ? $this->input->get( 'offset' ) : 0
					);
		$_order		= array(
						$this->input->get( 'sort' ) ? $this->input->get( 'sort' ) : $_default_sort,
						$this->input->get( 'order' ) ? $this->input->get( 'order' ) : $_default_order
					);

		//	Set sorting and ordering info in session data so it's remembered for when user returns
		$this->session->set_userdata( $_hash . 'per_page', $_limit[0] );
		$this->session->set_userdata( $_hash . 'sort', $_order[0] );
		$this->session->set_userdata( $_hash . 'order', $_order[1] );

		//	Set values for the page
		$this->data['search']				= new stdClass();
		$this->data['search']->per_page		= $_limit[0];
		$this->data['search']->sort			= $_order[0];
		$this->data['search']->order		= $_order[1];
		$this->data['search']->show			= $this->input->get( 'show' );

		// --------------------------------------------------------------------------

		//	Prepare the where
		if ( $this->data['search']->show ) :

			$_where = '( ';

			if ( $this->data['search']->show ) :

				$_where .= '`v`.`type` IN (';

					$_statuses = array_keys( $this->data['search']->show );
					foreach ( $_statuses AS &$stat ) :

						$stat = strtoupper( $stat );

					endforeach;
					$_where .= "'" . implode( "','", $_statuses ) . "'";

				$_where .= ')';

			endif;

			$_where .= ')';

		else :

			$_where = NULL;

		endif;

		// --------------------------------------------------------------------------

		//	Pass any extra data to the view
		$this->data['actions']		= $this->shop_vouchers_actions;
		$this->data['sortfields']	= $this->shop_vouchers_sortfields;

		// --------------------------------------------------------------------------

		//	Fetch vouchers
		$this->data['vouchers']		= new stdClass();
		$this->data['vouchers']->data = $this->shop_voucher_model->get_all( FALSE, $_order, $_limit, $_where, $_search );

		//	Work out pagination
		$this->data['vouchers']->pagination					= new stdClass();
		$this->data['vouchers']->pagination->total_results	= $this->shop_voucher_model->count_vouchers( FALSE, $_where, $_search );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/vouchers/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _vouchers_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.vouchers_create' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to create vouchers.' );
			redirect( 'admin/shop/vouchers' );

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			//	Common
			$this->form_validation->set_rules( 'type',					'', 'required|callback__callback_voucher_valid_type' );
			$this->form_validation->set_rules( 'code',					'', 'required|is_unique[' . NAILS_DB_PREFIX . 'shop_voucher.code]|callback__callback_voucher_valid_code' );
			$this->form_validation->set_rules( 'label',					'', 'required' );
			$this->form_validation->set_rules( 'valid_from',			'', 'required|callback__callback_voucher_valid_from' );
			$this->form_validation->set_rules( 'valid_to',				'', 'callback__callback_voucher_valid_to' );

			//	Voucher Type specific rules
			switch ( $this->input->post( 'type' ) ) :

				case 'LIMITED_USE' :

					$this->form_validation->set_rules( 'limited_use_limit',	'', 'required|is_natural_no_zero' );

					$this->form_validation->set_message( 'is_natural_no_zero',	'Only positive integers are valid.' );

					$this->form_validation->set_rules( 'discount_type',			'', 'required|callback__callback_voucher_valid_discount_type' );
					$this->form_validation->set_rules( 'discount_application',	'', 'required|callback__callback_voucher_valid_discount_application' );

				break;

				case 'NORMAL' :
				default :

					$this->form_validation->set_rules( 'discount_type',			'', 'required|callback__callback_voucher_valid_discount_type' );
					$this->form_validation->set_rules( 'discount_application',	'', 'required|callback__callback_voucher_valid_discount_application' );

				break;

				case 'GIFT_CARD' :

					//	Quick hack
					$_POST['discount_type']			= 'AMOUNT';
					$_POST['discount_application']	= 'ALL';

				break;

			endswitch;

			//	Discount Type specific rules
			switch ( $this->input->post( 'discount_type' ) ) :

				case 'PERCENTAGE' :

					$this->form_validation->set_rules( 'discount_value',	'', 'required|is_natural_no_zero|greater_than[0]|less_than[101]' );

					$this->form_validation->set_message( 'is_natural_no_zero',	'Only positive integers are valid.' );
					$this->form_validation->set_message( 'greater_than',		'Must be in the range 1-100' );
					$this->form_validation->set_message( 'less_than',			'Must be in the range 1-100' );

				break;

				case 'AMOUNT' :

					$this->form_validation->set_rules( 'discount_value',	'', 'required|numeric|greater_than[0]' );

					$this->form_validation->set_message( 'greater_than',		'Must be greater than 0' );

				break;

				default:

					//	No specific rules

				break;

			endswitch;

			//	Discount application specific rules
			switch ( $this->input->post( 'discount_application' ) ) :

				case 'PRODUCT_TYPES' :

					$this->form_validation->set_rules( 'product_type_id',	'', 'required|callback__callback_voucher_valid_product_type' );

					$this->form_validation->set_message( 'greater_than',		'Must be greater than 0' );

				break;


				case 'PRODUCTS' :
				case 'SHIPPING' :
				case 'ALL' :
				default :

					//	No specific rules

				break;

			endswitch;

			$this->form_validation->set_message( 'required',			lang( 'fv_required' ) );
			$this->form_validation->set_message( 'is_unique',			'Code already in use.' );


			if ( $this->form_validation->run( $this ) ) :

				//	Prepare the $_data variable
				$_data	= array();

				$_data['type']					= $this->input->post( 'type' );
				$_data['code']					= strtoupper( $this->input->post( 'code' ) );
				$_data['discount_type']			= $this->input->post( 'discount_type' );
				$_data['discount_value']		= $this->input->post( 'discount_value' );
				$_data['discount_application']	= $this->input->post( 'discount_application' );
				$_data['label']					= $this->input->post( 'label' );
				$_data['valid_from']			= $this->input->post( 'valid_from' );
				$_data['is_active']				= TRUE;

				if ( $this->input->post( 'valid_to' ) ) :

					$_data['valid_to']			= $this->input->post( 'valid_to' );

				endif;

				//	Define specifics
				if ( $this->input->post( 'type' ) == 'GIFT_CARD' ) :

					$_data['gift_card_balance']		= $this->input->post( 'discount_value' );
					$_data['discount_type']			= 'AMOUNT';
					$_data['discount_application']	= 'ALL';

				endif;

				if ( $this->input->post( 'type' ) == 'LIMITED_USE' ) :

					$_data['limited_use_limit']	= $this->input->post( 'limited_use_limit' );

				endif;

				if ( $this->input->post( 'discount_application' ) == 'PRODUCT_TYPES' ) :

					$_data['product_type_id']	= $this->input->post( 'product_type_id' );

				endif;

				// --------------------------------------------------------------------------

				//	Attempt to create
				if ( $this->shop_voucher_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Voucher "' . $_data['code'] . '" was created successfully.' );
					redirect( 'admin/shop/vouchers' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the voucher. '  . $this->shop_voucher_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		$this->data['page']->title = 'Create Voucher';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['product_types'] = $this->shop_product_type_model->get_all_flat();

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->load( 'nails.admin.shop.vouchers.min.js', TRUE );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/vouchers/create',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	public function _callback_voucher_valid_code( &$str )
	{
		$str = strtoupper( $str );

		if  ( preg_match( '/[^a-zA-Z0-9]/', $str ) ) :

			$this->form_validation->set_message( '_callback_voucher_valid_code', 'Invalid characters.' );
			return FALSE;

		else :

			return TRUE;

		endif;

	}

	public function _callback_voucher_valid_type( $str )
	{
		$_valid_types = array('NORMAL','LIMITED_USE','GIFT_CARD');
		$this->form_validation->set_message( '_callback_voucher_valid_type', 'Invalid voucher type.' );
		return array_search( $str, $_valid_types ) !== FALSE;
	}

	public function _callback_voucher_valid_discount_type( $str )
	{
		$_valid_types = array('PERCENTAGE','AMOUNT');
		$this->form_validation->set_message( '_callback_voucher_valid_discount_type', 'Invalid discount type.' );
		return array_search( $str, $_valid_types ) !== FALSE;
	}

	public function _callback_voucher_valid_product_type( $str )
	{
		$this->form_validation->set_message( '_callback_voucher_valid_product_type', 'Invalid product type.' );
		return (bool) $this->shop_product_type_model->get_by_id( $str );
	}

	public function _callback_voucher_valid_from( &$str )
	{
		//	Check $str is a valid date
		$_date = date( 'Y-m-d H:i:s', strtotime( $str ) );

		//	Check format of str
		if ( preg_match( '/^\d\d\d\d\-\d\d-\d\d$/', trim( $str ) ) ) :

			//in YYYY-MM-DD format, add the time
			$str = trim( $str ) . ' 00:00:00';

		endif;

		if ( $_date != $str ) :

			$this->form_validation->set_message( '_callback_voucher_valid_from', 'Invalid date.' );
			return FALSE;

		endif;

		//	If valid_to is defined make sure valid_from isn't before it
		if ( $this->input->post( 'valid_to' ) ) :

			$_date = strtotime( $this->input->post( 'valid_to' ) );

			if ( strtotime( $str ) >= $_date ) :

				$this->form_validation->set_message( '_callback_voucher_valid_from', 'Valid From date cannot be after Valid To date.' );
				return FALSE;

			endif;

		endif;

		return TRUE;
	}

	public function _callback_voucher_valid_to( &$str )
	{
		//	If empty ignore
		if ( ! $str )
			return TRUE;

		// --------------------------------------------------------------------------

		//	Check $str is a valid date
		$_date = date( 'Y-m-d H:i:s', strtotime( $str ) );

		//	Check format of str
		if ( preg_match( '/^\d\d\d\d\-\d\d\-\d\d$/', trim( $str ) ) ) :

			//in YYYY-MM-DD format, add the time
			$str = trim( $str ) . ' 00:00:00';

		endif;

		if ( $_date != $str ) :

			$this->form_validation->set_message( '_callback_voucher_valid_to', 'Invalid date.' );
			return FALSE;

		endif;

		//	Make sure valid_from isn't before it
		$_date = strtotime( $this->input->post( 'valid_from' ) );

		if ( strtotime( $str ) <= $_date ) :

			$this->form_validation->set_message( '_callback_voucher_valid_to', 'Valid To date cannot be before Valid To date.' );
			return FALSE;

		endif;

		return TRUE;
	}


	// --------------------------------------------------------------------------


	protected function _vouchers_activate()
	{
		if ( ! user_has_permission( 'admin.shop:0.vouchers_activate' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to activate vouchers.' );
			redirect( 'admin/shop/vouchers' );

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 5 );

		if ( $this->shop_voucher_model->update( $_id, array( 'is_active' => TRUE ) ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Voucher was activated successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> There was a problem activating the voucher. ' . $this->shop_voucher_model->last_error() );

		endif;

		redirect( 'admin/shop/vouchers' );
	}


	// --------------------------------------------------------------------------


	protected function _vouchers_deactivate()
	{
		if ( ! user_has_permission( 'admin.shop:0.vouchers_deactivate' ) ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you do not have permission to suspend vouchers.' );
			redirect( 'admin/shop/vouchers' );

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 5 );

		if ( $this->shop_voucher_model->update( $_id, array( 'is_active' => FALSE ) ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Voucher was suspended successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> There was a problem suspending the voucher. ' . $this->shop_voucher_model->last_error() );

		endif;

		redirect( 'admin/shop/vouchers' );
	}


	// --------------------------------------------------------------------------


	/**
	 * Other managers
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function sales()
	{
		if ( ! user_has_permission( 'admin.shop:0.sale_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_sales_' . $_method ) ) :

			$this->{'_sales_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _sales_index()
	{
		$this->data['page']->title = 'Manage Sales';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/sales/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _sales_create()
	{
		$this->data['page']->title = 'Create Sale';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/shop/sales/edit',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _sales_edit()
	{
		$this->data['page']->title = 'Edit Sale "xxx"';

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/shop/sales/edit',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _sales_delete()
	{
		$this->session->set_flashdata( 'message', '<strong>TODO:</strong> Delete a sale.' );
	}


	// --------------------------------------------------------------------------


	/**
	 * Other managers
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function manage()
	{
		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_manage_' . $_method ) ) :

			//	Is fancybox?
			$this->data['is_fancybox']	= $this->input->get( 'is_fancybox' ) ? '?is_fancybox=1' : '';

			//	Override the header and footer
			if ( $this->data['is_fancybox'] ) :

				$this->data['header_override'] = 'structure/header/nails-admin-blank';
				$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

			endif;

			//	Start the page title
			$this->data['page']->title = 'Manage &rsaquo; ';

			//	Call method
			$this->{'_manage_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_index()
	{
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/manage/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_attribute()
	{
		if ( ! user_has_permission( 'admin.shop:0.attribute_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_attribute_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_attribute_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Attributes ';

			$this->{'_manage_attribute_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_attribute_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['attributes'] = $this->shop_attribute_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/attribute/index',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_attribute_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.attribute_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',			'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',	'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data				= new stdClass();
				$_data->label		= $this->input->post( 'label' );
				$_data->description	= $this->input->post( 'description' );

				if ( $this->shop_attribute_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Attribute created successfully.' );
					redirect( 'admin/shop/manage/attribute' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Attribute. ' . $this->shop_category_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['attributes'] = $this->shop_attribute_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/attribute/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_attribute_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.attribute_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['attribute'] = $this->shop_attribute_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['attribute'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',			'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',	'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data				= new stdClass();
				$_data->label		= $this->input->post( 'label' );
				$_data->description	= $this->input->post( 'description' );

				if ( $this->shop_attribute_model->update( $this->data['attribute']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Attribute saved successfully.' );
					redirect( 'admin/shop/manage/attribute' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Attribute. ' . $this->shop_attribute_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['attribute']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['attributes'] = $this->shop_attribute_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/attribute/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_attribute_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.attribute_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_attribute_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Attribute was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Attribute. ' . $this->shop_attribute_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/attribute' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_brand()
	{
		if ( ! user_has_permission( 'admin.shop:0.brand_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_brand_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_brand_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Brands ';

			$this->{'_manage_brand_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_brand_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE, 'only_active' => FALSE );
		$this->data['brands'] = $this->shop_brand_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/brand/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_brand_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.brand_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'logo_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->logo_id			= (int) $this->input->post( 'logo_id' ) ? (int) $this->input->post( 'logo_id' ) : NULL;
				$_data->cover_id		= (int) $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_brand_model->create( $_data ) ) :

					//	Redirect to clear form
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Brand created successfully.' );
					redirect( 'admin/shop/manage/brand' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Brand. ' . $this->shop_brand_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['brands'] = $this->shop_brand_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/brand/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_brand_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.brand_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['brand'] = $this->shop_brand_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['brand'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'logo_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->logo_id			= (int) $this->input->post( 'logo_id' ) ? (int) $this->input->post( 'logo_id' ) : NULL;
				$_data->cover_id		= (int) $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_brand_model->update( $this->data['brand']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Brand saved successfully.' );
					redirect( 'admin/shop/manage/brand' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Brand. ' . $this->shop_brand_model->last_error();

				endif;

			else :

				$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Brand.';

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['brand']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['brands'] = $this->shop_brand_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/brand/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_brand_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.brand_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_brand_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Brand was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Brand. ' . $this->shop_brand_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/brand' . $this->data['is_fancybox'] );
	}



	// --------------------------------------------------------------------------


	protected function _manage_category()
	{
		if ( ! user_has_permission( 'admin.shop:0.category_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_category_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_category_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Categories ';

			$this->{'_manage_category_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['categories'] = $this->shop_category_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/category/index',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.category_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'parent_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->parent_id		= $this->input->post( 'parent_id' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_category_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category created successfully.' );
					redirect( 'admin/shop/manage/category' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Category. ' . $this->shop_category_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['categories'] = $this->shop_category_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/category/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.category_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['category'] = $this->shop_category_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['category'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'parent_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->parent_id		= $this->input->post( 'parent_id' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_category_model->update( $this->data['category']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category saved successfully.' );
					redirect( 'admin/shop/manage/category' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Category. ' . $this->shop_category_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title = 'Edit &rsaquo; ' . $this->data['category']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['categories'] = $this->shop_category_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/category/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.category_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_category_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Category. ' . $this->shop_category_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/category' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_collection()
	{
		if ( ! user_has_permission( 'admin.shop:0.collection_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_collection_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_collection_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Collections ';

			$this->{'_manage_collection_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_collection_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE, 'only_active' => FALSE );
		$this->data['collections'] = $this->shop_collection_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',						$this->data );
		$this->load->view( 'admin/shop/manage/collection/index',	$this->data );
		$this->load->view( 'structure/footer',						$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_collection_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.collection_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );

				if ( $this->shop_collection_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Collection created successfully.' );
					redirect( 'admin/shop/manage/collection' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Collection. ' . $this->shop_collection_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['collections'] = $this->shop_collection_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/collection/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_collection_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.collection_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['collection'] = $this->shop_collection_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['collection'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );

				if ( $this->shop_collection_model->update( $this->data['collection']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Collection saved successfully.' );
					redirect( 'admin/shop/manage/collection' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Collection. ' . $this->shop_collection_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['collection']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['collections'] = $this->shop_collection_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/collection/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_collection_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.collection_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_collection_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Collection was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Collection. ' . $this->shop_collection_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/collection' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_range()
	{
		if ( ! user_has_permission( 'admin.shop:0.range_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_range_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_range_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Ranges ';

			$this->{'_manage_range_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_range_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['ranges'] = $this->shop_range_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/range/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_range_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.range_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );

				if ( $this->shop_range_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Range created successfully.' );
					redirect( 'admin/shop/manage/range' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Range. ' . $this->shop_range_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['ranges'] = $this->shop_range_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/range/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_range_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.range_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['range'] = $this->shop_range_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['range'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_active',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );
				$_data->is_active		= (bool) $this->input->post( 'is_active' );

				if ( $this->shop_range_model->update( $this->data['range']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Range saved successfully.' );
					redirect( 'admin/shop/manage/range' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Range. ' . $this->shop_range_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['range']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['ranges'] = $this->shop_range_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/range/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_range_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.range_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_range_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Range was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Range. ' . $this->shop_range_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/range' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag()
	{
		if ( ! user_has_permission( 'admin.shop:0.tag_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_tag_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_tag_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Tags ';

			$this->{'_manage_tag_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['tags'] = $this->shop_tag_model->get_all( NULL,NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/tag/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.tag_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_tag_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag created successfully.' );
					redirect( 'admin/shop/manage/tag' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Tag. ' . $this->shop_tag_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['tags'] = $this->shop_tag_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/tag/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.tag_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['tag'] = $this->shop_tag_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['tag'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'cover_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->cover_id		= $this->input->post( 'cover_id' ) ? (int) $this->input->post( 'cover_id' ) : NULL;
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->shop_tag_model->update( $this->data['tag']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag saved successfully.' );
					redirect( 'admin/shop/manage/tag' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Tag. ' . $this->shop_tag_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['tag']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['tags'] = $this->shop_tag_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/shop/manage/tag/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.tag_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_tag_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Tag. ' . $this->shop_tag_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/tag' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tax_rate()
	{
		if ( ! user_has_permission( 'admin.shop:0.tax_rate_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_tax_rate_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_tax_rate_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Tax Rates ';

			$this->{'_manage_tax_rate_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_tax_rate_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['tax_rates'] = $this->shop_tax_rate_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/tax_rate/index',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tax_rate_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.tax_rate_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',	'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'rate',	'',	'xss_clean|required|in_range[0-1]' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );
			$this->form_validation->set_message( 'in_range', lang( 'fv_in_range' ) );

			if ( $this->form_validation->run() ) :

				$_data			= new stdClass();
				$_data->label	= $this->input->post( 'label' );
				$_data->rate	= $this->input->post( 'rate' );

				if ( $this->shop_tax_rate_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tax Rate created successfully.' );
					redirect( 'admin/shop/manage/tax_rate' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Tax Rate. ' . $this->shop_tax_rate_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['tax_rates'] = $this->shop_tax_rate_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/tax_rate/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tax_rate_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.tax_rate_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['tax_rate'] = $this->shop_tax_rate_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['tax_rate'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',	'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'rate',	'',	'xss_clean|required|in_range[0-1]' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );
			$this->form_validation->set_message( 'in_range', lang( 'fv_in_range' ) );

			if ( $this->form_validation->run() ) :

				$_data			= new stdClass();
				$_data->label	= $this->input->post( 'label' );
				$_data->rate	= (float) $this->input->post( 'rate' );

				if ( $this->shop_tax_rate_model->update( $this->data['tax_rate']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tax Rate saved successfully.' );
					redirect( 'admin/shop/manage/tax_rate' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Tax Rate. ' . $this->shop_tax_rate_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['tax_rate']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['tax_rates'] = $this->shop_tax_rate_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/shop/manage/tax_rate/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tax_rate_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.tax_rate_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_tax_rate_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tax Rate was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Tax Rate. ' . $this->shop_tax_rate_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/tax_rate' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_product_type_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_product_type_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Product Types ';

			$this->{'_manage_product_type_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_index()
	{
		//	Fetch data
		$_data = array( 'include_count' => TRUE );
		$this->data['product_types'] = $this->shop_product_type_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',						$this->data );
		$this->load->view( 'admin/shop/manage/product_type/index',	$this->data );
		$this->load->view( 'structure/footer',						$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required|is_unique[' . NAILS_DB_PREFIX . 'shop_product_type.label]' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_physical',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'ipn_method',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'max_per_order',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'max_variations',	'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );
			$this->form_validation->set_message( 'is_unique', lang( 'fv_is_unique' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->is_physical		= (bool) $this->input->post( 'is_physical' );
				$_data->ipn_method		= $this->input->post( 'ipn_method' );
				$_data->max_per_order	= (int) $this->input->post( 'max_per_order' );
				$_data->max_variations	= (int) $this->input->post( 'max_variations' );

				if ( $this->shop_product_type_model->create( $_data ) ) :

					//	Redirect to clear form
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Type created successfully.' );
					redirect( 'admin/shop/manage/product_type' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Product Type. ' . $this->shop_product_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['product_types'] = $this->shop_product_type_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',						$this->data );
		$this->load->view( 'admin/shop/manage/product_type/edit',	$this->data );
		$this->load->view( 'structure/footer',						$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['product_type'] = $this->shop_product_type_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['product_type'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required|unique_if_diff[' . NAILS_DB_PREFIX . 'shop_product_type.label.' . $this->input->post( 'label_old' ) . ']' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_physical',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'ipn_method',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'max_per_order',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'max_variations',	'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->is_physical		= (bool)$this->input->post( 'is_physical' );
				$_data->ipn_method		= $this->input->post( 'ipn_method' );
				$_data->max_per_order	= (int) $this->input->post( 'max_per_order' );
				$_data->max_variations	= (int) $this->input->post( 'max_variations' );

				if ( $this->shop_product_type_model->update( $this->data['product_type']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Type saved successfully.' );
					redirect( 'admin/shop/product_type' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Product Type. ' . $this->shop_product_type_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['product_type']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['product_types'] = $this->shop_product_type_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',						$this->data );
		$this->load->view( 'admin/shop/manage/product_type/edit',	$this->data );
		$this->load->view( 'structure/footer',						$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_meta()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_meta__manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load model
		$this->load->model( 'shop/shop_product_type_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_product_type_meta_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Product Type Meta ';

			$this->{'_manage_product_type_meta_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_meta_index()
	{
		//	Fetch data
		$_data = array( 'include_associated_product_types' => TRUE );
		$this->data['meta_fields'] = $this->shop_product_type_meta_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',							$this->data );
		$this->load->view( 'admin/shop/manage/product_type_meta/index',	$this->data );
		$this->load->view( 'structure/footer',							$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_meta_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_meta_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',						'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'admin_form_sub_label',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'admin_form_placeholder',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'admin_form_tip',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'associated_product_types',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'allow_multiple',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_filter',					'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data								= new stdClass();
				$_data->label						= $this->input->post( 'label' );
				$_data->admin_form_sub_label		= $this->input->post( 'admin_form_sub_label' );
				$_data->admin_form_placeholder		= $this->input->post( 'admin_form_placeholder' );
				$_data->admin_form_tip				= $this->input->post( 'admin_form_tip' );
				$_data->associated_product_types	= $this->input->post( 'associated_product_types' );
				$_data->allow_multiple				= (bool) $this->input->post( 'allow_multiple' );
				$_data->is_filter					= (bool) $this->input->post( 'is_filter' );

				if ( $this->shop_product_type_meta_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Type Meta Field created successfully.' );
					redirect( 'admin/shop/manage/product_type_meta' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Product Type Meta Field. ' . $this->shop_product_type_meta_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['product_types']	= $this->shop_product_type_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',							$this->data );
		$this->load->view( 'admin/shop/manage/product_type_meta/edit',	$this->data );
		$this->load->view( 'structure/footer',							$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_meta_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_meta_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_data = array( 'include_associated_product_types' => TRUE );
		$this->data['meta_field'] = $this->shop_product_type_meta_model->get_by_id( $this->uri->segment( 6 ), $_data );

		if ( empty( $this->data['meta_field'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',						'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'admin_form_sub_label',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'admin_form_placeholder',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'admin_form_tip',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'associated_product_types',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'allow_multiple',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'is_filter',					'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				$_data								= new stdClass();
				$_data->label						= $this->input->post( 'label' );
				$_data->admin_form_sub_label		= $this->input->post( 'admin_form_sub_label' );
				$_data->admin_form_placeholder		= $this->input->post( 'admin_form_placeholder' );
				$_data->admin_form_tip				= $this->input->post( 'admin_form_tip' );
				$_data->associated_product_types	= $this->input->post( 'associated_product_types' );
				$_data->allow_multiple				= (bool) $this->input->post( 'allow_multiple' );
				$_data->is_filter					= (bool) $this->input->post( 'is_filter' );

				if ( $this->shop_product_type_meta_model->update( $this->data['meta_field']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Type Meta Field saved successfully.' );
					redirect( 'admin/shop/manage/product_type_meta' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Product Type Meta Field. ' . $this->shop_product_type_meta_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= 'Edit &rsaquo; ' . $this->data['meta_field']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['product_types']	= $this->shop_product_type_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',							$this->data );
		$this->load->view( 'admin/shop/manage/product_type_meta/edit',	$this->data );
		$this->load->view( 'structure/footer',							$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_product_type_meta_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.product_type_meta_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->shop_product_type_meta_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Type was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Product Type. ' . $this->shop_product_type_model->last_error() );

		endif;

		redirect( 'admin/shop/manage/product_type_meta' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	/**
	 * Other managers
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function reports()
	{
		if ( ! user_has_permission( 'admin.shop:0.can_generate_reports' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Default report sources
		$this->_report_sources = array();

		if ( user_has_permission( 'admin.shop:0.inventory_manage' ) ) :

			$this->_report_sources[] = array( 'Inventory', 'Out of Stock variants', 'out_of_stock_variants' );

		endif;

		// --------------------------------------------------------------------------

		//	Default report formats
		$this->_report_formats		= array();
		$this->_report_formats[]	= array( 'CSV', 'Easily imports to many software packages, including Microsoft Excel.', 'csv' );
		$this->_report_formats[]	= array( 'HTML', 'Produces an HTML table containing the data', 'html' );
		$this->_report_formats[]	= array( 'PDF', 'Saves a PDF using the data from the HTML export option', 'pdf' );
		$this->_report_formats[]	= array( 'PHP Serialize', 'Export as an object serialized using PHP\'s serialize() function', 'serialize' );
		$this->_report_formats[]	= array( 'JSON', 'Export as a JSON array', 'json' );

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_reports_' . $_method ) ) :

			$this->{'_reports_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _reports_index()
	{
		if ( $this->input->is_cli_request() ) :

			return $this->_reports_index_cli();

		endif;

		// --------------------------------------------------------------------------

		if ( ! user_has_permission( 'admin.shop:0.can_generate_reports' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = 'Generate Reports';

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	Form validation and update
			$this->load->library( 'form_validation' );

			//	Define rules
			$this->form_validation->set_rules( 'report', '', 'xss_clean|required' );
			$this->form_validation->set_rules( 'format', '', 'xss_clean|required' );

			//	Set Messages
			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );

			//	Execute
			if ( $this->form_validation->run() && isset( $this->_report_sources[$this->input->post( 'source' )] ) && isset( $this->_report_formats[$this->input->post( 'format' )] ) ) :

				$_source = $this->_report_sources[$this->input->post( 'source' )];
				$_format = $this->_report_formats[$this->input->post( 'format' )];

				if ( ! method_exists( $this, '_report_source_' . $_source[2] ) ) :

					$this->data['error'] = '<strong>Sorry,</strong> that data source is not available.';

				elseif ( ! method_exists( $this, '_report_format_' . $_format[2] ) ) :

					$this->data['error'] = '<strong>Sorry,</strong> that format type is not available.';

				else :

					//	All seems well, generate the report!
					$_data = $this->{'_report_source_' . $_source[2]}();

					//	Anything to report?
					if ( ! empty( $_data ) ) :

						//	if $_data is an array then we need to write multiple files to a zip
						if ( is_array( $_data ) ) :

							//	Load Zip class
							$this->load->library( 'zip' );

							//	Process each file
							foreach( $_data AS $data ) :

								$_file = $this->{'_report_format_' . $_format[2]}( $data, TRUE );

								$this->zip->add_data( $_file[0], $_file[1] );

							endforeach;

							$this->zip->download( 'shop-report-' . $_source[2] . '-' . date( 'Y-m-d_H-i-s' ) );

						else :

							$this->{'_report_format_' . $_format[2]}( $_data );

						endif;

					endif;

					return;

				endif;

			elseif ( ! isset( $this->_report_sources[ $this->input->post( 'source' ) ] ) ) :

				$this->data['error'] = '<strong>Sorry,</strong> invalid data source.';

			elseif ( ! isset( $this->_report_formats[ $this->input->post( 'format' ) ] ) ) :

				$this->data['error'] = '<strong>Sorry,</strong> invalid format type.';

			else:

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		$this->data['sources'] = $this->_report_sources;
		$this->data['formats'] = $this->_report_formats;

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/shop/reports/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _reports_index_cli()
	{
		//	TODO: Complete CLI functionality for report generating
		echo 'Sorry, this functionality is not complete yet. If you are experiencing timeouts please increase the timeout limit for PHP.';
	}


	// --------------------------------------------------------------------------


	protected function _report_source_out_of_stock_variants()
	{
		if ( ! user_has_permission( 'admin.shop:0.inventory_manage' ) ) :

			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		$_out			= new stdClass();
		$_out->label	= 'Out of Stock variants';
		$_out->filename	= NAILS_DB_PREFIX . 'out_of_stock_variants';
		$_out->fields	= array();
		$_out->data		= array();

		// --------------------------------------------------------------------------

		//	Fetch all variants which are out of stock
		$this->db->select( 'p.id product_id, p.label product_label, v.id variation_id, v.label variation_label, v.sku, v.quantity_available' );
		$this->db->join( NAILS_DB_PREFIX . 'shop_product p', 'p.id = v.product_id', 'LEFT' );
		$this->db->where( 'v.stock_status', 'OUT_OF_STOCK' );
		$_out->data = $this->db->get( NAILS_DB_PREFIX . 'shop_product_variation v' )->result_array();

		if ( $_out->data ) :

			$_out->fields = array_keys( $_out->data[0] );

		endif;

		// --------------------------------------------------------------------------

		return $_out;
	}


	// --------------------------------------------------------------------------


	protected function _report_format_csv( $data, $return_data = FALSE )
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

			$this->load->view( 'admin/shop/reports/csv', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.csv';
			$_out[]	= $this->load->view( 'admin/shop/reports/csv', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _report_format_html( $data, $return_data = FALSE )
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

			$this->load->view( 'admin/shop/reports/html', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.html';
			$_out[]	= $this->load->view( 'admin/shop/reports/html', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _report_format_serialize( $data, $return_data = FALSE )
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

			$this->load->view( 'admin/shop/reports/serialize', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.txt';
			$_out[]	= $this->load->view( 'admin/shop/reports/serialize', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _report_format_json( $data, $return_data = FALSE )
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

			$this->load->view( 'admin/shop/reports/json', $this->data );

		else :

			$_out	= array();
			$_out[]	= $data->filename . '.json';
			$_out[]	= $this->load->view( 'admin/shop/reports/json', $this->data, TRUE );

			return $_out;

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _report_format_pdf( $data, $return_data = FALSE )
	{
		$_html = $this->_report_format_html( $data, TRUE );

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


/**
	 * Manage vouchers
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function product_availability_notifications()
	{
		if ( ! user_has_permission( 'admin.shop:0.notifications_manage' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		//	Load voucher model
		$this->load->model( 'shop/shop_inform_product_available_model' );

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_product_availability_notifications_' . $_method ) ) :

			$this->{'_product_availability_notifications_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _product_availability_notifications_index()
	{
		//	Set method info
		$this->data['page']->title = 'Manage Product Availability Notifications';

		// --------------------------------------------------------------------------

		$this->data['notifications'] = $this->shop_inform_product_available_model->get_all();

		// --------------------------------------------------------------------------

		$this->asset->load( 'nails.admin.shop.productavailabilitynotification.browse.min.js', TRUE );
		$this->asset->inline( 'var _SHOP_PRODUCT_AVAILABILITY_NOTIFICATION_BROWSE = new NAILS_Admin_Shop_Product_Availability_Notification_Browse()', 'JS' );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',										$this->data );
		$this->load->view( 'admin/shop/product_availability_notifications/index',	$this->data );
		$this->load->view( 'structure/footer',										$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _product_availability_notifications_create()
	{
		if ( ! user_has_permission( 'admin.shop:0.notification_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'email',	'',	'xss_clean|required|valid_email' );
			$this->form_validation->set_rules( 'item',	'',	'xss_clean|required' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'valid_email',	lang( 'fv_valid_email' ) );

			if ( $this->form_validation->run() ) :

				$_item = explode( ':', $this->input->post( 'item' ) );

				$_data					= new stdClass();
				$_data->email			= $this->input->post( 'email' );
				$_data->product_id		= isset( $_item[0] ) ? (int) $_item[0] : NULL;
				$_data->variation_id	= isset( $_item[1] ) ? (int) $_item[1] : NULL;

				if ( $this->shop_inform_product_available_model->create( $_data ) ) :

					//	Redirect to clear form
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Availability Notification created successfully.' );
					redirect( 'admin/shop/product_availability_notifications' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Product Availability Notification. ' . $this->shop_inform_product_available_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = 'Create Product Availability Notification';

		// --------------------------------------------------------------------------

		$this->data['products_variations_flat'] = $this->shop_product_model->get_all_product_variation_flat();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',										$this->data );
		$this->load->view( 'admin/shop/product_availability_notifications/edit',	$this->data );
		$this->load->view( 'structure/footer',										$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _product_availability_notifications_edit()
	{
		if ( ! user_has_permission( 'admin.shop:0.notification_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['notification'] = $this->shop_inform_product_available_model->get_by_id( $this->uri->segment( 5 ) );

		if ( ! $this->data['notification'] ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'email',	'',	'xss_clean|required|valid_email' );
			$this->form_validation->set_rules( 'item',	'',	'xss_clean|required' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'valid_email',	lang( 'fv_valid_email' ) );

			if ( $this->form_validation->run() ) :

				$_item = explode( ':', $this->input->post( 'item' ) );

				$_data					= new stdClass();
				$_data->email			= $this->input->post( 'email' );
				$_data->product_id		= isset( $_item[0] ) ? (int) $_item[0] : NULL;
				$_data->variation_id	= isset( $_item[1] ) ? (int) $_item[1] : NULL;

				if ( $this->shop_inform_product_available_model->update( $this->data['notification']->id, $_data ) ) :

					//	Redirect to clear form
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Availability Notification updated successfully.' );
					redirect( 'admin/shop/product_availability_notifications' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem updated the Product Availability Notification. ' . $this->shop_inform_product_available_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = 'Edit Product Availability Notification';

		// --------------------------------------------------------------------------

		$this->data['products_variations_flat'] = $this->shop_product_model->get_all_product_variation_flat();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',										$this->data );
		$this->load->view( 'admin/shop/product_availability_notifications/edit',	$this->data );
		$this->load->view( 'structure/footer',										$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _product_availability_notifications_delete()
	{
		if ( ! user_has_permission( 'admin.shop:0.notifications_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 5 );

		if ( $this->shop_inform_product_available_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Product Availability Notification was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Product availability Notification. ' . $this->shop_inform_product_available_model->last_error() );

		endif;

		redirect( 'admin/shop/product_availability_notifications' );
	}


	// --------------------------------------------------------------------------


	public function _callback_inventory_valid_price( $str )
	{
		$str = trim( $str );

		if ( $str && ! is_numeric( $str ) ) :

			$this->form_validation->set_message( '_callback_inventory_valid_price', 'This is not a valid price' );
			return FALSE;

		else :

			return TRUE;

		endif;
	}


	// --------------------------------------------------------------------------


	public function _callback_inventory_valid_quantity( $str )
	{
		$str = trim( $str );

		if ( $str && ! is_numeric( $str ) ) :

			$this->form_validation->set_message( '_callback_inventory_valid_quantity', 'This is not a valid quantity' );
			return FALSE;

		elseif ( $str && is_numeric( $str ) && $str < 0 ) :

			$this->form_validation->set_message( '_callback_inventory_valid_quantity', lang( 'fv_is_natural' ) );
			return FALSE;

		else :

			return TRUE;

		endif;
	}


	// --------------------------------------------------------------------------


	public function _callback_inventory_valid_sku( $str, $variation_id )
	{
		$str = trim( $str );

		if ( empty( $str ) ) :

			return TRUE;

		endif;

		if ( $variation_id ) :

			$this->db->where( 'id !=', $variation_id );

		endif;

		$this->db->where( 'is_deleted', FALSE );
		$this->db->where( 'sku', $str );
		$_result = $this->db->get( NAILS_DB_PREFIX . 'shop_product_variation' )->row();

		if ( $_result ) :

			$this->form_validation->set_message( '_callback_inventory_valid_sku', 'This SKU is already in use.' );
			return FALSE;

		else :

			return TRUE;

		endif;
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

if ( ! defined( 'NAILS_ALLOW_EXTENSION_SHOP' ) ) :

	class Shop extends NAILS_Shop
	{
	}

endif;

/* End of file shop.php */
/* Location: ./modules/admin/controllers/shop.php */