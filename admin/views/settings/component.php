<div class="group-settings component">
    <?php

    echo form_open();
    echo form_hidden('slug', $slug);

    foreach ($fieldsets as $aFieldset) {

        echo '<fieldset>';
        echo '<legend>';
        echo $aFieldset['legend'] ?: 'Generic Settings';
        echo '</legend>';

        foreach ($aFieldset['fields'] as $oField) {

            $aField = (array) $oField;

            if (empty($aField['type'])) {
                $aField['type'] = 'text';
            }

            if (array_key_exists($aField['key'], $settings)) {
                $aField['default'] = $settings[$aField['key']];
            }

            switch ($aField['type']) {

                case 'bool':
                case 'boolean':

                    echo form_field_boolean($aField);
                    break;

                case 'dropdown':
                case 'select':
                    $aField['class'] = 'select2';
                    echo form_field_dropdown($aField, (array) $aField['options']);
                    break;

                case 'wysiwyg':
                    echo form_field_wysiwyg($aField);
                    break;

                default:
                    echo form_field($aField);
                    break;
            }
        }

        echo '</fieldset>';
    }

    echo '<p>' . form_submit('submit', 'Save Changes', 'class="btn btn-success"') . '</p>';
    echo form_open();

    ?>
</div>