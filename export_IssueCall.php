<?php session_start();
	$type=$_SESSION['usertype'];
	include("dbConnection.php");
	$date=date('Y-m-d');
	if(isset($_POST["export_data"])){
		$filename = "issuecall_data_export_".date('Y-m-d') . ".xls";
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"$filename\"");	
		$extra= mysqli_query($con,"SELECT name,remarks,comment,issue FROM `walkin` 
		where branchId in (select branchId from branch
		where state IN('Tamilnadu','Pondicherry','Karnataka','Andhra Pradesh','Telangana')) and date='$date'");
		$columnHeader = '';  
		$columnHeader ="Name" . "\t" . "Branch Remarks" . "\t" . "Issue Call Comment" ."\t" . "Type" . "\t";
		$setData = '';
		
		while($rec = mysqli_fetch_assoc($extra)){	
			$rowData='';
			foreach($rec as $value)	{
				$value='"' .$value. '"' . "\t";
				$rowData.=$value;
			}
			$setData .=trim($rowData). "\n";
		}	
		echo ucwords($columnHeader) . "\n" . $setData . "\n";	
	}
?>