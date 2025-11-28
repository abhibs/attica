<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$date = date("Y-m-d");
	include("dbConnection.php");
	
	$id = $_POST['id'];
	$impsId = $_POST['imps'];
	
	$sql1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT ple,paymentType FROM trans WHERE id='$id'"));
	
	//if($sql1['paymentType'] == 'NEFT/RTGS'){
	if ($impsId != 'CASH'){
		$sql2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT Bproof FROM bankdetails WHERE ID='$impsId'"));
		$impsFile = $sql2['Bproof'];
	}
	else{
		$impsFile = '';
	}
	
	$ple = ($sql1['ple'] != '') ? $sql1['ple'] : 0;
	$Bproof= ($impsFile != '' ) ? $impsFile : 0;
	
	$data = [];
	$data['ple'] = $ple;
	$data['Bproof'] = $Bproof;	
	
	echo json_encode($data);