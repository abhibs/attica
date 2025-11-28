<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');
	$time=date("h:i:s");
	
	if(isset($_GET['rid'])){
	    $id = $_GET['rid'];
		$sqlA = mysqli_query($con,"DELETE FROM releasedata WHERE rid = '$id'");
		if($sqlA){
		    echo header("location:xreleaseDataR.php?"); 
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Deleting Data!')</script>";
		}
	}
?>