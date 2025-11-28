<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	
    /* CHECK IF THE CUSTOMER IS ALREADY REGISTERED  */
	if (isset($_GET['contact']) && isset($_GET['Id']) && isset($_GET['encTime'])) {
	    $contact = $_GET['contact'];
		$id = $_GET['Id'];
		$encData = base64_encode(date("Y-m-d").$_GET['encTime']);
		$count = mysqli_num_rows(mysqli_query($con,"SELECT mobile FROM customer WHERE mobile='$contact'"));
		if($count >= 1){
			echo header("location:xexistingCustomer.php?id=".$id."&encData=".$encData);   // EXISTING CUSTOMER
		}
		else{
			echo header("location:xaddCustomer.php?id=".$id."&encData=".$encData);   // NEW CUSTOMER
		}
	}
	else{
		include("logout.php");
	}
?>