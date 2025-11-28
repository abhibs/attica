<?php
	session_start();
	
	// DELETE EXPENSE ( @ xviewExpense.php )
	if(isset($_GET['id']) && $_GET['id']!=''){
		include("dbConnection.php");
		$id=$_GET['id'];
		$sql = "DELETE FROM expense WHERE id='$id'";
		if(mysqli_query($con,$sql)){
			header("location:xviewExpense.php");
		}		
		else{
			echo "<script>alert('Error Occured,Please Try Again')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewExpense.php';\",150);</script>";
		}
	}
	else{
		header("location:logout.php");
	}