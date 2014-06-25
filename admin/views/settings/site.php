<div class="group-settings site">
	<p>
		Configure various aspects of the site.
	</p>

	<hr />

		<ul class="tabs">
			<?php $_active = $this->input->post( 'update' ) == 'analytics' || ! $this->input->post() ? '' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-analytics">Analytics</a>
			</li>
			<?php $_active = $this->input->post( 'update' ) == 'auth' ? 'active' : 'active'?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-auth">Registration &amp; Authentication</a>
			</li>

		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'analytics' || ! $this->input->post() ? '' : ''?>
			<div id="tab-analytics" class="tab page <?=$_display?> analytics">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'analytics' )?>
				<p>
					Configure your analytics accounts. If field is left empty then that provider will not be used.
				</p>
				<hr />
				<fieldset id="shop-settings-notifications">
					<legend>Google Analytics</legend>
					<?php

						//	Order Notifications
						$_field					= array();
						$_field['key']			= 'google_analytics_account';
						$_field['label']		= 'Profile ID';
						$_field['default']		= app_setting( $_field['key'] );
						$_field['placeholder']	= 'UA-XXXXX-YY';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'auth' ? 'active' : 'active'?>
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
						You can refer to <?=anchor( 'http://hybridauth.sourceforge.net/userguide.html', 'HybridAuth\'s Documentation' )?> for
						instructions on how to create these applications
					</p>
					<?php

						foreach( $providers AS $provider ) :

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
										echo 'Please provide the following information. All fields are required.';
									echo '</p>';

									foreach ( $provider['fields'] AS $key => $label ) :

										$_field				= array();
										$_field['key']		= 'auth_social_signon_' . $provider['slug'] . '_' . $key;
										$_field['label']	= $label;
										$_field['required']	= TRUE;
										$_field['default']	= app_setting( $_field['key'] );

										echo form_field( $_field );

									endforeach;

								echo '</div>';

							echo '</div>';

						endforeach;

					?>
				</fieldset>
				<?php endif; ?>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

		</section>
</div>