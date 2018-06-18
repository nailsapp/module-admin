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

                    foreach ($CONFIG['INDEX_FIELDS'] as $sField => $sLabel) {
                        $aAttr = [
                            'class' => ['field', 'field--' . $sField],
                        ];

                        if (in_array($sField, $CONFIG['INDEX_BOOL_FIELDS'])) {
                            $aAttr['width'] = 150;
                        } elseif (in_array($sField, $CONFIG['INDEX_USER_FIELDS'])) {
                            $aAttr['width'] = 300;
                        }

                        $sAttr = '';
                        foreach ($aAttr as $sKey => $mValue) {
                            if (is_array($mValue)) {
                                $sAttr = ' ' . $sKey . '="' . implode(' ', $mValue) . '"';
                            } else {
                                $sAttr = ' ' . $sKey . '="' . $mValue . '"';
                            }
                        }
                        echo '<th ' . $sAttr . '>' . $sLabel . '</th>';
                    }

                    ?>
                    <th class="actions" style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

                if (!empty($items)) {

                    foreach ($items as $oItem) {

                        echo '<tr>';
                        foreach ($CONFIG['INDEX_FIELDS'] as $sField => $sLabel) {
                            if (property_exists($oItem, $sField)) {
                                //  @todo - handle more field types
                                if (preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $oItem->{$sField})) {
                                    echo Helper::loadDateTimeCell($oItem->{$sField});
                                } elseif (preg_match('/\d\d\d\d-\d\d-\d\d/', $oItem->{$sField})) {
                                    echo Helper::loadDateCell($oItem->{$sField});
                                } elseif (in_array($sField, $CONFIG['INDEX_BOOL_FIELDS'])) {
                                    echo Helper::loadBoolCell($oItem->{$sField});
                                } elseif (in_array($sField, $CONFIG['INDEX_USER_FIELDS'])) {
                                    echo Helper::loadUserCell($oItem->{$sField});
                                } else {
                                    echo '<td class="field field--' . $sField . '">';
                                    echo $oItem->{$sField};
                                    echo '</td>';
                                }
                            } elseif (strpos($sField, '.') !== false) {
                                //  @todo - handle arrays in expanded objects
                                $aField  = explode('.', $sField);
                                $sField1 = getFromArray(0, $aField);
                                $sField2 = getFromArray(1, $aField);
                                echo '<td class="field field--' . $sField . '">';
                                if (property_exists($oItem, $sField1)) {
                                    if (!empty($oItem->{$sField1}) && property_exists($oItem->{$sField1}, $sField2)) {
                                        echo $oItem->{$sField1}->{$sField2};
                                    } else {
                                        echo '<span class="text-muted">&mdash;</span>';
                                    }
                                } else {
                                    echo '<span class="text-muted">&mdash;</span>';
                                }
                                echo '</td>';
                            } else {
                                echo '<td class="field field--' . $sField . '">';
                                echo '<span class="text-muted">&mdash;</span>';
                                echo '</td>';
                            }
                        }

                        echo '<td class="actions">';

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

                        echo '</td>';
                        echo '</tr>';
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
