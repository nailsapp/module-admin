<div class="group-settings component">
    <?php

    echo form_open();
    echo form_hidden('slug', $slug);

    foreach ($fieldsets as $aFieldset) {

        echo '<fieldset>';
        echo '<legend>';
        echo $aFieldset['legend'] ?: 'Generic Settings';
        echo '</legend>';

        foreach ($aFieldset['fields'] as $aField) {

            echo form_field((array) $aField);
        }

        echo '</fieldset>';
    }

    echo '<p>' . form_submit('submit', 'Save Changes', 'class="btn btn-success"') . '</p>';
    echo form_open();

    ?>
</div>