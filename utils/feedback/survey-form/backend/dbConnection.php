<?php
	
	require '../../../../Config/Database.php';
	
	$con= mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DBNAME);
	if(!$con){
		die('could not connect;'.mysqli_connect_error());
	}