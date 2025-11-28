<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$date = date("Y-m-d");
	include("dbConnection.php");

	$tid = $_POST['id'];
	$impsId = $_POST['imps'];

	$sql1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT T.paymentType,C.idFile,C.addFile,C.cusThump,C.custSign FROM trans T,customer C WHERE T.id='$tid' AND T.phone=C.mobile"));
		//if ($sql1['paymentType'] == 'NEFT/RTGS' && $impsId != 'CASH') {
		if ($impsId != 'CASH'){
			$sql2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT Bproof FROM bankdetails WHERE ID='$impsId'"));
			$Bproof = ($sql2['Bproof'] != '') ? $sql2['Bproof'] : 0;
		} else {
			$Bproof = 0;
		} 

	$idFile = ($sql1['idFile'] != '') ? $sql1['idFile'] : 0;
	$addFile = ($sql1['addFile'] != '') ? $sql1['addFile'] : 0;
	$custSign = ($sql1['custSign'] != '') ? $sql1['custSign'] : 0;
	$cusThump = ($sql1['cusThump'] != '') ? $sql1['cusThump'] : 0;

	$data = [];
	$data['idFile'] = $idFile;
	$data['addFile'] = $addFile;
	$data['custSign'] = $custSign;
	$data['cusThump'] = $cusThump;
	$data['Bproof'] = $Bproof;

	echo json_encode($data);
