<?php

use Nails\Common\Factory;
use Nails\Common\Interfaces;

/**
 * @var string                        $sFormUrl
 * @var Factory\Component             $oComponent
 * @var Interfaces\Component\Settings $oSetting
 * @var Factory\Model\Field[]         $aFieldSets
 */

$aTabs = [];
foreach ($aFieldSets as $sLabel => $aFields) {
    $aTabs[] = [
        'label'   => $sLabel,
        'content' => function () use ($aFields) {

            foreach ($aFields as $iIndex => $oField) {

                if (empty($oField->getKey())) {
                    throw new \Nails\Common\Exception\NailsException(
                        sprintf(
                            'Property "key" is missing for field "%s"',
                            $oField->label ?: $iIndex
                        )
                    );

                } elseif (is_callable('\Nails\Common\Helper\Form\Field::' . $oField->type)) {
                    echo call_user_func('\Nails\Common\Helper\Form\Field::' . $oField->type, (array) $oField);

                } else {
                    echo \Nails\Common\Helper\Form\Field::text((array) $oField);
                }
            }
        },
    ];
}

echo form_open($sFormUrl);
echo \Nails\Admin\Helper::tabs($aTabs);
?>
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </div>
<?php
echo form_close();
