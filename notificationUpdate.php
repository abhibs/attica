<?php
	session_start();
	date_default_timezone_set("Asia/Kolkata");
	$type = $_SESSION['usertype'];
	include("dbConnection.php");
	
	// 	INSERT NOTIFICATION
	if(isset($_POST['insertNotification'])){
		$sender = $_POST['sender'];
		$receiver = $_POST['receiver'];
		$branch = $_POST['branch'];
		$info = $_POST['info'];
		$remarks = $_POST['remarks'];
		
		$date = date('Y-m-d');
		$time = date("h:i:s");
		
		$insertQuery = "INSERT INTO notification(sender, receiver, branch, info, remarks, date, time, status) VALUES ('$sender', '$receiver', '$branch', '$info', '$remarks', '$date', '$time', 'Pending')";
		
		if(mysqli_query($con, $insertQuery)){
			echo "<script>setTimeout(\"location.href = 'zonalApprovalRemarks.php';\", 150);</script>";
		} 
		else {
			echo "<script>alert('Error Occurred, Please try again!!!');</script>";
			echo "<script>setTimeout(\"location.href = 'zonalApprovalRemarks.php';\", 150);</script>";			
		}
	}
	
	// UPDATE NOTIFICATION
	if(isset($_GET['updateNotificationUpdate'])){
		$id = $_GET['rowid'];
		
		$updateQuery = "UPDATE notification
		SET status='Done'
		WHERE id='$id'";
		
		$response = [];
		if(mysqli_query($con, $updateQuery)){
			$response = [
			"message"=>"Updated Successfully Updated",			
			];
		}
		else{
			$response = [
			"error"=>"Error, Something Went Wrong"
			];
		}
		echo json_encode($response);
	}	
	
?>	