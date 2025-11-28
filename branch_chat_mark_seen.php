<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$branchId = $_POST['branchId'] ?? '';
$role     = $_POST['role']     ?? ''; // 'Center' or 'Branch'
$lastId   = isset($_POST['last_id']) ? (int)$_POST['last_id'] : 0;

if ($branchId === '' || ($role !== 'Center' && $role !== 'Branch')) {
    echo json_encode(['success' => false, 'error' => 'Invalid params']);
    exit;
}

$branchIdEsc = mysqli_real_escape_string($con, $branchId);

if ($role === 'Center') {
    // messages from Branch that HO has now seen
    $sql = "
        UPDATE branch_chat
        SET seen_center = 1
        WHERE branchId = '$branchIdEsc'
          AND sender_type = 'Branch'
          AND seen_center = 0
          " . ($lastId > 0 ? "AND id <= $lastId" : "") . "
    ";
} else {
    // messages from Center that Branch has now seen
    $sql = "
        UPDATE branch_chat
        SET seen_branch = 1
        WHERE branchId = '$branchIdEsc'
          AND sender_type = 'Center'
          AND seen_branch = 0
          " . ($lastId > 0 ? "AND id <= $lastId" : "") . "
    ";
}

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error'   => 'DB error: ' . mysqli_error($con)
    ]);
}

