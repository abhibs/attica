<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$branchId = $_GET['branchId'] ?? '';
if ($branchId === '') {
    echo json_encode(['success' => false, 'error' => 'Missing branchId']);
    exit;
}

$role = $_GET['role'] ?? '';
if ($role === 'Branch') {
    $role = 'Branch';
} elseif ($role === 'Center') {
    $role = 'Center';
} else {
    $role = '';
}

$branchIdEsc = mysqli_real_escape_string($con, $branchId);

/*
 * Fetch messages for a branch. Ordered oldest → newest.
 * Time and date converted from UTC (stored) to IST for display.
 */
$sql = "
    SELECT *
    FROM (
        SELECT
            c.id,
            c.sender_type,
            c.sender_id,
            c.message_type,
            c.message,
            c.image_file,
            DATE_FORMAT(c.created_at, '%d-%m %H:%i') AS time,
            DATE_FORMAT(c.created_at, '%d-%m-%Y')     AS day,
            CASE 
                WHEN c.sender_type = 'Branch' THEN
                    CONCAT(COALESCE(b.branchName, c.sender_id), ' (', c.sender_id, ')')
                WHEN c.sender_type = 'Center' THEN
                    CASE 
                        WHEN c.sender_id = 'atticamaster' THEN 'Attica Master'
                        ELSE COALESCE(e.name, c.sender_id)
                    END
                ELSE c.sender_id
            END AS sender_label
        FROM branch_chat c
        LEFT JOIN branch   b ON c.sender_id = b.branchId
        LEFT JOIN employee e ON c.sender_id = e.empId
        WHERE c.branchId = '$branchIdEsc'
        ORDER BY c.id DESC
        LIMIT 200
    ) AS recent
    ORDER BY recent.id ASC
";

$res = mysqli_query($con, $sql);
$messages = [];

while ($row = mysqli_fetch_assoc($res)) {
    $imageUrl = null;
    if (!empty($row['image_file'])) {
        $imageUrl = 'ChatImages/' . $row['image_file'];
    }

    $messages[] = [
        'id'           => (int)$row['id'],
        'sender_type'  => $row['sender_type'],
        'sender_id'    => $row['sender_id'],
        'sender_label' => $row['sender_label'],
        'message_type' => $row['message_type'],
        'message'      => $row['message'],
        'image_file'   => $row['image_file'],
        'image_url'    => $imageUrl,
        'time'         => $row['time'],
        'day'          => $row['day'], 
    ];
}

$seenUpto = 0;

if ($role !== '') {
    $oppositeRole = ($role === 'Branch') ? 'Center' : 'Branch';
    $oppositeRoleEsc = mysqli_real_escape_string($con, $oppositeRole);

    $qSeen = mysqli_query(
        $con,
        "SELECT last_seen_id 
         FROM branch_chat_seen 
         WHERE branchId = '$branchIdEsc' AND role = '$oppositeRoleEsc'
         LIMIT 1"
    );

    if ($qSeen && mysqli_num_rows($qSeen) > 0) {
        $rowSeen  = mysqli_fetch_assoc($qSeen);
        $seenUpto = (int)$rowSeen['last_seen_id'];
    }
}

echo json_encode([
    'success'   => true,
    'messages'  => $messages,
    'seen_upto' => $seenUpto
]);

