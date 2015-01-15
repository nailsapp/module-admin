<div class="group-testimonials browse">
	<p>
	<?php

		echo lang( 'testimonials_index_intro' );

		if ( user_has_permission( 'admin.testimonial:0.can_create' ) ) :

			echo anchor( 'admin/testimonial/create', lang( 'testimonials_nav_create' ), 'class="awesome small green right"' );

		endif;

	?>
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

				foreach ( $testimonials as $testimonial ) :

					echo '<tr>';
						echo '<td class="quote">';
							echo $testimonial->quote;
							echo '<small>' . $testimonial->quote_by . '</small>';
						echo '</td>';
						echo '<td class="order">';
							echo $testimonial->order;
						echo '</td>';
						echo '<td class="actions">';

							if ( user_has_permission( 'admin.testimonial:0.can_edit_objects' ) ) :

								echo anchor( 'admin/testimonial/edit/' . $testimonial->id, lang( 'action_edit' ), 'class="awesome small"' );

							endif;

							if ( user_has_permission( 'admin.testimonial:0.can_delete_objects' ) ) :

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