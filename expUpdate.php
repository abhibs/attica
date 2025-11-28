<?php
	session_start();
	include("dbConnection.php");
	
	// UPDATE EXPENSE AMOUNT ( @ editExpense.php )
	if(isset($_POST['editExpense'])){
		$id = $_POST['id'];
		$amount= $_POST['amount'];
		$remarks = $_POST['remarks'];
		$sql= "UPDATE expense SET amount='$amount', remarks='$remarks' WHERE id='$id'";
		if(mysqli_query($con,$sql)){
			header("location:expenseApproval.php");
		}
		else{
			echo "<script>alert('Error Occured,Please Try Again')</script>";
			echo "<script>setTimeout(\"location.href = 'expenseApproval.php';\",150);</script>";
		}
	}
?>
