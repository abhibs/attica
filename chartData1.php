<?php
	include("dbConnection.php");
	
	if($_POST['type'] == 'Monthly'){
		$months = $_POST['months'];
		$from = date("Y-m-01", strtotime( date( 'Y-m-01' )." -$months months"));
		$to = date('Y-m-d');
		$branchId = $_POST['branchId'];
		$state = '';
		
		switch($branchId){
			case "All Branches": $state=''; break;
			case "Bangalore"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')"; break;
			case "Karnataka"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')"; break;
			case "Chennai"     : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Chennai')"; break;
			case "Tamilnadu"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')"; break;
			case "Hyderabad"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')"; break;
			case "AP-TS"       : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')"; break;
			case "Pondicherry" : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')"; break;
			default            : $state=" AND branchId='$branchId'"; break;
		}
		
		$sql = "SELECT YEAR(date) AS year,MONTHNAME(date) AS month,round(SUM(grossW)) AS grossW
		FROM trans
		WHERE date BETWEEN '$from' AND '$to' AND status='Approved' AND metal='Gold'".$state."
		GROUP BY YEAR(date),MONTH(date)";
		$execSQL = mysqli_query($con,$sql);
		
		$i = 0;
		$data = [];
		while($row = mysqli_fetch_assoc($execSQL)){
			$data[$i][0] = $row['month']."\n".$row['year'];
			$data[$i][1] = $row['grossW'];
			$i++;
		}
		echo json_encode($data);
	}
	else if($_POST['type'] == 'Daily'){
		$days = $_POST['days'];
		$from = date("Y-m-01", strtotime( date( 'Y-m-01' )." -$days days"));
		$to = date('Y-m-d');
		$branchId = $_POST['branchId'];
		$state = '';
		
		switch($branchId){
			case "All Branches": $state=''; break;
			case "Bangalore"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')"; break;
			case "Karnataka"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')"; break;
			case "Chennai"     : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Chennai')"; break;
			case "Tamilnadu"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')"; break;
			case "Hyderabad"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')"; break;
			case "AP-TS"       : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')"; break;
			case "Pondicherry" : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')"; break;
			default            : $state=" AND branchId='$branchId'"; break;
		}
		
		$sql = "SELECT date,round(SUM(grossW)) AS grossW,rate
		FROM trans
		WHERE date BETWEEN '$from' AND '$to' AND status='Approved' AND metal='Gold'".$state."
		GROUP BY date";
		$execSQL = mysqli_query($con,$sql);
		
		$i = 0;
		$data = [];
		while($row = mysqli_fetch_assoc($execSQL)){
			$data[$i][0] = date('d-m-Y', strtotime($row['date']));
			$data[$i][1] = $row['grossW'];
			$data[$i][2] = $row['rate'];
			$i++;
		}
		echo json_encode($data);
	}
?>