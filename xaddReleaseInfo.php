<?php
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	
	/*  ADD RELEASE GOLD INFO ( @ xreleaseGold.php ) */
	if(isset($_POST['submitRel'])){
		
		if($_SESSION['bal'] < $_POST['cash']){
			echo "<script type='text/javascript'>alert('Insufficient Funds For Release!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseGold.php';\",150);</script>";
		}
		
		// RELEASEINFO
		$release = mysqli_prepare($con, "INSERT INTO releasedata(releaseID, BranchId, customerId, name, phone, relPlaceType, relPlace, relDoc1, relDoc2, relDoc3, type, amount, relCash, relIMPS, relWith, pledgeSlips, bankName, branchName, accountHolder, relationship, loanAccNo, accountNo, IFSC, bProof, cProof, relGrossW, relNetW, relPurity, TEempId, TEcash, status, flag, date, time, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, '', '', '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', ?, ?, ?, ?, ?, ?, 0, ?, ?, '')");
		mysqli_stmt_bind_param($release, "ssssssssssssssssssssssssssss", $releaseId, $branchId, $customerId, $name, $phone, $relPlaceType, $relPlace, $type, $amount, $relCash, $relIMPS, $relWith, $pledgeSlips, $bankName, $branchName, $accountHolder, $relationship, $loanAccNo, $accountNo, $ifsc, $relGrossW, $relNetW, $relPurity, $TEempId, $relCash, $status, $date, $time);
		
		// EVERYCUSTOMER
		$everyCustomer = mysqli_prepare($con, "UPDATE everycustomer SET status='Release' WHERE contact=? AND date=?");
		mysqli_stmt_bind_param($everyCustomer, "ss", $phone, $date);
		
		$releaseId = mt_rand(100000,999999);
		$branchId = $_SESSION['branchCode'];
		$customerId = $_SESSION['customerID'];
		$name = $_POST['name'];
		$phone = $_POST['contact'];
		
     	$relPlaceType = $_POST['pPlace'];
     	$relPlace = $_POST['pledgeName'];
     	$type = $_POST['releasetype'];
    	
     	$relCash = $_POST['cash'];
     	$relIMPS = isset($_POST['imps']) ? $_POST['imps'] : 0;
		$amount = intval($relCash) + intval($relIMPS);
		
		if($relPlaceType == "BANK" && $type == "CASH/IMPS"){
			$relWith = "BANK";
		}
		else if($relPlaceType == "NBFC" && $type == "CASH/IMPS"){
			$relWith = $_POST['releasewith'];
		}
		else{
			$relWith = "";
		}
     	$pledgeSlips = isset($_POST['pledge_number']) ? $_POST['pledge_number'] : 0;
		
     	$bankName = isset($_POST['bankname']) ? $_POST['bankname'] : "";
     	$branchName = isset($_POST['branchname']) ? $_POST['branchname'] : "";
     	$accountHolder = isset($_POST['accountHolder']) ? $_POST['accountHolder'] : "";
     	$relationship = isset($_POST['relationship']) ? $_POST['relationship'] : "";
     	$loanAccNo = isset($_POST['loan_account_number']) ? $_POST['loan_account_number'] : "";
     	$accountNo = isset($_POST['accountnumber']) ? $_POST['accountnumber'] : "";
     	$ifsc = isset($_POST['ifsc']) ? $_POST['ifsc'] : "";
		
     	$relGrossW = $_POST['relGrossW'];
     	$relNetW = $_POST['relNetW'];
     	$relPurity = $_POST['relPurity'];
		
     	$TEempId = $_POST['TEId'];
        $date = date("Y-m-d");
		$time = date("H:i:s");
     	$status = 'Begin';
		
		if(mysqli_stmt_execute($release)){
			mysqli_stmt_execute($everyCustomer);
			echo header("location:xuploadDocsRel.php?rid=".$releaseId."&mob=".$phone);
		}
		else{
			echo "<script type='text/javascript'>alert('Error Storing Data!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseGold.php';\",150);</script>";
		}
		
	}
?>
