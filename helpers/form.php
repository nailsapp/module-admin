<?php

if (!function_exists('form_dynamic_table')) {
    function form_dynamic_table($aField)
    {
        return \Nails\Admin\Helper\Form::dynamicTable($aField);
    }
}

if (!function_exists('form_field_dynamic_table')) {
    function form_field_dynamic_table($aField)
    {
        return \Nails\Admin\Helper\Form::form_field_dynamic_table($aField);
    }
}
