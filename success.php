<?php
	session_start();
	include("dbConnection.php");
	$name=$_SESSION['name'];
	$phone=$_SESSION['mobile'];
	$sql="select customerId from customer where name='$name'";
	$res=mysqli_query($con,$sql);
	$row=mysqli_fetch_array($res);
	$id=$_SESSION['customerID'];
	$otp = rand(100000,999999);
	$message ="Dear " .$name. ", your registration details are submitted successfully, Thanks for showing interest in Attica Gold Pvt Ltd.";
	$url="http://api-alerts.solutionsinfini.com/v3/?method=sms&api_key=Ac1114feab4e31bdfaef1f02d3d3c23e7&to=".$phone."&sender=ATTICA&message=".$message;
	$_SESSION['otp']=$otp;
	$_SESSION['mobile']=$phone;
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true
		//CURLOPT_POSTFIELDS => $postData
		//,CURLOPT_FOLLOWLOCATION => true
	));
	//Ignore SSL certificate verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//get response
	$output = curl_exec($ch);
	//Print error if any
	if(curl_errno($ch))
	{
	echo 'error:' . curl_error($ch);
	}
	curl_close($ch);
	echo "<script type='text/javascript'>alert('Customer added successfully!')</script>";
	echo "<script>setTimeout(\"location.href = 'addTransaction.php';\",150);</script>";

	//echo header("location:viewCustomers.php");
?>