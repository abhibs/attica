<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');
	
	$time = date("h:i:s");
	$username = $_SESSION['employeeId'];
	
	// APPROVE IMPS BILL ( @ xapprovePhysicalIMPSData.php )
	if(isset($_POST['submitApproveIMPS'])){
	    $id = $_POST['id'];
		$accTimer = $_POST['accTimer']; 
		
		$sql = "UPDATE trans 
		SET status='Approved', imps_empid='$username', impstime='$time' , accTimer= '$accTimer'
		WHERE id='$id'";
		
		if($_POST['goldType'] == "Release Gold"){
			$releaseId = $_POST['relID'];
			$sql .= "; UPDATE releasedata SET status='Billed' WHERE phone='$_POST[custPhone]' AND releaseID='$releaseId' AND date='$_POST[relDate]';";
		}
		
		if(mysqli_multi_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Approved!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Approving,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
	}
	
	// REJECT IMPS BILL ( @ xapprovePhysicalIMPSData.php )
	else if(isset($_POST['submitRejectIMPS'])){
	    $id = $_POST['id'];
		
		$sql = "UPDATE trans 
		SET status='Rejected', imps_empid='$username', impstime='$time' 
		WHERE id='$id'";
		
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Rejected!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Rejecting,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
	}
	
	// CONVERT TO CASH ( @ xapprovePhysicalIMPSData.php )
	else if(isset($_POST['ConvertToCash'])){
		
		$id = $_POST['id'];
		$amountPaid = $_POST['amountPaid'];
		
		$sql = "UPDATE trans 
		SET status='Approved', cashA='$amountPaid', paymentType='Cash', impsA='0', imps_empid='$username', impstime='$time' 
		WHERE id='$id'";
		
		if($_POST['goldType'] == "Release Gold"){
			$releaseId = $_POST['relID'];
			$sql .= "; UPDATE releasedata SET status='Billed' WHERE phone='$_POST[custPhone]' AND releaseID='$releaseId' AND date='$_POST[relDate]';";
		}
		
		if(mysqli_multi_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Converted to Cash & Approved!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Converting To Cash,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
		 
	}
	
?>	

