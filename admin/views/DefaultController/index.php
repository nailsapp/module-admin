<?php

use Nails\Admin\Helper;
use Nails\Admin\Controller\DefaultController;

$oMustache = \Nails\Factory::service('Mustache');

?>
<div class="group-defaultcontroller browse">
    <p>
        Manage <?=$CONFIG['TITLE_PLURAL']?>.
    </p>
    <?=adminHelper('loadSearch', $search)?>
    <?=adminHelper('loadPagination', $pagination)?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <?php
                    //  @todo (Pablo - 2018-11-05) - Tidy this nesting up a little
                    foreach ($CONFIG['INDEX_FIELDS'] as $sLabel => $sProperty) {

                        $sNormalisedLabel = strtolower($sLabel);
                        $sNormalisedLabel = preg_replace('/[^a-z0-9 \-_]/', '', $sNormalisedLabel);
                        $sNormalisedLabel = str_replace([' ', '_'], '-', $sNormalisedLabel);

                        $bIsBoolCell = DefaultController::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_BOOL_FIELDS']);

                        $bIsUserCell = DefaultController::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_USER_FIELDS']);

                        $bIsCenteredCell = DefaultController::inArray([
                            $sProperty,
                            $sNormalisedLabel,
                        ], $CONFIG['INDEX_CENTERED_FIELDS']);

                        $aAttr = [
                            'class' => ['field', 'field--' . $sNormalisedLabel],
                        ];

                        if ($bIsBoolCell) {
                            $aAttr['width']   = 150;
                            $aAttr['class'][] = 'boolean';
                        } elseif ($bIsUserCell) {
                            $aAttr['width'] = 300;
                        } elseif ($bIsCenteredCell) {
                            $aAttr['class'][] = 'text-center';
                        }

                        $sAttr = '';
                        foreach ($aAttr as $sKey => $mValue) {
                            if (is_array($mValue)) {
                                $sAttr .= ' ' . $sKey . '="' . implode(' ', $mValue) . '"';
                            } else {
                                $sAttr .= ' ' . $sKey . '="' . $mValue . '"';
                            }
                        }
                        ?>
                        <th <?=$sAttr?>>
                            <?=$sLabel?>
                        </th>
                        <?php
                    }
                    ?>
                    <th class="actions" style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($items)) {
                    foreach ($items as $oItem) {
                        ?>
                        <tr>
                            <?php

                            foreach ($CONFIG['INDEX_FIELDS'] as $sLabel => $sProperty) {

                                $sNormalisedLabel = strtolower($sLabel);
                                $sNormalisedLabel = preg_replace('/[^a-z0-9 \-_]/', '', $sNormalisedLabel);
                                $sNormalisedLabel = str_replace([' ', '_'], '-', $sNormalisedLabel);

                                $bIsBoolCell = DefaultController::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_BOOL_FIELDS']);

                                $bIsUserCell = DefaultController::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_USER_FIELDS']);

                                $bIsNumeric = DefaultController::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_NUMERIC_FIELDS']);

                                $bIsCenteredCell = DefaultController::inArray([
                                    $sProperty,
                                    $sNormalisedLabel,
                                ], $CONFIG['INDEX_CENTERED_FIELDS']);


                                if (is_callable($sProperty)) {

                                    $mValue   = $sProperty($oItem);
                                    $aClasses = [];

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue,
                                            trim('field field--' . $sNormalisedLabel . ' ' . implode(' ', $aClasses))
                                        );
                                    }

                                } elseif (property_exists($oItem, $sProperty)) {

                                    $mValue          = $oItem->{$sProperty};
                                    $aClasses        = [];
                                    $sCellAdditional = '';
                                    if (classUses($CONFIG['MODEL_INSTANCE'], '\Nails\Common\Traits\Model\Nestable')) {
                                        $aBreadcrumbs = json_decode($oItem->breadcrumbs);
                                        if (!empty($aBreadcrumbs)) {
                                            $aItems          = arrayExtractProperty($aBreadcrumbs, 'label');
                                            $sCellAdditional = '<small>' . implode(' &rsaquo; ', $aItems) . '</small>';
                                        }
                                    }

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue,
                                            trim('field field--' . $sProperty . ' ' . implode(' ', $aClasses)),
                                            $sCellAdditional
                                        );
                                    }

                                } elseif (strpos($sProperty, '.') !== false) {

                                    //  @todo (Pablo - 2018-08-08) - Handle arrays in expanded objects
                                    $aField     = explode('.', $sProperty);
                                    $aClasses   = [];
                                    $sProperty1 = getFromArray(0, $aField);
                                    $sProperty2 = getFromArray(1, $aField);

                                    if (property_exists($oItem, $sProperty1)) {
                                        if (!empty($oItem->{$sProperty1}) && property_exists($oItem->{$sProperty1}, $sProperty2)) {
                                            $mValue = $oItem->{$sProperty1}->{$sProperty2};
                                        } else {
                                            $mValue = '<span class="text-muted">&mdash;</span>';
                                        }
                                    } else {
                                        $mValue = '<span class="text-muted">&mdash;</span>';
                                    }

                                    if ($bIsNumeric && !$bIsUserCell) {
                                        $mValue = number_format($mValue);
                                    }

                                    if ($bIsCenteredCell) {
                                        $aClasses[] = 'text-center';
                                    }

                                    if ($bIsBoolCell) {
                                        echo Helper::loadBoolCell($mValue);
                                    } elseif ($bIsUserCell) {
                                        echo Helper::loadUserCell($mValue);
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $mValue,
                                            trim('field field--' . $sProperty . ' ' . implode(' ', $aClasses))
                                        );
                                    }

                                } else {
                                    ?>
                                    <td class="field field--<?=$sProperty?>">
                                        <span class="text-muted">&mdash;</span>
                                    </td>
                                    <?php
                                }
                            }

                            ?>
                            <td class="actions">
                                <?php

                                if ($CONFIG['CAN_VIEW'] && property_exists($oItem, 'url')) {
                                    echo anchor(
                                        $oItem->url,
                                        lang('action_view'),
                                        'class="btn btn-xs btn-default" target="_blank"'
                                    );
                                }

                                foreach ($CONFIG['INDEX_ROW_BUTTONS'] as $aButton) {
                                    $sUrl   = getFromArray('url', $aButton);
                                    $sLabel = getFromArray('label', $aButton);
                                    $sClass = getFromArray('class', $aButton);
                                    $sAttr  = getFromArray('attr', $aButton);
                                    $sPerm  = getFromArray('permission', $aButton);
                                    $sPerm  = $sPerm ? 'admin:' . $CONFIG['PERMISSION'] . ':' . $sPerm : '';

                                    if (empty($CONFIG['PERMISSION']) || empty($sPerm) || userHasPermission($sPerm)) {

                                        $cEnabled = getFromArray('enabled', $aButton);
                                        if (is_callable($cEnabled) && !$cEnabled($oItem)) {
                                            continue;
                                        }

                                        $sLabel = $oMustache->render($sLabel, $oItem);
                                        $sClass = $oMustache->render($sClass, $oItem);
                                        $sAttr  = $oMustache->render($sAttr, $oItem);
                                        $sUrl   = $oMustache->render($sUrl, $oItem);

                                        if (!preg_match('/^(\/|https?:\/\/)/', $sUrl)) {
                                            $sUrl = $CONFIG['BASE_URL'] . '/' . $sUrl;
                                        }

                                        echo anchor(
                                            $sUrl,
                                            $sLabel,
                                            'class="btn btn-xs ' . $sClass . '" ' . $sAttr
                                        );
                                    }
                                }

                                if ($CONFIG['CAN_EDIT']) {
                                    if (empty($CONFIG['PERMISSION']) || userHasPermission('admin:' . $CONFIG['PERMISSION'] . ':edit')) {
                                        echo anchor(
                                            $CONFIG['BASE_URL'] . '/edit/' . $oItem->id,
                                            lang('action_edit'),
                                            'class="btn btn-xs btn-primary"'
                                        );
                                    }
                                }

                                if ($CONFIG['CAN_DELETE']) {
                                    if (empty($CONFIG['PERMISSION']) || userHasPermission('admin:' . $CONFIG['PERMISSION'] . ':delete')) {
                                        if ($CONFIG['CAN_RESTORE']) {
                                            $sConfirm = 'You <strong>can</strong> undo this action.';
                                        } else {
                                            $sConfirm = 'You <strong>cannot</strong> undo this action.';
                                        }
                                        echo anchor(
                                            $CONFIG['BASE_URL'] . '/delete/' . $oItem->id,
                                            lang('action_delete'),
                                            'class="btn btn-xs btn-danger confirm" data-body="' . $sConfirm . '"'
                                        );
                                    }
                                }

                                ?>
                            </td>
                        </tr>
                        <?php
                    }

                } else {
                    ?>
                    <tr>
                        <td colspan="<?=count($CONFIG['INDEX_FIELDS']) + 1?>" class="no-data">
                            No items found
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?=adminHelper('loadPagination', $pagination)?>
</div>
