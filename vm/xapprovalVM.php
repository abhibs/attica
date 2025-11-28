<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');
	$time = date("h:i:s");
	$username = $_SESSION['login_username'];
	
	/* --------------------------------- CASH BILLS -------------------------------------- */
	
	if(isset($_POST['submitApproveCash'])){
		
		$transId = $_POST['tid'];
		$livetimer = $_POST['livetimer'];		
		
		$sql = "UPDATE trans 
		SET status='Approved',remarks='$username', approvetime='$time' , livetimer= '$livetimer'
		WHERE id='$transId';";
		
		if(isset($_POST['rid'])){
			$sql .= "UPDATE releasedata SET status='Billed' WHERE rid='$_POST[rid]' AND date='$_POST[relDate]';";
		}
		
		if(isset($_POST['ornID']) && COUNT($_POST['ornID']) > 0){
			foreach (array_combine($_POST['ornID'], $_POST['typeInfo']) as $ornId => $value) {
				$sql .= "UPDATE ornament SET typeInfo='$value' WHERE ornamentId='$ornId' AND date='$date';";
			}
		}
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}		
		$remarks = $_POST['remarks'];
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			//echo header("location:xviewTransaction.php");
			header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
	
	if(isset($_POST['submitRejectCash'])){
		
		$transId = $_POST['tid'];
		$sql = "UPDATE trans 
		SET status='Rejected',remarks='$username', approvetime='$time'  
		WHERE id='$transId';";
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}
		$remarks = '';
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			echo header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
	
	/* --------------------------------- IMPS BILLS -------------------------------------- */
	
	if(isset($_POST['submitVerifyIMPS'])){

		$livetimer = $_POST['livetimer'];	
		
		$transId = $_POST['tid'];
		if($_POST['impsStatus'] == '0'){
			$status = 'Approved';
		}
		else{
			$status = 'Verified';
		}
		$sql = "UPDATE trans 
		SET status='$status',remarks='$username', approvetime='$time' , livetimer= '$livetimer' 
		WHERE id='$transId';";
		
		if(isset($_POST['ornID']) && COUNT($_POST['ornID']) > 0){
			foreach (array_combine($_POST['ornID'], $_POST['typeInfo']) as $ornId => $value) {
				$sql .= "UPDATE ornament SET typeInfo='$value' WHERE ornamentId='$ornId' AND date='$date';";
			}
		}
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}
		$remarks = $_POST['remarks'];
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			//echo header("location:xviewTransactionIMPS.php");
			header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
	
	if(isset($_POST['submitRejectIMPS'])){
		
		$transId = $_POST['tid'];
		$sql = "UPDATE trans 
		SET status='Rejected',remarks='$username', approvetime='$time'  
		WHERE id='$transId';";
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}
		$remarks = '';
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			echo header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
	
	/* --------------------------------- CASH & IMPS BILLS -------------------------------------- */
	
	if(isset($_POST['submitVerifyCashIMPS'])){

		$livetimer = $_POST['livetimer'];	
		
		$transId = $_POST['tid'];
		if($_POST['impsStatus'] == '0'){
			$status = 'Approved';
		}
		else{
			$status = 'Verified';
		}
		$sql = "UPDATE trans 
		SET status='$status',remarks='$username', approvetime='$time' , livetimer= '$livetimer'
		WHERE id='$transId';";
		
		if(isset($_POST['ornID']) && COUNT($_POST['ornID']) > 0){
			foreach (array_combine($_POST['ornID'], $_POST['typeInfo']) as $ornId => $value) {
				$sql .= "UPDATE ornament SET typeInfo='$value' WHERE ornamentId='$ornId' AND date='$date';";
			}
		}
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}
		$remarks = $_POST['remarks'];
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			//echo header("location:xviewTransactionBoth.php");
			$redirectURL='zbilllStatus1.php';
			header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
	
	if(isset($_POST['submitRejectCashIMPS'])){
		
		$transId = $_POST['tid'];
		$sql = "UPDATE trans 
		SET status='Rejected',remarks='$username', approvetime='$time'  
		WHERE id='$transId';";
		
		if(isset($_POST['detail'])){
			$detail = mysqli_real_escape_string($con,$_POST['detail']);
		}
		else{
			$detail = '';
		}
		$remarks = '';
		$sql .="INSERT INTO customerinfo(mobile,branchId,billId,idNum,addNum,detail,remarks,approval,date,time) VALUES ('$_POST[mobile]','$_POST[branchId]','$_POST[billId]','$_POST[idNum]','$_POST[addNum]','$detail','$remarks','$username','$date','$time')";
		
		if(mysqli_multi_query($con,$sql)){
			echo header("location:zbilllStatus1.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'zbilllStatus1.php';\",150);</script>";
		}
		
	}
?>

