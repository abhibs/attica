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
	// 		else if(!empty($_POST['everyCustomerImage'])){
	// 			$fName1 = $_POST['everyCustomerImage'];
	// 		}
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
	// 		else if(!empty($_POST['everyCustomerImage'])){
	// 			$fName1 = $_POST['everyCustomerImage'];
	// 		}
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



if (isset($_POST['submitStock'])) {

    $stock       = $_POST['stock'];
    $standards   = $_POST['standards'];
    $count       = (int)$_POST['count'];  // ensure integer
    $location    = $_POST['location'];
    $responsible = $_POST['responsible'];
    $assign      = $_POST['assign'];
    $vehicle     = $_POST['vehicle'];
    $branch      = $_POST['branch'];
    $date        = date('Y-m-d');

    /* -------- File upload -------- */
    $final_file = '';
    if (!empty($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name'])) {

        $file      = $_FILES['file']['name'];
        $file_loc  = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_type = $_FILES['file']['type'];

        // Better extension extraction
        $file_extn = '';
        $dotPos = strrpos($file, '.');
        if ($dotPos !== false) {
            $file_extn = substr($file, $dotPos); // e.g. ".jpg"
        }

        $folder       = "StockManagement/";
        $filenameBase = date('YmdHis');
        $final_file   = $filenameBase . 'stockmanagement' . $file_extn;

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
            INSERT INTO stockitem
            (stock, standards, count, location,
             responsible, assign, vehicle, branch,
             image, date)
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

        /* 2) UPDATE main stock table (column - count) */

        // whitelist to avoid SQL injection on column name
        $allowedStocks = [
			'Aluminium-Clamps-Pack',
			'Brown-Corner-Beeding',
			'Brown-PVC-Panel',
			'Brown-wall-Panel',
			'Cash-Counting-Machine',
			'Electrical-miscellaneous',
			'Floor-Mat(Local)',
			'Floor-Mat(Premium)',
			'Karat-Machine',
			'LED-Roof(Warranty)',
			'LED-Roof(Without Warranty)',
			'Printer-Machine',
			'Red-Fevicol-Box',
			'System',
			'UPS/Battery',
			'Weighing-Scale',
			'White-PVC-Panel',
			'White-Corner-Beeding',
		];

        if (in_array($stock, $allowedStocks, true)) {

            $col = $stock; // safe (whitelisted)

            // Ensure row id=1 exists once
            mysqli_query($con, "INSERT IGNORE INTO stock (id) VALUES (1)");

            $updateSql = "
                UPDATE stock
                SET $col = GREATEST(0, COALESCE($col, 0) - ?)
                WHERE id = 1
            ";

            $stmt2 = mysqli_prepare($con, $updateSql);
            if (!$stmt2) {
                throw new Exception('Prepare failed for stock update: ' . mysqli_error($con));
            }

            mysqli_stmt_bind_param($stmt2, "i", $count);

            if (!mysqli_stmt_execute($stmt2)) {
                throw new Exception('Execute failed for stock update: ' . mysqli_stmt_error($stmt2));
            }
            mysqli_stmt_close($stmt2);
        }

        // -------------------------------
        // If everything above is OK
        // -------------------------------
        mysqli_commit($con);
        echo "<script>alert('Stock Submitted Successfully'); window.location.href='stockManagement.php';</script>";

    } catch (Exception $e) {

        // Something failed: rollback everything
        mysqli_roll_back($con);
        $err = addslashes($e->getMessage());
        echo "<script>alert('Error: {$err}'); window.location.href='stockManagement.php';</script>";
    }
}



