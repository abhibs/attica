<?php 
session_start();
$data=$_POST['data'];
$gross=$_SESSION['gross'];
 $paid=$_SESSION['amountP'];
 $net=$_SESSION['netAm'];
 $diff=$data-$net;
 if(isset($_SESSION['netRel']))
 {
$marginAmount=$gross-$data;
		$netA=$data;
		$amountP=$paid+$diff;
		$data2=$marginAmount*100;
		$data1=$data2/$gross;
	 $_SESSION['amountP']=$amountP;

		$_SESSION['netAm']=$netA;
		
		$_SESSION['marginAmount']=$marginAmount;
		
$return_arr[] = array(
                    "margin" => $marginAmount,
					"ctype" => $data1,
					 "payable" => $_SESSION['amountP']);
					 echo json_encode($return_arr);
 }
 else if(!isset($_SESSION['netRel']))
 {
	 $marginAmount=$gross-$data;
		$netA=$data;
		$amountP=round($netA,0);
		$data2=$marginAmount*100;
		$data1=$data2/$gross;
	 $_SESSION['amountP']=$amountP;

		$_SESSION['netAm']=$netA;
		
		$_SESSION['marginAmount']=$marginAmount;
		
$return_arr[] = array(
                    "margin" => $marginAmount,
					"ctype" => $data1,
					 "payable" => $_SESSION['amountP']);
					 echo json_encode($return_arr);
	 
 }
?>