<?php
	
    error_reporting(E_ERROR | E_PARSE);
    session_start();
    include("dbConnection.php");
    date_default_timezone_set("Asia/Kolkata");
    $date = date('Y-m-d');
    $time = date("H:i:s");
    $branch = $_SESSION['branchCode'];

if (isset($_POST["submitAttend"])) {
	    $val = $_POST['empp'];
	    $branchNameSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branch'"));
    	$emplNameSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT empId,name FROM employee WHERE empId='$val'"));
		
	    if(isset($emplNameSQL['empId']) && isset($emplNameSQL['name'])){
	        $brname = $branchNameSQL['branchName'];
			$name = $emplNameSQL['name'];		
			$img = $_POST['image'];
			$folderPath = "AttendanceImage/";
			$image_parts = explode(";base64,", $img);
			//$image_type_aux = explode("image/", $image_parts[0]);
			//$image_type = $image_type_aux[1];
			$image_base64 = base64_decode($image_parts[1]);
			$fileName = $val.date('YmdHis'). '.jpg';
			$file = $folderPath . $fileName;
			file_put_contents($file, $image_base64);

        // Check if there are any previous attendance records for this employee
        $prevAttendanceDateSQL = mysqli_fetch_assoc(mysqli_query($con, "SELECT date FROM attendance WHERE empId='$val' ORDER BY date DESC LIMIT 1"));

        if (!$prevAttendanceDateSQL) {
            // If there are no previous attendance records, mark the employee as present
            $sql = "INSERT INTO attendance(empId, name, branch, branchId, date, time, photo, vmempID, vmStatus, vmTime, lastlogin, status) VALUES ('$val', '$name', '$brname', '$branch', '$date', '$time', '$fileName', '0', '0', '00:00:00', '0000-00-00', '0')";
            if (mysqli_query($con, $sql)) {
                echo "<script>alert('Employee: " . $val . " Marked Present')</script>";
            } else {
                echo "<script>alert('No Attendance Marked');</script>";
            }
        } else {
            $prevAttendanceDate = $prevAttendanceDateSQL['date'];
            $prevAttendanceTimestamp = strtotime($prevAttendanceDate);
            $currentTimestamp = strtotime($date);
            $daysSinceLastAttendance = floor(($currentTimestamp - $prevAttendanceTimestamp - 1) / (60 * 60 * 24));

            $checkPreviousStatusSQL = "SELECT status FROM attendance WHERE empId = '$val' AND status = 1";
            $previousStatusResult = mysqli_query($con, $checkPreviousStatusSQL);
            $numPreviousStatus = mysqli_num_rows($previousStatusResult);

            // Update status if $daysSinceLastAttendance is greater than or equal to 3
            if ($daysSinceLastAttendance >= 3 || $numPreviousStatus > 0) {
                // Check the current status
                $checkStatusSQL = "SELECT status FROM attendance WHERE empId = '$val' AND date = '$date'";
                $statusResult = mysqli_query($con, $checkStatusSQL);
                $existingStatus = mysqli_fetch_assoc($statusResult)['status'];

                if ($existingStatus == 0) {
                    // Update status to 1
                    $updateStatusSQL = "UPDATE attendance SET status = 1 WHERE empId = '$val' AND date = '$date'";
                    if (mysqli_query($con, $updateStatusSQL)) {
                        // Status updated successfully, proceed to insert a new record
                        $sql = "INSERT INTO attendance(empId, name, branch, branchId, date, time, photo, vmempID, vmStatus, vmTime, lastlogin, status) VALUES ('$val', '$name', '$brname', '$branch', '$date', '$time', '$fileName', '0', '0', '00:00:00', '$prevAttendanceDate', '1')";
                        if (mysqli_query($con, $sql)) {
                            echo "<script>alert('Access denied: PLEASE CONTACT HR DEPARTMENT.');</script>";
                        } else {
                            echo "<script>alert('Error inserting new record: " . mysqli_error($con) . "');</script>";
                        }
                    } else {
                        echo "<script>alert('Error updating status: " . mysqli_error($con) . "');</script>";
                    }
                } else {
                    // Status is already 1, do not update again
                    echo "<script>alert('Access denied: PLEASE CONTACT HR DEPARTMENT.');</script>";
                }
            }

            if ($daysSinceLastAttendance <= 2 && $numPreviousStatus < 1) {
                $sql = "INSERT INTO attendance(empId, name, branch, branchId, date, time, photo, vmempID, vmStatus, vmTime, lastlogin, status) VALUES ('$val', '$name', '$brname', '$branch', '$date', '$time', '$fileName', '0', '0', '00:00:00', '$prevAttendanceDate', '0')";
                if (mysqli_query($con, $sql)) {
                    echo "<script>alert('Employee: " . $val . " Marked Present')</script>";
                } else {
                    echo "<script>alert('No Attendance Marked');</script>";
                }
            }
        }

        echo "<script>setTimeout(\"location.href = 'addAttend.php';\", 150);</script>";
    } else {
        echo "<script>alert('YOUR EMPLOYEE-ID IS INVALID, PLEASE CONTACT HR DEPARTMENT');</script>";
        echo "<script>setTimeout(\"location.href = 'addAttend.php';\", 150);</script>";
    }
}

	
	if (isset($_POST["submitissue"])) {
		$sql = "select branchName from branch where branchId='$branch'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_array($res);
		$branchId = $row['branchName'];
		$inscon = "INSERT INTO issue(issueType,branchId,branchName,typeId,des,date,time) VALUES ('$_POST[issue]','$branch','$branchId','$_POST[typeId]','$_POST[des]','$date','$time')";
		if ((mysqli_query($con, $inscon))) {
			echo "<script>alert('New Issue Reported')</script>";
			echo "<script>setTimeout(\"location.href = 'reportIssue.php';\",150);</script>";
		} 
		else {
			echo "<script>alert('Cannot Report Issue!!, Try Again!')</script>";
			echo "<script>setTimeout(\"location.href = 'reportIssue.php';\",150);</script>";
		}
	}
	
    // ADD NEW BRANCH (@ viewBranchDetails.php)
    if (isset($_POST["submitBranch"])){
        // BRANCH TABLE
		$branchId = $_POST['branchId'];
		$branchName = $_POST['branchName'];
		$branchArea = $_POST['branchArea'];
		$addr = $_POST['branchAddress'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$pincode = $_POST['pincode'];
		$officeContact  = '';
		$branchManager  = '';
		$phone  = $_POST['phone'];
		$email = (isset($_POST['email'])) ? $_POST['email'] : "";
		$gst = $_POST['gst'];
		$latitude = '';
		$longitude = '';
		$url = (isset($_POST['url'])) ? $_POST['url'] : "";
		$Status = 1;
		$priceId = $_POST['priceId'];
		$grade = 'C';
		$openDate = $date;
		$closedate = '';
		$renewal_date = '0000-00-00';
		$renewal_status = 0;
		$ws_access = 0;
		
		// USER TABLE
		$type = 'Branch';
		$username = $_POST['branchId'];
		$branch = $_POST['branchId'];
		$temp = '';
		
		$inscon = "INSERT INTO branch(branchId,branchName,branchArea,addr,city,state,pincode,officeContact,branchManager,phone,email,gst,latitude,longitude,url,Status,priceId,grade,openDate,closeDate,renewal_date,renewal_status,ws_access,meet) VALUES ('$branchId','$branchName','$branchArea','$addr','$city','$state','$pincode','$officeContact','$branchManager','$phone','$email','$gst','$latitude','$longitude','$url','$Status','$priceId','$grade','$openDate','$closedate','$renewal_date','$renewal_status','$ws_access','');";
		
	    $inscon .="INSERT INTO users(type,username,password,employeeId,branch,agent,date,ip,language) VALUES ('$type','$username','$temp','$temp','$branch','$temp','$temp','$temp','$temp')";
		
		if(mysqli_multi_query($con,$inscon)){
			echo "<script>alert('New Branch Added')</script>";
			echo "<script>setTimeout(\"location.href = 'viewBranchDetails.php';\",150);</script>";
		}
		else{
			echo "<script>alert('Error Occurred')</script>";
			echo "<script>setTimeout(\"location.href = 'addBranch.php';\",150);</script>";
		}
    }




	if (isset($_POST["submitCase"])) {
	// BRANCH TABLE
	$name = $_POST['caseName'];
	$date = $_POST['caseDate'];
	$content = $_POST['caseContent'];

	$inscon = "INSERT INTO cases(name, date, content) VALUES ('$name','$date','$content');";

	if (mysqli_multi_query($con, $inscon)) {
		echo "<script>alert('New Case Added')</script>";
		echo "<script>setTimeout(\"location.href = 'case.php';\",150);</script>";
	} else {
		echo "<script>alert('Error Occurred')</script>";
		echo "<script>setTimeout(\"location.href = 'case.php';\",150);</script>";
	}
}



	
	// ADD GOLD RATE ( @ viewGoldRate.php )
	if(isset($_POST['submitGold'])){
		$type = $_POST['metal'];
		$user = $_POST['user'];
		$cash = $_POST['cash'];
		$transfer = $_POST['transfers'];
		if(isset($_POST['place']) && COUNT($_POST['place'])>0){
			$multiple = $_POST['place'];
			$sql = "";
			foreach($multiple as $place){
				$sql.="INSERT INTO gold(user,cash,transferRate,city,type,date,time) VALUES ('$user','$cash','$transfer','$place','$type','$date','$time');";
			}
			if(mysqli_multi_query($con,$sql)){
				header("location:viewGoldRate.php");
			}
			else{
				echo "<script type='text/javascript'>alert('ERROR OCCURED WHILE UPDATING THE RATE')</script>";
				echo "<script>setTimeout(\"location.href = 'viewGoldRate.php';\",150);</script>";
			}
		}
		else{
			echo "<script type='text/javascript'>alert('CHECK AT LEAST ONE PLACE')</script>";
			echo "<script>setTimeout(\"location.href = 'viewGoldRate.php';\",150);</script>";
		}
	}	
	
	if (isset($_POST["submitFund"])) {
		$type = $_POST['type2'];
		if ($type == 'By Cash') {
			$inscon = "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
			VALUES('$_POST[available]','$_POST[requested]','$_POST[type2]','$branch','','','','','','','Pending','$date','$time','','')";
		} 
		else if ($type == 'By Bank Transfer') {
			$inscon = "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
			VALUES('$_POST[available]','$_POST[requested]','$_POST[type2]','$branch','$_POST[account]','$_POST[holder]','$_POST[ifsc]','$_POST[branch]','$_POST[bank]','','Pending','$date','$time','','$_POST[mobile]')";
		} 
		else if ($type == 'By Cheque') {
			$inscon = "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
			VALUES('$_POST[available]','$_POST[requested]','$_POST[type2]','$branch','$_POST[number]','$_POST[chrequester]','','','','$_POST[date]','Pending','$date','$time','','')";
		} 
		else if ($type == 'To NBFC') {
			$inscon = "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
			VALUES('$_POST[available]','$_POST[requested]','$_POST[type2]','$branch','','','','$_POST[branch]','$_POST[bank]','','Pending','$date','$time','$_POST[requester]','$_POST[mob]')";
		}
		else {
			$inscon = "INSERT INTO fund(available,request,type,branch,number,holder,ifsc,bankBranch,bankName,chequeDate,status,date,time,customerName,customerMobile)
			VALUES('$_POST[available]','$_POST[requested]','$_POST[type2]','$branch','','','','','','','Pending','$date','$time','$_POST[requester]','$_POST[mob]')";
		}
		if ((mysqli_query($con, $inscon))) {
			echo "<script>setTimeout(\"location.href = 'requestFund.php';\",150);</script>";
		} 
		else {
			echo "<script>setTimeout(\"location.href = 'requestFund.php';\",150);</script>";
		}
	}
	
	if (isset($_POST["submitExpenses"])) {
		$employee = $_SESSION['employeeId'];
		
		if (file_exists($_FILES['file']['tmp_name'])) {
			$file = $_FILES['file']['name'];
			$file_loc = $_FILES['file']['tmp_name'];
			$file_size = $_FILES['file']['size'];
			$file_type = $_FILES['file']['type'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$folder = "ExpenseDocuments/";
			$new_size = $file_size / 1024;
			$new_file_name = strtolower($file);
			$filename = date('Ymdhis');
			$final_file = str_replace($new_file_name, $filename . $employee . 'EXP1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $final_file);
			} else {
			$final_file = '';
		}
		
		if (file_exists($_FILES['file1']['tmp_name'])) {
			$file1 = $_FILES['file1']['name'];
			$file_loc1 = $_FILES['file1']['tmp_name'];
			$file_size1 = $_FILES['file1']['size'];
			$file_type = $_FILES['file1']['type'];
			$file_extn1 = substr($file1, strrpos($file1, '.') - 1);
			$folder = "ExpenseDocuments/";
			$new_size = $file_size1 / 1024;
			$new_file_name1 = strtolower($file1);
			$filename1 = date('Ymdhis');
			$final_file1 = str_replace($new_file_name1, $filename1 . $employee . 'EXP2' . $file_extn1, $new_file_name1);
			move_uploaded_file($file_loc1, $folder . $final_file1);
			} else {
			$final_file1 = '';
		}
		
		$status = "Pending";
		$inscon = "INSERT INTO expense(branchCode,employeeId,particular,type,file,file1,amount,status,date,time,remarks)
		VALUES ('$branch','$employee','$_POST[particular]','$_POST[expense]','$final_file','$final_file1','$_POST[amount]','$status','$date','$time','')";
		if ((mysqli_query($con, $inscon))) {
			echo "<script>alert('Expenses added Successfully!')</script>";
			echo "<script>setTimeout(\"location.href = 'dailyExpenses.php';\",150);</script>";
			} else {
			echo "<script>alert('Error Storing Expense!')</script>";
			echo "<script>setTimeout(\"location.href = 'dailyExpenses.php';\",150);</script>";
		}
	}
	
    // DAILY CLOSING (@ dailyClosing.php)
	if (isset($_POST["submitClosing"])) 
	{
		$date      = isset($date) ? $date : date('Y-m-d');
		$time      = isset($time) ? $time : date('H:i:s');
		$branchId  = $_POST['branchId'] ?? ($_SESSION['branchCode'] ?? '');
	
		$totalAmount       = $_POST['totalamount'];
		$totalTransaction  = $_POST['totalTran'];
		$totalTranAmount   = $_POST['totalTranAmount'];
		$todayxpense       = $_POST['todaysExpense'];
		$balance           = $_POST['balance'];
		$grossWG           = $_POST['grossWeightG'];
		$netWG             = $_POST['netWeightG'];
		$grossAG           = $_POST['grossAmountG'];
		$netAG             = $_POST['netAmountG'];
		$grossWS           = $_POST['grossWeightS'];
		$netWS             = $_POST['netWeightS'];
		$grossAS           = $_POST['grossAmountS'];
		$netAS             = $_POST['netAmountS'];
		$open              = $_POST['open'];
		$for               = $_POST['ho'];
	
		$a  = $_POST['aa'];  $b  = $_POST['bb'];  $c  = $_POST['cc'];  $d  = $_POST['dd'];  $e  = $_POST['ee'];
		$f  = $_POST['ff'];  $g  = $_POST['gg'];  $h  = $_POST['hh'];  $i  = $_POST['ii'];  $j  = $_POST['jj'];
		$aa = $_POST['aaa']; $bb = $_POST['bbb']; $cc = $_POST['ccc']; $dd = $_POST['ddd']; $ee = $_POST['eee'];
		$ff = $_POST['fff']; $gg = $_POST['ggg']; $hh = $_POST['hhh']; $ii = $_POST['iii']; $jj = $_POST['jjj'];
	
		$total = $_POST['total'];
		$diff  = $_POST['diff'];
	
		$cheques_input     = isset($_POST['cheques']) ? trim($_POST['cheques']) : '';
		$cheques_original  = isset($_POST['cheques_original']) ? trim($_POST['cheques_original']) : '';
	
		if ($cheques_input === '') {
			if ($cheques_original !== '') {
				$cheques = (int)$cheques_original;
			} else {
				$lastRow = mysqli_fetch_assoc(mysqli_query(
					$con,
					"SELECT cheques FROM closing WHERE branchId='" . mysqli_real_escape_string($con, $branchId) . "'
					 ORDER BY date DESC, closingID DESC LIMIT 1"
				));
				$cheques = isset($lastRow['cheques']) && $lastRow['cheques'] !== null ? (int)$lastRow['cheques'] : 0;
			}
		} else {
			$cheques = (int)$cheques_input;
		}
	
		$inscon = "
		  INSERT INTO closing
			(date, time, totalAmount, transactions, transactionAmount, expenses, balance, cheques,
			 grossWG, netWG, grossAG, netAG, grossWS, netWS, grossAS, netAS,
			 one, two, three, four, five, six, seven, eight, nine, ten,
			 total, diff, branchId, open, forward)
		  VALUES
			(
			  '$date', '$time',
			  '$totalAmount', '$totalTransaction', '$totalTranAmount', '$todayxpense', '$balance', '$cheques',
			  '$grossWG', '$netWG', '$grossAG', '$netAG', '$grossWS', '$netWS', '$grossAS', '$netAS',
			  '$a', '$c', '$b', '$d', '$e', '$j', '$f', '$g', '$h', '$i',
			  '$total', '$diff', '".mysqli_real_escape_string($con, $branchId)."', '$open', '$for'
			)
		";
	
		$_SESSION['closing'] = $date;
	
		if (mysqli_query($con, $inscon)) {
			echo "<script>setTimeout(function(){ location.href='dailyClosing.php'; }, 150);</script>";
		} else {
			echo "<script type='text/javascript'>alert('ERROR STORING DATA')</script>";
			echo "<script>setTimeout(function(){ location.href='dailyClosing.php'; }, 150);</script>";
		}
	}
	
	
	if (isset($_POST["submitTransferFund"])) {
		$avai = $_POST['available'];
		$tra = $_POST['transfer'];
		$to = $_POST['to'];
		$inscon = "INSERT INTO trare(avai,transferAmount,branchTo,branchId,status,date,time) VALUES ('$avai','$tra','$to','$branch','Pending','$date','$time')";
		if ((mysqli_query($con, $inscon))) {
			echo "<script type='text/javascript'>alert('" . $tra . " Fund Transferred to " . $to . "')</script>";
			echo "<script>setTimeout(\"location.href = 'transferFund.php';\",150);</script>";
		} 
		else {
			echo "<script type='text/javascript'>alert('Cannot Transfer Funds')</script>";
			echo "<script>setTimeout(\"location.href = 'transferFund.php';\",150);</script>";
		}
	}
	
	if (isset($_POST["submitissue"])) {
		$inscon = "INSERT INTO issue(issueType,branchId,des,status,date,time) VALUES ('$_POST[issue]','$branch','$_POST[des]','Pending','$date','$time')";
		if ((mysqli_query($con, $inscon))) {
			echo "<script>alert('Ticket Raised Successfully')</script>";			
			echo "<script>setTimeout(\"location.href = 'reportIssue.php';\",150);</script>";
		}
		else {
			echo "<script>alert('Submit Error!')</script>";			
			echo "<script>setTimeout(\"location.href = 'reportIssue.php';\",150);</script>";
		}
	}
	
	if (isset($_POST["submitPass"])) {
		$user = $_POST['user'];
		$pass = $_POST['passw'];
		$confirm = $_POST['confirm'];
		if ($pass == $confirm) {
			$inscon = "update users set password='$pass' where username='$user'";
			if ((mysqli_query($con, $inscon))) {
				echo "<script>alert('Password changed Successfully!')</script>";
				echo "<script>setTimeout(\"location.href = 'logout.php';\",150);</script>";
			} 
			else {				
				echo "<script>alert('Password is not changed!')</script>";
				echo "<script>setTimeout(\"location.href = 'passReset.php';\",150);</script>";
			}
		}
	}
	
	// SEND EMAIL (@ compose.php)
	if (isset($_POST["sendEmail"])) {
		if(file_exists($_FILES['file']['tmp_name'])){
			$datetime = date('Ymdhis');
			$folder = "MailDocuments/";
			$file = $_FILES['file']['name'];
			$file_loc = $_FILES['file']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.')-1);
			$arr = explode(".", $file, 2);
			$file_name = str_replace(' ','',$arr[0]);
			$eFile = str_replace($file_name,$file_name.$datetime.'email'.$file_extn,$file_name);
			move_uploaded_file($file_loc,$folder.$eFile);
		}
		else{
			$eFile = '';
		}		
		
		$type = $_SESSION['usertype'];
		$fromBranch = $_POST['fromUser'];
		$inscon = "INSERT INTO mails(fromBranch,toBranch,subject,content,file,date,time,userType,flag) VALUES ('$fromBranch','$_POST[to]','$_POST[sub]','$_POST[content]','$eFile','$date','$time','$type','0')";
		if(mysqli_query($con,$inscon)){
			echo "<script type='text/javascript'>alert('Mail Sent!')</script>";
			echo "<script>setTimeout(\"location.href = 'inbox.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error Sending Mail, Resend Again!')</script>";
			echo "<script>setTimeout(\"location.href = 'compose.php';\",150);</script>";
		}
	}
	
    // ADD NEW EMPLOYEE DETAILS ( @ addEmployee.php )
	if (isset($_POST["submitemp"])) {
		$type = $_SESSION['usertype'];
		
		$empId = $_POST['empId'];
		$checkEmpl = mysqli_num_rows(mysqli_query($con,"SELECT empId FROM employee WHERE empId='$empId'"));
		
		if($checkEmpl == 0){
			$inscon = "INSERT INTO employee(empId,name,contact,designation,mailId,address,location,photo,rating) VALUES ('$_POST[empId]','$_POST[name]','$_POST[number]','$_POST[designation]','','','','', '0')";
			if (mysqli_query($con, $inscon)) {
				echo "<script>alert('Employee Added Successfully')</script>";			
				if ($type == 'Zonal' || $type == 'Master') {
					echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
				} 
				else if ($type == 'HR'){
					echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
				}
			} 
			else {
				echo "<script>alert('Submit Error!')</script>";
				if ($type == 'Zonal' || $type == 'Master') {
					echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
				} 
				else if ($type == 'HR' ){
					echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
				}
			}
		}
		else{
			echo "<script>alert('ERROR !!! EMPLOYEE ALREADY EXISTS!!!')</script>";
			if ($type == 'Zonal' || $type == 'Master') {
				echo "<script>setTimeout(\"location.href = 'assignBranch.php';\",150);</script>";
			} 
			else if ($type == 'HR'){
				echo "<script>setTimeout(\"location.href = 'addEmployee.php';\",150);</script>";
			}
		}
	}
	
	
	// FETCH BRANCH DATA ( @ customer_registration.php ) 
	if(isset($_POST["fetch_branches"]) && $_POST["fetch_branches"]=="branch_data"){	
	
		$state = $_POST['state'];
		
		$response='';
		$branchData = [];
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE state IN ('$state') and Status=1 ORDER BY branchName");
		while($row = mysqli_fetch_assoc($branchSQL)){
			$branchData[] = $row;
		}
			$response.='<option selected="true" disabled="disabled" value="">Select Branch</option>';
		foreach ($branchData as $key=>$value) {
			$response.= '<option  value="' . $value['branchId'] . '">' . $value['branchName'] . '</option>';
		}
		echo $response;
		
	}
	
	// FETCH BRANCH QR CODE ( @ customer_QR_scan.php ) 
	if(isset($_POST["fetch_qr_code"]) && $_POST["fetch_qr_code"]=="branch_QR"){	
		//$branch = $_POST['branch'];
		$branch = base64_encode($_POST['branch']);
		include('libs/phpqrcode/qrlib.php');
		$tempDir = 'temp/';
		require 'Config/QRClink.php';
		$unique_id=rand();
		$codeContents = QRCodeURL . 'customer_registration.php?branch=' . urlencode($branch);
		$filename = $unique_id . $branch . date('Ymdhis');
		QRcode::png($codeContents, $tempDir . '' . $filename . '.png', QR_ECLEVEL_L, 5);
		$response = '<img src="temp/' . @$filename . '.png" style="width:300px; height:300px;">';
		
		echo $response;
		
	}	

	if (isset($_POST["submitTask"])) {
	// BRANCH TABLE
	$branchId = $_POST['branchId'];
	$remark = $_POST['remark'];
	$priority = $_POST['priority'];
	$date = $date;

	$inscon = "INSERT INTO task(branchId,remark,priority,date) VALUES ('$branchId','$remark','$priority','$date');";

	if (mysqli_multi_query($con, $inscon)) {
		echo "<script>alert('New Task Added')</script>";
		echo "<script>setTimeout(\"location.href = 'task.php';\",150);</script>";
	} else {
		echo "<script>alert('Error Occurred')</script>";
		echo "<script>setTimeout(\"location.href = 'task.php';\",150);</script>";
	}
}
