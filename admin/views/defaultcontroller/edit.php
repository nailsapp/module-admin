<div class="group-defaultcontroller edit">
    <?=form_open()?>
    <fieldset>
        <legend>Basic Details</legend>
        <?php

        foreach ($CONFIG['FIELDS'] as $oField) {

            if (in_array($oField->key, $CONFIG['EDIT_IGNORE_FIELDS'])) {
                continue;
            }

            $aField = array(
                'key'      => $oField->key,
                'label'    => $oField->label,
                'default'  => !empty($item) && property_exists($item, $oField->key) ? $item->{$oField->key} : '',
                'class'    => 'field field--' . $oField->key
            );
            echo form_field($aField);

        }
        ?>
    </fieldset>
    <p>
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </p>
    <?=form_close()?>
</div>
