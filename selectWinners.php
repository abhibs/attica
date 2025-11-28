<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'] ?? '';
if ($type == 'Master') {
  include("header.php"); include("menumaster.php");
} elseif ($type == 'BD') {
  include("header.php"); include("menubd.php");
} elseif ($type == 'Software') {
  include("header.php"); include("menuSoftware.php");
} elseif ($type == 'Legal') {
  include("header.php"); include("menulegal.php");
} else {
  include("logout.php");
  exit;
}
include("dbConnection.php");

$hlh_errors = [];
$hlh_winners = [];
$hlh_losers = [];
$hlh_from = $hlh_to = '';
$hlh_summary = '';
$hlh_who = $_GET['hlh_who'] ?? 'both';

$hlh_loser_has_img = false;
if ($q = mysqli_query($con, "SHOW COLUMNS FROM state_highlight_losers LIKE 'image_path'")) {
  $hlh_loser_has_img = mysqli_num_rows($q) > 0;
  mysqli_free_result($q);
}

if (isset($_GET['hlh_do'])) {
  $mode = $_GET['hlh_mode'] ?? 'date';
  $validYmd = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

  if ($mode === 'date') {
    $d = trim($_GET['hlh_date'] ?? '');
    if ($d && $validYmd($d)) { $hlh_from = $d; $hlh_to = $d; }
    else { $hlh_errors[] = "Choose a valid date."; }
  } elseif ($mode === 'range') {
    $f = trim($_GET['hlh_from'] ?? '');
    $t = trim($_GET['hlh_to'] ?? '');
    if ($f && $t && $validYmd($f) && $validYmd($t)) {
      if (strtotime($t) < strtotime($f)) $hlh_errors[] = "End date cannot be earlier than start date.";
      $hlh_from = $f; $hlh_to = $t;
    } else { $hlh_errors[] = "Choose a valid From and To date."; }
  } elseif ($mode === 'thisweek' || $mode === 'lastweek') {
    $today = new DateTime('today');
    if ($mode === 'lastweek') $today->modify('-1 week');
    $ws = (clone $today)->modify('monday this week');
    $we = (clone $ws)->modify('sunday this week');
    $hlh_from = $ws->format('Y-m-d');
    $hlh_to   = $we->format('Y-m-d');
  } else {
    $hlh_errors[] = "Unknown mode.";
  }

  if (!$hlh_errors) {
    // Winners (overlap logic)
    if ($hlh_who !== 'losers') {
      $sqlW = "
        SELECT 'Winner' as row_type, state, display_start_date, display_end_date,
               rank_no, person_name, role_title, branch_name, image_path
        FROM state_highlight_winners
        WHERE is_active=1
          AND display_end_date >= ?
          AND display_start_date <= ?
        ORDER BY display_start_date DESC, state ASC, rank_no ASC, person_name ASC
      ";
      if ($st = $con->prepare($sqlW)) {
        $st->bind_param("ss", $hlh_from, $hlh_to);
        $st->execute();
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) $hlh_winners[] = $row;
        $st->close();
      }
    }

    // Losers (overlap logic)
    if ($hlh_who !== 'winners') {
      $loserFields = $hlh_loser_has_img
        ? "person_name, branch_name, image_path"
        : "person_name, branch_name";
      $sqlL = "
        SELECT 'Loser' as row_type, state, display_start_date, display_end_date,
               $loserFields
        FROM state_highlight_losers
        WHERE is_active=1
          AND display_end_date >= ?
          AND display_start_date <= ?
        ORDER BY display_start_date DESC, state ASC, person_name ASC
      ";
      if ($st = $con->prepare($sqlL)) {
        $st->bind_param("ss", $hlh_from, $hlh_to);
        $st->execute();
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) $hlh_losers[] = $row;
        $st->close();
      }
    }

    if ($hlh_from === $hlh_to) {
      $hlh_summary = "For ".date('D, d M Y', strtotime($hlh_from));
    } else {
      $hlh_summary = "For ".date('d M Y', strtotime($hlh_from))." – ".date('d M Y', strtotime($hlh_to));
    }
  }
}

if (!is_dir('images/toppers')) { @mkdir('images/toppers', 0775, true); }
$loserImageEnabled = false;
if ($q = mysqli_query($con, "SHOW COLUMNS FROM state_highlight_losers LIKE 'image_path'")) {
  $loserImageEnabled = mysqli_num_rows($q) > 0;
  mysqli_free_result($q);
}
$errors = [];
$success = '';
$states = ['Karnataka','Bangalore','Outside Bangalore','TamilNadu','Chennai','Outside Chennai','AndhraPradesh','Telangana','Hyderabad','Outside Hyderabad'];
if (isset($_GET['del_w'])) {
  $id = (int)$_GET['del_w'];
  if ($st = $con->prepare("SELECT image_path FROM state_highlight_winners WHERE id=?")) {
    $st->bind_param("i", $id);
    $st->execute(); $st->bind_result($imgPath); $st->fetch(); $st->close();
    if ($imgPath && is_file($imgPath)) { @unlink($imgPath); }
  }
  if ($st = $con->prepare("DELETE FROM state_highlight_winners WHERE id=?")) {
    $st->bind_param("i", $id);
    $st->execute(); $st->close();
    $success = "Winner deleted.";
  }
}
if (isset($_GET['del_l'])) {
  $id = (int)$_GET['del_l'];
  if ($loserImageEnabled) {
    if ($st = $con->prepare("SELECT image_path FROM state_highlight_losers WHERE id=?")) {
      $st->bind_param("i", $id);
      $st->execute(); $st->bind_result($imgPath); $st->fetch(); $st->close();
      if ($imgPath && is_file($imgPath)) { @unlink($imgPath); }
    }
  }
  if ($st = $con->prepare("DELETE FROM state_highlight_losers WHERE id=?")) {
    $st->bind_param("i", $id);
    $st->execute(); $st->close();
    $success = "Loser deleted.";
  }
}
$stateView = trim($_GET['state_view'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $state = trim($_POST['state'] ?? '');
  $from  = trim($_POST['display_start_date'] ?? '');
  $to    = trim($_POST['display_end_date'] ?? '');
  if ($state === '') $errors[] = "Please choose a state.";
  if ($from  === '') $errors[] = "Please choose Display From date.";
  if ($to    === '') $errors[] = "Please choose Display To date.";
  if (!$errors) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
      $errors[] = "Invalid date format. Use YYYY-MM-DD.";
    } elseif (strtotime($to) < strtotime($from)) {
      $errors[] = "Display To cannot be earlier than Display From.";
    }
  }
  $w_names  = $_POST['w_name']  ?? [];
  $w_ranks  = $_POST['w_rank']  ?? [];
  $w_roles  = $_POST['w_role']  ?? [];
  $w_bnames = $_POST['w_bname'] ?? [];
  $l_names  = $_POST['l_name']  ?? [];
  $l_bnames = $_POST['l_bname'] ?? [];
  if (!$errors) {
    if (!empty($w_names) && is_array($w_names)) {
      $stmtW = $con->prepare("INSERT INTO state_highlight_winners (state, display_start_date, display_end_date, rank_no, person_name, role_title, branch_name, image_path, is_active) VALUES (?,?,?,?,?,?,?,?,1)");
      for ($i=0; $i<count($w_names); $i++) {
        $name  = trim($w_names[$i]  ?? '');
        $rank  = (int)($w_ranks[$i] ?? 0);
        $role  = trim($w_roles[$i]  ?? '');
        $bname = trim($w_bnames[$i] ?? '');
        $imgPath = '';
        $okFile  = false;
        if (!empty($_FILES['w_image']['name'][$i])) {
          $tmp  = $_FILES['w_image']['tmp_name'][$i];
          $orig = $_FILES['w_image']['name'][$i];
          $err  = $_FILES['w_image']['error'][$i];
          $size = $_FILES['w_image']['size'][$i];
          if ($err === UPLOAD_ERR_OK && is_uploaded_file($tmp)) {
            if ($size <= 10*1024*1024) {
              $finfo = new finfo(FILEINFO_MIME_TYPE);
              $mime  = $finfo->file($tmp);
              $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
              if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/','_', pathinfo($orig, PATHINFO_FILENAME));
                $newName  = date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '_' . $safeBase . '.' . $ext;
                $dest     = 'images/toppers/' . $newName;
                if (move_uploaded_file($tmp, $dest)) { $imgPath = $dest; $okFile = true; }
                else { $errors[] = "Failed to move uploaded file for Winner #".($i+1); }
              } else { $errors[] = "Winner #".($i+1).": invalid image type (JPG/PNG/WEBP only)."; }
            } else { $errors[] = "Winner #".($i+1).": file too large (max 10MB)."; }
          } else if ($name!=='' || $rank>0 || $role!=='' || $bname!=='') {
            $errors[] = "Winner #".($i+1).": upload an image.";
          }
        } else if ($name!=='' || $rank>0 || $role!=='' || $bname!=='') {
          $errors[] = "Winner #".($i+1).": upload an image.";
        }
        if ($name==='' && $rank===0 && !$okFile) continue;
        if ($name==='' || $rank<=0 || !$okFile) { $errors[] = "Winner #".($i+1).": fill Name + Rank and provide Image."; continue; }
        if (!$errors) {
          $stmtW->bind_param("sssissss", $state, $from, $to, $rank, $name, $role, $bname, $imgPath);
          $stmtW->execute();
        }
      }
      if (isset($stmtW)) $stmtW->close();
    }
    if (!empty($l_names) && is_array($l_names)) {
      if ($loserImageEnabled) {
        $stmtL = $con->prepare("INSERT INTO state_highlight_losers (state, display_start_date, display_end_date, person_name, branch_name, image_path, is_active) VALUES (?,?,?,?,?,?,1)");
      } else {
        $stmtL = $con->prepare("INSERT INTO state_highlight_losers (state, display_start_date, display_end_date, person_name, branch_name, is_active) VALUES (?,?,?,?,?,1)");
      }
      for ($i=0; $i<count($l_names); $i++) {
        $name  = trim($l_names[$i]  ?? '');
        $bname = trim($l_bnames[$i] ?? '');
        if ($name==='' && $bname==='') continue;
        if ($name==='') { $errors[] = "Loser #".($i+1).": Name is required."; continue; }
        $imgPath = null;
        if ($loserImageEnabled && !empty($_FILES['l_image']['name'][$i])) {
          $tmp  = $_FILES['l_image']['tmp_name'][$i];
          $orig = $_FILES['l_image']['name'][$i];
          $err  = $_FILES['l_image']['error'][$i];
          $size = $_FILES['l_image']['size'][$i];
          if ($err === UPLOAD_ERR_OK && is_uploaded_file($tmp)) {
            if ($size <= 5*1024*1024) {
              $finfo = new finfo(FILEINFO_MIME_TYPE);
              $mime  = $finfo->file($tmp);
              $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
              if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/','_', pathinfo($orig, PATHINFO_FILENAME));
                $newName  = date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '_' . $safeBase . '.' . $ext;
                $dest     = 'images/toppers/' . $newName;
                if (move_uploaded_file($tmp, $dest)) { $imgPath = $dest; }
                else { $errors[] = "Failed to move uploaded file for Loser #".($i+1); }
              } else { $errors[] = "Loser #".($i+1).": invalid image type (JPG/PNG/WEBP only)."; }
            } else { $errors[] = "Loser #".($i+1).": file too large (max 5MB)."; }
          }
        }
        if (!$errors) {
          if ($loserImageEnabled) {
            $stmtL->bind_param("ssssss", $state, $from, $to, $name, $bname, $imgPath);
          } else {
            $stmtL->bind_param("sssss", $state, $from, $to, $name, $bname);
          }
          $stmtL->execute();
        }
      }
      if (isset($stmtL)) $stmtL->close();
    }
    if (!$errors) {
      $success   = "Highlights saved for {$state} — {$from} to {$to}.";
      $stateView = $state;
    }
  }
}
$activeWinners = $activeLosers = [];
$today = date('Y-m-d');

function shx_state_keys($key){
  $u = strtoupper(trim($key));
  switch ($u) {
    case 'KARNATAKA':           return ['Karnataka','Bangalore','Outside Bangalore'];
    case 'BANGALORE':           return ['Bangalore'];
    case 'OUTSIDE BANGALORE':   return ['Outside Bangalore'];

    case 'TAMILNADU':
    case 'TAMIL NADU':          return ['TamilNadu','Pondicherry','Chennai','Outside Chennai'];
    case 'CHENNAI':             return ['Chennai'];
    case 'OUTSIDE CHENNAI':     return ['Outside Chennai'];

    case 'ANDHRAPRADESH':
    case 'ANDHRA PRADESH':      return ['AndhraPradesh','Andhra Pradesh'];

    case 'TELANGANA':           return ['Telangana','Hyderabad','Outside Hyderabad'];
    case 'HYDERABAD':           return ['Hyderabad'];
    case 'OUTSIDE HYDERABAD':   return ['Outside Hyderabad'];

    default:                    return [$key];
  }
}

function shx_bind_params_array(mysqli_stmt $stmt, string $types, array $params){
  $refs = [];
  $refs[] = &$types;
  foreach ($params as $k => $v) $refs[] = &$params[$k];
  return call_user_func_array([$stmt,'bind_param'],$refs);
}
if ($stateView !== '') {
  $keys = shx_state_keys($stateView);
  $ph   = implode(',', array_fill(0, count($keys), '?'));

  $sqlW = "
    SELECT id, display_start_date, display_end_date, rank_no, person_name, role_title, branch_name, image_path
    FROM state_highlight_winners
    WHERE state IN ($ph) AND is_active=1 AND display_end_date >= ?
    ORDER BY display_start_date DESC, rank_no ASC, person_name ASC";
  if ($st = $con->prepare($sqlW)) {
    $types  = str_repeat('s', count($keys)) . 's';
    $params = array_merge($keys, [$today]);
    shx_bind_params_array($st, $types, $params);
    $st->execute(); $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $activeWinners[] = $row;
    $st->close();
  }

  $loserFields = $loserImageEnabled
    ? "id, display_start_date, display_end_date, person_name, branch_name, image_path"
    : "id, display_start_date, display_end_date, person_name, branch_name";

  $sqlL = "
    SELECT $loserFields
    FROM state_highlight_losers
    WHERE state IN ($ph) AND is_active=1 AND display_end_date >= ?
    ORDER BY display_start_date DESC, person_name ASC";
  if ($st = $con->prepare($sqlL)) {
    $types  = str_repeat('s', count($keys)) . 's';
    $params = array_merge($keys, [$today]);
    shx_bind_params_array($st, $types, $params);
    $st->execute(); $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $activeLosers[] = $row;
    $st->close();
  }
  $showLoserPhotoCol = $loserImageEnabled;
}
?>
<style>
  #wrapper h3{ text-transform:uppercase; font-weight:600; font-size:18px; color:#123C69; }
  .hpanel .panel-body{ box-shadow:10px 15px 15px #999; border:1px solid #edf2f9; background:#f5f5f5; border-radius:3px; padding:20px; }
  .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
  .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
  .btn-primary{ background-color:#123C69; }
  .theadRow{ text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }
  .btn-success{ display:inline-block; padding:.7em 1.4em; margin:0 .3em .3em 0; border-radius:.15em; text-decoration:none; font-size:12px; text-transform:uppercase; color:#fffafa; background:#123C69; box-shadow:inset 0 -.6em 0 -.35em rgba(0,0,0,.17); }
  .fa_Icon{ color:#990000; }
  .table-responsive .row{ margin:0; }
  #shx { --accent:#990000; --bg:#f6f7fb; --muted:#6b7280; --card:#ffffff; --radius:16px; font-family: system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial; }
  #shx * { box-sizing:border-box; }
  #shx .shx-wrap{max-width:1100px;margin:0 auto;padding:0}
  #shx .shx-card{background:#fff;border:1px solid #eee;border-radius:var(--radius);box-shadow:0 6px 18px rgba(0,0,0,.06); margin-bottom:16px}
  #shx .shx-hdr{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,var(--accent),#6b0000);color:#fff; padding:14px 18px;border-radius:var(--radius) var(--radius) 0 0}
  #shx .shx-hdr h2{margin:0;font-size:18px;font-weight:800;letter-spacing:.3px}
  #shx .shx-content{padding:16px 18px;background:#fff}
  #shx .shx-row{display:grid;grid-template-columns:1.2fr .8fr;gap:12px}
  #shx .shx-col-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  #shx .shx-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
  #shx .shx-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
  @media (max-width:900px){
    #shx .shx-row{grid-template-columns:1fr}
    #shx .shx-col-2,#shx .shx-grid,#shx .shx-grid-3{grid-template-columns:1fr}
  }
  #shx label{font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:6px}
  #shx input,#shx select{width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;background:#fff;outline:none;box-shadow:none}
  #shx input[type="file"]{padding:8px}
  #shx .shx-section{margin:16px 0 6px;font-weight:800;color:#111827}
  #shx .shx-muted{color:var(--muted);font-size:12px}
  #shx .shx-tp-card{padding:12px;border:1px solid #eee;border-radius:12px;background:var(--card);position:relative}
  #shx .shx-row-close{ position:absolute;top:8px;right:8px;width:28px;height:28px;border-radius:999px; border:1px solid #e5e7eb;background:#fff;color:#111;cursor:pointer; display:grid;place-items:center;font-weight:800;line-height:0; }
  #shx .shx-row-close:hover{background:#f3f4f6}
  #shx .shx-btnbar{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  #shx .shx-btn{border:none;border-radius:12px;padding:10px 14px;cursor:pointer;font-weight:700}
  #shx .shx-btn-primary{background:var(--accent);color:#fff}
  #shx .shx-btn-ghost{background:#fff;border:1px solid #e5e7eb;color:#111}
  #shx .shx-msg{padding:10px 12px;border-radius:12px;margin-bottom:12px}
  #shx .shx-ok{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
  #shx .shx-err{background:#fff1f2;color:#991b1b;border:1px solid #fecaca}
  #shx .shx-mini{display:flex;gap:10px;align-items:center;margin:8px 0 16px}
  #shx .shx-table{width:100%;border-collapse:separate;border-spacing:0 8px}
  #shx .shx-table th{font-size:12px;text-align:left;color:#374151;padding:6px}
  #shx .shx-table td{background:#fff;border:1px solid #eee;padding:10px;border-radius:10px}
  #shx .shx-thumb{width:56px;height:56px;object-fit:cover;border-radius:10px;border:1px solid #eee}
  #shx .shx-del{display:inline-block;padding:6px 10px;border:1px solid #ef4444;color:#ef4444;border-radius:10px;text-decoration:none}
  #shx .shx-del:hover{background:#fef2f2}
</style>

<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-heading">
          <h3><i class="fa_Icon fa fa-trophy"></i> Statewise Highlights (Winners & Losers)</h3>
        </div>
        <div class="panel-body">
          <div id="shx">
            <div class="shx-wrap">
              <?php if($success): ?><div class="shx-msg shx-ok"><?=htmlspecialchars($success)?></div><?php endif; ?>
              <?php if($errors): ?>
                <div class="shx-msg shx-err"><b>Please fix:</b><ul style="margin:8px 0 0 18px">
                  <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
                </ul></div>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" id="shxForm">
                <?php
                  $presetRows  = max(1, intval($_POST['__w_rows'] ?? 1));
                  $presetRowsL = max(1, intval($_POST['__l_rows'] ?? 1));
                ?>

                <div class="shx-card">
                  <div class="shx-hdr"><h2>Create / Manage Highlights</h2></div>
                  <div class="shx-content">
                    <div class="shx-row">
                      <div class="shx-col-2">
                        <div>
                          <label>Display From</label>
                          <input type="date" name="display_start_date" value="<?=htmlspecialchars($_POST['display_start_date'] ?? date('Y-m-d'))?>" required>
                        </div>
                        <div>
                          <label>Display To</label>
                          <input type="date" name="display_end_date" value="<?=htmlspecialchars($_POST['display_end_date'] ?? date('Y-m-d'))?>" required>
                        </div>
                      </div>
                      <div>
                        <label>State</label>
                        <select name="state" required>
                          <option value="">-- Select --</option>
                          <?php foreach($states as $s): ?>
                            <option value="<?=htmlspecialchars($s)?>" <?= (isset($_POST['state']) && $_POST['state']===$s)?'selected':''; ?>>
                              <?=htmlspecialchars($s)?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="shx-card">
                  <div class="shx-hdr"><h2>Top / Best Performers</h2></div>
                  <div class="shx-content">
                    <div class="shx-section">Top/Best Performers (Winners)</div>
                    <div id="winners" class="shx-grid">
                      <?php for ($i=0; $i<$presetRows; $i++): ?>
                      <div class="shx-tp-card">
                        <button type="button" class="shx-row-close" title="Remove">×</button>
                        <div class="shx-grid-3">
                          <div>
                            <label>Rank</label>
                            <input type="number" name="w_rank[]" min="1" placeholder="1, 2, 3..." value="<?=htmlspecialchars($_POST['w_rank'][$i] ?? '')?>">
                          </div>
                          <div>
                            <label>Name</label>
                            <input type="text" name="w_name[]" placeholder="Person Name" value="<?=htmlspecialchars($_POST['w_name'][$i] ?? '')?>">
                          </div>
                          <div>
                            <label>Role (optional)</label>
                            <input type="text" name="w_role[]" placeholder="Branch Manager" value="<?=htmlspecialchars($_POST['w_role'][$i] ?? '')?>">
                          </div>
                        </div>
                        <div class="shx-grid">
                          <div>
                            <label>Branch Name (optional)</label>
                            <input type="text" name="w_bname[]" placeholder="e.g., Jayanagar Branch" value="<?=htmlspecialchars($_POST['w_bname'][$i] ?? '')?>">
                          </div>
                          <div>
                            <label>Photo (JPG/PNG/WEBP, ≤5MB)</label>
                            <input type="file" name="w_image[]" accept="image/jpeg,image/png,image/webp">
                          </div>
                        </div>
                      </div>
                      <?php endfor; ?>
                    </div>
                    <div class="shx-btnbar">
                      <button type="button" class="shx-btn shx-btn-ghost" id="addWinner">+ Add Winner</button>
                      <span class="shx-muted">Add ties by repeating the same rank for multiple winners.</span>
                    </div>
                  </div>
                </div>

                <div class="shx-card">
                  <div class="shx-hdr"><h2>Lowest / Worst Performers</h2></div>
                  <div class="shx-content">
                    <div class="shx-section">Lowest/Worst Performers (Losers)</div>
                    <div id="losers" class="shx-grid">
                      <?php for ($i=0; $i<$presetRowsL; $i++): ?>
                      <div class="shx-tp-card">
                        <button type="button" class="shx-row-close" title="Remove">×</button>
                        <div class="shx-grid">
                          <div>
                            <label>Name</label>
                            <input type="text" name="l_name[]" placeholder="Person Name" value="<?=htmlspecialchars($_POST['l_name'][$i] ?? '')?>">
                          </div>
                          <div>
                            <label>Branch Name (optional)</label>
                            <input type="text" name="l_bname[]" placeholder="e.g., Kumbakonam" value="<?=htmlspecialchars($_POST['l_bname'][$i] ?? '')?>">
                          </div>
                        </div>
                        <div class="shx-grid">
                          <div>
                            <label>Photo (optional, JPG/PNG/WEBP, ≤5MB)</label>
                            <input type="file" name="l_image[]" accept="image/jpeg,image/png,image/webp">
                          </div>
                        </div>
                      </div>
                      <?php endfor; ?>
                    </div>
                    <div class="shx-btnbar">
                      <button type="button" class="shx-btn shx-btn-ghost" id="addLoser">+ Add Loser</button>
                    </div>
                  </div>
                </div>

                <input type="hidden" name="__w_rows" id="__w_rows" value="<?= (int)$presetRows ?>">
                <input type="hidden" name="__l_rows" id="__l_rows" value="<?= (int)$presetRowsL ?>">
                <center><button type="submit" class="shx-btn shx-btn-primary">Save Highlights</button></center>
                <br>
              </form>

              <div class="shx-card">
                <div class="shx-hdr"><h2>Existing Active Highlights</h2></div>
                <div class="shx-content">
                  <div class="shx-section">Display To ≥ Today</div>
                  <form method="get" class="shx-mini">
                    <label style="margin:0">State</label>
                    <select name="state_view" onchange="this.form.submit()">
                      <option value="">-- Select --</option>
                      <?php foreach($states as $s): ?>
                        <option value="<?=htmlspecialchars($s)?>" <?= ($stateView===$s)?'selected':''; ?>><?=htmlspecialchars($s)?></option>
                      <?php endforeach; ?>
                    </select>
                    <?php if ($stateView===''): ?>
                      <span class="shx-muted">Choose a state to view & delete active entries.</span>
                    <?php endif; ?>
                  </form>

                  <?php if ($stateView!==''): ?>
                    <div class="shx-section">Winners — <?=htmlspecialchars($stateView)?></div>
                    <?php if (!$activeWinners): ?>
                      <div class="shx-muted">No active winners.</div>
                    <?php else: ?>
                      <table class="shx-table">
                        <thead>
                          <tr>
                            <th>Photo</th><th>Name</th><th>Rank</th><th>Role</th>
                            <th>Branch Name</th><th>Display Window</th><th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php foreach($activeWinners as $w): ?>
                          <tr>
                            <td><img class="shx-thumb" src="<?=htmlspecialchars($w['image_path'])?>" alt=""></td>
                            <td><?=htmlspecialchars($w['person_name'])?></td>
                            <td><?= (int)$w['rank_no'] ?></td>
                            <td><?=htmlspecialchars($w['role_title'] ?? '')?></td>
                            <td><?=htmlspecialchars($w['branch_name'] ?? '')?></td>
                            <td><?=date('d M Y', strtotime($w['display_start_date']))?> – <?=date('d M Y', strtotime($w['display_end_date']))?></td>
                            <td><a class="shx-del" href="?state_view=<?=urlencode($stateView)?>&del_w=<?=$w['id']?>" onclick="return confirm('Delete this winner?');">Delete</a></td>
                          </tr>
                        <?php endforeach; ?>
                        </tbody>
                      </table>
                    <?php endif; ?>

                    <div class="shx-section" style="margin-top:16px">Losers — <?=htmlspecialchars($stateView)?></div>
                    <?php if (!$activeLosers): ?>
                      <div class="shx-muted">No active losers.</div>
                    <?php else: ?>
                      <table class="shx-table">
                        <thead>
                          <tr>
                            <?php if (!empty($showLoserPhotoCol)): ?><th>Photo</th><?php endif; ?>
                            <th>Name</th><th>Branch Name</th><th>Display Window</th><th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php foreach($activeLosers as $l): ?>
                          <tr>
                            <?php if (!empty($showLoserPhotoCol)): ?>
                              <td>
                                <?php $p = isset($l['image_path']) ? trim((string)$l['image_path']) : ''; if ($p !== ''): ?>
                                  <img class="shx-thumb" src="<?= htmlspecialchars($p) ?>" alt="">
                                <?php endif; ?>
                              </td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($l['person_name']) ?></td>
                            <td><?= htmlspecialchars($l['branch_name'] ?? '') ?></td>
                            <td><?= date('d M Y', strtotime($l['display_start_date'])) ?> – <?= date('d M Y', strtotime($l['display_end_date'])) ?></td>
                            <td><a class="shx-del" href="?state_view=<?= urlencode($stateView) ?>&del_l=<?= $l['id'] ?>" onclick="return confirm('Delete this loser?');">Delete</a></td>
                          </tr>
                        <?php endforeach; ?>
                        </tbody>
                      </table>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
<!-- Highlights History (Winners & Losers) -->
<div class="shx-card">
  <div class="shx-hdr"><h2>Highlights History (Winners & Losers)</h2></div>
  <div class="shx-content">

    <?php if (!empty($hlh_errors)): ?>
      <div class="shx-msg shx-err">
        <b>Please fix:</b>
        <ul style="margin:8px 0 0 18px">
          <?php foreach($hlh_errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="get" id="hlhForm" class="shx-mini" style="flex-wrap:wrap; gap:12px">
      <input type="hidden" name="hlh_do" value="1">

      <label style="margin:0">Show</label>
      <select name="hlh_who" style="min-width:160px">
        <option value="both"   <?= ($hlh_who==='both')?'selected':''; ?>>Winners & Losers</option>
        <option value="winners"<?= ($hlh_who==='winners')?'selected':''; ?>>Winners only</option>
        <option value="losers" <?= ($hlh_who==='losers')?'selected':''; ?>>Losers only</option>
      </select>

      <label style="margin:0">Mode</label>
      <select name="hlh_mode" id="hlh_mode" onchange="hlhToggle()" style="min-width:180px">
        <option value="date"      <?= (($_GET['hlh_mode'] ?? '')==='date')?'selected':''; ?>>Single Date</option>
        <option value="thisweek"  <?= (($_GET['hlh_mode'] ?? '')==='thisweek')?'selected':''; ?>>This Week (Mon–Sun)</option>
        <option value="lastweek"  <?= (($_GET['hlh_mode'] ?? '')==='lastweek')?'selected':''; ?>>Last Week (Mon–Sun)</option>
        <option value="range"     <?= (($_GET['hlh_mode'] ?? '')==='range')?'selected':''; ?>>Custom Range</option>
      </select>

      <div id="hlh_date_wrap">
        <label style="margin:0">Date</label>
        <input type="date" name="hlh_date" value="<?= htmlspecialchars($_GET['hlh_date'] ?? '') ?>">
      </div>

      <div id="hlh_range_wrap" style="display:none; gap:10px">
        <div>
          <label style="margin:0">From</label>
          <input type="date" name="hlh_from" value="<?= htmlspecialchars($_GET['hlh_from'] ?? '') ?>">
        </div>
        <div>
          <label style="margin:0">To</label>
          <input type="date" name="hlh_to" value="<?= htmlspecialchars($_GET['hlh_to'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="shx-btn shx-btn-primary">Search</button>
    </form>

    <?php if (!empty($_GET['hlh_do']) && !$hlh_errors): ?>
      <div class="shx-section" style="margin-top:8px">
        Results <?= $hlh_summary ? ' — '.htmlspecialchars($hlh_summary) : '' ?>
      </div>

      <?php
        $rows = [];
        foreach ($hlh_winners as $r) { $rows[] = $r; }
        foreach ($hlh_losers as $r)  { $rows[] = $r; }
      ?>

      <?php if (!$rows): ?>
        <div class="shx-muted">No results for the selected date(s).</div>
      <?php else: ?>
        <table class="shx-table" style="margin-top:8px">
          <thead>
            <tr>
              <th>Type</th>
              <th>Photo</th>
              <th>State</th>
              <th>Rank</th>
              <th>Name</th>
              <th>Role</th>
              <th>Branch</th>
              <th>Display Window</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['row_type']) ?></td>
                <td>
                  <?php if (!empty($r['image_path'])): ?>
                    <img class="shx-thumb" src="<?= htmlspecialchars($r['image_path']) ?>" alt="">
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['state']) ?></td>
                <td><?= isset($r['rank_no']) ? (int)$r['rank_no'] : '' ?></td>
                <td><?= htmlspecialchars($r['person_name']) ?></td>
                <td><?= htmlspecialchars($r['role_title'] ?? ($r['row_type']==='Loser' ? '' : '')) ?></td>
                <td><?= htmlspecialchars($r['branch_name'] ?? '') ?></td>
                <td><?= date('d M Y', strtotime($r['display_start_date'])) ?> – <?= date('d M Y', strtotime($r['display_end_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <!-- If you prefer split tables instead of combined, use the two blocks below and remove the combined table above.

      <?php if ($hlh_who !== 'losers'): ?>
        <div class="shx-section" style="margin-top:16px">Winners</div>
        <?php if (!$hlh_winners): ?>
          <div class="shx-muted">No winners found.</div>
        <?php else: ?>
        <table class="shx-table" style="margin-top:8px">
          <thead>
            <tr>
              <th>Photo</th><th>State</th><th>Rank</th><th>Name</th><th>Role</th><th>Branch</th><th>Display Window</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hlh_winners as $r): ?>
              <tr>
                <td><?php if (!empty($r['image_path'])): ?><img class="shx-thumb" src="<?= htmlspecialchars($r['image_path']) ?>" alt=""><?php endif; ?></td>
                <td><?= htmlspecialchars($r['state']) ?></td>
                <td><?= (int)$r['rank_no'] ?></td>
                <td><?= htmlspecialchars($r['person_name']) ?></td>
                <td><?= htmlspecialchars($r['role_title'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['branch_name'] ?? '') ?></td>
                <td><?= date('d M Y', strtotime($r['display_start_date'])) ?> – <?= date('d M Y', strtotime($r['display_end_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($hlh_who !== 'winners'): ?>
        <div class="shx-section" style="margin-top:16px">Losers</div>
        <?php if (!$hlh_losers): ?>
          <div class="shx-muted">No losers found.</div>
        <?php else: ?>
        <table class="shx-table" style="margin-top:8px">
          <thead>
            <tr>
              <th>Photo</th><th>State</th><th>Name</th><th>Branch</th><th>Display Window</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hlh_losers as $r): ?>
              <tr>
                <td><?php if (!empty($r['image_path'])): ?><img class="shx-thumb" src="<?= htmlspecialchars($r['image_path']) ?>" alt=""><?php endif; ?></td>
                <td><?= htmlspecialchars($r['state']) ?></td>
                <td><?= htmlspecialchars($r['person_name']) ?></td>
                <td><?= htmlspecialchars($r['branch_name'] ?? '') ?></td>
                <td><?= date('d M Y', strtotime($r['display_start_date'])) ?> – <?= date('d M Y', strtotime($r['display_end_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      <?php endif; ?>

      -->
    <?php endif; ?>

  </div>
</div>

<script>
  (function(){
    window.hlhToggle = function(){
      const mode = document.getElementById('hlh_mode').value;
      const d    = document.getElementById('hlh_date_wrap');
      const r    = document.getElementById('hlh_range_wrap');
      if (mode === 'date') { d.style.display=''; r.style.display='none'; }
      else if (mode === 'range') { d.style.display='flex'; r.style.display='flex'; }
      else { d.style.display='none'; r.style.display='none'; }
    };
    hlhToggle();
  })();
</script>

            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
  <?php include("footer.php"); ?>
</div>

<script>
  (function(){
    const root  = document.getElementById('shx');
    if (!root) return;
    const wWrap = root.querySelector('#winners');
    const lWrap = root.querySelector('#losers');
    const wH    = root.querySelector('#__w_rows');
    const lH    = root.querySelector('#__l_rows');
    const addWinnerBtn = root.querySelector('#addWinner');
    if (addWinnerBtn) addWinnerBtn.addEventListener('click', () => {
      const card = document.createElement('div');
      card.className = 'shx-tp-card';
      card.innerHTML = `
        <button type="button" class="shx-row-close" title="Remove">×</button>
        <div class="shx-grid-3">
          <div><label>Rank</label><input type="number" name="w_rank[]" min="1" placeholder="1, 2, 3..."></div>
          <div><label>Name</label><input type="text" name="w_name[]" placeholder="Person Name"></div>
          <div><label>Role (optional)</label><input type="text" name="w_role[]" placeholder="Branch Manager"></div>
        </div>
        <div class="shx-grid">
          <div><label>Branch Name (optional)</label><input type="text" name="w_bname[]" placeholder="e.g., Jayanagar Branch"></div>
          <div><label>Photo (JPG/PNG/WEBP, ≤5MB)</label><input type="file" name="w_image[]" accept="image/jpeg,image/png,image/webp"></div>
        </div>`;
      wWrap.appendChild(card);
      if (wH) wH.value = (parseInt(wH.value||'1',10) + 1).toString();
    });
    const addLoserBtn = root.querySelector('#addLoser');
    if (addLoserBtn) addLoserBtn.addEventListener('click', () => {
      const card = document.createElement('div');
      card.className = 'shx-tp-card';
      card.innerHTML = `
        <button type="button" class="shx-row-close" title="Remove">×</button>
        <div class="shx-grid">
          <div><label>Name</label><input type="text" name="l_name[]" placeholder="Person Name"></div>
          <div><label>Branch Name (optional)</label><input type="text" name="l_bname[]" placeholder="e.g., Kumbakonam"></div>
        </div>
        <div class="shx-grid">
          <div><label>Photo (optional, JPG/PNG/WEBP, ≤5MB)</label><input type="file" name="l_image[]" accept="image/jpeg,image/png,image/webp"></div>
        </div>`;
      lWrap.appendChild(card);
      if (lH) lH.value = (parseInt(lH.value||'1',10) + 1).toString();
    });
    root.addEventListener('click', (e) => {
      if (e.target.classList.contains('shx-row-close')) {
        const card = e.target.closest('.shx-tp-card');
        if (!card) return;
        if (card.parentElement === wWrap && wH) {
          wH.value = Math.max(0, parseInt(wH.value||'1',10) - 1).toString();
        } else if (card.parentElement === lWrap && lH) {
          lH.value = Math.max(0, parseInt(lH.value||'1',10) - 1).toString();
        }
        card.remove();
      }
    });
  })();
</script>
