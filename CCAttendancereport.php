<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("dbConnection.php"); // must define $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

$type   = $_SESSION['usertype']       ?? '';
$empId  = $_SESSION['login_username'] ?? ''; // logged-in username
$today  = date('Y-m-d');

/* ---------------------------------
   Access control
---------------------------------- */
if (!in_array($type, ['CallCenterUser','CCAdmin'], true)) {
    include("logout.php");
    exit();
}

/* ---------------------------------
   Helpers
---------------------------------- */
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
function date_range($start, $end){ // inclusive Y-m-d
    $out = [];
    $cur = strtotime($start);
    $endT = strtotime($end);
    if ($cur === false || $endT === false) return $out;
    if ($cur > $endT) return $out;
    while ($cur <= $endT) { $out[] = date('Y-m-d', $cur); $cur = strtotime('+1 day', $cur); }
    return $out;
}

/* ---------------------------------
   Tables + dynamic columns
---------------------------------- */
$USERS_TABLE = 'users';
$ATT_TABLE   = 'ccattendance';

$USR_USERNAME_COL = firstExistingColumn($con, $USERS_TABLE, ['username','login_username','user','email']);
$USR_EMP_ID_COL   = firstExistingColumn($con, $USERS_TABLE, ['employeeId','empId','empld','employee_id','EmpId','EmpID']);
$USR_TYPE_COL     = firstExistingColumn($con, $USERS_TABLE, ['type','usertype','role']);

$ATT_USER_COLS = existingCols($con, $ATT_TABLE, [
  'CCemplID','empld','empId','employeeId','login_username','username','user'
]);
$ATT_DATE_COL  = firstExistingColumn($con, $ATT_TABLE, [
  'date','login_date','login_time','time','created_at','timestamp','lastlogin','CCTime'
]);

$errors = [];
if (!$USR_USERNAME_COL || !$USR_TYPE_COL) {
  $errors[] = "Users table is missing required columns.";
}
if (empty($ATT_USER_COLS) || !$ATT_DATE_COL) {
  $errors[] = "ccattendance table is missing required columns.";
}

/* ---------------------------------
   Load all CallCenter users
---------------------------------- */
$allUsers = [];          // username => ['username'=>, 'employeeId'=>?]
$allUsernames = [];      // list of usernames
if (!$errors) {
  $uUser = str_replace('`','``',$USR_USERNAME_COL);
  $uType = str_replace('`','``',$USR_TYPE_COL);
  $sel = "`{$uUser}` AS username";
  if ($USR_EMP_ID_COL) $sel .= ", `".str_replace('`','``',$USR_EMP_ID_COL)."` AS employeeId";

  $qAll = "SELECT {$sel} FROM `{$USERS_TABLE}` WHERE `{$uType}`='CallCenterUser'";
  if ($res = mysqli_query($con, $qAll)) {
    while ($r = mysqli_fetch_assoc($res)) {
      $u = $r['username'];
      $allUsers[$u] = ['username'=>$u,'employeeId'=>$r['employeeId'] ?? null];
      $allUsernames[] = $u;
    }
    mysqli_free_result($res);
  } else {
    $errors[] = 'Users query failed: '.mysqli_error($con);
  }
}
$totalCCUsers = count($allUsernames);

/* ---------------------------------
   Calendar month navigation
---------------------------------- */
$y = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
if ($m < 1 || $m > 12) { $y = (int)date('Y'); $m = (int)date('n'); }

$firstOfMonth = sprintf('%04d-%02d-01', $y, $m);
$daysInMonth  = (int)date('t', strtotime($firstOfMonth));
$lastOfMonth  = sprintf('%04d-%02d-%02d', $y, $m, $daysInMonth);

$prevY = $y; $prevM = $m - 1; if ($prevM < 1) { $prevM = 12; $prevY--; }
$nextY = $y; $nextM = $m + 1; if ($nextM > 12) { $nextM = 1; $nextY++; }

/* ---------------------------------
   Bulk load attendance for month
---------------------------------- */
$presentByDate = [];  // 'Y-m-d' => set of usernames present (assoc array for O(1) lookups)
$attUserExprParts = [];
foreach ($ATT_USER_COLS as $c) $attUserExprParts[] = 'a.`'.str_replace('`','``',$c).'`';
$attUserExpr = (count($attUserExprParts) > 1) ? 'COALESCE('.implode(',', $attUserExprParts).')' : $attUserExprParts[0];
$aDateExpr   = 'a.`'.str_replace('`','``',$ATT_DATE_COL).'`';
$toUserExpr  = 'u.`'.str_replace('`','``',$USR_USERNAME_COL).'`';
$joinCond    = "($attUserExpr = $toUserExpr)";
if ($USR_EMP_ID_COL) {
  $toEmpExpr = 'u.`'.str_replace('`','``',$USR_EMP_ID_COL).'`';
  $joinCond  = "($attUserExpr = $toUserExpr OR $attUserExpr = $toEmpExpr)";
}

if (!$errors) {
  $qMon = "
    SELECT DATE({$aDateExpr}) AS d, u.`".str_replace('`','``',$USR_USERNAME_COL)."` AS username
    FROM `{$USERS_TABLE}` u
    JOIN `{$ATT_TABLE}` a
      ON {$joinCond}
     AND DATE({$aDateExpr}) BETWEEN ? AND ?
    WHERE u.`".str_replace('`','``',$USR_TYPE_COL)."`='CallCenterUser'
  ";
  if ($stmt = mysqli_prepare($con, $qMon)) {
    mysqli_stmt_bind_param($stmt, 'ss', $firstOfMonth, $lastOfMonth);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
      while ($r = mysqli_fetch_assoc($res)) {
        $d = $r['d'];
        $u = $r['username'];
        if (!isset($presentByDate[$d])) $presentByDate[$d] = [];
        $presentByDate[$d][$u] = true; // set presence
      }
      mysqli_free_result($res);
    } else {
      $errors[] = 'Month attendance load failed: '.mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
  } else {
    $errors[] = 'Month attendance prepare failed: '.mysqli_error($con);
  }
}

/* ---------------------------------
   Day detail (clickable date)
---------------------------------- */
$dayParam = isset($_GET['d']) ? $_GET['d'] : '';
$dayDetail = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayParam) ? $dayParam : '');

$presentList = []; $absentList = [];
if ($dayDetail && isset($presentByDate[$dayDetail])) {
  $presentList = array_keys($presentByDate[$dayDetail]);
}
if ($dayDetail) {
  // absent = all users - present
  $absentList = array_values(array_diff($allUsernames, $presentList));
}

/* ---------------------------------
   Range summary (POST)
---------------------------------- */
$from = isset($_POST['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['from']) ? $_POST['from'] : $today;
$to   = isset($_POST['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['to'])   ? $_POST['to']   : $today;

$rangeRows = []; // each row: ['i'=>#, 'employee'=>username, 'days_present'=>X, 'total_days'=>Y, 'pct'=>Z, 'absent_dates'=>CSV]

if (!$errors) {
  $dates = date_range($from, $to); // inclusive
  $totalDays = count($dates);

  if ($totalDays > 0) {
    // Load attendance for range
    $qRange = "
      SELECT DATE({$aDateExpr}) AS d, u.`".str_replace('`','``',$USR_USERNAME_COL)."` AS username
      FROM `{$USERS_TABLE}` u
      JOIN `{$ATT_TABLE}` a
        ON {$joinCond}
       AND DATE({$aDateExpr}) BETWEEN ? AND ?
      WHERE u.`".str_replace('`','``',$USR_TYPE_COL)."`='CallCenterUser'
    ";
    $presentMap = []; // username => set of dates present
    if ($stmt = mysqli_prepare($con, $qRange)) {
      mysqli_stmt_bind_param($stmt, 'ss', $from, $to);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
          $u = $r['username']; $d = $r['d'];
          if (!isset($presentMap[$u])) $presentMap[$u] = [];
          $presentMap[$u][$d] = true;
        }
        mysqli_free_result($res);
      } else {
        $errors[] = 'Range attendance query failed: '.mysqli_error($con);
      }
      mysqli_stmt_close($stmt);
    } else {
      $errors[] = 'Range attendance prepare failed: '.mysqli_error($con);
    }

    // Build per-user summary
    $i = 1;
    foreach ($allUsernames as $u) {
      $presentDates = isset($presentMap[$u]) ? array_keys($presentMap[$u]) : [];
      $daysPresent  = count($presentDates);
      $pct          = ($totalDays > 0) ? round($daysPresent * 100 / $totalDays, 1) : 0.0;
      // absent dates in range
      $absDates = array_values(array_diff($dates, $presentDates));
      $rangeRows[] = [
        'i'            => $i++,
        'employee'     => $u,
        'days_present' => $daysPresent,
        'total_days'   => $totalDays,
        'pct'          => $pct,
        'absent_dates' => implode(', ', $absDates),
      ];
    }
  }
}
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
<title>Attendance Report</title>
<style>
  /* Layout + Theme (match your existing panels/colors) */
  #wrapper { background:#f5f5f5; min-height:100vh; }
  .hpanel.panel-box { margin-top: 12px; border: 4px solid #fff; border-radius: 10px; padding: 10px; background:#f6f2ec; }
  .panel-heading h3 { text-transform: uppercase; font-weight:600; font-size: 20px; color:#123C69; margin:0; }
  .btn-primary, .btn-success { background:#123C69; color:#fff; border:none; padding:8px 12px; border-radius:6px; }
  .btn-primary:hover, .btn-success:hover { filter:brightness(0.95); }
  .muted { color:#6d7b88; }
  .text-success { color:#123C69; text-transform:uppercase; font-weight:bold; font-size:11px; }

  /* Calendar */
  .cal-wrap { display:grid; grid-template-columns: 1fr 320px; gap:18px; }
  @media (max-width: 992px){ .cal-wrap{ grid-template-columns: 1fr; } }
  .cal { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:14px 16px; }
  .cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
  .cal-title { font-weight:700; color:#123C69; }
  .cal-grid { display:grid; grid-template-columns: repeat(7, 1fr); gap:8px; }
  .cal-dow { text-align:center; font-size:12px; text-transform:uppercase; color:#6d7b88; padding:4px 0; }
  .cal-day {
    background:#f7f8fb; border:1px solid #e7e9ef; border-radius:10px; padding:8px; height:68px;
    display:flex; flex-direction:column; gap:6px; justify-content:space-between;
  }
  .cal-day a { text-decoration:none; color:inherit; display:block; height:100%; }
  .cal-num { font-weight:700; color:#123C69; }
  .cal-count {
    background:#eef3ff; border:1px solid #d7e4ff; color:#123C69; font-size:12px; text-align:center;
    padding:2px 0; border-radius:10px;
  }
  .cal-empty { background:transparent; border:none; }
  .cal-today { outline:2px solid #ffcf40; }

  /* Day detail */
  .day-card { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:14px 16px; }
  .pill { display:inline-block; padding:3px 8px; border-radius:9999px; font-size:12px; margin-right:6px; }
  .pill.green { background:#e6f4ea; color:#1b5e20; border:1px solid #c8e6c9; }
  .pill.red   { background:#ffebee; color:#b71c1c; border:1px solid #ffcdd2; }
  .listbox    { border:1px solid #eee; background:#fafafa; border-radius:8px; padding:8px; max-height:150px; overflow:auto; }

  /* Range summary */
  .range-card { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); padding:14px 16px; margin-top:14px; }
  table { width:100%; border-collapse:separate; border-spacing:0; }
  th, td { padding:10px 12px; font-size:14px; }
  thead th { background:#123C69; color:#fff; text-transform:uppercase; font-size:12px; letter-spacing:.02em; }
  tbody tr:nth-child(even){ background:#f9fafb; }
  tbody td { border-bottom:1px solid #eee; vertical-align:top; }
</style>
</head>
<body>
<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel panel-box">
        <div class="panel-heading">
          <h3 class="text-success no-margins">
            <span style="color:#900" class="fa fa-calendar"></span> <b>Attendance</b>
          </h3>
        </div>

        <div class="panel-body">
          <?php if ($errors): ?>
            <div style="color:#b00; margin-bottom:10px;"><?php echo esc(implode(' | ', $errors)); ?></div>
          <?php endif; ?>

          <!-- Calendar + Day Detail -->
          <div class="cal-wrap">
            <div class="cal">
              <div class="cal-header">
                <a class="btn-primary" href="?y=<?php echo $prevY; ?>&m=<?php echo $prevM; ?>">&lt;</a>
                <div class="cal-title"><?php echo esc(date('F Y', strtotime($firstOfMonth))); ?></div>
                <a class="btn-primary" href="?y=<?php echo $nextY; ?>&m=<?php echo $nextM; ?>">&gt;</a>
              </div>

              <!-- Days of week -->
              <div class="cal-grid">
                <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow): ?>
                  <div class="cal-dow"><?php echo $dow; ?></div>
                <?php endforeach; ?>

                <?php
                  $firstDow = (int)date('w', strtotime($firstOfMonth)); // 0=Sun..6=Sat
                  for ($i=0; $i<$firstDow; $i++) echo '<div class="cal-day cal-empty"></div>';

                  for ($d=1; $d<=$daysInMonth; $d++) {
                    $ds   = sprintf('%04d-%02d-%02d', $y, $m, $d);
                    $pres = isset($presentByDate[$ds]) ? count($presentByDate[$ds]) : 0;
                    $isToday = ($ds === $today);
                    echo '<div class="cal-day'.($isToday?' cal-today':'').'">';
                    echo '  <a href="?y='.esc($y).'&m='.esc($m).'&d='.esc($ds).'">';
                    echo '    <div class="cal-num">'.(int)$d.'</div>';
                    echo '    <div class="cal-count">'.(int)$pres.' / '.(int)$totalCCUsers.'</div>';
                    echo '  </a>';
                    echo '</div>';
                  }
                ?>
              </div>
            </div>

            <div class="day-card">
              <h4 style="margin:0 0 8px 0; color:#123C69;">Day Detail</h4>
              <div class="muted" style="margin-bottom:6px;">Click a date on the calendar to load attendance.</div>
              <div style="margin-bottom:8px;">
                <strong>Date:</strong> <?php echo ($dayDetail ? esc($dayDetail) : '—'); ?>
              </div>
              <div style="display:flex; gap:12px; align-items:center; margin-bottom:6px;">
                <span class="pill green">Present: <?php echo ($dayDetail ? (int)count($presentList) : 0); ?></span>
                <span class="pill red">Absent: <?php echo ($dayDetail ? (int)count($absentList) : 0); ?></span>
              </div>

              <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div>
                  <div class="text-success">Present</div>
                  <div class="listbox"><?php echo $dayDetail ? esc(implode(', ', $presentList)) : '—'; ?></div>
                </div>
                <div>
                  <div class="text-success">Absent</div>
                  <div class="listbox"><?php echo $dayDetail ? esc(implode(', ', $absentList)) : '—'; ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Range Summary -->
          <div class="range-card">
            <h4 style="margin:0 0 10px 0; color:#123C69;">Attendance – Range Summary</h4>
            <form method="POST" style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
              <label>From</label>
              <input type="date" name="from" value="<?php echo esc($from); ?>" class="form-control" style="max-width:170px;">
              <label>To</label>
              <input type="date" name="to" value="<?php echo esc($to); ?>" class="form-control" style="max-width:170px;">
              <button class="btn-success" type="submit">Load Summary</button>
            </form>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Days Present</th>
                    <th>Total Days</th>
                    <th>Attendance %</th>
                    <th>Absent Dates</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($rangeRows)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:18px;">Select a date range and click Load Summary.</td></tr>
                  <?php else: ?>
                    <?php foreach ($rangeRows as $r): ?>
                      <tr>
                        <td><?php echo (int)$r['i']; ?></td>
                        <td><?php echo esc($r['employee']); ?></td>
                        <td><?php echo (int)$r['days_present']; ?></td>
                        <td><?php echo (int)$r['total_days']; ?></td>
                        <td><?php echo number_format((float)$r['pct'], 1); ?>%</td>
                        <td style="font-size:12px;"><?php echo esc($r['absent_dates']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <?php if ($errors): ?>
            <div style="margin-top:10px; color:#b00; font-size:12px;">
              <b>Note:</b> This report attempts to auto-detect columns in <code>users</code> and <code>ccattendance</code>.
              Verify your schema if counts look off.
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

