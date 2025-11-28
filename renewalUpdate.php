<?php 
	
	error_reporting(E_ERROR | E_PARSE);
    session_start();
    include("dbConnection.php");
	$date=date('Y-m-d');
	
	/* --------------------------  INTERNET RENEWAL ----------------------------------------- */
	if(isset($_POST['internetRenewal'])){
		$id = $_POST['id'];
		$renewaldate = $_POST['date'];
		
		if($id != ''){
			$sql = "UPDATE renewal SET internet='$renewaldate' WHERE id='$id'";
			if(mysqli_query($con, $sql)){
				$diff = round((strtotime($renewaldate) - strtotime($date))/86400);
				echo json_encode(['status'=>'0', 'diff'=>$diff]);
			}
			else{
				echo json_encode(['status'=>'1', 'diff'=>0]);
			}
		}
		else{
			echo json_encode(['status'=>'2', 'diff'=>0]);
		}
	}
	
	
	/*    UPDATE RENEWAL TABLE  @shopLicense.php    */
	if(isset($_POST['shopLicense_renewalUpdate'])){
		
		$row_id = $_POST['id'];
		$selectedDate = $_POST['date'];
		$diff = ($selectedDate != '') ? round((strtotime($selectedDate) - strtotime($date))/86400) : '';
		$validity = $_POST['valid'];
		
		$jsonData = '';
		
		if($_POST['update'] == 'date'){
			$sql = "UPDATE renewal SET shop_license='$selectedDate' WHERE id='$row_id'";
		}
		else if($_POST['update'] == 'validity'){
			$sql = "UPDATE renewal SET shop_license_validity='$validity' WHERE id='$row_id'";
		}
		
		if(mysqli_query($con, $sql)){
			if($validity == 'Processing'){
				$jsonData = [
				'result'=>'0',
				'date' => $selectedDate,
				'diff' => $diff,
				'status' => "Processing",
				'color' => 'table-row-processing'
				];
			}
			else if($validity == 'Life Time'){
				$jsonData = [
				'result'=>'0',
				'date' => $selectedDate,
				'diff' => "",
				'status' => "Life Time",
				'color' => 'table-row-success'
				];
			}
			else if($validity == 'One Time'){
				if($diff == ''){
					$jsonData = [
					'result'=>'0',
					'date' => $selectedDate,
					'diff' => $diff,
					'status' => "Unknown",
					'color' => 'table-row-info'
					];
				}
				else{
					if($diff > 15){
						$jsonData = [
						'result'=>'0',
						'date' => $selectedDate,
						'diff' => $diff,
						'status' => "Active",
						'color' => 'table-row-dummy'
						];
					}
					else if($diff < 15 && $diff >= 0){
						$jsonData = [
						'result'=>'0',
						'date' => $selectedDate,
						'diff' => $diff,
						'status' => "Warning",
						'color' => 'table-row-warning'
						];
					}
					else if($diff < 0){
						$jsonData = [
						'result'=>'0',
						'date' => $selectedDate,
						'diff' => $diff,
						'status' => "Expired",
						'color' => 'table-row-expired'
						];
					}
				}
			}
			else{
				$jsonData = [
				'result'=>'0',
				'date' => $selectedDate,
				'diff' => $diff,
				'status' => "Unknown",
				'color' => 'table-row-info'
				];
			}
			
			echo json_encode($jsonData);
		}
		else{
			echo json_encode(['status'=>'1']);
		}
		
	}
	
?>	