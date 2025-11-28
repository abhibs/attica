<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("dbConnection.php"); // must define $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

$type       = $_SESSION['usertype']       ?? '';
$empId      = $_SESSION['login_username'] ?? '';
$empNm      = $_SESSION['employeeName']   ?? '';
$employeeId = $_SESSION['employeeId']     ?? '';
$today      = date('Y-m-d');

if (!isset($_SESSION['CCUsertype'])) {
    $_SESSION['CCUsertype'] = '';
}

if (!empty($empId)) {
    if ($stmt = $con->prepare("SELECT agent FROM users WHERE employeeId = ? LIMIT 1")) {
        $stmt->bind_param("s", $empId);
        if ($stmt->execute()) {
            $stmt->bind_result($agent);
            if ($stmt->fetch()) {
                $_SESSION['CCUsertype'] = trim((string)$agent);
            } else {
                $_SESSION['CCUsertype'] = '';
            }
        }
        $stmt->close();
    }
}

/* ------------------------------
   Helpers
------------------------------- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function postv($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
function sp($v){ $v = trim((string)$v); return ($v === '') ? ' ' : $v; }
function num($v){ $t = trim((string)$v); $t = preg_replace('/[^0-9.]/', '', $t); return ($t === '' ? '0' : $t); }
function safe_redirect($url){
    if (!headers_sent()) {
        header("Location: $url", true, 303);
        exit;
    }
    echo '<script>location.replace('.json_encode($url).');</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url='.htmlspecialchars($url, ENT_QUOTES).'"></noscript>';
    exit;
}

/* ------------------------------
   Fetch latest Gold / Silver rates from ccgold
------------------------------- */
$goldRate   = '';
$silverRate = '';

$resGold = mysqli_query(
    $con,
    "SELECT `rate` FROM `ccgold`
     WHERE `type`='Gold'
     ORDER BY `date` DESC, `time` DESC LIMIT 1"
);
if ($resGold && ($r = mysqli_fetch_assoc($resGold))) {
    $goldRate = (string)$r['rate'];
}

$resSilver = mysqli_query(
    $con,
    "SELECT `rate` FROM `ccgold`
     WHERE `type`='Silver'
     ORDER BY `date` DESC, `time` DESC LIMIT 1"
);
if ($resSilver && ($r = mysqli_fetch_assoc($resSilver))) {
    $silverRate = (string)$r['rate'];
}

/* ------------------------------
   Determine selected row by SI
------------------------------- */
$selected_si = '';
if (isset($_GET['si'])) $selected_si = trim($_GET['si']);
if ($selected_si === '' && isset($_GET['entry_id'])) $selected_si = trim($_GET['entry_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['si']))            $selected_si = trim($_POST['si']);
    elseif (!empty($_POST['entry_id']))  $selected_si = trim($_POST['entry_id']);
}

/* ------------------------------
   INSERT / UPDATE (BEFORE OUTPUT)
------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // raw post values
    $p_date            = postv('date');
    $p_name            = postv('name');
    $p_mob1            = postv('mob1');
    $p_mob2            = postv('mob2');
    $p_location        = postv('location');
    $p_district        = postv('district');
    $p_business_type   = postv('business_type');
    $p_metal_type      = postv('metal_type');
    $p_grams           = postv('grams');
    $p_releasing_amt   = postv('releasing_amount');
    $p_bank_name       = postv('bank_name');
    $p_assigned_branch = postv('assigned_branch');
    $p_online_price    = postv('online_price');
    $p_price_pg        = postv('price_per_gram'); // ignored in calc (auto)
    $p_advertisement   = postv('advertisement');
    $p_emp_name        = postv('emp_name');
    $p_lead            = postv('lead');
    $p_status          = postv('status');

    // remarks 1..15
    // visible text box: "remarks_new" = new remark user typed
    $newRemark = postv('remarks_new');

    // hidden fields carry existing remarks1..15
    $remarks = [];
    for ($i = 1; $i <= 15; $i++) {
        $remarks[$i] = postv('remarks'.$i);  // may be '' or existing text
    }

    // Append new remark into first empty remark slot
    $newRemarkTrim = trim($newRemark);
    if ($newRemarkTrim !== '') {
        $placed = false;
        for ($i = 1; $i <= 15; $i++) {
            if (trim((string)$remarks[$i]) === '') {
                $remarks[$i] = $newRemarkTrim;
                $placed = true;
                break;
            }
        }
        // If all full, overwrite last
        if (!$placed) {
            $remarks[15] = $newRemarkTrim;
        }
    }

    // Apply sp() policy
    for ($i = 1; $i <= 15; $i++) {
        $remarks[$i] = sp($remarks[$i]);
    }

    // enforce blanks policy
    $t_date            = ($p_date === '' ? $today : $p_date);
    $t_name            = sp($p_name);
    $t_mob1            = sp($p_mob1);
    $t_mob2            = sp($p_mob2);
    $t_location        = sp($p_location);
    $t_district        = sp($p_district);
    $t_business_type   = sp($p_business_type);
    $t_metal_type      = sp($p_metal_type);
    $t_bank_name       = sp($p_bank_name);
    $t_assigned_branch = sp($p_assigned_branch);
    $t_advertisement   = sp($p_advertisement);
    // emp_name must store employeeId/login id
    $t_emp_name        = sp($empId);
    $t_lead            = sp($p_lead);
    $t_status          = sp($p_status);

    $t_remarks1  = $remarks[1];
    $t_remarks2  = $remarks[2];
    $t_remarks3  = $remarks[3];
    $t_remarks4  = $remarks[4];
    $t_remarks5  = $remarks[5];
    $t_remarks6  = $remarks[6];
    $t_remarks7  = $remarks[7];
    $t_remarks8  = $remarks[8];
    $t_remarks9  = $remarks[9];
    $t_remarks10 = $remarks[10];
    $t_remarks11 = $remarks[11];
    $t_remarks12 = $remarks[12];
    $t_remarks13 = $remarks[13];
    $t_remarks14 = $remarks[14];
    $t_remarks15 = $remarks[15];

    // numeric normalization
    $t_grams           = num($p_grams);
    $t_releasing_amt   = num($p_releasing_amt);
    $t_online_price    = num($p_online_price);

    // Price per gram is auto-calculated:
    // Price per gram = Grams * Online price - Releasing amount
    // We'll also store the same into `value`.
    $calcVal = (float)$t_grams * (float)$t_online_price - (float)$t_releasing_amt;

   // --- Price per gram (PPG) = Releasing Amount / Grams (only for Release / Physical+Release) ---
$grams = (float)$t_grams;
$rel   = (float)$t_releasing_amt;

if (in_array($t_business_type, ['Release','Physical+Release'], true) && $grams > 0) {
    $t_price_per_gram = sprintf('%.2f', $rel / $grams);
} else {
    // For non-release rows (or grams = 0), keep it NULL
    $t_price_per_gram = null;
}

/* If you keep 'value' for something else, leave it as-is.
   If you want 'value' to match PPG, you can set:
   $t_value = $t_price_per_gram !== null ? $t_price_per_gram : null;
   Otherwise keep your existing meaning for `value`. */
$t_value = (string)num($t_value ?? '0'); // or keep your existing logic for value

    if ($selected_si !== '') {
        // UPDATE your own row
        $sql = "UPDATE `ccexcel` SET
            `date`=?, `name`=?, `mob1`=?, `mob2`=?, `location`=?, `district`=?,
            `business_type`=?, `metal_type`=?, `grams`=?, `releasing_amount`=?, `value`=?, `bank_name`=?,
            `assigned_branch`=?, `online_price`=?, `price_per_gram`=?, `advertisement`=?, `emp_code`=?, `emp_name`=?,
            `lead`=?, `status`=?,
            `remarks1`=?, `remarks2`=?, `remarks3`=?, `remarks4`=?, `remarks5`=?,
            `remarks6`=?, `remarks7`=?, `remarks8`=?, `remarks9`=?, `remarks10`=?,
            `remarks11`=?, `remarks12`=?, `remarks13`=?, `remarks14`=?, `remarks15`=?
            WHERE `SI`=? AND `emp_code`=?";
        if ($stmt = mysqli_prepare($con, $sql)) {
            // 35 strings + int (SI) + string (emp_code)
            $types = str_repeat('s', 35) . 'is';

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                $t_date, $t_name, $t_mob1, $t_mob2, $t_location, $t_district,
                $t_business_type, $t_metal_type, $t_grams, $t_releasing_amt, $t_value, $t_bank_name,
                $t_assigned_branch, $t_online_price, $t_price_per_gram, $t_advertisement, $empId, $t_emp_name,
                $t_lead, $t_status,
                $t_remarks1, $t_remarks2, $t_remarks3, $t_remarks4, $t_remarks5,
                $t_remarks6, $t_remarks7, $t_remarks8, $t_remarks9, $t_remarks10,
                $t_remarks11, $t_remarks12, $t_remarks13, $t_remarks14, $t_remarks15,
                $selected_si, $empId
            );
            $ok = mysqli_stmt_execute($stmt);
            if (!$ok) {
                echo '<div style="color:#b00;font-weight:bold;margin:10px 0">Update error: '.esc(mysqli_stmt_error($stmt)).'</div>';
            }
            mysqli_stmt_close($stmt);
            if ($ok) {
                safe_redirect($_SERVER['PHP_SELF'].'?si='.rawurlencode($selected_si));
            }
        } else {
            echo '<div style="color:#b00;font-weight:bold;margin:10px 0">Update prepare error: '.esc(mysqli_error($con)).'</div>';
        }
    } else {
        // INSERT new
        $sql = "INSERT INTO `ccexcel`
            (`date`,`name`,`mob1`,`mob2`,`location`,`district`,`business_type`,`metal_type`,
             `grams`,`releasing_amount`,`value`,`bank_name`,`assigned_branch`,
             `online_price`,`price_per_gram`,`advertisement`,`emp_code`,`emp_name`,`lead`,`status`,
             `remarks1`,`remarks2`,`remarks3`,`remarks4`,`remarks5`,
             `remarks6`,`remarks7`,`remarks8`,`remarks9`,`remarks10`,
             `remarks11`,`remarks12`,`remarks13`,`remarks14`,`remarks15`)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        // ↑↑↑ 35 columns, 35 placeholders ↑↑↑
        if ($stmt = mysqli_prepare($con, $sql)) {
            // 35 strings
            $types = str_repeat('s', 35);

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                $t_date, $t_name, $t_mob1, $t_mob2, $t_location, $t_district, $t_business_type, $t_metal_type,
                $t_grams, $t_releasing_amt, $t_value, $t_bank_name, $t_assigned_branch,
                $t_online_price, $t_price_per_gram, $t_advertisement, $empId, $t_emp_name, $t_lead, $t_status,
                $t_remarks1, $t_remarks2, $t_remarks3, $t_remarks4, $t_remarks5,
                $t_remarks6, $t_remarks7, $t_remarks8, $t_remarks9, $t_remarks10,
                $t_remarks11, $t_remarks12, $t_remarks13, $t_remarks14, $t_remarks15
            );
            $ok = mysqli_stmt_execute($stmt);
            if (!$ok) {
                echo '<div style="color:#b00;font-weight:bold;margin:10px 0">Insert error: '.esc(mysqli_stmt_error($stmt)).'</div>';
            }
            $new_si = $ok ? mysqli_insert_id($con) : '';
            mysqli_stmt_close($stmt);
            if ($ok) {
                safe_redirect($_SERVER['PHP_SELF'].'?si='.rawurlencode((string)$new_si));
            }
        } else {
            echo '<div style="color:#b00;font-weight:bold;margin:10px 0">Insert prepare error: '.esc(mysqli_error($con)).'</div>';
        }
    }
}

/* ------------------------------
   Auth + header/menu (after POST)
------------------------------- */
if ($type === 'CallCenterUser') {
    include("header.php");
    include("menucallUser.php");
}
else if($type=='Issuecall'){
		include("header.php");
		include("menuissues.php");
	}
else {
    include("logout.php");
    exit();
}

/* ------------------------------
   Fetch entry for autofill
------------------------------- */
$entry = [];
if ($selected_si !== '') {
    $sql = "SELECT * FROM `ccexcel` WHERE `SI` = ? AND `emp_code` = ? LIMIT 1";
    if ($stmt = mysqli_prepare($con, $sql)) {
        $si_int = (int)$selected_si;
        mysqli_stmt_bind_param($stmt, 'is', $si_int, $empId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) $entry = mysqli_fetch_assoc($res) ?: [];
        else echo '<div style="color:#b00;margin:8px 0;"><b>Fetch error:</b> '.esc(mysqli_error($con)).'</div>';
        mysqli_stmt_close($stmt);
    } else {
        echo '<div style="color:#b00;margin:8px 0;"><b>Prepare error:</b> '.esc(mysqli_error($con)).'</div>';
    }
}

/* ------------------------------
   Load branches for dropdown
------------------------------- */
$branches = [];
$brRes = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status = 1 ORDER BY branchName");
if ($brRes) {
    while ($b = mysqli_fetch_assoc($brRes)) {
        $branches[] = $b;
    }
}

/* ------------------------------
   List all entries for this emp
------------------------------- */

/* ------------------------------ 
   Date filter (for listing table)
------------------------------- */
function is_ymd($s){ return is_string($s) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s); }

$today = date('Y-m-d');
$default_from = date('Y-m-d', strtotime('-30 days'));

$from = isset($_GET['from']) && is_ymd($_GET['from']) ? $_GET['from'] : $default_from;
$to   = isset($_GET['to'])   && is_ymd($_GET['to'])   ? $_GET['to']   : $today;

/* keep from <= to */
if ($from > $to) { $tmp=$from; $from=$to; $to=$tmp; }

/* ------------------------------
   List all entries for this emp (+ date filter)
------------------------------- */
$where  = "WHERE `emp_code` = '".mysqli_real_escape_string($con, $empId)."'";
$where .= " AND `date` BETWEEN '".mysqli_real_escape_string($con, $from)."' AND '".mysqli_real_escape_string($con, $to)."'";

$list_sql = "SELECT * FROM `ccexcel` $where ORDER BY `date` DESC, `SI` DESC";
$user_entries_result = mysqli_query($con, $list_sql);

if ($user_entries_result === false) {
    echo '<div style="color:#b00;margin:8px 0;"><b>List query error:</b> '.esc(mysqli_error($con)).'</div>';
}

function mask_phone_display($phone){
    $digits = preg_replace('/\D+/', '', (string)$phone);
    $len    = strlen($digits);
    if ($len <= 3) {
        return $digits; // nothing to mask
    }
    $last3  = substr($digits, -3);
    // mask all but last 3 digits
    return str_repeat('*', max(0, $len - 3)) . $last3;
}


/* Debug counts */
$cnt_all  = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM `ccexcel`"))[0] ?? 0;
$cnt_mine = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM `ccexcel` WHERE `emp_code`='".mysqli_real_escape_string($con,$empId)."'"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Call Center Excel</title>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>

<style>
  #wrapper { background: #f5f5f5; }
  #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 20px; color: #123C69; }
  #wrapper h4 { text-transform: uppercase; font-weight: 600; font-size: 16px; color: #123C69; }
  .text-success { color: #123C69; text-transform: uppercase; font-weight: bold; font-size: 11px; }
  .btn-success { background-color: #123C69; color: #fff; }
  thead { text-transform: uppercase; background-color: #123C69; color: #f2f2f2; font-size: 12px; }
  .panel-box { margin-top: 20px; border: 4px solid #fff; border-radius: 10px; padding: 10px; background:#f6f2ec; }
  .dataTables_empty { text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
  .table-wrap { overflow:auto; }
  .nowrap { white-space: nowrap; }
  /* Let DataTables handle horizontal scroll; keep cells on one line */
  #ccexcelTable { width: 100% !important; }
  #ccexcelTable th, #ccexcelTable td { white-space: nowrap; }
  div.dataTables_wrapper { width: 100%; overflow-x: auto; }
  /* Remove outer overflow that fights DataTables scroll calc */
  .table-wrap { overflow: visible; } /* or just delete the .table-wrap class from the HTML */
  #ccexcelTable tbody tr { cursor: pointer; }
  #ccexcelTable tbody tr.selected { background: #ffe7a3 !important; }
  #tableSearch { width: 280px; max-width: 100%; }
</style>
</head>
<body>
<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel panel-box">
        <div class="panel-heading">
          <h3 class="text-success no-margins">
            <span style="color:#900" class="fa fa-file-text"></span> <b>CALL CENTER EXCEL FORM</b>
            <button id="clearBtn" style="float:right;margin-left:8px" class="btn btn-default">
              <b><i class="fa fa-eraser"></i> CLEAR FORM</b>
            </button>
            <button style="float:right" onclick="window.location.reload();" class="btn btn-success">
              <b><i style="color:#ffcf40" class="fa fa-spinner"></i> RELOAD</b>
            </button>
          </h3>
          <div style="margin-top:6px;font-size:12px;color:#555;">
            Welcome : <?php echo esc($empId); ?></b> |
            Team Total=<b><?php echo (int)$cnt_all; ?></b> |
            Your Entries=<b><?php echo (int)$cnt_mine; ?></b>
            <?php if ($selected_si !== '') { echo " | Selected SI=<b>".esc($selected_si)."</b>"; } ?>
          </div>
        </div>

        <div class="panel-body">
          <form id="ccForm" method="POST" action="<?php echo esc($_SERVER['PHP_SELF']).($selected_si!=='' ? '?si='.rawurlencode($selected_si) : ''); ?>">
            <input type="hidden" name="si" id="si" value="<?php echo esc($selected_si); ?>">

            <div class="row">
              <div class="col-sm-2">
                <label class="text-success">Date</label>
                <input type="date" class="form-control" name="date" id="date" value="<?php echo esc($entry['date'] ?? $today); ?>" required>
              </div>
              <div class="col-sm-2">
                <label class="text-success">Name</label>
                <input type="text" class="form-control" name="name" id="name" value="<?php echo esc($entry['name'] ?? ''); ?>" >
              </div>
              <div class="col-sm-2">
                <label class="text-success">Mobile 1</label>
                <input type="text" class="form-control" name="mob1" id="mob1" value="<?php echo esc($entry['mob1'] ?? ''); ?>" required>
              </div>
              <div class="col-sm-2">
                <label class="text-success">Mobile 2</label>
                <input type="text" class="form-control" name="mob2" id="mob2" value="<?php echo esc($entry['mob2'] ?? ''); ?>">
              </div>
              <div class="col-sm-2">
                <label class="text-success">Location</label>
                <input type="text" class="form-control" name="location" id="location" value="<?php echo esc($entry['location'] ?? ''); ?>" >
              </div>
              <div class="col-sm-2">
                <label class="text-success">District</label>
                <?php $dist = $entry['district'] ?? ''; ?>
                <select class="form-control" name="district" id="district">
                  <option value="">-- Select --</option>
                  <option value="Bangalore"       <?php echo ($dist==='Bangalore'      ?'selected':''); ?>>Bangalore</option>
                  <option value="Karnataka"       <?php echo ($dist==='Karnataka'      ?'selected':''); ?>>Karnataka</option>
                  <option value="Chennai"         <?php echo ($dist==='Chennai'        ?'selected':''); ?>>Chennai</option>
                  <option value="Tamil Nadu"      <?php echo ($dist==='Tamil Nadu'     ?'selected':''); ?>>Tamil Nadu</option>
                  <option value="Hyderabad"       <?php echo ($dist==='Hyderabad'      ?'selected':''); ?>>Hyderabad</option>
                  <option value="Andhra Pradesh"  <?php echo ($dist==='Andhra Pradesh' ?'selected':''); ?>>Andhra Pradesh</option>
                  <option value="Telangana"       <?php echo ($dist==='Telangana'      ?'selected':''); ?>>Telangana</option>
                </select>
              </div>

              <div class="col-sm-2">
                <label class="text-success">Type of Business</label>
                <?php $bt = $entry['business_type'] ?? ''; ?>
                <select class="form-control" name="business_type" id="business_type" >
                  <option value="Physical"          <?php echo ($bt==='Physical'         ?'selected':''); ?>>Physical</option>
                  <option value="Release"           <?php echo ($bt==='Release'          ?'selected':''); ?>>Release</option>
                  <option value="Pledge"            <?php echo ($bt==='Pledge'           ?'selected':''); ?>>Pledge</option>
                  <option value="Physical+Release"  <?php echo ($bt==='Physical+Release' ?'selected':''); ?>>Physical+Release</option>
                </select>
              </div>

              <div class="col-sm-2">
                <label class="text-success">Metal Type</label>
                <?php $mt = $entry['metal_type'] ?? ''; ?>
                <select class="form-control" name="metal_type" id="metal_type">
                  <option value="" disabled <?php echo ($mt===''?'selected':''); ?>>Select Metal Type</option>
                  <option value="Gold"   <?php echo ($mt==='Gold'  ?'selected':''); ?>>Gold</option>
                  <option value="Silver" <?php echo ($mt==='Silver'?'selected':''); ?>>Silver</option>
                </select>
              </div>

              <div class="col-sm-2">
                <label class="text-success">Grams</label>
                <input type="text" class="form-control" name="grams" id="grams" value="<?php echo esc($entry['grams'] ?? ''); ?>" >
              </div>

              <div class="col-sm-2" id="wrap_releasing_amount">
                <label class="text-success">Releasing Amount</label>
                <input type="text" class="form-control" name="releasing_amount" id="releasing_amount" value="<?php echo esc($entry['releasing_amount'] ?? ''); ?>">
              </div>

              <div class="col-sm-2" id="wrap_bank_name">
                <label class="text-success">Bank Name / Pledge Place</label>
                <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?php echo esc($entry['bank_name'] ?? ''); ?>">
              </div>

              <div class="col-sm-2">
                <label class="text-success">Assigned to AGPL Branch</label>
                <?php $ab = $entry['assigned_branch'] ?? ''; ?>
                <select class="form-control" name="assigned_branch" id="assigned_branch">
                  <option value="">-- Select --</option>
                  <?php foreach ($branches as $b):
                      $val = $b['branchName'].'('.$b['branchId'].')';
                  ?>
                    <option value="<?php echo esc($val); ?>" <?php echo ($ab === $val ? 'selected':''); ?>>
                      <?php echo esc($val); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <?php
                // default online price auto from ccgold ONLY on new record
                $onlineDefault = $entry['online_price'] ?? '';
                if ($onlineDefault === '') {
                    if ($mt === 'Gold' && $goldRate !== '')   $onlineDefault = $goldRate;
                    if ($mt === 'Silver' && $silverRate !== '') $onlineDefault = $silverRate;
                }
              ?>
              <div class="col-sm-2">
                <label class="text-success">Online Price</label>
                <input type="text" class="form-control" name="online_price" id="online_price"
                       value="<?php echo esc($onlineDefault); ?>">
              </div>

              <div class="col-sm-2" id="wrap_price_per_gram">
                <label class="text-success">Price per gram</label>
                <input type="text" class="form-control" name="price_per_gram" id="price_per_gram"
                       value="<?php echo esc($entry['price_per_gram'] ?? ''); ?>" readonly>
              </div>

              <div class="col-sm-2">
                <label class="text-success">Advertisement</label>
                <?php $ad = $entry['advertisement'] ?? ''; ?>
                <select class="form-control" name="advertisement" id="advertisement">
                  <option value="">-- Select --</option>
                  <?php
                  $advList = ['TV','Bus','Friends','Website','Google','Existing','Whatsapp','Instagram','Socialmedia','Newspaper','LED Screen','Youtube','Facebook'];
                  foreach ($advList as $opt):
                  ?>
                    <option value="<?php echo esc($opt); ?>" <?php echo ($ad===$opt ? 'selected':''); ?>>
                      <?php echo esc($opt); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-sm-2">
                <label class="text-success">EMP Code</label>
                <input type="text" class="form-control" name="emp_code" id="emp_code" value="<?php echo esc($empId); ?>" readonly>
              </div>
              <div class="col-sm-2" style="display:none">
                <label class="text-success">EMP Name</label>
                <input type="text" class="form-control" name="emp_name" id="emp_name" value="  ">
              </div>

              <div class="col-sm-2">
                <label class="text-success">Lead</label>
                <?php $ld = $entry['lead'] ?? ''; ?>
                <select class="form-control" name="lead" id="lead">
                  <option value="Hot"   <?php echo ($ld==='Hot'  ?'selected':''); ?>>Hot</option>
                  <option value="Warm"  <?php echo ($ld==='Warm' ?'selected':''); ?>>Warm</option>
                  <option value="Cold"  <?php echo ($ld==='Cold' ?'selected':''); ?>>Cold</option>
                </select>
              </div>

              <div class="col-sm-2">
                <label class="text-success">Status</label>
                <?php $st = $entry['status'] ?? ''; ?>
                <select class="form-control" name="status" id="status">
                  <option value="">-- Select --</option>
                  <?php
                  $statusList = [
                    'Visited Sold',
                    'Visited Not Sold',
                    'Planning to Visit',
                    'Pending',
                    'RNR',
                    'Enquiry Call',
                    "Can't Send Executive",
                    'Not Interested',
                    'Not Feasible',
                    'Pledge',
                    'Re-Pledge',
                    'Job Enquiry',
                    'Advertisement Call',
                    'Wrong Call',
                    'Others'
                  ];
                  foreach ($statusList as $opt):
                  ?>
                    <option value="<?php echo esc($opt); ?>" <?php echo ($st===$opt ? 'selected':''); ?>>
                      <?php echo esc($opt); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <?php
              // Always keep all 15 remarks in hidden fields (existing values)
              for ($i = 1; $i <= 15; $i++):
                $val = $entry["remarks{$i}"] ?? '';
              ?>
                <input type="hidden"
                       name="remarks<?php echo $i; ?>"
                       id="remarks<?php echo $i; ?>"
                       value="<?php echo esc($val); ?>">
              <?php endfor; ?>

              <!-- Single visible field = new remark to append -->
              <div class="col-sm-2">
                <label class="text-success">Remarks</label>
                <input type="text" class="form-control" name="remarks_new" id="remarks_new" value="">
              </div>

            <div class="col-sm-12 text-center">
              <br><br>
              <button class="btn btn-success" type="submit" id="submitBtn" name="submit">
                <?php echo ($selected_si!=='' ? 'Update Entry' : 'Submit Form'); ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Existing Entries (All Columns) -->
      <div class="hpanel panel-box" style="margin-top:15px">
        <div class="panel-heading">
          <h3 class="text-success no-margins"><b>Existing Entries (Your EMP Code)</b></h3>
        </div>
        <div class="panel-heading" style="padding-top:10px">
  <form method="get" class="form-inline" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <label class="text-success" style="margin-right:6px">Filter by Date:</label>
    <input type="date" class="form-control" name="from" value="<?php echo esc($from); ?>">
    <span>to</span>
    <input type="date" class="form-control" name="to"   value="<?php echo esc($to); ?>">
    <button class="btn btn-success" type="submit"><b>Apply</b></button>
    <a class="btn btn-default" href="<?php echo esc($_SERVER['PHP_SELF']); ?>"><b>Clear</b></a>
  </form>
</div>

        <div class="panel-body table-wrap">
          <?php if ($user_entries_result === false): ?>
            <div class="dataTables_empty">Query error – see message above.</div>
          <?php else: ?>
            <table id="ccexcelTable" class="display cell-border compact" style="width:100%">
              <thead>
                <tr>
                  <th>SI</th>
                  <th>Date</th>
                  <th>Name</th>
                  <th>Mob-1</th>
                  <th>Mob-2</th>
                  <th>Location</th>
                  <th>District</th>
                  <th>Business Type</th>
                  <th>Metal Type</th>
                  <th>Grams</th>
                  <th>Releasing Amount</th>
                  <th>Value</th>
                  <th>Bank Name</th>
                  <th>Assigned Branch</th>
                  <th>Online Price</th>
                  <th>Price per gram</th>
                  <th>Advertisement</th>
                  <th>Lead</th>
                  <th>Status</th>
                  <th>Remarks1</th>
                  <th>Remarks2</th>
                  <th>Remarks3</th>
                  <th>Remarks4</th>
                  <th>Remarks5</th>
                  <th>Remarks6</th>
                  <th>Remarks7</th>
                  <th>Remarks8</th>
                  <th>Remarks9</th>
                  <th>Remarks10</th>
                  <th>Remarks11</th>
                  <th>Remarks12</th>
                  <th>Remarks13</th>
                  <th>Remarks14</th>
                  <th>Remarks15</th>
                  <th>Created At</th>
                  <th>Updated At</th>
                </tr>
              </thead>
              <tbody>
              <?php if (mysqli_num_rows($user_entries_result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($user_entries_result)) { ?>
                  <tr>
                    <td><?php echo esc($row['SI']); ?></td>
                    <td><?php echo esc($row['date']); ?></td>
                    <td><?php echo esc($row['name']); ?></td>
                        <?php
                          $mob1_full   = $row['mob1'] ?? '';
                          $mob2_full   = $row['mob2'] ?? '';
                          $mob1_masked = mask_phone_display($mob1_full);
                          $mob2_masked = mask_phone_display($mob2_full);
                        ?>
                    <td class="phone-cell" data-full="<?php echo esc($mob1_full); ?>" data-search="<?php echo esc($mob1_full); ?>" data-order="<?php echo esc($mob1_full); ?>"> <?php echo esc($mob1_masked); ?> </td>
                    <td class="phone-cell" data-full="<?php echo esc($mob2_full); ?>" data-search="<?php echo esc($mob2_full); ?>" data-order="<?php echo esc($mob2_full); ?>"> <?php echo esc($mob2_masked); ?> </td>
                    <td><?php echo esc($row['location']); ?></td>
                    <td><?php echo esc($row['district']); ?></td>
                    <td><?php echo esc($row['business_type']); ?></td>
                    <td><?php echo esc($row['metal_type']); ?></td>
                    <td><?php echo esc($row['grams']); ?></td>
                    <td><?php echo esc($row['releasing_amount']); ?></td>
                    <td><?php echo esc($row['value']); ?></td>
                    <td><?php echo esc($row['bank_name']); ?></td>
                    <td><?php echo esc($row['assigned_branch']); ?></td>
                    <td><?php echo esc($row['online_price']); ?></td>
                    <td><?php echo esc($row['price_per_gram']); ?></td>
                    <td><?php echo esc($row['advertisement']); ?></td>
                    <td><?php echo esc($row['lead']); ?></td>
                    <td><?php echo esc($row['status']); ?></td>
                    <td><?php echo esc($row['remarks1']); ?></td>
                    <td><?php echo esc($row['remarks2']); ?></td>
                    <td><?php echo esc($row['remarks3']); ?></td>
                    <td><?php echo esc($row['remarks4']); ?></td>
                    <td><?php echo esc($row['remarks5']); ?></td>
                    <td><?php echo esc($row['remarks6']); ?></td>
                    <td><?php echo esc($row['remarks7']); ?></td>
                    <td><?php echo esc($row['remarks8']); ?></td>
                    <td><?php echo esc($row['remarks9']); ?></td>
                    <td><?php echo esc($row['remarks10']); ?></td>
                    <td><?php echo esc($row['remarks11']); ?></td>
                    <td><?php echo esc($row['remarks12']); ?></td>
                    <td><?php echo esc($row['remarks13']); ?></td>
                    <td><?php echo esc($row['remarks14']); ?></td>
                    <td><?php echo esc($row['remarks15']); ?></td>
                    <td><?php echo esc($row['created_at']); ?></td>
                    <td><?php echo esc($row['updated_at']); ?></td>
                  </tr>
                <?php } ?>
              <?php else: ?>
                <tr><td colspan="36" class="dataTables_empty">No entries found for your EMP Code</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
  <?php include("footer.php"); ?>
</div>
<script>
(function () {
  function norm(v){ if (v == null) return ''; v = (''+v).trim(); return (v === ' ') ? '' : v; }
  function todayISO(){
    var d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0,10);
  }
  function setFormActionWithSI(si){
    var base = location.protocol + '//' + location.host + location.pathname;
    var url  = si ? (base + '?si=' + encodeURIComponent(si)) : base;
    history.replaceState({}, '', url);
    var frm = document.getElementById('ccForm');
    if (frm) frm.setAttribute('action', url);
  }

  // latest rates from PHP
  var GOLD_RATE   = <?php echo json_encode($goldRate); ?>;
  var SILVER_RATE = <?php echo json_encode($silverRate); ?>;

var table = new DataTable('#ccexcelTable', {
  order: [[1,'desc'], [0,'desc']],
  pageLength: 25,
  stateSave: true,
  responsive: false,   // turn off to avoid width miscalc with many columns
  fixedHeader: false,  // turn off to prevent header width mismatch
  scrollX: true,       // use native DT horizontal scroll
  autoWidth: false
});
/* Adjust columns once layout is painted */
setTimeout(function(){ table.columns.adjust(); }, 0);
window.addEventListener('resize', function(){ table.columns.adjust(); });


  var searchInput = document.getElementById('tableSearch');
  if (searchInput){
    searchInput.addEventListener('keyup', function(){
      table.search(this.value).draw();
    });
  }

  const formMap = {
    si:               document.getElementById('si'),
    date:             document.getElementById('date'),
    name:             document.getElementById('name'),
    mob1:             document.getElementById('mob1'),
    mob2:             document.getElementById('mob2'),
    location:         document.getElementById('location'),
    district:         document.getElementById('district'),
    business_type:    document.getElementById('business_type'),
    metal_type:       document.getElementById('metal_type'),
    grams:            document.getElementById('grams'),
    releasing_amount: document.getElementById('releasing_amount'),
    bank_name:        document.getElementById('bank_name'),
    assigned_branch:  document.getElementById('assigned_branch'),
    online_price:     document.getElementById('online_price'),
    price_per_gram:   document.getElementById('price_per_gram'),
    advertisement:    document.getElementById('advertisement'),
    lead:             document.getElementById('lead'),
    status:           document.getElementById('status'),
    remarks_new:      document.getElementById('remarks_new'),
    remarks1:         document.getElementById('remarks1'),
    remarks2:         document.getElementById('remarks2'),
    remarks3:         document.getElementById('remarks3'),
    remarks4:         document.getElementById('remarks4'),
    remarks5:         document.getElementById('remarks5'),
    remarks6:         document.getElementById('remarks6'),
    remarks7:         document.getElementById('remarks7'),
    remarks8:         document.getElementById('remarks8'),
    remarks9:         document.getElementById('remarks9'),
    remarks10:        document.getElementById('remarks10'),
    remarks11:        document.getElementById('remarks11'),
    remarks12:        document.getElementById('remarks12'),
    remarks13:        document.getElementById('remarks13'),
    remarks14:        document.getElementById('remarks14'),
    remarks15:        document.getElementById('remarks15'),

    submitBtn:        document.getElementById('submitBtn')
  };

  const COL = {
    SI:0, DATE:1, NAME:2, MOB1:3, MOB2:4, LOCATION:5, DISTRICT:6,
    BTYPE:7, METAL:8, GRAMS:9, REL_AMT:10, VALUE:11,
    BANK:12, ASSIGNED:13, OPRICE:14, PPG:15, AD:16,
    LEAD:17, STATUS:18, R1:19, R2:20, R3:21, R4:22, R5:23,
    R6:24, R7:25, R8:26, R9:27, R10:28, R11:29, R12:30, R13:31, R14:32, R15:33,
    CREATED:34, UPDATED:35
  };

  function highlightOnly(tr){
    table.rows().nodes().forEach(function(r){ r.classList.remove('selected'); });
    tr.classList.add('selected');
  }

  function isReleaseType(v){
    return v === 'Release' || v === 'Physical+Release';
  }
  function getFullPhoneFromCell(tr, colIndex){
    if (!tr) return '';
    var cells = tr.querySelectorAll('td');
    if (!cells || !cells[colIndex]) return '';
    var cell = cells[colIndex];
    var full = cell.getAttribute('data-full');
    if (full != null && full !== '') return full.trim();
    return norm(cell.textContent || '');
  }

  var wrapReleaseAmt = document.getElementById('wrap_releasing_amount');
  var wrapBank       = document.getElementById('wrap_bank_name');
  var wrapPPG        = document.getElementById('wrap_price_per_gram');

  // auto-calc price per gram: Grams * Online Price - Releasing Amount
  // PPG = Releasing Amount / Grams (only for Release / Physical+Release)
function recalcPricePerGram(){
  if (!formMap.price_per_gram) return;

  // Only applicable for Release types
  var bt = formMap.business_type ? formMap.business_type.value : '';
  var isRelease = (bt === 'Release' || bt === 'Physical+Release');

  if (!isRelease) {
    formMap.price_per_gram.value = '';
    return;
  }

  var g  = parseFloat(formMap.grams && formMap.grams.value ? formMap.grams.value : '0') || 0;
  var ra = parseFloat(formMap.releasing_amount && formMap.releasing_amount.value ? formMap.releasing_amount.value : '0') || 0;

  if (g > 0) {
    var ppg = ra / g;
    formMap.price_per_gram.value = isFinite(ppg) ? ppg.toFixed(2) : '';
  } else {
    formMap.price_per_gram.value = '';
  }
}


  // auto-fill online price from ccgold based on metal type (always update)
  function applyMetalRate(){
    if (!formMap.metal_type || !formMap.online_price) return;
    var rate = '';
    if (formMap.metal_type.value === 'Gold')  rate = GOLD_RATE || '';
    if (formMap.metal_type.value === 'Silver') rate = SILVER_RATE || '';
    if (rate) {
      formMap.online_price.value = rate;
      recalcPricePerGram();
    }
  }

  function toggleReleaseFields(){
    if (!formMap.business_type) return;
    var v = formMap.business_type.value;
    var show = isReleaseType(v);
    [wrapReleaseAmt, wrapBank, wrapPPG].forEach(function(el){
      if (!el) return;
      el.style.display = show ? '' : 'none';
    });
    if (!show) {
      if (formMap.releasing_amount) formMap.releasing_amount.value = '';
      if (formMap.bank_name)        formMap.bank_name.value = '';
      if (formMap.price_per_gram)   formMap.price_per_gram.value = '';
    }
    recalcPricePerGram();
  }

    function copyTextToClipboard(text){
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function(){
        alert('Mobile number copied to clipboard');
      }).catch(function(){
        // fallback
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch(e){}
        document.body.removeChild(ta);
        alert('Mobile number copied to clipboard');
      });
    } else {
      // old browsers fallback
      var ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); } catch(e){}
      document.body.removeChild(ta);
      alert('Mobile number copied to clipboard');
    }
  }

  function fillFormFromRow(tr){
    var row = table.row(tr);
    if (!row || !row.any()) return;
    var d = row.data();

    formMap.si.value               = norm(d[COL.SI]);
    formMap.date.value             = norm(d[COL.DATE]) || todayISO();
    formMap.name.value             = norm(d[COL.NAME]);
    var fullMob1 = getFullPhoneFromCell(tr, COL.MOB1);
    var fullMob2 = getFullPhoneFromCell(tr, COL.MOB2);
    formMap.mob1.value             = fullMob1;
    formMap.mob2.value             = fullMob2;
    formMap.location.value         = norm(d[COL.LOCATION]);
    formMap.district.value         = norm(d[COL.DISTRICT]);
    formMap.business_type.value    = norm(d[COL.BTYPE]) || 'Physical';
    formMap.metal_type.value       = norm(d[COL.METAL]) || '';
    formMap.grams.value            = norm(d[COL.GRAMS]);
    formMap.releasing_amount.value = norm(d[COL.REL_AMT]);
    formMap.bank_name.value        = norm(d[COL.BANK]);
    formMap.assigned_branch.value  = norm(d[COL.ASSIGNED]);
    formMap.online_price.value     = norm(d[COL.OPRICE]);
    formMap.price_per_gram.value   = norm(d[COL.PPG]);
    formMap.advertisement.value    = norm(d[COL.AD]);

    var leadVal = norm(d[COL.LEAD]);
    if (!leadVal || ['Hot','Warm','Cold',' '].indexOf(leadVal) === -1) leadVal = 'Cold';
    formMap.lead.value             = leadVal;

    formMap.status.value           = norm(d[COL.STATUS]);

    // Hidden existing remarks1..15 from row
    if (formMap.remarks1)  formMap.remarks1.value  = norm(d[COL.R1]);
    if (formMap.remarks2)  formMap.remarks2.value  = norm(d[COL.R2]);
    if (formMap.remarks3)  formMap.remarks3.value  = norm(d[COL.R3]);
    if (formMap.remarks4)  formMap.remarks4.value  = norm(d[COL.R4]);
    if (formMap.remarks5)  formMap.remarks5.value  = norm(d[COL.R5]);
    if (formMap.remarks6)  formMap.remarks6.value  = norm(d[COL.R6]);
    if (formMap.remarks7)  formMap.remarks7.value  = norm(d[COL.R7]);
    if (formMap.remarks8)  formMap.remarks8.value  = norm(d[COL.R8]);
    if (formMap.remarks9)  formMap.remarks9.value  = norm(d[COL.R9]);
    if (formMap.remarks10) formMap.remarks10.value = norm(d[COL.R10]);
    if (formMap.remarks11) formMap.remarks11.value = norm(d[COL.R11]);
    if (formMap.remarks12) formMap.remarks12.value = norm(d[COL.R12]);
    if (formMap.remarks13) formMap.remarks13.value = norm(d[COL.R13]);
    if (formMap.remarks14) formMap.remarks14.value = norm(d[COL.R14]);
    if (formMap.remarks15) formMap.remarks15.value = norm(d[COL.R15]);

    // visible "new remark" is always blank so user adds a fresh one
    if (formMap.remarks_new) formMap.remarks_new.value = '';

    formMap.submitBtn.innerText = 'Update Entry';
    setFormActionWithSI(formMap.si.value);

    highlightOnly(tr);
    toggleReleaseFields();
    recalcPricePerGram();
    formMap.name.focus();
    document.getElementById('ccForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  $(document).on('click', '#ccexcelTable tbody tr', function () {
    fillFormFromRow(this);
  });
// Click on masked phone cell = copy real number to clipboard
  $(document).on('click', '#ccexcelTable tbody td.phone-cell', function (e) {
    var full = this.getAttribute('data-full') || '';
    if (full) {
      copyTextToClipboard(full);
    }
    // we do NOT stopPropagation – row-click still works to load the form
  });
  function clearForm(){
    formMap.si.value            = '';
    formMap.submitBtn.innerText = 'Submit Form';

    formMap.date.value             = todayISO();
    formMap.name.value             = '';
    formMap.mob1.value             = '';
    formMap.mob2.value             = '';
    formMap.location.value         = '';
    formMap.district.value         = '';
    formMap.business_type.value    = 'Physical';
    formMap.metal_type.value       = ''; // back to placeholder
    formMap.grams.value            = '';
    if (formMap.releasing_amount) formMap.releasing_amount.value = '';
    if (formMap.bank_name)        formMap.bank_name.value        = '';
    formMap.assigned_branch.value  = '';
    formMap.online_price.value     = '';
    if (formMap.price_per_gram)   formMap.price_per_gram.value   = '';
    formMap.advertisement.value    = '';
    formMap.lead.value             = 'Cold';
    formMap.status.value           = '';
    if (formMap.remarks_new) formMap.remarks_new.value = '';
    if (formMap.remarks1)  formMap.remarks1.value  = '';
    if (formMap.remarks2)  formMap.remarks2.value  = '';
    if (formMap.remarks3)  formMap.remarks3.value  = '';
    if (formMap.remarks4)  formMap.remarks4.value  = '';
    if (formMap.remarks5)  formMap.remarks5.value  = '';
    if (formMap.remarks6)  formMap.remarks6.value  = '';
    if (formMap.remarks7)  formMap.remarks7.value  = '';
    if (formMap.remarks8)  formMap.remarks8.value  = '';
    if (formMap.remarks9)  formMap.remarks9.value  = '';
    if (formMap.remarks10) formMap.remarks10.value = '';
    if (formMap.remarks11) formMap.remarks11.value = '';
    if (formMap.remarks12) formMap.remarks12.value = '';
    if (formMap.remarks13) formMap.remarks13.value = '';
    if (formMap.remarks14) formMap.remarks14.value = '';
    if (formMap.remarks15) formMap.remarks15.value = '';

    setFormActionWithSI('');
    table.rows().nodes().forEach(function(r){ r.classList.remove('selected'); });

    toggleReleaseFields();
    applyMetalRate();
    recalcPricePerGram();
    formMap.name.focus();
  }

  $(document).on('click', '#clearBtn', function (e) {
    e.preventDefault();
    clearForm();
  });


  // event wiring
  if (formMap.metal_type) {
    formMap.metal_type.addEventListener('change', applyMetalRate);
  }
  if (formMap.business_type) {
    formMap.business_type.addEventListener('change', function(){
      toggleReleaseFields();
    });
  }
  if (formMap.releasing_amount) {
    formMap.releasing_amount.addEventListener('keyup', recalcPricePerGram);
  }
  if (formMap.grams) {
    formMap.grams.addEventListener('keyup', recalcPricePerGram);
  }
  if (formMap.online_price) {
    formMap.online_price.addEventListener('keyup', recalcPricePerGram);
  }

  // initial state
  toggleReleaseFields();
  applyMetalRate();
  recalcPricePerGram();

})();
  

</script>
</body>
</html>
