<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');
	$time=date("h:i:s");
	$branchCode = $_SESSION['branchCode'];
	$username = $_SESSION['login_username'];
	
	if(isset($_GET['ACid'])){
	    $id = $_GET['ACid'];
		$remarks = $username;
		$status = "Verified";
		$sql = "UPDATE trans SET status = '$status',remarks = '$remarks' WHERE id = '$id'";
		if(mysqli_query($con,$sql)){
		    echo header("location:xviewTransaction.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewCustomerDetails.php';\",150);</script>";
		}			
	}
	else if(isset($_GET['RCid'])){
	    $id = $_GET['RCid'];
		$remarks = $username;
		$status = "Rejected";
		$sql = "UPDATE trans SET status = '$status',remarks = '$remarks' WHERE id = '$id'";
		if(mysqli_query($con,$sql)){
		    echo header("location:xviewTransaction.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewCustomerDetails.php';\",150);</script>";
		}
	}
	else if(isset($_GET['AIid'])){
	    $id = $_GET['AIid'];
		$remarks = $username;
		$status = "Verified";
		$sql = "UPDATE trans SET status = '$status',remarks = '$remarks' WHERE id = '$id'";
		if(mysqli_query($con,$sql)){
		    echo header("location:xviewTransactionIMPS.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewCustomerDetails.php';\",150);</script>";
		}	
	}
	else if(isset($_GET['RIid'])){
	    $id = $_GET['RIid'];
		$remarks = $username;
		$status = "Rejected";
		$sql = "UPDATE trans SET status = '$status',remarks = '$remarks' WHERE id = '$id'";
		if(mysqli_query($con,$sql)){
		    echo header("location:xviewTransactionIMPS.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewCustomerDetails.php';\",150);</script>";
		}
	}
	
?>