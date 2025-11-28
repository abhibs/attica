<?php
	session_start();
	
	if($_POST['data'] == $_SESSION['loginotp']){
		echo "OTP Validated";	
	}
    else{
    	echo "Invalid OTP";
	}
	unset($_SESSION['loginotp']);
?>