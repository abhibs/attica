<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("dbConnection.php"); // must define $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

$type   = $_SESSION['usertype']       ?? '';
$empId  = $_SESSION['login_username'] ?? '';
$today  = date('Y-m-d');

/* ------------------------------
   Access control
------------------------------- */
if (!in_array($type, ['CallCenterUser','CCAdmin'], true)) {
    include("logout.php");
    exit();
}

/* ------------------------------
   Helpers
------------------------------- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function hasCol(mysqli $con, $table, $col){
    $t = str_replace('`','``',$table);
    $c = str_replace('`','``',$col);
    $q = "SHOW COLUMNS FROM `{$t}` LIKE '{$c}'";
    $r = mysqli_query($con, $q);
    $ok = $r && mysqli_num_rows($r) > 0;
    if ($r) mysqli_free_result($r);
    return $ok;
}
function firstExistingColumn(mysqli $con, $table, array $cands){
    foreach ($cands as $c) if (hasCol($con, $table, $c)) return $c;
    return null;
}
function existingCols(mysqli $con, $table, array $cands){
    $out = [];
    foreach ($cands as $c) if (hasCol($con, $table, $c)) $out[] = $c;
    return $out;
}

/* ------------------------------
   KPI DATA
   users: username, employeeId, type='CallCenterUser'
   ccattendance: (any user id column), (any date/time column)
   ccexcel: created_at, updated_at
------------------------------- */
$attendanceErr = $usersErr = $kpiErr = null;

$USERS_TABLE = 'users';
$ATT_TABLE   = 'ccattendance';

/* users columns */
$USR_USERNAME_COL = firstExistingColumn($con, $USERS_TABLE, ['username','login_username','user','email']);
$USR_EMP_ID_COL   = firstExistingColumn($con, $USERS_TABLE, ['employeeId','empId','empld','employee_id','EmpId','EmpID']);
$USR_TYPE_COL     = firstExistingColumn($con, $USERS_TABLE, ['type','usertype','role']);
if (!$USR_USERNAME_COL || !$USR_TYPE_COL) {
  $usersErr = "Missing users columns: ".(!$USR_USERNAME_COL?'[username] ':'').(!$USR_TYPE_COL?'[type] ':'');
}

/* ccattendance columns */
$ATT_USER_COLS = existingCols($con, $ATT_TABLE, [
  'CCemplID','empld','empId','employeeId','login_username','username','user'
]);
$ATT_DATE_COL  = firstExistingColumn($con, $ATT_TABLE, [
  'date','login_date','login_time','time','created_at','timestamp','lastlogin','CCTime'
]);

if (empty($ATT_USER_COLS) || !$ATT_DATE_COL) {
  $attendanceErr = "Missing ccattendance columns: ".(empty($ATT_USER_COLS)?'[user id] ':'').(!$ATT_DATE_COL?'[date/time] ':'');
}

/* All call-center users */
$allCCUsernames = [];
$totalCCUsers = 0;
if (!$usersErr) {
  $uUser = str_replace('`','``',$USR_USERNAME_COL);
  $uType = str_replace('`','``',$USR_TYPE_COL);
  $qAll = "SELECT `{$uUser}` AS username FROM `{$USERS_TABLE}` WHERE `{$uType}`='CallCenterUser'";
  if ($resAll = mysqli_query($con, $qAll)) {
    while ($r = mysqli_fetch_assoc($resAll)) $allCCUsernames[] = $r['username'];
    mysqli_free_result($resAll);
    $totalCCUsers = count($allCCUsernames);
  } else { $usersErr = mysqli_error($con); }
}

/* Logged in today */
$loggedUsernames = [];
$loggedInToday = 0;

if (!$usersErr && !$attendanceErr) {
  // Build COALESCE(a.`userCol1`, a.`userCol2`, ...) to match either users.username or users.employeeId
  $attUserExprParts = [];
  foreach ($ATT_USER_COLS as $c) $attUserExprParts[] = 'a.`'.str_replace('`','``',$c).'`';
  $attUserExpr = (count($attUserExprParts) > 1) ? 'COALESCE('.implode(',', $attUserExprParts).')' : $attUserExprParts[0];

  $aDate = 'a.`'.str_replace('`','``',$ATT_DATE_COL).'`';
  $uUser = 'u.`'.str_replace('`','``',$USR_USERNAME_COL).'`';
  $uType = 'u.`'.str_replace('`','``',$USR_TYPE_COL).'`';

  $joinCond = "($attUserExpr = $uUser)";
  if ($USR_EMP_ID_COL) {
    $uEmp = 'u.`'.str_replace('`','``',$USR_EMP_ID_COL).'`';
    $joinCond = "($attUserExpr = $uUser OR $attUserExpr = $uEmp)";
  }

  $qLogged = "
    SELECT DISTINCT u.`".str_replace('`','``',$USR_USERNAME_COL)."` AS username
    FROM `{$USERS_TABLE}` u
    JOIN `{$ATT_TABLE}` a
      ON {$joinCond}
     AND DATE({$aDate}) = ?
    WHERE {$uType} = 'CallCenterUser'
  ";

  if ($stmt = mysqli_prepare($con, $qLogged)) {
    mysqli_stmt_bind_param($stmt, 's', $today);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
      while ($r = mysqli_fetch_assoc($res)) $loggedUsernames[] = $r['username'];
      mysqli_free_result($res);
      $loggedInToday = count($loggedUsernames);
    } else {
      $attendanceErr = mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
  } else {
    $attendanceErr = mysqli_error($con);
  }
}

/* Not logged in today */
$notLoggedToday = array_values(array_diff($allCCUsernames, $loggedUsernames));
sort($notLoggedToday);

/* ccexcel created today */
$createdToday = 0;
$resC = mysqli_query($con, "SELECT COUNT(*) AS c FROM `ccexcel` WHERE DATE(`created_at`)='$today'");
if ($resC) { $createdToday = (int) (mysqli_fetch_assoc($resC)['c'] ?? 0); mysqli_free_result($resC); }
else { $kpiErr = mysqli_error($con); }

/* ccexcel updated today but created earlier */
$updatedTodayDifferentDate = 0;
$resU = mysqli_query($con, "SELECT COUNT(*) AS c FROM `ccexcel` WHERE DATE(`updated_at`)='$today' AND DATE(`created_at`) <> '$today'");
if ($resU) { $updatedTodayDifferentDate = (int) (mysqli_fetch_assoc($resU)['c'] ?? 0); mysqli_free_result($resU); }
else { $kpiErr = mysqli_error($con); }

if ($type === 'CCAdmin') {
    include("header.php");
    include("menuCCAdmin.php");
} else {
    include("logout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Call Center KPI Dashboard</title>
<style>
  /* Keep your original look */
  #wrapper { background:#f5f5f5; min-height:100vh; }
  .hpanel.panel-box { margin-top: 12px; border: 4px solid #fff; border-radius: 10px; padding: 10px; background:#f6f2ec; }
  .panel-heading h3 { text-transform: uppercase; font-weight:600; font-size: 20px; color:#123C69; }
  .btn-success { background:#123C69; color:#fff; }
  .text-success { color:#123C69; text-transform:uppercase; font-weight:bold; font-size:11px; }

  /* KPI cards */
  .kpi-grid {
    display:grid;
    grid-template-columns: repeat( auto-fill, minmax(280px, 1fr) );
    gap:16px;
    margin: 12px 4px 6px 4px;
  }
  .kpi {
    background:#fff;
    border-radius:12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    padding:16px 18px;
    display:flex; flex-direction:column; gap:6px;
    min-height:110px;
  }
  .kpi .label { font-size:12px; color:#5b6b7b; text-transform:uppercase; letter-spacing:.02em; }
  .kpi .value { font-size:34px; line-height:1; font-weight:800; color:#123C69; }
  .kpi small { color:#6d7b88; }
  .kpi .list {
    font-size:12px; color:#333; background:#fafafa; border-radius:8px; padding:6px 8px;
    max-height:120px; overflow:auto; border:1px solid #eee;
  }
  .ok { color:#2e7d32; }
  .warn { color:#c62828; }
</style>
</head>
<body>
<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel panel-box">
        <div class="panel-heading">
          <h3 class="text-success no-margins">
            <span style="color:#900" class="fa fa-bar-chart"></span> <b>CALL CENTER KPI DASHBOARD</b>
            <button style="float:right" onclick="window.location.reload();" class="btn btn-success">
              <b><i style="color:#ffcf40" class="fa fa-refresh"></i> REFRESH</b>
            </button>
          </h3>
        </div>

        <div class="panel-body">
          <div class="kpi-grid">
            <!-- Welcome -->
            <div class="kpi">
              <div class="label">Welcome</div>
              <div class="value" style="text-align:center;"><?php echo ($type==='CCAdmin' ? 'CCAdmin' : esc($empId)); ?></div>
              <small><center>Have a productive day.</center></small>
            </div>

            <!-- Logged in today -->
            <div class="kpi">
              <div class="label">Logged In Today</div>
              <div class="value" style="text-align:center;"><?php echo (int)$loggedInToday; ?></div>
              <small class="ok">Out of <?php echo (int)$totalCCUsers; ?> CallCenterUsers</small>
            </div>

            <!-- Not logged in today -->
            <div class="kpi">
              <div class="label">Not Logged In Today</div>
              <div class="value" style="text-align:center;"><?php echo (int)count($notLoggedToday); ?></div>
              <?php if (!empty($notLoggedToday)): ?>
                <div class="list warn"><?php echo esc(implode(', ', $notLoggedToday)); ?></div>
              <?php else: ?>
                <small class="ok">All Present</small>
              <?php endif; ?>
            </div>

            <!-- Created today -->
            <div class="kpi">
              <div class="label">ccexcel Created Today</div>
              <div class="value" style="text-align:center;"><?php echo (int)$createdToday; ?></div>
            </div>

            <!-- Updated today -->
            <div class="kpi">
              <div class="label">Updated Today (Created Earlier)</div>
              <div class="value" style="text-align:center;"><?php echo (int)$updatedTodayDifferentDate; ?></div>
            </div>
          </div>

          <?php if ($attendanceErr || $usersErr || $kpiErr): ?>
            <div style="margin-top:8px; font-size:12px; color:#b00;">
              <b>Note:</b>
              <?php
                if ($attendanceErr) echo 'Attendance: '.esc($attendanceErr).'. ';
                if ($usersErr)     echo 'Users: '.esc($usersErr).'. ';
                if ($kpiErr)       echo 'KPI: '.esc($kpiErr).'. ';
              ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</div>
</body>
</html>


