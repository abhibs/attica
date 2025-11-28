<?php
session_start();
require_once 'dbConnection.php';

// Only Master or HR can delete
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Master','HR'])) {
    echo 'error';
    exit;
}

$empId = $_POST['delete_empId'] ?? '';
$date  = $_POST['delete_date']  ?? '';

if ($empId === '' || $date === '') {
    echo 'error';
    exit;
}

// Basic sanitization
$empId = mysqli_real_escape_string($con, $empId);
$date  = mysqli_real_escape_string($con, $date);

// Delete – adjust WHERE if you have a unique id column
$sql = "DELETE FROM attendance WHERE empId = '$empId' AND date = '$date' AND status = 1";

if (mysqli_query($con, $sql)) {
    echo 'success';
} else {
    echo 'error';
}

