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
                        echo '<th class="field field--' . $sField . '">' . $sLabel . '</th>';
                    }

                    ?>
                    <th class="actions" style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

                if (!empty($items)) {

                    foreach ($items as $oItem) {

                        echo '<tr>';
                        foreach ($CONFIG['INDEX_FIELDS'] as $sField => $sLabel) {
                            echo '<td class="field field--' . $sField . '">';
                            //  @todo - handle different field types
                            if (property_exists($oItem, $sField)) {
                                echo $oItem->{$sField};
                            } elseif(strpos($sField, '.') !== false) {
                                //  @todo - handle arrays in expanded objects
                                $aField  = explode('.', $sField);
                                $sField1 = getFromArray(0, $aField);
                                $sField2 = getFromArray(1, $aField);
                                if (property_exists($oItem, $sField1)) {
                                    if (property_exists($oItem->{$sField1}, $sField2)) {
                                        echo $oItem->{$sField1}->{$sField2};
                                    } else {
                                        echo $sField;
                                    }
                                } else {
                                    echo $sField;
                                }
                            } else {
                                echo $sField;
                            }
                            echo '</td>';
                        }

                        echo '<td class="actions">';

                        if (empty($CONFIG['PERMISSION']) || userHasPermission('admin:' . $CONFIG['PERMISSION'] . ':edit')) {
                            echo anchor(
                                $CONFIG['BASE_URL'] . '/edit/' . $oItem->id,
                                lang('action_edit'),
                                'class="btn btn-xs btn-primary"'
                            );
                        }

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

                        echo '</td>';
                        echo '</tr>';
                    }

                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="no-data">
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
