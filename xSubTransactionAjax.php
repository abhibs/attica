<?php 
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');
	$type = $_SESSION['usertype'];
	$employeeId = $_SESSION['employeeId'];
	
	/* ------------------ FOR CASH TRANSACTION > xviewTransaction.php -----------------------*/
	if(isset($_POST["cash_transaction_status"])){
		
		if($_POST["cash_transaction_status"] =="approved"){
			$transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.rate, T.time, T.date, T.type, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin, T.status 
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status IN ('Verified','Approved') AND T.paymentType='Cash' AND T.branchId=B.branchId AND B.ezviz_vc = '$employeeId'
			ORDER BY T.id DESC 
			LIMIT 10");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray .= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['time']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
				"<td>".$row['amountPaid']."</td>".
				"<td>".$row['type']."</td>".
				"<td>".$row['margin']."<br>(".$row['comm']."%)</td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}
		
		if($_POST["cash_transaction_status"] == "rejected"){
		    $transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.time, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status='Rejected' AND T.paymentType='Cash' AND T.branchId=B.branchId AND B.ezviz_vc = '$employeeId'
			ORDER BY T.id DESC");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray .= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
				"<td>".$row['amountPaid']."</td>".
				"<td>".$row['margin']."<br>(".$row['comm']."%)</td>".
				//"<td><a class='btn btn-success' href='xdeleteTrans.php?cashId=".$row['id']."' onClick=\"javascript: return confirm('Are you sure you want to delete?');\"><i style='color:#ff0000' class='fa fa-times'></i></a></td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}	
		
	}
	
	
	/* ------------------ FOR IMPS TRANSACTION > xviewTransactionIMPS.php -----------------------*/
	if(isset($_POST["imps_transaction_status"])){
		
	    if($_POST["imps_transaction_status"]  == "approved"){
			$transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.time, T.type, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin, T.status, T.cashA, T.impsA, T.releaseID 
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status IN ('Verified','Approved') AND T.paymentType='NEFT/RTGS' AND T.branchId=B.branchId  AND B.ezviz_vc = '$employeeId' 
			ORDER BY T.id DESC 
			LIMIT 10");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray.= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['time']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
			    "<td>".$row['amountPaid']."<br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>".
				"<td>".$row['type']."<br>";
				if($row['type'] == 'Release Gold'){
					$relType = mysqli_fetch_assoc(mysqli_query($con,"SELECT type,relCash,relIMPS FROM releasedata WHERE releaseID='$row[releaseID]' AND date='$date'"));
					if($relType['type'] == 'CASH/IMPS'){
						$transArray.= ($relType['relCash'] == 0)?'(IMPS)':'(CASH/IMPS)'.
						"<br>Cash:".$relType['relCash']."<br>IMPS:".$relType['relIMPS']."</td>";
					}
					else{
						$transArray.= "(CASH)</td>";
					}
				}
				else{
					$transArray.= "</td>";
				}
				$transArray.= "<td>".$row['margin']."<br>(".$row['comm']."%)</td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><b class='text-success' >".$row['status']. "</b></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}
		
		if($_POST["imps_transaction_status"]  == "rejected"){
			$transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin 
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status='Rejected' AND T.paymentType='NEFT/RTGS' AND T.branchId=B.branchId  AND B.ezviz_vc = '$employeeId'
			ORDER BY T.id DESC");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray.= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
				"<td>".$row['amountPaid']."</td>".
				"<td>".$row['margin']."<br>(".$row['comm']."%)</td>".
				//"<td><a class='btn btn-success' href='xdeleteTrans.php?impsId=".$row['id']."' onClick=\"javascript: return confirm('Are you sure you want to delete?');\"><i style='color:#ff0000' class='fa fa-times'></i></a></td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}
		
	}
	
	
	/* ------------------ FOR BOTH CASH / IMPS TRANSACTION > xviewTransactionBoth.php -----------------------*/
	if(isset($_POST["cashImps_transaction_status"])){
		
		if($_POST["cashImps_transaction_status"]  == "approved"){
			$transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.time, T.type, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin, T.status, T.releaseID, cashA, impsA 
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status IN ('Verified','Approved') AND T.paymentType='Cash/IMPS' AND T.branchId=B.branchId  AND B.ezviz_vc = '$employeeId'
			ORDER BY T.id DESC 
			LIMIT 10");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray.= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['time']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
				"<td>".$row['amountPaid']."<br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>".
				"<td>".$row['type']."<br>";
				if($row['type'] == 'Release Gold'){
					$relType = mysqli_fetch_assoc(mysqli_query($con,"SELECT type,relCash,relIMPS FROM releasedata WHERE releaseID='$row[releaseID]' AND date='$date'"));
					if($relType['type'] == 'CASH/IMPS'){
						$transArray.= ($relType['relCash'] == 0)?'(IMPS)':'(CASH/IMPS)'.
						"<br>Cash:".$relType['relCash']."<br>IMPS:".$relType['relIMPS']."</td>";
					}
					else{
						$transArray.= "(CASH)</td>";
					}
				}
				else{
					$transArray.= "</td>";
				}
				$transArray.= "<td>" .$row['margin']."<br>(".$row['comm']."%)</td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><b class='text-success' >" .$row['status']. "</b></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}
		
		if($_POST["cashImps_transaction_status"]  == "rejected"){
			$transArray = "";
			$i = 1;
			$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.time, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin 
			FROM trans T,branch B 
			WHERE T.date='$date' AND T.status='Rejected' AND T.paymentType='Cash/IMPS' AND T.branchId=B.branchId  AND B.ezviz_vc = '$employeeId'
			ORDER BY T.id DESC");
			while($row = mysqli_fetch_assoc($sql)){
				$transArray.= "<tr>".
				"<td>".$i."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['grossW']."</td>".
				"<td>".$row['netW']."</td>".
				"<td>".$row['grossA']."</td>".
				"<td>".$row['netA']."</td>".
				"<td>".$row['amountPaid']."</td>".
				"<td>".$row['margin']."<br>(".$row['comm']."%)</td>".
				//"<td><a class='btn btn-success' href='xdeleteTrans.php?impsId=".$row['id']."' onClick=\"javascript: return confirm('Are you sure you want to delete?');\"><i style='color:#ff0000' class='fa fa-times'></i></a></td>".
				"<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>".
				"</tr>";
				$i++;
			}
			echo $transArray;
		}
		
	}
	
	
	/* ------------------ FOR BLOCKED CUSTOMERS > xmanageCustomer.php -----------------------*/
	if(isset($_POST["custId"])){
		
		$contactNumber = $_POST["custId"];		
		$resultArray = array();
		$resultArray = "";

		$billQuery=mysqli_query($con,"SELECT t.name as customer_name,t.phone as contact_number, t.date, t.grossW, t.amountPaid, t.type, t.metal, b.branchName
		FROM trans t LEFT JOIN branch b ON t.branchId = b.branchId
		WHERE t.phone='$contactNumber' and t.status='Approved'");
		$billCount=mysqli_num_rows($billQuery);
		if($billCount>0){
			$i = 1;
			while($row = mysqli_fetch_assoc($billQuery)){				
				$resultArray.="<tr>";
				$resultArray.="<td>".$i."</td>";
				$resultArray.="<td>".$row['branchName']."</td>";
				$resultArray.="<td>".$row['customer_name']."</td>";				
				$resultArray.="<td>".$row['type']."</td>";
				$resultArray.="<td>".$row['metal']."</td>";
				$resultArray.="<td>".$row['grossW']."</td>";
				$resultArray.="<td>".$row['amountPaid']."</td>";
				$resultArray.="<td>".$row['date']."</td>";			
				$resultArray.= "</tr>";
				$i++;
			}
		}		
		echo $resultArray;			
	}	
	
	// to update the remark as recently blocked after the customer is unblocked by the approval team
	if(isset($_POST["action"]) && $_POST["action"]=="customer_status"){
		$id=$_POST["cust_arg2"];
		$contactNumber=$_POST["cust_arg3"];
		$customerStatus = $_POST["cust_arg4"];		
		if($customerStatus=='Blocked'){
			$status = "Begin";
			$currentStatus="Unblocked";	
			$block_counter =$_POST["cust_arg5"]+1;	
		}
		if($customerStatus=='0'){
			$status = 'Blocked';
			$currentStatus="Blocked";
			$block_counter =$_POST["cust_arg5"];		
		}

		$statusQuery=mysqli_query($con,"UPDATE `everycustomer` SET `status` = '$status', `status_remark` = 'Recently Blocked',`block_counter` = '$block_counter'  WHERE `everycustomer`.`Id` = $id");
		if($statusQuery){
			echo $currentStatus;
		}		
	}
	
	// to set the alert counter of the blocked customers in the menumaster.php,menuapproval.php
	if(isset($_POST["action"]) && $_POST["action"]=="alert"){
		$customerQuery = mysqli_query($con,"SELECT * FROM everycustomer WHERE date='$date' and status='Blocked' ORDER BY Id ASC");
		$customerCount=mysqli_num_rows($customerQuery);		
		echo $customerCount;		
	}	
	
	/* checking for the ID proof number and address proof number for new customer entry */
	if(isset($_POST["customerType"]) && $_POST["customerType"]=="New"){
		
		if(isset($_POST["proofNumber"])){
			
 			$proofNumber = $_POST["proofNumber"];		
			$contactNo = $_POST["contactNo"];			
			if($proofNumber!=""){
				$query ="SELECT mobile FROM `customer` where idNumber='$proofNumber' OR addNumber='$proofNumber' AND mobile!='$contactNo'";
			}		
			$sql=mysqli_query($con,$query);	
			$rowCount=mysqli_num_rows($sql);
			$row = mysqli_fetch_assoc($sql);
			if($rowCount>0){
				$contact=$row["mobile"];
				$yesterday = date('Y-m-d', strtotime($date.' -1 day'));
				$start_date = date('Y-m-d', strtotime($yesterday.' -20 day'));
				// Check whether the customer with this contact number has billed in less than 20days
				$billCount = mysqli_num_rows(mysqli_query($con,"SELECT phone FROM trans WHERE phone='$contact' AND date BETWEEN '$start_date' AND '$yesterday' and status='Approved'"));
				if($billCount>0){
					$response="available";// if yes then block the customer from billing
					$query = "UPDATE `everycustomer` SET `status` = 'Blocked', `remark`='The ID proof number ($proofNumber) provided by the customer has been billed recently' WHERE `contact` = '$contactNo' AND `date`='$date';";
					mysqli_query($con,$query);	
				}else{
					$response="not-available";
				}					
					
			}else{
				$response="not-available";
			} 
			echo $response;
			
		}	
		
	}
	
	/* checking for the ID proof number and address proof number for existing customer entry */
	if(isset($_POST["customerType"]) && $_POST["customerType"]=="Existing"){
		
		if(isset($_POST["proofNumber"])){
			
			$proofNumber = $_POST["proofNumber"];		
			$contactNo = $_POST["contactNo"];			
			if($proofNumber!=""){
				$query ="SELECT idNumber,addNumber FROM `customer` WHERE (idNumber='$proofNumber' OR addNumber='$proofNumber') AND mobile='$contactNo'";
			}
			$sql=mysqli_query($con,$query);	
			$rowCount=mysqli_num_rows($sql);
			$row = mysqli_fetch_assoc($sql);
			if($rowCount>0){				
				$response="success";		
			}else{
				$query2 ="SELECT mobile FROM `customer` WHERE (idNumber='$proofNumber' OR addNumber='$proofNumber') AND mobile!='$contactNo'";
				$sql2=mysqli_query($con,$query2);	
				$rowCount2=mysqli_num_rows($sql2);
				$row2 = mysqli_fetch_assoc($sql2);
				if($rowCount2>0){
					$contact=$row2["mobile"];
					$yesterday = date('Y-m-d', strtotime($date.' -1 day'));
					$start_date = date('Y-m-d', strtotime($yesterday.' -20 day'));
					// Check whether the customer with this contact number has billed in less than 20days
					$billCount = mysqli_num_rows(mysqli_query($con,"SELECT phone FROM trans WHERE phone='$contact' AND date BETWEEN '$start_date' AND '$yesterday' and status='Approved'"));
					if($billCount>0){
						$response="available";// if yes then block the customer from billing
						$query = "UPDATE `everycustomer` SET `status` = 'Blocked', `remark`='The ID proof number ($proofNumber) provided by the customer has been billed recently' WHERE `contact` = '$contactNo' AND `date`='$date';";
						mysqli_query($con,$query);	
					}else{
						$response="not-available";
					}	 				
				}else{
					$response="not-available";
				}
			}
			echo $response;
			
		}
		
	}
	/* -----------------------  ADD FRAUD CUSTOMER DETAILS @ xmanageCustomer.php ------------------- */
	if (isset($_POST['action']) && $_POST['action']=="fraud") {

		$cusname = $_POST['cusname'];
		$mob = $_POST['cusmob'];
		$type = $_POST['type'];
		$idnumber = $_POST['idnumber'];
		$date = date('Y-m-d');

		$inscon = "INSERT INTO fraud (name,phone,type,idnumber,date) VALUES ('$cusname','$mob','$type','$idnumber','$date')";
		if(mysqli_query($con,$inscon)){
			echo "success";
		}else{
			echo "error";
		}
	}	
?>

