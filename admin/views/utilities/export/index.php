<div class="group-utilities export">
    <p>
        Export data stored in the site\'s database in a variety of formats.
    </p>
    <p class="alert alert-warning">
        <strong>Please note:</strong> Exporting may take some time when executing on large databases. Please be patient.
    </p>
    <?=form_open()?>
    <fieldset>
        <legend>Data Source</legend>
        <?php

        $aField = [
            'key'      => 'source',
            'label'    => 'Source',
            'required' => true,
            'class'    => 'select2',
            'options' => []
        ];

        $options = [];
        foreach ($sources as $oSource) {
            $aField['options'][$oSource->slug] = $oSource->label . ' - ' . $oSource->description;
        }

        echo form_field_dropdown($aField);

        ?>
    </fieldset>
    <fieldset>
        <legend>Export Format</legend>
        <?php

        $aField = [
            'key'      => 'format',
            'label'    => 'Format',
            'required' => true,
            'class'    => 'select2',
            'options' => []
        ];

        $options = [];
        foreach ($formats as $oFormat) {
            $aField['options'][$oFormat->slug] = $oFormat->label . ' - ' . $oFormat->description;
        }

        echo form_field_dropdown($aField);

        ?>
    </fieldset>
    <p>
        <?=form_submit('submit', 'Export', 'class="btn btn-primary"')?>
    </p>
    <?=form_close()?>
</div>
