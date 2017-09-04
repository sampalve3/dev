<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '/var/www/html/resizeImage/MysqliDb.php';
//header("Content-type:image/png");
// DEFINE('DB_HOST','35.160.66.102');
// // DEFINE('DB_HOST','localhost');
// DEFINE('DB_USER','premium');
// DEFINE('DB_PASSWORD','pre2m0i1u0m');
// DEFINE('DB_NAME','confab_intext_nseit');

function dd($v, $t='') {
	echo "<br>$t = $v";
}

//$folder ='/var/www/html/imageResize/imageMagick/';
$folder='/var/www/html/resizeImage/resizedImages/';
// $folder='/var/www/html/confab-console-v2/account/nseit/assets/common/img/';
//$mysqli= new MysqliDb(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

//$cols = Array('comp_logo','comp_id');
// // $mysqli->where('comp_logo like "%http%"');
// $result =$mysqli->get("mstr_company_info",null,$cols);
// echo $mysqli->getLastQuery();
//print_r($result);exit;
$dir='/var/www/html/resizeImage/missing_company_logos';

 // foreach($result as  $row){
	$images = glob("$dir/*.{jpg,png,jpeg,gif}", GLOB_BRACE);

	// print_r($images);exit;
	
	foreach ($images as $singleImage) {
		$filename=$singleImage;
		// echo $singleImage;
		preg_match ( '/([0-9]+)/', $singleImage, $matches );
		$name='logo_'.$matches[0].'';



		// print_r($name);exit;
		# code...
	
			
			// $filename=$row['comp_logo'];
			// $filename="/var/www/html/imageResize/oneImageFull/kiri.png";
			// $info=pathinfo($filename);
			// $name = 'logo_1083';
			//$name = $row['comp_id'].basename($filename);
			// dd($filename);

			$image = new Imagick();
			$image->newImage(256, 256, new ImagickPixel('white'));
			$image->setImageFormat('jpg');

			
			if(isset($filename) && !empty($filename)){
			 	try {
			 		if(!$im = new imagick($filename)){
			 			throw new Exception('Could not create thumb');
			 		}
				 	$imageprops = $im->getImageGeometry();
				 	$width = $imageprops['width'];
				 	$height = $imageprops['height'];
				 	// dd($width,$height);

				 	if($height > $width) {
						$ratio=$height/$width;
						$newHeight=230;
						$newWidth=230 / $ratio;
					} else {
						$ratio=$width/$height;
						$newWidth=230;
						$newHeight=230 / $ratio;
					}
				 	// if($width > $height){
				 	//     $newHeight = 200;
				 	//     $newWidth = (200 / $height) * $width;
				 	// }else{
				 	//     $newWidth = 200;
				 	//     $newHeight = (200 / $width) * $height;
				 	// }

					$x=128-($newWidth/2);
					$y=128-($newHeight/2);

				 	$im->resizeImage($newWidth,$newHeight, imagick::FILTER_LANCZOS, 0.9, true);
				 	// $im->cropImage (200,200,0,0);
				 	//$string =explode('.', $name);

				 	$image->setImageColorspace($im->getImageColorspace() ); 
				 	$image->compositeImage($im, Imagick::COMPOSITE_DEFAULT, $x, $y);
				 	//$image->compositeImage($im, Imagick::COMPOSITE_DEFAULT, 10, 10);
				 	// $path=$folder.$name.'.'.$info['extension'];
				 	$path=$folder.$name.'.jpg';
				 	// echo $path;
				 	$image->setImageFormat('jpg');
				 	$image->writeImage($path); //replace original background

				 	}catch (Exception $e) {
					echo "Can not create image for $filename";
				}

			}
	}
	//$data=array('comp_logo',$path);
	
	//$mysqli->where('comp_id',$row['comp_id']);
	//$mysqli->update("mstr_company_info",$data);

// }