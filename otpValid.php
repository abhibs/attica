<?php
	session_start();	
	$otp=$_POST['data'];
	$real=$_SESSION['otp'];
	if($otp==$real){
		echo "OTP Validated";		
	}
	else{
		echo "Invalid OTP";
	}	
?>