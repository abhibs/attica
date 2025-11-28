<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");

$branchId = $_SESSION['branchCode'];
$invoiceNumber = $_SESSION['invoiceNumber']; 
$employeeId = $_SESSION['employeeId'];

$id = $_POST['id'];

$sql = mysqli_fetch_assoc(mysqli_query($con, "SELECT empId,name FROM employee WHERE empId='$employeeId'"));

$empId = $sql['empId'];
$empName = $sql['name'];
$name = $_POST['name'];
$contact = $_POST['contact'];
$adres = $_POST['adres'];
$city = $_POST['city'];
$pin = $_POST['pin'];

$grossWeight = $_POST['totalGrossW'];
$NetWeight = $_POST['totalNetW'];
$stoneWeight = $grossWeight - $NetWeight;

$amount= $_POST['payableA'];
$rate = $_POST['intrest'];
$rateAmount = $_POST['intrestrate'];
 
$date = date('Y-m-d');
$time = date("h:i:s");
    
$lastBillIdQuery = "SELECT MAX(id) AS lastId FROM pledge_bill";
$lastBillIdResult = mysqli_query($con, $lastBillIdQuery);
$lastBillIdRow = mysqli_fetch_assoc($lastBillIdResult);
$lastBillId = $lastBillIdRow['lastId'];
$newBillId = ($lastBillId === null) ? 110000 : $lastBillId;
$bill=11000;
$billId = $bill.$newBillId + 1;

if (file_exists($_FILES['kyc1']['tmp_name'])) {
			$kyc1 = $_FILES['kyc1']['name'];
			$file_loc = $_FILES['kyc1']['tmp_name'];
			$file_size = $_FILES['kyc1']['size'];
			$file_type = $_FILES['kyc1']['type'];
			$file_extn = substr($kyc1, strrpos($kyc1, '.') - 1);
			$folder = "PledgeDocuments/";
			$new_size = $file_size / 1024;
			$new_file_name = strtolower($kyc1);
			$filename = date('Ymdhis');
			$final_file = str_replace($new_file_name, $filename . 'kyc1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folder . $final_file);
			} else {
			$final_file = '';
		}
		
		if (file_exists($_FILES['kyc2']['tmp_name'])) {
			$kyc2 = $_FILES['kyc2']['name'];
			$file_loc1 = $_FILES['kyc2']['tmp_name'];
			$file_size1 = $_FILES['kyc2']['size'];
			$file_type = $_FILES['kyc2']['type'];
			$file_extn1 = substr($kyc2, strrpos($kyc2, '.') - 1);
			$folder = "PledgeDocuments/";
			$new_size = $file_size1 / 1024;
			$new_file_name1 = strtolower($kyc2);
			$filename1 = date('Ymdhis');
			$final_file1 = str_replace($new_file_name1, $filename1 . 'kyc2' . $file_extn1, $new_file_name1);
			move_uploaded_file($file_loc1, $folder . $final_file1);
			} else {
			$final_file1 = '';
		}
		
		
			if(isset($_POST['image']) && !empty($_POST['image'])){
			$cphoto = $_POST['image'];
			$date = date('Ymd');
			$folderPath = "PledgeDocuments/";
			$image_parts = explode(";base64,", $cphoto);	
			$image_base64 = base64_decode($image_parts[1]);
			$fName = $billId.$date. '.jpg';
			$fName1 = $billId.$date. '.jpg';
			$files = $folderPath . $fName;
			file_put_contents($files, $image_base64);
		}

		else{
			$fName1 = '';
		}
	
			if(isset($_POST['image1']) && !empty($_POST['image1'])){
			$ophoto = $_POST['image1'];
			$datetime = date('Ymdhis');
			$folderPath = "PledgeDocuments/";
			$image_parts = explode(";base64,", $ophoto);	
			$image_base64 = base64_decode($image_parts[1]);
			$oName = $billId.$datetime. '.jpg';
			$oName1 = $billId.$datetime. '.jpg';
			$files = $folderPath . $oName;
			file_put_contents($files, $image_base64);
		}

		else{
			$oName1 = '';
		}

   $insertQuery = "INSERT INTO pledge_bill (billId,invoiceId, name, contact, address, city, pincode,kyc1,kyc2,customerImage,ornamentImage, grossW, stoneW, amount, rate,rateAmount, branchId, empId, empName, status, date, time) 
                    VALUES ('$billId','$invoiceNumber', '$name', '$contact', '$adres', '$city', '$pin', '$final_file','$final_file1','$fName1','$oName1','$grossWeight', '$stoneWeight',' $amount', '$rate', '$rateAmount', '$branchId', '$empId', '$empName', 'Billed', '$date', '$time');";

	$upadateQuerry = "UPDATE everycustomer SET status='Pledged' WHERE id='$id'";
	
	
if (mysqli_query($con, $insertQuery) && mysqli_query($con, $upadateQuerry)) {
   
    unset($_SESSION['invoiceNumber']);    
     echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error inserting Data!'));
    }

?>
