<?php
session_start();

	$otp=$_POST['otp'];
	$real=$_SESSION['otp'];
	if($otp==$real)
	{
		echo "OTP Validated";
		
	}
else
{
	echo "Invalid OTP";
}

?>