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
        echo !empty($page->module->name) ? $page->module->name . ' - ' : null;
        echo !empty($page->title) ? $page->title . ' - ' : null;
        echo APP_NAME;

    ?></title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <!--    NAILS JS GLOBALS    -->
    <script style="text/javascript">
        /* jshint ignore:start */
        window.ENVIRONMENT      = '<?=\Nails\Environment::get()?>';
        window.SITE_URL         = '<?=site_url('', isPageSecure())?>';
        window.NAILS            = {};
        window.NAILS.URL        = '<?=NAILS_ASSETS_URL?>';
        window.NAILS.LANG       = {};
        window.NAILS.USER       = {};
        window.NAILS.USER.ID    = <?=activeUser('id')?>;
        window.NAILS.USER.FNAME = '<?=addslashes(activeUser('first_name'))?>';
        window.NAILS.USER.LNAME = '<?=addslashes(activeUser('last_name'))?>';
        window.NAILS.USER.EMAIL = '<?=addslashes(activeUser('email'))?>';
        /* jshint ignore:end */
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

    $brandColorPrimary   = appSetting('primary_colour', 'admin')   ? appSetting('primary_colour', 'admin')   : '#171D20';
    $brandColorSecondary = appSetting('secondary_colour', 'admin') ? appSetting('secondary_colour', 'admin') : '#515557';
    $brandColorHighlight = appSetting('highlight_colour', 'admin') ? appSetting('highlight_colour', 'admin') : '#F09634';

    ?>
    <style type="text/css">

        .admin-branding-text-primary {
            color: <?=$brandColorPrimary?>;
        }
        .admin-branding-background-primary {
            background: <?=$brandColorPrimary?>;
        }
        .admin-branding-text-secondary {
            color: <?=$brandColorSecondary?>;
        }
        .admin-branding-background-secondary {
            background: <?=$brandColorSecondary?>;
        }
        .admin-branding-text-highlight {
            color: <?=$brandColorHighlight?>;
        }
        .admin-branding-background-highlight {
            background: <?=$brandColorHighlight?>;
        }
        table thead tr th {
            background-color: <?=$brandColorPrimary?>;
        }

    </style>
</head>
<body class="<?=!$adminControllers ? 'no-modules' : ''?>">
    <div class="header">
        <div class="app-name">
            <b class="fa fa-bars " id="mobileMenuBurger"></b>
            <a href="<?=site_url('admin')?>">
                <span class="app-name admin-branding-text-primary">
                    <?=APP_NAME?>
                </span>
            </a>
        </div>
        <div class="user-shortcuts">
            <div class="shortcut loggedin-as" rel="tipsy" title="Logged in as <?=activeUser('first_name,last_name')?>">
            <?php

            $url  = 'admin/auth/accounts/edit/' . activeUser('id');
            $attr = 'class="fancybox admin-branding-text-primary" data-fancybox-type="iframe"';
            $img  = img(
                array(
                    'src' => cdnavatar(activeUser('profile_img'), 30, 30),
                    'class' => 'avatar'
                )
            );

            echo anchor(
                $url,
                '<span class="name">' . activeUser('first_name,last_name') . '</span>' . $img,
                $attr
            );

            ?>
            </div>
            <div class="shortcut to-frontend" rel="tipsy" title="Switch to front end">
                <?=anchor('', '<span class="fa fa-reply-all"></span>', 'class="admin-branding-text-primary"')?>
            </div>
            <?php

            if (wasAdmin()) {

                $adminRecovery = getAdminRecoveryData();

                ?>
                <div class="shortcut admin-recovery" rel="tipsy" title="Log back in as <?=$adminRecovery->name?>">
                    <?php

                    echo anchor(
                        $adminRecovery->loginUrl,
                        '<span class="fa fa-sign-out"></span>',
                        'class="admin-branding-text-primary"'
                    );

                    ?>
                </div>
                <?php
            }

            ?>
            <div class="shortcut logout" rel="tipsy" title="Log out">
            <?php

            echo anchor(
                'auth/logout',
                '<span class="fa fa-power-off"></span>',
                'class="admin-branding-text-primary"'
            );

            ?>
            </div>
        </div>
        <div id="mobileMenu">
            <ul class="menuItems">
            <?php

            foreach ($adminControllersNav as $module) {

                ?>
                <li>
                    <span class="moduleName">
                        <?=$module->label?>
                    </span>
                    <ul>
                        <?php

                        foreach ($module->actions as $url => $methodDetails) {

                            echo '<li>';
                            echo anchor(
                                'admin/' . $url,
                                $methodDetails->label
                            );
                            echo '</li>';
                        }

                        ?>
                    </ul>
                </li>
                <?php
            }

            ?>
            </ul>
        </div>
    </div>
    <div class="sidebar">
        <div class="nav-search admin-branding-background-secondary">
            <input type="search" placeholder="Type to search menu" />
        </div>
        <ul class="modules">
        <?php

        foreach ($adminControllersNav as $module) {

            $sortableClass = $module->sortable ? 'sortable' : 'not-sortable';
            $openState     = $module->open ? 'open' : 'closed';

            ?>
            <li class="module admin-branding-background-primary <?=$sortableClass?>" data-grouping="<?=md5($module->label)?>" data-initial-state="<?=$openState?>">
                <div class="box <?=$openState?>">
                    <h2>
                        <div class="icon admin-branding-text-highlight">
                        <?php

                        //  Sorting handle
                        if ($module->sortable) {
                            echo '<span class="handle admin-branding-background-primary fa fa-navicon"></span>';
                        }

                        //  Icon
                        if (empty($module->icon)) {

                            $icon = 'fa-cog';

                        } else {

                            //  Check if any have been listed as !important
                            $importantIcons = preg_grep('/^(.*)!important$/', $module->icon);

                            if (!empty($importantIcons)) {

                                $icon = reset($importantIcons);
                                $icon = trim(rtrim($icon, '!important'));

                            } else {

                                //  No !important icons, use the most popular
                                $icons = array_count_values($module->icon);
                                $icons = array_keys($icons);
                                $icon  = reset($icons);
                            }
                        }
                        echo  '<b class="fa fa-fw ' . $icon . '"></b>';

                        ?>
                        </div>
                        <span class="module-name">
                            <?=$module->label?>
                        </span>
                        <a href="#" class="toggle">
                            <span class="toggler">
                                <span class="close">
                                    <b class="fa fa-minus"></b>
                                </span>
                                <span class="open">
                                    <b class="fa fa-plus"></b>
                                </span>
                            </span>
                        </a>
                    </h2>
                    <div class="box-container">
                        <ul>
                        <?php

                        foreach ($module->actions as $url => $methodDetails) {

                            ?>
                            <li>
                                <a href="<?=site_url('admin/' . $url)?>">
                                    <?php

                                    echo $methodDetails->label;

                                    if (!empty($methodDetails->alerts)) {

                                        foreach ($methodDetails->alerts as $alert) {

                                            //  Skip empty alerts
                                            $sValue = $alert->getValue();
                                            if (empty($sValue)) {
                                                continue;
                                            }

                                            $sLabel    = $alert->getLabel() ?: '';
                                            $sTipsy    = $sLabel ? 'rel="tipsy-right"' : '';
                                            $sSeverity = $alert->getSeverity();

                                            echo '<span class="indicator ' . $sSeverity . '" ' . $sTipsy . ' title="' . $sLabel . '">';
                                                echo $sValue;
                                            echo '</span>';
                                        }
                                    }

                                    ?>
                                </a>
                            </li>
                            <?php
                        }

                        ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php
        }

        ?>
        </ul>
        <div class="text-center" id="admin-nav-reset-buttons">
            <a href="#" data-action="reset">Reset Nav</a>
            <a href="#" data-action="open">Open All</a>
            <a href="#" data-action="close">Close All</a>
        </div>
        <div class="no-modules">
            <p class="alert alert-danger">
                <strong>No modules available.</strong>
                </br>
                This is a configuration error and should be reported to the app developers.
            </p>
        </div>
    </div>
    <div class="content">
        <div class="content_inner">
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

                ?>
                <div class="page-title">
                    <h1>
                        <?php

                        echo !empty($pageTitle) ? $pageTitle : '';

                        if (!empty($headerButtons)) {

                            echo '<span class="header-buttons">';
                            foreach ($headerButtons as $button) {

                                $sConfirmClass = $button['confirmTitle'] || $button['confirmBody'] ? ' confirm' : '';

                                $attr   = array();
                                $attr[] = 'class="btn btn-xs btn-' . $button['context'] . $sConfirmClass . '"';
                                $attr[] = $button['confirmTitle'] ? 'data-title="' . $button['confirmTitle'] . '"' : '';
                                $attr[] = $button['confirmBody'] ? 'data-body="' . $button['confirmBody'] . '"' : '';

                                $attr = array_filter($attr);

                                if ($button['context'] === 'danger') {
                                    $button['label'] = '<i class="fa fa-exclamation-triangle"></i>' . $button['label'];
                                }

                                echo anchor(
                                    $button['url'],
                                    $button['label'],
                                    implode(' ', $attr)
                                ) . ' ';
                            }
                            echo '</span>';
                        }

                        ?>
                    </h1>
                </div>
                <?php
            }

            if (!empty($error)) {

                ?>
                <div class="alert alert-danger">
                    <p>
                        <strong>
                            <b class="alert-icon fa fa-times-circle"></b>
                            Sorry, something went wrong.
                        </strong>
                    </p>
                    <p><?=$error?></p>
                </div>
                <?php
            }

            if (!empty($negative)) {

                ?>
                <div class="alert alert-danger">
                    <p><?=$negative?></p>
                </div>
                <?php
            }

            if (!empty($success)) {

                ?>
                <div class="alert alert-success">
                    <p>
                        <strong>
                            <b class="alert-icon fa fa-check-circle"></b>
                            Success!
                        </strong>
                    </p>
                    <p><?=$success?></p>
                </div>
                <?php
            }

            if (!empty($positive)) {

                ?>
                <div class="alert alert-success">
                    <p><?=$positive?></p>
                </div>
                <?php
            }

            if (!empty($info)) {

                ?>
                <div class="alert alert-info">
                    <p><?=$info?></p>
                </div>
                <?php
            }

            if (!empty($warning)) {

                ?>
                <div class="alert alert-warning">
                    <p><?=$warning?></p>
                </div>
                <?php
            }

            if (!empty($message)) {

                ?>
                <div class="alert alert-warning">
                    <p><?=$message?></p>
                </div>
                <?php
            }

            if (!empty($notice)) {

                ?>
                <div class="alert alert-info">
                    <p><?=$notice?></p>
                </div>
                <?php
            }
