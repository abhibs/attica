<?php
	session_start();
    	include("dbConnection.php");    
	date_default_timezone_set('Asia/Kolkata');	
	$emp_id = $_SESSION['employeeId'];
	$date = date('Y-m-d');

    // EDIT BRANCH ( @ editBranch.php )
    if(isset($_POST["editBranch"])) {
	    
		$branchId = $_POST['branchId'];
		$colName = $_POST['colName'];
		$colValue = $_POST['colValue'];
		$sql = "UPDATE branch SET ".$colName."='".$colValue."' WHERE branchId='$branchId'";
		
		if(mysqli_query($con,$sql)){
			echo '1';
		}
		else{
			echo '0';
		}
    }



	if (isset($_POST["editCase"])) {

	$id = $_POST['id'];
	$colName = $_POST['colName'];
	$colValue = $_POST['colValue'];
	$sql = "UPDATE cases SET " . $colName . "='" . $colValue . "' WHERE id='$id'";

	if (mysqli_query($con, $sql)) {
		echo '1';
	} else {
		echo '0';
	}
}

if (isset($_POST["editGoldRate"])) {
	$id = $_POST['id'];
	$colName = $_POST['colName'];
	$colValue = $_POST['colValue'];
	$emp_id = $_SESSION['employeeId'];
	$sql = "UPDATE gold SET " . $colName . "='" . $colValue . "',
		user = '" . $emp_id . "'        
		WHERE id='$id'";
	if (mysqli_query($con, $sql)) {
		echo '1';
	} else {
		echo '0';
	}
}
	
	// EDIT BILL DATA ( @ editTrans.php 
	if(isset($_POST["editTrans"])){
		if( isset($_POST["transId"]) && $_POST["transId"]!=''){
			
			$transId = $_POST['transId'];
			$colName = $_POST['colName'];
			$colValue = $_POST['colValue'];
			$sql = "UPDATE trans SET ".$colName."='".$colValue."' WHERE id='$transId'";
			
			if(mysqli_query($con,$sql)){
				echo '1';
			}
			else{
				echo '0';
			}
			
		}
	}
	// EDIT PLEDGE DATA  ( @ editpledge.php
	if(isset($_POST["editPledge"])){
		if(isset($_POST["pledgeId"]) && $_POST["pledgeId"]!=''){
			$pledgeId=$_POST['pledgeId'];
			$colName=$_POST['colName'];
			$colValue=$_POST['colValue'];
			$sql="UPDATE pledge_bill SET ".$colName."='".$colValue."' WHERE id='$pledgeId'";

			if(mysqli_query($con,$sql)){
				echo '1';
			}
			else{
				echo '0';
			}
		}
	}
	
	// UPDATE TRANS DATA ( @ searchGoldSendData.php 
	if(isset($_POST["updateGoldSendData"])){
		
		if(isset($_POST['trans_id']) && $_POST['trans_id']!=''){
			$id = $_POST['trans_id'];
			$branchId = $_POST['branchId'];
			$sta = $_POST['sta'];
			$staDate = $_POST['staDate'];
			$currentBranch = trim($_POST['CurrentBranch'] ?? '');
			$sql = "UPDATE trans SET sta='$sta', CurrentBranch='$currentBranch', staDate='$staDate' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:searchGoldSendData.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:searchGoldSendData.php?branchId=".$branchId);
			}
		}
	}
	
	// REJECT WALKING DATA @ searchWalkinData.php 
	if(isset($_POST["editWalkinReject"])){
		$row_id = $_POST['row_id'];
		$sql = "UPDATE walkin SET issue='Rejected', status=1 WHERE id='$row_id'";
		if(mysqli_query($con,$sql)){
		    echo '1';
		}
		else{
			echo '0';
		}
	}
	
	// DELETE WALKING DATA @ searchWalkinData.php 
	if(isset($_POST["editWalkinDelete"])){
		$row_id = $_POST['row_id'];
		$sql = "DELETE FROM walkin WHERE id='$row_id'";
		if(mysqli_query($con,$sql)){
		    echo '1';
		}
		else{
			echo '0';
		}
	}
	
	// ACCEPT / REJECT GOLD BUYER DATA 
	if(isset($_POST["updateBuyerQuotData"])){
	
		$id = $_POST['id'];
		$status = $_POST['status'];
		
		$sql = "UPDATE buyer_quot SET status='$status' WHERE id='$id'";
		if(mysqli_query($con,$sql)){
		   echo json_encode(["message"=>"success"]);
		}
		else{
			echo json_encode(["message"=>"error"]);
		}
	}
	
	// ZONAL-ASSIGN-BRANCH @assignZonalBranch.php
	if(isset($_POST['updateZonalBranch'])){
		$branchId = $_POST['branchId'];
		$zonalEmpId = $_POST['zonalEmpId'];

		$sql = "UPDATE branch SET ezviz_vc='$zonalEmpId' WHERE branchId='$branchId'";
		if (mysqli_query($con, $sql)) {

			// Prepare current date and time
			$currentDate = date('Y-m-d');
			$currentTime = date('h:i:s');
			// Insert into zonalbranch_assign
			$insert_sql = "INSERT INTO zonalbranch_assign (branchId, ezviz_vc, date, time)
		               VALUES ('$branchId', '$zonalEmpId', '$currentDate', '$currentTime')";
				mysqli_query($con, $insert_sql); // Optional: check result

				echo json_encode(["message" => "success"]);
		} else {
			echo json_encode(["message" => "error"]);
		}
	}
	// Update stock

	if (isset($_POST["editStock"])) {
		$id = $_POST['id'];
		$colName = $_POST['colName'];
		$colValue = $_POST['colValue'];
		$sql = "UPDATE stock SET " . $colName . "='" . $colValue . "', date = '" . $date . "' WHERE id='$id'";
		if (mysqli_query($con, $sql)) {
			echo '1';
		} else {
			echo '0';
		}
	}	

	// ZONAL BILL VERIFICATION @zonalBillVerification.php
	if (isset($_POST['zonalBillVerification'])) {
    	$id = $_POST['id'];
    	$branchId = $_POST['branchId']; // new

    	if ($id == "" || $branchId == "") {
        	echo json_encode(["message" => "error"]);
        	exit;
    	}

    	// Get ezviz_vc from branch
    	$res = mysqli_query($con, "SELECT ezviz_vc FROM branch WHERE branchId='$branchId'");
    	$row = mysqli_fetch_assoc($res);
    	$ezviz_vc = $row['ezviz_vc'];

    	// Update transaction status
    	$sql = "UPDATE trans SET status='ZONAL-VERIFIED', ezviz_vc='$ezviz_vc' WHERE id='$id'";
    	if (mysqli_query($con, $sql)) {
        	echo json_encode(["message" => "success"]);
    	} else {
      		  echo json_encode(["message" => "error"]);
   		 }
	}

	// ZONAL BILL REJECT @zonalBillVerification.php
	if (isset($_POST['zonalBillReject'])) {
	$id = $_POST['id'];
	$branchId = $_POST['branchId']; // new

	if ($id == "" || $branchId == "") {
		echo json_encode(["message" => "error"]);
		exit;
	}

	// Get ezviz_vc from branch
	$res = mysqli_query($con, "SELECT ezviz_vc FROM branch WHERE branchId='$branchId'");
	$row = mysqli_fetch_assoc($res);
	$ezviz_vc = $row['ezviz_vc'];

	// Update transaction status
	$sql = "UPDATE trans SET status='Rejected', ezviz_vc='$ezviz_vc' WHERE id='$id'";
	if (mysqli_query($con, $sql)) {
		echo json_encode(["message" => "success"]);
	} else {
		echo json_encode(["message" => "error"]);
	}
}

	// WEBSITE CANDIDATE STATUS UPDATE @job.php
	if(isset($_POST['updateJobStatus'])){
		$id = $_POST['jobId'];
		if($id == ""){
			echo json_encode(["message"=>"error"]);
		}
		else{
			$sql = "UPDATE job SET status='Done' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
		   		echo json_encode(["message"=>"success"]);
			}
			else{
				echo json_encode(["message"=>"error"]);
			}
		}
	}	

	// ------------------------------ UPLOAD THING ----------------------------------
	// WEBSITE CANDIDATE STATUS UPDATE @xviewCustomerDetails.php.php
	if(isset($_POST['updateTransUpload'])){
		$id = $_POST['transid'];
		if($id == ""){
			echo json_encode(["message"=>"error"]);
		}
		else{
			$sql = "UPDATE trans SET status='Begin' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
		   		echo json_encode(["message"=>"success"]);
			}
			else{
				echo json_encode(["message"=>"error"]);
			}
		}
	}
	// WEBSITE CANDIDATE STATUS UPDATE @xviewCustomerDetails.php.php
	if(isset($_POST['updateReleaseUpload'])){
		$id = $_POST['relid'];
		if($id == ""){
			echo json_encode(["message"=>"error"]);
		}
		else{
			$sql = "UPDATE releasedata SET status='Begin' WHERE rid='$id'";
			if(mysqli_query($con,$sql)){
		   		echo json_encode(["message"=>"success"]);
			}
			else{
				echo json_encode(["message"=>"error"]);
			}
		}
	}
	// ------------------------------ UPLOAD THING ----------------------------------
if (isset($_POST['updateBegin'])) {
	$id = $_POST['beginid'];
	$remark = $_POST['issue_remark'];

	if ($id == "") {
		echo json_encode(["message" => "error"]);
	} else {
		$sql = "UPDATE everycustomer 
                SET issue_remark='$remark' 
                WHERE Id='$id'";
		if (mysqli_query($con, $sql)) {
			echo json_encode(["message" => "success"]);
		} else {
			echo json_encode(["message" => "error"]);
		}
	}
}

if (isset($_POST["editTask"])) {
	$id = $_POST['id'];
	$colName = $_POST['colName'];
	$colValue = $_POST['colValue'];
	$sql = "UPDATE task SET " . $colName . "='" . $colValue . "',
		date = '" . $date . "'
		WHERE id='$id'";

	if (mysqli_query($con, $sql)) {
		echo '1';
	} else {
		echo '0';
	}
}

	
?>

