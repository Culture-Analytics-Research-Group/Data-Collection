<?php
$job = $_GET["load"];

if($job == "crop"){
	$batch_size = 50; 
	$check = array(16, 32, 46);
	global $face_total;
	$face_total = 0;
}
if($job == "tag"){
	$batch_size = 25;
	$check = array(5, 16, 22);
}

global $file_array, $check_array;
$file_array = array();
$check_data1 = array();
$check_data2 = array();
$check_data3 = array();

function db_connect (){
	#$servername = "localhost";
	#$username = "onewoma8_TIME";
	#$password = '#8dL_lC0{y5z';
	#$db = "onewoma8_TIME";
	
	$servername = "localhost";
	$username = "root";
	$password = '';
	$db = "time_data";
		
	$connection = mysqli_connect($servername, $username, $password, $db);
	
	if (mysqli_connect_errno()){
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	return $connection;
}

function select($job, $batch_size, $connection){
	
	if($job == "crop"){
		$count_query1 = "SELECT COUNT(*) FROM `pages` WHERE `faces` IS NULL AND `group_num` IS NULL AND `working`=1;";
		$count_query2 = "SELECT COUNT(*) FROM `pages` WHERE `faces` IS NULL AND `group_num` IS NULL;";
		$select_query = "SELECT `page_file` FROM `pages` WHERE `faces` IS NULL AND `group_num` IS NULL AND `working`= 0 ORDER BY RAND() LIMIT 1;";
	}

	if($job == "tag"){
		$count_query1 = "SELECT COUNT(*) FROM `data` WHERE `gender` IS NULL AND `working`=1;";
		$count_query2 = "SELECT COUNT(*) FROM `data` WHERE `gender` IS NULL;";
		$select_query = "SELECT `image` FROM `data` WHERE `gender` IS NULL AND `working`=0 ORDER BY RAND() LIMIT 1;";
	}

	
	$result = mysqli_query($connection, $count_query1);
	$row = mysqli_fetch_array($result);
	$count1 = $row[0];
	
	$result = mysqli_query($connection, $count_query2);
	$row = mysqli_fetch_array($result);
	$count2 = $row[0];

	if(($count2/$batch_size) > ($count1)){
		echo "<h1>Page requests are at capacity. <br> Please try again later.</h1>";
		exit;
	}
	
	$result = mysqli_query($connection, $select_query);
	$row = mysqli_fetch_array($result);
	$file_name = $row[0];
	
	if($job == "crop"){
		$update_query = "UPDATE `pages` SET `working` = 1, `timestamp` = NOW() WHERE `page_file` = '$file_name';";
	}

	if($job == "tag"){
		$update_query = "UPDATE `data` SET `working` = 1, `timestamp` = NOW() WHERE `image` = '$file_name';";	
	}
	
	mysqli_query($connection, $update_query);
	
	return $file_name;
	
}

function parse_filename($job, $filename){
	$year = substr($filename, 0, 4);
	$month = substr($filename, 5, 2);
	$day = substr($filename, 8, 2);
	
	$page_file_source = $year."/".$year."-".$month."-".$day."/";
	
	if($job == "crop"){
		global $face_total;
		$filename = chop($filename, ".jpg");
		$page_number = substr($filename, 16);
		$crop_filename = $year."-".$month."-".$day." page".$page_number." face1.jpg";
		$file_data = array($year,$month,$day,$page_number,$crop_filename,$page_file_source,$face_total);
	}

	if($job == "tag"){
		$filename = chop($filename, "face0..9.jpg");
		$page_number = (int)substr($filename, 15);
		$page_filename = $year."-".$month."-".$day." page ".$page_number.".jpg";
		$face_file_source = "faces/".$year."-".$month."-".$day."/";
		$file_data = array($year,$month,$day,$page_number,$page_filename,$page_file_source,$face_file_source);
	}
	return $file_data;
}

function hidden($job, $batch_current, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3){
	
	echo '<input type="hidden" name="batch_current" value="'.$batch_current.'">
		 <input type="hidden" name="filename" value="'.$filename.'">
		 <input type="hidden" name="file_data" value="'.implode(',',$file_data).'">
		 <input type="hidden" name="file_array" value="'.implode(',',$file_array).'">
		 <input type="hidden" name="check_data1" value="'.implode(',',$check_data1).'">
		 <input type="hidden" name="check_data2" value="'.implode(',',$check_data2).'">
		 <input type="hidden" name="check_data3" value="'.implode(',',$check_data3).'">';
	
	if($job == "crop"){
		echo '<input type="hidden" name="x1" value="">
			 <input type="hidden" name="y1" value="">
			 <input type="hidden" name="x2" value="">
			 <input type="hidden" name="y2" value="">
			 <input type="hidden" name="img_width" value="">
			 <input type="hidden" name="img_height" value="">';
	}	
}

function display($job, $file_data){
	echo '<h2>'.$file_data[1].'&nbsp/'.$file_data[2].'&nbsp/'.$file_data[0].'&nbsp Page:'.$file_data[3].'</h2>';
	
	if($job == "crop"){
		echo '<p class="instructions"> If there is one face or more on this page, click and drag the mouse over the image to select a face. </p>
			 <p class="instructions"> If there are no faces at all on the page, select \'There are no faces on this page\' and then click the \'Done with this page\' button. </p> <br>
			 
			 <input id = "NO_FACE_ON_PAGE" type="radio" name="face" value="0" required onclick = "hide();"> 
			 <div class="radio_text" id="NoFaceText" onclick = "check(\'NO_FACE_ON_PAGE\'); hide();"> 
			 There are no clearly visible faces on this page </div> <br>

			 <input id = "FACE_ON_PAGE" type="radio" name="face" value="1" required onclick = "show(); check_inputs();"> 
			 <div class="radio_text" id="FaceText" onclick = "check(\'FACE_ON_PAGE\'); show(); check_inputs();">
			 There is at least one <b style="font-size: 1.1em">clearly visible</b> face on this page</div> <br><br>

			 <button id = "another_button" type="submit" name="submitted" value="another"> There\'s another <span style="font-size: 1.2em;">clearly visible </span> face on this page. </button> 
			 <div class="instructions" id="another_instructions"> If you have more faces to crop and tag on this page, click here.</div><br>
			 <button id = "submit_button" type="submit" name="submitted" value="submit"> <span style="font-size: 1.2em;">Done with this page.</span> </button> 
			 <div class="instructions" id="done_instructions"> If there is only one face, or if you have just selected the last face on this page, click here.</div> ';
		
		
	}

	if($job == "tag"){
		echo '<p class="instructions" style="margin-top: 35px; font-size: 25px;"> The image on the left features a selected face, <br> marked by a red rectangle. </p> 
			  <p class="instructions"> Answer the following questions about the selected face as best as you can.</p> <br>
			 	 
			  <span class="titles">Is the selected face depicted in full color or in a single shade (in monochrome)?<br></span>
			  <input id = "Color" type="radio" name="colorSpace" value="1" required><label onclick = "check(\'Color\')">In color</label><br>
			  <input id = "Monochrome" type="radio" name="colorSpace" value="0" required><label onclick = "check(\'Monochrome\')">Monochrome (examples: black & white, sepia)</label><br><br>
			  
			  
			  <span class="titles">Is the face a photo of a person or an illustration?<br></span>
			  <input id = "Photo" type="radio" name="photoVsDrawing" value="1" required><label onclick = "check(\'Photo\')" >Photo</label><br>
			  <input id = "Drawing" type="radio" name="photoVsDrawing" value="0" required><label onclick = "check(\'Drawing\')" >Illustration</label><br><br>
			  
			  
			  <span class="titles">Is the face in profile, or is it facing forward?<br></span>
			  <input id= "Straight" type="radio" name="straightVsProfile" value="1" required><label onclick = "check(\'Straight\')" >Facing forward</label><br>
			  <input id= "Profile" type="radio" name="straightVsProfile" value="0" required><label onclick = "check(\'Profile\')" >In profile</label><br><br>
			  
			  
			  <span class="titles">Is the face smiling?<br></span>
			  <input id= "Smile" type="radio" name="smile" value="1" required><label onclick = "check(\'Smile\')" >Yes</label><br>
			  <input id= "No_Smile" type="radio" name="smile" value="0" required><label onclick = "check(\'No_Smile\')" >No</label><br><br>							
			  
			  <span class="titles">What is the gender of the face?<br></span>
			  <input id="Male" type="radio" name="gender" value="male" required><label onclick = "check(\'Male\')" >Male</label><br>
			  <input id="Female" type="radio" name="gender" value="female" required><label onclick = "check(\'Female\')" >Female</label><br>
			  <input id="MaleFemale" type="radio" name="gender" value="unknown" required><label onclick = "check(\'MaleFemale\')" >Unknown</label><br><br>
			  
			  <span class="titles">What is the Race of the person whose face is selected? <br></span>
			  <input id="White" type="radio" name="race" value="white" required><label onclick = "check(\'White\')" >White <span style= "font-size: 0.8em;">(including Hispanic and Middle Eastern)</span></label><br>
			  <input id="Black" type="radio" name="race" value="black" required><label onclick = "check(\'Black\')" >Black</label><br>
			  <input id="Asian" type="radio" name="race" value="asian" required><label onclick = "check(\'Asian\')" >Asian</label><br>
			  <input id="Indian" type="radio" name="race" value="americanindian" required><label onclick = "check(\'Indian\')" >American Indian</label><br>
			  <input id="Islander" type="radio" name="race" value="pacificislander" required><label onclick = "check(\'Islander\')" >Pacific Islander</label><br>
			  <input id="NoRace" type="radio" name="race" value="unknown" required><label onclick = "check(\'NoRace\')" >Unkown</label><br><br>
			  
			  
			  <span class="titles">Does the face belong to an adult or child?<br></span>
			  <input id="Adult" type="radio" name="adult" value="1" required><label onclick = "check(\'Adult\')" >Adult</label><br>
			  <input id="Child" type="radio" name="adult" value="0" required><label onclick = "check(\'Child\')" >Child</label><br><br>
			  
			  <span class="titles">Rate the image-quality of the selected face.<br></span>
			  <input id= "Good" type="radio" name="quality" value="good" required><label onclick = "check(\'Good\')" >Good (the face is clearly readable)</label><br>
			  <input id= "Fair" type="radio" name="quality" value="fair" required><label onclick = "check(\'Fair\')" >Fair (the face is small or blurry, but still readable)</label><br>
			  <input id= "Poor" type="radio" name="quality" value="poor" required><label onclick = "check(\'Poor\')" >Poor (the face is difficult to read - too small and/or too blurry) </label><br>
			  <input id= "Unreadable" type="radio" name="quality" value="discard" required><label onclick = "check(\'Unreadable\')" >Unreadable or not human or incorrect</label><br><br>
			  
			 		 
			  <p class = "instructions"> Answer these questions about the image that contains the selected face. </p>
			  
			  <span class="titles">What best describes this image?<br></span>
			  <input id="ad" type="radio" name="category" value="ad"  required><label onclick = "check(\'ad\')">This is an advertisement</label><br>
			  <input id="feature" type="radio" name="category" value="feature"  required><label onclick = "check(\'feature\')">This is part of a feature story</label><br>
			  <input id="cover" type="radio" name="category" value="cover"  required><label onclick = "check(\'cover\')">This is the magazine cover</label><br>
			  <input id = "author" type="radio" name="category" value="author"  required><label onclick = "check(\'author\')">This is a photo of the article\'s author</label><br><br>
			 			 
			  
			  <span class="titles">Is the selected face within a single-person portrait, or is there more than one person in the image?</span><br>
			  <input id ="multiface" type="radio" name="multiface" value="1" required><label onclick = "check(\'multiface\')">There are two or more people in the image.</label><br>
			  <input id= "portrait" type="radio" name="multiface" value="0" required><label onclick = "check(\'portrait\')">The image is a portrait of a single person</label><br><br>
			  
			  <input type="submit" name="submitted" value="submit"> <br><br>';
	}	
}

function post_variables($job){
	global $refresh_check, $batch_current, $submitted, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3;
	$refresh_check = false;

	$batch_current = $_POST["batch_current"];
	$filename = $_POST["filename"];
	$file_data = explode(',',$_POST["file_data"]);
	
	$submitted = $_POST["submitted"];
	$file_array = explode(',',$_POST["file_array"]);
	$check_data1 = explode(',',$_POST["check_data1"]);
	$check_data2 = explode(',',$_POST["check_data2"]);
	$check_data3 = explode(',',$_POST["check_data3"]);
	
	if($job == "crop"){
		global $face, $x1, $x2, $y1, $y2, $img_width, $img_height, $sizex, $sizey;
		$face = $_POST["face"];
		$x1 = $_POST["x1"];	
		$y1	= $_POST["y1"];
		$x2	= $_POST["x2"];
		$y2	= $_POST["y2"];
		$img_width = $_POST["img_width"];
		$img_height = $_POST["img_height"];
		$sizex = $x2-$x1;
		$sizey = $y2-$y1;
	}
	
	if($job == "tag"){
		global $color, $photo, $angle, $gender, $race, $adult, $smile, $quality, $multiface, $category;
		$color = $_POST["colorSpace"];
		$photo = $_POST["photoVsDrawing"];
		$angle = $_POST["straightVsProfile"];	
		$gender = $_POST["gender"];	
		$race =	$_POST["race"];
		$adult = $_POST["adult"];	
		$smile = $_POST["smile"];	
		$quality = $_POST["quality"];	
		$multiface = $_POST["multiface"];
		$category = $_POST["category"];
	}
	
	if($job=="demo"){
		global $age, $gender, $race, $location;
		$age = $_POST["age"];	
		$gender = $_POST["gender"];	
		$race =	$_POST["race"];
		$location = $_POST["location"];	
	}

}

function submit($job, $connection){
	global $filename;
	if($job == "crop"){
		global $file_data, $x1, $x2, $y1, $y2, $face, $batch_current;
		if($batch_current == $check[0] or $batch_current == $check[1] or $batch_current == $check[2]){
			if($submitted != "another"){
				$delet_query = "DELETE FROM `crop_check` WHERE `year` = $year AND `month` = $month AND `day` = $day AND `page` = $page_number;";
				mysqli_query($connection, $delete_query);
			}
			else{	
				$insert_query = "INSERT INTO `crop_check` (`year`, `month`, `day`, `page`, `image`, `x1`, `y1`, `x2`, `y2`) VALUES(".$file_data[0].",
								".$file_data[1].",".$file_data[2].",".$file_data[3].",'".$file_data[4]."', $x1, $y1, $x2, $y2);";
				if($face == 1){
					mysqli_query($connection, $insert_query);
				}
			}
		}
		else
		{
			$insert_query = "INSERT INTO `data` (`year`, `month`, `day`, `page`, `image`, `x1`, `y1`, `x2`, `y2`) VALUES(".$file_data[0].",
							".$file_data[1].",".$file_data[2].",".$file_data[3].",'".$file_data[4]."', $x1, $y1, $x2, $y2);";
			$update_query = "UPDATE `pages` SET `faces` = ".$file_data[6].",`working`= 0 WHERE `page_file` = '$filename';";
	
			if($face == 1){
				mysqli_query($connection, $insert_query);
			}
			mysqli_query($connection, $update_query);		
		}
		
	}

	if($job == "tag"){
		global $color, $photo, $angle, $gender, $race, $adult, $smile, $quality, $multiface, $category;
		$insert_query = "UPDATE `data` SET `color`= $color, `photo`=$photo, `angle`=$angle, `gender`='$gender', `race`='$race', 
						`adult`=$adult, `smile`=$smile, `quality`='$quality', `multiface`=$multiface, `category`='$category' WHERE `image` = '$filename';";
		$update_query = "UPDATE `data` SET `working`= 0 WHERE `image` = '$filename';";
		mysqli_query($connection, $insert_query);
		mysqli_query($connection, $update_query);	
	}
}

function final_submit($job, $connection){
	global $age, $gender, $race, $location, $file_array, $check_data1, $check_data2, $check_data3;
	if($job == "crop"){
		$group_select = "SELECT MAX(`group_num`) FROM `crop_groups`";
	}
	if($job == "tag"){
		$flag = 0;
		$group_select = "SELECT MAX(`tag_group`) FROM `tag_groups`";	
	}
	
	$result = mysqli_query($connection, $group_select);
	$row = mysqli_fetch_array($result);
	$group = $row[0];
	$group++;	
	
	$code = rand(1000000, 9999999);
	
	$check_data = array($check_data1, $check_data2, $check_data3);
	
	foreach($check_data as $check_entry){
		if($job == "crop"){
			$check_flag_query = "SELECT `nFaces` FROM `ground_truth_crop` WHERE `File`='".$check_entry[0]."';";
			$result = mysqli_query($connection, $check_flag_query);
			$row = mysqli_fetch_array($result);
			$count = $row[0];
			
			$check_array[] = $check_entry[0];
			$faces_array[] = $check_entry[1];
			$flag_array[] =  $check_entry[1]-$count;		
		}
		if($job == "tag"){
			$check_query = "SELECT `gender`, `color`, `photo`  FROM `ground_truth` WHERE `image` = '".$check_entry[10]."';";
			$result = mysqli_query($connection, $check_query);
			$row = mysqli_fetch_array($result);
			$check_gender = $row[0];
			$check_color = $row[1];
			$check_photo = $row[2];
			$flag = 0;
			
			if ($check_gender!=$check_entry[3]){$flag++;}
			if ($check_color!=$check_entry[0]){$flag++;}
			if ($check_photo!=$check_entry[1]){$flag++;}

			$check_array[] = $check_entry[10];
			$flag_array[] = $flag;
			
			$insert_check_query = "INSERT INTO `tag_check` (`tag_group`, `color`, `photo`, `angle`, `gender`, `race`, `adult`, `smile`, `quality`, `image`) VALUES ($group,".$check_entry[0].",".$check_entry[1].",".
								  $check_entry[2].",'".$check_entry[3]."','".$check_entry[4]."',".$check_entry[5].",".$check_entry[6].",'".$check_entry[7]."','".$check_entry[10]."');";
			mysqli_query($connection, $insert_check_query);	
		}
	}

	if($job == "crop"){
		$insert_flag_query = "INSERT INTO crop_groups (group_num, flag, flag2, flag3, faces1, faces2, faces3, check1, check2, check3, code) VALUES($group, $flag_array[0], $flag_array[1],
							 $flag_array[2], $faces_array[0], $faces_array[1], $faces_array[2], '$check_array[0]', '$check_array[1]', '$check_array[2]', $code);";
							 
		$demo_query = "INSERT INTO `demographics` (`crop_group`, `age`, `race`, `gender`, `location`) VALUES ($group, '$age', '$race', '$gender', '$location');";	
	}

	if($job == "tag"){
		$insert_flag_query = "INSERT INTO `tag_groups` (`tag_group`, `flag`, `flag2`, `flag3`, `check1`, `check2`, `check3`, `code`) VALUES($group, 
							 $flag_array[0], $flag_array[1], $flag_array[2], '$check_array[0]', '$check_array[1]', '$check_array[2]', $code);";
		$demo_query = "INSERT INTO `demographics` (`tag_group`, `age`, `race`, `gender`, `location`) VALUES ($group, '$age', '$race', '$gender', '$location');";
	}
	
	mysqli_query($connection, $insert_flag_query);
	mysqli_query($connection, $demo_query);
	
	foreach($file_array as $filename){
		if($job == "crop"){
			$update_group_query = "UPDATE `pages` SET `group_num` = $group, `timestamp` = NULL WHERE `page_file` = '$filename';";
			mysqli_query($connection, $update_group_query);
		}
		if($job == "tag"){
			$update_group_query = "UPDATE `data` SET `tag_group`= $group, `timestamp` = NULL WHERE `image` = '$filename';";
			mysqli_query($connection, $update_group_query);	
		}
	}
	
	return $code;
}

function post_hidden(){
	global $refresh_check, $submitted, $batch_current, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3;
	
	echo '<input type="hidden" name="refresh_check" value="'.$refresh_check.'">
		 <input type="hidden" name="submitted" value="'.$submitted.'">
		 <input type="hidden" name="batch_current" value="'.$batch_current.'">
		 <input type="hidden" name="filename" value="'.$filename.'">
		 <input type="hidden" name="file_data" value="'.implode(',',$file_data).'">
		 <input type="hidden" name="file_array" value="'.implode(',',$file_array).'">
		 <input type="hidden" name="check_data1" value="'.implode(',',$check_data1).'">
		 <input type="hidden" name="check_data2" value="'.implode(',',$check_data2).'">
		 <input type="hidden" name="check_data3" value="'.implode(',',$check_data3).'">';
	
}

function check_select($job, $connection){
	if($job == "crop"){
		$select_query = "SELECT `File` FROM `ground_truth_crop` ORDER BY RAND() LIMIT 1;";
	}

	if($job == "tag"){
		$select_query = "SELECT `image` FROM `ground_truth` ORDER BY RAND() LIMIT 1;";
	}	
	
	$result = mysqli_query($connection, $select_query);
	
	$row = mysqli_fetch_array($result);	
	$filename = $row[0];
	return $filename;
}

function crop_image(){
	global $filename, $file_data, $x1, $x2, $y1, $y2, $img_width, $img_height;
	$path = "faces/".$file_data[0]."-".$file_data[1]."-".$file_data[2];
	$img_height = $_POST["img_height"];
	$image_size = getimagesize($file_data[5].$filename);
	$w = $image_size[0];
	$h = $image_size[1];

	$img_width=$w*$img_height/$h;
	
	if(!file_exists($path)){
		mkdir($path);
	}
	
	$x1 = $x1*$w/$img_width;
	$x2 = $x2*$w/$img_width;
	$y1 = $y1*$h/$img_height;
	$y2 = $y2*$h/$img_height;

	$window = array('x' => $x1, 'y' => $y1, 'width' => ($x2-$x1) , 'height' => ($y2-$y1));
	
	$crop = imagecreatefromjpeg($file_data[5].$filename);
	$crop = imagecrop($crop, $window);
	imagejpeg($crop, $path."/".$file_data[4]);
	imagedestroy($crop);
}

function demographic($job, $file_array, $check_data1, $check_data2, $check_data3){
	echo '<form id="demo" class = "input" action="./post.php?load='.$job.'&load2=demo" method="post" target="_self">
		 <br>

		<span class="instructions"> Your Gender: </span> <br>
		<input type="radio" name="gender" value="male" required>Male<br>
		<input type="radio" name="gender" value="female" required>Female<br>
		<input type="radio" name="gender" value="unknown" required>Neither<br><br>

		<span class="instructions"> Your Race: </span> <br>
		<input type="radio" name="race" value="white" required>White<br>
		<input type="radio" name="race" value="black" required>Black<br>
		<input type="radio" name="race" value="asian" required>Asian<br>
		<input type="radio" name="race" value="americanindian" required>American Indian<br>
		<input type="radio" name="race" value="pacificislander" required>Pacific Islander<br>
		<input type="radio" name="race" value="unknown" required>None of the above, mixed, or unknown<br><br>

		<span class="instructions"> Your age: </span> <br>
		<input type="radio" name="age" value="18-25" required>18-25<br>
		<input type="radio" name="age" value="26-35" required>26-35<br>
		<input type="radio" name="age" value="36-45" required>36-45<br>
		<input type="radio" name="age" value="46-55" required>46-55<br>
		<input type="radio" name="age" value="56-65" required>56-65<br>
		<input type="radio" name="age" value="66+"   required>66+<br><br>
		
		<span class="instructions"> Your location: </span> <br> 
		<input type="radio" name="location" value="Asia" required>Asia (including Turkey and Indonesia, excluding Russia and Egypt)<br>
		<input type="radio" name="location" value="Europe" required>Europe (including Russia)<br>
		<input type="radio" name="location" value="Africa" required>Africa (including the Sinai Peninsula in Egypt)<br>
		<input type="radio" name="location" value="North America" required>North America (including Central America and the Caribbean)<br>
		<input type="radio" name="location" value="South America" required>South America<br>
		<input type="radio" name="location" value="Antarctica" required>Antarctica<br>
		<input type="radio" name="location" value="Oceania" required>Australia and Oceania<br><br>
		
		<input type="submit" name="submitted" value="submit"> <br><br>
		
		<input type="hidden" name="file_array" value="'.implode(',',$file_array).'">
		<input type="hidden" name="check_data1" value="'.implode(',',$check_data1).'">
		<input type="hidden" name="check_data2" value="'.implode(',',$check_data2).'">
		<input type="hidden" name="check_data3" value="'.implode(',',$check_data3).'">
		
		 </form>';
	exit;
}

function coverfaces($job, $connection, $filename, $file_data, $batch_current){
	if($job == "crop"){
		if($batch_current == $check[0] or $batch_current == $check[1] or $batch_current == $check[2]){
			$coordinate_query = "SELECT `x1`, `y1`, `x2`, `y2` FROM `crop_check` WHERE `year` = ".$file_data[0]." 
								AND `month`=".$file_data[1]." AND `day`=".$file_data[2]." AND `page` = ".$file_data[3].";";
		}
		else{
			$coordinate_query = "SELECT `x1`, `y1`, `x2`, `y2` FROM `data` WHERE `year` = ".$file_data[0]." 
								AND `month`=".$file_data[1]." AND `day`=".$file_data[2]." AND `page` = ".$file_data[3].";";
		}
		$path = $file_data[5].$filename;
	}
	if($job == "tag"){
		$coordinate_query = "SELECT `x1`, `y1`, `x2`, `y2` FROM `data` WHERE `image` = '$filename';";
		$path = $file_data[5].$file_data[4];
	}
	$result = mysqli_query($connection, $coordinate_query);
	
	$image_size = getimagesize($path);
	$w = $image_size[0];
	$h = $image_size[1];
	$counter=0;
	
	echo '<script>
		 var height = document.getElementById("page").offsetHeight;
		 var width = '.$w.'*height/'.$h.';
		 var x1=[]; var x2=[]; var y1=[]; var y2=[]; </script>';
	while($coordinates = mysqli_fetch_array($result)){
		$counter++;

		echo '<script> 
		x1.push('.$coordinates[0].'*width/'.$w.');
		y1.push('.$coordinates[1].'*height/'.$h.');
		x2.push('.$coordinates[2].'*width/'.$w.');
		y2.push('.$coordinates[3].'*height/'.$h.');
		</script>';
	}
	
	for ($q=0; $q<$counter; $q++){
		if($job == "crop"){
			echo '<script>
			 var i = '.$q.';
			 var x = x1[i]+(document.body.clientWidth)/2.0- width;
			 var y = y1[i];
			 var w = x2[i]-x1[i];
			 var h = y2[i]-y1[i];
			var rectangle = document.createElement("DIV");

				rectangle.style.backgroundColor="#1dff00";
				rectangle.style.border= "none";
				rectangle.style.position="absolute";
				rectangle.style.marginLeft="-10px";
				rectangle.style.marginTop="5px";
				rectangle.style.left=x+"px";
				rectangle.style.top=y+"px";
				rectangle.style.width=w+"px";
				rectangle.style.height=h+"px";
				rectangle.style.zIndex=2;

			document.body.appendChild(rectangle);
			</script>';
		}
		if($job == "tag"){
			echo '<script>
			 var i = '.$q.';
			 var x = x1[i]+(document.body.clientWidth)/2.0- width;
			 var y = y1[i];
			 var w = x2[i]-x1[i];
			 var h = y2[i]-y1[i];
			var rectangle = document.createElement("DIV");

				rectangle.style.backgroundColor="rgba(29,255,0,0.1)";
				rectangle.style.border= "thick solid #1dff00";
				rectangle.style.position="absolute";
				rectangle.style.marginLeft="-10px";
				rectangle.style.marginTop="5px";
				rectangle.style.left=x+"px";
				rectangle.style.top=y+"px";
				rectangle.style.width=w+"px";
				rectangle.style.height=h+"px";
				rectangle.style.zIndex=2;

			document.body.appendChild(rectangle);
			</script>';
		}
	}	
	
}













?>

