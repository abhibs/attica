<?php
session_start();
include("dbConnection.php");
date_default_timezone_set('Asia/Calcutta');

$date = date('Y-m-d');
$time = date("H:i:s");

// identify where the request came from
// doorstep.php sends source = doorstep (we'll add this in step 2)
$source   = isset($_POST['source']) ? trim($_POST['source']) : '';
$isDoorstep = ($source === 'doorstep');

if (
    isset($_POST['ecID']) && $_POST['ecID'] !== '' &&
    isset($_POST['quotationImage']) && $_POST['quotationImage'] !== ''
) {
    $id = (int)$_POST['ecID'];

    // decode base64 image
    $imgStr = $_POST['quotationImage'];
    $image_parts = explode(";base64,", $imgStr);
    if (count($image_parts) !== 2) {
        echo "<script>alert('INVALID IMAGE DATA')</script>";
        $redirect = $isDoorstep ? 'doorstep.php' : 'xeveryCustomer.php';
        echo "<script>setTimeout(\"location.href = '$redirect';\",150);</script>";
        exit;
    }

    $image_base64 = base64_decode($image_parts[1]);
    $quotationFile = date('YmdHis') . uniqid() . '.png';
    $file = 'QuotationImage/' . $quotationFile;
    file_put_contents($file, $image_base64);

    // payload stored in DB (JSON)
    $data = [];
    $data['image']  = $quotationFile;
    $data['status'] = 1;
    $data['rate']   = isset($_POST['givenRate']) ? $_POST['givenRate'] : '';
    $encoded = mysqli_real_escape_string($con, json_encode($data));

    // 🔀 Decide table + redirect based on source
    if ($isDoorstep) {
        // HO Doorstep → release_requests
        $sql      = "UPDATE release_requests SET quotation='$encoded' WHERE id='$id'";
        $redirect = "doorstep.php";
    } else {
        // Old/Branch flow → everycustomer
        $sql      = "UPDATE everycustomer SET quotation='$encoded' WHERE Id='$id'";
        $redirect = "xeveryCustomer.php";
    }

    if (mysqli_query($con, $sql)) {
        header("Location: $redirect");
        exit;
    } else {
        echo "<script>alert('ERROR OCCURRED , PLEASE TRY AGAIN')</script>";
        echo "<script>setTimeout(\"location.href = '$redirect';\",150);</script>";
    }
}

/* ----------------- OLD QUOTATION + ENQUIRY FLOW (kept as-is) ----------------- */
/* This block is for the older branch workflow and still writes to walkin + everyCustomer.
   Doorstep.php does NOT use this path, so you can leave it unchanged. */

else if (isset($_POST['quotationAndEnquiry'])) {
    $rowId = $_POST['rowId'];
    $customerData = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT customer, contact FROM everyCustomer WHERE Id='$rowId'")
    );

    $name   = $customerData['customer'];
    $mobile = $customerData['contact'];
    $gold   = $_POST['gold'];
    $having = $_POST['having'];
    $metal  = $_POST['metal'];
    $grossW = $_POST['grossW'];
    $netW   = $_POST['netW'];
    $purity = $_POST['purity'];
    $branchId = $_SESSION['branchCode'];
    $remarks  = $_POST['remarks'];
    $rate     = $_POST['rate'];
    $releaseAmount = isset($_POST['releaseAmount']) ? $_POST['releaseAmount'] : "";

    $image_parts = explode(";base64,", $_POST['quotationImage']);
    $image_base64 = base64_decode($image_parts[1]);
    $quotationFile = $branchId . date('YmdHis') . uniqid() . '.png';
    $file = 'QuotationImage/' . $quotationFile;
    file_put_contents($file, $image_base64);

    $sql  = "INSERT INTO walkin(name, mobile, gold, havingG, metal, issue, gwt, nwt, purity, ramt, branchId, agent_id, followUp, comment, remarks, zonal_remarks, status, emp_type, date, indate, time, quotation, bills, quot_rate)
             VALUES('$name', '$mobile', '$gold', '$having', '$metal', '', '$grossW', '$netW', '$purity', '$releaseAmount', '$branchId', '', '', '', '$remarks', '', '', '', '$date', '', '$time', '$quotationFile', '0', '$rate');";

    $sql .= "UPDATE everyCustomer SET status='Enquiry', quotation='$quotationFile' WHERE Id='$rowId'";

    $response = [];
    if (mysqli_multi_query($con, $sql)) {
        $response = ["status" => "successful"];
    } else {
        $response = ["status" => "error"];
    }
    echo json_encode($response);
}

else {
    $redirect = $isDoorstep ? 'doorstep.php' : 'xeveryCustomer.php';
    echo "<script>alert('BROWSER ERROR !!!')</script>";
    echo "<script>setTimeout(\"location.href = '$redirect';\",150);</script>";
}
?>

