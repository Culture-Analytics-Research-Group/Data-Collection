<?php

	#remote connection variables
	
	#local connection variables
	$servername = "localhost";
	$username = "root";
	$password = '';
	$db = ""; #whatever local database is being used

	$connection = mysqli_connect($servername, $username, $password, $db);

	
	
	$select_query = "SELECT `image` FROM `data` WHERE `tag_group` = 0 AND TIMEDIFF(NOW(), `timestamp`) > '01:00:00';";
	$result = mysqli_query($connection, $select_query);
	
	while($row = mysqli_fetch_array($result)){
		$junk_page[] = $row[0];
	}
	
	foreach($junk_page as $junk){
		$delete_query =	"UPDATE `data` SET `color` = NULL, `photo` = NULL, `angle` = NULL, `gender` = NULL, `race` = NULL, `adult` = NULL, `smile` = NULL, `quality` = NULL, `working`=0, `timestamp`= NULL  WHERE `image` = '$junk'";
		mysqli_query($connection, $delete_query);
	}

	
	$update_query = "UPDATE `ground_truth` SET `working` = 0, `timestamp` = NULL WHERE TIMEDIFF(NOW(), `timestamp`) > '01:00:00';";
	mysqli_query($connection, $update_query);
	
	
?>