<?php

$columnsOut = array();

foreach ($data->list_fields() as $column) {

    $columnsOut[] = '"' . str_replace('"', '""', $column) . '"';
}

echo $columnsOut ? implode(',', $columnsOut) . "\n" : '';

// --------------------------------------------------------------------------

//  Now do the data dance
while ($row = $data->_fetch_object()) {

    $csvRow = '';

    foreach ($row as $value) {

        //  Stringy items only, please
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {

            $value = json_encode($value, JSON_PRETTY_PRINT);
        }

        //  Sanitize
        $value = str_replace('"', '""', $value);
        $value = trim(preg_replace("/\r\n|\r|\n/", ' ', $value));

        //  Add to the row
        $csvRow .= '"' . $value . '",';
    }

    //  Spit it oot, hen!
    echo substr($csvRow, 0, -1) . "\n";
}
