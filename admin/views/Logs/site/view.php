<div class="group-logs view sitelog">
	<ol>
	<?php

		foreach ($logs as $line) {

			echo '<li>';
				echo $line;
			echo '</li>';
		}

	?>
	</ol>
</div>