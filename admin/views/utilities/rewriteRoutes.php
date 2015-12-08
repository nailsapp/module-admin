<div class="group-utilities rewrite-routes">
    <p>
        This site uses dynamic routing to configure various aspects of its
        functionality; recreate the routes file using the button below.
    </p>
    <hr />
    <?php

    echo form_open();
    echo '<p>' . form_submit( 'go', 'Rewrite Routes', 'class="btn btn-success"' ) . '</p>';
    echo form_close();

    ?>
</div>