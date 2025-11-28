<?php
	session_start();
	include("dbConnection.php");
	
	if(isset($_POST["exportCurrentAttend"]))  // @viewAttendance.php (current month)
	{
		$output="";
		$sno=1;
		$output .='<table class="table" border="1">
		<th>SLNO</th>
		<th>EmployeeId</th>
		<th>Employee Name</th>
		<th>Branch</th>
		<th>Present Days</th>
		<th>LoginBefore9:30</th>
		<th>Login9:30-9:40</th>
		<th>Login9:40-10:00</th>
		<th>Login10:00-10:30</th>
		<th>After10:30</th>';
		$query = mysqli_query($con, "select empId,name,branch, count(DISTINCT date ) as Attendance , 
		SUM(CASE WHEN time <= '09:30:59' THEN 1 ELSE 0 END) AS Login930,
		SUM(CASE WHEN time BETWEEN '09:31:00' AND '09:40:59' THEN 1 ELSE 0 END) AS Login940,
		SUM(CASE WHEN time BETWEEN '09:41:00' AND '10:00:59' THEN 1 ELSE 0 END) AS Login10,
		SUM(CASE WHEN time BETWEEN '10:01:00' AND '10:30:59' THEN 1 ELSE 0 END) AS Login1030,
		SUM(CASE WHEN time >= '10:31:00' THEN 1 ELSE 0 END) AS Login1031
		from (SELECT empId,name, time,branch, date FROM attendance where month(date) = month(CURRENT_DATE) GROUP BY empId,date) q GROUP BY empId;");
		while($row=mysqli_fetch_array($query)){			
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['empId'] .'</td>
			<td>'. $row['name'].'</td>
			<td>'. $row['branch'].'</td>
			<td>'. $row['Attendance'] .'</td>
			<td>'. $row['Login930'] . '</td>
			<td>'. $row['Login940'] . '</td>
			<td>'. $row['Login10'] . '</td>
			<td>'. $row['Login1030'] . '</td>
			<td>'. $row['Login1031'] . '</td></tr>';
			$sno+=1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=AttendMonth.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportSearchAttend"]))  // @viewAttendance.php (searched date)
	{   
		$output="";
		$sno=1;
		
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		$output .='<table class="table" border="1">
		<th>SLNO</th>
		<th>EmployeeId</th>
		<th>Employee Name</th>
		<th>Branch</th>
		<th>Present Days</th>
		<th>LoginBefore9:30</th>
		<th>Login9:30-9:40</th>
		<th>Login9:40-10:00</th>
		<th>Login10:00-10:30</th>
		<th>After10:30</th>';
		$query = mysqli_query($con, "select empId,name,branch, count(DISTINCT date ) as Attendance , 
		SUM(CASE WHEN time <= '09:30:59' THEN 1 ELSE 0 END) AS Login930,
		SUM(CASE WHEN time BETWEEN '09:31:00' AND '09:40:59' THEN 1 ELSE 0 END) AS Login940,
		SUM(CASE WHEN time BETWEEN '09:41:00' AND '10:00:59' THEN 1 ELSE 0 END) AS Login10,
		SUM(CASE WHEN time BETWEEN '10:01:00' AND '10:30:59' THEN 1 ELSE 0 END) AS Login1030,
		SUM(CASE WHEN time >= '10:31:00' THEN 1 ELSE 0 END) AS Login1031
		from (SELECT empId,name, time,branch, date FROM attendance where date between '$from' and '$to' GROUP BY empId,date) q GROUP BY empId;");
		while($row=mysqli_fetch_array($query)){
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['empId'] .'</td>
			<td>'. $row['name'].'</td>
			<td>'. $row['branch'].'</td>
			<td>'. $row['Attendance'] .'</td>
			<td>'. $row['Login930'] . '</td>
			<td>'. $row['Login940'] . '</td>
			<td>'. $row['Login10'] . '</td>
			<td>'. $row['Login1030'] . '</td>
			<td>'. $row['Login1031'] . '</td></tr>';
			$sno+=1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=AttendMonth.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportDailyAttend"])) // @ dailyAttend.php
	{
		$output = "";
		$sno = 1;
		$state = "";
		
		$branchName = $_POST['branch'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		switch($branchName){
			case "All Branches": $state=''; break;
			case "Bangalore"   : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')"; break;
			case "Karnataka"   : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')"; break;
			case "Chennai"     : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE city='Chennai')"; break;
			case "Tamilnadu"   : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')"; break;
			case "Hyderabad"   : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')"; break;
			case "AP-TS"       : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')"; break;
			case "Pondicherry" : $state=" AND b.branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')"; break;
			default            : $state=" AND b.branchId='$branchName'"; break;
		}
		$query = mysqli_query($con, "SELECT a.empId, a.name, a.branch, a.date, a.time, b.city, b.state 
		FROM attendance a,branch b
		WHERE a.date BETWEEN '$from' AND '$to' AND a.branchId=b.branchId".$state."
		ORDER BY a.empId, a.date");
		
		$output .='<table class="table" border="1">
		<th>#</th>
		<th>Employee_Id</th>
		<th>Employee_Name</th>
		<th>Date</th>
		<th>time</th>
		<th>Branch</th>
		<th>City</th>
		<th>State</th>';
		while($row = mysqli_fetch_array($query)){
			$output .='<tr>
			<td>'. $sno.'</td>
			<td>'. $row['empId'].'</td>
			<td>'. $row['name'] .'</td>
			<td>'. $row['date'] . '</td>
			<td>'. $row['time'] . '</td>
			<td>'. $row['branch'] .'</td>
			<td>'. $row['city'] .'</td>
			<td>'. $row['state'] .'</td>';
			$sno = $sno+1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excelAttend.xls");
		echo $output;
	}
    else if(isset($_POST["exportPledge"])) // @ viewbill.php (&&) @ transactionReports.php
	{
		$output = "";
		$sno = 1;
		
		
		$from = $_POST['from'];
		$to = $_POST['to'];
		$grossWTotal = 0;
		$netWTotal = 0;
		$amountTotal = 0;
		$interestAmountTotal = 0;
		$totalAmtToCollectTotal = 0;
		$balanceAmtTotal = 0;
		
			$output .='<table class="table" border="1">
		<th>Si.no</th>
		<th>Date</th>
		<th>BillNo</th>
		<th>BranchName</th>
		<th>CustomerName</th>
		<th>MobileNo</th>
		<th>GrossWt</th>
		<th>NetWt</th>
		<th>Amount</th>
		<th>Interest% </th>
		<th>InterestAmount</th>
		<th>MonthCount</th>
		<th>ActualInterest</th>
		<th>Total_amount_to be_collect</th>
		<th>Balance_Amt</th>
		<th>EmployeeID</th>
		<th>EmployeeName</th>
		<th>Status</th>
		
		';
		
			$query = mysqli_query($con,"SELECT p.billId,p.invoiceId,p.branchId,p.name,p.contact,p.grossW,p.stoneW,p.amount,p.rate,p.rateAmount,p.empId,p.empName,p.date,p.status FROM pledge_bill p WHERE p.date BETWEEN '$from' AND '$to' AND status!='Rejected' ");
		
    while ($row = mysqli_fetch_array($query)) {
    
        $branchId = $row['branchId'];
        $branchNameQuery = mysqli_query($con, "SELECT branchName FROM branch WHERE branchId = '$branchId'");
    
        if ($branchNameQuery) {
    
            $branchNameRow = mysqli_fetch_assoc($branchNameQuery);
    
            if ($branchNameRow) {
                $branchName = $branchNameRow["branchName"];
            } else {
                $branchName = "Unknown Branch";
            }
        } else {
            $branchName = "Unknown Branch";
        }
    
        $date = $row['date'];
        $currentDate = date('Y-m-d'); // Get the current date in 'Y-m-d' format
        $daysDifference = floor((strtotime($currentDate) - strtotime($date)) / (60 * 60 * 24));
    
		// Calculating actual month
		if ($daysDifference < 30) {
			$factor = "1";
		} else {
			$factor = floor($daysDifference / 30) + 1;
		}

		// Calculate actual interest
		if ($daysDifference < 30) {
			$actualInterest = $row['rateAmount'];
		} elseif ($daysDifference >= 31 && $daysDifference <= 60) {
			$actualInterest = (2 * $row['rateAmount']);
		} elseif ($daysDifference >= 61 && $daysDifference <= 90) {
			$actualInterest = (3 * $row['rateAmount']);
		} elseif ($daysDifference >= 91 && $daysDifference <= 120) {
			$actualInterest = (3 * $row['rateAmount']) + ($row['amount'] * 0.03);
		} elseif ($daysDifference >= 121 && $daysDifference <= 150) {
			$actualInterest = $row['rateAmount'] + ((3 * $row['rateAmount']) + ($row['amount'] * 0.03));
		} elseif ($daysDifference >= 151 && $daysDifference <= 180) {
			$actualInterest = (2 * $row['rateAmount']) + ((3 * $row['rateAmount']) + ($row['amount'] * 0.03));
		} else {
			$actualInterest = 0;
		}

		// paidAmount
		$billId = $row['billId'];
		$paidInterestQuery = mysqli_query($con, "SELECT SUM(paidAmount) AS totalPaidAmount,date FROM pledge_fund WHERE billId='$billId'");
		if ($paidInterestQuery) {
			$interestRow = mysqli_fetch_assoc($paidInterestQuery);
			$paidAmount = $interestRow['totalPaidAmount'];
			$releasedate=$interestRow['date'];
		} else {
			$paidAmount = 0;
		}

		// Calculate remaining balance based on actualInterest
		// $paidAmount = 0; 
		$remainBalance = ($row['amount'] + $actualInterest) - $paidAmount;
		
	    // status
	    
		if ($row['status'] == 'Billed') {
			$status='Pledged';
			
		} else 
			if ($row['status'] == 'Released') {
				$status='Released';
		}else 
			if ($row['status'] == 'Salegold') {
				$status='Sold';
		}else 
			if ($row['status'] == '') {
				$status='';
		}

		$netW = $row['grossW'] - $row['stoneW'];
		
		if($status!='Rejected'){
			$grossWTotal += $row['grossW'];
			$netWTotal += $netW;
			$amountTotal += $row['amount'];
			$interestAmountTotal += $row['rateAmount'];
			$totalAmtToCollectTotal += $paidAmount;
			$balanceAmtTotal += $remainBalance;
			}else{
				echo "";
			}
			
        $output .= '<tr><td>' . $sno . '</td>
            <td>' . $row['date'] . '</td>			
            <td>' . $row['billId'] . '</td>
            <td>' . $branchName . '</td> 
            <td>' . $row['name'] . '</td>
            <td>' . $row['contact'] . '</td>
            <td>' . $row['grossW'] . '</td> 
            <td>' . $netW . '</td>
            <td>' . $row['amount'] . '</td>
            <td>' . $row['rate'] . '</td>
            <td>' . $row['rateAmount'] . '</td>
			<td>' . $factor . '</td> 
			<td>' . $actualInterest . '</td> 
			<td>' . $paidAmount . '</td> 
			<td>' . $remainBalance . '</td> 
			<td>' . $row['empId'] . '</td>
            <td>' . $row['empName'] . '</td>
			<td>' . $status . '</td>

			';
    
        
    
        $output .= '</tr>';
        $sno = $sno + 1;
    }
        $output .= '<tfoot>
    					<tr>
    						<td colspan="6">Total:</td>
    						<td>' . $grossWTotal . '</td>
    						<td>' . $netWTotal . '</td>
    						<td>' . $amountTotal . '</td>
    						<td></td>
    						<td>' . $interestAmountTotal . '</td>
    						<td></td>
    						<td></td>
    						<td>' . $totalAmtToCollectTotal . '</td>
    						<td>' . $balanceAmtTotal . '</td>
    						
    					</tr>
    				</tfoot>';

		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excel1.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportExpense"]))   // @ dailyExpense.php
	{
		$output="";
		$sno=1;
		
		$branchName = $_POST['branch'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		$output .='<table class="table" border="1">
		<th>SLNO </th>
		<th>BranchId </th>
		<th>Particular</th>
		<th>Type</th>
		<th>Amount</th>
		<th>Status</th>
		<th>Date</th>';
		if($branchName == "All Branches"){
			$query = mysqli_query($con,"SELECT e.*,b.branchName FROM expense e,branch b WHERE e.branchCode=b.branchId AND e.status='Approved' AND e.date BETWEEN '$from' AND '$to'");
		}
		else{
			$query = mysqli_query($con,"SELECT e.*,b.branchName FROM expense e,branch b WHERE e.branchCode=b.branchId AND e.status='Approved' AND e.branchCode='$branchName' AND e.date BETWEEN '$from' AND '$to'");
		}
		while($row=mysqli_fetch_array($query)){
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['branchName'].'</td>
			<td>'. $row['particular'].'</td>
			<td>'. $row['type'] .'</td>
			<td>'. $row['amount'] . '</td>
			<td>'. $row['status'] . '</td>
			<td>'. $row['date'] . '</td></tr>';
			$sno=$sno+1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excel.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportBills"])) // @ viewbill.php (&&) @ transactionReports.php
	{
		$output = "";
		$sno = 1;
		
		$branchName = $_POST['branch'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		$output .='<table class="table" border="1">
		<th>SLNO</th>
		<th>BranchId </th>
		<th>CustomerId</th>
		<th>BillId</th>
		<th>Name</th>
		<th>Phone</th>
		<th>GrossW</th>
		<th>NetW</th>
		<th>GrossA</th>
		<th>NetA</th>
		<th>Release</th>
		<th>Paid</th>
		<th>Date</th>
		<th>Status</th>';
		if($branchName == "All Branches"){
			$query = mysqli_query($con,"SELECT t.customerId,t.billId,t.name,t.phone,t.grossW,t.netW,t.grossA,t.netA,t.releases,t.amountPaid,t.date,t.sta,b.branchName FROM trans t,branch b WHERE t.date BETWEEN '$from' AND '$to' AND t.status='Approved' AND t.branchId=b.branchId");
		}
		else{
			$query = mysqli_query($con,"SELECT t.customerId,t.billId,t.name,t.phone,t.grossW,t.netW,t.grossA,t.netA,t.releases,t.amountPaid,t.date,t.sta,b.branchName FROM trans t,branch b WHERE t.branchId='$branchName' AND t.date BETWEEN '$from' AND '$to' AND t.status='Approved' AND t.branchId=b.branchId");
		}
		while($row=mysqli_fetch_array($query)){	
			if($row['sta'] == "Checked"){
				$st="Moved to HO";
			}
			else if($row['sta'] != "Checked"){
				$st="Pending";
			}
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['branchName'].'</td>
			<td>'. $row['customerId'].'</td>
			<td>'. $row['billId'] .'</td>
			<td>'. $row['name'] . '</td>
			<td>'. $row['phone'] . '</td>
			<td>'. $row['grossW'] .'</td>
			<td>'. $row['netW'] . '</td>
			<td>'. $row['grossA'] . '</td>
			<td>'. $row['netA'] .'</td>
			<td>'. $row['releases'] . '</td>
			<td>'. $row['amountPaid'] . '</td>
			<td>'. $row['date'] . '</td>
			<td>'. $st.'</td></tr>';
			$sno=$sno+1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excel1.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportFund"]))  // @ fundReports.php
	{
		$output="";
		$sno=1;
		
		$branchName = $_POST['branch'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		$output .='<table class="table" border="1">
		<th>SLNO </th>
		<th>Available </th>
		<th>Request</th>
		<th>Type</th>
		<th>BranchId</th>
		<th>Date</th>
		<th>Status</th>';
		if($branchName == "All Branches"){
			$query = mysqli_query($con,"SELECT f.*,b.branchName FROM fund f,branch b where f.date BETWEEN '$from' AND '$to' AND f.branch=b.branchId AND f.status='Approved'");
		}
		else{
			$query = mysqli_query($con,"SELECT f.*,b.branchName FROM fund f,branch b where f.date BETWEEN '$from' AND '$to' AND f.branch=b.branchId AND f.status='Approved' AND f.branch='$branchName'");
		}
		while($row=mysqli_fetch_array($query)){
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['available'].'</td>
			<td>'. $row['request'].'</td>
			<td>'. $row['type'] .'</td>
			<td>'. $row['branchName'] . '</td>
			<td>'. $row['date'] . '</td>
			<td>'. $row['status'] .'</td></tr>';
			$sno=$sno+1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excel2.xls");
		echo $output;
	}
	
	else if(isset($_POST["exportClosingData"]))  // @dailyReports.php
	{
		$output="";
		$sno=1;
		
		$branchName = $_POST['branch'];
		$from = $_POST['from'];
		$to = $_POST['to'];
		
		$output .='<table class="table" border="1">
		<th>SLNO </th>
		<th>BranchId </th>
		<th>Branch</th>
		<th>Total Funds</th>
		<th>Bill</th>
		<th>Trans Amount</th>
		<th>Balance</th>
		<th>GrossW</th>
		<th>NetW</th>
		<th>GrossA</th>
		<th>NetA</th>
		<th>Expenses</th>
		<th>Total</th>
		<th>Difference</th>
		<th>Date</th>';
		if($branchName == "All Branches"){
			$query = mysqli_query($con,"SELECT c.*,b.branchName FROM closing c,branch b WHERE c.date BETWEEN '$from' AND '$to' AND c.branchId=b.branchId ORDER BY c.branchId ASC");
		}
		else{
			$query = mysqli_query($con,"SELECT c.*,b.branchName FROM closing c,branch b WHERE c.date BETWEEN '$from' AND '$to' AND c.branchId=b.branchId AND c.branchId='$branchName' ORDER BY c.branchId ASC");
		}
		while($row=mysqli_fetch_array($query)){
			$output .='<tr><td>'.$sno.'</td>
			<td>'. $row['branchId'].'</td>
			<td>'. $row['branchName'].'</td>
			<td>'. $row['totalAmount'].'</td>
			<td>'. $row['transactions'] .'</td>
			<td>'. $row['transactionAmount'] . '</td>
			<td>'. $row['balance'] . '</td>
			<td>'. $row['grossWG'] .'</td>
			<td>'. $row['netWG'] . '</td>
			<td>'. $row['grossAG'] . '</td>
			<td>'. $row['netAG'] .'</td>
			<td>'. $row['expenses'] . '</td>
			<td>'. $row['total'] . '</td>
			<td>'. $row['diff'] . '</td>
			<td>'. $row['date'] . '</td></tr>';
			$sno=$sno+1;
		}
		$output .= '</table>';
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=excel3.xls");
		echo $output;
	}
	
	else if(isset($_POST['exportWalkinData'])){  // issues.php
		$output="";
		$sno=1;		
		$date = $_POST['walkin_date'];
		
		$query = mysqli_query($con,"SELECT  w.*, b.branchName, b.city, b.state, t.phone
		FROM walkin w 
		LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
		LEFT JOIN branch b ON w.branchId = b.branchId
		WHERE w.date='$date' AND w.issue != 'Rejected'
		ORDER BY w.id DESC");
		
		$output .='<table class="table" border="1">
		<th>#</th>
		<th>BranchName</th>
		<th>City</th>
		<th>State</th>
		<th>Name</th>
		<th>Contact</th>
		<th>Type</th>
		<th>Having</th>
		<th>Metal</th>
		<th>GrossW</th>
		<th>ReleaseA</th>
		<th>Rate</th>
		<th>Branch_Remark</th>
		<th>Zonal_Remark</th>
		<th>Disposition</th>
		<th>CSR_Remark</th>
		<th>Agent</th>
		<th>Date</th>
		<th>Time</th>';
		while($row = mysqli_fetch_assoc($query)){
			$output .= '<tr><td>'.$sno.'</td>
			<td>'. $row['branchName'].'</td>
			<td>'. $row['city'].'</td>
			<td>'. $row['state'].'</td>
			<td>'. $row['name'] .'</td>
			<td>'. $row['mobile'] . '</td>
			<td>'. $row['gold'] . '</td>
			<td>'. $row['havingG'] .'</td>
			<td>'. $row['metal'] . '</td>
			<td>'. $row['gwt'] . '</td>
			<td>'. $row['ramt'] .'</td>
			<td>'. $row['quot_rate'] . '</td>
			<td>'. $row['remarks'] . '</td>
			<td>'. $row['zonal_remarks'] . '</td>';						
			$output .= ($row['mobile'] == $row['phone']) ? '<td>Sold in Attica</td>' : '<td>'. $row['issue'] . '</td>';						
			$output .= '<td>'. $row['comment'] . '</td>
			<td>'. $row['agent_id'] . '</td>
			<td>'. $row['date'] . '</td>
			<td>'. $row['time'] . '</td></tr>';
			$sno = $sno + 1;
		}
		$output .= '</table>';
		
		header("Content-Type:application/xls");
		header("Content-Disposition:attatchment; filename=enquiryData-".$date.".xls");
		echo $output;
	}
	
	else
	{
		echo "<script type='text/javascript'>alert('Access Denied!')</script>";
		echo "<script>setTimeout(\"location.href = 'logout.php';\",150);</script>";
	}
?>