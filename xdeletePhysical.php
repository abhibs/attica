<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	
	// DELETE IMPS BILL ( @ xapprovePhysicalIMPS.php )
	if(isset($_GET['iid'])){
	    $id = $_GET['iid'];
		$sql = "DELETE FROM trans WHERE id='$id'";
		if(mysqli_query($con,$sql)){
		    echo header("location:xapprovePhysicalIMPS.php?"); 
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Deleting Data!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapprovePhysicalIMPS.php';\",150);</script>";
		}
	}	
?>