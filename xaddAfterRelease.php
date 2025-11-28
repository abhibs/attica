<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");
	$branchCode = $_SESSION['branchCode'];
	$date = date('Y-m-d');
    $time = date("h:i:s");
	
	if (isset($_POST['submitTerminate'])) {
		
		$rid = $_POST['rid'];
		$ckm = $_POST['ckm'];
		$remarks = $_POST['remark'];
		
		$amount = $_POST['relAmount'];
		$relCash = $_POST['relCash'];
		$relIMPS = $_POST['relIMPS'];
		
		if (file_exists($_FILES['rel']['tmp_name'])) {
			$filename = date('Ymdhis');
			$folder = "ReleaseDocuments/";
			$file = $_FILES['rel']['name'];
			$file_loc = $_FILES['rel']['tmp_name'];
			//$file_size = $_FILES['rel']['size'];
			//$file_type = $_FILES['rel']['type'];
			//$new_size = $file_size / 1024;
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$final_file = str_replace($new_file_name, $filename . $rid . 'REL' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $final_file);
		}
		else{
			$final_file = '';
		}
		$sqlA = "UPDATE releasedata SET amount='$amount',relCash='$relCash',relIMPS='$relIMPS', relDoc3='$final_file',status='Terminated',Remarks='$remarks' WHERE rid='$rid';";
		
		$avail = $_POST['avail'];
		$commAmount = $_POST['commAmount'];
		$name = $_POST['custName'];
		$phone = $_POST['custPhone'];
		$sqlA .= "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
		VALUES('$avail','$commAmount','recovery','$branchCode','','','','','','','Approved','$date','$time','$name','$phone')";		
		
		if (mysqli_multi_query($con, $sqlA)) {
			echo "<script type='text/javascript'>alert('Release Billing Is Terminated !!!')</script>";
			//echo "<script>setTimeout(\"location.href = 'releaseCommisionInvoice.php?rid=".base64_encode($rid)."';\",150);</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
		} 
		else {
			echo "<script>alert(Failed TO Upload the Data!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
		}
	}
	
	if(isset($_POST['submitReleaseDone'])){
		$rid = $_POST['rid'];
		$sqlA = "UPDATE releasedata SET flag=1 WHERE rid='$rid'";
		
		if (mysqli_query($con, $sqlA)) {
			echo 1;
		} 
		else {
			echo 0;
		}
	}
	
?>