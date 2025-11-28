<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	
	
	//  @ approveTFund.php
	// APPROVE  
	if(isset($_GET['approveId']) && !empty($_GET['approveId'])){
		$id = $_GET['approveId'];
		$sql = mysqli_stmt_init($con);
		mysqli_stmt_prepare($sql,"UPDATE fund SET status='Approved' WHERE id=?");
		mysqli_stmt_bind_param($sql,"i",$id);
		if(mysqli_stmt_execute($sql)){
			header("location:subApproveFund.php");
		}
		else{
			echo "<script>alert('Error Occurred')</script>";
			echo "<script>setTimeout(\"location.href = 'subApproveFund.php';\",150);</script>";
		}
	}
	
	//  REJECT  
	if(isset($_GET['rejectId']) && !empty($_GET['rejectId'])){
		$id = $_GET['rejectId'];
		$sql = mysqli_stmt_init($con);
		mysqli_stmt_prepare($sql,"UPDATE fund SET status='Rejected' WHERE id=?");
		mysqli_stmt_bind_param($sql,"i",$id);
		if(mysqli_stmt_execute($sql)){
			header("location:subApproveFund.php");
		}
		else{
			echo "<script>alert('Error Occurred')</script>";
			echo "<script>setTimeout(\"location.href = 'subApproveFund.php';\",150);</script>";
		}
	}
	
	//  DELETE  
	if(isset($_GET['deleteId']) && !empty($_GET['deleteId'])){
		$id = $_GET['deleteId'];
		$sql = mysqli_stmt_init($con);
		mysqli_stmt_prepare($sql,"DELETE FROM fund WHERE id=?");
		mysqli_stmt_bind_param($sql,"i",$id);
		if(mysqli_stmt_execute($sql)){
			header("location:subApproveFund.php");
		}
		else{
			echo "<script>alert('Error Occurred')</script>";
			echo "<script>setTimeout(\"location.href = 'subApproveFund.php';\",150);</script>";
		}
	}

?>	
