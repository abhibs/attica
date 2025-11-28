<?php
	session_start();
	date_default_timezone_set("Asia/Kolkata");

	$json_data = file_get_contents('php://input');
	$request_data = json_decode($json_data, true);	

	include("dbConnection.php");	
	$date = date('Y-m-d');

	// GET REGISTERED CUSTOMER DATA
	if(isset($request_data['getRegisteredData'])){

		$branchString = $request_data['branchString'];
		//$query = "SELECT Id, branch, customer, time, status FROM everycustomer WHERE date='$date' AND status IN ('Begin', 'Blocked') AND branch IN (". $branchString .")";
		$query = "SELECT Id, branch,ornament_docs,quotation,customer, time, status FROM everycustomer WHERE date='$date' AND status IN ('Begin', 'Blocked', '0') AND branch IN (". $branchString .")";

		$data = [];
		if($result = mysqli_query($con, $query)){
			while($row = mysqli_fetch_assoc($result)){
				$data[] = $row;
			}
		}
		else{
			$data['error'] = "Error";
		}

		echo json_encode($data);
	}

?>
