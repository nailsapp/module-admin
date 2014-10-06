<div class="group-settings shop-pg">
	<p <?=$this->input->get( 'is_fancybox' ) ? 'class="system-alert"' : ''?>>
		Configure the WorldPay payment gateway. The following fields can be either found or set within the "Installation Administration" area of WorldPay's Merchant interface.
	</p>
	<hr />
	<?=form_open( 'admin/settings/shop_pg/WorldPay?is_fancybox=' . $this->input->get( 'is_fancybox' ) )?>
		<fieldset>
			<legend>WorldPay Defined Settings</legend>
			<p>
				Provide details of the following settings which WorldPay has defined.
			</p>
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
				$_field['label']		= 'Administratrion Code';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Account ID for this shop.';
				$_field['required']		= TRUE;

				echo form_field( $_field );

			?>
		</fieldset>
		<fieldset>
			<legend>User Defined Settings</legend>
			<p>
				These settings are defined by the user, it is important that details are entered <strong>exactly</strong> as they are on WorldPay.
			</p>
			<?php

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_callbackPassword';
				$_field['label']		= 'Payment Response Password';
				$_field['info']			= '<a href="#" id="generate-password" class="awesome small">Generate</a>';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The Payment Response Password for this installation.';
				$_field['type']			= 'password';
				$_field['required']		= TRUE;
				$_field['id']			= 'the-password';
				$_field['tip']			= 'This should be sufficiently hard to guess and definitely never revealed to anyone.';

				echo form_field( $_field );

				// --------------------------------------------------------------------------

				$_field					= array();
				$_field['key']			= 'omnipay_WorldPay_secretWord';
				$_field['label']		= 'MD5 Secret for Transactions';
				$_field['info']			= '<a href="#" id="generate-secret" class="awesome small">Generate</a>';
				$_field['default']		= app_setting( $_field['key'], 'shop' );
				$_field['placeholder']	= 'The MD5 Secret for this installation\'s transactions.';
				$_field['type']			= 'password';
				$_field['required']		= TRUE;
				$_field['id']			= 'the-secret';
				$_field['tip']			= 'This should be sufficiently hard to guess and definitely never revealed to anyone.';

				echo form_field( $_field );

			?>
		</fieldset>
		<p>
			<button type="submit" class="awesome">
				Save Changes
			</button>
		</p>
	<?=form_close()?>
	<hr />
	<p>
		The following settings are not configurable at this end, but should be set in the WorldPay Installation Administration area,
	</p>
	<fieldset>
		<?php

			$_field					= array();
			$_field['key']			= 'noteditable_description';
			$_field['label']		= 'Description';
			$_field['placeholder']	= 'Anything you like, something descriptive!';
			$_field['readonly']		= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field					= array();
			$_field['key']			= 'noteditable_customerdescription';
			$_field['label']		= 'Customer Description';
			$_field['placeholder']	= 'Anything you like, something descriptive!';
			$_field['readonly']		= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_storebuilderused';
			$_field['label']	= 'Store-Builder used:';
			$_field['default']	= 'Other - Please specify below';
			$_field['readonly']	= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_storebuilderother';
			$_field['label']	= 'Store-builder: if other - please specify';
			$_field['default']	= 'Nails Shop';
			$_field['readonly']	= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_shop_url = app_setting( 'url', 'shop' ) ? app_setting( 'url', 'shop' ) : 'shop/';

			$_field				= array();
			$_field['key']		= 'noteditable_paymentresponseurl';
			$_field['label']	= 'Payment Response URL';
			$_field['default']	= site_url( 'api/shop/webhook/worldpay' );
			$_field['readonly']	= TRUE;
			$_field['info']		 = 'Please note that this URL will vary between installations. The URL shown ';
			$_field['info']		.= 'above is correct for the <strong>' . ENVIRONMENT . '</strong> (or equivilent) ';
			$_field['info']		.= 'installation. If WorldPay is having problems confirming payments ensure this URL ';
			$_field['info']		.= 'is worldwide accessible.';

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_paymentresponseenabled';
			$_field['label']	= 'Payment Response enabled?';
			$_field['default']	= TRUE;
			$_field['readonly']	= TRUE;

			echo form_field_boolean( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_enablerecurringpaymentresponse';
			$_field['label']	= 'Enable Recurring Payment Response';
			$_field['default']	= TRUE;
			$_field['readonly']	= TRUE;

			echo form_field_boolean( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_';
			$_field['label']	= 'Enable the Shopper Response';
			$_field['default']	= FALSE;
			$_field['readonly']	= TRUE;

			echo form_field_boolean( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_';
			$_field['label']	= 'Suspension of Payment Response';
			$_field['default']	= FALSE;
			$_field['readonly']	= TRUE;

			echo form_field_boolean( $_field );

			// --------------------------------------------------------------------------

			$_field					= array();
			$_field['key']			= 'noteditable_';
			$_field['label']		= 'Payment Response failure email address';
			$_field['placeholder']	= 'Email address where you\'d like to receive failure notifications';
			$_field['readonly']		= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_';
			$_field['label']	= 'Attach HTTP(s) Payment Message to the failure email?';
			$_field['default']	= TRUE;
			$_field['readonly']	= TRUE;

			echo form_field_boolean( $_field );

			// --------------------------------------------------------------------------

			$_field					= array();
			$_field['key']			= 'noteditable_';
			$_field['label']		= 'Merchant receipt email address';
			$_field['placeholder']	= 'Email address where you\'d like to receive receipts';
			$_field['readonly']		= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field					= array();
			$_field['key']			= 'noteditable_';
			$_field['label']		= 'Info servlet password';
			$_field['placeholder']	= 'Leave blank';
			$_field['readonly']		= TRUE;

			echo form_field( $_field );

			// --------------------------------------------------------------------------

			$_field				= array();
			$_field['key']		= 'noteditable_';
			$_field['label']	= 'Signature Fields';
			$_field['default']	= 'instId:amount:currency:cartId';
			$_field['readonly']	= TRUE;

			echo form_field( $_field );

		?>
	</fieldset>
	<fieldset>
		<legend>Template Pages</legend>
		<p>
			In order to make the checkout experience more pleasant for the user, we recommend replacing the default WorldPay template files with these ones:
		</p>
		<ul>
			<li><a href="#template-resultY" class="fancybox">resultY.html</a> - The page the user sees when checkout is successfull.</li>
			<li><a href="#template-resultC" class="fancybox">resultC.html</a> - The page the user sees when checkout is cancelled.</li>
		</ul>
	</fieldset>
</div>
<div type="text/template" id="template-resultY" style="display:none">
<p style="max-width:100%;box-sizing:border-box;" class="system-alert message">
	Use the HTML code below to create a file called <code>resultY.html</code> and upload it into the file management area of WorldPay.
	<br />
	<strong>Please note:</strong> The URL mentioned in this template will vary between environments.
</p>
<textarea readonly="readonly" onClick="this.select();" style="width:100%;box-sizing:border-box;height:400px;margin:0;">
<?php
$_app_name			= APP_NAME;
$_shop_url			= app_setting( 'url', 'shop' ) ? app_setting( 'url', 'shop' ) : 'shop/';
$_processing_url	= site_url( $_shop_url . 'checkout/processing' );
$_html = <<<EOT
<html>
	<head>
		<title>Please Wait...</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta http-equiv="refresh" content="3;URL='$_processing_url'" />
		<style type="text/css">

			body
			{
				font-family:helvetica,arial,sans-serif;
				font-size:12px;
				text-align:Center;
				padding:30px;
			}

			h2
			{
				font-size:12px;
				margin-bottom:40px;
			}

			input[type=submit]
			{
				border:1px solid #ccc;
				background:#ececec;
				padding:7px;
				cursor:pointer;
				border-radius:3px;
				-webkit-border-radius:3px;
				-moz-border-radius:3px;
			}

		</style>
	</head>
	<body>
		<h1>
			Please wait while we redirect
			<br />you back to $_app_name
		</h1>
		<p>
			Your payment was accepted.
		</p>
	</body>
</html>
EOT;

	echo htmlspecialchars( $_html );
?>
</textarea>
</div>
<div type="text/template" id="template-resultC" style="display:none">
<p style="width:100%;box-sizing:border-box;" class="system-alert message">
	Use the HTML code below to create a file called <code>resultC.html</code> and upload it into the file management area of WorldPay.
	<br />
	<strong>Please note:</strong> The URL mentioned in this template will vary between environments.
</p>
<textarea readonly="readonly" onClick="this.select();" style="width:100%;box-sizing:border-box;height:400px;margin:0;">
<?php
$_app_name			= APP_NAME;
$_shop_url			= app_setting( 'url', 'shop' ) ? app_setting( 'url', 'shop' ) : 'shop/';
$_processing_url	= site_url( $_shop_url . 'checkout/cancel' );
$_html = <<<EOT
<html>
	<head>
		<title>Please Wait...</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta http-equiv="refresh" content="3;URL='$_processing_url'" />
		<style type="text/css">

			body
			{
				font-family:helvetica,arial,sans-serif;
				font-size:12px;
				text-align:Center;
				padding:30px;
			}

			h2
			{
				font-size:12px;
				margin-bottom:40px;
			}

			input[type=submit]
			{
				border:1px solid #ccc;
				background:#ececec;
				padding:7px;
				cursor:pointer;
				border-radius:3px;
				-webkit-border-radius:3px;
				-moz-border-radius:3px;
			}

		</style>
	</head>
	<body>
		<h1>
			Please wait while we redirect
			<br />you back to $_app_name
		</h1>
		<p>
			Your payment was cancelled.
		</p>
	</body>
</html>
EOT;

	echo htmlspecialchars( $_html );
?>
</textarea>
</div>