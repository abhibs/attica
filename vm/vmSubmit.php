<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");
	
	/* ------------------------------------  PASSWORD CHANGE >> zbmhoHome1.php  -------------------------------------  */
	if (isset($_POST['vmPasswordChange'])) {
		$Password = $_POST['password'];
		$empId = $_POST['employeeId'];
		$sql = "UPDATE users SET password='$Password' WHERE employeeId='$empId'";
		if (mysqli_query($con, $sql)) {
			echo "<script type='text/javascript'>alert('YOUR PASSWORD HAS BEEN CHANGED')</script>";
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		}
		else {
			echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		}
	}
	
	/* ------------------------------------  NEW CUSTOMER >> zbmhoHome1.php  -------------------------------------  */
	
	if (isset($_POST['VMsubmitNCHidden'])) {
	
	    $branchID = $_POST['branchID'];
		$cusname = strtoupper(trim($_POST['name']));
		$mob = $_POST['contact'];
		$type = $_POST['type'];	
			
		$extra = [];
		$extra["GrossW"] = $_POST['grossW'];
		$extra['itemCount'] = $_POST['itemCount'];
		$extra['Hallmark'] = $_POST['hallmark'];
		$extra['With'] = (isset($_POST['withMetal'])) ? $_POST['withMetal'] : 'without';
		$extra['RelAmount'] = (isset($_POST['relAmount'])) ? $_POST['relAmount'] : '';
		$extra['RelSlips'] = (isset($_POST['relSlips'])) ? $_POST['relSlips'] : '';
		$extra['Pledge'] = (isset($_POST['pledge'])) ? $_POST['pledge'] : 'no';
				
		$idnumber = $_POST['remarks'];
		$date = date('Y-m-d');
		$time = date("H:i:s");
		
		/* ======================== Check whether the customer has billed ======================== */
		$status = 0;
		$remark = "";
		
		$customerPast = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS bills FROM trans WHERE phone='$mob' AND status='Approved'"));
		
		$extra["bills"] = $customerPast['bills'];
		$extra = json_encode($extra);
		/* ======================== End of Check whether the customer has billed ======================== */
		
		
		$fName1 = '';
		$inscon = "INSERT INTO everycustomer(customer,contact,type,idnumber,branch,image,quotation,date,time,status,status_remark,remark,block_counter,extra,reg_type,agent,agent_time) VALUES ('$cusname','$mob','$type','$idnumber','$branchID','','$fName1','$date','$time','$status','','$remark','0','$extra','VM','','$time')";
		if (mysqli_query($con, $inscon)) {
			echo "<script type='text/javascript'>alert('Customer Added Successfully.')</script>";
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		} 
		else {
			echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		}
	}
	
	/* ------------------------------------  CUSTOMER WRONG / DOUBLE ENTRY >> xeveryCustomer1.php  -------------------------------------  */
	if(isset($_POST['customerStatusChange'])){
		$id = $_POST['id'];
		$status = 'Wrong Entry';
		$remark1 = $_POST['remark1'];
		$sql = "UPDATE everycustomer SET status='$status', remark1='$remark1' WHERE Id='$id'";
		if (mysqli_query($con, $sql)) {
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		} 
		else {
			echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
			echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
		}
	}
	
	/* ------------------------------------  UPDATE REGISTERED CUSTOMER >> updateRegistered.php  -------------------------------------  */
if(isset($_POST['updateRegisteredCustomer'])){
    $time = date("H:i:s");
    $id        = $_POST['id'];
    $contact   = $_POST['contact'];
    $vmtime    = $_POST['liveTimer'];
    $walkinType = $_POST['walkinType'] ?? '';  // <-- NEW

    // Build $extra
    $extra = [];
    $extra["GrossW"]   = $_POST['grossW'];
    $extra['itemCount'] = $_POST['itemCount'];
    $extra['Hallmark']  = $_POST['hallmark'];
    $extra['With']      = (isset($_POST['withMetal'])) ? $_POST['withMetal'] : 'without';
    $extra['RelAmount'] = (isset($_POST['relAmount'])) ? $_POST['relAmount'] : '';
    $extra['RelSlips']  = (isset($_POST['relSlips'])) ? $_POST['relSlips'] : '';
    $extra['Pledge']    = (isset($_POST['pledge'])) ? $_POST['pledge'] : 'no';
    $extra['Language']  = (isset($_POST['language'])) ? $_POST['language'] : 'English';
    $extra["bills"]     = (isset($_POST['totalbills'])) ? $_POST['totalbills'] : '0';
    $extra = json_encode($extra);

    $idnumber = $_POST['remarks'];
    $status   = 0;

    // (optional) basic escaping
    $id_esc         = mysqli_real_escape_string($con, $id);
    $idnumber_esc   = mysqli_real_escape_string($con, $idnumber);
    $extra_esc      = mysqli_real_escape_string($con, $extra);
    $agent_esc      = mysqli_real_escape_string($con, $_SESSION['employeeId']);
    $vmtime_esc     = mysqli_real_escape_string($con, $vmtime);
    $walkinType_esc = mysqli_real_escape_string($con, $walkinType);

    $inscon = "
        UPDATE everycustomer SET
            idNumber     = '$idnumber_esc',
            status       = '$status',
            extra        = '$extra_esc',
            agent        = '$agent_esc',
            agent_time   = '$time',
            vmtime       = '$vmtime_esc',
            walkinType   = '$walkinType_esc'   -- <-- NEW
        WHERE id = '$id_esc'
    ";

    if (mysqli_query($con, $inscon)) {
        echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
    } else {
        echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG, PLEASE TRY AGAIN.')</script>";
        echo "<script>setTimeout(\"location.href = 'updateRegistered.php?id=".$id_esc."';\",150);</script>";
    }
}

	if (isset($_POST['openclosesubmit'])) {
	$branchId = $_POST['branchId'];
	$status = $_POST['status']; // 1 for open, 0 for close

	$updateQuery = "UPDATE branch SET openclosestatus = '$status' WHERE branchId = '$branchId'";
	if (mysqli_query($con, $updateQuery)) {
		$statusText = ($status == '1') ? 'Open' : 'Close';
		echo "<script>alert('Status Updated As $statusText for $branchId');</script>";
		echo "<script>setTimeout(\"location.href = 'zbmhoHome1.php';\",150);</script>";
	} else {
		echo "<script>alert('Error updating status');</script>";
	}
	}
?>

