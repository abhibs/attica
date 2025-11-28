<?php
session_start();
header('Content-Type: application/json');
require_once 'dbConnection.php';

$branchId = $_GET['branchId'] ?? '';
$contact  = $_GET['contact']  ?? '';

$branchId = mysqli_real_escape_string($con, trim($branchId));
$contact  = mysqli_real_escape_string($con, trim($contact));

if ($branchId === '' || $contact === '') {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$type = $_SESSION['usertype'] ?? '';
$role = ($type === 'Branch') ? 'Branch' : 'Center';

/* Mark messages as read for the viewer */
if ($role === 'Center') {
    // HO / SubZonal reading branch messages
    $upd = "
        UPDATE customer_chat
        SET is_read_center = 1
        WHERE branch_id        = '$branchId'
          AND customer_contact = '$contact'
          AND sender_type      = 'Branch'
          AND is_read_center   = 0
    ";
    mysqli_query($con, $upd);
} else {
    // Branch reading HO / Center messages
    $upd = "
        UPDATE customer_chat
        SET is_read_branch = 1
        WHERE branch_id        = '$branchId'
          AND customer_contact = '$contact'
          AND sender_type     <> 'Branch'
          AND is_read_branch   = 0
    ";
    mysqli_query($con, $upd);
}

/* Fetch messages */
$sql = "
    SELECT
        id,
        branch_id,
        customer_name,
        customer_contact,
        sender_type,
        sender_id,
        message_type,
        message,
        image_file,
        DATE_FORMAT(created_at, '%d-%m %H:%i')  AS time,
        DATE_FORMAT(created_at, '%d-%m-%Y')     AS day
    FROM customer_chat
    WHERE branch_id        = '$branchId'
      AND customer_contact = '$contact'
    ORDER BY id ASC
";
$res = mysqli_query($con, $sql);

$messages  = [];
$uploadDir = 'ChatUploads'; // change if your chat images folder name is different

while ($row = mysqli_fetch_assoc($res)) {
    $imageUrl = '';
    if (!empty($row['image_file'])) {
        $imageUrl = $uploadDir . '/' . $row['image_file'];
    }

    $messages[] = [
        'id'           => (int)$row['id'],
        'sender_type'  => $row['sender_type'],
        'sender_id'    => $row['sender_id'],
        'sender_label' => $row['sender_id'],  // you can improve with employee name if you want
        'message_type' => $row['message_type'],
        'message'      => $row['message'],
        'image_url'    => $imageUrl,
        'time'         => $row['time'],
        'day'          => $row['day'],
    ];
}

echo json_encode([
    'success'  => true,
    'messages' => $messages
]);

