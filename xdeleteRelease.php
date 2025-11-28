<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
include("dbConnection.php");

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$sql = "DELETE FROM releasedata WHERE rid='$id'";
	if (mysqli_query($con, $sql)) {
		echo "<script type='text/javascript'>alert('Deleted Data')</script>";
		echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>";
	} else {
		echo "<script type='text/javascript'>alert('Error Deleting Data!')</script>";
		echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>";
	}
}
