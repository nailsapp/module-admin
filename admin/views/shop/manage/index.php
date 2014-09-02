<div class="group-shop manage index">
	<p class="<?=$is_fancybox ? 'system-alert' : ''?>">
		Choose which Manager you'd like to utilise.
	</p>
	<?=$is_fancybox ? '' : '<hr />'?>
	<ul>
	<?php

		//	Doing this in PHP so there's no white space (affects layout) but still easily readable
		echo '<li>' . anchor( 'admin/shop/manage/attribute' . $is_fancybox, 'Attributes' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/brand' . $is_fancybox, 'Brands' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/category' . $is_fancybox, 'Categories' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/collection' . $is_fancybox, 'Collections' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/range' . $is_fancybox, 'Ranges' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/tag' . $is_fancybox, 'Tags' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/tax_rate' . $is_fancybox, 'Tax Rates' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/product_type' . $is_fancybox, 'Product Types' ) . '</li>';
		echo '<li>' . anchor( 'admin/shop/manage/product_type_meta' . $is_fancybox, 'Product Type Meta' ) . '</li>';

	?>
	</ul>
</div>