<?php
$csv = array_map('str_getcsv', file('/var/www/html/research/chordDiagram/test_chord.csv'));

$factors=array_shift($csv);//getting first row of csv ie factors names 
$first = array_shift($factors);

//getting influencers names out created array of auther names
foreach ($csv as $value) {
	$array_authors[]=$value[0];

}

foreach ($csv as $key => $value) {
	array_shift($value);
	$newcsv[] = $value;
}


$pushArray =0;




// echo sizeof($array_authors);

// print_r($newcsv);
// exit;
$output = array();
$row_counter = 0;
foreach ($newcsv as $key => $data) {
	for($i=0;$i<sizeof($array_authors);$i++){
		// array_unshift($data, $pushArray);

		$output[$row_counter][] = 0;
		// $output[sizeof($array_authors) + $i][] = 0;
		// $output[sizeof($array_authors) + $i][] = $v;
	}

	foreach ($data as $k => $v):
		$output[$row_counter][] = $v;
		$output[sizeof($array_authors) + $k][] = $v;
		
		// $output[sizeof($array_authors) + $k][sizeof($array_authors) + sizeof($array_authors) - $k] = 0;
	endforeach;

	// foreach ($data as $k => $v):
	// 	$output[sizeof($array_authors) + $k][sizeof($array_authors)+$k] = 0;
		
	// 	// $output[sizeof($array_authors) + $k][sizeof($array_authors) + sizeof($array_authors) - $k] = 0;
	// endforeach;

	
	

	// array_push($data,$pushArray);
	// $finalArray[]=$data;

	$row_counter++;
}

foreach ($newcsv as $key => $data) {
	foreach ($data as $k => $v):
		$output[sizeof($array_authors) + $k][] = 0;
	endforeach;
}


ksort($output);
// print_r($output);

// echo json_encode($output);
exit;

// echo json_encode($finalArray);exit;

$Names = array_merge($array_authors,$factors);

print_r($Names);exit;


?>