<?php

//  Page title
if (!empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->module->name . ' &rsaquo; ' . $page->title;
} elseif (empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->title;
} elseif (!empty($page->module->name)) {
    $sPageTitle = $page->module->name;
}

$aHeaderButtons = adminHelper('getHeaderButtons');

if (!empty($sPageTitle) || !empty($aHeaderButtons)) {
    ?>
    <div class="page-title">
        <h1>
            <?php

            echo !empty($sPageTitle) ? $sPageTitle : '';

            if (!empty($aHeaderButtons)) {

                echo '<span class="header-buttons">';
                foreach ($aHeaderButtons as $aButton) {

                    $aClasses = array_filter([
                        'btn',
                        'btn-xs',
                        'btn-' . $aButton['context'],
                        $aButton['confirmTitle'] || $aButton['confirmBody'] ? 'confirm' : '',
                        is_array($aButton['url']) ? 'dropdown-toggle' : '',
                    ]);
                    $aAttr    = array_filter([
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

            ?>
        </h1>
    </div>
    <?php
}

if (!empty($error)) {
    ?>
    <div class="alert alert-danger">
        <span class="alert__close">&times;</span>
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
        <span class="alert__close">&times;</span>
        <p><?=$negative?></p>
    </div>
    <?php
}

if (!empty($success)) {
    ?>
    <div class="alert alert-success">
        <span class="alert__close">&times;</span>
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
        <span class="alert__close">&times;</span>
        <p><?=$positive?></p>
    </div>
    <?php
}

if (!empty($info)) {
    ?>
    <div class="alert alert-info">
        <span class="alert__close">&times;</span>
        <p><?=$info?></p>
    </div>
    <?php
}

if (!empty($warning)) {
    ?>
    <div class="alert alert-warning">
        <span class="alert__close">&times;</span>
        <p><?=$warning?></p>
    </div>
    <?php
}

if (!empty($message)) {
    ?>
    <div class="alert alert-warning">
        <span class="alert__close">&times;</span>
        <p><?=$message?></p>
    </div>
    <?php
}

if (!empty($notice)) {
    ?>
    <div class="alert alert-info">
        <span class="alert__close">&times;</span>
        <p><?=$notice?></p>
    </div>
    <?php
}
