<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include("dbConnection.php");

header('Content-Type: application/json');

/* --- PHP limits for upload --- */
@ini_set('upload_max_filesize', '16M');
@ini_set('post_max_size',       '16M');
@ini_set('max_execution_time',  '120');
@ini_set('max_input_time',      '120');

/* ---------------- INPUTS ---------------- */
$zonalIdPost = trim($_POST['zonalId'] ?? '');
$impsIdPost  = trim($_POST['impsId']  ?? '');
$message     = trim($_POST['message'] ?? '');

/* ---------------- SESSION / ROLE ---------------- */
$usertype    = $_SESSION['usertype']       ?? '';   // 'SubZonal' | 'Accounts IMPS' | 'AccHead' | 'Master' | ...
$sessLogin   = $_SESSION['login_username'] ?? '';
$sessEmpId   = $_SESSION['employeeId']     ?? '';

$sender_type = '';
$sender_id   = '';
$zonalId     = '';
$impsId      = '';

/* =====================================================
   RESOLVE ZONAL / IMPS IDs + SENDER TYPE
   ===================================================== */
if ($usertype === 'SubZonal') {
    /* ---- ZONAL SIDE ---- */
    $zonalId = $sessEmpId ?: $zonalIdPost;
    if ($zonalId === '') {
        echo json_encode(['success' => false, 'error' => 'Zonal id not resolved']);
        exit;
    }

    // IMPS must come from clicked-row button (hidden field)
    $impsId = $impsIdPost;
    if ($impsId === '') {
        echo json_encode(['success' => false, 'error' => 'IMPS user not selected']);
        exit;
    }

    $sender_type = 'Zonal';
    $sender_id   = $sessEmpId ?: $sessLogin ?: $zonalId;

} elseif ($usertype === 'Accounts IMPS' || $usertype === 'AccHead' || $usertype === 'Master') {
    /* ---- IMPS / AccHead / Master SIDE ---- */
    // canonical IMPS employeeId
    $impsCanonical = '';

    if ($sessEmpId !== '') {
        $impsCanonical = $sessEmpId;
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
            $impsCanonical = $row['employeeId'] ?? '';
        }
    }

    if ($impsCanonical === '') {
        echo json_encode(['success' => false, 'error' => 'IMPS employeeId not resolved']);
        exit;
    }

    $impsId  = $impsCanonical;  // store employeeId in imps_id
    $zonalId = $zonalIdPost;    // Zonal comes from clicked row

    if ($zonalId === '') {
        echo json_encode(['success' => false, 'error' => 'Zonal user not selected']);
        exit;
    }

    // Sender type: IMPS for IMPS/AccHead, Master for Master
    if ($usertype === 'Master') {
        $sender_type = 'Master';
    } else {
        $sender_type = 'IMPS';
    }

    $sender_id = $sessLogin ?: $impsCanonical;

} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized usertype: ' . $usertype]);
    exit;
}

/* =====================================================
   IMAGE UPLOAD (optional)
   ===================================================== */
$message_type  = 'text';
$imageFileName = null;
$MAX_IMAGE_SIZE = 8 * 1024 * 1024; // 8MB

if (
    isset($_FILES['image']) &&
    is_array($_FILES['image']) &&
    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $fileError = $_FILES['image']['error'];

    if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
        echo json_encode(['success' => false, 'error' => 'Image too large (ini limit).']);
        exit;
    }
    if ($fileError !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Image upload error: '.$fileError]);
        exit;
    }
    if ($_FILES['image']['size'] > $MAX_IMAGE_SIZE) {
        echo json_encode(['success' => false, 'error' => 'Image too large. Max 8MB.']);
        exit;
    }

    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $origName   = $_FILES['image']['name'];
    $tmpPath    = $_FILES['image']['tmp_name'];
    $ext        = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid type (jpg,jpeg,png,gif,webp).']);
        exit;
    }

    $uploadDir = __DIR__ . '/ZonalImpsChatImages/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $imageFileName = date('YmdHis') . '_zimps_' . mt_rand(1000,9999) . '.' . $ext;
    $destFull      = $uploadDir . $imageFileName;

    if (!move_uploaded_file($tmpPath, $destFull)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save image.']);
        exit;
    }

    $message_type = 'image';
}

/* If text-only and no message, block empty send */
if ($message_type === 'text' && $message === '') {
    echo json_encode(['success' => false, 'error' => 'Nothing to send']);
    exit;
}

/* =====================================================
   INSERT ROW
   ===================================================== */
$zonalIdEsc    = mysqli_real_escape_string($con, $zonalId);
$impsIdEsc     = mysqli_real_escape_string($con, $impsId);
$msgEsc        = mysqli_real_escape_string($con, $message);
$senderTypeEsc = mysqli_real_escape_string($con, $sender_type);
$senderIdEsc   = mysqli_real_escape_string($con, $sender_id);
$typeEsc       = mysqli_real_escape_string($con, $message_type);
$createdAtIst  = mysqli_real_escape_string($con, date('Y-m-d H:i:s'));

$imageValueSql = $imageFileName
    ? "'" . mysqli_real_escape_string($con, $imageFileName) . "'"
    : "NULL";

$sql = "
    INSERT INTO zonal_imps_chat
        (zonalId, imps_id, sender_type, sender_id, message_type, message, image_file, created_at)
    VALUES
        ('$zonalIdEsc', '$impsIdEsc', '$senderTypeEsc', '$senderIdEsc', '$typeEsc', '$msgEsc', $imageValueSql, '$createdAtIst')
";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error'   => 'DB error: ' . mysqli_error($con)
    ]);
}

