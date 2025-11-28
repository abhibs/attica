<?php
	session_start();
	include("dbConnection.php");
	$date = date('Y-m-d');
	$time = date("h:i:s");
	
	if(isset($_POST['ecID']) && $_POST['ecID']!='' && isset($_POST['quotationImage']) && $_POST['quotationImage']!=''){
		$id = $_POST['ecID'];
		$branchId = $_POST['branchId'];
		
		$image_parts = explode(";base64,",$_POST['quotationImage']);
		$image_base64 = base64_decode($image_parts[1]);	
		$quotationFile = $branchId.date('YmdHis').uniqid().'.png';
		$file = 'QuotationImage/'.$quotationFile;
		file_put_contents($file, $image_base64);
		
		$data = [];
		$data['image'] = "$quotationFile";
		$data['status'] = 1;
		$data['rate'] = $_POST['givenRate'];
		$encoded = json_encode($data);
		
		$sql = "UPDATE everycustomer SET quotation='$encoded' WHERE Id='$id'";
		if(mysqli_query($con,$sql)){
			echo header("location:xeveryCustomer.php");
		}
		else{
			echo "<script>alert('ERROR OCCURRED , PLEASE TRY AGAIN')</script>";
			echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
		}
	}
	else if(isset($_POST['quotationAndEnquiry'])){
		$rowId = $_POST['rowId'];
		$customerData = mysqli_fetch_assoc(mysqli_query($con, "SELECT customer, contact FROM everyCustomer WHERE Id='$rowId'"));
		
		$name = $customerData['customer'];
		$mobile = $customerData['contact'];
		$gold = $_POST['gold'];
		$having = $_POST['having'];
		$metal = $_POST['metal'];
		$grossW = $_POST['grossW'];
		$netW = $_POST['netW'];
		$purity = $_POST['purity'];
		$branchId = $_SESSION['branchCode'];
		$remarks = $_POST['remarks'];
		$rate = $_POST['rate'];
		$releaseAmount = isset($_POST['releaseAmount']) ? $_POST['releaseAmount'] : "";
		
		$image_parts = explode(";base64,",$_POST['quotationImage']);
		$image_base64 = base64_decode($image_parts[1]);	
		$quotationFile = $branchId.date('YmdHis').uniqid().'.png';
		$file = 'QuotationImage/'.$quotationFile;
		file_put_contents($file, $image_base64);
		
		$sql = "INSERT INTO walkin(name, mobile, gold, havingG, metal, issue, gwt, nwt, purity, ramt, branchId, agent_id, followUp, comment, remarks, zonal_remarks, status, emp_type, date, indate, time, quotation, bills, quot_rate) VALUES('$name', '$mobile', '$gold', '$having', '$metal', '', '$grossW', '$netW', '$purity', '$releaseAmount', '$branchId', '', '', '', '$remarks', '', '', '', '$date', '', '$time', '$quotationFile', '0', '$rate');";
		$sql .= "UPDATE everyCustomer SET status='Enquiry', quotation='$quotationFile' WHERE Id='$rowId'";
		
		$response = [];
		if(mysqli_multi_query($con, $sql)) {
			$response = ["status"=>"successful"];
		} 
		else {
			$response = ["status"=>"error"];
		}		
		echo json_encode($response);
	}
	else{
		echo "<script>alert('BROWSER ERROR !!!')</script>";
		echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
	}