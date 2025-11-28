<?php
session_start();
header('Content-Type: application/json');
require_once 'dbConnection.php';

$branchId = $_GET['branchId'] ?? '';
$contact  = $_GET['contact']  ?? '';
$role     = $_GET['role']     ?? '';

$branchId = mysqli_real_escape_string($con, trim($branchId));
$contact  = mysqli_real_escape_string($con, trim($contact));
$role     = trim($role);

if ($branchId === '' || $contact === '' || $role === '') {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

if ($role === 'Center') {
    // unread for Center (messages sent by Branch)
    $sql = "
        SELECT COUNT(*) AS c
        FROM customer_chat
        WHERE branch_id        = '$branchId'
          AND customer_contact = '$contact'
          AND sender_type      = 'Branch'
          AND is_read_center   = 0
    ";
} else {
    // unread for Branch (messages sent by Center / HO / others)
    $sql = "
        SELECT COUNT(*) AS c
        FROM customer_chat
        WHERE branch_id        = '$branchId'
          AND customer_contact = '$contact'
          AND sender_type     <> 'Branch'
          AND is_read_branch   = 0
    ";
}

$res = mysqli_query($con, $sql);
$row = $res ? mysqli_fetch_assoc($res) : ['c' => 0];
$unread = (int)($row['c'] ?? 0);

echo json_encode([
    'success'      => true,
    'unread_count' => $unread
]);

