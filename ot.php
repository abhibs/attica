<?php
	session_start();
	include("dbConnection.php");
	include("Config/SMS/sms_generator.php");
	
	$phone = $_POST['data'];
	$name = $_POST['name'];
	
	$otp = rand(100000, 999999);
	date_default_timezone_set("Asia/Kolkata");
	
	$time = date("H:i:s");
	$date = date("Y-m-d");
	
	if ($phone < 9999999999 && $phone > 6000000000) {
		$sql = "insert into otp(customerName,mobile,otp,date,time,message,flag,employee_id,remarks) values ('$name','$phone','$otp','$date','$time','Registration',0,'','')";
		$res = mysqli_query($con, $sql);
			
		$sms = new SMS($phone);
		$sms->customer_verification($name, $otp);
		
		
		$_SESSION['otp'] = $otp;
		$_SESSION['mobile'] = $phone;
		$_SESSION['cus'] = $name;
		
	}
