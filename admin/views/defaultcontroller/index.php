<div class="group-defaultcontroller browse">
    <p>
        Manage <?=$CONFIG_TITLE_PLURAL?>.
    </p>
    <?=adminHelper('loadSearch', $search)?>
    <?=adminHelper('loadPagination', $pagination)?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Last modified by</th>
                    <th>Last modified date</th>
                    <th class="actions" style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

                if (!empty($items)) {
                    foreach ($items as $oItem) {
                        ?>
                        <tr>
                            <td>
                                <?=$oItem->label?>
                            </td>
                            <?=adminHelper('loadUserCell', $oItem->modified_by);?>
                            <?=adminHelper('loadDateCell', $oItem->modified);?>
                            <td class="actions">
                                <?php

                                if (userHasPermission('admin:' . $CONFIG_PERMISSION . ':edit')) {
                                    echo anchor(
                                        'admin/' . $CONFIG_BASE_URL . '/edit/' . $oItem->id,
                                        lang('action_edit'),
                                        'class="btn btn-xs btn-primary"'
                                    );
                                }

                                if (userHasPermission('admin:' . $CONFIG_PERMISSION . ':delete')) {
                                    echo anchor(
                                        'admin/' . $CONFIG_BASE_URL . '/delete/' . $oItem->id,
                                        lang('action_delete'),
                                        'class="btn btn-xs btn-danger confirm" data-body="You can undo this action."'
                                    );
                                }

                                ?>
                            </td>
                        </tr>
                        <?php
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
