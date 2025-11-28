<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$branchId = $_GET['branchId'];
		$query = "DELETE FROM trans WHERE id='$id'";
		if(mysqli_query($con,$query)){
			header("location:viewBillDetails.php?branchId=".$branchId);
		}
		else{
			echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'viewBillDetails.php?branchId=".$branchId."';\",150);</script>";
		}
	}
?>