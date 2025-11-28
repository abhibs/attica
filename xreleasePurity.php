<?php
    session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$time=date("H:i:s");
	$date=date("Y-m-d");	
	$branchId=$_SESSION['branchCode'];
	
	//Price for the gold
	$branchPrice = mysqli_fetch_assoc(mysqli_query($con,"SELECT priceId FROM branch WHERE branchId='$branchId'"));
	$priceId = $branchPrice['priceId'];
	if($priceId==1){
		$price='Bangalore';
	}
	else if($priceId==2){
		$price='Karnataka';
	}
	else if($priceId==3){		
		$price='Andhra Pradesh'; 		
	}
	else if($priceId==4){		
		$price='Telangana';		
	}
	else if($priceId==5){		
		$price='Pondicherry';		
	}
	else if($priceId==6){		
		$price='Tamilnadu';		
	}
	
	$sql="SELECT cash FROM gold WHERE date='$date' AND city='$price' AND type='Gold' ORDER BY id DESC";
	$row=mysqli_fetch_array(mysqli_query($con,$sql));
	$rate=$row['cash'];	
		
	if($_GET['am'] != "" && $_GET['rnw'] != ""){
	    $amount = $_GET['am'];
		$netW = $_GET['rnw'];
		$purity = (($amount/$netW)/$rate)*100;
		echo $purity;
	}
	else{
	    echo 0;
	}
?>