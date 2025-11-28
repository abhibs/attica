<?php
session_start();
header('Content-Type: application/json');

include("dbConnection.php");

if (!$con) {
    echo json_encode([
        'success'      => false,
        'error'        => 'DB connection error',
        'total_unread' => 0
    ]);
    exit;
}

$usertype = $_SESSION['usertype']       ?? '';
$login    = $_SESSION['login_username'] ?? '';
$empId    = $_SESSION['employeeId']     ?? '';

/**
 * Only “center” roles see a global Branch Chat total.
 * (Add/remove roles here as needed.)
 */
$centerRoles = [
    'Master',
    'SubZonal',
    'Zonal',
    'Accounts',
    'Accounts IMPS',
    'AccHead'
];

if (!in_array($usertype, $centerRoles, true)) {
    echo json_encode([
        'success'      => false,
        'error'        => 'Not allowed',
        'total_unread' => 0
    ]);
    exit;
}

/**
 * Base condition:
 *  - messages sent FROM branches
 *  - not yet seen on Center side
 */
$where = "sender_type = 'Branch' AND seen_center = 0";

/**
 * OPTIONAL: if you want the Zonal to see ONLY their assigned branches,
 *           add a filter here using your actual schema.
 *
 * Example assumes `branch` table has a `zonalIncharge` = employeeId:
 */
if ($usertype === 'SubZonal' || $usertype === 'Zonal') {
    $zonalId = $empId ?: $login;
    if ($zonalId !== '') {
        $zonalIdEsc = mysqli_real_escape_string($con, $zonalId);
        $where .= "
            AND branchId IN (
                SELECT branchId
                FROM branch
                WHERE ezviz_vc = '$zonalIdEsc'
            )
        ";
    }
}

/* ---- Final query: total unread from all (assigned) branches ---- */
$sql = "
    SELECT COUNT(*) AS unread_total
    FROM branch_chat
    WHERE $where
";

$res = mysqli_query($con, $sql);
if (!$res) {
    echo json_encode([
        'success'      => false,
        'error'        => 'DB error: ' . mysqli_error($con),
        'total_unread' => 0
    ]);
    exit;
}

$row         = mysqli_fetch_assoc($res);
$totalUnread = (int)($row['unread_total'] ?? 0);

echo json_encode([
    'success'      => true,
    'total_unread' => $totalUnread
]);
