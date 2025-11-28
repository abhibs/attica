<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$zonalId = $_POST['zonalId'] ?? '';
$role    = $_POST['role']    ?? '';   // 'Zonal' | 'IMPS' | 'Master'
$lastId  = isset($_POST['last_id']) ? (int)$_POST['last_id'] : 0;
$impsId  = $_POST['imps_id'] ?? '';

if ($zonalId === '' || $role === '') {
    echo json_encode(['success' => false, 'error' => 'Invalid params']);
    exit;
}

$zonalIdEsc = mysqli_real_escape_string($con, $zonalId);
$impsFilter = '';

if ($impsId !== '') {
    $impsEsc   = mysqli_real_escape_string($con, $impsId);
    $impsFilter = " AND imps_id = '$impsEsc' ";
}

/*
 * Zonal viewed messages:
 *   mark seen_zonal = 1 for messages coming FROM IMPS or Master
 *   only for this IMPS conversation (imps_id)
 *
 * IMPS / Master viewed messages:
 *   mark seen_imps = 1 for messages coming FROM Zonal
 *   only for this IMPS conversation (imps_id)
 */

if ($role === 'Zonal') {
    $sql = "
        UPDATE zonal_imps_chat
        SET seen_zonal = 1
        WHERE zonalId = '$zonalIdEsc'
          AND sender_type IN ('IMPS','Master')
          AND seen_zonal = 0
          $impsFilter
          " . ($lastId > 0 ? "AND id <= $lastId" : "") . "
    ";
} else {
    // role = 'IMPS' or 'Master'
    $sql = "
        UPDATE zonal_imps_chat
        SET seen_imps = 1
        WHERE zonalId = '$zonalIdEsc'
          AND sender_type = 'Zonal'
          AND seen_imps = 0
          $impsFilter
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

