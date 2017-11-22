<?php
$csv = array_map('str_getcsv', file('test_chord.csv'));
// $csv = array_map('str_getcsv', file('/var/www/html/research/chordDiagram/dendogram-1.csv'));

$factors=array_shift($csv);//getting first row of csv ie factors names 
$first = array_shift($factors);


// print_r($factors);exit;
//getting influencers names out. created array of auther names
foreach ($csv as $value) {
	$array_authors[]=$value[0];

}

//shifting first element of csv to keep only data into it.
foreach ($csv as $key => $value) {
	array_shift($value);
	$newcsv[] = $value;
}


// $pushArray =0;


$respondents=0;//Total of data present inside csv ie. count of values .

foreach ($newcsv as $key => $d_row) {
	foreach($d_row as $d_data){
		$respondents = $respondents+(int)$d_data;
		
	}
}

$emptyPerc = 0.3; //What % of the circle should become empty
$emptyStroke = round($respondents*$emptyPerc);//this value is used in script to Maintain ration of cut portion.


//Creation of MAtrix like array Starts here.
$output = array();
$row_counter = 0;
foreach ($newcsv as $key => $data) {
	for($i=0;$i<=sizeof($array_authors);$i++){
		$output[$row_counter][] = 0;
	}

	foreach ($data as $k => $v):
		$output[$row_counter][] = (int)$v;
		$output[sizeof($array_authors) + $k][] = (int)$v;
		if($k==sizeof($data)-1){
			$output[$row_counter][] = 0;

		}
	endforeach;

	$row_counter++;
}


foreach ($newcsv as $key => $data) {
	foreach ($data as $k => $v):
		$output[sizeof($array_authors) + $k][] = 0;

	endforeach;

}

ksort($output);


foreach ($output as $key => $data) {
	if($key>=sizeof($array_authors)){
		array_push($data,0);
		$newoutput[]=$data;
	}else{
		$newoutput[]=$data;
	}
	
}

//adding empty stroke value in matrix at postion
$cnt = sizeof($array_authors)+sizeof($factors)+1;
for($i=0;$i<=$cnt;$i++){
	$moto []=0;
	if($i==$cnt){
		$moto[]=$emptyStroke;
	}

}
$inserted_array[]=$moto;


$cnt = sizeof($array_authors)+sizeof($factors)+1;
for($i=0;$i<=$cnt;$i++){
	if($i==sizeof($array_authors)+1){
		$lastRow []=$emptyStroke;
	}else{
		$lastRow []=0;
	}
	

}

$offset = sizeof($array_authors)+1;

array_splice($newoutput, $offset,0,$inserted_array);

array_push($newoutput,$lastRow);

array_push($array_authors,"");
array_push($factors,"");

$Names = array_merge($array_authors,$factors);




?>