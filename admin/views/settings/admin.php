<div class="group-settings site">
	<p>
		Configure various aspects of the site.
	</p>

	<hr />

		<ul class="tabs">
			<?php $_active = $this->input->post( 'update' ) == 'branding' || ! $this->input->post() ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-branding">Branding</a>
			</li>

			<?php $_active = $this->input->post( 'update' ) == 'whitelist' ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-whitelist">Whitelist</a>
			</li>

		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'branding' || ! $this->input->post() ? 'active' : ''?>
			<div id="tab-branding" class="tab page <?=$_display?> branding">
				<?=form_open( NULL, 'style="margin-bottom:0;"' )?>
				<?=form_hidden( 'update', 'branding' )?>
				<p>
					Give admin a lick of paint using your brand colours.
				</p>
				<hr />
				<fieldset id="site-settings-google">
					<legend>Colours</legend>
					<?php

						$_field					= array();
						$_field['key']			= 'primary_colour';
						$_field['label']		= 'Primary Colour';
						$_field['default']		= app_setting( $_field['key'], 'admin' );
						$_field['placeholder']	= 'Specify a valid CSS colour value, i.e a hex code or rgb()';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field					= array();
						$_field['key']			= 'secondary_colour';
						$_field['label']		= 'Secondary Colour';
						$_field['default']		= app_setting( $_field['key'], 'admin' );
						$_field['placeholder']	= 'Specify a valid CSS colour value, i.e a hex code or rgb()';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field					= array();
						$_field['key']			= 'highlight_colour';
						$_field['label']		= 'Highlight Colour';
						$_field['default']		= app_setting( $_field['key'], 'admin' );
						$_field['placeholder']	= 'Specify a valid CSS colour value, i.e a hex code or rgb()';

						echo form_field( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome" style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

			<?php $_display = $this->input->post( 'update' ) == 'whitelist' ? 'active' : ''?>
			<div id="tab-whitelist" class="tab page <?=$_display?> whitelist">
				<?=form_open( NULL, 'style="margin-bottom:0;"')?>
				<?=form_hidden( 'update', 'whitelist' )?>
				<p>
					Specify which IP's can access admin. If no IP addresses are specified then
					admin will be accessible from any IP address.
				</p>
				<hr />
				<fieldset>
					<legend>Admin Whitelist</legend>
					<?php

						$_field					= array();
						$_field['key']			= 'whitelist';
						$_field['label']		= 'Whitelist';
						$_field['type']			= 'textarea';
						$_field['default']		= trim( implode( "\n", (array) app_setting( $_field['key'], 'admin' ) ) );
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