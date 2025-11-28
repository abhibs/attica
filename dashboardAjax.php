<?php
	session_start();
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	if(isset($_GET['branchId']) && $_GET['branchId']!=''){
		$branchId = $_GET['branchId'];
		
		//GROSS AMOUNT AND NET AMOUNT
		$businessQuery = mysqli_fetch_assoc(mysqli_query($con,"SELECT ROUND(SUM(grossW),2) as grossW,ROUND(SUM(netA),2) as netA FROM trans where date='$date' AND branchId='$branchId' AND metal='Gold' AND status='Approved'"));
	
		/* OPENING BALANCE */
		function openBalance(){
			$rowA = mysqli_fetch_array(mysqli_query($GLOBALS['con'],"SELECT forward,balance,total,diff FROM closing WHERE branchId='$GLOBALS[branchId]' ORDER BY date DESC LIMIT 1"));
			if(isset($rowA['forward'])){
				if($rowA['forward'] =='Forward to HO'){
					$open = 0;
				}
				else if($rowA['forward'] == 'Carry Forward'){
					$open = $rowA['balance'];
				}
				else if($rowA['forward'] =='Pending Cases'){
					$open = $rowA['total'] + $rowA['diff'];
				}
				else{
					$open = 0;
				}
			}
			else{
				$open = 0;
			}
			return $open;
		}
		
		$row = mysqli_fetch_assoc(mysqli_query($con,"SELECT
		(SELECT ROUND(SUM(netW),1) FROM trans WHERE sta='' AND staDate='' AND status='Approved' AND metal='Gold' AND branchId='$branchId') AS goldRemain,
		(SELECT SUM(request) FROM fund WHERE date='$date' AND status='Approved' AND branch='$branchId') AS requestCash,
		(SELECT SUM(cashA) FROM trans WHERE date='$date' AND branchId='$branchId' AND status='Approved') AS transCash,		
		(SELECT SUM(relCash) FROM releasedata WHERE date='$date' AND BranchId='$branchId' AND status IN ('Approved','Billed','Terminated','Carry Forward')) AS relCash,	
		(SELECT SUM(amount) FROM expense WHERE date='$date' AND branchCode='$branchId' AND status='Approved') AS expense,
		(SELECT SUM(transferAmount) FROM trare WHERE date='$date' AND branchId='$branchId' and status='Approved') AS transferAmount,
		(SELECT SUM(T.transferAmount) FROM trare T,branch B WHERE T.date='$date' AND T.status='Approved' AND T.branchTo=B.branchName AND B.branchId='$branchId') AS receivedAmount
		"));
		
		$open = openBalance()+0;
		$balance = ($open + $row['requestCash'] + $row['receivedAmount']) - ($row['transCash'] + $row['expense'] + $row['transferAmount'] + $row['relCash']) ;			
					
		$data[] = array('gross'=>($businessQuery['grossW']+0),'net'=>($businessQuery['netA']+0),'gold'=>$row['goldRemain'],'balance'=>$balance);
		echo json_encode($data);
	}
	else{
		$data[] = array('gross'=>0,'net'=>0,'gold'=>0,'balance'=>0);
		echo json_encode($data);
	}
?>