<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
    <meta charset="UTF-8" />
    <title>
    <?php

        echo lang('admin_word_short') . ' - ';
        echo isset($page->module->name) ? $page->module->name . ' - ' : NULL;
        echo isset($page->title) ? $page->title . ' - ' : NULL;
        echo APP_NAME;

    ?></title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <!--    NAILS JS GLOBALS    -->
    <script style="text/javascript">
        window.ENVIRONMENT      = '<?=\Nails\Environment::get()?>';
        window.SITE_URL         = '<?=siteUrl('', \Nails\Functions::isPageSecure())?>';
        window.NAILS            = {};
        window.NAILS.URL        = '<?=NAILS_ASSETS_URL?>';
        window.NAILS.LANG       = {};
        window.NAILS.USER       = {};
        window.NAILS.USER.ID    = <?=activeUser('id')?>;
        window.NAILS.USER.FNAME = '<?=addslashes(activeUser('first_name'))?>';
        window.NAILS.USER.LNAME = '<?=addslashes(activeUser('last_name'))?>';
        window.NAILS.USER.EMAIL = '<?=addslashes(activeUser('email'))?>';
    </script>
    <noscript>
        <style type="text/css">

            .js-only
            {
                display:none;
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
    <link rel="stylesheet" type="text/css" media="print" href="<?=NAILS_ASSETS_URL . 'css/nails.admin.print.css'?>" />
    <?php

    $_primary   = appSetting('primary_colour', 'admin')   ? appSetting('primary_colour', 'admin')   : '#171D20';
    $_secondary = appSetting('secondary_colour', 'admin') ? appSetting('secondary_colour', 'admin') : '#515557';
    $_highlight = appSetting('highlight_colour', 'admin') ? appSetting('highlight_colour', 'admin') : '#F09634';

    ?>
    <style type="text/css">

        .admin-branding-text-primary
        {
            color: <?=$_primary?>;
        }
        .admin-branding-background-primary
        {
            background: <?=$_primary?>;
        }

        .admin-branding-text-secondary
        {
            color: <?=$_secondary?>;
        }
        .admin-branding-background-secondary
        {
            background: <?=$_secondary?>;
        }

        .admin-branding-text-highlight
        {
            color: <?=$_highlight?>;
        }
        .admin-branding-background-highlight
        {
            background: <?=$_highlight?>;
        }

        table thead tr th
        {
            background-color : <?=$_primary?>;
        }

    </style>
</head>
<body class="blank">
<?php

    //  Page title
    if (!empty($page->module->name) && !empty($page->title)) {

        $pageTitle = $page->module->name . ' &rsaquo; ' . $page->title;

    } elseif (empty($page->module->name) && !empty($page->title)) {

        $pageTitle = $page->title;

    } elseif (!empty($page->module->name)) {

        $pageTitle = $page->module->name;
    }

    $headerButtons = adminHelper('getHeaderButtons');

    if (!empty($pageTitle) || !empty($headerButtons)) {

        echo '<div class="page-title">';
            echo '<h1>';
                echo !empty($pageTitle) ? $pageTitle : '';

                if (!empty($headerButtons)) {

                    echo '<span class="header-buttons">';
                    foreach ($headerButtons as $aButton) {

                        $aClasses = array_filter([
                            'btn',
                            'btn-xs',
                            'btn-' . $aButton['context'],
                            $aButton['confirmTitle'] || $aButton['confirmBody'] ? 'confirm' : '',
                            is_array($aButton['url']) ? 'dropdown-toggle' : '',
                        ]);
                        $aAttr  = array_filter([
                            'class="' . implode(' ', $aClasses) . '"',
                            $aButton['confirmTitle'] ? 'data-title="' . $aButton['confirmTitle'] . '"' : '',
                            $aButton['confirmBody'] ? 'data-body="' . $aButton['confirmBody'] . '"' : '',
                        ]);

                        if (is_array($aButton['url'])) {

                            ?>
                            <div class="btn-group">
                                <button type="button" <?=implode(' ', $aAttr)?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?=$aButton['label']?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php
                                    foreach ($aButton['url'] as $sLabel => $sItemUrl) {
                                        ?>
                                        <li>
                                            <a href="<?=siteUrl($sItemUrl)?>">
                                                <?=$sLabel?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php

                        } else {

                            if ($aButton['context'] === 'danger') {
                                $aButton['label'] = '<i class="fa fa-exclamation-triangle"></i>' . $aButton['label'];
                            }

                            echo anchor(
                                    $aButton['url'],
                                    $aButton['label'],
                                    implode(' ', $aAttr)
                                ) . ' ';
                        }
                    }
                    echo '</span>';
                }
            echo '</h1>';
        echo '</div>';
    }

    if (!empty($error)) {

        echo '<div class="alert alert-danger">';
            echo '<p>';
                echo '<strong>';
                    echo '<b class="alert-icon fa fa-times-circle"></b>';
                    echo 'Sorry, something went wrong.';
                echo '</strong>';
            echo '</p>';
            echo '<p>' . $error . '</p>';
        echo '</div>';
    }

    if (!empty($negative)) {

        echo '<div class="alert alert-danger">';
            echo '<p>' . $negative . '</p>';
        echo '</div>';
    }

    if (!empty($success)) {

        echo '<div class="alert alert-success">';
            echo '<p>';
                echo '<strong>';
                    echo '<b class="alert-icon fa fa-check-circle"></b>';
                    echo 'Success!';
                echo '</strong>';
            echo '</p>';
            echo '<p>' . $success . '</p>';
        echo '</div>';
    }

    if (!empty($positive)) {

        echo '<div class="alert alert-success">';
            echo '<p>' . $positive . '</p>';
        echo '</div>';
    }

    if (!empty($message)) {

        echo '<div class="alert alert-warning">';
            echo '<p>' . $message . '</p>';
        echo '</div>';
    }

    if (!empty($notice)) {

        echo '<div class="alert alert-info">';
            echo '<p>' . $notice . '</p>';
        echo '</div>';
    }
