<?php
$oAsset = \Nails\Factory::service('Asset');
$oAsset->clear();
$oAsset->load('nails.min.css', \Nails\Common\Constants::MODULE_SLUG);
$oView = \Nails\Factory::service('View');
$oView->load('structure/header/blank');
?>
    <div class="nails-auth login u-center-screen">
        <div class="panel">
            <h1 class="panel__header text-center">
                404 Page Not Found
            </h1>
            <div class="panel__body text-center">
                The page you requested was not found, or your request was invalid.
            </div>
        </div>
    </div>
<?php
$oView->load('structure/footer/blank');
