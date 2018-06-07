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

                $aField            = (array) $oField;
                $aField['default'] = !empty($item) && property_exists($item, $oField->key) ? $item->{$oField->key} : '';

                if (!array_key_exists('required', $aFieldSets)) {
                    $aField['required'] = in_array('required', $oField->validation);
                }

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
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </div>
    <?=form_close()?>
</div>
