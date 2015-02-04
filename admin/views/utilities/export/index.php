<div class="group-utilities export">
    <p>
        Export data stored in the site\'s database in a variety of formats.
    </p>
    <p class="system-alert message">
        <strong>Please note:</strong> Exporting may take some time when executing on large databases. Please be patient.
    </p>
    <?=form_open()?>
    <fieldset>
        <legend>Data Source</legend>
        <?php

            //  Display Name
            $field             = array();
            $field['key']      = 'source';
            $field['label']    = 'Source';
            $field['required'] = true;
            $field['class']    = 'select2';

            $options = array();
            foreach ($sources as $key => $source) {

                $options[$key] = $source[0] . ' - ' . $source[1];
            }

            echo form_field_dropdown($field, $options);

        ?>
    </fieldset>
    <fieldset>
        <legend>Export Format</legend>
        <?php

            //  Display Name
            $field             = array();
            $field['key']      = 'format';
            $field['label']    = 'Format';
            $field['required'] = true;
            $field['class']    = 'select2';

            $options = array();
            foreach ($formats as $key => $format) {

                $options[$key] = $format[0] . ' - ' . $format[1];
            }

            echo form_field_dropdown($field, $options);

        ?>
    </fieldset>
    <p>
        <?=form_submit('submit', 'Export', 'class="awesome"')?>
    </p>
    <?=form_close()?>
</div>