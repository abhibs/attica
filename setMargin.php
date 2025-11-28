<?php 
session_start();
$data=$_POST['data'];
$data1=$_SESSION['netAm']-$data;
$_SESSION['netRel']=$data1;
$_SESSION['amountP']=round($data1,0);
echo $data1;
?>