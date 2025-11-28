<?php

	session_start();
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");

	// ---------------------------------- BRANCH TARGET | @xeveryCustomer.php  -----------------------------------
	if(isset($_GET['branchTarget'])){
		$branchId = $_GET['branch'];
		
		$fDate = date("Y-m-01",strtotime("-3 Month"));
		$lDate = date("Y-m-j", strtotime("last day of previous month"));
		$prevAvgWeight = "SELECT ROUND(COALESCE(SUM(grossW), 0)) AS prevGrossW
			FROM trans
			WHERE date BETWEEN '$fDate' AND '$lDate' AND status='Approved' AND metal='Gold' AND branchId='$branchId'";
			
			
		$fDateThis = date('Y-m-01');
		$lDateThis = date('Y-m-d');
		$currentMonthWeight = "SELECT ROUND(COALESCE(SUM(grossW), 0)) AS currGrossW
			FROM trans
			WHERE date BETWEEN '$fDateThis' AND '$lDateThis' AND status='Approved' AND metal='Gold' AND branchId='$branchId'";
			
			
		$target = [];
		if(
			($query1 = mysqli_query($con, $prevAvgWeight)) && 
			($query2 = mysqli_query($con, $currentMonthWeight))
		){
			$result1 = mysqli_fetch_assoc($query1);
			$result2 = mysqli_fetch_assoc($query2);
		
			$avg = ($result1['prevGrossW'] > 0) ? ROUND($result1['prevGrossW']/3) : 0;
			$curr = $result2['currGrossW'];
			
			$diff = ($avg + 1000) - $curr;
			
			$message = "";
			$text = "";
			if($diff > 0){
				$message = "Need +$diff gms to Achieve Target";
			}
			else if($diff <= 0){
				$message = "Target Achieved";
			}
			
			$target = [
				"status" => "success",
				"message" => $message
			];
			
		}
		else{
			$target = [
				"status" => "error",
				"message" => "Something Went Wrong!!!"
			];
		}
		
		echo json_encode($target);
	}

?>
