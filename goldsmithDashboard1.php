<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
date_default_timezone_set('Asia/Kolkata');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include('dbConnection.php'); // $con
@mysqli_set_charset($con, 'utf8mb4');

/* -----------------------------
   Helpers & small utils
----------------------------- */
function set_flash($msg){ $_SESSION['__flash_msg'] = $msg; }
function get_flash(){
  if (!empty($_SESSION['__flash_msg'])) { $m = $_SESSION['__flash_msg']; unset($_SESSION['__flash_msg']); return $m; }
  return '';
}
if (empty($_SESSION['smelter_form_nonce'])) {
  $_SESSION['smelter_form_nonce'] = bin2hex(random_bytes(16));
}
function col_exists(mysqli $con, string $table, string $col): bool {
  $table = str_replace('`','',$table);
  $col   = str_replace('`','',$col);
  $like  = mysqli_real_escape_string($con, $col);
  $rs    = mysqli_query($con, "SHOW COLUMNS FROM `{$table}` LIKE '{$like}'");
  return ($rs && mysqli_num_rows($rs) > 0);
}
function ensure_dir($dir) {
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  return is_dir($dir) && is_writable($dir);
}
function save_upload($fileKey, $destDir) {
  if (empty($_FILES[$fileKey]['name'])) return '';
  if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return '';
  $maxSize = 10 * 1024 * 1024; // 10 MB
  if ($_FILES[$fileKey]['size'] > $maxSize) return '';
  $allowed = ['jpg','jpeg','png','pdf'];
  $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed, true)) return '';
  if (!ensure_dir($destDir)) return '';
  $baseSafe = preg_replace('/[^a-zA-Z0-9_\-]/','_', pathinfo($_FILES[$fileKey]['name'], PATHINFO_FILENAME));
  $fname = time() . "_" . $baseSafe . "." . $ext;
  $target = rtrim($destDir, '/').'/'.$fname;
  if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target)) {
    return str_replace(__DIR__.'/', '', $target); // relative path
  }
  return '';
}
function numval($v) { return (float)str_replace(',', '', trim((string)$v)); }
function mismatch_flag(
  $from_branch, $branchId,
  $branch_gross, $branch_net, $branch_purity,
  $before_wt,   $before_netwt, $before_purity,
  $tol = 0.0005
){
  if ($from_branch !== '' && $branchId !== '' && $from_branch !== $branchId) return 'yes';
  if ($before_wt !== ''   && $branch_gross   !== '' && abs(numval($before_wt)    - numval($branch_gross))   > $tol) return 'yes';
  if ($before_netwt !== ''&& $branch_net     !== '' && abs(numval($before_netwt) - numval($branch_net))     > $tol) return 'yes';
  if ($before_purity !== ''&& $branch_purity !== '' && abs(numval($before_purity)- numval($branch_purity))  > $tol) return 'yes';
  return 'no';
}

/* -----------------------------
   Ensure smelter columns exist
----------------------------- */
try {
  if (!col_exists($con, 'smelter', 'mismatch'))      mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `mismatch` ENUM('yes','no') DEFAULT 'no'");
  if (!col_exists($con, 'smelter', 'updated_at'))    mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `updated_at` DATETIME NULL");
  if (!col_exists($con, 'smelter', 'updated_by'))    mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `updated_by` VARCHAR(100) NULL");
  if (!col_exists($con, 'smelter', 'before_netwt'))  mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `before_netwt` DECIMAL(10,3) NULL");
  if (!col_exists($con, 'smelter', 'after_netwt'))   mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `after_netwt` DECIMAL(10,3) NULL");
  if (!col_exists($con, 'smelter', 'before_img'))    mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `before_img` VARCHAR(255) NULL");
  if (!col_exists($con, 'smelter', 'after_img'))     mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `after_img` VARCHAR(255) NULL");
  if (!col_exists($con, 'smelter', 'branch_gross'))  mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `branch_gross` DECIMAL(10,3) NULL");
  if (!col_exists($con, 'smelter', 'branch_net'))    mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `branch_net` DECIMAL(10,3) NULL");
  if (!col_exists($con, 'smelter', 'branch_purity')) mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `branch_purity` DECIMAL(10,3) NULL");
  if (!col_exists($con, 'smelter', 'branch_name'))   mysqli_query($con, "ALTER TABLE `smelter` ADD COLUMN `branch_name` VARCHAR(150) NULL");
} catch (Throwable $e) {
  // continue
}

/* =========================================================
   POST HANDLER (PRG) — before any output
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nonce = $_POST['nonce'] ?? '';
  if (empty($_SESSION['smelter_form_nonce']) || !hash_equals($_SESSION['smelter_form_nonce'], $nonce)) {
    set_flash('Invalid or duplicate submission. Please try again.');
    header('Location: '.$_SERVER['PHP_SELF']); exit;
  }
  $_SESSION['smelter_form_nonce'] = bin2hex(random_bytes(16)); // rotate

  $mode           = trim($_POST['mode'] ?? 'create'); // create | update_after
  $update_id      = (int)($_POST['update_id'] ?? 0);

  // bill/phone; packet_no = billId-phone-branchId
  $bill_id        = trim($_POST['bill_id'] ?? '');
  $phone          = trim($_POST['phone']   ?? '');

  $packet_no      = trim($_POST['packet_no'] ?? '');   // set by JS
  $from_branch    = trim($_POST['from_branch'] ?? ''); // branchId
  $before_purity  = trim($_POST['before_purity'] ?? '');
  $before_wt_raw  = trim($_POST['before_wt'] ?? '');
  $before_netwt   = trim($_POST['before_netwt'] ?? '');
  $after_purity   = trim($_POST['after_purity'] ?? '');
  $after_wt       = trim($_POST['after_wt'] ?? '');
  $after_netwt    = trim($_POST['after_netwt'] ?? '');
  $user           = $_SESSION['login_username'] ?? '';

  try {
    if ($mode === 'update_after' && $update_id > 0) {
      if ($after_purity==='' || $after_wt==='') {
        set_flash('Please enter both After Purity and After Weight.');
        header('Location: '.$_SERVER['PHP_SELF']); exit;
      }

      // Lock to the exact packet_no/from_branch stored on this row
      $qLock = mysqli_prepare($con, "SELECT packet_no, from_branch FROM smelter WHERE id=? LIMIT 1");
      mysqli_stmt_bind_param($qLock, "i", $update_id);
      mysqli_stmt_execute($qLock);
      $rsLock = mysqli_stmt_get_result($qLock);
      $db_packet_no = $db_from_branch = null;
      if ($lk = mysqli_fetch_assoc($rsLock)) {
        $db_packet_no   = $lk['packet_no'];
        $db_from_branch = $lk['from_branch'];
      }
      mysqli_stmt_close($qLock);

      if ($db_packet_no === null) {
        set_flash('Invalid selection: record not found for AFTER update.');
        header('Location: '.$_SERVER['PHP_SELF']); exit;
      }

      $after_img = save_upload('after_img', __DIR__.'/goldsmith/After');

      // Recompute mismatch from stored snapshots + BEFORE values
      $mismatch = 'no';
      $qS = mysqli_prepare($con, "SELECT from_branch, before_wt, before_netwt, before_purity, branch_gross, branch_net, branch_purity FROM smelter WHERE id=? LIMIT 1");
      mysqli_stmt_bind_param($qS, "i", $update_id);
      mysqli_stmt_execute($qS);
      $rsS = mysqli_stmt_get_result($qS);
      if ($srow = mysqli_fetch_assoc($rsS)) {
        $mismatch = mismatch_flag(
          $srow['from_branch'],
          $srow['from_branch'], // compare to itself (branch id mismatch would already be captured on create)
          $srow['branch_gross'],
          $srow['branch_net'],
          $srow['branch_purity'],
          $srow['before_wt'],
          $srow['before_netwt'],
          $srow['before_purity']
        );
      }
      mysqli_stmt_close($qS);

      // UPDATE: never touch packet_no/from_branch here
      $set   = "after_purity=?, after_wt=?, updated_at=NOW(), updated_by=?, mismatch=?";
      $types = "ssss";
      $args  = [$after_purity, $after_wt, $user, $mismatch];

      if ($after_netwt !== '') { $set .= ", after_netwt=?"; $types .= "s"; $args[] = $after_netwt; }
      if ($after_img   !== '') { $set .= ", after_img=?";   $types .= "s"; $args[] = $after_img; }

      $set .= " WHERE id=? LIMIT 1";
      $types .= "i"; $args[] = $update_id;

      $st = mysqli_prepare($con, "UPDATE smelter SET $set");
      mysqli_stmt_bind_param($st, $types, ...$args);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);

      set_flash('AFTER saved. Packet No retained: '.$db_packet_no);
      header('Location: '.$_SERVER['PHP_SELF']); exit;

    } else {
      // CREATE (BEFORE)
      if ($from_branch==='' || $before_wt_raw==='') {
        set_flash('Please fill From Branch and Before Weight (mandatory).');
        header('Location: '.$_SERVER['PHP_SELF']); exit;
      }

      // If packet_no didn't arrive from JS for any reason, build it here.
      if ($packet_no === '' && $bill_id !== '' && $phone !== '' && $from_branch !== '') {
        $packet_no = $bill_id . '-' . $phone . '-' . $from_branch;
      }

      $before_img = save_upload('before_img', __DIR__.'/goldsmith/Before');
      $after_img  = save_upload('after_img',  __DIR__.'/goldsmith/After');

      // Snapshot latest trans values (by billId + phone + branchId) & compute mismatch
      $mismatch = 'no';
      $branch_gross = $branch_net = $branch_purity = null;
      $branchId_latest = '';
      $branch_name_from_id = '';

      // 1) Get latest TRANS row for this bill+phone+branch
      if ($bill_id !== '' && $phone !== '' && $from_branch !== '') {
        $q = "SELECT t.branchId, t.grossW, t.netW, t.purity
              FROM trans t
              WHERE t.billId = ?
                AND t.phone  = ?
                AND t.branchId = ?
              ORDER BY t.id DESC
              LIMIT 1";
        if ($ps = mysqli_prepare($con, $q)) {
          mysqli_stmt_bind_param($ps, "sss", $bill_id, $phone, $from_branch);
          mysqli_stmt_execute($ps);
          $rs = mysqli_stmt_get_result($ps);
          if ($row = mysqli_fetch_assoc($rs)) {
            $branchId_latest = $row['branchId'];
            $branch_gross    = $row['grossW'];
            $branch_net      = $row['netW'];
            $branch_purity   = $row['purity'];
          }
          mysqli_stmt_close($ps);
        }
      }

      // 2) Branch Name from branch table by selected Branch ID (from_branch)
      if ($from_branch !== '') {
        $qb = mysqli_prepare($con, "SELECT branchName FROM branch WHERE branchId=? LIMIT 1");
        mysqli_stmt_bind_param($qb, "s", $from_branch);
        mysqli_stmt_execute($qb);
        $rb = mysqli_stmt_get_result($qb);
        if ($b = mysqli_fetch_assoc($rb)) $branch_name_from_id = $b['branchName'];
        mysqli_stmt_close($qb);
      }

      // 3) Compute mismatch across id + value fields
      $mismatch = mismatch_flag(
        $from_branch, $branchId_latest,
        $branch_gross, $branch_net, $branch_purity,
        $before_wt_raw, $before_netwt, $before_purity
      );

      // 4) Insert smelter row; packet_no = billId-phone-branchId; include branch_name
      $sql = "INSERT INTO smelter
              (packet_no, from_branch, before_purity, before_wt, before_netwt, before_img,
               after_purity, after_wt, after_netwt, after_img,
               branch_gross, branch_net, branch_purity, branch_name,
               created_by, created_at, mismatch)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?)";
      $st = mysqli_prepare($con, $sql);
      $created_by = ($_SESSION['login_username'] ?? '');

      mysqli_stmt_bind_param(
        $st,
        "ssssssssssssssss",
        $packet_no,            // 1 (billId-phone-branchId)
        $from_branch,          // 2
        $before_purity,        // 3
        $before_wt_raw,        // 4
        $before_netwt,         // 5
        $before_img,           // 6
        $after_purity,         // 7
        $after_wt,             // 8
        $after_netwt,          // 9
        $after_img,            // 10
        $branch_gross,         // 11
        $branch_net,           // 12
        $branch_purity,        // 13
        $branch_name_from_id,  // 14
        $created_by,           // 15
        $mismatch              // 16
      );
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);

      // 5) Mark the latest matching trans by (billId, phone, branchId) as received = 1
      if ($bill_id !== '' && $phone !== '' && $from_branch !== '') {
        $sqlUpd = "
          UPDATE trans
          SET received = 1
          WHERE id = (
            SELECT id FROM (
              SELECT id
              FROM trans
              WHERE billId = ?
                AND phone  = ?
                AND branchId = ?
              ORDER BY id DESC
              LIMIT 1
            ) x
          )
        ";
        if ($up = mysqli_prepare($con, $sqlUpd)) {
          mysqli_stmt_bind_param($up, "sss", $bill_id, $phone, $from_branch);
          mysqli_stmt_execute($up);
          $affected = mysqli_stmt_affected_rows($up);
          mysqli_stmt_close($up);
          if ($affected < 1) {
            set_flash('Note: Received flag not updated (no latest trans matched billId/phone/branch).');
          }
        }
      }

      set_flash('Smelter BEFORE saved. Packet No = '.$packet_no.'. Received updated for the latest trans row of this bill/phone/branch.');
      header('Location: '.$_SERVER['PHP_SELF']); exit;
    }
  } catch (Throwable $e) {
    set_flash('DB error: '.$e->getMessage());
    header('Location: '.$_SERVER['PHP_SELF']); exit;
  }
}
/* ========================= end POST; below is GET render ========================= */

/* -----------------------------
   Role-based chrome
----------------------------- */
$type = $_SESSION['usertype'] ?? '';
if ($type === 'Goldsmith') {
  include("header.php");
  include("menugold.php");
} elseif ($type === 'Master') {
  include("header.php");
  include("menumaster.php");
} elseif($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
} else {
  include("logout.php"); exit;
}

/* -----------------------------
   Dropdown data for GET
   (UNIQUE by billId + phone + branchId)
----------------------------- */
$pendingBefore = [];
$qb = "
  SELECT 
      t.id, t.customerId, t.billId, t.phone,
      t.grossW, t.netW, t.purity,
      b.branchName, b.branchId
  FROM trans t
  JOIN (
    SELECT billId, phone, branchId, MAX(id) AS max_id
    FROM trans
    WHERE CurrentBranch = 'HO'
      AND status = 'Approved'
      AND metal  = 'Gold'
      AND (sta IS NOT NULL AND sta <> '')
      AND (staDate IS NOT NULL AND staDate <> '' AND staDate <> '0000-00-00')
      AND (received IS NULL OR received = 0 OR received = '0')
    GROUP BY billId, phone, branchId
  ) latest ON latest.max_id = t.id
  JOIN branch b ON b.branchId = t.branchId
  ORDER BY t.id DESC
";
$rsb = mysqli_query($con, $qb);
while ($r = mysqli_fetch_assoc($rsb)) $pendingBefore[] = $r;

$pendingAfter = [];
$qa = "SELECT id, packet_no, from_branch, before_wt, before_purity, before_netwt, before_img, created_at
       FROM smelter
       WHERE (after_purity IS NULL OR after_purity = '')
          OR (after_wt IS NULL OR after_wt = '')
       ORDER BY created_at DESC";
$rsa = mysqli_query($con, $qa);
while ($r = mysqli_fetch_assoc($rsa)) $pendingAfter[] = $r;

$branches = [];
$rs = mysqli_query($con, "SELECT branchName AS bn, branchId As bid
                          FROM branch
                          WHERE status = 1
                          ORDER BY branchName ASC");
while ($row = mysqli_fetch_assoc($rs)) $branches[] = ['name'=>$row['bn'],'id'=>$row['bid']];

?>
<style>
.form-card{margin:24px auto;max-width:980px;background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.12);}
.section-title{font-weight:800;font-size:14px;text-transform:uppercase;letter-spacing:.4px;color:#555;margin:8px 0 12px;border-bottom:1px solid #eee;padding-bottom:6px;}
.small-note{color:#666;font-size:12px;margin-top:4px;}
.btn-primary{background:#990000;border-color:#990000;} .btn-primary:hover{background:#660000;border-color:#660000;}
</style>

<div id="wrapper">
  <div class="content">
    <div class="form-card">
      <h3 class="mb-3 text-center">Smelter Entry</h3>

      <?php $fx = get_flash(); if ($fx!==''): ?>
        <div class="alert alert-info" role="alert"><?php echo htmlspecialchars($fx); ?></div>
      <?php endif; ?>

      <!-- Pending BEFORE (by Bill+Phone+Branch uniqueness) -->
      <div class="form-group">
        <label><strong>Customers to be received (today/yesterday)</strong></label>
        <select id="pendingBefore" class="form-control">
          <option value="">Select customer to receive</option>
          <?php foreach ($pendingBefore as $pt): ?>
            <option value="<?php echo htmlspecialchars($pt['customerId']); ?>"
              data-bill="<?php echo htmlspecialchars($pt['billId']); ?>"
              data-phone="<?php echo htmlspecialchars($pt['phone']); ?>"
              data-branch="<?php echo htmlspecialchars($pt['branchId']); ?>"
              data-branchname="<?php echo htmlspecialchars($pt['branchName']); ?>"
              data-gw="<?php echo htmlspecialchars($pt['grossW']); ?>"
              data-nw="<?php echo htmlspecialchars($pt['netW']); ?>"
              data-purity="<?php echo htmlspecialchars($pt['purity']); ?>">
              <?php
                $pkt = $pt['billId'].'-'.$pt['phone'].'-'.$pt['branchId'];
                echo htmlspecialchars(
                  'Bill: '.$pt['billId'].' | Phone: '.$pt['phone'].' | Cust: '.$pt['customerId'].
                  ' — Gross:'.$pt['grossW'].' Net:'.$pt['netW'].' Purity:'.$pt['purity'].' — '.$pt['branchName'].
                  ' — Packet: '.$pkt
                );
              ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="small-note">Packet No will be saved as <b>billId-phone-branchId</b>.</div>
      </div>

      <!-- Pending AFTER (from smelter) -->
      <div class="form-group">
        <label><strong>Pending AFTER (Smelter)</strong></label>
        <select id="pendingAfter" class="form-control">
          <option value="">Select to complete AFTER</option>
          <?php foreach ($pendingAfter as $pa): ?>
            <option value="<?php echo (int)$pa['id']; ?>"
              data-packet="<?php echo htmlspecialchars($pa['packet_no']); ?>"
              data-branch="<?php echo htmlspecialchars($pa['from_branch']); ?>"
              data-bp="<?php echo htmlspecialchars($pa['before_purity']); ?>"
              data-bw="<?php echo htmlspecialchars($pa['before_wt']); ?>"
              data-bnw="<?php echo htmlspecialchars($pa['before_netwt']); ?>"
              data-bi="<?php echo htmlspecialchars(basename($pa['before_img'] ?? '' )); ?>">
              <?php
                $label = 'Packet: '.($pa['packet_no'] ?: ('ID#'.$pa['id'])).
                         ' — BranchID:'.$pa['from_branch'].
                         ' — BeforeWt:'.$pa['before_wt'];
                echo htmlspecialchars($label);
              ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Trans info display -->
      <div id="transInfo" style="display:none; margin:12px 0 16px;">
        <div class="section-title">Latest branch-entered data (for this bill/phone/branch)</div>
        <div>GrossW: <span id="showGross"></span></div>
        <div>NetW: <span id="showNet"></span></div>
        <div>Purity: <span id="showPurity"></span></div>
        <div>Branch: <span id="showBranch"></span></div>
      </div>

      <!-- Form -->
      <form id="smelterForm" method="post" enctype="multipart/form-data" autocomplete="off" style="padding:5px;">
        <input type="hidden" name="mode" id="mode" value="create">
        <input type="hidden" name="update_id" id="update_id" value="0">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($_SESSION['smelter_form_nonce']); ?>">
        <!-- Hidden fields we post -->
        <input type="hidden" name="bill_id" id="bill_id">
        <input type="hidden" name="phone"   id="phone">

        <div class="form-group">
          <label>Packet No <span class="small-note">(auto: billId-phone-branchId)</span></label>
          <input type="text" name="packet_no" id="packet_no" class="form-control" readonly>
        </div>

        <div class="form-group">
          <label>From Branch (Branch ID)</label>
          <select name="from_branch" id="from_branch" class="form-control" required>
            <option value="">Select a branch…</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?php echo htmlspecialchars($b['id']); ?>">
                <?php echo htmlspecialchars($b['name'].' - '.$b['id']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="row">
          <!-- BEFORE -->
          <div class="col-md-6">
            <div class="section-title">Before</div>
            <div class="form-group">
              <label>Before Purity</label>
              <input type="text" name="before_purity" id="before_purity" class="form-control">
            </div>
            <div class="form-group">
              <label>Before Weight</label>
              <input type="text" name="before_wt" id="before_wt" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Before Net Wt</label>
              <input type="text" name="before_netwt" id="before_netwt" class="form-control">
            </div>
            <div class="form-group">
              <label>Before Image</label>
              <input type="file" name="before_img" id="before_img" class="form-control-file" accept="image/*,application/pdf">
              <div id="before_img_note" class="small-note"></div>
            </div>
          </div>

          <!-- AFTER -->
          <div class="col-md-6">
            <div class="section-title">After</div>
            <div class="form-group">
              <label>After Purity</label>
              <input type="text" name="after_purity" id="after_purity" class="form-control">
            </div>
            <div class="form-group">
              <label>After Weight</label>
              <input type="text" name="after_wt" id="after_wt" class="form-control">
            </div>
            <div class="form-group">
              <label>After Net Wt</label>
              <input type="text" name="after_netwt" id="after_netwt" class="form-control">
            </div>
            <div class="form-group">
              <label>After Image</label>
              <input type="file" name="after_img" id="after_img" class="form-control-file" accept="image/*,application/pdf">
            </div>
          </div>
        </div>

        <div class="small-note" id="mode_note">Tip: Choose a customer at top to auto-fill. AFTER can be completed later.</div>
        <br>
        <button type="submit" class="btn btn-primary btn-block">Save</button>
      </form>
    </div>
  </div>
  <?php include('footer.php'); ?>
</div>

<script>
// Pending BEFORE (builds packet_no = billId-phone-branchId)
document.getElementById('pendingBefore').addEventListener('change', function(){
  const opt = this.options[this.selectedIndex];
  if (!opt.value) {
    document.getElementById('transInfo').style.display = 'none';
    document.getElementById('mode').value = 'create';
    document.getElementById('update_id').value = '0';
    document.getElementById('mode_note').textContent = 'Tip: Choose a customer at top to auto-fill. AFTER can be completed later.';
    document.getElementById('bill_id').value = '';
    document.getElementById('phone').value   = '';
    document.getElementById('packet_no').value = '';
    return;
  }
  document.getElementById('mode').value = 'create';
  document.getElementById('update_id').value = '0';

  const bill   = opt.getAttribute('data-bill') || '';
  const phone  = opt.getAttribute('data-phone') || '';
  const branch = opt.getAttribute('data-branch') || '';

  document.getElementById('bill_id').value = bill;
  document.getElementById('phone').value   = phone;

  const pkt = [bill, phone, branch].join('-');
  document.getElementById('packet_no').value = pkt;

  document.getElementById('from_branch').value = branch;
  document.getElementById('before_purity').value = opt.getAttribute('data-purity') || '';
  document.getElementById('before_wt').value = opt.getAttribute('data-gw') || '';
  document.getElementById('before_netwt').value = opt.getAttribute('data-nw') || '';

  document.getElementById('showGross').textContent = opt.getAttribute('data-gw') || '';
  document.getElementById('showNet').textContent   = opt.getAttribute('data-nw') || '';
  document.getElementById('showPurity').textContent= opt.getAttribute('data-purity') || '';
  document.getElementById('showBranch').textContent= opt.getAttribute('data-branchname') || '';
  document.getElementById('transInfo').style.display = 'block';

  document.getElementById('after_purity').required = false;
  document.getElementById('after_wt').required = false;
  document.getElementById('mode_note').textContent = 'Create mode: Packet No = billId-phone-branchId. AFTER can be filled later.';
});

// Pending AFTER (smelter) — lock packet_no to existing row's value
document.getElementById('pendingAfter').addEventListener('change', function(){
  const opt = this.options[this.selectedIndex];
  const modeEl = document.getElementById('mode');
  const updEl  = document.getElementById('update_id');
  const pktIn  = document.getElementById('packet_no');
  const fbIn   = document.getElementById('from_branch');

  if (!opt.value) {
    modeEl.value = 'create';
    updEl.value  = '0';
    pktIn.readOnly = true;
    pktIn.value    = '';
    fbIn.value     = '';
    document.getElementById('mode_note').textContent = 'Tip: Choose a customer at top to auto-fill. AFTER can be completed later.';
    return;
  }

  modeEl.value = 'update_after';
  updEl.value  = opt.value;

  // Use EXACT packet_no saved in smelter
  const pkt = opt.getAttribute('data-packet') || '';
  pktIn.value    = pkt;
  pktIn.readOnly = true;

  // Pre-fill BEFORE info display (not updated server-side)
  fbIn.value = opt.getAttribute('data-branch') || '';
  document.getElementById('before_purity').value = opt.getAttribute('data-bp') || '';
  document.getElementById('before_wt').value     = opt.getAttribute('data-bw') || '';
  document.getElementById('before_netwt').value  = opt.getAttribute('data-bnw') || '';

  const bi = opt.getAttribute('data-bi');
  document.getElementById('before_img_note').textContent = bi ? ('Existing BEFORE image: ' + bi) : '';

  document.getElementById('after_purity').required = true;
  document.getElementById('after_wt').required     = true;
  document.getElementById('mode_note').textContent = 'Update mode: Using the SAME Packet No from BEFORE. Fill AFTER values.';
});
</script>

