<div class="group-blog manage categories overview">
	<?php

		if ( $is_fancybox ) :

			echo '<h1>' . $page->title . '</h1>';
			$_class = 'system-alert';

		else :

			$_class = '';

		endif;

	?>
	<p class="<?=$_class?>">
		Use categories to group broad post topics together. For example, a category might be 'Music', or 'Travel'.
		<?php

			if ( app_setting( 'tags_enabled', 'blog' ) ) :

				echo 'For specific details (e.g New Year ' . date( 'Y') . ') consider using a ' . anchor( 'admin/blog/manage/tag' . $is_fancybox, 'tag' ) . '.';

			endif;

		?>
	</p>
	<?=$is_fancybox ? '' : '<hr />'?>
	<ul class="tabs disabled">
		<li class="tab active">
			<?=anchor( 'admin/blog/manage/category' . $is_fancybox, 'Overview' )?>
		</li>
		<li class="tab">
			<?=anchor( 'admin/blog/manage/category/create' . $is_fancybox, 'Create Category' )?>
		</li>
	</ul>
	<section class="tabs pages">
		<div class="tab page active">
			<table>
				<thead>
					<tr>
						<th class="label">Label</th>
						<th class="count">Posts</th>
						<th class="modified">Modified</th>
						<th class="actions">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php

					if ( $categories ) :

						foreach( $categories AS $category ) :

							echo '<tr>';
								echo '<td class="label">';
									echo $category->label;
									echo $category->description ? '<small>' . character_limiter( strip_tags( $category->description ), 225 ) . '</small>' : '';
								echo '</td>';
								echo '<td class="count">';
									echo isset( $category->post_count ) ? $category->post_count : '&mdash;';
								echo '</td>';
								echo $this->load->view( '_utilities/table-cell-datetime', array( 'datetime' => $category->modified ), TRUE );
								echo '<td class="actions">';

									if ( user_has_permission( 'admin.blog.category_edit' ) ) :

										echo anchor( 'admin/blog/manage/category/edit/' . $category->id . $is_fancybox, lang( 'action_edit' ), 'class="awesome small"' );

									endif;

									if ( user_has_permission( 'admin.blog.category_delete' ) ) :

										echo anchor( 'admin/blog/manage/category/delete/' . $category->id . $is_fancybox, lang( 'action_delete' ), 'class="awesome small red confirm" data-title="Are you sure?" data-body="This action cannot be undone."' );

									endif;

								echo '</td>';
							echo '</tr>';

						endforeach;

					else :

						echo '<tr>';
							echo '<td colspan="3" class="no-data">';
								echo 'No Categories, add one!';
							echo '</td>';
						echo '</tr>';

					endif;

				?>
				</tbody>
			</table>
		</div>
	</section>
</div>
<?php

	$this->load->view( 'admin/blog/manage/category/_footer' );