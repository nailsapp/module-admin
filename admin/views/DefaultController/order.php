<?=form_open()?>
<table>
    <thead>
        <tr>
            <th width="50"></th>
            <th>Item</th>
        </tr>
    </thead>
    <tbody class="js-admin-sortable" data-handle=".handle">
        <?php
        foreach ($items as $oItem) {
            ?>
            <tr>
                <td width="50" class="text-center handle">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </td>
                <td>
                    <?=$oItem->label?>
                    <input type="hidden" name="order[]" value="<?=$oItem->id?>">
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<div class="admin-floating-controls">
    <button type="submit" class="btn btn-primary">
        Save Changes
    </button>
</div>
<?=form_close()?>
