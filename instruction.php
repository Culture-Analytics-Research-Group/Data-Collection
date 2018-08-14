<!DOCTYPE php>
<html>

<head>

	<title>Instructions</title>
	<link rel="stylesheet" type="text/css" href="css/survey_css.css" />


</head>

<body>

<?php
$job = $_GET["load"];

if($job == "crop"){
	$instructions = "How to Crop Images.pdf";
}

if($job == "tag"){
	$instructions = "How to Classify an Image.pdf";
}
	
echo '<h1>Instructions</h1>
	  <div id="contentDiv">
	  <iframe style="width: 100%; height:100%; " src="'.$instructions.'"></iframe>
	  </div>
	  <div align="center">
	  <form action="./survey.php" method="get" target="_self">
	  <input type="radio" name="load" value="'.$job.'" required>I have read and understood these instructions.<br><br>
	  <button type="submit">Next</button> 
	  </form>
	  </div>';
?>

</body>
</html>