<div class="group-settings shop-pg">
	<p>
		Configure the WorldPay payment gateway. The following fields can be found within the "Installation Administration" area of WorldPay's Merchant interface.
	</p>
	<hr />
	<?=form_open( 'admin/settings/shop_pg/WorldPay?is_fancybox=' . $this->input->get( 'is_fancybox' ) )?>
		<fieldset id="shop-pg-settings-worldpay">
			<legend>WorldPay Settings</legend>
			<?php

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_installationId';
				$_field['label']		= 'Installation ID';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Installation ID for this shop.';
				$_field['required']		= TRUE;

				echo form_field( $_field );

				// --------------------------------------------------------------------------

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_accountId';
				$_field['label']		= 'Account ID';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Account ID for this shop.';
				$_field['required']		= TRUE;

				echo form_field( $_field );

				// --------------------------------------------------------------------------

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_secretWord';
				$_field['label']		= 'Secret Word';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Secret Word for this installation.';
				$_field['type']			= 'password';

				echo form_field( $_field );

				// --------------------------------------------------------------------------

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_callbackPassword';
				$_field['label']		= 'Callback Password';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Callback Password for this installation.';
				$_field['type']			= 'password';

				echo form_field( $_field );

			?>
		</fieldset>
		<p>
			<button type="submit" class="awesome">
				Save Changes
			</button>
		</p>
	<?=form_close()?>
</div>