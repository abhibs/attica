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
		
		$sql = "UPDATE trans 
		SET status='Approved',remarks='$username', approvetime='$time'  
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
			$redirectURL='xviewTransaction.php';
			header("location:approvalMessage.php?name=" . $_POST['name'] . "&phone=" . $_POST['mobile'] . "&grossW=" . $_POST['grossW'] . "&amount=" . $_POST['amountPaid']. "&releaseAmount=" . $_POST['releaseAmount']."&redirectURL=" . $redirectURL);
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransaction.php';\",150);</script>";
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
			echo header("location:xviewTransaction.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransaction.php';\",150);</script>";
		}
		
	}
	
	/* --------------------------------- IMPS BILLS -------------------------------------- */
	
	if(isset($_POST['submitVerifyIMPS'])){
		
		$transId = $_POST['tid'];
		if($_POST['impsStatus'] == '0'){
			$status = 'Approved';
		}
		else{
			$status = 'Verified';
		}
		$sql = "UPDATE trans 
		SET status='$status',remarks='$username', approvetime='$time'  
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
			$redirectURL='xviewTransactionIMPS.php';
			header("location:approvalMessage.php?name=" . $_POST['name'] . "&phone=" . $_POST['mobile'] . "&grossW=" . $_POST['grossW'] . "&amount=" . $_POST['amountPaid']. "&releaseAmount=" . $_POST['releaseAmount']. "&redirectURL=" . $redirectURL);
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransactionIMPS.php';\",150);</script>";
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
			echo header("location:xviewTransactionIMPS.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransactionIMPS.php';\",150);</script>";
		}
		
	}
	
	/* --------------------------------- CASH & IMPS BILLS -------------------------------------- */
	
	if(isset($_POST['submitVerifyCashIMPS'])){
		
		$transId = $_POST['tid'];
		if($_POST['impsStatus'] == '0'){
			$status = 'Approved';
		}
		else{
			$status = 'Verified';
		}
		$sql = "UPDATE trans 
		SET status='$status',remarks='$username', approvetime='$time' 
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
			$redirectURL='xviewTransactionBoth.php';
			header("location:approvalMessage.php?name=" . $_POST['name'] . "&phone=" . $_POST['mobile'] . "&grossW=" . $_POST['grossW'] . "&amount=" . $_POST['amountPaid']. "&releaseAmount=" . $_POST['releaseAmount']. "&redirectURL=" . $redirectURL);
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransactionBoth.php';\",150);</script>";
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
			echo header("location:xviewTransactionBoth.php");
		}
		else{
		    echo "<script type='text/javascript'>alert('Error Approving!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewTransactionBoth.php';\",150);</script>";
		}
		
	}
?>
