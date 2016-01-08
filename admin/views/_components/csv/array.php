<?php

//  Determine the field titles if we can
$first   = (array) reset($data);
$columns = array_keys($first);
$columnsOut = array();

foreach ($columns as $column) {

    $columnsOut[] = '"' . str_replace('"', '""', $column) . '"';
}

echo $columnsOut ? implode(',', $columnsOut) . "\n" : '';

// --------------------------------------------------------------------------

//  Now do the data dance
for ($i=0; $i < count($data); $i++) {

    $csvRow = '';

    foreach ($data[$i] as $value) {

        //  Stringy items only, please
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {

            $value = json_encode($value, JSON_PRETTY_PRINT);
        }

        //  Sanitize
        $value = str_replace('"', '""', $value);
        $value = trim(preg_replace("/\r\n|\r|\n/", ' ', $value));

        //  Add to the csvRow
        $csvRow .= '"' . $value . '",';
    }

    //  Spit it oot, hen!
    echo substr($csvRow, 0, -1) . "\n";
}
