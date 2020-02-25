<?php

use Nails\Admin\Helper;
use Nails\Common\Service\Input;
use Nails\Factory;

/** @var Input $oInput */
$oInput = Factory::service('Input');
$sUrl   = current_url() . '?slug=' . $slug . '&isModal=' . $oInput->get('isModal');

$aTabs = [];
foreach ($aFieldsets as $aFieldset) {
    $aTabs[] = [
        'label'   => $aFieldset['legend'] ?? 'Generic Settings',
        'content' => function () use ($aFieldset) {
            foreach ($aFieldset['fields'] as $iIndex => $oField) {

                if (empty($oField->key)) {

                    throw new \Nails\Common\Exception\NailsException(
                        sprintf(
                            'Property "key" is missing for field "%s"',
                            $oField->label ?: $iIndex
                        )
                    );
                }

                if (is_callable('\Nails\Common\Helper\Form\Field::' . $oField->type)) {
                    echo call_user_func('\Nails\Common\Helper\Form\Field::' . $oField->type, (array) $oField);
                } else {
                    echo Nails\Common\Helper\Form\Field::text((array) $oField);
                }
            }
        },
    ];
}

?>
<div class="group-settings component">
    <?php
    echo form_open($sUrl);
    echo Helper::tabs($aTabs);
    ?>
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </div>
    <?=form_close()?>
</div>
