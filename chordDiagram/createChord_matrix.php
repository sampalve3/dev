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

foreach ($newcsv as $key => $data) {
	for($i=0;$i<=sizeof($array_authors);$i++){
		array_unshift($data, $pushArray);

	}
	array_push($data,$pushArray);
	$finalArray[]=$data;
}

// print_r($finalArray);

// echo json_encode($finalArray);exit;

$Names = array_merge($array_authors,$factors);

print_r($Names);exit;


?>