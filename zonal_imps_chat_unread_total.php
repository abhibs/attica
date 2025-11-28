<?php
session_start();
header('Content-Type: application/json');
require_once 'dbConnection.php';

$usertype  = $_SESSION['usertype']       ?? '';
$sessLogin = $_SESSION['login_username'] ?? '';
$sessEmp   = $_SESSION['employeeId']     ?? '';

$total = 0;

if ($usertype === 'Accounts IMPS' || $usertype === 'AccHead') {

    // Resolve canonical IMPS employeeId (same pattern as in *_send.php)
    $impsEmployeeId = '';

    if ($sessEmp !== '') {
        $impsEmployeeId = $sessEmp;
    } else {
        $loginEsc = mysqli_real_escape_string($con, $sessLogin);
        $q = mysqli_query(
            $con,
            "SELECT employeeId 
             FROM users 
             WHERE username = '$loginEsc' OR employeeId = '$loginEsc'
             LIMIT 1"
        );
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $impsEmployeeId = $row['employeeId'] ?? '';
        }
    }

    if ($impsEmployeeId === '') {
        echo json_encode(['success' => false, 'total_unread' => 0]);
        exit;
    }

    $impsEsc = mysqli_real_escape_string($con, $impsEmployeeId);

    $sql = "
        SELECT COUNT(*) AS unread_total
        FROM zonal_imps_chat
        WHERE sender_type = 'Zonal'
          AND seen_imps   = 0
          AND imps_id     = '$impsEsc'
    ";

} elseif ($usertype === 'Master') {

    // Master sees aggregate unread from all Zonals (no per-IMPS split)
    $sql = "
        SELECT COUNT(*) AS unread_total
        FROM zonal_imps_chat
        WHERE sender_type = 'Zonal'
          AND seen_imps   = 0
    ";

} elseif ($usertype === 'SubZonal') {

    // Zonal: unread from IMPS / Master, but only for valid IMPS/AccHead users
    $zonalId = $sessEmp;
    if ($zonalId === '') {
        echo json_encode(['success' => false, 'total_unread' => 0]);
        exit;
    }

    $zonalEsc = mysqli_real_escape_string($con, $zonalId);

    $sql = "
        SELECT COUNT(*) AS unread_total
        FROM zonal_imps_chat c
        WHERE c.zonalId     = '$zonalEsc'
          AND c.sender_type IN ('IMPS','Master')
          AND c.seen_zonal  = 0
          AND EXISTS (
              SELECT 1
              FROM users u
              WHERE u.type IN ('Accounts IMPS','AccHead')
                AND (
                    u.employeeId = c.imps_id
                    OR u.username = c.imps_id
                )
          )
    ";


} else {
    // No Zonal ↔ IMPS chat for this role
    echo json_encode(['success' => false, 'total_unread' => 0]);
    exit;
}

$res = mysqli_query($con, $sql);
if ($res) {
    $row   = mysqli_fetch_assoc($res);
    $total = (int)($row['unread_total'] ?? 0);

    echo json_encode([
        'success'      => true,
        'total_unread' => $total
    ]);
} else {
    echo json_encode([
        'success'      => false,
        'total_unread' => 0,
        'error'        => mysqli_error($con)
    ]);
}

