<?php
    session_start();
	
	include("Config/SMS/sms_generator.php");
	
    $phone = $_POST['pho'];
    $add = $_POST['add'];
	
	$sms = new SMS($phone);
	$sms->branch_link($add);
    
    header("location:sms.php");
?>
