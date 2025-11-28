<?php
	
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	
	// ---------------------------------- UPDATE GOLD RATE | @updateDisplay.php  -----------------------------------
	if(isset($_POST['updateGoldRate'])){
		$rate = $_POST['rate'];
		$sql = "UPDATE misc SET day='$rate' WHERE purpose='Gold Rate'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}	
	}
	
	// ---------------------------------- UPDATE SILVER RATE | @updateDisplay.php  -----------------------------------
	if(isset($_POST['updateSilverRate'])){
		$rate = $_POST['rate'];
		$sql = "UPDATE misc SET day='$rate' WHERE purpose='Silver Rate'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}
	}
	
	// ---------------------------------- UPDATE KA CLOSED BRANCH | @updateDisplay.php  -----------------------------------
	if(isset($_POST['closeKAbranch'])){
		$branches = $_POST['branches'];
		$sql = "UPDATE misc SET day='$branches' WHERE purpose='KA Closed'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}		
	}
	
	// ---------------------------------- UPDATE TN CLOSED BRANCH | @updateDisplay.php  -----------------------------------
	if(isset($_POST['closeTNbranch'])){
		$branches = $_POST['branches'];
		$sql = "UPDATE misc SET day='$branches' WHERE purpose='TN Closed'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}		
	}
	
	// ---------------------------------- UPDATE APT CLOSED BRANCH | @updateDisplay.php  -----------------------------------
	if(isset($_POST['closeAPTbranch'])){
		$branches = $_POST['branches'];
		$sql = "UPDATE misc SET day='$branches' WHERE purpose='APT Closed'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}		
	}
	
	// ---------------------------------- REMOVE CLOSED BRANCH | @updateDisplay.php  -----------------------------------
	if(isset($_POST['updateClosed'])){
		$state = $_POST['state'];
		$branches = $_POST['branches'];
		
		if($state == "ka"){
			$sql = "UPDATE misc SET day='$branches' WHERE purpose='KA Closed'";
		}
		else if($state == "tn"){
			$sql = "UPDATE misc SET day='$branches' WHERE purpose='TN Closed'";
		}
		else if($state == "apt"){
			$sql = "UPDATE misc SET day='$branches' WHERE purpose='APT Closed'";
		}
		
		if(mysqli_query($con, $sql)){
			echo json_encode(["message"=>"success"]);
		}
		else{
			echo json_encode(["message"=>"error"]);
		}			
	}
	
	// ---------------------------------- UPDATE SPECIAL RATE | @updateDisplay.php  -----------------------------------
	if(isset($_POST['updateSpecialRate'])){
		$rate = $_POST['rate'];
		$sql = "UPDATE misc SET day='$rate' WHERE purpose='Special Rate'";
		
		if(mysqli_query($con, $sql)){
			echo json_encode([
				"message"=>"success"
			]);
		}
		else{
			echo json_encode([
				"message"=>"error"
			]);
		}	
	}
	
?>
