<!DOCTYPE html>
<!--[if lt IE 7 ]>
<html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>
<html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>
<html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <meta charset="UTF-8"/>
    <title>
        <?php

        echo lang('admin_word_short') . ' - ';
        echo isset($page->module->name) ? $page->module->name . ' - ' : null;
        echo isset($page->title) ? $page->title . ' - ' : null;
        echo APP_NAME;

        ?></title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <!--    NAILS JS GLOBALS    -->
    <script style="text/javascript">
        window.ENVIRONMENT = '<?=\Nails\Environment::get()?>';
        window.SITE_URL = '<?=siteUrl('', \Nails\Functions::isPageSecure())?>';
        window.NAILS = {};
        window.NAILS.URL = '<?=NAILS_ASSETS_URL?>';
        window.NAILS.LANG = {};
        window.NAILS.USER = {};
        window.NAILS.USER.ID = <?=activeUser('id')?>;
        window.NAILS.USER.FNAME = '<?=addslashes(activeUser('first_name'))?>';
        window.NAILS.USER.LNAME = '<?=addslashes(activeUser('last_name'))?>';
        window.NAILS.USER.EMAIL = '<?=addslashes(activeUser('email'))?>';
    </script>
    <noscript>
        <style type="text/css">

            .js-only {
                display: none;
            }

        </style>
    </noscript>
    <!--    ASSETS  -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700" rel="stylesheet" type="text/css">
    <?php

    $oAsset = \Nails\Factory::service('Asset');
    $oAsset->output('CSS');
    $oAsset->output('CSS-INLINE');
    $oAsset->output('JS-INLINE-HEADER');

    ?>
    <link rel="stylesheet" type="text/css" media="print" href="<?=NAILS_ASSETS_URL . 'css/nails.admin.print.css'?>"/>
    <?php

    $_primary   = appSetting('primary_colour', 'admin') ? appSetting('primary_colour', 'admin') : '#171D20';
    $_secondary = appSetting('secondary_colour', 'admin') ? appSetting('secondary_colour', 'admin') : '#515557';
    $_highlight = appSetting('highlight_colour', 'admin') ? appSetting('highlight_colour', 'admin') : '#F09634';

    ?>
    <style type="text/css">

        .admin-branding-text-primary {
            color: <?=$_primary?>;
        }

        .admin-branding-background-primary {
            background: <?=$_primary?>;
        }

        .admin-branding-text-secondary {
            color: <?=$_secondary?>;
        }

        .admin-branding-background-secondary {
            background: <?=$_secondary?>;
        }

        .admin-branding-text-highlight {
            color: <?=$_highlight?>;
        }

        .admin-branding-background-highlight {
            background: <?=$_highlight?>;
        }

        table thead tr th {
            background-color: <?=$_primary?>;
        }

    </style>
</head>
<body class="blank">
<?php

\Nails\Factory::service('View')
    ->load('admin/_components/structure/page-header');
