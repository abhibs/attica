<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	
	// DELETE EMPLOYEE ( @ addEmployee.php )
	if(isset($_GET['emplId'])){
		$id = $_GET['emplId'];
		$sql = "DELETE FROM employee WHERE id='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script>alert('Employee ID: ".$id." Deleted!')</script>";
			echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
		}
		else{
			echo "<script>alert('No Records Found');</script>";
			echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
		}
	}
?>