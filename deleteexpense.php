<?php
include("dbConnection.php");
$id=$_GET['id'];
$sql="delete from expense where id='$id'";
$res=mysqli_query($con,$sql);
header("location:dailyExpense.php");
?>