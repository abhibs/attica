<?php 
session_start();
$data=$_POST['data'];
$data1=$data/100;
$_SESSION['margin']=$data1;
$gross=$_SESSION['gross'];
$margin=$_SESSION['marginAmount'];
$marginAmount=$data1*$gross;
		$netA=$gross-$marginAmount;
		$amountP=$netA;
	 $_SESSION['amountP']=$amountP;

		$_SESSION['netAm']=$netA;
		
		$_SESSION['marginAmount']=$gross*$data1;
		
$return_arr[] = array(
                    "ctype" => $data,
                    "margin" => $_SESSION['marginAmount'],
					"netA" => $_SESSION['netAm'],
					 "amountP" => $_SESSION['amountP']);
					 echo json_encode($return_arr);
?>