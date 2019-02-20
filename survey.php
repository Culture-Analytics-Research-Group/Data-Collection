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

<body>
<?php
if(!isset($refresh_check)){
	session_unset();
	$refresh_check = false;
	$_SESSION["refresh_check"] = false;
}

include 'functions.php';

$connection = db_connect();

$batch_current = 0;

if(!empty($_POST)){
	
	$refresh_check = $_POST["refresh_check"];
	$batch_current = $_POST["batch_current"];
	$filename = $_POST["filename"];
	$file_data = explode(',',$_POST["file_data"]);
	$submitted = $_POST["submitted"];
	$file_array = explode(',',$_POST["file_array"]);
	$check_data1 = explode(',',$_POST["check_data1"]);
	$check_data2 = explode(',',$_POST["check_data2"]);
	$check_data3 = explode(',',$_POST["check_data3"]);
	if($job == "crop"){
		if($submitted=="another"){
			echo '<script type="text/javascript"> again = true; </script>';
		}
		else{
			echo '<script type="text/javascript"> again = false; </script>';
		}
	}
}



if($batch_current < $batch_size){
	
	if(!empty($_POST) and $submitted == "another"){
	}
	else{
		if($refresh_check == false and $_SESSION["refresh_check"] == false){
			if($batch_current == $check[0] or $batch_current == $check[1] or $batch_current == $check[2]){
				$filename = check_select($job, $connection);
			}
			else{
				$filename = select($job, $batch_size, $connection, $batch_current);
				$file_array[] = $filename;

			}
			

			$file_data = parse_filename($job, $filename);
			
			
			$_SESSION["refresh_check"] = true;
			$_SESSION["filename"] = $filename;
			$_SESSION["file_data"] = $file_data;
		}
		$filename = $_SESSION["filename"];
		$file_data = $_SESSION["file_data"];
		
		
		
	}

}
else{
	if(!empty($_POST["code"])){
		echo "<h1>Your completion code is: ".$_POST["code"]."</h1>";
		exit;
	}
	else{
		echo '<form id="demo" class = "input" action="./post.php?load='.$job.'&load2=demo" method="post" target="_self">';
		demographic($job, $file_array, $check_data1, $check_data2, $check_data3);
		hidden($job, $batch_current, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3);
		echo '</form>';
		exit;
	}
}


?>

<div id="contentDiv">
<?php
	if($job == "crop"){
		echo '<img class="page" id="page" src="'.$file_data[5].$filename.'">';
		//echo '<img class="page" id="page" src="1961\1961-07-14\1961-07-14 page 10.jpg">';
		if(!empty($_POST) and $submitted == "another"){
			coverfaces($job, $connection, $filename, $file_data, $batch_current, $check);
		}
	}
	if($job == "tag"){
		echo '<img class="page" id="page" src="'.$file_data[5].$file_data[4].'">';
		coverfaces($job, $connection, $filename, $file_data, $batch_current, $batch_current, $check);
		#echo '<img class="face" src="'.$file_data[6].$filename.'">';
	}

?>
</div>

<div id="warning" onmouseover="set_warning()"> DO NOT NAVIGATE BACK!</div>
<?php echo '<div id="page_num"> Completed: <br><span style="font-size:20px;"> '.$batch_current.'</span></div>'; ?> 

<div id="interfaceDiv">
	
	<?php
		echo '<form action="./post.php?load='.$job.'" method="post" target="_self">';
		display($job, $file_data);
		hidden($job, $batch_current, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3);
		echo '</form>';
		if($job=="crop"){
			echo '<script>another()</script>';
		}
	?>

</div>

</body>
</html>