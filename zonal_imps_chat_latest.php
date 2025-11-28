<?php
session_start();
include("dbConnection.php");

header('Content-Type: application/json');

$zonalId  = $_GET['zonalId'] ?? '';
$role     = $_GET['role']    ?? 'IMPS';  // 'Zonal' | 'IMPS' | 'Master'
$impsId   = $_GET['imps_id'] ?? '';      // used for Zonal side; IMPS side uses session

if ($zonalId === '') {
    echo json_encode(['success' => false, 'error' => 'Missing zonalId']);
    exit;
}

$sessionType = $_SESSION['usertype']       ?? '';
$sessLogin   = $_SESSION['login_username'] ?? '';
$sessEmpId   = $_SESSION['employeeId']     ?? '';

/* -------------------------------------------------
   Resolve canonical IMPS employeeId
   ------------------------------------------------- */
$impsEmployeeId = '';

if ($role === 'IMPS') {
    // We are checking unread for the logged-in IMPS
    if ($sessEmpId !== '') {
        $impsEmployeeId = $sessEmpId;
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
} elseif ($role === 'Zonal') {
    // Zonal side – impsId comes from JS (row’s employeeId)
    $impsEmployeeId = $impsId;
}

// Build filter on imps_id if we have it
$zonalIdEsc = mysqli_real_escape_string($con, $zonalId);
$impsClause = '';
$impsFilter = '';

if ($impsEmployeeId !== '') {
    $impsEsc   = mysqli_real_escape_string($con, $impsEmployeeId);
    $impsClause = " AND c.imps_id = '$impsEsc' ";   // for latest message query
    $impsFilter = " AND c.imps_id = '$impsEsc' ";   // for unread count
}

/* -------------------------------------------------
   1) Latest message in this room
   ------------------------------------------------- */
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
            WHEN c.sender_type = 'Zonal' THEN 
                COALESCE(e.name, c.sender_id)
            WHEN c.sender_type = 'IMPS' THEN 
                COALESCE(u.agent, u.username, e.name, c.sender_id)
            WHEN c.sender_type = 'Master' THEN 
                COALESCE(u.agent, e.name, 'Master')
            ELSE 
                c.sender_id
        END AS sender_label
    FROM zonal_imps_chat c
    LEFT JOIN employee e ON c.sender_id = e.empId
    LEFT JOIN users    u ON (c.sender_id = u.username OR c.sender_id = u.employeeId)
    WHERE c.zonalId = '$zonalIdEsc'
    $impsClause
    ORDER BY c.id DESC
    LIMIT 1
";

$resLatest = mysqli_query($con, $sqlLatest);
$latest = null;

if ($row = mysqli_fetch_assoc($resLatest)) {
    $latestId = (int)$row['id'];
    $imageUrl = !empty($row['image_file']) ? 'ZonalImpsChatImages/'.$row['image_file'] : null;

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

/* -------------------------------------------------
   2) Unread count
   ------------------------------------------------- */

/*
 * Zonal side (role = 'Zonal'):
 *   - show unread from IMPS/Master, per-IMPS if impsEmployeeId present
 *
 * IMPS side (role = 'IMPS'):
 *   - show unread from Zonal only for THIS IMPS (imps_id = its employeeId)
 *
 * Master (role = 'Master'):
 *   - show unread from Zonal aggregated (no per-IMPS split)
 */

if ($role === 'Zonal') {

    $sqlUnread = "
        SELECT COUNT(*) AS unreadCount
        FROM zonal_imps_chat c
        WHERE c.zonalId     = '$zonalIdEsc'
          AND c.sender_type IN ('IMPS','Master')
          AND c.seen_zonal  = 0
          $impsFilter
    ";

} elseif ($role === 'IMPS') {

    // IMPS side → only its own room (per imps_id)
    if ($impsEmployeeId === '') {
        // cannot resolve IMPS – safely return 0 so we don't break UI
        echo json_encode([
            'success'      => true,
            'latest'       => $latest,
            'unread_count' => 0
        ]);
        exit;
    }

    $sqlUnread = "
        SELECT COUNT(*) AS unreadCount
        FROM zonal_imps_chat c
        WHERE c.zonalId     = '$zonalIdEsc'
          AND c.sender_type = 'Zonal'
          AND c.seen_imps   = 0
          $impsFilter
    ";

} else {
    // Master or others – aggregated unread from Zonal (no per-IMPS split)
    $sqlUnread = "
        SELECT COUNT(*) AS unreadCount
        FROM zonal_imps_chat c
        WHERE c.zonalId     = '$zonalIdEsc'
          AND c.sender_type = 'Zonal'
          AND c.seen_imps   = 0
    ";
}

$resUnread   = mysqli_query($con, $sqlUnread);
$rowUnread   = mysqli_fetch_assoc($resUnread);
$unreadCount = (int)($rowUnread['unreadCount'] ?? 0);

/* -------------------------------------------------
   Response
   ------------------------------------------------- */
echo json_encode([
    'success'      => true,
    'latest'       => $latest,
    'unread_count' => $unreadCount
]);

