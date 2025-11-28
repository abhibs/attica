<?php
    session_start();
	$id = $_REQUEST['id'];
	include("dbConnection.php");
	/*$pay=$_SESSION['paymentType'];
	if($pay=="Cash")
	{
	$date=date("Y-m-d");
	$sql2="select cash from gold where date='$date'";
	$res2=mysqli_query($con,$sql2);
	$row2=mysqli_fetch_array($res2);
	$rate=$row2['cash'];
	}
	else if($pay=="NEFT/IMPS")
	{
		$date=date("Y-m-d");
	$sql2="select transferRate from gold where date='$date'";
	$res2=mysqli_query($con,$sql2);
	$row2=mysqli_fetch_array($res2);
	$rate=$row2['transferRate'];
	}*/
	$sql1="select * from ornament where ornamentId='$id'";
	$res1=mysqli_query($con,$sql1);
	$row=mysqli_fetch_array($res1);
	$purity=$row['purity'];
	$read=$row['reading'];
	$we=$row['weight'];
	$rate=$row['rate'];
	$purity1=$purity/100;
	$wei=$row['weight']-$row['sWaste'];
	$gross=$rate*$purity1*$read;
	$margin=$_SESSION['marginAmount'];
	$mar=0.03;
	$mar1=$gross*$mar;
	$netAm=$gross-$mar1;
	$amountP=$netAm;
	$_SESSION['grossW']=$_SESSION['grossW']-$we;
	$_SESSION['gross']=$_SESSION['gross']-$gross;
	$_SESSION['netW']=$_SESSION['netW']-$wei;
	$_SESSION['netAm']=$_SESSION['netAm']-$netAm;
	$_SESSION['amountP']=$_SESSION['amountP']-round($amountP);
	$_SESSION['marginAmount']=$margin-$mar1;
	$sql="delete from ornament where ornamentId='$id'";
	mysqli_query($con,$sql);
	header("Location:addTransaction.php");
?>		  