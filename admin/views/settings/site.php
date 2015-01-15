<div class="group-settings site">
	<p>
		Configure various aspects of the site.
	</p>

	<hr />

		<ul class="tabs">

			<?php $_active = $this->input->post( 'update' ) == 'analytics' || ! $this->input->post() ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-analytics">Analytics</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'auth' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-auth">Registration &amp; Authentication</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'maintenance' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-maintenance">Maintenance Mode</a>
			</li>

		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'analytics' || ! $this->input->post() ? 'active' : ''?>
			<div id="tab-analytics" class="tab page <?=$_display?> analytics">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'analytics' )?>
				<p>
					Configure your analytics accounts. If field is left empty then that provider will not be used.
				</p>
				<hr />
				<fieldset id="site-settings-google">
					<legend>Google Analytics</legend>
					<?php

						$_field					= array();
						$_field['key']			= 'google_analytics_account';
						$_field['label']		= 'Profile ID';
						$_field['default']		= app_setting( $_field['key'] );
						$_field['placeholder']	= 'UA-XXXXX-YY';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'auth' ? 'active' : ''?>
			<div id="tab-auth" class="tab page <?=$_display?> auth">
				<?=form_open( NULL, 'style="margin-bottom:0;"')?>
				<?=form_hidden( 'update', 'auth' )?>
				<p>
					Configure the site's registration and authentication settings &amp; defaults.
				</p>
				<hr />
				<fieldset>
					<legend>User Registration</legend>
					<?php

						$_field					= array();
						$_field['key']			= 'user_registration_enabled';
						$_field['label']		= 'Registration Enabled';
						$_field['default']		= app_setting( $_field['key'] ) ? TRUE : FALSE;

						echo form_field_boolean( $_field, 'Admin will always be able to create users.' );

					?>
				</fieldset>
				<?php if ( ! empty( $providers ) ) : ?>
				<fieldset id="site-settings-socialsignin">
					<legend>Social Sign In</legend>
					<p>
						With the exception of OpenID providers, each social network requires that you create an external application which links
						your website to theirs. These external applications ensure that users are logging into the proper website and allows
						the network to send the user back to the correct website after successfully authenticating their account.
					</p>
					<p>
						You can refer to <?=anchor( 'http://hybridauth.sourceforge.net/userguide.html', 'HybridAuth\'s Documentation', 'target="_blank"' )?> for
						instructions on how to create these applications
					</p>
					<?php

						foreach ( $providers as $provider ) :

							$_field					= array();
							$_field['key']			= 'auth_social_signon_' . $provider['slug'] . '_enabled';
							$_field['label']		= $provider['label'];
							$_field['default']		= app_setting( $_field['key'] ) ? TRUE : FALSE;

							echo '<div class="field checkbox boolean configure-provider">';

								echo '<span class="label">';
									echo $_field['label'];
								echo '</span>';
								echo '<span class="input">';

									$_selected = set_value( $_field['key'], (bool) $_field['default'] );

									echo '<div class="toggle toggle-modern"></div>';
									echo form_checkbox( $_field['key'], TRUE, $_selected );
									echo $provider['fields'] ? '<a href="#configure-provider-' . $provider['slug'] . '" class="awesome orange fancybox">Configure</a>' : '';
									echo form_error( $_field['key'], '<span class="error">', '</span>' );

								echo '</span>';

								echo '<div id="configure-provider-' . $provider['slug'] . '" class="configure-provider-fancybox" style="display:none;">';

									echo '<p style="text-align:center;">';
										echo 'Please provide the following information. Fields marked with a * are required.';
									echo '</p>';

									foreach ( $provider['fields'] as $key => $label ) :

										/**
										 * Secondary conditional detects an actual array fo fields rather than
										 * just the label/required array. Design could probably be improved...
										 **/

										if ( is_array( $label ) && ! isset( $label['label'] ) ) :

											foreach ( $label as $key1 => $label1 ) :

												$_field				= array();
												$_field['key']		= 'auth_social_signon_' . $provider['slug'] . '_' . $key . '_' . $key1;
												$_field['label']	= $label1['label'];
												$_field['required']	= $label1['required'];
												$_field['default']	= app_setting( $_field['key'] );

												echo form_field( $_field );

											endforeach;

										else :

											$_field				= array();
											$_field['key']		= 'auth_social_signon_' . $provider['slug'] . '_' . $key;
											$_field['label']	= $label['label'];
											$_field['required']	= $label['required'];
											$_field['default']	= app_setting( $_field['key'] );

											echo form_field( $_field );

										endif;

									endforeach;

								echo '</div>';

							echo '</div>';

						endforeach;

					?>
				</fieldset>
				<?php endif; ?>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'maintenance' ? 'active' : ''?>
			<div id="tab-maintenance" class="tab page <?=$_display?> maintenance">
				<?=form_open( NULL, 'style="margin-bottom:0;"')?>
				<?=form_hidden( 'update', 'maintenance' )?>
				<p>
					Maintenance mode disables disables access to the site with the exception
					for those IP addresses listed in the whitelist.
				</p>
				<p class="system-alert message">
					<strong>Note:</strong> Maintenance mode can be enabled via this setting,
					or by placing a file entitled <code>.MAINTENANCE</code> at the site's root.
					If the <code>.MAINTENANCE</code> file is found then the site will forcibly
					be placed into maintenance mode, regardless of this setting.
				</p>
				<hr />
				<fieldset>
					<legend>Maintenance Mode</legend>
					<?php

						$_field					= array();
						$_field['key']			= 'maintenance_mode_enabled';
						$_field['label']		= 'Enabled';
						$_field['default']		= app_setting( $_field['key'] ) ? TRUE : FALSE;

						echo form_field_boolean( $_field );

						// --------------------------------------------------------------------------

						$_field					= array();
						$_field['key']			= 'maintenance_mode_whitelist';
						$_field['label']		= 'Whitelist';
						$_field['type']			= 'textarea';
						$_field['default']		= trim( implode( "\n", (array) app_setting( $_field['key'] ) ) );
						$_field['placeholder']	= 'Specify IP addresses to whitelist either comma seperated or on new lines.';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

		</section>
</div>