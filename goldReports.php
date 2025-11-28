<?php
ob_start(); // avoid headers already sent + allow redirects if you add later
session_start();

// Show ALL errors during debugging (comment out in prod)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Make mysqli throw exceptions so we never get a silent blank page
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$type = $_SESSION['usertype'] ?? '';
if ($type !== 'Branch') {
    include("logout.php");
    exit();
}

include("header.php");
include("menu.php");
include("dbConnection.php");

// Ensure utf8mb4
@mysqli_set_charset($con, 'utf8mb4');

// ---------- Helpers ----------
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function post($k){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : ''; }
function arr_get($a,$k,$d=null){ return isset($a[$k]) ? $a[$k] : $d; }

// Build a map of all branches for display
$branchMap = [];
try {
    $q = mysqli_query($con, "SELECT branchId, branchName FROM branch");
    while ($r = mysqli_fetch_assoc($q)) {
        $branchMap[$r['branchId']] = $r['branchName'];
    }
} catch (Throwable $e) {
    // Non-fatal; keep page usable
}

function branch_label(string $id, array $map): string {
    $id = trim($id);
    if ($id === '') return '';
    $name = $map[$id] ?? ($id === 'HO' ? 'Head Office' : $id);
    return $name . ' (' . $id . ')';
}
function branch_history_label(?string $csv, array $map): string {
    $parts = array_filter(array_map('trim', explode(',', (string)$csv)));
    $out = [];
    foreach ($parts as $p) { $out[] = branch_label($p, $map); }
    return implode(', ', $out);
}

$branchId   = $_SESSION['branchCode'] ?? '';
$branchName = '';
$currentState = '';
try {
    $stmt = mysqli_prepare($con, "SELECT branchName, state FROM branch WHERE branchId=?");
    mysqli_stmt_bind_param($stmt, "s", $branchId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $branchName = $row['branchName'] ?? '';
        $currentState = $row['state'] ?? '';
    }
    mysqli_stmt_close($stmt);
} catch (Throwable $e) {
    // Show in flash later
}

$branchOptions = [];
try {
    if ($currentState !== '') {
        // Normalize state and decide the extra states to include
        $st = strtolower(trim($currentState));
        $extraStateClause = '';
        if (in_array($st, ['tamil nadu','tamilnadu'], true)) {
            // If branch is in Tamil Nadu, also show Pondicherry/Puducherry branches
            $extraStateClause = " OR state IN ('Pondicherry','Puducherry')";
        } elseif (in_array($st, ['pondicherry','puducherry'], true)) {
            // If branch is in Pondicherry/Puducherry, also show Tamil Nadu branches
            $extraStateClause = " OR state IN ('Tamil Nadu','Tamilnadu')";
        }

        $sql = "
            SELECT branchId, branchName
            FROM branch
            WHERE status = 1
              AND branchId NOT IN ('AGPL000','AGPL999')
              AND branchId <> ?
              AND (
                    (state = ? $extraStateClause)
                 OR UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE')
                 OR UPPER(branchName) LIKE '%CORPORATE%'
              )
            ORDER BY
              CASE
                WHEN UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE') THEN 0
                WHEN UPPER(branchName) LIKE '%CORPORATE%' THEN 0
                ELSE 1
              END,
              branchName
        ";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $branchId, $currentState);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($b = mysqli_fetch_assoc($res)) { $branchOptions[] = $b; }
        mysqli_stmt_close($stmt);
    } else {
        // Fallback (unknown state): still include HO/Corporate options
        $sql = "
            SELECT branchId, branchName
            FROM branch
            WHERE status = 1
              AND branchId NOT IN ('AGPL000','AGPL999')
              AND branchId <> ?
              AND (
                    UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE')
                 OR UPPER(branchName) LIKE '%CORPORATE%'
              )
            ORDER BY
              CASE
                WHEN UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE') THEN 0
                WHEN UPPER(branchName) LIKE '%CORPORATE%' THEN 0
                ELSE 1
              END,
              branchName
        ";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $branchId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($b = mysqli_fetch_assoc($res)) { $branchOptions[] = $b; }
        mysqli_stmt_close($stmt);
    }
} catch (Throwable $e) {
    // Non-fatal
}

// ---------- Flash system ----------
$flash = ['class' => '', 'msg' => ''];
function set_flash(&$flash, $class, $msg) { $flash['class'] = $class; $flash['msg'] = $msg; }

// ---------- Handle forwarding + goldtare ----------
if (isset($_POST['update_forwarding'])) {
    try {
        $tareweight  = (float)post('tareweight');
        $tobranch    = post('tobranch');
        $packetcount = (int)post('packetcount');
        $selectedIds = isset($_POST['mul']) && is_array($_POST['mul']) ? array_map('intval', $_POST['mul']) : [];

        if ($tareweight <= 0) {
            set_flash($flash, 'danger', 'Enter a valid tare weight (> 0).');
        } elseif ($packetcount <= 0 || empty($selectedIds)) {
            set_flash($flash, 'danger', 'Select at least one packet (row checkbox).');
        } elseif ($tobranch === '') {
            set_flash($flash, 'danger', 'Please select a Forwarding Branch (first dropdown).');
        } else {
            // Update trans for each selected id
            foreach ($selectedIds as $id) {
                $sel = isset($_POST['fwd'][$id]) ? mysqli_real_escape_string($con, $_POST['fwd'][$id]) : mysqli_real_escape_string($con, $tobranch);
                if ($sel !== '') {
                    $sql = "
                        UPDATE trans
                        SET CurrentBranch = CASE
                            WHEN CurrentBranch IS NULL OR CurrentBranch = '' THEN '$sel'
                            ELSE CONCAT(CurrentBranch, ', ', '$sel')
                        END,
                            sta      = CASE WHEN '$sel' IN ('HO','AGPL000','AGPL999') THEN 'Checked' ELSE sta END,
                            staDate  = CASE WHEN '$sel' IN ('HO','AGPL000','AGPL999') THEN CURDATE()      ELSE staDate END
                        WHERE id = $id
                        LIMIT 1
                    ";
                    mysqli_query($con, $sql);
                }
            }

            // Insert goldtare
            $transIdsCsv = implode(',', $selectedIds);
            $qgt = "INSERT INTO goldtare (frombranch, tobranch, packetcount, tareweight, status, trans_ids, created_at)
                    VALUES (?, ?, ?, ?, 'Pending', ?, NOW())";
            $stmtGt = mysqli_prepare($con, $qgt);
            mysqli_stmt_bind_param($stmtGt, "ssids", $branchId, $tobranch, $packetcount, $tareweight, $transIdsCsv);
            mysqli_stmt_execute($stmtGt);
            mysqli_stmt_close($stmtGt);

            set_flash($flash, 'success', "Forwarded to ".esc($tobranch)."; saved tare (".esc($tareweight).") for ".esc($packetcount)." packet(s).");
        }
    } catch (Throwable $e) {
        set_flash($flash, 'danger', 'Error: '.$e->getMessage());
    }
}

// ---------- GOLDTARE workflow: Pending -> Received/Cancelled and forwarding from Received ----------
if (isset($_POST['update_goldtare'])) {
    try {
        $statusChange = $_POST['gt_status'] ?? [];   // id => 'Received'|'Cancelled'|''
        $forwardTo    = $_POST['gt_forward'] ?? [];  // id => branch

        $idsTouched = array_unique(array_merge(array_keys($statusChange), array_keys($forwardTo)));
        foreach ($idsTouched as $rawId) {
            $id = (int)$rawId;
            if ($id <= 0) continue;

            $newStatus = trim((string)arr_get($statusChange, $id, ''));
            $newFwd    = trim((string)arr_get($forwardTo, $id, ''));

            $rs = mysqli_query($con, "SELECT id, frombranch, tobranch, status, CurrentBranch, trans_ids FROM goldtare WHERE id=$id LIMIT 1");
            if (!$rs || mysqli_num_rows($rs) === 0) continue;
            $gt = mysqli_fetch_assoc($rs);

            if ($gt['tobranch'] !== $branchId) continue;

            $transIds = [];
            if (!empty($gt['trans_ids'])) {
                $transIds = array_filter(array_map('intval', explode(',', $gt['trans_ids'])));
            }

            $updateTransForIds = function(array $ids, $appendBranch) use ($con) {
                $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
                if (empty($ids) || $appendBranch === '') return;

                $idList = implode(',', $ids);
                $appendBranchSafe = mysqli_real_escape_string($con, $appendBranch);

                $sql = "
                    UPDATE trans
                    SET CurrentBranch = CASE
                            WHEN CurrentBranch IS NULL OR CurrentBranch='' THEN '$appendBranchSafe'
                            ELSE CONCAT(CurrentBranch, ', ', '$appendBranchSafe')
                        END,
                        sta     = CASE WHEN '$appendBranchSafe' IN ('HO','AGPL000','AGPL999') THEN 'Checked' ELSE sta END,
                        staDate = CASE WHEN '$appendBranchSafe' IN ('HO','AGPL000','AGPL999') THEN CURDATE()  ELSE staDate END
                    WHERE id IN ($idList)
                ";
                mysqli_query($con, $sql);
            };

            if ($gt['status'] === 'Pending') {
                if ($newStatus === 'Cancelled') {
                    mysqli_query($con, "UPDATE goldtare SET status='Cancelled', updated_at=NOW() WHERE id=$id LIMIT 1");
                    continue;
                }

                if ($newStatus === 'Received') {
                    // Mark received (append this branch if not in history)
                    $needAppend = true;
                    if (!empty($gt['CurrentBranch']) && strpos($gt['CurrentBranch'], $branchId) !== false) {
                        $needAppend = false;
                    }
                    if ($needAppend) {
                        mysqli_query($con, "
                            UPDATE goldtare
                            SET CurrentBranch = CASE
                                  WHEN CurrentBranch IS NULL OR CurrentBranch='' THEN '".$branchId."'
                                  ELSE CONCAT(CurrentBranch, ', ', '".$branchId."')
                                END,
                                status='Received',
                                updated_at=NOW()
                            WHERE id=$id
                            LIMIT 1
                        ");
                    } else {
                        mysqli_query($con, "UPDATE goldtare SET status='Received', updated_at=NOW() WHERE id=$id");
                    }

                    // reflect in trans
                    $updateTransForIds($transIds, $branchId);

                    // Optional immediate forward
                    if ($newFwd !== '') {
                        $safeFwd = mysqli_real_escape_string($con, $newFwd);
                        mysqli_query($con, "
                            UPDATE goldtare
                            SET
                              CurrentBranch = CASE
                                  WHEN CurrentBranch IS NULL OR CurrentBranch='' THEN CONCAT('".$branchId."', ', ', '".$safeFwd."')
                                  ELSE CONCAT(CurrentBranch, ', ', '".$safeFwd."')
                              END,
                              tobranch='".$safeFwd."',
                              status='Pending',
                              updated_at=NOW()
                            WHERE id=$id
                            LIMIT 1
                        ");
                        $updateTransForIds($transIds, $safeFwd);
                    }
                    continue;
                }

                // If user selects forward without Received, ignore per your rule
                if ($newStatus === '' && $newFwd !== '') {
                    continue;
                }
            }

            if ($gt['status'] === 'Received') {
                if ($newFwd !== '') {
                    $safeFwd = mysqli_real_escape_string($con, $newFwd);
                    mysqli_query($con, "
                        UPDATE goldtare
                        SET
                          CurrentBranch = CASE
                              WHEN CurrentBranch IS NULL OR CurrentBranch='' THEN CONCAT('".$branchId."', ', ', '".$safeFwd."')
                              ELSE CONCAT(CurrentBranch, ', ', '".$safeFwd."')
                          END,
                          tobranch='".$safeFwd."',
                          status='Pending',
                          updated_at=NOW()
                        WHERE id=$id
                        LIMIT 1
                    ");
                    $updateTransForIds($transIds, $safeFwd);
                }
                continue;
            }
        }

        set_flash($flash, 'success', 'Goldtare updates saved.');
    } catch (Throwable $e) {
        set_flash($flash, 'danger', 'Error: '.$e->getMessage());
    }
}
?>
<style>
    #wrapper{ background:#f5f5f5; }
    #wrapper h3{ text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
    .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
    .btn-primary{ background-color:#123C69; }
    .theadRow{ text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }
    .dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
    .btn-success{ display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; text-decoration:none; font-size:12px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa; background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative; }
    .totalGold td{ font-weight:bold; }
    select.form-control { padding: 4px 6px; height: 30px; }
    .alert{ padding:8px 12px; border-radius:6px; margin-bottom:10px; }
    .alert.success{ background:#e7f6e9; color:#0b5; border:1px solid #0b5; }
    .alert.danger{ background:#fdecea; color:#c00; border:1px solid #e0a1a1; }
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="col-sm-12">
                        <h3><span style="color:#900" class="fa fa-file-text"></span> GOLD SEND REPORT</h3>
                    </div>
                    <div style="clear:both"></div>

                    <div class="col-sm-6">
                        <div class="input-group" style="margin-top:23px">
                            <span class="input-group-addon"><span style="color:#990000" class="fa fa-institution"></span></span>
                            <input style="font-weight:bold;color:#900;text-transform:uppercase;" type="text" readonly class="form-control"
                                   value="Branch : <?php echo esc($branchName).' ('.esc($branchId).')'; ?>">
                        </div>
                    </div>
                    <div style="clear:both"></div>
                </div>

                <div class="panel-body" style="border:5px solid #fff;border-radius:10px;padding:20px;box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);background-color:#F5F5F5;">

                    <?php if ($flash['msg']): ?>
                        <div class="alert <?php echo esc($flash['class']); ?>">
                            <?php echo $flash['msg']; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="goldSendForm" action="<?php echo esc($_SERVER['PHP_SELF']); ?>">
                        <table id="example2" class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr align="center" class="theadRow">
                                <th><i class="fa fa-sort-numeric-asc"></i></th>
                                <td><b>Bill ID</b></td>
                                <td><b>Date & Time</b></td>
                                <td><b>Gross Weight</b></td>
                                <td><b>Net Weight</b></td>
                                <td><b><span class="fa fa-money"></span> Gr Amt</b></td>
                                <td><b><span class="fa fa-money"></span> Net Amt</b></td>
                                <td><b><span class="fa fa-money"></span> Gold Rate</b></td>
                                <td><b><span class="fa fa-money"></span> Purity</b></td>
                                <td><b><span class="fa fa-money"></span> Status</b></td>
                                <td><b>Billed Branch</b></td>
                                <td style="width:210px"><b>Forwarding Branch</b></td>
                                <td style="width:90px">
                                  <b>Action</b><br>
                                  <label style="font-weight:normal">
                                    <input type="checkbox" id="checkAll">
                                  </label>
                                </td>
                                <td style="width:120px"><b>Remarks</b></td>
                            </tr>
                            <?php
                            $i = 1;
                            try {
                                $sql1 = mysqli_query($con, "
                                    SELECT id,billId,grossW,netW,grossA,netA,rate,purity,status,date,time,branchId,CurrentBranch
                                    FROM trans
                                    WHERE status='Approved'
                                      AND metal='Gold'
                                      AND sta!='Checked'
                                      AND TRIM(SUBSTRING_INDEX(CurrentBranch, ',', -1)) = '".mysqli_real_escape_string($con,$branchId)."'
                                      AND branchId = '".mysqli_real_escape_string($con,$branchId)."'
                                    ORDER BY date DESC, time DESC, id DESC
                                ");
                                while ($row1 = mysqli_fetch_assoc($sql1)) {
                                    $lastHop = $row1['CurrentBranch'];
                                    if ($lastHop !== null && $lastHop !== '') {
                                        $parts = explode(',', $lastHop);
                                        $lastHop = trim(end($parts));
                                    } else {
                                        $lastHop = '';
                                    }

                                    echo "<tr>";
                                    echo "<td style='width:50px'>" . $i .  "</td>";
                                    echo "<td>" . esc($row1['billId']) . "</td>";
                                    echo "<td>" . esc($row1['date']) . "<br>". esc($row1['time']) ."</td>";
                                    echo "<td>" . round($row1['grossW'],2). "</td>";
                                    echo "<td>" . round($row1['netW'],2) . "</td>";
                                    echo "<td>" . round($row1['grossA'],0) . "</td>";
                                    echo "<td>" . round($row1['netA'],0) . "</td>";
                                    echo "<td>" . esc($row1['rate']) . "</td>";
                                    echo "<td>" . round($row1['purity'],2) . "</td>";
                                    echo "<td>" . esc($row1['status']) . "</td>";

                                    echo "<td><span class='label label-default'>". esc(branch_label($row1['branchId'], $branchMap)) ."</span></td>";

                                    echo "<td>";
                                    echo "<select class='form-control fwd-select' name='fwd[".(int)$row1['id']."]'>";
                                    echo "<option value=''>-- Select Branch --</option>";
                                    foreach ($branchOptions as $opt) {
                                        if ($opt['branchId'] === $branchId) continue;
                                        $val = $opt['branchId'];
                                        $text = $opt['branchName']." (".$opt['branchId'].")";
                                        $selAttr = ($val === $lastHop) ? " selected" : "";
                                        echo "<option value='".esc($val)."'$selAttr>".esc($text)."</option>";
                                    }
                                    // ensure HO in list
                                    $hasHO = false;
                                    foreach ($branchOptions as $bo){ if (strtoupper($bo['branchId'])==='HO') { $hasHO=true; break; } }
                                    if (!$hasHO) {
                                        $selAttr = ($lastHop === 'HO') ? " selected" : "";
                                        echo "<option value='HO'$selAttr>Head Office (HO)</option>";
                                    }
                                    echo "</select>";
                                    echo "</td>";
                                    echo "<td><input type='checkbox' class='row-check' name='mul[]' value='".(int)$row1['id']."'/></td>";
                                    echo "<td></td>";
                                    echo "</tr>";
                                    $i++;
                                }
                            } catch (Throwable $e) {
                                echo "<tr><td colspan='14' class='dataTables_empty'>Error loading rows: ".esc($e->getMessage())."</td></tr>";
                            }
                            ?>
                            <tr class="totalGold">
                                <?php
                                $row2 = ['netW'=>0,'grossW'=>0,'netA'=>0,'grossA'=>0,'count'=>0,'rateAvg'=>0];
                                try {
                                    $sql2 = mysqli_query($con, "
                                        SELECT ROUND(SUM(netW),2)  AS netW,
                                               ROUND(SUM(grossW),2) AS grossW,
                                               ROUND(SUM(netA),2)   AS netA,
                                               ROUND(SUM(grossA))   AS grossA,
                                               COUNT(id)            AS count,
                                               AVG(rate)            AS rateAvg
                                        FROM trans
                                        WHERE status='Approved'
                                          AND metal='Gold'
                                          AND sta!='Checked'
                                          AND TRIM(SUBSTRING_INDEX(CurrentBranch, ',', -1)) = '".mysqli_real_escape_string($con,$branchId)."'
                                          AND branchId = '".mysqli_real_escape_string($con,$branchId)."'
                                    ");
                                    $row2 = mysqli_fetch_assoc($sql2) ?: $row2;
                                } catch (Throwable $e) {
                                    // keep defaults
                                }
                                $pur = ($row2 && ($row2['netW'] ?? 0) != 0)
                                      ? ((($row2['grossA'] ?? 0)/($row2['netW'] ?? 1))/max(1e-9, ($row2['rateAvg'] ?? 0))*100)
                                      : 0;
                                ?>
                                <th colspan="2" class="text-success">Gross Weight</th>
                                <td><?php echo round($row2['grossW'] ?? 0,2);?></td>
                                <th class="text-success">Net Weight</th>
                                <td><?php echo round($row2['netW'] ?? 0,2);?></td>
                                <th class="text-success">Gross Amount</th>
                                <td><?php echo round($row2['grossA'] ?? 0,0);?></th>
                                <th class="text-success">Net Amount</th>
                                <td><?php echo round($row2['netA'] ?? 0,2);?></td>
                                <th class="text-success">Average Purity</th>
                                <td><?php echo round($pur,2);?> %</td>
                                <th class="text-success" colspan="2">Packets: <?php echo (int)($row2['count'] ?? 0);?></th>
                            </tr>

                            <!-- Bottom: Tare + hidden autos -->
                            <tr>
                                <td colspan="14" style="background:#fff; padding:12px; border-top:2px solid #ddd;">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label style="margin-bottom:4px;">Tare Weight (gms)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="tareweight" id="tareweight" placeholder="Enter tare weight">
                                        </div>
                                    </div>
                                    <input type="hidden" name="tobranch" id="tobranch" value="">
                                    <input type="hidden" name="packetcount" id="packetcount" value="0">
                                </td>
                            </tr>

                            <!-- Single combined button -->
                            <tr>
                              <td colspan="14">
                                <button type="button" id="btnUpdatePrint" class="btn btn-success">
                                  <i class="fa fa-print"></i> Update Forwarding &amp; Print PDF
                                </button>
                              </td>
                            </tr>

                            </tbody>
                        </table>
                    </form>

                </div>
            </div>

            <!-- GOLDTARE inbox -->
            <div class="hpanel" style="margin-top:18px;">
              <div class="panel-heading">
                <h3 style="margin:0 0 10px 0;"><span class="fa fa-inbox" style="color:#900"></span> GOLDTARE Receive and Forward (Branch: <?php echo esc($branchId); ?>)</h3>
              </div>
              <div class="panel-body" style="border:5px solid #fff;border-radius:10px;padding:16px;box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);background-color:#FDFDFD;">

                <?php
                try {
                    $branchEsc = mysqli_real_escape_string($con, $branchId);
                    $gtRes = mysqli_query($con, "
                        SELECT id, frombranch, tobranch, packetcount, tareweight, status, created_at, CurrentBranch
                        FROM goldtare
                        WHERE tobranch = '".$branchEsc."'
                          AND status IN ('Pending','Received')
                        ORDER BY FIELD(status, 'Pending','Received'), id DESC
                    ");
                } catch (Throwable $e) {
                    $gtRes = false;
                    echo '<div class="alert danger">Error loading goldtare: '.esc($e->getMessage()).'</div>';
                }
                ?>

                <form method="POST" action="<?php echo esc($_SERVER['PHP_SELF']); ?>">
                  <input type="hidden" name="update_goldtare" value="1" />
                  <table class="table table-striped table-bordered table-hover">
                    <thead class="theadRow">
                      <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Packets</th>
                        <th>Tare (g)</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Current Branch History</th>
                        <th style="width:180px;">Action</th>
                        <th style="width:240px;">Forward To</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $n=1;
                      if ($gtRes && mysqli_num_rows($gtRes)>0):
                        while($g = mysqli_fetch_assoc($gtRes)):
                      ?>
                      <tr>
                        <td><?php echo $n++; ?></td>
                        <td><span class="label label-default"><?php echo esc(branch_label($g['frombranch'], $branchMap)); ?></span></td>
                        <td><span class="label label-info"><?php echo esc(branch_label($g['tobranch'], $branchMap)); ?></span></td>
                        <td><?php echo (int)$g['packetcount']; ?></td>
                        <td><?php echo number_format((float)$g['tareweight'],2); ?></td>
                        <td><?php echo esc($g['status']); ?></td>
                        <td><?php echo esc($g['created_at']); ?></td>
                        <td style="max-width:320px; white-space:normal;"><?php echo esc(branch_history_label($g['CurrentBranch'] ?? '', $branchMap)); ?></td>

                        <td>
                          <?php if ($g['status'] === 'Pending'): ?>
                            <select name="gt_status[<?php echo (int)$g['id']; ?>]" class="form-control">
                              <option value="">— choose —</option>
                              <option value="Received">Received</option>
                              <option value="Cancelled">Cancelled</option>
                            </select>
                          <?php else: ?>
                            <em style="color:#555;">Received here</em>
                          <?php endif; ?>
                        </td>

                        <td>
                          <?php
                            $hasHO = false;
                            foreach ($branchOptions as $bo){ if (strtoupper($bo['branchId'])==='HO') { $hasHO=true; break; } }
                          ?>
                          <?php if ($g['status'] === 'Pending'): ?>
                            <select name="gt_forward[<?php echo (int)$g['id']; ?>]" class="form-control">
                              <option value="">— forward after Received —</option>
                              <?php foreach ($branchOptions as $opt): ?>
                                <option value="<?php echo esc($opt['branchId']); ?>"><?php echo esc($opt['branchName']." (".$opt['branchId'].")"); ?></option>
                              <?php endforeach; if (!$hasHO): ?>
                                <option value="HO">Head Office (HO)</option>
                              <?php endif; ?>
                            </select>
                          <?php else: ?>
                            <select name="gt_forward[<?php echo (int)$g['id']; ?>]" class="form-control">
                              <option value="">— select branch to forward —</option>
                              <?php foreach ($branchOptions as $opt): ?>
                                <option value="<?php echo esc($opt['branchId']); ?>"><?php echo esc($opt['branchName']." (".$opt['branchId'].")"); ?></option>
                              <?php endforeach; if (!$hasHO): ?>
                                <option value="HO">Head Office (HO)</option>
                              <?php endif; ?>
                            </select>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="10" class="text-center">No items for this branch.</td>
                      </tr>
                      <?php endif; ?>
                    </tbody>

                    <?php if ($gtRes && mysqli_num_rows($gtRes)>0): ?>
                    <tfoot>
                      <tr>
                        <td colspan="10" class="text-right">
                          <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Apply Updates
                          </button>
                        </td>
                      </tr>
                    </tfoot>
                    <?php endif; ?>
                  </table>
                </form>
              </div>
            </div>

        </div>
        <div style="clear:both"><br></div>
    </div>
<?php include("footer.php"); ?>
<script>
(function(){
  function $$all(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }

  var mainForm   = document.getElementById('goldSendForm');
  var btnUpdatePrint = document.getElementById('btnUpdatePrint');

  function syncTobranchFromFirst(){
    var first = document.querySelector('.fwd-select');
    if (!first) return;
    var val = first.value;
    // Mirror first to others (SEND TABLE)
    $$all('.fwd-select').forEach(function(sel, idx){
      if (idx === 0) return;
      sel.value = val;
    });
    var tb = document.getElementById('tobranch');
    if (tb) tb.value = val;
  }
  function syncCountsAndBranch(){
    var cnt = $$all('.row-check').filter(function(cb){ return cb.checked; }).length;
    var pc  = document.getElementById('packetcount');
    if (pc) pc.value = String(cnt);
    syncTobranchFromFirst();
  }

  // === CHANGE HANDLER FOR BOTH TABLES ===
  document.addEventListener('change', function(e){
    // --- SEND TABLE: select all rows / checkboxes ---
    var first = document.querySelector('.fwd-select');
    if (first && e.target === first) {
      syncTobranchFromFirst();
    }
    if (e.target && e.target.id === 'checkAll') {
      $$all('.row-check').forEach(function(cb){ cb.checked = e.target.checked; });
      syncCountsAndBranch();
    }
    if (e.target && e.target.matches('.row-check')) {
      syncCountsAndBranch();
    }

    // --- GOLDTARE TABLE: STATUS (Received) auto-apply to all ---
    if (e.target && e.target.matches('select[name^="gt_status["]')) {
      // Only when "Received" is chosen
      if (e.target.value === 'Received') {
        $$all('select[name^="gt_status["]').forEach(function(sel){
          sel.value = 'Received';
        });
      }
    }

    // --- GOLDTARE TABLE: FORWARD TO auto-apply to all ---
    if (e.target && e.target.matches('select[name^="gt_forward["]')) {
      var v = e.target.value;
      if (v !== '') {
        $$all('select[name^="gt_forward["]').forEach(function(sel){
          sel.value = v;
        });
      }
    }
  });

  // Init
  syncTobranchFromFirst();
  syncCountsAndBranch();

  function appendSelectedIdsAsHidden() {
    // Clean previous injections
    $$all('#goldSendForm input[type="hidden"][data-injected="mul"]').forEach(function(n){ n.remove(); });
    $$all('.row-check:checked').forEach(function(cb){
      var hid = document.createElement('input');
      hid.type = 'hidden';
      hid.name = 'mul[]';
      hid.value = cb.value;
      hid.setAttribute('data-injected', 'mul');
      mainForm.appendChild(hid);
    });
  }

  if (btnUpdatePrint && mainForm){
    btnUpdatePrint.addEventListener('click', function(){
      var selected = $$all('.row-check:checked');
      if (selected.length === 0){
        alert('Please select at least one row before proceeding.');
        return;
      }

      syncCountsAndBranch();

      var tbEl = document.getElementById('tobranch');
      var pcEl = document.getElementById('packetcount');
      var twEl = document.getElementById('tareweight');

      var tb = (tbEl && tbEl.value ? tbEl.value.trim() : '');
      var pc = parseInt(pcEl && pcEl.value ? pcEl.value : '0', 10);
      var twStr = (twEl && twEl.value ? twEl.value.trim() : '');
      var tw = parseFloat(twStr);

      if (twStr === '' || isNaN(tw) || tw <= 0) {
        alert('Enter a valid tare weight (> 0).');
        return;
      }
      if (pc <= 0) {
        alert('Select at least one packet (row checkbox).');
        return;
      }
      if (!tb) {
        alert('Please select a Forwarding Branch (first dropdown).');
        return;
      }

      appendSelectedIdsAsHidden();

      // Hidden flag to trigger PHP update_forwarding
      var upd = document.getElementById('__upd_flag');
      if (!upd) {
        upd = document.createElement('input');
        upd.type = 'hidden';
        upd.name = 'update_forwarding';
        upd.value = '1';
        upd.id = '__upd_flag';
        mainForm.appendChild(upd);
      }

      var fd = new FormData(mainForm);

      btnUpdatePrint.disabled = true;
      btnUpdatePrint.innerHTML = 'Processing...';

      // First: call this same page to perform update_forwarding
      fetch(window.location.href, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      }).then(function(resp){
        if (!resp.ok) throw new Error('Server error (' + resp.status + ')');

        // Remove update flag so PDF submit does not re-run update_forwarding
        if (upd && upd.parentNode) upd.parentNode.removeChild(upd);

        // Now: open PDF in new tab
        var prevAction = mainForm.action;
        var prevTarget = mainForm.target;

        var existing = document.getElementById('__pdf_first_fwd');
        if (existing) existing.remove();
        var firstFwd = document.querySelector('.fwd-select');
        var hidF = document.createElement('input');
        hidF.type = 'hidden';
        hidF.name = 'fwd_first';
        hidF.id   = '__pdf_first_fwd';
        hidF.value = firstFwd ? (firstFwd.value || '') : '';
        mainForm.appendChild(hidF);

        mainForm.action = 'goldTransferPdfReport.php';
        mainForm.target = '_blank';
        mainForm.submit();
        mainForm.action = prevAction || '';
        mainForm.target = prevTarget || '';

      }).catch(function(err){
        alert('Error while updating forwarding:\n' + err.message);
      }).finally(function(){
        btnUpdatePrint.disabled = false;
        btnUpdatePrint.innerHTML = '<i class="fa fa-print"></i> Update Forwarding & Print PDF';
      });
    });
  }
})();
</script>

