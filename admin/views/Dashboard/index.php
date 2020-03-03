<div class="group-dashboard">
    <?php

    if ($adminControllers) {

        ?>
       <p>
            Welcome to <?=\Nails\Config::get('APP_NAME')?>'s Administration pages. From here you can control aspects of
            the site.
        </p>
        <p>
            Get started by choosing an option from the left.
        </p>
        <div class="welcome">
            <p class="icon">
                <span class="fa fa-smile-o">
            </p>
            <p class="text">
                <?=$phrase?>
            </p>
        </div>
        <?php

    } else {

        ?>
        <p class="alert alert-warning">
            <strong>How very strange...</strong>
            <br />
            You don't have permission to access any of the administration features for this site. This is most
            likely a misconfiguration of your account; please see the site administrator for assistance, or send
            an email to <a href="mailto:<?=\Nails\Config::get('APP_DEVELOPER_EMAIL')?>"><?=\Nails\Config::get('APP_DEVELOPER_EMAIL')?></a>.
        </p>
        <?php
    }

    ?>
</div>