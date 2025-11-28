<?php

	include("dbConnection.php");

	$date = date("Y-m-d");
	$branchId = $_GET['branchId'];
	// $branchId = 'AGPL001';

	$str = file_get_contents(dirname(__FILE__).'/questions.json');
	$data = json_decode($str, true);

	$answeredQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total_count, SUM(CASE WHEN answer LIKE '%Ask me later%' THEN 1 ELSE 0 END) AS later_count
		FROM quiz_branch_data 
		WHERE date='$date' AND branchId='$branchId'"));

	$survey = [];
	$mainQuestions = [];
	$subQuestions = [];

	function getSubQuestions($options){
		global $data, $subQuestions;
		if($options != null){
			$len = count($options);
			foreach($options as $key=>$value){
				if($value != null){
					$subQuestions[$value] = $data[$value];
					getSubQuestions($data[$value]['options']);
				}		
			}
		}
	}

	if($answeredQuery['total_count'] == 0){

		$sql = mysqli_fetch_assoc(mysqli_query($con, "SELECT GROUP_CONCAT(qid) As qid FROM quiz_schedule WHERE date='$date'"));
		$questions = explode(",", $sql['qid']);

		$len = count($questions);
		for($i=0; $i<$len; $i++){
			$mainQuestions[$questions[$i]] = $data[$questions[$i]];
			getSubQuestions($data[$questions[$i]]['options']);
		}

		$survey = [
			"status"=>"questions",
			"questions"=>[
				"main"=>$mainQuestions,
				"sub"=>$subQuestions
			]		
		];

		echo json_encode($survey);
		
	}
	else{
		if($answeredQuery['later_count'] == 0){

			$sms_array = [];

			$sql = mysqli_fetch_assoc(mysqli_query($con, "SELECT GROUP_CONCAT(qid) AS qid FROM quiz_schedule WHERE date='$date'"));		
			$questions = explode(",", $sql['qid']);

			$sms_array = [];
			foreach ($questions as $key => $value) {

				if($data[$value]['sms'] != "" && array_search($data[$value]['sms'], $sms_array) === false){
					array_push($sms_array, $data[$value]['sms']);
				}
			}

			$sms_string = implode(",", $sms_array);
			$survey = [
				"status"=>"sms",
				"url"=>"https://atticagold.in/utils/feedback/upload/?branch=".$branchId."&date=".$date."&ids=".$sms_string
			];

			echo json_encode($survey);

		}
		else if($answeredQuery['later_count'] > 0){

			$sql = mysqli_fetch_assoc(mysqli_query($con, "SELECT GROUP_CONCAT(qid) AS qid
				FROM quiz_branch_data
				WHERE date='$date' AND answer LIKE '%Ask me later%' AND branchId='$branchId'"));
			$questions = explode(",", $sql['qid']);

			$len = count($questions);
			for($i=0; $i<$len; $i++){
				$mainQuestions[$questions[$i]] = $data[$questions[$i]];
			}

			$survey = [
				"status"=>"questions",
				"questions"=>[
					"main"=>$mainQuestions,
					"sub"=>$subQuestions
				]		
			];

			echo json_encode($survey);

		}
	}

?>
