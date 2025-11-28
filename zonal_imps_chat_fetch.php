<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$zonalId = $_GET['zonalId'] ?? '';
if ($zonalId === '') {
    echo json_encode(['success' => false, 'error' => 'Missing zonalId']);
    exit;
}

$usertype   = $_SESSION['usertype']       ?? '';
$sessLogin  = $_SESSION['login_username'] ?? '';
$sessEmp    = $_SESSION['employeeId']     ?? '';
$impsIdGet  = $_GET['imps_id']            ?? '';

/*
 * For IMPS-side roles, we always resolve a canonical IMPS employeeId
 * from the session/users table, and ignore any incoming imps_id.
 * This covers: Accounts IMPS, AccHead, Master.
 */
$impsSideTypes = ['Accounts IMPS', 'AccHead', 'Master'];

$impsEmpId   = '';
$impsUser    = $sessLogin;

/* ---------- Resolve IMPS id for IMPS-side roles ---------- */
if (in_array($usertype, $impsSideTypes, true)) {

    if ($sessEmp !== '') {
        $impsEmpId = $sessEmp;
    } else {
        $loginEsc = mysqli_real_escape_string($con, $sessLogin);
        $q = mysqli_query(
            $con,
            "SELECT employeeId, username
             FROM users
             WHERE username = '$loginEsc' OR employeeId = '$loginEsc'
             LIMIT 1"
        );
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $impsEmpId = $row['employeeId'] ?? '';
            if ($impsUser === '') {
                $impsUser = $row['username'] ?? '';
            }
        }
    }

    if ($impsEmpId === '') {
        echo json_encode(['success' => false, 'error' => 'Missing imps_id (session)']);
        exit;
    }

    $impsEmpEsc  = mysqli_real_escape_string($con, $impsEmpId);
    $impsUserEsc = mysqli_real_escape_string($con, $impsUser);

    // Backwards compatible: match rows stored with imps_id = employeeId OR username
    $whereImps = "(c.imps_id = '$impsEmpEsc' OR c.imps_id = '$impsUserEsc')";

} else {
    /* ---------- Zonal side or others use GET imps_id (as before) ---------- */
    if ($impsIdGet === '') {
        echo json_encode(['success' => false, 'error' => 'Missing imps_id']);
        exit;
    }
    $impsIdEsc = mysqli_real_escape_string($con, $impsIdGet);
    $whereImps = "c.imps_id = '$impsIdEsc'";
}

$zonalIdEsc = mysqli_real_escape_string($con, $zonalId);

$sql = "
    SELECT
        c.id,
        c.sender_type,
        c.sender_id,
        c.message_type,
        c.message,
        c.image_file,
        DATE_FORMAT(c.created_at, '%d-%m %H:%i')  AS time,
        DATE_FORMAT(c.created_at, '%d-%m-%Y')     AS day,

        CASE 
            WHEN c.sender_type = 'Zonal' THEN 
                COALESCE(e.name, c.sender_id)

            WHEN c.sender_type = 'IMPS' THEN 
                COALESCE(u.agent, e.name, c.sender_id)

            WHEN c.sender_type = 'Master' THEN 
                COALESCE(u.agent, e.name, 'Master')

            ELSE 
                c.sender_id
        END AS sender_label

    FROM zonal_imps_chat c
    LEFT JOIN employee e 
        ON c.sender_id = e.empId
    /* Role-aware join to users so we don't double match employeeId + username */
    LEFT JOIN users u 
        ON (
            (c.sender_type = 'IMPS'  AND c.sender_id = u.username)
         OR (c.sender_type IN ('Zonal','Master') AND c.sender_id = u.employeeId)
        )
    WHERE c.zonalId = '$zonalIdEsc'
      AND $whereImps
    ORDER BY c.id ASC
    LIMIT 200
";

$res = mysqli_query($con, $sql);
$messages = [];

while ($row = mysqli_fetch_assoc($res)) {
    $imageUrl = null;
    if (!empty($row['image_file'])) {
        $imageUrl = 'ZonalImpsChatImages/' . $row['image_file'];
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

echo json_encode([
    'success'  => true,
    'messages' => $messages
]);

