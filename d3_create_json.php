<?php

$csv = array_map('str_getcsv', file('/var/www/html/research/influencers.csv'));

$factors=array_shift($csv);
// $removed_fact=array_shift($factors);
// // print_r($factors);
// print_r($factors);

foreach ($csv as $value) {
	$array_authors[]=$value[0];
}
// print_r($array_authors);


/*
//dependency array of autthor as key and factors as value.
foreach ($csv as $key => $value) {
	// print_r($value);
	// echo $value[0];
	for($i=0;$i<=count($factors);$i++){
		if(isset($value[$i]) && !empty($value[$i]) && $value[$i]>1){
			$dependency_array[$value[0]][]=$factors[$i];
		}
	}

	print_r($dependency_array);
}*/


foreach ($factors as $key => $value) {
	foreach ($csv as $data) {
		if(isset($data[$key]) && !empty($data[$key]) && $data[$key]>2){
			$dependency_array_influencers[$value][]=$data[0];
		}
	}
	// print_r($dependency_array_influencers);
}

//Array of influencers name.
foreach ($array_authors as  $author_name) {
	$final_array[]=array(
			"type" => "view",
			"name" => $author_name,
			"depends" =>''
	);
}

//Array of Topics. 
foreach ($dependency_array_influencers as $key => $value) {
	$final_array[]=array(
			"type" => "view1",
			"name" => $key,
			"depends" => $value
		);
}
echo json_encode($final_array);

$fp = fopen('/var/www/html/research/d3-process-map/data/default/results.json', 'w');
fwrite($fp, json_encode($final_array));
fclose($fp);
// print_r($final_array);
?>