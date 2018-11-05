<?php

use Nails\Admin\Helper;

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
                    foreach ($CONFIG['INDEX_FIELDS'] as $sField => $sLabel) {

                        if ($sField === '{{DYNAMIC_FIELDS}}') {
                            foreach ($CONFIG['INDEX_FIELDS_DYNAMIC'] as $sColumnName =>  $cRowValue) {
                                $aAttr = [
                                    'class' => ['field', 'field--' . $sColumnName],
                                ];

                                if (in_array($sColumnName, $CONFIG['INDEX_BOOL_FIELDS'])) {
                                    $aAttr['width']   = 150;
                                    $aAttr['class'][] = 'boolean';
                                } elseif (in_array($sColumnName, $CONFIG['INDEX_USER_FIELDS'])) {
                                    $aAttr['width'] = 300;
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
                                    <?=$sColumnName?>
                                </th>
                                <?php
                            }
                            continue;
                        }

                        $aAttr = [
                            'class' => ['field', 'field--' . $sField],
                        ];

                        if (in_array($sField, $CONFIG['INDEX_BOOL_FIELDS'])) {
                            $aAttr['width']   = 150;
                            $aAttr['class'][] = 'boolean';
                        } elseif (in_array($sField, $CONFIG['INDEX_USER_FIELDS'])) {
                            $aAttr['width'] = 300;
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

                            foreach ($CONFIG['INDEX_FIELDS'] as $sField => $sLabel) {
                                if ($sField === '{{DYNAMIC_FIELDS}}') {

                                    foreach ($CONFIG['INDEX_FIELDS_DYNAMIC'] as $sColumnName =>  $cRowValue) {

                                        $sValue = $cRowValue($oItem);

                                        if (in_array($sField, $CONFIG['INDEX_BOOL_FIELDS'])) {
                                            echo Helper::loadBoolCell($sValue);
                                        } elseif (in_array($sField, $CONFIG['INDEX_USER_FIELDS'])) {
                                            echo Helper::loadUserCell($sValue);
                                        } else {
                                            echo Helper::loadCellAuto($sValue, 'field field--' . $sField);
                                        }
                                    }

                                } elseif (property_exists($oItem, $sField)) {

                                    $sCellAdditional = '';
                                    if (classUses($CONFIG['MODEL_INSTANCE'], '\Nails\Common\Traits\Model\Nestable')) {
                                        $aBreadcrumbs = json_decode($oItem->breadcrumbs);
                                        if (!empty($aBreadcrumbs)) {
                                            $aItems          = arrayExtractProperty($aBreadcrumbs, 'label');
                                            $sCellAdditional = '<small>' . implode(' &rsaquo; ', $aItems) . '</small>';
                                        }
                                    }

                                    if (in_array($sField, $CONFIG['INDEX_BOOL_FIELDS'])) {
                                        echo Helper::loadBoolCell($oItem->{$sField});
                                    } elseif (in_array($sField, $CONFIG['INDEX_USER_FIELDS'])) {
                                        echo Helper::loadUserCell($oItem->{$sField});
                                    } else {
                                        echo Helper::loadCellAuto(
                                            $oItem->{$sField},
                                            'field field--' . $sField,
                                            $sCellAdditional
                                        );
                                    }

                                } elseif (strpos($sField, '.') !== false) {

                                    //  @todo (Pablo - 2018-08-08) - Handle arrays in expanded objects
                                    $aField  = explode('.', $sField);
                                    $sField1 = getFromArray(0, $aField);
                                    $sField2 = getFromArray(1, $aField);

                                    if (property_exists($oItem, $sField1)) {
                                        if (!empty($oItem->{$sField1}) && property_exists($oItem->{$sField1}, $sField2)) {
                                            $mValue = $oItem->{$sField1}->{$sField2};
                                        } else {
                                            $mValue = '<span class="text-muted">&mdash;</span>';
                                        }
                                    } else {
                                        $mValue = '<span class="text-muted">&mdash;</span>';
                                    }

                                    echo Helper::loadCellAuto(
                                        $mValue,
                                        'field field--' . $sField
                                    );

                                } else {
                                    ?>
                                    <td class="field field--<?=$sField?>">
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

                                        $sUrl   = $oMustache->render($sUrl, $oItem);
                                        $sLabel = $oMustache->render($sLabel, $oItem);
                                        $sClass = $oMustache->render($sClass, $oItem);
                                        $sAttr  = $oMustache->render($sAttr, $oItem);

                                        echo anchor(
                                            $CONFIG['BASE_URL'] . '/' . $sUrl,
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
