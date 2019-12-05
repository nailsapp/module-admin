<?php

use Nails\Admin\Helper;
use Nails\Common\Service\Input;
use Nails\Factory;

/** @var Input $oInput */
$oInput = Factory::service('Input');

$aTabs = [];
foreach ($aFieldSets as $sLabel => $aFields) {
    $aTabs[] = [
        'label'   => $sLabel,
        'content' => function () use ($aFields) {
            foreach ($aFields as $oField) {
                if (is_callable('form_field_' . $oField->type)) {
                    echo call_user_func('form_field_' . $oField->type, (array) $oField);
                } else {
                    echo form_field((array) $oField);
                }
            }
        },
    ];
}

?>
<div class="group-defaultcontroller edit" <?=$CONFIG['EDIT_PAGE_ID'] ? 'id="' . $CONFIG['EDIT_PAGE_ID'] . '"' : ''?>>
    <?php
    echo form_open();
    echo Helper::tabs($aTabs);
    ?>
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
        <?php
        if (!empty($item) && $CONFIG['ENABLE_NOTES']) {
            ?>
            <button type="button"
                    class="btn btn-default pull-right js-admin-notes"
                    data-model-name="<?=$CONFIG['MODEL_NAME']?>"
                    data-model-provider="<?=$CONFIG['MODEL_PROVIDER']?>"
                    data-id="<?=$item->id?>">
                Notes
            </button>
            <?php
        }
        ?>
    </div>
    <?=form_close()?>
</div>
