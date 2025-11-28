<?php 
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set("Asia/Kolkata");
	include("../dbConnection.php");
	
	if(isset($_POST['HoRegistrationSubmit'])){
		$branch = $_POST['branchId'];
		
		$name = $_POST['customerName'];
		$mobile = $_POST['customerMobile'];	
		$type = $_POST['customerType'];
		
		$extra = [];
		$extra["GrossW"] = $_POST['customerGrossW'];
		$extra["Language"] = $_POST['language'];
		$extra = json_encode($extra);
		
		$date = date("Y-m-d");
		$time = date("h:i:s");
		$status = "Begin";
		$reg_type = "VD";
		
		$everyCustomerQuery = "INSERT INTO everycustomer(customer, contact, type, idnumber, branch, image, quotation, date, time, status, status_remark, remark, block_counter, extra, reg_type, agent) VALUES ('$name', '$mobile', '$type', '', '$branch', '', '', '$date', '$time', '$status', '', '', '0', '$extra', '$reg_type','')";
		
		if (mysqli_query($con, $everyCustomerQuery)) {
			echo "<script type='text/javascript'>alert('Customer Registered Successfully.')</script>";
			echo "<script>setTimeout(\"location.href = 'register.php';\",150);</script>";
		} 
		else {
			echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
			echo "<script>setTimeout(\"location.href = 'register.php';\",150);</script>";
		}
	}
	
	if(isset($_POST['assignVM'])){
		$id = $_POST['everycustomerid'];
		$agentId = $_POST['agentId'];
		
		$sql = "UPDATE everycustomer SET agent='$agentId' WHERE Id='$id'";
		
		if (mysqli_query($con, $sql)) {
			echo "<script type='text/javascript'>alert('Customer Assigned Successfully.')</script>";
			echo "<script>setTimeout(\"location.href = 'assignVM.php';\",150);</script>";
		} 
		else {
			echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
			echo "<script>setTimeout(\"location.href = 'assignVM.php';\",150);</script>";
		}
	}
?>