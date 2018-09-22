<?php

	#remote connection variables
	
	#local connection variables
	//$servername = "localhost";
	//$username = "root";
	//$password = '';
	//$db = "time_data"; #whatever local database is being used

	$connection = mysqli_connect($servername, $username, $password, $db);

	$select_query = "SELECT `page_file` FROM `pages` WHERE `group_num` IS NULL AND TIMEDIFF(NOW(), `timestamp`) > '01:20:00';";
	$result = mysqli_query($connection, $select_query);


	while($row = mysqli_fetch_array($result)){
		$junk_page[] = $row[0];
	}
	
	#parse file name
	foreach($junk_page as $junk){
		$page = chop($junk, ".jpg");
		$year = substr($page, 0, 4);
		$month = substr($page, 5, 2);
		$day = substr($page, 8, 2);
		$page_number = substr($page, 16);

		$int_month=(int)$month;
		$int_day=(int)$day;
		
		$select_query =	"SELECT `image` FROM `data` WHERE `year` = $year AND `month`=$int_month AND `day`=$int_day AND `page` = $page_number;";
		$result = mysqli_query($connection, $select_query);
		while($row = mysqli_fetch_array($result)){

		//enter your path name
			$path = "/home/onewoma8/public_html/Magazine_Project/TIMEvault/faces/".$year."-".$month."-".$day."/".$row[0];
			unlink($path);
			echo "$path <br>";
		}
		
		$delete_query =	"DELETE FROM `data` WHERE `year` = $year AND `month`=$int_month AND `day`=$int_day AND `page` = $page_number;";
		mysqli_query($connection, $delete_query);

	}

	$update_query1 = "UPDATE `pages` SET `faces` = NULL, `working` = 0, `timestamp` = NULL WHERE `group_num` IS NULL AND TIMEDIFF(NOW(), `timestamp`) > '01:20:00';";
	$update_query2 = "UPDATE `ground_truth_crop` SET `working` = 0, `timestamp` = NULL WHERE TIMEDIFF(NOW(), `timestamp`) > '01:20:00';";
	mysqli_query($connection, $update_query1);
	mysqli_query($connection, $update_query2);

	print("done!");

	
?>