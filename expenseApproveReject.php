<?php
	session_start();
	include("dbConnection.php");
	
	// APPROVE EXPENSE ( @ expenseApproval.php )
	if(isset($_GET['appId'])){
		$id = $_GET['appId'];
		$sql = "UPDATE expense SET status='Approved' where id='$id'";
		if(mysqli_query($con,$sql)){
			header("location:expenseApproval.php");
		}
		else{
			echo "<script>alert('Error Occured,Please Try Again')</script>";
			echo "<script>setTimeout(\"location.href = 'expenseApproval.php';\",150);</script>";
		}
	}
	
	// REJECT EXPENSE ( @ expenseApproval.php )
	if(isset($_GET['rejId'])){
		$id = $_GET['rejId'];
		$sql = "UPDATE expense SET status='Rejected' where id='$id'";
		if(mysqli_query($con,$sql)){
			header("location:expenseApproval.php");
		}
		else{
			echo "<script>alert('Error Occured,Please Try Again')</script>";
			echo "<script>setTimeout(\"location.href = 'expenseApproval.php';\",150);</script>";
		}
	}
	
?>