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

            if (is_string($aFields)) {
                echo $aFields;

            } else {
                foreach ($aFields as $iIndex => $oField) {
                    if (empty($oField->key)) {
                        throw new \Nails\Common\Exception\NailsException(
                            sprintf(
                                'Property "key" is missing for field "%s"',
                                $oField->label ?: $iIndex
                            )
                        );

                    } elseif (is_callable('\Nails\Common\Helper\Form\Field::' . $oField->type)) {
                        echo call_user_func('\Nails\Common\Helper\Form\Field::' . $oField->type, (array) $oField);

                    } else {
                        echo Nails\Common\Helper\Form\Field::text((array) $oField);
                    }
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
        if (!empty($oItem) && $CONFIG['ENABLE_NOTES']) {
            ?>
            <button type="button"
                    class="btn btn-default pull-right js-admin-notes"
                    data-model-name="<?=$CONFIG['MODEL_NAME']?>"
                    data-model-provider="<?=$CONFIG['MODEL_PROVIDER']?>"
                    data-id="<?=$oItem->id?>">
                Notes
            </button>
            <?php
        }
        ?>
    </div>
    <?=form_close()?>
</div>
