<div class="group-defaultcontroller edit">
    <?=form_open()?>
    <fieldset>
        <legend>Basic Details</legend>
        <?php

        $aField = array(
            'key'      => 'label',
            'label'    => 'Label',
            'required' => true,
            'default'  => !empty($item) ? $item->label : ''
        );
        echo form_field($aField);

        ?>
    </fieldset>
    <p>
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </p>
    <?=form_close()?>
</div>
