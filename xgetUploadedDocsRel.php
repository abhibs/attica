<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$date = date("Y-m-d");
include("dbConnection.php");

$rid = $_POST['rid'];
$phone = $_POST['mobile'];

$sql1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT c.idFile,c.addFile,,C.cusThump,C.custSign,r.relDoc1,r.cProof,r.bProof FROM releasedata r,customer c WHERE r.rid='$rid' AND r.phone=c.mobile LIMIT 1"));

$idFile = ($sql1['idFile'] != '') ? $sql1['idFile'] : 0;
$addFile = ($sql1['addFile'] != '') ? $sql1['addFile'] : 0;
$relDoc1 = ($sql1['relDoc1'] != '') ? $sql1['relDoc1'] : 0;
$cProof = ($sql1['cProof'] != '') ? $sql1['cProof'] : 0;
$bProof = ($sql1['bProof'] != '') ? $sql1['bProof'] : 0;
$custSign = ($sql1['custSign'] != '') ? $sql1['custSign'] : 0;
$cusThump = ($sql1['cusThump'] != '') ? $sql1['cusThump'] : 0;

$data = [];
$data['idFile'] = $idFile;
$data['addFile'] = $addFile;
$data['relDoc1'] = $relDoc1;
$data['cProof'] = $cProof;
$data['bProof'] = $bProof;
$data['custSign'] = $custSign;
$data['cusThump'] = $cusThump;

echo json_encode($data);
