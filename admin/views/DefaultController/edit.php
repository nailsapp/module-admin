<div class="group-defaultcontroller edit">
    <?php $oInput = \Nails\Factory::service('Input'); ?>
    <?=form_open()?>
    <input type="hidden" name="activeTab" value="<?=set_value('activeTab')?>" id="activeTab"/>
    <ul class="tabs">
        <?php

        $i = 0;
        foreach ($aFieldSets as $sFieldSet => $aFields) {
            if (empty($i)) {
                $sActive = $oInput->post('activeTab') == 'tab-' . $i || !$oInput->post('activeTab') ? 'active' : '';
            } else {
                $sActive = $oInput->post('activeTab') == 'tab-' . $i ? 'active' : '';
            }
            ?>
            <li class="tab <?=$sActive?>">
                <a href="#" data-tab="tab-<?=$i?>">
                    <?=$sFieldSet?>
                </a>
            </li>
            <?php
            $i++;
        }

        ?>
    </ul>
    <section class="tabs">
        <?php
        $i = 0;
        foreach ($aFieldSets as $sLegend => $aFields) {
            if (empty($i)) {
                $sActive = $oInput->post('activeTab') == 'tab-' . $i || !$oInput->post('activeTab') ? 'active' : '';
            } else {
                $sActive = $oInput->post('activeTab') == 'tab-' . $i ? 'active' : '';
            }
            ?>
            <div class="tab-page tab-<?=$i?> <?=$sActive?> fieldset">
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
            </div>
            <?php
            $i++;
        }

        ?>
    </section>
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </div>
    <?=form_close()?>
</div>
