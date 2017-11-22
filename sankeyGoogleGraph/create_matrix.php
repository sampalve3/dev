<?php
$csv = array_map('str_getcsv', file('/var/www/html/research/sankeyGoogleGraph/dendogram-1.csv'));




$factors=array_shift($csv);
// $first = array_shift($factors);
// $factor =array_pop($factors);
// $factor =array_pop($factors);

// print_r($factors);

// array_push($factors, "");

foreach ($csv as $value) {
	$array_authors[]=$value[0];
}
// print_r($array_authors);
// print_r($csv);exit;
// array_push($array_authors, "");

foreach ($csv as $index => $data) {
	for($i=1;$i<=sizeof($data);$i++){
		if(isset($data[$i]) && !empty($data[$i])){
			$data_array[]=array(
					$data[0],$factors[$i],(int)$data[$i]
				
			);
		}
	}
}
$json =$data_array;

?>