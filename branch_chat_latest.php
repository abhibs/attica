<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$branchId  = $_GET['branchId'] ?? '';
$lastSeen  = isset($_GET['last_seen']) ? (int)$_GET['last_seen'] : 0;

if ($branchId === '') {
    echo json_encode(['success' => false, 'error' => 'Missing branchId']);
    exit;
}

$branchIdEsc = mysqli_real_escape_string($con, $branchId);

/* ---------- 1) Get the latest message (for info) ---------- */
$sqlLatest = "
    SELECT
        c.id,
        c.sender_type,
        c.sender_id,
        c.message_type,
        c.message,
        c.image_file,
        DATE_FORMAT(c.created_at, '%d-%m %H:%i') AS time,
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
    LIMIT 1
";

$resLatest = mysqli_query($con, $sqlLatest);
$latest = null;

if ($row = mysqli_fetch_assoc($resLatest)) {
    $latestId = (int)$row['id'];
    $imageUrl = !empty($row['image_file']) ? 'ChatImages/'.$row['image_file'] : null;

    $latest = [
        'id'           => $latestId,
        'sender_type'  => $row['sender_type'],
        'sender_id'    => $row['sender_id'],
        'sender_label' => $row['sender_label'],
        'message_type' => $row['message_type'],
        'message'      => $row['message'],
        'image_file'   => $row['image_file'],
        'image_url'    => $imageUrl,
        'time'         => $row['time']
    ];
}

/* ---------- 2) Count unread messages using seen flags ---------- */
$role = $_GET['role'] ?? 'Center';  // default HO side

if ($role === 'Branch') {
    // Branch sees unread messages sent FROM Center
    $unreadWhere = "sender_type = 'Center' AND seen_branch = 0";
} else {
    // HO/Center sees unread messages sent FROM Branch
    $unreadWhere = "sender_type = 'Branch' AND seen_center = 0";
}

$sqlUnread = "
    SELECT COUNT(*) AS unreadCount
    FROM branch_chat
    WHERE branchId = '$branchIdEsc'
      AND $unreadWhere
";

$resUnread   = mysqli_query($con, $sqlUnread);
$rowUnread   = mysqli_fetch_assoc($resUnread);
$unreadCount = (int)($rowUnread['unreadCount'] ?? 0);

/* ---------- Final Response ---------- */
echo json_encode([
    'success'      => true,
    'latest'       => $latest,
    'unread_count' => $unreadCount
]);

