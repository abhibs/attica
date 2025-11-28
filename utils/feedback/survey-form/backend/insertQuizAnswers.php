<?php
	session_start();

	$json_data = file_get_contents('php://input');
	$request_data = json_decode($json_data, true);

	include("dbConnection.php");

	$date = date("Y-m-d");
	$time = date("h:i:s");

	$branchId = $request_data['branchId'];
	$empId = $request_data['empId'];
	$data = $request_data['data'];

	// SMS CODE SEGMENT
	$sms_array = [];

	$answeredQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total_count
		FROM quiz_branch_data 
		WHERE date='$date' AND branchId='$branchId'"));

	if($answeredQuery['total_count'] == 0){
		$len = count($data);
		$query = "";

		for($i=0; $i<$len; $i++){
			$arr = $data[$i];
			$query .= "INSERT INTO quiz_branch_data(branchId, empId, qid, question, answer, date, time) VALUES ('$branchId', '$empId','$arr[id]', '$arr[question]', '$arr[answer]', '$date', '$time');";

			// SMS CODE SEGMENT
			if($arr['sms'] != "" && array_search($arr['sms'], $sms_array) === false){
				array_push($sms_array, $arr['sms']);
			}
		}

		// $query = rtrim($query, ',');
		if(mysqli_multi_query($con, $query)){
			$sms_string = implode(",", $sms_array);
			echo json_encode(["message"=>"Success", "url"=>"https://atticagold.in/utils/feedback/upload/?branch=".$branchId."&date=".$date."&ids=".$sms_string]);
		}
		else{
			echo json_encode(["message"=>"Error"]);
		}
	}
	else{
		$len = count($data);
		$query = "";

		for($i=0; $i<$len; $i++){
			$arr = $data[$i];
			$query .= "UPDATE quiz_branch_data SET answer='$arr[answer]' WHERE qid='$arr[id]' AND date='$date' AND branchId='$branchId';";

			// SMS CODE SEGMENT
			if(array_search($arr['sms'] != "" && $arr['sms'], $sms_array) === false){
				array_push($sms_array, $arr['sms']);
			}
		}

		if(mysqli_multi_query($con, $query)){		
			$sms_string = implode(",", $sms_array);
			echo json_encode(["message"=>"Success", "url"=>"https://atticagold.in/utils/feedback/upload/?branch=".$branchId."&date=".$date."&ids=".$sms_string]);
		}
		else{
			echo json_encode(["message"=>"Success"]);
		}
	}

?>
