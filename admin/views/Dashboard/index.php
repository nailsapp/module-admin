<?php

use Nails\Admin\Interfaces\Dashboard\Alert;
use Nails\Config;

/**
 * @var array   $adminControllers
 * @var Alert[] $aAlerts
 * @var string  $sPhrase
 */
?>
<div class="group-dashboard">
    <?php

    if ($adminControllers) {

        if (!empty($aAlerts)) {
            foreach ($aAlerts as $oAlert) {
                ?>
                <div class="alert alert-<?=$oAlert->getSeverity()?>">
                    <span class="alert__close">&times;</span>
                    <?php

                    $sTitle = $oAlert->getTitle();
                    $sBody  = $oAlert->getBody();

                    echo $sTitle ? '<strong>' . $sTitle . '</strong>' : '';
                    echo $sBody ? '<p>' . $sBody . '</p>' : '';

                    ?>
                </div>
                <?php
            }
        }

        ?>
        <p>
            Welcome to <?=Config::get('APP_NAME')?>'s Administration pages. From here you can control aspects of the site.
        </p>
        <p>
            Get started by choosing an option from the left.
        </p>
        <div class="welcome">
            <p class="icon">
                <span class="fa fa-smile-o">
            </p>
            <p class="text">
                <?=$sPhrase?>
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
            an email to <?=mailto(Config::get('APP_DEVELOPER_EMAIL'))?>.
        </p>
        <?php
    }

    ?>
</div>
