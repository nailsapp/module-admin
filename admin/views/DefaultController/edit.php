<div class="group-defaultcontroller edit" <?=$CONFIG['EDIT_PAGE_ID'] ? 'id="' . $CONFIG['EDIT_PAGE_ID'] . '"' : ''?>>
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
                    if (is_callable('form_field_' . $oField->type)) {
                        echo call_user_func('form_field_' . $oField->type, (array) $oField);
                    } else {
                        echo form_field((array) $oField);
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
