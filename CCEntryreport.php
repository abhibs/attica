<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("dbConnection.php"); // must define $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

$type   = $_SESSION['usertype']       ?? '';
$empId  = $_SESSION['login_username'] ?? '';
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
function date_range($start, $end){
  $out=[]; $cs=strtotime($start); $ce=strtotime($end);
  if($cs===false||$ce===false||$cs>$ce) return $out;
  while($cs<=$ce){ $out[] = date('Y-m-d',$cs); $cs=strtotime('+1 day',$cs);}
  return $out;
}

/* ---------------------------------
   AJAX: lists & single entry
   - ?ajax=list&kind=created|updated&date=YYYY-MM-DD[&emp=EMP_CODE]
   - ?ajax=list&kind=created|updated&from=YYYY-MM-DD&to=YYYY-MM-DD[&emp=EMP_CODE]
   - ?ajax=one&si=123
---------------------------------- */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_GET['ajax'] === 'one') {
        $si = (int)($_GET['si'] ?? 0);
        if ($si <= 0) { echo json_encode(['ok'=>false,'error'=>'bad_si']); exit; }
        $row = null;
        if ($stmt = mysqli_prepare($con, "SELECT * FROM ccexcel WHERE SI=? LIMIT 1")) {
            mysqli_stmt_bind_param($stmt, 'i', $si);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res) ?: null;
            mysqli_free_result($res);
            mysqli_stmt_close($stmt);
        } else { echo json_encode(['ok'=>false,'error'=>mysqli_error($con)]); exit; }
        echo json_encode(['ok'=>true,'row'=>$row]); exit;
    }

    if ($_GET['ajax'] === 'list') {
        $kind = ($_GET['kind'] ?? '');
        if (!in_array($kind, ['created','updated'], true)) { echo json_encode(['ok'=>false,'error'=>'bad_kind']); exit; }
        $col  = ($kind === 'created') ? 'created_at' : 'updated_at';

        $emp  = trim($_GET['emp'] ?? '');
        $date = $_GET['date'] ?? '';
        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';

        $where = [];
        $types = '';
        $vals  = [];

        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) {
            $where[] = "DATE($col)=?";
            $types  .= 's';
            $vals[]  = $date;
        } else {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)) $from = $today;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$to))   $to   = $today;
            $where[] = "DATE($col) BETWEEN ? AND ?";
            $types  .= 'ss';
            $vals[]  = $from; $vals[] = $to;
        }
        if ($emp !== '') {
            $where[] = "emp_code = ?";
            $types  .= 's';
            $vals[]  = $emp;
        }

        $sql = "SELECT * FROM ccexcel";
        if ($where) $sql .= " WHERE ".implode(' AND ',$where);
        $sql .= " ORDER BY $col DESC";

        $rows = [];
        if ($stmt = mysqli_prepare($con, $sql)) {
            if ($types) mysqli_stmt_bind_param($stmt, $types, ...$vals);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
            mysqli_stmt_close($stmt);
        } else { echo json_encode(['ok'=>false,'error'=>mysqli_error($con)]); exit; }

        echo json_encode(['ok'=>true,'rows'=>$rows]); exit;
    }

    echo json_encode(['ok'=>false,'error'=>'unknown_ajax']); exit;
}

/* ---------------------------------
   Users (emp_codes) - optional
---------------------------------- */
$allUsernames = [];
if ($res = mysqli_query($con, "SELECT username FROM users WHERE type='CallCenterUser'")) {
    while ($r = mysqli_fetch_assoc($res)) $allUsernames[] = $r['username'];
    mysqli_free_result($res);
}

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
   Monthly Created/Updated counts
---------------------------------- */
$createdCountByDate = [];
$updatedCountByDate = [];

if ($stmt = mysqli_prepare($con, "SELECT DATE(created_at) d, COUNT(*) c FROM ccexcel WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY d")) {
    mysqli_stmt_bind_param($stmt, 'ss', $firstOfMonth, $lastOfMonth);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $createdCountByDate[$r['d']] = (int)$r['c'];
    mysqli_free_result($res); mysqli_stmt_close($stmt);
}
if ($stmt = mysqli_prepare($con, "SELECT DATE(updated_at) d, COUNT(*) c FROM ccexcel WHERE DATE(updated_at) BETWEEN ? AND ? GROUP BY d")) {
    mysqli_stmt_bind_param($stmt, 'ss', $firstOfMonth, $lastOfMonth);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $updatedCountByDate[$r['d']] = (int)$r['c'];
    mysqli_free_result($res); mysqli_stmt_close($stmt);
}

/* ---------------------------------
   Day detail mini-lists (optional, unchanged)
---------------------------------- */
$dayParam  = isset($_GET['d']) ? $_GET['d'] : '';
$dayDetail = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayParam) ? $dayParam : '');

$createdList = [];
$updatedList = [];
if ($dayDetail) {
    if ($stmt = mysqli_prepare($con, "SELECT SI, name, emp_code, created_at FROM ccexcel WHERE DATE(created_at)=? ORDER BY created_at")) {
        mysqli_stmt_bind_param($stmt, 's', $dayDetail);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) $createdList[] = $r;
        mysqli_free_result($res); mysqli_stmt_close($stmt);
    }
    if ($stmt = mysqli_prepare($con, "SELECT SI, name, emp_code, updated_at FROM ccexcel WHERE DATE(updated_at)=? ORDER BY updated_at")) {
        mysqli_stmt_bind_param($stmt, 's', $dayDetail);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) $updatedList[] = $r;
        mysqli_free_result($res); mysqli_stmt_close($stmt);
    }
}

/* ---------------------------------
   Range summary (Created & Updated per user)
---------------------------------- */
$from = isset($_POST['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['from']) ? $_POST['from'] : $today;
$to   = isset($_POST['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['to'])   ? $_POST['to']   : $today;

$userCreated = []; $userUpdated = [];
if ($stmt = mysqli_prepare($con, "SELECT emp_code, COUNT(*) c FROM ccexcel WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY emp_code")) {
    mysqli_stmt_bind_param($stmt, 'ss', $from, $to);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $userCreated[$r['emp_code']] = (int)$r['c'];
    mysqli_free_result($res); mysqli_stmt_close($stmt);
}
if ($stmt = mysqli_prepare($con, "SELECT emp_code, COUNT(*) c FROM ccexcel WHERE DATE(updated_at) BETWEEN ? AND ? GROUP BY emp_code")) {
    mysqli_stmt_bind_param($stmt, 'ss', $from, $to);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $userUpdated[$r['emp_code']] = (int)$r['c'];
    mysqli_free_result($res); mysqli_stmt_close($stmt);
}
$allEmpCodes = $allUsernames;
$seenCodes = array_unique(array_merge(array_keys($userCreated), array_keys($userUpdated)));
foreach ($seenCodes as $code) if (!in_array($code, $allEmpCodes, true)) $allEmpCodes[] = $code;
sort($allEmpCodes);

$rangeRows = [];
$ii = 1;
foreach ($allEmpCodes as $code) {
    $rangeRows[] = [
        'i'       => $ii++,
        'user'    => $code,
        'created' => (int)($userCreated[$code] ?? 0),
        'updated' => (int)($userUpdated[$code] ?? 0),
    ];
}

/* ---------------------------------
   Reports table (default: TODAY)
---------------------------------- */
$r_from = isset($_GET['r_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['r_from']) ? $_GET['r_from'] : $today;
$r_to   = isset($_GET['r_to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['r_to'])   ? $_GET['r_to']   : $today;
$r_name = isset($_GET['r_name']) ? trim($_GET['r_name']) : '';
$r_phone= isset($_GET['r_phone'])? trim($_GET['r_phone']) : '';

$rep_rows = [];
$rep_where = [];
$params = [];
$types  = '';

$rep_where[] = "DATE(created_at) BETWEEN ? AND ?"; $types.='ss'; $params[]=$r_from; $params[]=$r_to;
if ($r_name !== '')  { $rep_where[] = "name LIKE ?"; $types.='s'; $params[]='%'.$r_name.'%'; }
if ($r_phone !== '') { $rep_where[] = "(mob1 LIKE ? OR mob2 LIKE ?)"; $types.='ss'; $params[]='%'.$r_phone.'%'; $params[]='%'.$r_phone.'%'; }

$rep_sql = "SELECT SI,date,name,mob1,mob2,district,lead,status,emp_code,created_at,updated_at FROM ccexcel";
if ($rep_where) $rep_sql .= " WHERE ".implode(' AND ', $rep_where);
$rep_sql .= " ORDER BY created_at DESC LIMIT 500";

if ($stmt = mysqli_prepare($con, $rep_sql)) {
    if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) $rep_rows[] = $r;
    mysqli_free_result($res); mysqli_stmt_close($stmt);
}

/* ---------------------------------
   Header/Menu
---------------------------------- */
if ($type === 'CCAdmin') { include("header.php"); include("menuCCAdmin.php"); }
else { include("header.php"); include("menucallUser.php"); }

/* ---------------------------------
   Monthly Created/Updated totals
---------------------------------- */
$monthCreatedTotal = array_sum($createdCountByDate);
$monthUpdatedTotal = array_sum($updatedCountByDate);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>CCExcel Report</title>
<style>
.cal-header .totals{display:flex;gap:8px;align-items:center}
.pill.blue{background:#e8f1ff;color:#0b3d91;border:1px solid #cfe0ff}
.pill.orange{background:#fff4e5;color:#a15c00;border:1px solid #ffe0b3}
#wrapper{background:#f5f5f5;min-height:100vh}
.hpanel.panel-box{margin-top:12px;border:4px solid #fff;border-radius:10px;padding:10px;background:#f6f2ec}
.panel-heading h3{text-transform:uppercase;font-weight:600;font-size:20px;color:#123C69;margin:0}
.btn-primary,.btn-success,.btn-link{background:#123C69;color:#fff;border:none;padding:6px 10px;border-radius:6px;text-decoration:none;display:inline-block;cursor:pointer}
.btn-link{background:transparent;color:#123C69;padding:0;border-radius:0;text-decoration:underline}
.muted{color:#6d7b88}
.text-success{color:#123C69;text-transform:uppercase;font-weight:bold;font-size:11px}
.table-wrap{overflow:auto}
.cal-wrap{display:grid;grid-template-columns:1fr 420px;gap:18px}
@media (max-width:992px){.cal-wrap{grid-template-columns:1fr}}
.cal{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:14px 16px}
.cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.cal-title{font-weight:700;color:#123C69}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}
.cal-dow{text-align:center;font-size:12px;text-transform:uppercase;color:#6d7b88;padding:4px 0}
.cal-day{background:#f7f8fb;border:1px solid #e7e9ef;border-radius:10px;padding:8px;height:84px;display:flex;flex-direction:column;gap:6px;justify-content:space-between}
.cal-day .num{font-weight:700;color:#123C69}
.cal-day .counts{text-align:center;font-size:12px}
.cal-day .counts a{color:#123C69;text-decoration:underline;margin:0 4px}
.cal-empty{background:transparent;border:none}
.cal-today{outline:2px solid #ffcf40}
.day-card,.range-card,.reports-card{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:14px 16px}
.listbox{border:1px solid #eee;background:#fafafa;border-radius:8px;padding:8px;max-height:180px;overflow:auto;font-size:13px}
.listbox .item{padding:4px 0;border-bottom:1px dashed #e7e7e7;display:flex;align-items:center;justify-content:space-between;gap:6px}
.listbox .item:last-child{border-bottom:none}
table{width:100%;border-collapse:separate;border-spacing:0}
th,td{padding:10px 12px;font-size:14px}
thead th{background:#123C69;color:#fff;text-transform:uppercase;font-size:12px;letter-spacing:.02em}
tbody tr:nth-child(even){background:#f9fafb}
tbody td{border-bottom:1px solid #eee;vertical-align:top}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:2147483000}
.modal{width:min(920px,96vw);background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.25);overflow:hidden}
#entryModal .modal{display:block !important}
#entryModal .modal{position:static !important;top:auto !important;left:auto !important;right:auto !important;bottom:auto !important;margin:0 !important;display:block !important;width:min(920px,96vw);max-height:90vh;overflow:hidden;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.25)}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);align-items:center;justify-content:center;z-index:2147483000}
#entryModal .modal-body{max-height:72vh;overflow:auto}
.entry-list{display:flex;flex-direction:column;gap:12px}
.entry-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.entry-card .card-head{display:flex;justify-content:space-between;align-items:center;font-weight:700;color:#123C69;margin-bottom:8px}
.entry-card .card-sub{color:#6b7280;font-size:12px;font-weight:500}
.entry-divider{height:1px;margin:10px 0;background:linear-gradient(90deg,#e5e7eb,#f9fafb,#e5e7eb);border:0}
.modal-header{padding:12px 16px;background:#123C69;color:#fff;display:flex;align-items:center;justify-content:space-between}
.modal-body{padding:14px 16px;max-height:72vh;overflow:auto}
.modal-close{background:transparent;border:none;color:#fff;font-size:20px;cursor:pointer}
.form-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.form-grid .field{display:flex;flex-direction:column}
.field label{font-size:11px;text-transform:uppercase;color:#6d7b88}
.field div{background:#f9fafb;border:1px solid #e7e9ef;padding:7px 8px;border-radius:6px}
@media (max-width:900px){.form-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:560px){.form-grid{grid-template-columns:1fr}}
.day-card .cal-header{display:flex;align-items:center;justify-content:flex-start;gap:12px;margin:0 0 8px 0}
.day-card .cal-header .btn-primary{padding:4px 8px;line-height:1.1;border-radius:8px}
.day-card .cal-header .cal-title{font-size:18px;font-weight:700;color:#123C69;white-space:nowrap;margin:0}
.day-card .cal-header .totals{display:flex;gap:8px;align-items:center}
.day-card .cal-header .pill{padding:3px 8px;font-size:12px;border-radius:8px}
/* --- DataTables header row: buttons + search on one line --- */
.dt-header{  display:flex;  align-items:center;  gap:10px;  flex-wrap:wrap;   margin-bottom:8px;}
.dt-header .dt-buttons{ display:flex; gap:6px; }
.dt-header .dt-search{ margin-left:auto; }
.dt-header .dt-search input,
.dataTables_wrapper .dataTables_filter input{  min-width:260px;  height:34px;  border-radius:6px;  padding:6px 10px;}
.dt-header .dt-search label>span{ display:none; }
.dataTables_wrapper .dataTables_filter label { display:flex; align-items:center; gap:6px; }
.dataTables_wrapper .dataTables_filter label > span { display:none; }

</style>

<!-- DataTables + Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.dataTables.min.css">
</head>
<body>
<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel panel-box">
        <div class="panel-heading">
          <h3 class="text-success no-margins">
            <span style="color:#900" class="fa fa-table"></span> <b>CCExcel Report</b>
          </h3>
        </div>

        <div class="panel-body">
          <!-- Calendar + Day Detail -->
          <div class="cal-wrap">
            <div class="cal">
              <div class="cal-header">
                <a class="btn-primary" href="?y=<?php echo $prevY; ?>&m=<?php echo $prevM; ?>">&lt;</a>
                <div class="cal-title"><?php echo esc(date('F Y', strtotime($firstOfMonth))); ?></div>
                <a class="btn-primary" href="?y=<?php echo $nextY; ?>&m=<?php echo $nextM; ?>">&gt;</a>
              </div>

              <div class="cal-grid">
                <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow): ?>
                  <div class="cal-dow"><?php echo $dow; ?></div>
                <?php endforeach; ?>

                <?php
                  $firstDow = (int)date('w', strtotime($firstOfMonth));
                  for ($i=0; $i<$firstDow; $i++) echo '<div class="cal-day cal-empty"></div>';

                  for ($d=1; $d<=$daysInMonth; $d++) {
                    $ds = sprintf('%04d-%02d-%02d', $y, $m, $d);
                    $cr = $createdCountByDate[$ds] ?? 0;
                    $up = $updatedCountByDate[$ds] ?? 0;
                    $isToday = ($ds === $today);
                    echo '<div class="cal-day'.($isToday?' cal-today':'').'">';
                    echo '  <div class="num">'.(int)$d.'</div>';
                    echo '  <div class="counts">';
                    echo '    Cr: <a href="#" class="open-list" data-kind="created" data-date="'.esc($ds).'">'.(int)$cr.'</a>';
                    echo '    &nbsp;/&nbsp;';
                    echo '    Up: <a href="#" class="open-list" data-kind="updated" data-date="'.esc($ds).'">'.(int)$up.'</a>';
                    echo '  </div>';
                    echo '</div>';
                  }
                ?>
              </div>
            </div>

            <div class="day-card">
              <h4 style="margin:0 0 8px 0; color:#123C69;">Month Detail</h4>
              <div class="muted" style="margin-bottom:6px;">Click the numbers on the calendar to open the detailed list.</div>
              <div style="margin-bottom:8px;"><strong>Date:</strong> <?php echo ($dayDetail ? esc($dayDetail) : '—'); ?></div>
              <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div class="cal-header">
                  <a class="btn-primary" href="?y=<?php echo $prevY; ?>&m=<?php echo $prevM; ?>">&lt;</a>
                  <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div class="cal-title"><?php echo esc(date('F Y', strtotime($firstOfMonth))); ?></div>
                    <div class="totals">
                      <span class="pill blue">Created: <?php echo (int)$monthCreatedTotal; ?></span>
                      <span class="pill orange">Updated: <?php echo (int)$monthUpdatedTotal; ?></span>
                    </div>
                  </div>
                  <a class="btn-primary" href="?y=<?php echo $nextY; ?>&m=<?php echo $nextM; ?>">&gt;</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Range Summary (numbers clickable) -->
          <div class="range-card" id="rangeCard" data-from="<?php echo esc($from); ?>" data-to="<?php echo esc($to); ?>">
            <h4 style="margin:0 0 10px 0; color:#123C69;">CCExcel – Range Summary</h4>
            <form method="POST" style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
              <label>From</label>
              <input type="date" name="from" value="<?php echo esc($from); ?>" class="form-control" style="max-width:170px;">
              <label>To</label>
              <input type="date" name="to" value="<?php echo esc($to); ?>" class="form-control" style="max-width:170px;">
              <button class="btn-success" type="submit">Load Summary</button>
            </form>
            <div class="table-wrap">
              <table id="summaryTable">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>User (emp_code)</th>
                    <th>Created</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($rangeRows)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:18px;">Select a date range and click Load Summary.</td></tr>
                  <?php else: foreach ($rangeRows as $r): ?>
                    <tr>
                      <td><?php echo (int)$r['i']; ?></td>
                      <td><?php echo esc($r['user']); ?></td>
                      <td>
                        <a href="#"
                           class="open-range-list"
                           data-kind="created"
                           data-emp="<?php echo esc($r['user']); ?>">
                           <?php echo (int)$r['created']; ?>
                        </a>
                      </td>
                      <td>
                        <a href="#"
                           class="open-range-list"
                           data-kind="updated"
                           data-emp="<?php echo esc($r['user']); ?>">
                           <?php echo (int)$r['updated']; ?>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Reports (defaults to today) -->
          <div class="reports-card">
            <h4 style="margin:0 0 10px 0; color:#123C69;">Reports – Today by default</h4>
            <form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; margin-bottom:10px;">
              <div>
                <label class="text-success">From</label>
                <input type="date" name="r_from" value="<?php echo esc($r_from); ?>" class="form-control" style="max-width:170px;">
              </div>
              <div>
                <label class="text-success">To</label>
                <input type="date" name="r_to" value="<?php echo esc($r_to); ?>" class="form-control" style="max-width:170px;">
              </div>
              <div>
                <label class="text-success">Customer</label>
                <input type="text" name="r_name" value="<?php echo esc($r_name); ?>" class="form-control" placeholder="Name contains" style="max-width:220px;">
              </div>
              <div>
                <label class="text-success">Phone</label>
                <input type="text" name="r_phone" value="<?php echo esc($r_phone); ?>" class="form-control" placeholder="mob1 or mob2" style="max-width:180px;">
              </div>
              <div>
                <button class="btn-success" type="submit">Search</button>
              </div>
            </form>
            <div class="table-wrap">
              <table id="reportsTable">
                <thead>
                  <tr>
                    <th>SI</th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Mob-1</th>
                    <th>Mob-2</th>
                    <th>District</th>
                    <th>Lead</th>
                    <th>Status</th>
                    <th>EMP</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($rep_rows)): ?>
                    <tr><td colspan="12" style="text-align:center; padding:18px;">No records found (max 500 shown).</td></tr>
                  <?php else: foreach ($rep_rows as $r): ?>
                    <tr>
                      <td><?php echo esc($r['SI']); ?></td>
                      <td><?php echo esc($r['date']); ?></td>
                      <td><?php echo esc($r['name']); ?></td>
                      <td><?php echo esc($r['mob1']); ?></td>
                      <td><?php echo esc($r['mob2']); ?></td>
                      <td><?php echo esc($r['district']); ?></td>
                      <td><?php echo esc($r['lead']); ?></td>
                      <td><?php echo esc($r['status']); ?></td>
                      <td><?php echo esc($r['emp_code']); ?></td>
                      <td><?php echo esc($r['created_at']); ?></td>
                      <td><?php echo esc($r['updated_at']); ?></td>
                      <td><a href="#" class="btn-link view-one" data-si="<?php echo esc($r['SI']); ?>">View</a></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</div>

<!-- Modal -->
<div class="modal-overlay" id="entryModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal">
    <div class="modal-header">
      <div id="modalTitle">Entry Details</div>
      <button class="modal-close" id="modalClose" type="button" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div id="modalContent">Loading…</div>
    </div>
  </div>
</div>

<!-- jQuery + DataTables + Buttons -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>

<script>
// Tiny helpers
const $  = (sel,root=document)=>root.querySelector(sel);
const $$ = (sel,root=document)=>Array.from(root.querySelectorAll(sel));
const modal = $('#entryModal'), modalTitle = $('#modalTitle'), modalContent = $('#modalContent');
const openModal  = ()=>{ modal.style.display='flex'; document.documentElement.style.overflow='hidden'; };
const closeModal = ()=>{ modal.style.display='none'; document.documentElement.style.overflow=''; };
$('#modalClose').addEventListener('click', closeModal);
modal.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

/* ---------------- DataTables helpers ---------------- */
function initDT(id){
  const table = $(id);
  if(!table) return;

  const $table = jQuery(id);
  if ( jQuery.fn.dataTable.isDataTable($table) ) {
    $table.DataTable().destroy();
  }
  $table.DataTable({
    responsive: true,

    // Put Buttons (B) and Filter (f) in the same custom container
    dom: '<"dt-header"Bf>rtip',

    buttons: [
      { extend: 'copyHtml5', title: document.title || 'export' },
      { extend: 'csvHtml5',  title: document.title || 'export' },
      { extend: 'pdfHtml5',  title: document.title || 'export', orientation: 'landscape', pageSize: 'A4' },
      { extend: 'print',     title: document.title || 'export' }
    ],

    // Nice placeholder + remove "Search:" text (works with DT 1.x and 2.x)
    language: { search: "", searchPlaceholder: "Search…" },

    pageLength: 25
  });
}

// Renderers (table-based for DataTables in modal)
function renderEntryRow(r){
  const f = (k)=> (r[k] ?? '');
  return `
    <tr>
      <td>${f('SI')}</td>
      <td>${f('date')}</td>
      <td>${f('name')}</td>
      <td>${f('mob1')}</td>
      <td>${f('mob2')}</td>
      <td>${f('district')}</td>
      <td>${f('lead')}</td>
      <td>${f('status')}</td>
      <td>${f('emp_code')}</td>
      <td>${f('created_at')}</td>
      <td>${f('updated_at')}</td>
    </tr>
  `;
}

function renderEntryTable(rows){
  if(!rows || !rows.length) return '<div>No entries.</div>';
  let html = `
    <div class="table-wrap">
      <table id="modalTable" class="display" style="width:100%">
        <thead>
          <tr>
            <th>SI</th>
            <th>Date</th>
            <th>Name</th>
            <th>Mob-1</th>
            <th>Mob-2</th>
            <th>District</th>
            <th>Lead</th>
            <th>Status</th>
            <th>EMP</th>
            <th>Created</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody>
  `;
  for(const r of rows){ html += renderEntryRow(r); }
  html += `
        </tbody>
      </table>
    </div>
  `;
  return html;
}

function renderEntryOne(row){
  if(!row) return '<div>No data.</div>';
  const f = (k)=> (row[k] ?? '');
  return `
    <div style="margin-bottom:10px;font-weight:600;">Basic Details</div>
    <div class="form-grid" style="margin-bottom:14px;">
      <div class="field"><label>SI</label><div>${f('SI')}</div></div>
      <div class="field"><label>Date</label><div>${f('date')}</div></div>
      <div class="field"><label>Name</label><div>${f('name')}</div></div>
      <div class="field"><label>Mob-1</label><div>${f('mob1')}</div></div>
      <div class="field"><label>Mob-2</label><div>${f('mob2')}</div></div>
      <div class="field"><label>District</label><div>${f('district')}</div></div>
      <div class="field"><label>Lead</label><div>${f('lead')}</div></div>
      <div class="field"><label>Status</label><div>${f('status')}</div></div>
      <div class="field"><label>EMP Code</label><div>${f('emp_code')}</div></div>
      <div class="field"><label>Created</label><div>${f('created_at')}</div></div>
      <div class="field"><label>Updated</label><div>${f('updated_at')}</div></div>
    </div>

    <div style="margin:10px 0 6px 0; font-weight:600;">Full Export</div>
    <div class="table-wrap">
      <table id="modalTable" class="display" style="width:100%">
        <thead>
          <tr>
            <th>SI</th>
            <th>Date</th>
            <th>Name</th>
            <th>Mob-1</th>
            <th>Mob-2</th>
            <th>District</th>
            <th>Lead</th>
            <th>Status</th>
            <th>EMP</th>
            <th>Created</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody>
          ${renderEntryRow(row)}
        </tbody>
      </table>
    </div>
  `;
}

/* ---------- Initialize DataTables on static tables ---------- */
jQuery(function(){
  initDT('#summaryTable');
  initDT('#reportsTable');
});

/* ---------- Click handlers (calendar/range/report) ---------- */
document.addEventListener('click', async function(e){
  const a = e.target.closest('a');
  if(!a) return;

  // Calendar day counts
  if(a.classList.contains('open-list')){
    e.preventDefault();
    const kind = a.dataset.kind, date = a.dataset.date;
    modalTitle.textContent = (kind==='created'?'Created':'Updated') + ' on ' + date;
    modalContent.innerHTML = 'Loading…'; openModal();
    try{
      const res = await fetch(`?ajax=list&kind=${encodeURIComponent(kind)}&date=${encodeURIComponent(date)}`);
      const js  = await res.json();
      if(js.ok){
        modalContent.innerHTML = renderEntryTable(js.rows);
        initDT('#modalTable');
      } else {
        modalContent.innerHTML = 'Error loading entries.';
      }
    }catch(err){
      modalContent.innerHTML = 'Error loading entries.';
    }
    return;
  }

  // Range summary numbers (per user within displayed range)
  if(a.classList.contains('open-range-list')){
    e.preventDefault();
    const kind = a.dataset.kind, emp = a.dataset.emp;
    const card = document.getElementById('rangeCard');
    const from = card.getAttribute('data-from'), to = card.getAttribute('data-to');
    modalTitle.textContent = `${kind==='created'?'Created':'Updated'} • ${emp} • ${from} → ${to}`;
    modalContent.innerHTML = 'Loading…'; openModal();
    try{
      const url = `?ajax=list&kind=${encodeURIComponent(kind)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&emp=${encodeURIComponent(emp)}`;
      const res = await fetch(url);
      const js  = await res.json();
      if(js.ok){
        modalContent.innerHTML = renderEntryTable(js.rows);
        initDT('#modalTable'); // export buttons for agent-wise entries
      } else {
        modalContent.innerHTML = 'Error loading entries.';
      }
    }catch(err){
      modalContent.innerHTML = 'Error loading entries.';
    }
    return;
  }

  // “View” single record
  if(a.classList.contains('view-one')){
    e.preventDefault();
    const si = a.dataset.si;
    modalTitle.textContent = 'Entry SI: ' + si;
    modalContent.innerHTML = 'Loading…'; openModal();
    try{
      const res = await fetch(`?ajax=one&si=${encodeURIComponent(si)}`);
      const js  = await res.json();
      if(js.ok){
        modalContent.innerHTML = renderEntryOne(js.row);
        initDT('#modalTable'); // export single row too
      } else {
        modalContent.innerHTML = 'Error loading entry.';
      }
    }catch(err){
      modalContent.innerHTML = 'Error loading entry.';
    }
    return;
  }
});
</script>
</body>
</html>
