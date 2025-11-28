<?php	
	session_start();
	include("dbConnection.php");
	$date = date('Y-m-d');
	$branchId = $_POST['branchId'];
	
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
	(SELECT SUM(request) FROM fund WHERE date='$date' AND status='Approved' AND branch='$branchId') AS requestCash,
	(SELECT SUM(cashA) FROM trans WHERE date='$date' AND branchId='$branchId' AND status='Approved') AS transCash,
	(SELECT SUM(impsA) FROM trans WHERE date='$date' AND branchId='$branchId' AND status='Approved') AS transIMPS,
	(SELECT SUM(relCash) FROM releasedata WHERE date='$date' AND BranchId='$branchId' AND status IN ('Billed')) AS relCash,
	(SELECT SUM(relIMPS) FROM releasedata WHERE type='CASH/IMPS' AND date='$date' AND BranchId='$branchId' AND status IN ('Approved','Billed')) AS relIMPS,
	(SELECT SUM(amount) FROM expense WHERE date='$date' AND branchCode='$branchId' AND status='Approved') AS expense,
	(SELECT SUM(transferAmount) FROM trare WHERE date='$date' AND branchId='$branchId' and status='Approved') AS transferAmount,
	(SELECT SUM(T.transferAmount) FROM trare T,branch B WHERE T.date='$date' AND T.status='Approved' AND T.branchTo=B.branchName AND B.branchId='$branchId') AS receivedAmount
	"));
	
	$open = openBalance()+0;
	$totalFunds = $row['requestCash'] + $row['transIMPS'] + $row['relIMPS'];
	
	$balance = ($open + $row['requestCash'] + $row['receivedAmount']) - ($row['transCash'] + $row['expense'] + $row['transferAmount'] + $row['relCash']);	
	$totalResult = ['open'=>$open,'totalFund'=>$totalFunds,'fundRec'=>$row['receivedAmount'],'fundTransfer'=>$row['transferAmount'],'expense'=>$row['expense'],'balance'=>$balance];
	
	$_SESSION['bal'] = $balance;
	echo json_encode($totalResult);
?>
