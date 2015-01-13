<div class="group-notification overview">
	<p>
		Configure who gets email notifications when certain events happen on site.
		Separate multiple email addresses using a comma; leaving blank will disable
		the notification
	</p>
	<hr />
	<?php

		if ( $notifications ) :

			echo form_open();

			foreach ( $notifications AS $grouping => $noti ) :

				echo '<fieldset>';
					echo $noti->label ? '<legend>' . $noti->label . '</legend>' : '';
					echo $noti->description ? '<p>' . $noti->description . '</p><hr />' : '';

					echo '<table>';
						echo '<thead>';
							echo '<tr>';
								echo '<th class="event-label">Event Name</th>';
								echo '<th class="value">Value</th>';
							echo '</tr>';
						echo '<thead>';
						echo '<tbody>';

						foreach ( $noti->options AS $key => $data ) :

							$_default = implode( ', ', $this->app_notification_model->get( $key, $grouping ) );

							echo '<tr>';
								echo '<td class="event-label">';
									echo ! empty( $data->label ) ? $data->label : 'Unknown';
									if ( ! empty( $data->sub_label ) ) :

									echo '<small>' . $data->sub_label . '</small>';

									endif;
								echo '</td>';

								$_has_tip = ! empty( $data->tip ) ? 'has-tip' : '';
								echo '<td class="value ' . $_has_tip . '">';
									echo '<div class="input-wrapper">';
										$_value = isset( $_POST['notification'][$grouping][$key] ) ? $_POST['notification'][$grouping][$key] : $_default;
										echo form_input( 'notification[' . $grouping . '][' . $key . ']', $_value, 'placeholder="Separate multiple email addresses using a comma"' );
									echo '</div>';
									echo $_has_tip ? '<b class="fa fa-question-circle fa-lg pull-right" rel="tipsy" title="' . str_replace( '"', '&quot;', $data->tip ) .  '"></b>' : '';
								echo '</td>';
							echo '</tr>';

						endforeach;

						echo '</tbody>';
					echo '</table>';
				echo '</fieldset>';

			endforeach;

			echo '<p>';
				echo form_submit( 'submit', lang( 'action_save_changes' ), 'class="awesome"' );
			echo '</p>';

			echo form_close();

		else :

			echo '<p class="system-alert">';
				echo 'Sorry, there are no configurable notifications.';
			echo '</p>';

		endif;

	?>
</div>