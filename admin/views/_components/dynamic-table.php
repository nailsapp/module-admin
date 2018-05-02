<table class="js-admin-dynamic-table" data-data="<?=htmlentities(json_encode($aData))?>">
    <thead>
        <tr>
            <th width="40">Order</th>
            <?php

            foreach ($aFields as $aField) {
                $bIsHidden = getFromArray('type', $aField) === 'hidden';
                if (!$bIsHidden) {
                    echo '<th>' . getFromArray('label', $aField) . '</th>';
                }
            }

            ?>
            <th width="40"></th>
        </tr>
    </thead>
    <tbody class="js-admin-dynamic-table__template js-admin-sortable" data-handle=".handle">
        <tr>
            <td class="text-center handle">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </td>
            <?php

            foreach ($aFields as $aField) {

                $sFieldKey = getFromArray('key', $aField);
                $sType     = getFromArray('type', $aField);
                $sClass    = getFromArray('class', $aField);

                $sCompiledKey = $sKey . '[{{index}}][' . $sFieldKey . ']';
                if ($sFieldKey === 'order') {
                    $sClass .= ' js-admin-sortable__order';
                }

                if (strtolower($sType) === 'hidden') {
                    echo form_hidden($sCompiledKey);
                } elseif (is_callable('form_' . $sType)) {
                    echo '<td>';
                    echo call_user_func_array('form_' . $sType, [$sCompiledKey, '{{' . $sFieldKey . '}}']);
                    echo '</td>';
                } elseif (!empty($aField['html'])) {
                    echo '<td>';
                    echo $aField['html'];
                    echo '</td>';
                }
            }

            ?>
            <td>
                <button type="button" class="btn btn-xs btn-danger js-admin-dynamic-table__remove">
                    &times;
                </button>
            </td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="5">
                <button type="button" class="btn btn-xs btn-success js-admin-dynamic-table__add">
                    &plus; Add
                </button>
            </td>
        </tr>
    </tbody>
</table>
