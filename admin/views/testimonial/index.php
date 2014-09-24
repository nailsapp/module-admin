<div class="group-testimonials browse">
	<p>
		<?=lang( 'testimonials_index_intro' )?>
	</p>
	<table style="margin-bottom:1.5em;">
		<thead>
			<tr>
				<th class="quote"><?=lang( 'testimonials_index_th_quote' )?></th>
				<th class="order"><?=lang( 'testimonials_index_th_order' )?></th>
				<th class="actions"><?=lang( 'testimonials_index_th_actions' )?></th>
			</tr>
		</thead>
		<tbody>
			<?php

			if ( $testimonials ) :

				foreach ( $testimonials AS $testimonial ) :

					echo '<tr>';
						echo '<td class="quote">';
							echo $testimonial->quote;
							echo '<small>' . $testimonial->quote_by . '</small>';
						echo '</td>';
						echo '<td class="order">';
							echo $testimonial->order;
						echo '</td>';
						echo '<td class="actions">';

							if ( user_has_permission( 'admin.testimonial:0.can_edit' ) ) :

								echo anchor( 'admin/testimonial/edit/' . $testimonial->id, lang( 'action_edit' ), 'class="awesome small"' );

							endif;

							if ( user_has_permission( 'admin.testimonial:0.can_delete' ) ) :

								echo anchor( 'admin/testimonial/delete/' . $testimonial->id, lang( 'action_delete' ), 'class="awesome red small confirm" data-title="Are you sure?" data-body="You cannot undo this action"' );

							endif;

						echo '</td>';
					echo '<tr>';

				endforeach;

			else :

				?>
				<tr>
					<td colspan="3" class="no-data"><?=lang( 'testimonials_index_no_testimonials' )?></td>
				</tr>
				<?php

			endif;

			?>
		</tbody>
	</table>
</div>