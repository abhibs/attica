<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");
$date = date('Y-m-d');
$time = date("H:i:s");
$branchCode = $_SESSION['branchCode'] ?? '';
$employeeId = $_POST['employeeId'] ?? '';

/*  ADD NEW CUSTOMER ( @ xeveryCustomer.php )  */
if (isset($_POST['submitNC'])) {

        $customer = strtoupper(trim($_POST['cusname']));
        $contact = trim($_POST['cusmob']);
        $type = $_POST['customerType'];
        $walkinType = $_POST['walkinType'];
        if (file_exists($_FILES['customerDocument']['tmp_name']) && $_FILES['customerDocument']['error'] == 0) {
                $file = $_FILES['customerDocument']['name'];
                $file_loc = $_FILES['customerDocument']['tmp_name'];
                $file_size = $_FILES['customerDocument']['size'];
                $file_type = $_FILES['customerDocument']['type'];

                $file_extn = substr($file, strrpos($file, '.'));
                $folder = "OrnamentDocs/";
                $new_size = $file_size / 1024;

                $new_file_name = strtolower($file);
                $filename = date('YmdHis');
                $final_file = $filename . $employee . 'ORNDOC' . $file_extn;

                move_uploaded_file($file_loc, $folder . $final_file);
                $ornament_docs = $folder . $final_file;
        } else {
                $ornament_docs = '';
        }



        /* ======================== BILL COUNT ======================== */
        $billsQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS bills FROM trans WHERE phone='$contact' AND status='Approved'"));
        if ($billsQuery['bills'] >= 2) {
                $status = "Blocked";
                $remark = "Blocked";
        } else {
                $status = "Begin";
                $remark = "";
        }
        /* ======================== END OF BILL COUNT ======================== */

        $extra = [];
        $extra["GrossW"] = '';
        $extra["bills"] = $billsQuery['bills'];
        $extra = json_encode($extra);
        $query = "INSERT INTO everycustomer(customer,contact,type,idnumber,branch,image,quotation,date,time,status,status_remark,remark,ornament_docs,block_counter,extra,reg_type,agent,agent_time,BMId,walkinType) VALUES ('$customer','$contact','$type','','$branchCode','','','$date','$time','$status','','$remark','$final_file','0','$extra','BM','', '','$employeeId','$walkinType')";
        if (mysqli_query($con, $query)) {
                header("location:xeveryCustomer.php");
        } else {
                echo "<script type='text/javascript'>alert('Error Storing Data!')</script>";
                echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
        }
}
/*  WALKIN DATA ( @ xbranchEnquiry.php ) */
if (isset($_POST['submitremarks'])) {
        //POSTED VALUES
        $id = $_POST['id'];
        $cusname = $_POST['cusname'];
        $mob = $_POST['cusmob'];
        $type = $_POST['typeGold'];
        $metal = $_POST['metal'];
        $gross = $_POST['gwt'];
        $net = $_POST['nwt'];
        $purity = $_POST['purity'];
        $ramt = (isset($_POST['ramt'])) ? $_POST['ramt'] : '';
        $havingG = ($type == 'physical') ? $_POST['havingG'] : "without";
        $remarks = $_POST['remarks'];
        $enquiry_image = $_POST['enquiry_image'];
        $quotation = $_POST['quotation'];
        $bills = $_POST['bills'];
        $givenRate = $_POST['givenRate'];
        $ecr = $_POST['ecr'];
        $ezviz_vc = $_POST['ezviz_vc'];

        if (file_exists($_FILES['file']['tmp_name'])) {
                $file = $_FILES['file']['name'];
                $file_loc = $_FILES['file']['tmp_name'];
                $file_size = $_FILES['file']['size'];
                $file_type = $_FILES['file']['type'];
                $file_extn = substr($file, strrpos($file, '.') - 1);
                $folder = "EnquiryImage/";
                $new_size = $file_size / 1024;
                $new_file_name = strtolower($file);
                $filename = date('Ymdhis');
                $final_file = str_replace($new_file_name, $filename . 'EnqImg' . $file_extn, $new_file_name);
                move_uploaded_file($file_loc, $folder . $final_file);
        } else {
                $final_file = '';
        }




        // Handle first file input - karatM
        if (file_exists($_FILES['karatM']['tmp_name'])) {
                $file = $_FILES['karatM']['name'];
                $file_loc = $_FILES['karatM']['tmp_name'];
                $file_size = $_FILES['karatM']['size'];
                $file_type = $_FILES['karatM']['type'];
                $file_extn = substr($file, strrpos($file, '.')); // fixed extension parsing
                $folder = "KaratMachine/";
                $new_size = $file_size / 1024;
                $new_file_name = strtolower($file);
                $filename = date('Ymdhis');
                $final_file_karat = $filename . 'KaratM' . $file_extn;
                move_uploaded_file($file_loc, $folder . $final_file_karat);
        } else {
                $final_file_karat = '';
        }

        // Handle second file input - weightM
        if (file_exists($_FILES['weightM']['tmp_name'])) {
                $file = $_FILES['weightM']['name'];
                $file_loc = $_FILES['weightM']['tmp_name'];
                $file_size = $_FILES['weightM']['size'];
                $file_type = $_FILES['weightM']['type'];
                $file_extn = substr($file, strrpos($file, '.')); // fixed extension parsing
                $folder = "WeightMachine/";
                $new_size = $file_size / 1024;
                $new_file_name = strtolower($file);
                $filename = date('Ymdhis');
                $final_file_weight = $filename . 'WeightM' . $file_extn;
                move_uploaded_file($file_loc, $folder . $final_file_weight);
        } else {
                $final_file_weight = '';
        }










        //UN INITIALIZED VARIABLES
        $a = '';
        $b = 1;
        $sql = "INSERT INTO walkin(name, mobile, gold, havingG, metal, issue, gwt, nwt, purity, ramt, branchId, agent_id, followUp, comment, remarks,enquiry_image, zonal_remarks, status, emp_type, date, indate, time, quotation, bills, quot_rate, ecr, karatM, weightM, ezviz_vc) VALUES('$cusname', '$mob', '$type', '$havingG', '$metal', '$a', '$gross', '$net', '$purity', '$ramt', '$branchCode', '$a', '$a', '$a', '$remarks','$final_file', '$a', '$b', '$employeeId', '$date', '$a', '$time', '$quotation', '$bills', '$givenRate', '$ecr', '$final_file_karat', '$final_file_weight', '$ezviz_vc');";
        $sql .= "UPDATE everycustomer SET status='Enquiry' WHERE id='$id'";

        if (mysqli_multi_query($con, $sql)) {
                echo "<script type='text/javascript'>alert('Customer Remarks Added Successfully!')</script>";
                echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
        } else {
                echo "<script type='text/javascript'>alert('Error Storing Data!')</script>";
                echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
        }
}
/*  ADD NEW CUSTOMER DETAILS ( @ xaddCustomer.php )   */
if (isset($_POST["submitCustomer"])) {
        $customerId = $_POST['cusId'];
        $name = strtoupper($_POST['name']);
        $gender = $_POST['gender'];
        $dob = $_POST['day'] . "-" . $_POST['month'] . "-" . $_POST['year'];
        $mobile = $_POST['mobile'];

        $caline = $_POST['caline'] ?? "Address";
        $clocality = $_POST['clocality'] ?? "Address";
        $cland = $_POST['cland'] ?? "Address";
        $cstate = $_POST['cstate'] ?? "Address";
        $ccity = $_POST['ccity'] ?? "Address";
        $cpin = $_POST['cpin'] ?? "560001";


        $IDproof = $_POST['idProof'];
        $IDproofNum = $_POST['idProofNum'];
        $ADDproof = $_POST['addProof'];
        $ADDproofNum = $_POST['addProofNum'];

        $additionalPerson = $_POST['relation'];
        $additionalContact = $_POST['rcontact'];

        $type = $_POST['typeGold'];
        $x = '';
        $y = 0;
        if (isset($_POST['image']) && !empty($_POST['image'])) {
                $cphoto = $_POST['image'];
                $datetime = date('Ymdhis');
                $folderPath = "CustomerImage/";
                $image_parts = explode(";base64,", $cphoto);
                //$image_type_aux = explode("image/", $image_parts[0]);
                //$image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fName = $customerId . $datetime . '.jpg';
                $fName1 = "CustomerImage/" . $customerId . $datetime . '.jpg';
                $files = $folderPath . $fName;
                file_put_contents($files, $image_base64);
        }
        //              else if(!empty($_POST['everyCustomerImage'])){
        //                      $fName1 = $_POST['everyCustomerImage'];
        //              }
        else {
                $fName1 = '';
        }
        $sql = "INSERT INTO customer(customerId,name,gender,dob,mobile,amobile,paline,pcity,pstate,ppin,pland,plocality,caline,ccity,cstate,cpin,cland,clocality,resident,idProof,addProof,idFile,addFile,idNumber,addNumber,date,customerImage,time,relation,rcontact,cusThump,custSign) VALUES ('$customerId','$name','$gender','$dob','$mobile','$x','$caline','$ccity','$cstate','$cpin','$cland','$clocality','$caline','$ccity','$cstate','$cpin','$cland','$clocality','$x','$IDproof','$ADDproof','$x','$x','$IDproofNum','$ADDproofNum','$date','$fName1','$time','$additionalPerson','$additionalContact','$x','$x')";

        if (mysqli_query($con, $sql)) {
                echo header("location:xsuccess.php?type=" . $type);
        } else {
                echo "<script>alert('Customer Registration Failed!')</script>";
                echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
        }
}

/*  UPDATE EXISTING CUSTOMER DETAILS ( @ xexistingCustomer.php )  */
if (isset($_POST["updateCustomer"])) {
        $customerId = $_POST['cusId'];
        $mobile = $_POST['mobile'];
        $additionalPerson = $_POST['relation'];
        $additionalContact = $_POST['rcontact'];

        $caline = $_POST['caline'] ?? "Address";
        $clocality = $_POST['clocality'] ?? "Address";
        $cland = $_POST['cland'] ?? "Address";
        $cstate = $_POST['cstate'] ?? "Address";
        $ccity = $_POST['ccity'] ?? "Address";
        $cpin = $_POST['cpin'] ?? "560001";

        $IDproof = $_POST['idProof'];
        $IDproofNum = $_POST['idProofNum'];
        $ADDproof = $_POST['addProof'];
        $ADDproofNum = $_POST['addProofNum'];

        if (isset($_POST['image']) && !empty($_POST['image'])) {
                $cphoto = $_POST['image'];
                $datetime = date('Ymdhis');
                $folderPath = "CustomerImage/";
                $image_parts = explode(";base64,", $cphoto);
                //$image_type_aux = explode("image/", $image_parts[0]);
                //$image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fName = $customerId . $datetime . '.jpg';
                $fName1 = "CustomerImage/" . $customerId . $datetime . '.jpg';
                $files = $folderPath . $fName;
                file_put_contents($files, $image_base64);
        }
        //              else if(!empty($_POST['everyCustomerImage'])){
        //                      $fName1 = $_POST['everyCustomerImage'];
        //              }
        else {
                $fName1 = '';
        }
        $type = $_POST['typeGold'];
        $sql = "UPDATE customer SET relation='$additionalPerson', rcontact='$additionalContact', idNumber='$IDproofNum', addNumber='$ADDproofNum', customerImage='$fName1', idProof='$IDproof', addProof='$ADDproof', caline='$caline', ccity='$ccity', cstate='$cstate', cpin='$cpin', cland='$cland', clocality='$clocality', paline='$caline', pcity='$ccity', pstate='$cstate', ppin='$cpin', pland='$cland', plocality='$clocality' WHERE mobile='$mobile'";

        if (mysqli_query($con, $sql)) {
                echo header("location:xsuccess.php?type=" . $type);
        } else {
                echo "<script>alert('Customer Registration Failed!')</script>";
                echo "<script>setTimeout(\"location.href = 'xeveryCustomer.php';\",150);</script>";
        }
}

/*  ADD NEW CUSTOMER - ZONAL ( @ addCustomerZonal.php )  */
if (isset($_POST['zonalSubmitNC'])) {

        $userType = $_POST['userType'];
        $branchID = $_POST['branchID'];
        $cusname = strtoupper(trim($_POST['name']));
        $mob = $_POST['contact'];
        $type = $_POST['type'];

        /* ======================== BILL COUNT ======================== */
        $billsQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS bills FROM trans WHERE phone='$mob' AND status='Approved'"));
        $status = "0";
        $remark = "";
        /* ======================== END OF BILL COUNT ======================== */

        $extra = [];
        $extra["GrossW"] = $_POST['grossW'];
        $extra['itemCount'] = $_POST['itemCount'];
        $extra['Hallmark'] = $_POST['hallmark'];
        $extra['With'] = (isset($_POST['withMetal'])) ? $_POST['withMetal'] : 'without';
        $extra['RelAmount'] = (isset($_POST['relAmount'])) ? $_POST['relAmount'] : '';
        $extra['RelSlips'] = (isset($_POST['relSlips'])) ? $_POST['relSlips'] : '';
        $extra['Pledge'] = (isset($_POST['pledge'])) ? $_POST['pledge'] : 'no';
        $extra["bills"] = $billsQuery['bills'];
        $extra = json_encode($extra);

        $idnumber = $_POST['remarks'];
        $fName1 = '';

        $inscon = "INSERT INTO everycustomer(customer,contact,type,idnumber,branch,image,quotation,date,time,status,status_remark,remark,block_counter,extra,reg_type,agent,agent_time) VALUES ('$cusname','$mob','$type','$idnumber','$branchID','','$fName1','$date','$time','$status','','$remark','0','$extra','$userType','','')";
        if (mysqli_query($con, $inscon)) {
                echo "<script>setTimeout(\"location.href = 'addCustomerZonal.php';\",150);</script>";
        } else {
                echo "<script type='text/javascript'>alert('SOMETHING WENT WRONG,PLEASE TRY AGAIN.')</script>";
                echo "<script>setTimeout(\"location.href = 'addCustomerZonal.php';\",150);</script>";
        }
}

/*  WALKIN DATA ( @ xVMEnquiry.php ) */
if (isset($_POST['submitremarksVM'])) {

        //POSTED VALUES
        $id = $_POST['id'];
        $cusname = $_POST['cusname'];
        $mob = $_POST['cusmob'];
        $type = $_POST['typeGold'];
        $metal = $_POST['metal'];
        $gross = $_POST['gwt'];
        $net = $_POST['nwt'];
        $branchCode = $_POST['branchid'];
        $purity = $_POST['purity'];
        $ramt = (isset($_POST['ramt'])) ? $_POST['ramt'] : '';
        $havingG = ($type == 'physical') ? $_POST['havingG'] : "without";
        $remarks = $_POST['remarks'];
        $quotation = $_POST['quotation'];
        $bills = $_POST['bills'];
        $givenRate = $_POST['givenRate'];

        $ecr = $_POST['ecr'];

        //UN INITIALIZED VARIABLES
        $a = '';
        $b = 0;

        $sql = "INSERT INTO walkin(name, mobile, gold, havingG, metal, issue, gwt, nwt, purity, ramt, branchId, agent_id, followUp, comment, remarks, zonal_remarks, status, emp_type, date, indate, time, quotation, bills, quot_rate, ecr) VALUES('$cusname', '$mob', '$type', '$havingG', '$metal', '$a', '$gross', '$net', '$purity', '$ramt', '$branchCode', '$a', '$a', '$a', '$remarks', '$a', '$b', '$employeeId', '$date', '$a', '$time', '$quotation', '$bills', '$givenRate', '$ecr');";
        $sql .= "UPDATE everycustomer SET status='Enquiry' WHERE id='$id'";

        if (mysqli_multi_query($con, $sql)) {
                echo "<script type='text/javascript'>alert('Customer Remarks Added Successfully!')</script>";
                echo "<script>setTimeout(\"location.href = 'vm/viewRegistered.php';\",150);</script>";
        } else {
                echo "<script type='text/javascript'>alert('Error Storing Data!')</script>";
                echo "<script>setTimeout(\"location.href = 'vm/viewRegistered.php';\",150);</script>";
        }
}



// -------------------leave Management--------------------

if (isset($_POST['submitLeave'])) {

	$empName = $_POST['empName'];
	$empId = $_POST['employeeId'];
	$designation = $_POST['designation'];
	$branchId = $_POST['branchId'];
	$leaveFrom = $_POST['leaveFrom'];
	$leaveTo = $_POST['leaveTo'];
	$reason = $_POST['reason'];

	$status = 'Pending';
	$date = date('Y-m-d');
	$time = date('H:i:s');

	$sql = "
        INSERT INTO `leave`
        (empName, employeeId, designation, branchId, leaveFrom, leaveTo, reason, status, `date`, `time`)
        VALUES
        ('$empName', '$employeeId', '$designation', '$branchId', '$leaveFrom', '$leaveTo', '$reason', '$status', '$date', '$time')
    ";

	if (mysqli_query($con, $sql)) {
		echo "<script>alert('Leave Submitted Successfully'); window.location.href='leaveManagement.php';</script>";
	} else {
		echo "<script>alert('Error: " . mysqli_error($con) . "'); window.location.href='leaveManagement.php';</script>";
	}
}

//STOCK MANAGEMENT

if (isset($_POST['submitStock'])) {

	$stock = $_POST['stock'];
	$standards = $_POST['standards'];
	$count = (int) $_POST['count'];  // ensure integer
	$location = $_POST['location'];
	$responsible = $_POST['responsible'];
	$assign = $_POST['assign'];
	$vehicle = $_POST['vehicle'];
	$branch = $_POST['branch'];
	$date = date('Y-m-d');

	/* -------- File upload -------- */
	$final_file = '';
	if (!empty($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name'])) {

		$file = $_FILES['file']['name'];
		$file_loc = $_FILES['file']['tmp_name'];
		$file_size = $_FILES['file']['size'];
		$file_type = $_FILES['file']['type'];

		// Better extension extraction
		$file_extn = '';
		$dotPos = strrpos($file, '.');
		if ($dotPos !== false) {
			$file_extn = substr($file, $dotPos); // e.g. ".jpg"
		}

		$folder = "StockManagement/";
		$filenameBase = date('YmdHis');
		$final_file = $filenameBase . 'stockmanagement' . $file_extn;

		if (!is_dir($folder)) {
			@mkdir($folder, 0777, true);
		}

		move_uploaded_file($file_loc, $folder . $final_file);
	}

	// -------------------------------
	// TRANSACTION START
	// -------------------------------
	mysqli_begin_transaction($con);

	try {

		/* 1) INSERT INTO stockitem (log) */
		$sql = "
            INSERT INTO `stockitem`
            (`stock`, `standards`, `count`, `location`,
             `responsible`, `assign`, `vehicle`, `branch`,
             `image`, `date`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

		$stmt = mysqli_prepare($con, $sql);
		if (!$stmt) {
			throw new Exception('Prepare failed for stockitem: ' . mysqli_error($con));
		}

		mysqli_stmt_bind_param(
			$stmt,
			"ssisssssss",
			$stock,
			$standards,
			$count,
			$location,
			$responsible,
			$assign,
			$vehicle,
			$branch,
			$final_file,
			$date
		);

		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception('Execute failed for stockitem: ' . mysqli_stmt_error($stmt));
		}
		mysqli_stmt_close($stmt);


		// -------------------------------
		// If everything above is OK
		// -------------------------------
		mysqli_commit($con);
		echo "<script>alert('Stock Submitted Successfully'); window.location.href='stockManagement.php';</script>";

	} catch (Exception $e) {

		// Something failed: rollback everything
		mysqli_rollback($con);
		$err = addslashes($e->getMessage());
		echo "<script>alert('Error: {$err}'); window.location.href='stockManagement.php';</script>";
	}
}


/* Doorstep */
if (isset($_POST['submitDoorstep'])) {

        $branchCode = 'AGPL000';
        $branchName = 'HO';

        // --- small helper ---
        function esc(mysqli $con, $v)
        {
                return mysqli_real_escape_string($con, trim((string)$v));
        }

        /* ----------------- BASIC FIELDS ----------------- */
        $customer  = strtoupper(esc($con, $_POST['cusname']   ?? ''));
        $contact   = esc($con, $_POST['cusmob']   ?? '');
        $location  = esc($con, $_POST['location'] ?? '');
        $metal     = esc($con, $_POST['metalType'] ?? '');
        $grams     = esc($con, $_POST['grams']    ?? '');
        $type      = esc($con, $_POST['customerType'] ?? '');   // business_type
        $kms       = esc($con, $_POST['kms']      ?? '');
        $reason1   = esc($con, $_POST['reason1']  ?? '');       // maps to `reason1`
        $reason2   = esc($con, $_POST['reason2']  ?? '');       // maps to `reason2`

        // Release-only fields (can be empty)
        $releaseAmount = isset($_POST['releaseAmount']) ? esc($con, $_POST['releaseAmount']) : '';
        $releasePlace  = esc($con, $_POST['releasePlace'] ?? '');

        $branchCodeEsc = esc($con, $branchCode);
        $status        = 'Pending';      // default from your enum

        /* ----------------- FILE UPLOADS ----------------- */
        $goldImageFile   = null;
        $releaseDocFile  = null;

        // Make sure these folders exist and have write permissions
        $goldDir  = 'gold_images/';
        $docDir   = 'release_docs/';

        if (!is_dir($goldDir)) @mkdir($goldDir, 0777, true);
        if (!is_dir($docDir))  @mkdir($docDir, 0777, true);

        // GOLD IMAGE (optional)
        if (!empty($_FILES['goldImage']['name']) && $_FILES['goldImage']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['goldImage']['name'], PATHINFO_EXTENSION);
                $goldImageFile = 'gold_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

                if (!move_uploaded_file($_FILES['goldImage']['tmp_name'], $goldDir . $goldImageFile)) {
                        $goldImageFile = null;   // upload failed, store as NULL
                }
        }

        // RELEASE DOC (optional)
        if (!empty($_FILES['releaseDoc']['name']) && $_FILES['releaseDoc']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['releaseDoc']['name'], PATHINFO_EXTENSION);
                $releaseDocFile = 'rel_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

                if (!move_uploaded_file($_FILES['releaseDoc']['tmp_name'], $docDir . $releaseDocFile)) {
                        $releaseDocFile = null;
                }
        }

        /* ----------------- BUILD INSERT QUERY ----------------- */
        // release_requests columns now:
        // id, name, contact_number, location, metal_type, grams, kms,
        // reason, reason2, release_amount, branch, business_type, release_place,
        // status, release_doc, gold_image, quotation, customer_id_doc, ...

        $releaseAmountSql = ($releaseAmount !== '' ? "'" . $releaseAmount . "'" : "NULL");
        $releaseDocSql    = ($releaseDocFile  !== null ? "'" . esc($con, $releaseDocFile)  . "'" : "NULL");
        $goldImageSql     = ($goldImageFile   !== null ? "'" . esc($con, $goldImageFile)   . "'" : "NULL");

        $query = "
        INSERT INTO release_requests(name, contact_number, location, metal_type, grams, kms, reason1, reason2, release_amount, branch, business_type, release_place, status, release_doc, gold_image, quotation, customer_id_doc) VALUES
            ('$customer', '$contact', '$location', '$metal', '$grams', '$kms', '$reason1', '$reason2', $releaseAmountSql, '$branchCodeEsc', '$type', '$releasePlace', '$status', $releaseDocSql, $goldImageSql, '', '')";

        if (mysqli_query($con, $query)) {
                header("Location: doorstep.php");
                exit;
        } else {
                echo "<h3 style='color:red'>Error Storing Data!</h3>";
                echo "<p>" . mysqli_error($con) . "</p>";
        }
}

