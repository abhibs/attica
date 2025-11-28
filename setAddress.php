<?php 
	session_start();
	include("dbConnection.php");
	$data=$_POST['data'];
	$sql="select url from branch where branchId='$data'";
	$res = mysqli_query($con,$sql);
	$row = mysqli_fetch_array($res);
	$shorturl=$row['url'];
	$data1 = $shorturl;
	echo $data1;
?>