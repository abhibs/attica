<?php 
session_start();
 include("dbConnection.php");
 $data=$_POST['data'];
	$sql1="select * from customer where customerId='$data'";
$res1 = mysqli_query($con, $sql1);
$row=mysqli_fetch_array($res1);
$name=$row['name'];
$phone=$row['mobile'];
$return_arr[] = array(
                    "name" => $name,
                    "phone" => $phone);
					 echo json_encode($return_arr);
?>