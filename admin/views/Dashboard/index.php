<?php

use Nails\Admin\Interfaces\Dashboard\Alert;
use Nails\Admin\Interfaces\Dashboard\Widget;
use Nails\Config;

/**
 * @var array    $adminControllers
 * @var Alert[]  $aAlerts
 * @var Widget[] $aWidgets
 */
?>
<div class="group-dashboard">
    <?php

    if ($adminControllers) {

        if (!empty($aAlerts)) {
            foreach ($aAlerts as $oAlert) {
                ?>
                <div class="alert alert-<?=$oAlert->getSeverity()?> alert--dashboard">
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

        echo '<div id="dashboard-widgets"></div>';

    } else {
        ?>
        <p class="alert alert-warning">
            <strong>How very strange...</strong>
            <br/>
            You don't have permission to access any of the administration features for this site. This is most
            likely a misconfiguration of your account; please see the site administrator for assistance.
        </p>
        <?php
    }

    ?>
</div>
