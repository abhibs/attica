<?php
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$time = date("H:i:s");
	$date = date("Y-m-d");
	
	//BRANCH ID
	$branchId=$_SESSION['branchCode'];
	$employeeId = $_SESSION['employeeId'];
	
	//PRICE FOR THE GOLD
	$branchPrice = mysqli_fetch_assoc(mysqli_query($con,"SELECT priceId FROM branch WHERE branchId='$branchId'"));
	$priceId = $branchPrice['priceId'];
	if($priceId==1){
		$price='Bangalore';
	}
	else if($priceId==2){
		$price='Karnataka';
	}
	else if($priceId==3){
		$price='Andhra Pradesh'; 
	}
	else if($priceId==4){
		$price='Telangana';
	}
	else if($priceId==5){
		$price='Chennai';
	}
	else if($priceId==6){
		$price='Tamilnadu';
	}

	// New Modification Code for Transaction : xaddTransaction.php 
	if(isset($_POST['submitTransaction'])){
	
	    if(!isset($_SESSION['bill'])){
			
			$_SESSION['transaction_error']="";
			$customerId = $_SESSION['customerID'];
		    //GENERATING BILL ID
			$billId = mt_rand(100000,999999);			
			//DATA FROM FORM		
			$gspd = $_POST['gspd'];		
			$paymentType=$_POST['paymentType'];
			

			/*	
			if($paymentType == "Cash" || $paymentType == "Cash/IMPS"){
				$sql="SELECT cash FROM gold WHERE date='$date' AND city='$price' AND type='$gspd' ORDER BY id DESC";
				$row=mysqli_fetch_array(mysqli_query($con,$sql));
				$rate=$row['cash'];
			}else if($paymentType == "NEFT/RTGS"){
				$sql="SELECT transferRate FROM gold WHERE date='$date' AND city='$price' AND type='$gspd' ORDER BY id DESC";
				$row=mysqli_fetch_array(mysqli_query($con,$sql));
				$rate=$row['transferRate'];
			}
			 */


			if ($paymentType == "Cash" || $paymentType == "Cash/IMPS") {
				$sql = "SELECT cash FROM gold WHERE date='$date' AND city='$branchId' AND type='$gspd' ORDER BY id DESC";
				$row = mysqli_fetch_array(mysqli_query($con, $sql));
				$rate = $row['cash'];
			} else if ($paymentType == "NEFT/RTGS") {
				$sql = "SELECT transferRate FROM gold WHERE date='$date' AND city='$branchId' AND type='$gspd' ORDER BY id DESC";
				$row = mysqli_fetch_array(mysqli_query($con, $sql));
				$rate = $row['transferRate'];
			}

			if(isset($_POST['accountHolder']) && isset($_POST['relationship']) && isset($_POST['bankname']) && isset($_POST['branchname']) && isset($_POST['accountnumber']) && isset($_POST['ifsc'])){
				//Bank/NBFC DATA				
				$cname = $_POST['accountHolder'];
				$relationship = $_POST['relationship'];
				$bankName = $_POST['bankname'];
				$branchBank = $_POST['branchname'];
				$accountNo = $_POST['accountnumber'];
				$ifsc = $_POST['ifsc'];
				$sqlIMPS = mysqli_query($con,"INSERT INTO bankdetails(customerId,billID,accountHolder,relationship,bank,branch,account,ifsc,Bproof,date,time) VALUES ('$customerId','$billId','$cname','$relationship','$bankName','$branchBank','$accountNo','$ifsc',' ','$date','$time')");
				if(!$sqlIMPS){
					unset($_SESSION['bill']);
					$_SESSION["transaction_error"]="Error Storing the bank details. Please try again!";
					header("Location: xaddTransaction.php");
				}
			}			
			
			//STORING SESSION VALUES
			$_SESSION['bill'] = $billId;
			$_SESSION['paymentType'] = $paymentType;						
			$_SESSION['metalType'] = $gspd;
			$_SESSION['rate1'] = $rate;	
			
			
		}else if(isset($_SESSION['bill'])){
			
		    $billId = $_SESSION['bill'];
			$gspd = $_SESSION['metalType'];
			$rate = $_SESSION['rate1'];
			$_SESSION['transaction_error']="";
			
		}
		
		$type = $_POST['type'];
		$pieces = $_POST['piece'];
		$weight = trim($_POST['weight']);
		$stoneWeight = trim($_POST['sweight']);
		$reading = $_POST['reading'];
		$purity = $_POST['purity'];
		$nine = $_POST['nine'];
		$margin = 0.03;
		//calculated Data
		$purityPerc = $purity/100;
		$grossamount = round($rate * $purityPerc * $reading);
		$gross =intval($grossamount);
		

		$jsonData= array();
		$ornamentList = array();
		
		$query="INSERT INTO ornament (billId,employeeId,metal,type,typeInfo,weight,sWaste,reading,purity,nine,date,gross,rate,pieces) VALUES ('$billId','$employeeId','$gspd','$type','','$weight','$stoneWeight','$reading','$purity','$nine','$date','$gross','$rate','$pieces')";
		if (mysqli_query($con,$query)){
			$ornamentList="";
			$bill = $_SESSION['bill'];
			$sql = mysqli_query($con,"SELECT * FROM ornament WHERE billId='$bill' AND date='$date'");
			while($row = mysqli_fetch_assoc($sql)){
				if($row['nine']== "916"){
					$ornamentList.= "<tr class='active-row'>";
				}
				else{
					$ornamentList.= "<tr class='passive-row'>";
				}
				$ornamentList.= "<td>" . $row['metal'] . "</td>";
				$ornamentList.= "<td>" . $row['type'] ."  (". $row['pieces']. ")</td>";
				$ornamentList.= "<td>" . $row['weight'] . "</td>";
				$ornamentList.= "<td>" . $row['sWaste'] . "</td>";
				$ornamentList.= "<td>" . $row['reading'] . "</td>";
				$ornamentList.= "<td>" . $row['purity'] . "</td>";
				$ornamentList.= "<td>" . $row['gross'] . "</td>";
				$ornamentList.= "<td><b><a class='text-danger'  title='Delete Record' onclick='delete_ornament(".$row['ornamentId'].")'><i class='fa fa-trash' aria-hidden='true'></i> Delete</a></b></td>";
				$ornamentList.= "</tr>";
			}
			$ornamentList.= "<hr>";
			$totalsql = mysqli_query($con,"SELECT SUM(weight) AS Weight,SUM(sWaste) AS sWaste, SUM(reading) AS reading,SUM(gross) AS Gross,SUM(pieces) as totalPieces FROM ornament WHERE billId='$bill' AND date='$date'");
			$totalResult = mysqli_fetch_assoc($totalsql);
			$totalPurity = round((($totalResult['Gross']/$totalResult['reading'])/$_SESSION['rate1'])*100,2);
			$ornamentList.= "<tr class='totalResult'><td>Total</td>";
			$ornamentList.= "<td>".$totalResult['totalPieces']."</td>";
			$ornamentList.= "<td>".round($totalResult['Weight'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['sWaste'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['reading'],3)."</td>";
			$ornamentList.= "<td>".$totalPurity."</td>";
			$ornamentList.= "<td>".$totalResult['Gross']."</td>";
			$ornamentList.= "<td></td></tr>"; 
			
		}else{
			$ornamentList="<tr>Error In Storing The Ornament Details!!</tr>";
		}
		
		$marginP =3;
		$margin_amount = ($totalResult['Gross']*$marginP)/100;
		$net_amount = round($totalResult['Gross'] - $margin_amount);

		$jsonData["ornamentList"]=$ornamentList;
		$jsonData["grossW"]=round($totalResult['Weight'],3);
		$jsonData["netW"]=round($totalResult['reading'],3);
		$jsonData["grossA"]=$totalResult['Gross'];		
		$jsonData["margin"]=round($margin_amount);
		$jsonData["net1"]=$net_amount;
		$jsonData["marginP"]=$marginP;
		$jsonData["paymentType"]=$_SESSION['paymentType'];
		$jsonData["gspd"]=$_SESSION['metalType'];
		$jsonData["transaction_error"]=$_SESSION['transaction_error'];
		echo json_encode($jsonData);
	}
	/* ======================================================================= */
	
	
	/* 
	    XADDTRANSACTION.PHP
		@TO ADD THE TRANSACTION DETAILS
	*/
	if(isset($_POST['submitT'])){
		unset($_SESSION['transaction_error']);
	    $available = $_SESSION['bal'];
	    $amountP = $_POST['payable'];
		$paymentType = $_SESSION['paymentType'];
		// + 
		$cashA = $_POST['cashA']; 

		if($available < $amountP && $paymentType == "Cash"){
			$_SESSION["transaction_error"]="Insufficient Funds !!";
			header("Location: xaddTransaction.php");
		}else if($available < $cashA && $paymentType == "Cash/IMPS"){
			$_SESSION["transaction_error"]="Insufficient Funds !!";
			header("Location: xaddTransaction.php");
		}
		else{
			
		    $billId = $_SESSION['bill'];
		    $customerId = $_SESSION['customerID'];
			$gspd = $_SESSION['metalType'];
			$rate = $_SESSION['rate1'];
			$name = $_POST['name'];
			$phone = $_POST['phone'];
			$grossW = $_POST['grossW'];
			$netW = $_POST['netW'];
			$grossA = $_POST['grossA'];
			$marginA = $_POST['margin'];
			$netA = $_POST['net1'];
			$Amount = $_POST['payable'];
			$bills = $_POST['bill2'];
			$purity = round((($grossA/$netW)/$rate)*100,3);
			$margin = $_POST['marginP'];
			$status = "Begin";
			
			//EMPTY VARIABLES
			$releases = '';
			$ple = '';
			$sta = '';
			$staDate = '';
			$ophoto = '';
			$remarks = '';
			$releaseID = 0;
			$relDate = '';
			
			if($_SESSION['paymentType'] == "Cash"){
				$cashA = $Amount;
				$impsA = 0;	
			}else if($_SESSION['paymentType'] == "NEFT/RTGS"){
			   	$cashA = 0;				
				$impsA = $Amount;
			}else{
				$cashA = $_POST['cashA'];
				$impsA = $_POST['impsA'];
			}
			
			$packetNo = (isset($_POST['packetNo'])) ? $_POST['packetNo'] : "";
			$final_file1 = '';
			$empId = (isset($_POST['empId'])) ? $_POST['empId'] : "";
			$transQuery = mysqli_query($con,"INSERT INTO trans(customerId,billId,name,phone,billCount,releases,ple,grossW,netW,netA, grossA,amountPaid,rate,date,time,branchId,type,comm,margin,purity,price,status,metal,sta,staDate,CurrentBranch,flag,kyc,ophoto,remarks,paymentType,cashA,impsA,releaseID,relDate,packetNo, employeeId, approvetime, imps_empid, impstime) VALUES('$customerId','$billId','$name','$phone','$bills','$releases','$ple','$grossW','$netW','$netA','$grossA','$Amount','$rate','$date','$time','$branchId','Physical Gold','$margin','$marginA','$purity','$priceId','$status','$gspd','$sta','$staDate','$branchId','$employeeId','$final_file1','$ophoto','$remarks','$paymentType','$cashA','$impsA','$releaseID','$relDate','$packetNo', '$empId', '', '', '')");
			
			$updateEC = "UPDATE everycustomer SET status='Billed' WHERE contact='$phone' AND date='$date' AND branch='$branchId'";
			
			if($transQuery){
				if(mysqli_query($con,$updateEC)){
					$_SESSION['rel'] = "";
					echo header("location:xuploadDocs.php?cid=".$customerId."&mob=".$phone."&bid=".$billId);
				}
				else{
				    $_SESSION["transaction_error"]="Update Failure. Transaction cannot be processed!";
					header("Location: xaddTransaction.php");
				}
			}
			else{
					$_SESSION["transaction_error"]="Transaction cannot be processed!";
					header("Location: xaddTransaction.php");
			}
		}
	}
	
	/*
		XADDTRANSACTION.PHP
		@RESET THE ORNAMENT DETAILS
	*/
	if(isset($_POST['submitTs'])){
		unset($_SESSION['bill']);
		unset($_SESSION['metalType']);
		unset($_SESSION['rate1']);
		unset($_SESSION['paymentType']);
	}
?>

