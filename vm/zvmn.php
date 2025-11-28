<?php
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');
	
	if (isset($_POST["verifyAttendance"])) {
        $vmStatus = 1;
        $empID = $_POST['empID'];
        $vmTime = $_POST['vmTime'];
        $vmempID = $_POST['vmempID'];
		
        $verifyAttendance = mysqli_query($con, "UPDATE attendance SET vmempID='$vmempID', vmStatus='$vmStatus', vmTime='$vmTime' WHERE empId='$empID' AND date='$date'");
        if ($verifyAttendance) {
            echo "SUCCESS";
		}else {
            echo "ERROR";
		}
	}
	
	if (isset($_POST["customerBill"])) {
        $mobile = $_POST['mobile'];
		$branchId = $_POST['branchId'];
		
        $everyCustomer = "UPDATE everycustomer 
		SET status=0
		WHERE contact='$mobile' AND branch='$branchId' AND date='$date'";
        if (mysqli_query($con,$everyCustomer)) {
            echo "SUCCESS";
		}else {
            echo "ERROR";
		}
	}
	
	if (isset($_POST["updateBranchMeetURL"])) {
        $url = $_POST['url'];
		$branchId = $_POST['branchId'];
		
        $everyCustomer = "UPDATE branch 
		SET meet = '$url'
		WHERE branchId='$branchId'";
		
        if (mysqli_query($con,$everyCustomer)) {
            echo "SUCCESS";
		}else {
            echo "ERROR";
		}
	}
	
	//if(isset($_POST['wrongEntryCustomer'])){
	//	$id = $_POST['id'];
	//	$everyCustomerQuery = "UPDATE everycustomer SET status='Wrong Entry' WHERE Id='$id'";
		
	//	if(mysqli_query($con, $everyCustomerQuery)){
	//		echo "SUCCESS";
	//	}
	//	else{
	//		echo "ERROR";
	//	}
	//}
	//

	if (isset($_POST['wrongEntryCustomer'])) {
	$id = $_POST['id'];
	$remarks = mysqli_real_escape_string($con, $_POST['remarks']);

	// Update the status to "Wrong Entry" and store the remarks
	$sql = "UPDATE everycustomer SET status='Wrong Entry', idnumber='$remarks' WHERE Id='$id'";

	if (mysqli_query($con, $sql)) {
		echo "SUCCESS";
	} else {
		echo "ERROR";
	}
	}
