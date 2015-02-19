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
        echo !empty($page->module->name) ? $page->module->name . ' - ' : NULL;
        echo !empty($page->title) ? $page->title . ' - ' : NULL;
        echo APP_NAME;

    ?></title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <!--    NAILS JS GLOBALS    -->
    <script style="text/javascript">
        window.ENVIRONMENT      = '<?=strtoupper(ENVIRONMENT)?>';
        window.SITE_URL         = '<?=site_url('', isPageSecure())?>';
        window.NAILS            = {};
        window.NAILS.URL        = '<?=NAILS_ASSETS_URL?>';
        window.NAILS.LANG       = {};
        window.NAILS.USER       = {};
        window.NAILS.USER.ID    = <?=activeUser('id')?>;
        window.NAILS.USER.FNAME = '<?=activeUser('first_name')?>';
        window.NAILS.USER.LNAME = '<?=activeUser('last_name')?>';
        window.NAILS.USER.EMAIL = '<?=activeUser('email')?>';
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

        echo $this->asset->output('CSS');
        echo $this->asset->output('CSS-INLINE');

    ?>
    <link rel="stylesheet" type="text/css" media="print" href="<?=NAILS_ASSETS_URL . 'css/nails.admin.print.css'?>" />
    <?php

        $brandColorPrimary   = app_setting('primary_colour', 'admin')   ? app_setting('primary_colour', 'admin')   : '#171D20';
        $brandColorSecondary = app_setting('secondary_colour', 'admin') ? app_setting('secondary_colour', 'admin') : '#515557';
        $brandColorHighlight = app_setting('highlight_colour', 'admin') ? app_setting('highlight_colour', 'admin') : '#F09634';

    ?>
    <style type="text/css">

        .admin-branding-text-primary
        {
            color: <?=$brandColorPrimary?>;
        }
        .admin-branding-background-primary
        {
            background: <?=$brandColorPrimary?>;
        }

        .admin-branding-text-secondary
        {
            color: <?=$brandColorSecondary?>;
        }
        .admin-branding-background-secondary
        {
            background: <?=$brandColorSecondary?>;
        }

        .admin-branding-text-highlight
        {
            color: <?=$brandColorHighlight?>;
        }
        .admin-branding-background-highlight
        {
            background: <?=$brandColorHighlight?>;
        }

        table thead tr th
        {
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

                if (activeUser('profile_img')) {

                    $img = img(array('src' => cdn_thumb(activeUser('profile_img'), 30, 30), 'class' => 'avatar'));

                } else {

                    $img = img(array('src' => cdn_blank_avatar(30, 30), 'class' => 'avatar'));
                }

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

                if ($this->user_model->wasAdmin()) {

                    $adminRecovery = $this->user_model->getAdminRecoveryData();

                    echo '<div class="shortcut admin-recovery" rel="tipsy" title="Log back in as ' . $adminRecovery->name . '">';
                        echo anchor(
                            $adminRecovery->loginUrl,
                            '<span class="fa fa-sign-out"></span>',
                            'class="admin-branding-text-primary"'
                        );
                    echo '</div>';
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

                    echo '<li>';
                        echo '<span class="moduleName">';
                            echo $module->label;
                        echo '</span>';

                        echo '<ul>';
                        foreach ($module->actions as $url => $methodDetails) {

                            echo '<li>';
                                echo anchor(
                                    'admin/' . $url,
                                    $methodDetails->label
                                );
                            echo '</li>';
                        }
                        echo '</ul>';

                    echo '</li>';
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

                                    echo '<li>';
                                        echo '<a href="' . site_url('admin/' . $url) . '">';
                                            echo $methodDetails->label;

                                            if (!empty($methodDetails->alerts)) {

                                                foreach ($methodDetails->alerts as $alert) {

                                                    //  Skip empty alerts
                                                    if (empty($alert->value)) {

                                                        continue;
                                                    }

                                                    $label    = $alert->label ? $alert->label : '';
                                                    $tipsy    = $alert->label ? 'rel="tipsy-right"' : '';
                                                    $severity = $alert->severity;

                                                    echo '<span class="indicator ' . $severity . '" ' . $tipsy . ' title="' . $label . '">';
                                                        echo $alert->value;
                                                    echo '</span>';
                                                }
                                            }

                                        echo '</a>';
                                    echo '</li>';
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
        <p class="text-center" id="admin-nav-reset">
            <a href="#">Reset Nav</a>
        </p>
        <div class="no-modules">
            <p class="system-alert error">
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

                $headerButtons = \Nails\Admin\Helper::getHeaderButtons();

                if (!empty($pageTitle) || !empty($headerButtons)) {

                    echo '<div class="page-title">';
                        echo '<h1>';
                            echo !empty($pageTitle) ? $pageTitle : '';

                            if (!empty($headerButtons)) {

                                echo '<span class="headerButtons">';
                                foreach ($headerButtons as $button) {

                                    $attr   = array();
                                    $attr[] = 'class="awesome small ' . $button['color'] . '"';
                                    $attr[] = $button['confirmTitle'] ? 'data-title="' . $button['confirmTitle'] . '"' : '';
                                    $attr[] = $button['confirmBody'] ? 'data-body="' . $button['confirmBody'] . '"' : '';

                                    $attr = array_filter($attr);

                                    echo anchor(
                                        $button['url'],
                                        $button['label'],
                                        implode(' ', $attr)
                                    );
                                }
                                echo '</span>';
                            }
                        echo '</h1>';
                    echo '</div>';
                }

                if (!empty($error)) {

                    echo '<div class="system-alert error">';
                        echo '<p><strong>';
                            echo '<b class="alert-icon fa fa-times-circle"></b>';
                            echo 'Sorry, something went wrong.';
                        echo '</strong></p>';
                        echo '<p>' . $error . '</p>';
                    echo '</div>';
                }

                if (!empty($success)) {

                    echo '<div class="system-alert success">';
                        echo '<p><strong>';
                            echo '<b class="alert-icon fa fa-check-circle"></b>';
                            echo 'Success!';
                        echo '</strong></p>';
                        echo '<p>' . $success . '</p>';
                    echo '</div>';
                }

                if (!empty($message)) {

                    echo '<div class="system-alert message">';
                        echo '<p>' . $message . '</p>';
                    echo '</div>';
                }

                if (!empty($notice)) {

                    echo '<div class="system-alert notice">';
                        echo '<p>' . $notice . '</p>';
                    echo '</div>';
                }
