<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	if(isset($_POST['del'])){
		if(isset($_POST['mul']) && COUNT($_POST['mul']) > 0){
			$i = 0;
			$multiple = $_POST['mul'];
			$sql="UPDATE trans SET sta='Checked',staDate='$date'  WHERE ";
			foreach($multiple as $item_id){
				$i++;
				if($i == 1){
					$sql .="id = " . mysqli_real_escape_string($con,$item_id) . "";
				}
				else{
					$sql .=" OR id = " . mysqli_real_escape_string($con,$item_id) . "";
				}
			}
			
			//echo $sql;
			
			mysqli_query($con,$sql) or die (mysqli_connect_errno());
			header("location:goldSendPdf.php");
		}
		else{
			echo "<script type='text/javascript'>alert('CHECK AT LEAST ONE TRANSACTION TO GENERATE GOLD SEND REPORT')</script>";
			echo "<script>setTimeout(\"location.href = 'goldReports.php';\",150);</script>";
		}
	}
	
	else if(isset($_POST['delsilver'])){
		if(isset($_POST['muls']) && COUNT($_POST['muls']) > 0){
			$i = 0;
			$multiple = $_POST['muls'];
			$sql="UPDATE trans SET sta='Checked',staDate='$date'  WHERE ";
			foreach($multiple as $item_id){
				$i++;
				if($i == 1){
					$sql .="id = " . mysqli_real_escape_string($con,$item_id) . "";
				}
				else{
					$sql .=" OR id = " . mysqli_real_escape_string($con,$item_id) . "";
				}
			}
			
			//echo $sql;
			
			mysqli_query($con,$sql) or die (mysqli_connect_errno());
			header("location:silverSendPdf.php");
		}
		else{
			echo "<script type='text/javascript'>alert('CHECK AT LEAST ONE TRANSACTION TO GENERATE SILVER SEND REPORT')</script>";
			echo "<script>setTimeout(\"location.href = 'silverReport.php';\",150);</script>";
		}
	}
?>