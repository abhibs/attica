<?php 
session_start();
 include("dbConnection.php");
 $data=$_POST['data'];
 //echo $data;
 $_SESSION['employeeId']=$data;
$dat=  $_SESSION['employeeId'];
	$sql1="select * from employee where empId='$dat'";
$res1 = mysqli_query($con, $sql1);
$row=mysqli_fetch_array($res1);
$name=$row['name'];
$_SESSION['employeeName']=$name;
$phone=$row['contact'];
$email=$row['mailId'];
$add=$row['address'];
$loc=$row['location'];
$des=$row['designation'];
/* $gen=$row['gender'];
$join=$row['joinDate'];
$qual=$row['qualification'];
$doc=$row['doc'];
$branch=$row['branch'];
$lan=$row['lang'];
$ref=$row['ref']; */
$return_arr[] = array(
                    "name" => $name,
                    "phone" => $phone,
					"email" => $email,
					"address" => $add,
					"loc" => $loc,
					"des" => $des    /*,
					"gender" => $gen,
					"join" => $join,
					"qual" => $qual,
					"doc" => $doc,
					"branch" => $branch,
					"lan" => $lan,
					"ref" => $ref */ );
					 echo json_encode($return_arr);
?>