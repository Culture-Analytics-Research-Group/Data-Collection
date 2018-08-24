# Data-Collection

The original purpose of this code was for cropping and gathering data on faces in a magazine archive and therefore the data queries and variables in this code are based around that purpose. If this is to be used as a template for a similar task (such as cropping and gathering data on cars or trains, etc), the data queries and variable will require editing.   

This is a web interface for gathering data from images on a large scale. Users should serve it with accompanying writable sql databases. We provide the accompanying database structures here. This interface is platform independent in that you can collect data directly with it.  However, we find it useful to use Amazon Mechanical Turk to find participants.  To use it with Amazaon Mechanical Turk, we shared it as a survey link and had participants enter their completion code at the end of the survey, so that we could verify their work.

This package comprises three different data gathering surveys.

The first survey allows users to crop portions of an image. The first survey allows users to crop portions of an image.   (For the crop, we used https://github.com/odyniec/imgareaselect ‘imgareaselect’ by Michal Wojciechowski. )

The second survey allows users to classify the already cropped images from a selection of categories. 

The third survey is simply a demographics survey that allows users to enter their demographic information, and is presented at the end of each of the previous two surveys.


The code is split into 4 different files instructions.php, survey.php, post.php, and functions.php. 

instructions.php is a landing page that presents the user with instructions for 
the current survey either the cropping survey or the classify survey. 

survey.php is the main interface of the survey that the user interacts with. 

The survey and instructions that will be presented are determined by the GET variable load in the URL. If load=crop then the crop instructions are presented if load=tag then the classifying survey is presented. If using the instructions, users must select that they have read the instructions in order to move onto the survey.

If the job is to crop images the image to be cropped is presented and the question of if the object to be cropped is present (faces in the case of the original purpose) in the image. If the object is present users can crop it by clicking and dragging over the object in the image. If multiple objects are present users may select that there are more objects on the page. Any previous cropped objects will be covered when cropping another object. If it is not present users may simply select that the object is not there and move to the next image. 

If the job is classifying images that were previously cropped then the image they were cropped from is presented with the cropped portion highlighted along with questions about the
classification of the image. 

Each job consists of a group of images (the total number can be set in the functions.php file).  The user must complete the tasks on all in the images in order to complete the job.  We keep track of each job with a unique group number.  Each group of images includes three check points that can be set. The check points present the user with ‘ground truth pages’ – these are pages where the classification or number of objects cropped is already known.  These check points raise flags to users that are not doing the tasks honestly. All these variables can be set in functions.php.

post.php handles all submission of data to the data base after a user has hit the submit button. If the job was cropping data is submitted to the database and the selected portion is cropped and saved to a folder on the server. If the job was classifying data is just submitted to the database. If a user has completed a check page then information on the page is placed in an array to later be checked and entered at the end of the survey. If the user has reached the end of the survey and filled out the demographics information then the demographics data and check data is submitted and a completion code is generated. If a user has no activity for 2 hours and then tries to submit data post.php will cause the session to timeout.

functions.php contains all the functions that are used in the survey and is included in both survey.php and post.php

functions.php Overview

$batch_size - variable controlling the amount of images per job
$check - array variable that contains when ground truth images will be shown in the job

$face_total - varriable for cropping that keeps track of the number of objects cropped from a specific image

$file_array - holds image file names to have a group number added at the end of each job

$check_data1
$check_data2 - holds data submitted by users on ground truth images 
$check_data3

db_connect() - returns a mysqli_connection object for connecting to the database, set $servername, $username, $password, and $database you wish to connect to

select($job, $batch_size, $connection) - selects images one at a time as long as there is enough images available for another job, otherwise users are presented
with a message that requests are currently at capactity. This function also marks pages as being worked on in the database and adds a timestamp for clearing data 
on a job that was never finished. The file name of the image is returned

check_select($job, $connection) - similar to select, except it selects ground truth images from their tables and does not set them as being worked on
or set a timestamp as they are not needed for the ground truth images since no data is submitted for them unless the survey is completed

parse_filename($job, $filename)- parses information from the file name of the image. If the job is cropping then this information is used to creat the path that cropped
images will be stored in. If the job is classifying then this information is used to determine the path of the original image. The parse data is stored in the $file_data array to later be
displayed and submitted to the database. This function is based on the file name scheme of the images originally used with this code. 

display($job, $file_data)-  handles what is displayed for the user depending what the job is. Inputs for the survey questions are printed out as radio buttons

hidden($job, $batch_current, $filename, $file_data, $file_array, $check_data1, $check_data2, $check_data3)-  prints out the hidden inputs for each job mainly the data
parsed from the filename. If the job is cropping the hidden inputs containing information for cropping the data is printed out.

post_hidden()- prints out hidden inputs for post.php that need to be sent back to survey.php

crop_image()- handles the cropping of images for the crop job and accounts for offset of different window resolutions and sizes.

post_variables($job)- sets the variables in post that will be submmited to the database for each job along with variables needed for post functions

submit($job, $connection)- submits data to the database for each job and marks images as no longer being worked on. If the job is cropping and no object was cropped then no data is submitted. If 
the job was cropping and the page was a ground truth page a temporary entry is mad in a table so that covering previously cropped objects on pages with multiple objects will work properly.

final_submit($job, $connection)- submits the demographis information to the database. A group number is generated by selecting the highest group number from the database group tables for each job and adding one.
This group number is assigned to each image that was part og the job. It is also inserted into the check table for each job along with possible flags raised from the information in the check arrays and a randomly
generated code that will be presented to the user. This code is for admins to manage payment via amazon mechanical turk.

demographic($job, $file_array, $check_data1, $check_data2, $check_data3)- displays the form and the inputs for users to enter their demographic information

coverfaces($job, $connection, $filename, $file_data)- covers previously cropped faces on iimages where multiple objects need to be cropped, by selecting previously submitted x and y coordinates from
the database. If the image is a groud truth image then it selects from the temporary entry in the table for crop checks


