<?php


// $csv_file=fopen("/var/www/html/test/bubblegraph/newcsv.csv","r") or die("cant open csv");


// get csv
$arr_influencers = array_map('str_getcsv', file('/var/www/html/research/bubblechart/bubble_chart_second.csv'));
// print_r($arr_influencers);
// array_shift($arr_influencers);
$factors=array_shift($arr_influencers);
$factor=array_shift($factors);
// print_r($factors);



// $arrayNames=array("Sexual harassment","Violence","Abortion","Marriage Laws & Acts ( Divorce & Dowry)","Women -Empowerment","Sexual harassment at work","Abuse of Law","Maternal Health","Menstruation","School Education","entrepreneurship","Sanitation","Sex Work","Girl Child Victim (chilld Marriage + Child Trafficking)","Property Rights","Higher Education","Diseases","Moral Policing","Surrogacy / Adoption","Family Planning","Women in Agriculture","Vocational Education","Gender Inequality at work","Perks & Benefits","Malnutrition","Mortality Rate","Female Infanticide / Foeticide","Sex Education");

$cluster_color = array("#e6550d","#6baed6","#31a354","#D900F7","#fdd0a2","#fd8d3c","#6baed6","#FF5733","#6baed6","#cfec84","#756bb1","#F3FF00","#22249B","#02FFFB","#01FFF3");
// 
// foreach  ($arr_influencers as $key => $val) {
foreach  ($arr_influencers as $single_influencer) {
	array_pop($single_influencer);
	$influencer = array_shift($single_influencer);
	
	// print_r($single_influencer);
	// echo "\r\n".$influencer.' ';
	$single_influencer = array_flip(array_flip($single_influencer));
	arsort($single_influencer, SORT_NUMERIC);
	// print_r(array_slice($single_influencer,1));
	$tweets_cluster = reset($single_influencer);
	$idx_cluster = key($single_influencer);
	// echo "\r\n\r\n".$influencer.' '.$idx_cluster.' '.$tweets_cluster."\r\n\r\n";

	$output[$factors[$idx_cluster]][]=array(
		"name"=>$influencer,
		"size"=>$tweets_cluster,
		"color"=>$cluster_color[$idx_cluster]
		);
	
}

// echo "<pre>";
// print_r($output);
// exit;
// echo json_encode($output);

foreach ($output as $key => $value) {
	$last_output[]=array(
		"name"=>$key,
		"children"=>$value
		);
}
// echo json_encode($last_output);
// print_r($last_output);



	$jsonDone[]=array(
		"name"=>"flare",
		"children"=>$last_output
		);

	$json=json_encode($jsonDone);
	$json1=substr($json, 1, -1);
	// print_r($json1);

	$fp = fopen('/var/www/html/research/charts/bubblechart/test.json', 'w');
	// fwrite($fp, json_encode($jsonDone));
	fwrite($fp, $json1);
	// fclose($fp);exit;


	// ftruncate($fh, $stat['size']-1);
	// // ftruncate($fh, $stat['size']-2);
	// // ftruncate($fh, 0);
	// print_r($stat);

	// rewind($fh);
	// exit;
 
?>