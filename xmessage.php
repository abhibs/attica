<?php
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date("Y-m-d");
	
	$phone = $_GET['phone'];
	$name = $_GET['name'];	
	$grossW = $_GET['grossW'];
	$paid = $_GET['amount'];
	
	$rel = $_SESSION['rel'];
	//$url="http://api-alerts.solutionsinfini.com/v3/?method=sms&api_key=Ac1114feab4e31bdfaef1f02d3d3c23e7&to=".$phone."&sender=ATTICA&message=".$message;	
	
	if($rel == ""){		
		$message = urlencode('Dear ' . $name . ', Thank you for billing in Attica Gold Company. Gold Gross Weight:' . $grossW . ', Amount Paid:' . $paid . '.');	
	} 
	else {
		//$message = urlencode("Dear " . $name . ", Thanks for billing in Attica Gold Pvt Ltd, Gold Gross Weight: " . $grossW . ", Release Amount : " . $rel . " ,Amount Paid: " . $paid . ".");
		$message = urlencode('Dear ' . $name . ', Thank you for billing in Attica Gold Company. Gold Gross Weight: ' . $grossW . ', Release Amount : ' . $rel . ' ,Amount Paid: ' . $paid . '.');		
	}
	
	// CUSTOMER MESSAGE
	$api_key = "Ac1114feab4e31bdfaef1f02d3d3c23e7";
	$to = $phone;
	$sender = "ATTICA";
	$url = "http://api-alerts.solutionsinfini.com/v3/?" .
	"method=sms&" .
	"api_key=" . $api_key . "&" .
	"to=" . $to . "&" .
	"sender=" . urlencode($sender) . "&" .
	"message=" . urlencode($message);
	// Send the request
	$output = file($url);
	$result = explode(":", $output[0]);
	
	//SOLD OUT API	
	require 'Config/CallCenter.php';	
	$rowS = [
    "mobile" => $phone,
    "customer_id" => $_SESSION['customerID'],
    "billing_date" => $date,
    "branch_id" => $_SESSION['branchCode']
	];
	$chs = curl_init($billingInfoURL);
	header('Content-Type: application/json');
	$a = json_encode($rowS, JSON_PRETTY_PRINT);
	curl_setopt($chs, CURLOPT_POSTFIELDS, $a);
	curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($chs);
	curl_close($chs);
	
	unset($_SESSION['customerId']);
	unset($_SESSION['bill']);
	unset($_SESSION['Rid']);
	unset($_SESSION['mobile']);
	
	header("location:xphysicalStatus.php");
	
?>