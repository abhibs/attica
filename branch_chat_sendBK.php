<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include("dbConnection.php");

header('Content-Type: application/json');

/*
 * NOTE: Real upload limits come from php.ini (upload_max_filesize, post_max_size).
 */
@ini_set('upload_max_filesize', '16M');
@ini_set('post_max_size',       '16M');
@ini_set('max_execution_time',  '120');
@ini_set('max_input_time',      '120');

$branchId = $_POST['branchId'] ?? '';
$message  = trim($_POST['message'] ?? '');

if ($branchId === '') {
    echo json_encode(['success' => false, 'error' => 'Missing branchId']);
    exit;
}

/* ------------------ Figure out who is sending ------------------ */
$usertype   = $_SESSION['usertype']        ?? '';
$branchCode = $_SESSION['branchCode']      ?? '';
$loginUser  = $_SESSION['login_username']  ?? ''; // atticamaster or empId

if ($usertype === 'Branch') {
    // Branch side user
    $sender_type = 'Branch';
    $sender_id   = $branchCode ?: $branchId;      // e.g. AGPL001
} else {
    // Any HO / center side user
    $sender_type = 'Center';
    $sender_id   = $loginUser ?: 'Center';        // atticamaster or 1002063
}

/* ------------------ IMAGE UPLOAD HANDLING ------------------ */
$message_type  = 'text';
$imageFileName = null;

// soft limit for a single image (8MB). Change if you want larger.
$MAX_IMAGE_SIZE = 8 * 1024 * 1024; // 8 MB

if (
    isset($_FILES['image']) &&
    is_array($_FILES['image']) &&
    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE   // a file was actually selected
) {
    $fileError = $_FILES['image']['error'];

    // If PHP ini limits are hit, we get INI_SIZE / FORM_SIZE
    if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
        echo json_encode([
            'success' => false,
            'error'   => 'Image too large per server limit (upload_max_filesize/post_max_size).'
        ]);
        exit;
    }

    // Any other upload error
    if ($fileError !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'error'   => 'Image upload error code: ' . $fileError
        ]);
        exit;
    }

    // Our own size check
    if ($_FILES['image']['size'] > $MAX_IMAGE_SIZE) {
        echo json_encode([
            'success' => false,
            'error'   => 'Image too large. Max size is 8MB.'
        ]);
        exit;
    }

    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $origName   = $_FILES['image']['name'];
    $tmpPath    = $_FILES['image']['tmp_name'];
    $ext        = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        echo json_encode([
            'success' => false,
            'error'   => 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.'
        ]);
        exit;
    }

    // Ensure folder exists
    $uploadDir = __DIR__ . '/ChatImages/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $imageFileName = date('YmdHis') . '_chat_' . mt_rand(1000, 9999) . '.' . $ext;
    $destFull      = $uploadDir . $imageFileName;

    if (!move_uploaded_file($tmpPath, $destFull)) {
        echo json_encode([
            'success' => false,
            'error'   => 'Failed to save image on server.'
        ]);
        exit;
    }

    // Upload success
    $message_type = 'image';
}

/* ------------------ Nothing to send? ------------------ */
if ($message_type === 'text' && $message === '') {
    echo json_encode(['success' => false, 'error' => 'Nothing to send']);
    exit;
}

/* ------------------ Insert into DB (created_at in IST) ------------------ */
$branchIdEsc   = mysqli_real_escape_string($con, $branchId);
$msgEsc        = mysqli_real_escape_string($con, $message);
$senderTypeEsc = mysqli_real_escape_string($con, $sender_type);
$senderIdEsc   = mysqli_real_escape_string($con, $sender_id);
$typeEsc       = mysqli_real_escape_string($con, $message_type);
$createdAtIst  = mysqli_real_escape_string($con, date('Y-m-d H:i:s'));

$imageValueSql = $imageFileName
    ? "'" . mysqli_real_escape_string($con, $imageFileName) . "'"
    : "NULL";

$sql = "
    INSERT INTO branch_chat
        (branchId, sender_type, sender_id, message_type, message, image_file, created_at)
    VALUES
        ('$branchIdEsc', '$senderTypeEsc', '$senderIdEsc', '$typeEsc', '$msgEsc', $imageValueSql, '$createdAtIst')
";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error'   => 'DB error: ' . mysqli_error($con)
    ]);
}

