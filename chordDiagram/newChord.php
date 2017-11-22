<?php
// $csv = array_map('str_getcsv', file('/var/www/html/research/chordDiagram/test_chord.csv'));


// Your file
$file = file("/var/www/html/research/chordDiagram/test_chord.csv");

// Let's get your keys on the first row
$keys = explode(';', $file[0]);
unset($keys[0]);

$matrix = array();
// Loop through each line
for ($i = 1; $i < count($file); $i += 1) {
    // Get values of the line
    $values = explode(';', $file[$i]);
    print_r($values);exit;

    // If the values have `"`, strip them
    for ($j = 0; $j < count($values); $j += 1) {
        $values[$j] = substr($values[$j], 1, (strlen($values[$j]) - 2));
    }

    // the line key
    $key = $values[0];
    unset($values[0]);

    // Set indexes
    $values = array_combine($keys, $values);
    // Add to your main array
    $matrix[$key] = $values;
}


print_r($matrix);


?>