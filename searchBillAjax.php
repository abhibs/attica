<?php
	session_start();
	include("dbConnection.php");
	$date = date('Y-m-d');
	$current_date = $date;
	
	
	if(isset($_POST["stateCode"])){	
		if($_POST["search_date"]!=""){
			$current_date = $_POST["search_date"];
		}

		
		if($_POST["stateCode"]=='Karnataka'){
			$state="'Karnataka'";
		}		
		if($_POST["stateCode"]=='Andhra Pradesh & Telangana'){
			$state="'Andhra Pradesh','Telangana'";
		}		
		if($_POST["stateCode"]=='Tamilnadu & Pondicherry'){
			$state="'Tamilnadu','Pondicherry'";
		}
		$resultArray = array();
		$resultArray = "";
		$invQuery=mysqli_query($con,"SELECT count(*) as bill_count, b.branchName, t.branchId FROM `trans` t inner join branch b on b.branchId=t.branchId where t.date='$current_date' and t.status='Approved' and b.state IN ($state) AND b.status=1 group by t.branchId,b.branchName order by bill_count desc");
		$invCount=mysqli_num_rows($invQuery);
		if($invCount>0){					
			$i = 1;
			while($invRow = mysqli_fetch_assoc($invQuery)){
				$resultArray.= "<tr>";
				$resultArray.= "<td>".$i."</td>";
				$resultArray.= "<td>".$invRow['branchName']."</td>";
				$resultArray.= "<td>".$invRow['branchId']."</td>";
				$resultArray.= "<td>".$invRow['bill_count']."</td>";
				$resultArray.= "<td class='text-center'><a class='btn btn-info' style='margin-right:10px;' data-toggle='modal' data-target='#viewDetails' onClick='transactionBillResult(\"".$invRow['branchId']."\",\"".$current_date."\")'><i style='color:#ffa500' class='fa fa-eye'></i> View </a></td>";
				$resultArray.= "</tr>";										
				$i++;
			}
			
		}						
		echo $resultArray;

	}
	
	if(isset($_POST["branch_id"])){
		if($_POST["bill_date"]==""){
		    $billDate=$current_date;
		}else{
		    $billDate=$_POST["bill_date"];
		}
		$branch_id = $_POST["branch_id"];
		$branchQuery=mysqli_query($con,"SELECT branchName FROM `branch` where branchId='$branch_id'");
		$bRow = mysqli_fetch_assoc($branchQuery);
		$branchName=$bRow["branchName"];
		$jsonData= array();
		$resultArray = array();
		$resultArray = "";
		$billQuery=mysqli_query($con,"SELECT id as invoice_id,name as customer_name,phone as contact_number, grossW FROM `trans` where date='$billDate' and branchId='$branch_id' and status='Approved'");
		$billCount=mysqli_num_rows($billQuery);
		if($billCount>0){
			$i = 1;
			while($row = mysqli_fetch_assoc($billQuery)){
				$resultArray.="<tr>";
				$resultArray.="<td>".$i."</td>";
				$resultArray.="<td>".$row['customer_name']."</td>";
				$resultArray.="<td><a href='searchbillsTransaction.php?search=".$row['contact_number']."'>".$row['contact_number']."</a></td>";
				//$resultArray.="<td>".round($row['grossW'],2)."</td>";
				$resultArray.= "</tr>";
				$i++;
			}
		}
		
		$jsonData["resultList"]=$resultArray;
		$jsonData["branch_name"]=$branchName;
		echo json_encode($jsonData);
		
	}
	
	if(isset($_POST["billDate"])){									

		$billDate=$_POST["billDate"];
		if($_POST["search_state"]!=""){
			$search_state = $_POST["search_state"];
			$condition ="and b.state='$search_state'";
		}else{
			$condition ="";
		}
		$resultArray = array();
		$resultArray = "";
		$invQuery=mysqli_query($con,"SELECT count(*) as bill_count, b.branchName, t.branchId FROM `trans` t inner join branch b on b.branchId=t.branchId where t.date='$billDate' and t.status='Approved' and b.status=1 $condition group by t.branchId,b.branchName order by bill_count desc");
		$invCount=mysqli_num_rows($invQuery);
		if($invCount>0){					
			$i = 1;
			while($invRow = mysqli_fetch_assoc($invQuery)){
				$resultArray.= "<tr>";
				$resultArray.= "<td>".$i."</td>";
				$resultArray.= "<td>".$invRow['branchName']."</td>";
				$resultArray.= "<td>".$invRow['branchId']."</td>";
				$resultArray.= "<td>".$invRow['bill_count']."</td>";
				$resultArray.= "<td class='text-center'><a class='btn btn-info' style='margin-right:10px;' data-toggle='modal' data-target='#viewDetails' onClick='transactionBillResult(\"".$invRow['branchId']."\",\"".$billDate."\")'><i style='color:#ffa500' class='fa fa-eye'></i> View </a></td>";
				$resultArray.= "</tr>";										
				$i++;
			}
			
		}						
		echo $resultArray;

	}	
	/* --------------- Display the transaction  -------------------- */
	if(isset($_POST["transactionType"]) && $_POST["transactionType"]=="physical_gold"){	
	
		$jsonData= array();
		$resultArray = array();
		$resultArray = "";		
		
		$branchId=$_POST["trans_branchId"];
		$billDate=$_POST["trans_date"];
		
		if($branchId=="" && $branchId==null){			
			$condition1 = "";
			$branchName = "";
		}else{			
			$condition1 = "and t.branchId='$branchId' ";
			$branchQuery=mysqli_query($con,"SELECT branchName from branch where branchId='$branchId'");
			$bRow= mysqli_fetch_assoc($branchQuery);
			$branchName=$bRow["branchName"];
		}
		
		if($billDate=="" && $billDate==null){
			$billDate=$current_date;
			$condition2 = "and t.date='$billDate' ";
		}else{			
			$condition2 = "and t.date='$billDate' ";
		}
		
		//echo "SELECT b.branchName, t.* FROM `trans` t inner join branch b on b.branchId=t.branchId WHERE b.status=1 $condition1 $condition2 order by id desc";
		$transQuery=mysqli_query($con,"SELECT b.branchName, t.* FROM `trans` t inner join branch b on b.branchId=t.branchId WHERE b.status=1 and t.status!='Deleted' $condition1 $condition2 order by id desc");
		$tCount=mysqli_num_rows($transQuery);
		if($tCount>0){			
			$i = 1;
			while($tRow = mysqli_fetch_assoc($transQuery)){
				if($tRow['status']=="Begin" || $tRow['status']=="Rejected"){
					$deleteBtn="<button class='btn btn-danger' onclick='delete_transaction(".$tRow['id'].")'><i class='fa fa-trash' aria-hidden='true' title='Delete this transaction'></i> </button>";
				}else{
					$deleteBtn="";
				}
				if($tRow['type']=="Physical Gold"){
					$type=$tRow['type'];
				}else{
					$type="<a href='searchReleasedata.php?releaseID=".$tRow['releaseID']."&date=".$tRow['date']."&phone=".$tRow['phone']."' class='btn btn-success btn-md'>".$tRow['type']."</a><p style='font-weight: bold;font-size: 14px;'>".$tRow['releaseID']."</p>";
				}
				$resultArray.= "<tr>";
				$resultArray.= "<td>".$i."</td>";
				$resultArray.= "<td><h5 style='text-transform:uppercase;color:#123C69;'>" . $tRow['branchName'] . "</h5>". $tRow['branchId'] ."<br></td>";
				$resultArray.= "<td><h5 style='text-transform:uppercase;color:#123C69;'>" . $tRow['name'] . "</h5>". $tRow['phone'] ."<br></td>";
				$resultArray.= "<td class='text-center'><p><a target='_blank' class='btn btn-success btn-md' title='View Bill' href='Invoice.php?id=".base64_encode($tRow['id'])."'> ".$tRow['billId']." </a> </p>".$tRow['time']."</td>";
				$resultArray.= "<td class='text-center'><p><a target='_blank' href='searchOrnament.php?billId=".$tRow['billId']."&date=".$tRow['date']."' class='btn btn-warning btn-md'>".$tRow['metal']."</a></p>".$tRow['rate']."</td>";
				$resultArray.= "<td class='text-center'>".$type."</td>";
				$resultArray.= "<td class='text-center'>".$tRow['paymentType']."<br>Cash : ".$tRow['cashA']."<br>IMPS : ".$tRow['impsA']."</td>";
				$resultArray.= "<td>".$tRow['grossW']."</td>";
				$resultArray.= "<td>".$tRow['netW']."</td>";
				$resultArray.= "<td>".$tRow['grossA']."</td>";
				$resultArray.= "<td>".$tRow['netA']."</td>";
				$resultArray.= "<td>".$tRow['amountPaid']."</td>";
				$resultArray.= "<td>".$tRow['status']."</td>";
				$resultArray.= "<td class='text-center'><a href='searchTrans.php?search=".$tRow['id']."' class='btn btn-success btn-md'><i class='fa_Icon fa fa-pencil'></i> </a> ".$deleteBtn."</td>";
				$resultArray.= "</tr>";										
				$i++;
			}	
		}

		$jsonData["resultList"]=$resultArray;
		$jsonData["branch_name"]=$branchName;
		$jsonData["billDate"]=$billDate;		
		echo json_encode($jsonData);
	}
	
	/* ------------------------------- Update the transaction  --------------------------------- */
	if(isset($_POST["update_transaction"]) && $_POST["update_transaction"]=="update_transaction"){	
		parse_str($_POST['billData'], $data);
		$today = date('Y-m-d');
		$current_date = strtotime($today);
		$trans_id=$data['trans_id'];
		$name=strtoupper($data['name']);
		
		$updateData=	"`name` = '$name',
						`phone` = '$data[phone]',
						`grossW` = '$data[grossW]',
						`netW` = '$data[netW]',
						`grossA` = '$data[grossA]',
						`netA` = '$data[netA]',
						`comm` = '$data[comm]',
						`margin` = '$data[margin]',
						`amountPaid` = '$data[amountPaid]',
						`paymentType` = '$data[paymentType]',
						`cashA` = '$data[cashA]',
						`impsA` = '$data[impsA]',						
						`sta`= '$data[sta]',
						`staDate`= '$data[staDate]',
						`status`= '$data[status]',
						`remarks`= '$data[remarks]',
						`ezviz_vc`= '$data[ezviz_vc]',
						`packetNo`= '$data[packetNo]'";
		
 		$updateTransQuery="UPDATE `trans` SET ".$updateData." WHERE `id` = ".$trans_id;
		$updateCustomerQuery="UPDATE `customer` SET `name` = '$name' WHERE `customerId` = '".$data['customerId']."' and mobile='".$data['phone']."'";	
		$updateReleasedataQuery="UPDATE `releasedata` SET `name` = '$name' WHERE `customerId` = '".$data['customerId']."' and phone='".$data['phone']."' and releaseID='".$data['releaseID']."'";	
		if(mysqli_query($con,$updateTransQuery)){
			mysqli_query($con,$updateCustomerQuery);
			if($data['type']=="Release Gold" && $data['releaseID']!=""){
			    mysqli_query($con,$updateReleasedataQuery);
			}
			$response = "SUCCESS";
		}else{
			$response = "ERROR";
		} 
		echo $response;
	
	}
	
	
	/* ---------------------------------- Delete the transaction -------------------------------- */
	if(isset($_POST["delete_transaction"]) && $_POST["delete_transaction"]=="delete_transaction"){
		
		$id=$_POST["trans_id"];		
		if($id==""){
		    $response = "ERROR";
		}else{

    		$delQuery="DELETE FROM `trans` WHERE `id` = ".$id;	
    
    		if(mysqli_query($con,$delQuery)){
    			$response = "SUCCESS";
    		}else{
    			$response = "ERROR";
    		}		
		}
		echo $response;
	}

	/* --------------- Display the release transaction  -------------------- */
	if(isset($_POST["transactionType"]) && $_POST["transactionType"]=="release_gold"){	
	
		$jsonData= array();
		$resultArray = array();
		$resultArray = "";		
		
		$branchId=$_POST["trans_branchId"];
		$billDate=$_POST["trans_date"];
		
		if($branchId=="" && $branchId==null){			
			$condition1 = "";
			$branchName = "";
		}else{			
			$condition1 = "and rd.BranchId='$branchId' ";
			$branchQuery=mysqli_query($con,"SELECT branchName from branch where branchId='$branchId'");
			$bRow= mysqli_fetch_assoc($branchQuery);
			$branchName=$bRow["branchName"];
		}
		
		if($billDate=="" && $billDate==null){
			$billDate=$current_date;
			$condition2 = "and rd.date='$billDate' ";
		}else{			
			$condition2 = "and rd.date='$billDate' ";
		}

		$releasedataQuery=mysqli_query($con,"SELECT rd.*,b.branchName, rd.branchName as branch_name FROM `releasedata` rd inner join branch b on b.branchId=rd.BranchId WHERE b.status=1 $condition1 $condition2 order by rid desc");
		$tCount=mysqli_num_rows($releasedataQuery);
		if($tCount>0){			
			$i = 1;
			while($tRow = mysqli_fetch_assoc($releasedataQuery)){
				if($tRow['status']=="Begin" || $tRow['status']=="Rejected"){
					$deleteBtn="<button class='btn btn-danger' onclick='delete_transaction(".$tRow['rid'].")'><i class='fa fa-trash' aria-hidden='true' title='Delete this transaction'></i> </button>";
				}else{
					$deleteBtn="";
				}
				$resultArray.= "<tr>";
				$resultArray.= "<td>".$i."</td>";
				$resultArray.= "<td><h5 style='text-transform:uppercase;color:#123C69;'>" . $tRow['branchName'] . "</h5>". $tRow['BranchId'] ."<br></td>";
				$resultArray.= "<td><h5 style='text-transform:uppercase;color:#123C69;'>" . $tRow['name'] . "</h5>". $tRow['phone'] ."<br>". $tRow['customerId'] ."</td>";
				$resultArray.= "<td>".$tRow['releaseID']."</td>";
				$resultArray.= "<td>".$tRow['relPlaceType']."</td>";
				$resultArray.= "<td>".$tRow['relPlace']."</td>";				
				$resultArray.= "<td>".$tRow['type']."</td>";
				$resultArray.= "<td>".$tRow['amount']."</td>";
				$resultArray.= "<td>".$tRow['relCash']."</td>";											
				$resultArray.= "<td>".$tRow['relIMPS']."</td>";											
				$resultArray.= "<td>".$tRow['relGrossW']."</td>";	
				$resultArray.= "<td>".$tRow['relNetW']."</td>";	
				$resultArray.= "<td>".$tRow['relPurity']."</td>";
				$resultArray.= "<td>".$tRow['status']."</td>";
				$resultArray.= "<td class='text-center'><a href='searchReleasedata.php?releaseID=".$tRow['releaseID']."&date=".$tRow['date']."&phone=".$tRow['phone']."' class='btn btn-success btn-md'><i class='fa_Icon fa fa-pencil'></i> </a> ".$deleteBtn."</td>";
				$resultArray.= "</tr>";										
				$i++;
			}	
		}

		$jsonData["resultList"]=$resultArray;
		$jsonData["branch_name"]=$branchName;
		$jsonData["billDate"]=$billDate;		
		echo json_encode($jsonData);

	}

	/* ------------------------------- Update the release data transaction  --------------------------------- */
	if(isset($_POST["update_release_transaction"]) && $_POST["update_release_transaction"]=="update_release_transaction"){	
		parse_str($_POST['releaseData'], $data);
		$rid=$data['rid'];
		$name=strtoupper($data['name']);
		
		$updateData=	"`name` = '$name',
						`amount` = '$data[amount]',
						`type` = '$data[type]',
						`relPlace` = '$data[relPlace]',
						`relCash` = '$data[relCash]',
						`relIMPS` = '$data[relIMPS]',
						`status`= '$data[status]'";	
	
		$updateReleasedataQuery="UPDATE `releasedata` SET ".$updateData." WHERE `rid` = '".$rid."'";	
		if(mysqli_query($con,$updateReleasedataQuery)){
			$response = "SUCCESS";
		}else{
			$response = "ERROR";
		} 
		echo $response;
	
	}
	
	/* ---------------------------------- Delete the releasedata transaction -------------------------------- */
	if(isset($_POST["delete_release_transaction"]) && $_POST["delete_release_transaction"]=="delete_release_transaction"){
		
		$id=$_POST["rid"];		
		if($id==""){
		    $response = "ERROR";
		}else{

    		$delQuery="DELETE FROM `releasedata` WHERE `rid` = ".$id;	
    
    		if(mysqli_query($con,$delQuery)){
    			$response = "SUCCESS";
    		}else{
    			$response = "ERROR";
    		}		
		}
		echo $response;
	}
	
	/* ----------------------------- Update the ornament ----------------------------- */
	if(isset($_POST["update_ornament"]) && $_POST["update_ornament"]=="update_ornament"){	
		$billId=$_POST['billId'];	
		$updateData=	"`type` = '$_POST[type]',
						`pieces` = '$_POST[pieces]',
						`weight` = '$_POST[weight]',
						`sWaste` = '$_POST[sWaste]',
						`reading` = '$_POST[reading]',
						`purity` = '$_POST[purity]',
						`gross` = '$_POST[gross]'";
		
 		$updateOrnamentQuery="UPDATE `ornament` SET ".$updateData." WHERE `ornamentId` = ".$_POST['ornamentId'];	
		if(mysqli_query($con,$updateOrnamentQuery)){
			$response = "SUCCESS";
		}else{
			$response = "ERROR";
		} 
		echo $response;	
	}
	
			/* ----------------------------- Update the Pledge ornament ----------------------------- */
		if(isset($_POST["update_pledge_ornament"]) && $_POST["update_pledge_ornament"]=="update_pledge_ornament"){	
			$invoiceId=$_POST['invoiceId'];	
			$updateData=	"`ornamentType` = '$_POST[ornamentType]',
							`count` = '$_POST[count]',
							`grossW` = '$_POST[grossW]',
							`stoneW` = '$_POST[stoneW]',
							`purity` = '$_POST[purity]',
							`amount` = '$_POST[amount]'";
			
			 $updateOrnamentQuery="UPDATE `pledge_ornament` SET ".$updateData." WHERE `id` = ".$_POST['id'];	
			if(mysqli_query($con,$updateOrnamentQuery)){
				$response = "SUCCESS";
			}else{
				$response = "ERROR";
			} 
			echo $response;	
		}


	/* ----------------------------- Delete the Pledge ornament ----------------------------- */
	if (isset($_POST['deletePledgeOrnament']) && $_POST['deletePledgeOrnament'] == 'deletePledgeOrnament') {
		$id = $_POST['id'];
		$invoiceId = $_POST['invoiceId'];
	
		$deleteQuery = "DELETE FROM pledge_ornament WHERE id='$id' AND invoiceId='$invoiceId'";
		if (mysqli_query($con, $deleteQuery)) {
			echo 'SUCCESS'; 
		} else {
			echo 'FAILURE'; 
		}
		exit();
	}
	
	
	/* ---------------------------- Update the fund transaction --------------------------- */
	if(isset($_POST["update_fund"]) && $_POST["update_fund"]=="update_fund"){	
	
		$updateData=	"`request` = '$_POST[request]',
						`customerName` = '$_POST[customerName]',
						`customerMobile` = '$_POST[customerMobile]',
						`status` = '$_POST[status]'";
		
 		$updateFundQuery="UPDATE fund SET ".$updateData." WHERE id = ".$_POST['fund_id'];	
		if(mysqli_query($con,$updateFundQuery)){
			$response = "SUCCESS";
		}else{
			$response = "ERROR";
		}
		echo $response;	
	}
	
	
	/* ----------------------------- Delete the fund transaction ----------------------------- */
	if(isset($_POST["action"]) && $_POST["action"]=="delete_fundTransaction"){
		
		$id=$_POST["fund_id"];		
 		if($id==""){
		    $response = "ERROR";
		}else{
			//$delQuery="UPDATE fund SET status='Rejected' WHERE id = ".$id;
    		$delQuery="DELETE FROM fund WHERE id = ".$id;	    
    		if(mysqli_query($con,$delQuery)){
    			$response = "SUCCESS";
    		}else{
    			$response = "ERROR";
    		}		
		}
		echo $response;
	}
	
	
	/* ----------------------------- Update OTP flag ----------------------------- */
	if(isset($_POST["action"]) && $_POST["action"]=="flag_otp"){
		$id = $_POST["otp_id"];
		$emp_id = $_POST["emp_id"];
		$remarks = $_POST['remarks'];
 		if($id==""){
		    $response = "ERROR";
		}else{
			
			$updateQuery="UPDATE otp SET flag=1, employee_id='$emp_id', remarks='$remarks' WHERE otpid = ".$id;
			  		    
    		if(mysqli_query($con,$updateQuery)){
    			$response = "SUCCESS";
    		}else{
    			$response = "ERROR";
    		}		
		}
		echo $response;			
	}
?>
