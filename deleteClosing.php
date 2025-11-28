<?php
include("dbConnection.php");
$id=$_GET['id'];
$sql="delete from closing where closingID='$id'";
$res=mysqli_query($con,$sql);
header("location:dailyReports.php");
?>