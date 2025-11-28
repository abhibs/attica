<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	
	if (isset($_GET['type'])){
		
		$sql = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,mobile FROM customer WHERE customerId = '$_SESSION[customerID]' AND mobile='$_SESSION[mobile]'"));
		$message ='Dear '.$sql['name'].',your registration is successful.Thank you for showing your interest in Attica Gold Company.';
        
        // CUSTOMER MESSAGE
		$api_key = "Ac1114feab4e31bdfaef1f02d3d3c23e7";
		$to = $sql['mobile'];
		$sender = "ATTICA";
		$url = "http://api-alerts.solutionsinfini.com/v3/?" .
		"method=sms&" .
		"api_key=" . $api_key . "&" .
		"to=" . $to . "&" .
		"sender=" . urlencode($sender) . "&" .
		"message=" . urlencode($message);
		// $output = file($url);
		
		echo "<script type='text/javascript'>alert('Customer Registration successfull!')</script>";
		if($_GET['type'] == "physical"){
			echo "<script>setTimeout(\"location.href = 'xaddTransaction.php';\",150);</script>";
		}
		else if ($_GET['type'] == "release"){
			echo "<script>setTimeout(\"location.href = 'xreleaseGold.php';\",150);</script>";
		}
	}
	else{
		echo "<script>alert('please select the type of Transaction!!!')</script>";
	    echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>"; 
	}
?>