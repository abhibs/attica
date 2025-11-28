<?php
	session_start();
	include("dbConnection.php");
	
	if(isset($_POST['submitVerifyExp'])){
		$id = $_POST['id'];
		$amount= $_POST['amount'];
		$remarks = $_POST['remarks'];
		$status = "Verified";
		$sql ="UPDATE expense SET status='$status',amount='$amount',remarks='$remarks' WHERE id='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Expense Verified!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewExpense.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error Updating expense!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewExpense.php';\",150);</script>"; 
		}
	}
	else if(isset($_POST['submitRejectExp'])){
		$id = $_POST['id'];
		$amount= $_POST['amount'];
		$remarks = $_POST['remarks'];
		$status = "Rejected";
		$sql ="UPDATE expense SET status='$status',remarks='$remarks' WHERE id='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Expense Rejected!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewExpense.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error Updating expense!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xviewExpense.php';\",150);</script>"; 
		}
	}