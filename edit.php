<?php
    error_reporting(E_ERROR | E_PARSE);
    session_start();
    include("dbConnection.php");
    date_default_timezone_set("Asia/Kolkata");
    $date=date('Y-m-d');
    $time=date("h:i:s");
	
	// EDIT CLOSING ( @ editClosing.php )
	if(isset($_POST['editClosing'])){
	    if(isset($_POST['cid']) && $_POST['cid']!=''){
			$closingId = $_POST['cid'];
			$forward = $_POST['forward'];
			$branchId = $_POST['branchId'];
			$sql = "UPDATE closing SET forward='$forward' WHERE closingID='$closingId'";
			if(mysqli_query($con,$sql)){
				echo header("location:editClosing.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editClosing.php?branchId=".$branchId);
			} 
		}
	}
	
	// DELETE CLOSING ( @ editClosing.php )
	if(isset($_POST['deleteClosing'])){
		if(isset($_POST['cid']) && $_POST['cid']!=''){
			$closingId = $_POST['cid'];
			$branchId = $_POST['branchId'];
			$sql = "DELETE FROM closing WHERE closingID='$closingId'";
			if(mysqli_query($con,$sql)){
				echo header("location:editClosing.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editClosing.php?branchId=".$branchId);
			}
		}
	}
	
	// EDIT CUSTOMER DATA ( @ editCustomers.php )
	if(isset($_POST['editCustomerData'])){
		if(isset($_POST['mobileNumber']) && $_POST['mobileNumber']!=''){
			$mobile = $_POST["mobileNumber"];
			$name = $_POST["name"];
			$gender = $_POST["gender"];
			$dob = $_POST["dob"];
			$idNumber = $_POST["idNumber"];
			$addNumber = $_POST["addNumber"];
			$sql = "UPDATE customer SET name='$name',gender='$gender',dob='$dob',idNumber='$idNumber',addNumber='$addNumber' WHERE mobile='$mobile'";
			if(mysqli_query($con,$sql)){
				echo header("location:editCustomers.php?mobile=".$mobile);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editCustomers.php?mobile=".$mobile);
			}
		}
	}
	
	// ASSIGN BRANCH TO BM ( @ assignBranch.php )
	if(isset($_POST["subassignBranch"])){
		$empID = $_POST['empl'];
		$BID = $_POST['bran'];
		$inscon="Update users set employeeId='$empID' where username='$BID'";		
		if(mysqli_query($con,$inscon)){
			
			echo "<script type='text/javascript'>alert('Employee ".$empID." assigned to Branch ".$BID."!')</script>";
			echo "<script>setTimeout(\"location.href = 'subAssignBranch.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error Assigning Branch!')</script>";
			echo "<script>setTimeout(\"location.href = 'subAssignBranch.php';\",150);</script>";
		}
	}
	if(isset($_POST["assignBranch"])){
                $empID = $_POST['empl'];
                $BID = $_POST['bran'];
                $inscon="Update users set employeeId='$empID' where username='$BID'";
                if(mysqli_query($con,$inscon)){

                        echo "<script type='text/javascript'>alert('Employee ".$empID." assigned to Branch ".$BID."!')</script>";
                        echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
                }
                else{
                        echo "<script type='text/javascript'>alert('Error Assigning Branch!')</script>";
                        echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
                }
        }
	
    // UPDATE EMPLOYEE DATA ( @ addEmployee.php )
	if (isset($_POST["updateemp"])) {
		$id   = $_POST["empIdRow"];
		$type = $_POST["userType"];

		// New fields (keep behavior simple like existing code)
		$salary  = isset($_POST['salary'])  ? $_POST['salary']  : '0.00';
		$advance = isset($_POST['advance']) ? $_POST['advance'] : '0.00';
		// Default to Active if not provided
		$status  = (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'Inactive' : 'Active';
		$inscon = "UPDATE employee SET 
						empId='$_POST[empId]',
						name='$_POST[name]',
						contact='$_POST[number]',
						designation='$_POST[designation]',
						salary='$salary',
						advance='$advance',
						status='$status'
				WHERE id='$id'";

		if (mysqli_query($con, $inscon)) {
			echo "<script>alert('Employee: ".$_POST['empId']." Details Modified')</script>";
			if ($type == 'HR') {
				echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
			} else if ($type == 'SubZonal') {
				echo "<script>setTimeout(\"location.href = 'subAssignBranch.php';\",150);</script>";
			} else {
				echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
			}
		} else {
			echo "<script type='text/javascript'>alert('Error Modifiying Data!')</script>";
			if ($type == 'HR') {
				echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
			} else if ($type == 'SubZonal') {
				echo "<script>setTimeout(\"location.href = 'subAssignBranch.php';\",150);</script>";
			} else {
				echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
			}
		}
	}

	
	// UPDATE EVERY CUSTOMER DETAILS ( @ editEveryCustomer.php )
	if(isset($_POST['editEveryCustomer'])){
		if(isset($_POST['ecid']) && $_POST['ecid']!=''){
			$id = $_POST['ecid'];
			$branchId = $_POST['branchId'];
			$customer = $_POST['customer'];
			$status = $_POST['ecStatus'];
			$sql = "UPDATE everycustomer SET customer='$customer', status='$status' WHERE Id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:editEveryCustomer.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editEveryCustomer.php?branchId=".$branchId);
			}
		}
	}


	if(isset($_POST['editEveryCustomer1'])){
		if(isset($_POST['ecid']) && $_POST['ecid']!=''){
			$id = $_POST['ecid'];
			$branchId = $_POST['branchId'];
			$customer = $_POST['customer'];
			$status = $_POST['ecStatus'];
			$sql = "UPDATE everycustomer SET customer='$customer', status='$status' WHERE Id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:masterEditCustomer.php");
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:masterEditCustomer.php");
			}
		}
	}
	
	// EDIT EXPENSE DETAILS ( @ editExpenseDetails.php )
	if(isset($_POST['editExpenseDetails'])){
		if(isset($_POST['eid']) && $_POST['eid']!=''){
			$id = $_POST['eid'];
			$amount = $_POST['expenseAmount'];
			$branchId = $_POST['branchId'];
			$sql = "UPDATE expense SET amount='$amount' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:editExpenseDetails.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editExpenseDetails.php?branchId=".$branchId);
			}
		}	
	}
	
	// DELETE EXPENSE ( @ editExpenseDetails.php )
	if(isset($_POST['deleteExpense'])){
		if(isset($_POST['eid']) && $_POST['eid']!=''){
			$id = $_POST['eid'];
			$branchId = $_POST['branchId'];
			$sql = "DELETE FROM expense WHERE id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:editExpenseDetails.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editExpenseDetails.php?branchId=".$branchId);
			}
		}	
	}
	

if ((isset($_POST["updatevm"]))) {
	$id = $_GET["vmId"];
	$inscon = "Update users set username='$_POST[empId]', password='$_POST[password]', language='$_POST[language]' where id = '$id'";
	mysqli_query($con, $inscon);
	
	$sql1 = "Update employee set name='$_POST[name]' where empId = '$_POST[empId]'";
	mysqli_query($con, $sql1);
	
	if ((mysqli_query($con, $sql1))) {

		echo "<script>alert('Employee: " . $_POST['empId'] . " Details Modified')</script>";
		echo "<script>setTimeout(\"location.href = 'zviewvm.php';\",150);</script>";
	} else {
		echo "<script type='text/javascript'>alert('Error Modifiying Data!')</script>";
		echo "<script>setTimeout(\"location.href = 'zviewvm.php';\",150);</script>";
	}
}

if(isset($_POST["editUser"])) 
{	
	$id=$_GET['id'];
		$inscon="Update users set
		type='$_POST[type]',
		username='$_POST[name]',
		password='$_POST[password]',
		branch='$_POST[branch]',
		employeeId='$_POST[id]',
		ip='$_POST[ip]'
		where id='$id'";
	
		if((mysqli_query($con,$inscon)))	
		{
		
	
		echo header("location:viewUsers.php");
		
		}
	
	else
	{
		
		echo header("location:addUsers.php?id=$id");
		
	}
}

/*if(isset($_POST["editGold"])) 
{	
	$id=$_GET['id'];
	$transfer = implode(",",$_POST['transfer']);
	$city = implode(",",$_POST['vehicle']);
	
		$inscon="Update gold set
		cash='$_POST[cash]',
		transferRate='$_POST[transfers]',
		city='$city',
		transfer='$transfer'
		where id='$id'";
	
		if((mysqli_query($con,$inscon)))	
		{
		
	
		echo header("location:viewGoldRate.php");
		
		}
	
	else
	{
		
		echo header("location:viewGoldRate.php");
		
	}
}*/
if(isset($_POST["editOr"])) 
{	
	$id=$_GET['id'];
	$or = implode(",",$_POST['one']);
	if(isset($_POST['nine'])){
	 $nine="Checked";	
	}
	else
	{
		$nine="Unchecked";
	}
		$inscon="Update ornament set
		type='$or',
		weight='$_POST[weight]',
		sWaste='$_POST[sweight]',
		waste='$_POST[waste]',
		reading='$_POST[reading]',
		purity='$_POST[purity]',
		nine='$nine'
		where ornamentId='$id'";
	
		if((mysqli_query($con,$inscon)))	
		{
		
	
		echo header("location:addTransaction.php");
		
		}
	
	else
	{
		
		echo header("location:editTransaction.php");
		
	}
}

if(isset($_POST["assignmtemployee"])) 
{
    $empID = $_POST['empl'];
    $BID = $_POST['bran'];
		$inscon="Update users set agent='$empID' where username='$BID'";
	
		if((mysqli_query($con,$inscon)))	
		{
		
	echo "<script type='text/javascript'>alert('Agent ".$empID." assigned to Branch ".$BID."!')</script>";
			echo "<script>setTimeout(\"location.href = 'monitormaster.php';\",150);</script>";
		}
	else
	{
		echo "<script type='text/javascript'>alert('Error Assigning Agent!')</script>";
		echo "<script>setTimeout(\"location.href = 'monitormaster.php';\",150);</script>";
	}
}
if(isset($_GET["authid"])) 
{
    $id = $_GET["authid"];
    $type=$_GET['ty'];
    $username = $_GET['un'];
    $ip=$_SERVER['REMOTE_ADDR'];
    if($type == 'Master' || $type == 'AccHead')
    {
        $inscon1="Update users set date='$date' where id = '$id'";
        $inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
    }
    else
    {
        $inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
    }
    if((mysqli_query($con,$inscon)))
    {
		if((mysqli_query($con,$inscon1)))
        {
		    echo "<script type='text/javascript'>alert('User Unlocked!')</script>";
		    echo "<script>setTimeout(\"location.href = 'zonal.php';\",150);</script>";
        }
        else
        {
            echo "<script type='text/javascript'>alert('Unauthorized Access Captured!')</script>";
            echo "<script>setTimeout(\"location.href = 'logout.php';\",150);</script>";
        }
	}
	else
	{
	    echo "<script type='text/javascript'>alert('Access Denied!')</script>";
	    echo "<script>setTimeout(\"location.href = 'zonal.php';\",150);</script>";
	}
}
if(isset($_GET["lockdate"])) 
{
    $id = $_GET["lockdate"];
    $prev_date = date('Y-m-d', strtotime($date .' -1 day'));
    $type=$_GET['ty'];
    $username = $_GET['un'];
    $ip=$_SERVER['REMOTE_ADDR'];
    if($type == 'Master' || $type == 'AccHead')
    {
        $inscon1="Update users set date='$prev_date' where id = '$id'";
        $inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
    }
    else
    {
        $inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
    }
    if((mysqli_query($con,$inscon)))
    {
        if((mysqli_query($con,$inscon1)))
        {
		    echo "<script type='text/javascript'>alert('User Locked!')</script>";
		    echo "<script>setTimeout(\"location.href = 'zonal.php';\",150);</script>";
        }
        else
        {
            echo "<script type='text/javascript'>alert('Unauthorized Access Captured!')</script>";
            echo "<script>setTimeout(\"location.href = 'logout.php';\",150);</script>";
        }
	}
	else
	{
	    echo "<script type='text/javascript'>alert('Cannot Lock User!')</script>";
	    echo "<script>setTimeout(\"location.href = 'zonal.php';\",150);</script>";
	}
}

if(isset($_POST["updatewalkin"])) 
{
    $id = $_GET["walkinid"];
    $inscon="Update walkin set amobile='$_POST[amob]', gold='$_POST[goldtype]', issue='$_POST[issuetype]', gwt='$_POST[gwt]', nwt='$_POST[nwt]', purity='$_POST[pty]', gamt='$_POST[gamt]', ramt='$_POST[rlamnt]', namt='$_POST[netamnt]', eamt='$_POST[eamt]', oamt='$_POST[oamt]', comment='$_POST[comment]' where id = '$id'";
    if((mysqli_query($con,$inscon)))
    {
		echo "<script>alert('Walkin Details Updated')</script>";
		echo "<script>setTimeout(\"location.href = 'enquiry.php';\",150);</script>";
	}
	else
	{
	    echo "<script type='text/javascript'>alert('Can not update Walkin Details!, Try Again!')</script>";
	    echo "<script>setTimeout(\"location.href = 'enquiry.php';\",150);</script>";
	}
}



/* --------------- Authorize Zonal Access @ zonal.php -------------------- */
if(isset($_POST["authorize_access"])) 
{
	$authaccessArray=array();
	$accessArray=array();
	$authArray=array();
	$accessArray=explode(',', $_POST["authCheck"]);
	$authArray=explode(',', $_POST["authType"]);
	$type=$_POST['ty'];
	$username = $_POST['un'];
	$ip=$_SERVER['REMOTE_ADDR'];	
	
	foreach($accessArray as $key => $val){		
		$id = $val;		
		if($authArray[$key]=="access-granted"){
			$access_date = date('Y-m-d', strtotime($date .' -1 day'));
		}else{
			$access_date = $date;
		}

		if($type == 'Master' || $type == 'AccHead')
		{
			$inscon1="Update users set date='$access_date' where id = '$id'";
			$inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
		}else{
			$inscon="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
		}
		if((mysqli_query($con,$inscon))){
			mysqli_query($con,$inscon1);
		}
	}
	$authaccessArray="";	
	$query = mysqli_query($con,"SELECT * FROM (SELECT * FROM users WHERE type='SubZonal') A JOIN (SELECT empId, name FROM employee GROUP BY empId, name) B ON A.employeeId=B.empId ORDER BY id DESC");
	$i = 1;
	while($row = mysqli_fetch_assoc($query)){
		$authaccessArray.="<tr>";
		$authaccessArray.="<td>" . $i . "</td>";
		$authaccessArray.="<td>" . $row['branch'] . " ". $row['type'] ."</td>";
		$authaccessArray.="<td style='text-transform:uppercase;'>" . $row['name'] . "</td>";
		$authaccessArray.="<td style='text-align:center'><input type='checkbox' class='authAccess' name='access[]' id='box_".$row['id']."' value='".$row['id']."'/></td>";
		    if($row['date'] != '' && $row['date'] != $date){
		        $authaccessArray.="<td style='text-align:center;color:#e74c3c;'><i class='fa fa-lock' aria-hidden='true'></i> Locked <input type='hidden' name='authType' id='authType_".$row['id']."' value='access-denied'/></td>";
	        }else{
	        	$authaccessArray.="<td style='text-align:center;color:#008000;'><i class='fa fa-check' aria-hidden='true'></i> Unlocked <input type='hidden' name='authType' id='authType_".$row['id']."' value='access-granted'/></td>";
		    }

		$authaccessArray.="<td style='text-align:center'><button class='btn btn-danger' onclick='deleteZonal(".$row['id'].")'><i class='fa fa-trash' aria-hidden='true' title='Delete this user'></i> </button></td>";
		$authaccessArray.="</tr>";
		$i++;
	}
		echo $authaccessArray;
}

/* --------------- Delete the zonal user @ zonal.php -------------------- */
if($_POST["action"]=="deleteZonal"){
	$id=$_POST["zonal"];
	$type=$_POST['ty'];
	$username = $_POST['un'];
	$ip=$_SERVER['REMOTE_ADDR'];
	
	$delQuery="UPDATE `users` SET `type` = 'Zonal-Deleted' WHERE `id` = ".$id;	
	$logQuery="insert into logs(type,username,ip,date,time) values ('$type','$username','$ip','$date','$time')";
 	if((mysqli_query($con,$delQuery)))
	{
		mysqli_query($con,$logQuery);
		$response = "SUCCESS";
	}else{
		$response = "ERROR";
	}
	echo $response;
}

/* --------------- Update license renewal of the branch @ license-renewal.php -------------------- */
if ($_POST["action"] === "license-renewal") {
    $id             = (int)$_POST["id"];
    $type           = $_POST['ty'];
    $username       = $_POST['un'];
    $ip             = $_SERVER['REMOTE_ADDR'];
    $today          = date('Y-m-d');
    $current_ts     = strtotime($today);
    $renewal_date   = $_POST["renewal_date"];
    $renewal_ts     = strtotime($renewal_date);

    $renewal_status = $current_ts > $renewal_ts ? 0 : 1;

    $updateQuery = "
      UPDATE `branch`
         SET `renewal_date`   = '$renewal_date',
             `renewal_status` = $renewal_status
       WHERE `id` = $id
    ";
    $logQuery = "
      INSERT INTO logs(type,username,ip,date,time)
           VALUES('$type','$username','$ip','$today','$time')
    ";
    if (mysqli_query($con, $updateQuery)) {
        mysqli_query($con, $logQuery);
        echo "SUCCESS";
    } else {
        echo "ERROR";
    }
    exit;
}

/* --------------- Update stone renewal of the branch @ edit.php -------------------- */
if ($_POST["action"] === "stone-renewal") {
    $id             = (int)$_POST["id"];
    $type           = $_POST['ty'];
    $username       = $_POST['un'];
    $ip             = $_SERVER['REMOTE_ADDR'];
    $today          = date('Y-m-d');
    $current_ts     = strtotime($today);
    $stone_date     = $_POST["stoneR_date"];
    $stone_ts       = strtotime($stone_date);

    $stoneR_status  = $current_ts > $stone_ts ? 0 : 1;

    $updateStone = "
      UPDATE `branch`
         SET `stoneR_date`   = '$stone_date',
             `stoneR_status` = $stoneR_status
       WHERE `id` = $id
    ";
    $logStone = "
      INSERT INTO logs(type,username,ip,date,time)
           VALUES('$type','$username','$ip','$today','$time')
    ";
    if (mysqli_query($con, $updateStone)) {
        mysqli_query($con, $logStone);
        echo "SUCCESS";
    } else {
        echo "ERROR";
    }
    exit;
}

	/* --------------- EDIT TRARE ( @ editTrare.php ) -------------------- */
	if(isset($_POST['editTrare'])){
	    if(isset($_POST['id']) && $_POST['id']!=''){
			$trareId = $_POST['id'];
			$branchId = $_POST['branchId'];
			$branchTo = $_POST['branchTo'];
			$transferAmount = $_POST['transferAmount'];
			$status = $_POST['status'];
			$sql = "UPDATE trare SET branchTo='$branchTo',transferAmount='$transferAmount',status='$status' WHERE id='$trareId'";
			if(mysqli_query($con,$sql)){
				echo header("location:editTrare.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editTrare.php?branchId=".$branchId);
			} 
		}
	}
	
	/* --------------- DELETE TRARE ( @ editTrare.php ) -------------------- */
	if(isset($_POST['deleteTrare'])){
		if(isset($_POST['id']) && $_POST['id']!=''){
			$trareId = $_POST['id'];
			$branchId = $_POST['branchId'];
			$sql = "DELETE FROM trare WHERE id='$trareId'";
			if(mysqli_query($con,$sql)){
				echo header("location:editTrare.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editTrare.php?branchId=".$branchId);
			}
		}
	}

	/* --------------- EDIT USERS ( @ editUsers.php ) -------------------- */
	if(isset($_POST['editUsers'])){
	    if(isset($_POST['id']) && $_POST['id']!=''){
			$user_id = $_POST['id'];
			$userType = $_POST['type'];
			$branch = $_POST['branch'];
			$employeeId = $_POST['employeeId'];
			$agent = $_POST['agent'];
			$language = $_POST['language'];
			$sql = "UPDATE users SET branch='$branch',employeeId='$employeeId',agent='$agent',language='$language' WHERE id='$user_id'";
			if(mysqli_query($con,$sql)){
				echo header("location:editUsers.php?userType=".$userType);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:editUsers.php?userType=".$userType);
			} 
		}
	}
	
	/* --------------- BRANCH SIDE ADD CUSTOMER ACCESS ( @ customerAddAccess.php ) -------------------- */
	if(isset($_POST['accessDaySubmit'])){
		$day = $_POST['access'];
		$sql = "UPDATE misc SET day='$day', date='$date', time='$time' WHERE purpose='Add Customer'";
		if(mysqli_query($con,$sql)){
			echo header("location:customerAddAccess.php");
		}
		else{
			echo "<script>alert('Error Occurred')</script>";
			echo header("location:customerAddAccess.php");
		} 
	}
	
	/* --------------- ZONAL BILL NAME CHANGE ( @ branchBillList.php ) -------------------- */
	if(isset($_POST['zonalBillNameEdit'])){
		if(isset($_POST['transId']) && $_POST['transId']!=''){
			$id = $_POST['transId'];
			$branchId = $_POST['branchId'];
			$customerName = $_POST['customerName'];
			$sql = "UPDATE trans SET name='$customerName' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:branchBillList.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:branchBillList.php?branchId=".$branchId);
			}
		}
	}

	/* --------------- SubZONAL BILL NAME CHANGE ( @ branchBillList.php ) -------------------- */
	if(isset($_POST['subZonalBillNameEdit'])){
		if(isset($_POST['transId']) && $_POST['transId']!=''){
			$id = $_POST['transId'];
			$branchId = $_POST['branchId'];
			$customerName = $_POST['customerName'];
			$sql = "UPDATE trans SET name='$customerName' WHERE id='$id'";
			if(mysqli_query($con,$sql)){
				echo header("location:subBranchBillList.php?branchId=".$branchId);
			}
			else{
				echo "<script>alert('Error Occurred')</script>";
				echo header("location:subBranchBillList.php?branchId=".$branchId);
			}
		}
	}
	
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
	
// 	/* --------------------------  SHOP LICENSE RENEWAL ----------------------------------------- */
// 	if(isset($_POST['shopLicenseRenewal'])){
// 		$id = $_POST['id'];
// 		$renewaldate = $_POST['date'];
		
// 		if($id != ''){
// 			$sql = "UPDATE renewal SET shop_license='$renewaldate' WHERE id='$id'";
// 			if(mysqli_query($con, $sql)){
// 				$diff = round((strtotime($renewaldate) - strtotime($date))/86400);
// 				echo json_encode(['status'=>'0', 'diff'=>$diff]);
// 			}
// 			else{
// 				echo json_encode(['status'=>'1', 'diff'=>0]);
// 			}
// 		}
// 		else{
// 			echo json_encode(['status'=>'2', 'diff'=>0]);
// 		}
// 	}
?>


