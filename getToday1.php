<?php
	session_start();
	include("dbConnection.php");
	$date=date("Y-m-d");
	
	$metal = $_POST['data'];
	$paymentType = $_POST['dat'];
	if($paymentType == 'Cash'){
		$money = 'cash';
	}
	else if($paymentType == 'NEFT/RTGS' || $paymentType == 'Cash/IMPS'){
		$money = 'transferRate';
	}	
	
	$rateQuery = "SELECT ".$money." as metalRate
	FROM gold
	WHERE type='".$metal."' AND date='$date' AND city=(
	SELECT
	(CASE
 	WHEN priceId=1 THEN 'Bangalore'
 	WHEN priceId=2 THEN 'Karnataka'
 	WHEN priceId=3 THEN 'Andhra Pradesh'
 	WHEN priceId=4 THEN 'Telangana'
 	WHEN priceId=5 THEN 'Chennai'
        WHEN priceId=6 THEN 'Tamilnadu'
	END) AS city
	FROM branch 
	WHERE branchId='".$_SESSION['branchCode']."')
	ORDER BY id DESC
	LIMIT 1";
	$rate = mysqli_fetch_assoc(mysqli_query($con,$rateQuery));
	
	if(isset($rate['metalRate'])){
		$_SESSION['gold'] = $rate['metalRate'];
		echo $rate['metalRate'];
	}
	else{
		echo 0;
	}
