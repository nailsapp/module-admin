<div class="group-defaultcontroller edit">
    <?php

    echo form_open();
    $aFieldSets = [];

    foreach ($CONFIG['FIELDS'] as $oField) {

        if (in_array($oField->key, $CONFIG['EDIT_IGNORE_FIELDS'])) {
            continue;
        }

        $sFieldSet = getFromArray('fieldset', (array) $oField, 'Basic Details');

        if (!array_key_exists($sFieldSet, $aFieldSets)) {
            $aFieldSets[$sFieldSet] = [];
        }

        $aFieldSets[$sFieldSet][] = $oField;
    }

    foreach ($aFieldSets as $sLegend => $aFields) {

        ?>
        <fieldset>
            <legend><?=$sLegend?></legend>
            <?php

            foreach ($aFields as $oField) {
                $aValidation = array_filter(explode('|', getFromArray('validation', (array) $oField)));
                $aField      = [
                    'key'       => getFromArray('key', (array) $oField),
                    'type'      => getFromArray('type', (array) $oField),
                    'label'     => getFromArray('label', (array) $oField),
                    'sub_label' => getFromArray('sub_label', (array) $oField),
                    'info'      => getFromArray('info', (array) $oField),
                    'required'  => in_array('required', $aValidation),
                    'default'   => !empty($item) && property_exists($item, $oField->key) ? $item->{$oField->key} : '',
                    'class'     => 'field field--' . getFromArray('key', (array) $oField),
                ];

                if (is_callable('form_field_' . $aField['type'])) {
                    echo call_user_func('form_field_' . $aField['type'], $aField);
                } else {
                    echo form_field($aField);
                }
            }

            ?>
        </fieldset>
        <?php

    }

    ?>
    <p>
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </p>
    <?=form_close()?>
</div>
