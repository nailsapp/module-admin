<div class="group-settings blog index">
	<p>
		The following blogs are enabled on your site.
		<?php

			if ( $this->user_model->is_superuser() ) :

				echo anchor( 'admin/settings/blog/create', 'Create Blog', 'class="right awesome small green"' );

			endif;

		?>
	</p>
	<hr />
	<table>
		<thead>
			<tr>
				<th class="label">Label</th>
				<th class="actions">Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php

			if ( ! empty( $blogs ) ) :

				foreach ( $blogs AS $blog ) :

					echo '<tr>';
						echo '<td class="label">';
							echo $blog->label;
						echo '</td>';
						echo '<td class="actions">';

							echo anchor( 'admin/settings/blog/edit/' . $blog->id, lang( 'action_edit' ), 'class="awesome small"' );
							echo anchor( 'admin/settings/blog/delete/' . $blog->id, lang( 'action_delete' ), 'class="awesome small red confirm" data-body="Deleting a blog will delete all associated posts, categories and tags. This action cannot be undone." data-title="Are you sure?"' );

						echo '</td>';
					echo '</tr>';

				endforeach;

			else :

				echo '<tr>';
					echo '<td class="no-data" colspan="2">';
						echo 'No blogs found';
					echo '</td>';
				echo '</tr>';

			endif;

		?>
		</tbody>
	</table>
</div>