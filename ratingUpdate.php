<?php
	session_start();
	$type = $_SESSION['usertype'];
	include("dbConnection.php");
	
	if($type == "Zonal" || $type == "Master"){
	
		$json_data = file_get_contents('php://input');
		$json = json_encode($json_data);
		$data = json_decode($json_data,true); 
	
		
		// 	UPDATE EMPLOYEE RATING
		if(isset($data['employeeRatingUpdate'])){
			$empId = $data['employeeid'];
			$rating = $data['rating'];
			
			$updateQuery = "UPDATE employee
			SET rating='$rating'
			WHERE empId='$empId'";
			
			$response = [];
			if(mysqli_query($con, $updateQuery)){
				$response = [
					"message"=>"Employee Rating Successfully Updated",
					"employee"=>$empId,
					"rating"=>$rating
				];
			}
			else{
				$response = [
					"error"=>"Error, Something Went Wrong"
				];
			}
			echo json_encode($response);		
		}
		
		// UPDATE BRANCH RATING
		if(isset($data['branchRatingUpdate'])){
			$branchId = $data['branchid'];
			$rating = $data['rating'];
			
			$updateQuery = "UPDATE branch
			SET rating='$rating'
			WHERE branchId='$branchId'";
			
			$response = [];
			if(mysqli_query($con, $updateQuery)){
				$response = [
					"message"=>"Branch Rating Successfully Updated",
					"branchId"=>$branchId,
					"rating"=>$rating
				];
			}
			else{
				$response = [
					"error"=>"Error, Something Went Wrong"
				];
			}
			echo json_encode($response);
		}	
		
	}
	
?>