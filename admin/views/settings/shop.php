<div class="group-settings shop">
	<p>
		Configure various aspects of the shop.
	</p>

	<hr />

		<ul class="tabs">
			<?php $_active = $this->input->post( 'update' ) == 'settings' || ! $this->input->post() ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-general">General</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'browse' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-browse">Browsing</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'skin' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-skin">Skin</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'skin-configure' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-skin-config">Skin - Configure</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'payment_gateway' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-payment-gateway">Payment Gateways</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'currencies' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-currencies">Currencies</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'shipping' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-shipping">Shipping</a>
			</li>
		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'settings' || ! $this->input->post() ? 'active' : ''?>
			<div id="tab-general" class="tab page <?=$_display?> general">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'settings' )?>
				<p>
					Generic store settings. Use these to control some store behaviours.
				</p>
				<hr />
				<fieldset id="shop-settings-name">
					<legend>Name</legend>
					<p>
						Is this a shop? Or is it store? Maybe a boutique...?
					</p>
					<?php

						//	Shop Name
						$_field					= array();
						$_field['key']			= 'name';
						$_field['label']		= 'Shop Name';
						$_field['default']		= app_setting( $_field['key'], 'shop' ) ? app_setting( $_field['key'], 'shop' ) : 'Shop';
						$_field['placeholder']	= 'Customise the Shop\'s Name';

						echo form_field( $_field );

					?>
				</fieldset>

				<fieldset id="shop-settings-url">
					<legend>URL</legend>
					<p>
						Customise the shop's URL by specifying it here.
					</p>
					<?php

						//	Shop URL
						$_field					= array();
						$_field['key']			= 'url';
						$_field['label']		= 'Shop URL';
						$_field['default']		= app_setting( $_field['key'], 'shop' ) ? app_setting( $_field['key'], 'shop' ) : 'shop/';
						$_field['placeholder']	= 'Customise the Shop\'s URL (include trialing slash)';

						echo form_field( $_field );

					?>
				</fieldset>

				<fieldset id="shop-settings-tax">
					<legend>Taxes</legend>
					<p>
						Configure how the shop calculates taxes on the products you sell.
					</p>
					<?php

						$_field				= array();
						$_field['key']		= 'price_exclude_tax';
						$_field['label']	= 'Product price exclude Taxes';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['text_on']	= strtoupper( lang( 'yes' ) );
						$_field['text_off']	= strtoupper( lang( 'no' ) );

						echo form_field_boolean( $_field );

					?>
				</fieldset>

				<fieldset id="shop-settings-invoice">
					<legend>Invoicing</legend>
					<p>
						These details will be visible on invoices and email receipts.
					</p>
					<?php

						//	Company Name
						$_field					= array();
						$_field['key']			= 'invoice_company';
						$_field['label']		= 'Company Name';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['placeholder']	= 'The registered company name.';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						//	Address
						$_field					= array();
						$_field['key']			= 'invoice_address';
						$_field['label']		= 'Company Address';
						$_field['type']			= 'textarea';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['placeholder']	= 'The address to show on the invoice.';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						//	VAT Number
						$_field					= array();
						$_field['key']			= 'invoice_vat_no';
						$_field['label']		= 'VAT Number';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['placeholder']	= 'Your VAT number, if any.';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						//	Company Number
						$_field					= array();
						$_field['key']			= 'invoice_company_no';
						$_field['label']		= 'Company Number';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['placeholder']	= 'Your company number.';

						echo form_field( $_field );

					?>
				</fieldset>

				<fieldset id="shop-settings-warehouse-collection">
					<legend>Warehouse Collection</legend>
					<p>
						If you'd like customers to be able to collect items from your warehouse or store, then enable it below and provide collection details.
					</p>
					<?php

						$_field					= array();
						$_field['key']			= 'warehouse_collection_enabled';
						$_field['label']		= 'Enabled';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field );

						// --------------------------------------------------------------------------

						if ( $this->input->post( $_field['key'] ) || app_setting( $_field['key'], 'shop' ) ) :

							$_style = '';

						else :

							$_style = 'style="display:none;"';

						endif;

						echo '<div id="warehouse-collection-address" ' . $_style . '>';

							$_field					= array();
							$_field['key']			= 'warehouse_addr_addressee';
							$_field['label']		= 'Addressee';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The person or department responsible for collection items.';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_line1';
							$_field['label']		= 'Address Line 1';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The first line of the warehouse\'s address';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_line2';
							$_field['label']		= 'Address Line 2';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The second line of the warehouse\'s address';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_town';
							$_field['label']		= 'Address Town';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The town line of the warehouse\'s address';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_postcode';
							$_field['label']		= 'Address Postcode';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The postcode line of the warehouse\'s address';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_state';
							$_field['label']		= 'Address State';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The state line of the warehouse\'s address, if applicable';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_addr_country';
							$_field['label']		= 'Address Country';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['placeholder']	= 'The country line of the warehouse\'s address';

							echo form_field( $_field );

							// --------------------------------------------------------------------------

							$_field					= array();
							$_field['key']			= 'warehouse_collection_delivery_enquiry';
							$_field['label']		= 'Enable Delivery Enquiry';
							$_field['default']		= app_setting( $_field['key'], 'shop' );
							$_field['tip']			= 'For items which are &quot;collect only&quot;, enable a button which allows the user to submit a delivery enquiry.';

							echo form_field_boolean( $_field );

						echo '</div>';

					?>
				</fieldset>

				<fieldset id="shop-settings-misc">
					<legend>Miscellaneous</legend>
					<?php

						//	Brand Listing
						$_field					= array();
						$_field['key']			= 'page_brand_listing';
						$_field['label']		= 'Brand Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the brand URL is used, but no slug is specified. Renders all the populated brands and their SEO data.' );

						// --------------------------------------------------------------------------

						//	Category Listing
						$_field					= array();
						$_field['key']			= 'page_category_listing';
						$_field['label']		= 'Category Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the category URL is used, but no slug is specified. Renders all the populated categories and their SEO data.' );

						// --------------------------------------------------------------------------

						//	Collection Listing
						$_field					= array();
						$_field['key']			= 'page_collection_listing';
						$_field['label']		= 'Collection Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the collection URL is used, but no slug is specified. Renders all the active collections and their SEO data.' );

						// --------------------------------------------------------------------------

						//	Range Listing
						$_field					= array();
						$_field['key']			= 'page_range_listing';
						$_field['label']		= 'Range Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the range URL is used, but no slug is specified. Renders all the active ranges and their SEO data.' );

						// --------------------------------------------------------------------------

						//	Sale Listing
						$_field					= array();
						$_field['key']			= 'page_sale_listing';
						$_field['label']		= 'Sale Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the sale URL is used, but no slug is specified. Renders all the active sales and their SEO data.' );

						// --------------------------------------------------------------------------

						//	Tag Listing
						$_field					= array();
						$_field['key']			= 'page_tag_listing';
						$_field['label']		= 'Tag Listing Page';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						echo form_field_boolean( $_field, 'The page shown when the tag URL is used, but no slug is specified. Renders all the populated tags and their SEO data.' );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'browse' ? 'active' : ''?>
			<div id="tab-browse" class="tab page <?=$_display?> browse">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'browse' )?>
				<p>
					Configure the default browsing experience for your customers.
				</p>
				<hr />
				<fieldset id="shop-browsing-tweaks">
					<legend>Browsing Tweaks</legend>
					<?php

						$_field					= array();
						$_field['key']		= 'expand_variants';
						$_field['label']	= 'Expand Variants';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['tip']		= 'Expand product variants so that each variant is seemingly an individual product when browsing.';

						echo form_field_boolean( $_field );

					?>
				</fieldset>

				<fieldset id="shop-sorting-tweaks">
					<legend>Sorting Defaults</legend>
					<?php

						/*
						 * Changing these? Then make sure you update the skins etc too.
						 * Probably would be a good TODO to abstract these into a model/config file somewhere.
						 */

						$_field				= array();
						$_field['key']		= 'default_product_per_page';
						$_field['label']	= 'Products per Page';
						$_field['class']	= 'select2';
						$_field['default']	= app_setting( $_field['key'], 'shop' );

						$_options			= array();
						$_options['10']		= '10';
						$_options['25']		= '25';
						$_options['50']		= '50';
						$_options['100']	= '100';

						echo form_field_dropdown( $_field, $_options );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'default_product_sort';
						$_field['label']	= 'Product Sorting';
						$_field['class']	= 'select2';
						$_field['default']	= app_setting( $_field['key'], 'shop' );

						$_options					= array();
						$_options['recent']			= 'Recently Added';
						$_options['price-low-high']	= 'Price: Low to High';
						$_options['price-high-low']	= 'Price: High to Low';
						$_options['a-z']			= 'A to Z';

						echo form_field_dropdown( $_field, $_options );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'skin' ? 'active' : ''?>
			<div id="tab-skin" class="tab page <?=$_display?> skin">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'skin' )?>
				<p>
					The following Shop skins are available to use.
				</p>
				<hr />
				<?php

					if ( $skins ) :

						$skin_selected = app_setting( 'skin', 'shop' ) ? app_setting( 'skin', 'shop' ) : 'skin-shop-gettingstarted';

						echo '<ul class="skins">';
						foreach( $skins AS $skin ) :

							$_name			= ! empty( $skin->name ) ? $skin->name : 'Untitled';
							$_description	= ! empty( $skin->description ) ? $skin->description : '';

							if ( file_exists( $skin->path . 'icon.png' ) ) :

								$_icon = $skin->url . 'icon.png';

							elseif ( file_exists( $skin->path . 'icon.jpg' ) ) :

								$_icon = $skin->url . 'icon.jpg';

							elseif ( file_exists( $skin->path . 'icon.gif' ) ) :

								$_icon = $skin->url . 'icon.gif';

							else :

								$_icon = NAILS_ASSETS_URL . 'img/admin/modules/settings/shop-skin-no-icon.png';

							endif;

							$_selected	= $skin->slug == $skin_selected ? TRUE : FALSE;
							$_class		= $_selected ? 'selected' : '';

							echo '<li class="skin ' . $_class . '" rel="tipsy" title="' . $_description . '">';
								echo '<div class="icon">' . img( $_icon ) . '</div>';
								echo '<div class="name">';
									echo $_name;
									echo '<span class="fa fa-check-circle"></span>';
								echo '</div>';
								echo form_radio( 'skin', $skin->slug, $_selected );
							echo '</li>';

						endforeach;
						echo '</ul>';

						echo '<hr class="clearfix" />';

					else :

						echo '<p class="system-alert error">';
							echo '<strong>Error:</strong> ';
							echo 'I\'m sorry, but I couldn\'t find any skins to use. This is a configuration error and should be raised with the developer.';
						echo '</p>';

					endif;

				?>
				<p>
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'skin_config' ? 'active' : ''?>
			<div id="tab-skin-config" class="tab page <?=$_display?> skin-config">
			<?php

				if ( ! empty( $skin_current ) ) :

					if ( ! empty( $skin_current->settings ) ) :

						echo form_open( NULL, 'style="margin-bottom:0;"' );
						echo form_hidden( 'update', 'skin_config' );
						echo form_hidden( 'skin_slug', $skin_current->slug );

						echo '<p class="system-alert notice">';
							echo 'You are configuring settings for the <strong>' . $skin_current->name . '</strong> shop skin.';
						echo '</p>';

						echo '<fieldset>';

						foreach ( $skin_current->settings AS $setting ) :

							$_field					= array();
							$_field['key']			= ! empty( $setting->key ) ? 'skin_config[' . $setting->key . ']' : '';;
							$_field['label']		= ! empty( $setting->label ) ? $setting->label : '';;
							$_field['placeholder']	= ! empty( $setting->placeholder ) ? $setting->placeholder : '';;
							$_field['tip']			= ! empty( $setting->tip ) ? $setting->tip : '';;

							if ( empty( $_field['key'] ) ) :

								continue;

							else :

								$_field['default']	= app_setting( $setting->key, 'shop-' . $skin_current->slug );

							endif;

							switch( $setting->type ) :

								case 'bool' :
								case 'boolean' :

									echo form_field_boolean( $_field );

								break;

								case 'dropdown' :

									if ( ! empty( $setting->options ) && is_array( $setting->options ) ) :

										$_options = array();
										$_field['class'] = 'select2';

										foreach( $setting->options AS $option ) :

											if ( isset( $option->value ) ) :

												$_value = $option->value;

											else :

												$_value = NULL;

											endif;

											if ( isset( $option->label ) ) :

												$_label = $option->label;

											else :

												$_label = NULL;

											endif;

											$_options[$_value] = $_label;

										endforeach;

										echo form_field_dropdown( $_field, $_options );

									endif;

								break;

								default :

									echo form_field( $_field );

								break;

							endswitch;

						endforeach;

						echo '</fieldset>';

						echo '<p style="margin-top:1em;margin-bottom:0;">';
							echo form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' );
						echo '</p>';

						echo form_close();

					else :

						echo '<p class="system-alert message">';
							echo '<strong>Sorry,</strong> no configurable settings for the "' . $skin_current->name . '" skin.';
						echo '</p>';

					endif;

				else :

					echo '<p class="system-alert message">';
						echo '<strong>Sorry,</strong> no configurable settings for this skin.';
					echo '</p>';

				endif;

			?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'payment_gateway' ? 'active' : ''?>
			<div id="tab-payment-gateway" class="tab page <?=$_display?> payment-gateway">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'payment_gateway' )?>
				<p>
					Set Payment Gateway credentials.
				</p>
				<hr />
				<?php

					if ( ! empty( $payment_gateways ) ) :

						echo '<table id="payment-gateways">';
							echo '<thead class="payment-gateways">';
								echo '<tr>';
									echo '<th class="enabled">Enabled</th>';
									echo '<th class="label">Label</th>';
									echo '<th class="configure">Configure</th>';
								echo '</tr>';
							echo '</thead>';
							echo '<tbody>';

							$_enabled_payment_gateways = set_value( 'enabled_payment_gateways', app_setting( 'enabled_payment_gateways', 'shop' ) );
							$_enabled_payment_gateways = array_filter( (array) $_enabled_payment_gateways );

							foreach( $payment_gateways AS $slug ) :

								$_enabled = array_search( $slug, $_enabled_payment_gateways ) !== FALSE ? TRUE : FALSE;

								echo '<tr>';
									echo '<td class="enabled">';
										echo '<div class="toggle toggle-modern"></div>';
										echo form_checkbox( 'enabled_payment_gateways[]', $slug, $_enabled );
									echo '</td>';
									echo '<td class="label">';
										echo $slug;
									echo '</td>';
									echo '<td class="configure">';
										echo anchor( 'admin/shop/configure/payment_gateway?module=' . urlencode( $slug ), 'Configure', 'data-fancybox-type="iframe" class="fancybox awesome small"' );
									echo '</td>';
								echo '</tr>';

							endforeach;

							echo '<tbody>';
						echo '</table>';
						echo '<hr />';

					else :

						echo '<p class="system-alert error">';
							echo '<strong>No payment gateways are available.</strong>';
							echo '<br />I could not find any payment gateways. Please contact the developers on ' . mailto( APP_DEVELOPER_EMAIL ) . ' for assistance.';
						echo '</p>';

					endif;

				?>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'currencies' ? 'active' : ''?>
			<div id="tab-currencies" class="tab page <?=$_display?> currencies">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'currencies' )?>
				<p>
					Configure supported currencies.
				</p>
				<hr />
				<fieldset id="shop-currencies-base">
					<legend>Base Currency</legend>
					<p>
						The base currency is the default currency of the shop. When you create a new product and define it's
						price, you are doing so in the base currency. You are free to change this but it will be reflected
						across the entire store, <em>change with <strong>extreme</strong> caution</em>.
					</p>
					<p>
					<?php

						//	Base Currency
						$_field					= array();
						$_field['key']			= 'base_currency';
						$_field['label']		= 'Base Currency';
						$_field['default']		= app_setting( $_field['key'], 'shop' );

						$_currencies = array();

						foreach( $currencies AS $c ) :

							$_currencies[$c->code] = $c->code . ' - ' . $c->label;

						endforeach;

						echo form_dropdown( $_field['key'], $_currencies, set_value( $_field['key'], $_field['default'] ), 'class="select2"' );

					?>
					</p>
				</fieldset>
				<fieldset id="shop-currencies-base">
					<legend>Additional Supported Currencies</legend>
					<p>
						Define which currencies you wish to support in your store in addition to the base currency.
					</p>
					<p class="system-alert message">
						<strong>Important:</strong> Not all payment gateways support all currencies and some must be configured to
						support a particular currency. Additional costs may apply, please choose additional currencies carefully.
					</p>
					<p>
					<?php

						$_default = set_value( 'additional_currencies', app_setting( 'additional_currencies', 'shop' ) );
						$_default = array_filter( (array) $_default );

						echo '<select name="additional_currencies[]" multiple="multiple" class="select2">';
						foreach ( $currencies AS $currency ) :

							$_selected = array_search( $currency->code, $_default ) !== FALSE ? 'selected="selected"' : '';

							echo '<option value="'. $currency->code . '" ' . $_selected . '>' . $currency->code . ' - ' . $currency->label . '</option>';

						endforeach;
						echo '</select>';

					?>
					</p>
					<hr />
					<p class="system-alert message">
						<strong>Important:</strong> If you wish to support multiple currencies you must also provide an
						App ID for the <a href="https://openexchangerates.org" target="_blank">Open Exchange Rates</a>
						service. The system uses this service to calculate exchange rates for all supported currencies.
						<br /><br />
						Find out more, and create your App, at <a href="https://openexchangerates.org" target="_blank">Open Exchange Rates</a>.
					</p>
					<?php

						$_field					= array();
						$_field['key']			= 'openexchangerates_app_id';
						$_field['label']		= 'Open Exchange Rates App ID';
						$_field['default']		= app_setting( 'openexchangerates_app_id', 'shop' );
						$_field['placeholder']	= 'Set the Open exchange Rate App ID';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'shipping' ? 'active' : ''?>
			<div id="tab-shipping" class="tab page <?=$_display?> shipping">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'shipping' )?>
				<fieldset id="shop-settings-shipping-domicile">
					<legend>Domicile</legend>
					<p>
						Where is your shop based? The domicile will be used to determine when to
						use international postage rates.
					</p>
					<?php

						$_field					= array();
						$_field['key']			= 'domicile';
						$_field['default']		= app_setting( $_field['key'], 'shop' ) ? app_setting( $_field['key'], 'shop' ) : 'GB';

						echo form_dropdown( $_field['key'], $countries_flat, set_value( $_field['key'], $_field['default'] ), 'class="select2"' );

					?>
				</fieldset>
				<fieldset id="shop-settings-shipping-countries">
					<legend>Ship To</legend>
					<p>
						Where you're willing to ship to. You can be as granular as individual countries,
						or you can choose entire continents. Your domicile will always be a shippable location.
					</p>
					<p>
						<strong>Continents</strong>
					</p>
					<p>
					<?php

						$_default = set_value( 'ship_to_continents', app_setting( 'ship_to_continents', 'shop' ) );
						$_default = array_filter( (array) $_default );

						echo '<select name="ship_to_continents[]" multiple="multiple" class="select2">';
							foreach ( $continents_flat AS $key => $label ) :

								$_selected = array_search( $key, $_default ) !== FALSE ? ' selected="selected"' : '';

								echo '<option value="'. $key . '"' . $_selected . '>' . $label . '</option>';

							endforeach;
						echo '</select>';

					?>
					</p>
					<p>
						<strong>Countries</strong>
					</p>
					<p>
					<?php

						$_default = set_value( 'ship_to_countries', app_setting( 'ship_to_countries', 'shop' ) );
						$_default = array_filter( (array) $_default );

						echo '<select name="ship_to_countries[]" multiple="multiple" class="select2">';
							foreach ( $countries_flat AS $key => $label ) :

								$_selected = array_search( $key, $_default ) !== FALSE ? ' selected="selected"' : '';

								echo '<option value="'. $key . '"' . $_selected . '>' . $label . '</option>';

							endforeach;
						echo '</select>';

					?>
					</p>
					<p>
						<strong>Exclude</strong> - Exclude certain countries, regardless if they fall under any of the options above.
					</p>
					<p>
					<?php

						$_default = set_value( 'ship_to_exclude', app_setting( 'ship_to_exclude', 'shop' ) );
						$_default = array_filter( (array) $_default );

						echo '<select name="ship_to_exclude[]" multiple="multiple" class="select2">';
							foreach ( $countries_flat AS $key => $label ) :

								$_selected = array_search( $key, $_default ) !== FALSE ? ' selected="selected"' : '';

								echo '<option value="'. $key . '"' . $_selected . '>' . $label . '</option>';

							endforeach;
						echo '</select>';

					?>
					</p>
				</fieldset>

				<hr />
				<p>
					Enable the shipping modules you wish to use. A shipping module defines a set of rules as
					to how to calculate the shipping cost for a basket. The system will work down the list, top
					to bottom, until an enabled module is able to give a price.
				</p>
				<p>
					If no price can be determined then the user will receive an error indicating to them that
					they should get in touch to complete the order.
				</p>
				<?php

					if ( ! empty( $shipping_modules ) ) :

						echo '<table id="shipping-modules">';
							echo '<thead class="shipping-modules">';
								echo '<tr>';
									echo '<th class="order">&nbsp;</th>';
									echo '<th class="enabled">Enabled</th>';
									echo '<th class="label">Label</th>';
									echo '<th class="configure">Configure</th>';
								echo '</tr>';
							echo '</thead>';
							echo '<tbody>';

							$_enabled_shipping_modules = set_value( 'enabled_shipping_modules', app_setting( 'enabled_shipping_modules', 'shop' ) );
							$_enabled_shipping_modules = array_filter( (array) $_enabled_shipping_modules );

							$_modules = array();

							//	Enabled ones first, in order
							foreach( $_enabled_shipping_modules AS $sm ) :

								$_modules[] = $sm;

							endforeach;

							//	Followed by the others
							foreach( $shipping_modules AS $sm ) :

								if ( array_search( $sm->slug, $_modules ) === FALSE ) :

									$_modules[] = $sm->slug;

								endif;

							endforeach;

							foreach( $_modules AS $slug ) :

								$_module = ! empty( $shipping_modules[$slug] ) ? $shipping_modules[$slug] : FALSE;

								if ( ! $_module ) :

									continue;

								endif;

								// --------------------------------------------------------------------------

								$_name			= ! empty( $_module->name ) ? $_module->name : 'Untitled';
								$_description	= ! empty( $_module->description ) ? $_module->description : '';
								$_enabled		= array_search( $slug, $_enabled_shipping_modules ) !== FALSE ? TRUE : FALSE;

								echo '<tr>';
									echo '<td class="order">';
										echo '<span class="fa fa-navicon"></span>';
									echo '</td>';
									echo '<td class="enabled">';
										echo '<div class="toggle toggle-modern"></div>';
										echo form_checkbox( 'enabled_shipping_modules[]', $slug, $_enabled );
									echo '</td>';
									echo '<td class="label">';
										echo $_name;
										echo $_description ? '<small>' . $_description . '</small>' : '';
									echo '</td>';
									echo '<td class="configure">';
										echo anchor( 'admin/shop/configure/shipping?module=' . urlencode( $slug ), 'Configure', 'data-fancybox-type="iframe" class="fancybox awesome small"' );
									echo '</td>';
								echo '</tr>';

							endforeach;

							echo '<tbody>';
						echo '</table>';
						echo '<hr />';

					else :

						echo '<p class="system-alert error">';
							echo '<strong>No shipping modules are available.</strong>';
							echo '<br />I could not find any shipping modules. Please contact the developers on ' . mailto( APP_DEVELOPER_EMAIL ) . ' for assistance.';
						echo '</p>';

					endif;

				?>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>
		</section>
</div>
<script type="text/template" id="template-new-shipping">
<tr>
	<td class="order-handle">
		<input type="hidden" name="methods[{{counter}}][order]" value="" class="order" />
	</td>
	<td class="courier">
		<?=form_input( 'methods[{{counter}}][courier]', NULL, 'class="table-cell"' )?>
	</td>
	<td class="method">
		<?=form_input( 'methods[{{counter}}][method]', NULL, 'class="table-cell"' )?>
	</td>
	<td class="default_price">
		<?=form_input( 'methods[{{counter}}][default_price]', NULL, 'class="table-cell"' )?>
	</td>
	<td class="default_price_additional">
		<?=form_input( 'methods[{{counter}}][default_price_additional]', NULL, 'class="table-cell"' )?>
	</td>
	<td class="tax_rate">
		<?=form_dropdown( 'methods[{{counter}}][tax_rate_id]', $tax_rates_flat, NULL, 'class="select2"' )?>
	</td>
	<td class="notes">
		<?=form_input( 'methods[{{counter}}][notes]', NULL, 'class="table-cell"' )?>
	</td>
	<td class="active">
		<?=form_checkbox( 'methods[{{counter}}][is_active]', TRUE )?>
	</td>
	<td class="default">
		<?=form_radio( 'default', '{{counter}}' )?>
	</td>
	<td class="delete">
		<a href="#" class="delete-row awesome small red">Delete</a>
	</td>
</tr>
</script>
<script type="text/template" id="template-new-tax-rate">
<tr>
	<td class="label">
		<?=form_input( 'rates[{{counter}}][label]', NULL, 'placeholder="Specify the tax rate label, e.g VAT" class="table-cell"' )?>
	</td>
	<td class="rate">
		<?=form_input( 'rates[{{counter}}][rate]', NULL, 'placeholder="Specify the rate for this tax band, decimal between 0 and 1, e.g for 20% you\'d enter 0.2" class="table-cell"' )?>
	</td>
	<td class="delete">
		<a href="#" class="delete-row awesome small red">Delete</a>
	</td>
</tr>
</script>