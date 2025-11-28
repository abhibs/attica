i<?php
    session_start();
    include("dbConnection.php");
	include("Config/SMS/sms_generator.php");
	
    date_default_timezone_set("Asia/Kolkata");
	$date = date("Y-m-d");
    $time = date("H:i:s");
	
	$branchId = $_POST['username'];
	$employeeId = $_POST['empid'];
	$returnStatus = [];
	
	$userDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) AS count, type, password, employeeId, date FROM users WHERE username='$branchId'"));
	
	if($userDetails['count'] > 0 && ($userDetails['type'] == 'Branch' || $userDetails['type'] == 'Zonal' || $userDetails['type'] == 'Call Centre' || $userDetails['type'] == 'SubZonal')){
		if($userDetails['type'] == 'Branch'){
			if($employeeId == $userDetails['employeeId']){
				$loginotp = rand(100000,999999);
				$_SESSION['loginotp'] = $loginotp;
				
				$employeeDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact FROM employee WHERE empId='$employeeId'"));
				$result = mysqli_query($con, "INSERT INTO loginotp(branch,empid,otp,date,time) VALUES ('$branchId','$employeeId','$loginotp','$date','$time')");
				
				$sms = new SMS($employeeDetails['contact']);
				$sms->bm_login($employeeDetails['name'], $loginotp);
				
				$returnStatus = ["status"=>"ok", "msg"=>"OTP Sent to Your Mobile Number"];
			}
			else{
				$returnStatus = ["status"=>"Invalid", "msg"=>"Branch and Employee Does Not Match!, Call Zonal Manager for Assistance!."];
			}
		}
		else if($userDetails['type'] == 'Zonal'){
			if($employeeId == $userDetails['password']){
				if($userDetails['date'] == $date){
					$loginotp = rand(100000,999999);
					$_SESSION['loginotp'] = $loginotp;
					$contact = "8925459505";
					
					$employeeDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact FROM employee WHERE empId='$branchId'"));
					$result = mysqli_query($con, "INSERT INTO loginotp(branch,empid,otp,date,time) VALUES ('Zonal','$branchId','$loginotp','$date','$time')");
					
					$sms = new SMS($contact);
					$sms->bm_login($employeeDetails['name'], $loginotp);
					
					$returnStatus = ["status"=>"ok", "msg"=>"OTP Sent to Concerned Mobile Number"];
				}
				else{
					$returnStatus = ["status"=>"Invalid", "msg"=>"Zonal Access Not Granted"];
				}
			}
			else{
				$returnStatus = ["status"=>"Invalid", "msg"=>"Invalid Username Or Password"];
			}
		}
		else if($userDetails['type'] == 'Call Centre'){
			if($employeeId == $userDetails['password']){
				$loginotp = rand(100000,999999);
				$_SESSION['loginotp'] = $loginotp;
				$contact = "9148759512";
				
				$employeeDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact FROM employee WHERE empId='$branchId'"));
				$result = mysqli_query($con, "INSERT INTO loginotp(branch,empid,otp,date,time) VALUES ('CC','$branchId','$loginotp','$date','$time')");
				
				$sms = new SMS($contact);
				$sms->bm_login($employeeDetails['name'], $loginotp);
				
				$returnStatus = ["status"=>"ok", "msg"=>"OTP Sent to Concerned Mobile Number"];
			}
			else{
				$returnStatus = ["status"=>"Invalid", "msg"=>"Invalid Username Or Password"];
			}
		}
		else if($userDetails['type'] == 'SubZonal'){
			if($employeeId == $userDetails['password']){
				if($userDetails['date'] == $date){
					$loginotp = rand(100000,999999);
					$_SESSION['loginotp'] = $loginotp;
					$contact = "8925459505";

					$employeeDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact FROM employee WHERE empId='$branchId'"));
					$result = mysqli_query($con, "INSERT INTO loginotp(branch,empid,otp,date,time) VALUES ('SubZonal','$branchId','$loginotp','$date','$time')");

					$sms = new SMS($contact);
					$sms->bm_login($employeeDetails['name'], $loginotp);

					$returnStatus = ["status"=>"ok", "msg"=>"OTP Sent to Concerned Mobile Number"];
				}
				else{
					$returnStatus = ["status"=>"Invalid", "msg"=>"Sub Zonal Access Not Granted"];
				}
			}
			else{
				$returnStatus = ["status"=>"Invalid", "msg"=>"Invalid Username Or Password"];
			}
		}
	}
	else{
		$returnStatus = ["status"=>"Invalid", "msg"=>"Invalid Credentials"];
	}
	echo json_encode($returnStatus);
?>
