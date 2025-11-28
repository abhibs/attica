<?php
	/* ----------------------- THANK YOU FOR BILLING MESSAGE TO THE CUSTOMERS ON BILL APPROVAL ----------------------------- */
	session_start();
	include("dbConnection.php");
// 	include("Config/SMS/sms_generator.php");
	
	date_default_timezone_set("Asia/Kolkata");
	$date = date("Y-m-d");
	
	$phone = $_GET['phone'];
	$name = $_GET['name'];	
	$grossW = $_GET['grossW'];
	$paid = $_GET['amount'];
	$redirectURL = $_GET['redirectURL']; // xviewTransaction.php OR xviewTransactionIMPS.php	
	$rel = $_GET['releaseAmount'];
	
	
// 	$sms = new SMS($phone);
// 	if($rel == ""){	
// 		$sms->physical_bill($name, $grossW, $paid);
// 	} 
// 	else {
// 		$sms->release_bill($name, $grossW, $rel, $paid);
// 	}
	
	header("location:".$redirectURL);
	
?>