<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');
	$time = date('h:i:s'); 
	$branchCode = $_SESSION['branchCode'];

	//  UPLOAD DOCS FROM PHYSICAL BILL ( @ xuploadDocs.php )
	if (isset($_POST['uploadViaComputer'])) {

		$billRow = $_POST['billRow'];
		$impsRow = $_POST['impsRow'];

		$phone = $_POST['phone'];
		$name = $_POST['name'];
		$grossW = $_POST['grossW'];
		$amountPaid = $_POST['amountPaid'];

		$datetime = date('Ymdhis');
		$custId = $_SESSION['customerID'];

		// SIGNATURE UPLOAD
		$foldersign = "CustomerSignature/";
		$cphoto3 = $_POST['image3'];      
		if ($cphoto3 != '') {
			$image_parts3 = explode(";base64,", $cphoto3);
			$image_base643 = base64_decode($image_parts3[1]);
			$signature = $custId . $datetime . '.jpg';
			$signature1 =  $foldersign .  $signature;
			file_put_contents($signature1, $image_base643);
		} else {
			$signature = '';
		}
		
		// THUMB IMPRESSION UPLOAD
		/*$folderthumb = "CustomerThumb/";
		$cphoto4 = $_POST['image4'];      
		if ($cphoto4 != '') {
			$image_parts4 = explode(";base64,", $cphoto4);
			$image_base644 = base64_decode($image_parts4[1]);
			$thumb =  $custId . $datetime . '.jpg';
			$thumb1 = $folderthumb . $thumb;
			file_put_contents($thumb1, $image_base644);
		} else {
			$thumb = '';
		}*/

		// ID PROOF UPLOAD
		$folder = "CustomerDocuments/";
		if (file_exists($_FILES['idFile']['tmp_name'])) {  
			$file = $_FILES['idFile']['name'];
			$file_loc = $_FILES['idFile']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$idFile = str_replace($new_file_name, $datetime . $custId . 'IDFILE' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $idFile);
		} else {
			$idFile = '';
		}
		// ADDRESS PROOF UPLOAD
		if (file_exists($_FILES['addFile']['tmp_name'])) {  
			$file = $_FILES['addFile']['name'];
			$file_loc = $_FILES['addFile']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$addFile = str_replace($new_file_name, $datetime . $custId . 'ADDFILE' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $addFile);
		} else {
			$addFile = '';
		}

		$sql = "UPDATE customer SET idFile='$idFile',addFile='$addFile',custSign='$signature',cusThump='$signature' WHERE mobile='$phone';";
		$sql .= "UPDATE trans SET status='Pending', time='$time' WHERE id='$billRow';";
		$sql .= "INSERT INTO customer_data(mobile,idFile,addFile,date,time) VALUES ('$phone','$idFile','$addFile','$date','$time');"; 

		// BANK ACCOUNT PROOF UPLOAD
		if ($impsRow != 'CASH' && $impsRow != '') {
			if (file_exists($_FILES['bankFile']['tmp_name'])) {  
				$file = $_FILES['bankFile']['name'];
				$file_loc = $_FILES['bankFile']['tmp_name'];
				$file_extn = substr($file, strrpos($file, '.') - 1);
				$folderb = "BankDocuments/";
				$new_file_name = strtolower($file);
				$Bproof = str_replace($new_file_name, $datetime . $custId . 'BPROOF' . $file_extn, $new_file_name);
				move_uploaded_file($file_loc, $folderb . $Bproof);
				$sql .= "UPDATE bankdetails SET Bproof='$Bproof' WHERE ID='$impsRow'";
			}
		}
		
		if (mysqli_multi_query($con, $sql)) {
			$_SESSION['rel'] = "";
			//echo header("location:xmessage.php?name=" . $name . "&phone=" . $phone . "&grossW=" . $grossW . "&amount=" . $amountPaid);
			/*--------------------  + new changes -----------------------*/
			
		    //SOLD OUT API	
			require 'Config/CallCenter.php';	
			$rowS = [
			"mobile" => $phone,
			"customer_id" => $_SESSION['customerID'],
			"billing_date" => $date,
			"branch_id" => $_SESSION['branchCode']
			];
			$chs = curl_init($billingInfoURL);
			header('Content-Type: application/json');
			$a = json_encode($rowS, JSON_PRETTY_PRINT);
			curl_setopt($chs, CURLOPT_POSTFIELDS, $a);
			curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($chs);
			curl_close($chs);	
			
			unset($_SESSION['customerID']);
			unset($_SESSION['bill']);
			unset($_SESSION['Rid']);
			unset($_SESSION['mobile']);
			
			header("location:xphysicalStatus.php");
			
			//echo "<script>setTimeout(\"location.href = 'xphysicalStatus.php';\",150);</script>";
			
			/* ----------------- end new changes -------------------------- */
		} else {
			echo "<script type='text/javascript'>alert('Error Updating!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xphysicalStatus.php';\",150);</script>";
		}
		
	}
	/* =============================================== END OF UPLOAD DOCUMENTS VIA COMPUTER ======================================== */


	/* **************************************************** BEFORE RELEASE BILL ************************************************ */

	//  COMPUTER UPLOAD FROM "BEFORE" RELEASE BILL ( @ xuploadDocsRel.php )
	if (isset($_POST['uploadViaComputerRel'])) {

		$rid = $_POST['billRow'];
		$type = $_POST['type'];

		$phone = $_POST['phone'];
		$name = $_POST['name'];
		$datetime = date('Ymdhis');
		$custId = $_POST['cid'];

		$foldersign = "CustomerSignature/";
		$cphoto3 = $_POST['image3'];      // SIGNATURE
		if ($cphoto3 != '') {
			$image_parts3 = explode(";base64,", $cphoto3);
			$image_base643 = base64_decode($image_parts3[1]);
			$signature = $custId . $datetime . '.jpg';
			$signature1 =  $foldersign .  $signature;
			file_put_contents($signature1, $image_base643);
		} else {
			$signature = '';
		}

		/*$folderthumb = "CustomerThumb/";
		$cphoto4 = $_POST['image4'];      // THUMB IMPRESSION
		if ($cphoto4 != '') {
			$image_parts4 = explode(";base64,", $cphoto4);
			$image_base644 = base64_decode($image_parts4[1]);
			$thumb =  $custId . $datetime . '.jpg';
			$thumb1 = $folderthumb . $thumb;
			file_put_contents($thumb1, $image_base644);
		} else {
			$thumb = '';
		}*/

		/*   CUSTOMER DOCUMENTS   */
		$folder = "CustomerDocuments/";
		if ($_FILES['idFile']['tmp_name']) {  // ID PROOF
			$file = $_FILES['idFile']['name'];
			$file_loc = $_FILES['idFile']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$idFile = str_replace($new_file_name, $datetime . $custId . 'IDFILE' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $idFile);
		} else {
			$idFile = '';
		}
		if ($_FILES['addFile']['tmp_name']) {  // ADDRESS PROOF
			$file = $_FILES['addFile']['name'];
			$file_loc = $_FILES['addFile']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$addFile = str_replace($new_file_name, $datetime . $custId . 'ADDFILE' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $addFile);
		} else {
			$addFile = '';
		}

		/*   RELEASE DOCUMENTS   */
		$folder2 = "ReleaseDocuments/";
		if ($_FILES['reldoc']['tmp_name']) {  // reldoc PROOF
			$file = $_FILES['reldoc']['name'];
			$file_loc = $_FILES['reldoc']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$reldoc = str_replace($new_file_name, $datetime . $custId . 'reldoc' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder2 . $reldoc);
		} else {
			$reldoc = '';
		}

		/*   OTHER RELEASE DOCUMENTS   */
		if ($type == 'NBFC') {
			if ($_FILES['nbfcproof']['tmp_name']) {  // reldoc PROOF
				$file = $_FILES['nbfcproof']['name'];
				$file_loc = $_FILES['nbfcproof']['tmp_name'];
				$file_extn = substr($file, strrpos($file, '.') - 1);
				$new_file_name = strtolower($file);
				$nbfcproof = str_replace($new_file_name, $datetime . $custId . 'nbfcproof' . $file_extn, $new_file_name);
				move_uploaded_file($file_loc, $folder2 . $nbfcproof);
				$bankproof = '';
			} else {
				$nbfcproof = '';
				$bankproof = '';
			}
		} else if ($type == 'BANK') {
			if ($_FILES['bankproof']['tmp_name']) {  // reldoc PROOF
				$file = $_FILES['bankproof']['name'];
				$file_loc = $_FILES['bankproof']['tmp_name'];
				$file_extn = substr($file, strrpos($file, '.') - 1);
				$new_file_name = strtolower($file);
				$bankproof = str_replace($new_file_name, $datetime . $custId . 'bankproof' . $file_extn, $new_file_name);
				move_uploaded_file($file_loc, $folder2 . $bankproof);
				$nbfcproof = '';
			} else {
				$bankproof = '';
				$nbfcproof = '';
			}
		} else {
			$bankproof = '';
			$nbfcproof = '';
		}

		$sql = "UPDATE customer SET idFile='$idFile',addFile='$addFile',custSign='$signature',cusThump='$signature' WHERE mobile='$phone';";
		$sql .= "UPDATE releasedata SET relDoc1='$reldoc',cProof='$nbfcproof',bProof='$bankproof',status='Pending' WHERE rid='$rid';";
		$sql .= "INSERT INTO customer_data(mobile,idFile,addFile,date,time) VALUES ('$phone','$idFile','$addFile','$date','$time');"; 

		if (mysqli_multi_query($con, $sql)) {
			echo "<script type='text/javascript'>alert('Documents uploaded successfully!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
		} else {
			echo "<script type='text/javascript'>alert('Error Uploading,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
		}
	}



	/* ************************************************ AFTER RELEASE BILL ************************************************** */

	//  COMPUTER UPLOAD FROM "AFTER" RELEASE BILL ( @ xuploadDocsAfterRel.php )
	if (isset($_POST['uploadViaComputerAfterRel'])) {

		$billRow = $_POST['billRow'];
		$paymentType = $_POST['paymentType'];
		$impsRow = $_POST['impsRow'];

		$custId = $_POST['customerId'];
		$releaseID = $_POST['releaseID'];
		$relDate = $_POST['relDate'];

		$phone = $_POST['phone'];
		$name = $_POST['name'];
		$grossW = $_POST['grossW'];
		$amountPaid = $_POST['amountPaid'];

		$datetime = date('Ymdhis');

		// AFTER RELEASE DOC
		if ($_FILES['relDoc']['tmp_name']) {
			$folder = "ReleaseDocuments/";
			$file = $_FILES['relDoc']['name'];
			$file_loc = $_FILES['relDoc']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$relDoc = str_replace($new_file_name, $datetime . $releaseID . 'RELDOC' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $relDoc);
		} else {
			$relDoc = '';
		}
		$sql = "UPDATE trans SET ple='$relDoc',status='Pending', time='$time' WHERE id='$billRow';";
		$sql .= "UPDATE releasedata SET relDoc3='$relDoc' WHERE releaseID='$releaseID' AND customerId='$custId' AND date='$relDate';";

		// IMPS DOC / BANK DOCUMENTS UPLOAD
		if ($impsRow != 'CASH' && $impsRow != '') {
			if ($_FILES['bankFile']['tmp_name']) {
				$folder1 = "BankDocuments/";
				$file = $_FILES['bankFile']['name'];
				$file_loc = $_FILES['bankFile']['tmp_name'];
				$file_extn = substr($file, strrpos($file, '.') - 1);
				$new_file_name = strtolower($file);
				$bankFile = str_replace($new_file_name, $datetime . $custId . 'BPROOF' . $file_extn, $new_file_name);
				move_uploaded_file($file_loc, $folder1 . $bankFile);
				$sql .= "UPDATE bankdetails SET Bproof='$bankFile' WHERE ID='$impsRow'";
			} else {
				$bankFile = '';
			}
		}

		if (mysqli_multi_query($con, $sql)) {
			//echo header("location:xmessage.php?name=" . $name . "&phone=" . $phone . "&grossW=" . $grossW . "&amount=" . $amountPaid);
			/*--------------------  + new changes -----------------------*/
			
			//SOLD OUT API	
			require 'Config/CallCenter.php';	
			$rowS = [
			"mobile" => $phone,
			"customer_id" => $_SESSION['customerID'],
			"billing_date" => $date,
			"branch_id" => $_SESSION['branchCode']
			];
			$chs = curl_init($billingInfoURL);
			header('Content-Type: application/json');
			$a = json_encode($rowS, JSON_PRETTY_PRINT);
			curl_setopt($chs, CURLOPT_POSTFIELDS, $a);
			curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($chs);
			curl_close($chs);
			
			unset($_SESSION['customerID']);
			unset($_SESSION['bill']);
			unset($_SESSION['Rid']);
			unset($_SESSION['mobile']);
			
			header("location:xphysicalStatus.php");
			
			//echo "<script>setTimeout(\"location.href = 'xphysicalStatus.php';\",150);</script>";
			
			/* ----------------- end new changes -------------------------- */
		} else {
			echo "<script type='text/javascript'>alert('Error Updating!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
		}
	}
	/* ==================================== END OF UPLOAD DOCUMENTS "AFTER" RELEASE BILL VIA COMPUTER ======================================== */


