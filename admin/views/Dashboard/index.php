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
        <div id="dashboard-widgets"></div>
        <div class="dashboard-widgets">
            <?php
            foreach ($aWidgets as $oWidget) {

                //  @todo (Pablo 25/02/2021) - Add support for ordering
                //  @todo (Pablo 25/02/2021) - Add support for resizing
                //  @todo (Pablo 25/02/2021) - Add support for configuring

                ?>
                <div class="dashboard-widget dashboard-widget--<?=$oWidget->getSize()?>">
                    <fieldset>
                        <legend class="dashboard-widget__label">
                            <?=$oWidget->getTitle()?>
                        </legend>
                        <div class="dashboard-widget__body <?=$oWidget->padBody() ? 'dashboard-widget__body--padded' : ''?>">
                            <?=$oWidget->getBody()?>
                        </div>
                        <?php
                        $sConfig = $oWidget->getConfig();
                        if (!empty($sConfig)) {
                            ?>
                            <div class="dashboard-widget__config">
                                <?=$oWidget->getConfig()?>
                            </div>
                            <?php
                        }
                        ?>
                    </fieldset>
                </div>
                <?php
            }
            ?>
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
