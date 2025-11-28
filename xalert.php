<?php
	session_start();
	include("dbConnection.php");
	if (isset($_SESSION['login_username']) && isset($_SESSION['branchCode'])){
		$branchId = $_SESSION['branchCode'];
		$empId = $_SESSION['employeeId'];

		$sqlLock = "UPDATE users SET employeeId='' WHERE type='Branch' AND username='$branchId'";
		if(mysqli_query($con,$sqlLock)){
			$_SESSION = array();
			session_destroy();
			header('location:index.php');
		}
		else{
			header('location:atticagoldcompany.com');
		}
	}
?>