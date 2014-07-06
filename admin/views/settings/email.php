<div class="group-settings site">
	<p>
		Configure the way the app sends email.
	</p>

	<hr />

		<ul class="tabs">
			<?php $_active = $this->input->post( 'update' ) == 'customise' || ! $this->input->post() ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-customise">Customise</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'driver' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-driver">Driver</a>
			</li>
		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'customise' || ! $this->input->post() ? 'active' : ''?>
			<div id="tab-customise" class="tab page <?=$_display?>">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'customise' )?>
				<fieldset id="email-settings-sender">
					<legend>Sender Details</legend>
					<p>
						These fields allow you to set the sender details of email sent from the site.
					</p>
					<?php

						$_field					= array();
						$_field['key']			= 'from_name';
						$_field['label']		= 'From Name';
						$_field['default']		= app_setting( $_field['key'], 'email' ) ? app_setting( $_field['key'], 'email' ) : APP_NAME;
						$_field['placeholder']	= 'The name of the sender which recipients should see.';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_url		= parse_url( site_url() );
						$_default	= 'nobody@' . $_url['host'];

						$_field					= array();
						$_field['key']			= 'from_email';
						$_field['label']		= 'From Email';
						$_field['default']		= app_setting( $_field['key'], 'email' ) ? app_setting( $_field['key'], 'email' ) : $_default;
						$_field['placeholder']	= 'The email address of the sender which recipients should see.';
						$_field['info']			= '<strong>Note:</strong> If sending using SMTP tos end email ensure this email is a valid account on the mail server. If it\'s not valid, some services will junk the email.';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'driver' ? 'active' : ''?>
			<div id="tab-driver" class="tab page <?=$_display?>">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'driver' )?>

				<p>
					Choose an environment to configure:
				</p>
				<p>
				<?php

					$_field						= array();
					$_field['key']				= 'environment';

					$_options					= array();
					$_options['']				= '';
					$_options['DEVELOPMENT']	= 'Development';
					$_options['STAGING']		= 'Staging';
					$_options['PRODUCTION']		= 'Production';

					if ( isset( $_options[ strtoupper( ENVIRONMENT ) ] ) ) :

						$_options[ strtoupper( ENVIRONMENT ) ] .= ' (current)';

					endif;

					echo form_dropdown( $_field['key'], $_options, set_value( $_field['key'] ), 'id="driver-environment" data-placeholder="Please Choose an Environment" class="select2"' );

				?>
				</p>

				<hr />

				<?php

					unset( $_options[''] );

					foreach( $_options AS $environment => $label ) :

						?>

						<div class="driver-settings <?=$environment?>" style="display:none;">

							<fieldset id="email-settings-driver-<?=$environment?>">
								<legend><?=$environment?>: Send Using</legend>
								<?php

									$_field					= array();
									$_field['key']			= $environment . '_driver';
									$_field['label']		= 'Driver';
									$_field['default']		= app_setting( $_field['key'], 'email' ) ? app_setting( $_field['key'], 'email' ) : 'SMTP';
									$_field['class']		= 'select2 environment-driver';

									$_options				= array();
									$_options['SMTP']		= 'SMTP';
									$_options['MANDRILL']	= 'Mandrill';

									echo form_field_dropdown( $_field, $_options );

								?>
							</fieldset>

							<fieldset id="email-settings-driver-smtp-<?=$environment?>" class="settings-smtp" style="display:none">
								<legend><?=$environment?>: SMTP Settings</legend>
								<?php

									$_field					= array();
									$_field['key']			= $environment . '_smtp_host';
									$_field['label']		= 'Host';
									$_field['default']		= app_setting( $_field['key'], 'email' ) ? app_setting( $_field['key'], 'email' ) : 'localhost';

									echo form_field( $_field );

									// --------------------------------------------------------------------------

									$_field					= array();
									$_field['key']			= $environment . '_smtp_username';
									$_field['label']		= 'Username';
									$_field['default']		= app_setting( $_field['key'], 'email' );

									echo form_field( $_field );

									// --------------------------------------------------------------------------

									$_field					= array();
									$_field['key']			= $environment . '_smtp_password';
									$_field['label']		= 'Password';
									$_field['default']		= app_setting( $_field['key'], 'email' );

									echo form_field( $_field );

									// --------------------------------------------------------------------------

									$_field					= array();
									$_field['key']			= $environment . '_smtp_port';
									$_field['label']		= 'Port';
									$_field['default']		= app_setting( $_field['key'], 'email' ) ? app_setting( $_field['key'], 'email' ) : '25';

									echo form_field( $_field );

								?>
							</fieldset>

							<fieldset id="email-settings-driver-mandrill-<?=$environment?>" class="settings-mandrill" style="display:none">
								<legend><?=$environment?>: Mandrill Settings</legend>
								<?php

									$_field					= array();
									$_field['key']			= $environment . '_mandrill_api_key';
									$_field['label']		= 'API Key';
									$_field['default']		= app_setting( $_field['key'], 'email' );

									echo form_field( $_field );

								?>
							</fieldset>

						</div>

						<?php

					endforeach;

				?>

				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>

			</div>

		</section>
</div>