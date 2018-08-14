<?php
session_name("phpsurvey");
session_start();
?>
<!DOCTYPE php>
<html>

<head>

	<title>Survey</title>

	<link rel="stylesheet" type="text/css" href="css/imgareaselect-default.css" />
	<link rel="stylesheet" type="text/css" href="css/survey_css.css" />
    <script type="text/javascript" src="scripts/jquery.min.js"></script>
    <script type="text/javascript" src="scripts/jquery.imgareaselect.pack.js"></script>
    <script type="text/javascript" src="scripts/survey_script.js"></script> 
	
</head>
<body onload="auto_submit()">
<?php
include 'functions.php';

if (isset($_SESSION['time_begin']) && (time() - $_SESSION['time_begin'] > 7200)) {
	
	session_unset();     
	session_destroy();   
	
	echo '<h1 style="text-align: center;">Session has timed out due to 2 hours of inactivity.</h1>';
	exit;
}
$_SESSION['time_begin'] = time(); 

$job = $_GET["load"];
if(isset($_GET["load2"])){
	$job = $_GET["load2"];
}
$connection = db_connect();
post_variables($job);

if($batch_current < $batch_size){
	if($batch_current == $check[0] or $batch_current == $check[1] or $batch_current == $check[2]){
		if($job == "crop"){
			if(($x1=="" or $sizex==0 or $sizey==0) and $face=="1"){
				echo '<script> alert("Please click and drag the mouse over the image to select a face.") </script>';
				$submitted = "another";
			}
			else{
				$check_data = $_SESSION["check_data"];
				if($face=="1"){
					$face_total = $file_data[6]+1;
					$file_data[6] = $face_total;
				}
	
				if($batch_current == $check[0]){
					$check_data1 = array("filename"=>$filename, "face_total"=>$face_total);
				}
				elseif($batch_current == $check[1]){
					$check_data2 = array("filename"=>$filename, "face_total"=>$face_total);
				}
				else{
					$check_data3 = array("filename"=>$filename, "face_total"=>$face_total);
				}
				
			}
			submit($job, $connection);
			$_SESSION["refresh_check"] = false;
		}
		if($job == "tag"){
			if($batch_current == $check[0]){
				$check_data1 = array("color"=>$color, "photo"=>$photo, "angle"=>$angle, "gender"=>$gender, "race"=>$race, "adult"=>$adult, 
								"smile"=>$smile, "quality"=>$quality, "multiface"=>$multiface, "category"=>$category, "filename"=>$filename);
			}
			elseif($batch_current == $check[1]){
				$check_data2 = array("color"=>$color, "photo"=>$photo, "angle"=>$angle, "gender"=>$gender, "race"=>$race, "adult"=>$adult, 
								"smile"=>$smile, "quality"=>$quality, "multiface"=>$multiface, "category"=>$category, "filename"=>$filename);
			}
			else{
				$check_data3 = array("color"=>$color, "photo"=>$photo, "angle"=>$angle, "gender"=>$gender, "race"=>$race, "adult"=>$adult, 
								"smile"=>$smile, "quality"=>$quality, "multiface"=>$multiface, "category"=>$category, "filename"=>$filename);
			}
			
			$_SESSION["refresh_check"] = false;								
		}
	}
	else{
		if($job == "crop"){
			if(($x1=="" or $sizex==0 or $sizey==0) and $face=="1"){
				echo '<script> alert("Please click and drag the mouse over the image to select a face.") </script>';
				$submitted = "another";
			}
			else{
				if($face=="1"){
					$face_total = $file_data[6]+1;
					$file_data[6] = $face_total;
					crop_image();
				}
				
				submit($job, $connection);
				$_SESSION["refresh_check"] = false;
				
				if($submitted == "another"){
					$crop_filename = $file_data[0]."-".$file_data[1]."-".$file_data[2]." page".$file_data[3]." face".($file_data[6]+1).".jpg";
					$file_data[4] = $crop_filename;
				}
			}
		}
		if($job == "tag"){
			$_SESSION["refresh_check"] = false;
			submit($job, $connection);
		}
	}
	
	if($submitted != "another"){
		$batch_current++;
	}
}
else{
	$job = $_GET["load"];
	$code = final_submit($job, $connection);
}

echo '<form id="auto" action="./survey.php?load='.$job.'" method="post" target="_self">';
post_hidden();
if(isset($code)){
	echo '<input type="hidden" name="code" value="'.$code.'">';
}

echo'</form>';




?>
</body>
</html>