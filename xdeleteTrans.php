<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	if(isset($_GET['cashId'])){
		$id = $_GET['cashId'];
		$query = "DELETE FROM trans WHERE id='$id'";
		if(mysqli_query($con,$query)){
			header("location:xviewTransaction.php");
		}
		else{
			echo "<script type='text/javascript'>alert('Bill Deleted Successfully!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransaction.php';\",150);</script>";
		}
	}
	else if(isset($_GET['impsId'])){
		$id = $_GET['impsId'];
		$query = "DELETE FROM trans WHERE id='$id'";
		if(mysqli_query($con,$query)){
			header("location:xviewTransactionIMPS.php");
		}
		else{
			echo "<script type='text/javascript'>alert('Bill Deleted Successfully!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransactionIMPS.php';\",150);</script>";
		}
	}
?>