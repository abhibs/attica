<?php

	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");

	$branchId = $_GET['branch'];

	$today_date = date("Y-m-d");
	$last_date = date('Y-m-d', strtotime('-30 days'));

	$transQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) AS bills
	FROM trans
	WHERE date BETWEEN '$last_date' AND '$today_date' AND 
	status='Approved' AND
	branchId='$branchId'"));

	$enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry 
	FROM walkin
	WHERE date BETWEEN '$last_date' AND '$today_date' AND 
	issue NOT IN ('Rejected') AND
	branchId='$branchId' "));

	$bills = $transQuery['bills'] ? $transQuery['bills'] :  0;
	$enquiry = $enquiryQuery['enquiry'] ? $enquiryQuery['enquiry'] : 0;

	$cog = 0;
	if($bills==0 && $enquiry == 0){
		$cog = 0;
	}
	else{
		$cog = ROUND(($bills/($enquiry + $bills)) * 100, 2);
	}

	echo $cog;

?>
