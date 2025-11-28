<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'] ?? '';
if ($type === 'Branch') {
    include("header.php");
    include("menu.php");
} else {
    include("logout.php");
    exit();
}

include("dbConnection.php");

$branchId     = $_SESSION['branchCode'] ?? '';
$branchRow    = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName, state FROM branch WHERE branchId='$branchId'"));
$branchName   = $branchRow['branchName'] ?? '';
$currentState = $branchRow['state'] ?? '';

/* --------- load ACTIVE branches in same state + HO (exclude AGPL000/AGPL999), HO on top ---------- */
$branchOptions = [];

if ($currentState !== '') {
    $sql = "
    SELECT branchId, branchName
    FROM branch
    WHERE status = 1
      AND branchId NOT IN ('AGPL000','AGPL999')
      AND branchId <> ?
      AND (
            state = ?
         OR  UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE')
         OR  UPPER(branchName) LIKE '%CORPORATE%'
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
    while ($b = mysqli_fetch_assoc($res)) {
        $branchOptions[] = $b;
    }
    mysqli_stmt_close($stmt);
} else {
    // Fallback: only HO-like entries + current branch (if active), excluding AGPL000/AGPL999; HO on top
    $sql = "
    SELECT branchId, branchName
    FROM branch
    WHERE status = 1
      AND branchId NOT IN ('AGPL000','AGPL999')
      AND branchId <> ?
      AND (
            UPPER(branchName) IN ('HO', 'HEAD OFFICE', 'HEAD-OFFICE')
         OR  UPPER(branchName) LIKE '%CORPORATE%'
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
    while ($b = mysqli_fetch_assoc($res)) {
        $branchOptions[] = $b;
    }
    mysqli_stmt_close($stmt);
}


/* --------- handle forwarding updates (with client-side redirect) ---------- */
if (isset($_POST['update_forwarding']) && !empty($_POST['mul']) && is_array($_POST['mul'])) {
    foreach ($_POST['mul'] as $rid) {
        $id  = (int)$rid;
        $sel = isset($_POST['fwd'][$id]) ? mysqli_real_escape_string($con, $_POST['fwd'][$id]) : '';

        if ($sel !== '') {
            // Append to CurrentBranch history: "..., NEW"
            // If empty/null, just set NEW
            // If Forwarding is HO or either AGPL000 / AGPL999 => mark as Checked and stamp staDate
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

    // Redirect back to this page without using header() (since output started already)
    $self = htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'), ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!doctype html>
<html><head>
<meta charset="utf-8">
<title>Updating…</title>
<noscript><meta http-equiv="refresh" content="0;url={$self}"></noscript>
<script>
  // Replace so back button won't resubmit the form
  window.location.replace("{$self}");
</script>
</head><body></body></html>
HTML;
    exit();
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
    .btn-success{ display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box;
        text-decoration:none; font-size:12px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa;
        background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative; }
    .totalGold td{ font-weight:bold; }
    select.form-control { padding: 4px 6px; height: 30px; }
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

                    <!-- Branch banner only (no date filters) -->
                    <div class="col-sm-6">
                        <div class="input-group" style="margin-top:23px">
                            <span class="input-group-addon"><span style="color:#990000" class="fa fa-institution"></span></span>
                            <input style="font-weight:bold;color:#900;text-transform:uppercase;" type="text" readonly class="form-control"
                                   value="Branch : <?php echo htmlspecialchars($branchName).' ('.htmlspecialchars($branchId).')'; ?>">
                        </div>
                    </div>
                    <div style="clear:both"></div>
                </div>

                <div class="panel-body" style="border:5px solid #fff;border-radius:10px;padding:20px;box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);background-color:#F5F5F5;">

                    <form method="POST" action="">
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
                                    <input type="checkbox" id="checkAll"> Select All
                                  </label>
                                </td>
                                <td style="width:120px"><b>Remarks</b></td>
                            </tr>
                            <?php
                            $i = 1;

                            // Helper to get last hop of CurrentBranch in SQL
                            // We'll filter rows where the *last* entry equals the logged-in $branchId
                            $sql1 = mysqli_query($con, "
                                SELECT id,billId,grossW,netW,grossA,netA,rate,purity,status,date,time,branchId,CurrentBranch
                                FROM trans
                                WHERE status='Approved'
                                  AND metal='Gold'
                                  AND sta!='Checked'
                                  AND TRIM(SUBSTRING_INDEX(CurrentBranch, ',', -1)) = '$branchId'
                                ORDER BY date DESC, time DESC, id DESC
                            ");
                            while ($row1 = mysqli_fetch_assoc($sql1)) {
                                // Determine the last hop for display/select default
                                $lastHop = $row1['CurrentBranch'];
                                if ($lastHop !== null && $lastHop !== '') {
                                    $parts = explode(',', $lastHop);
                                    $lastHop = trim(end($parts));
                                } else {
                                    $lastHop = '';
                                }

                                echo "<tr>";
                                echo "<td style='width:50px'>" . $i .  "</td>";
                                echo "<td>" . htmlspecialchars($row1['billId']) . "</td>";
                                echo "<td>" . htmlspecialchars($row1['date']) . "<br>". htmlspecialchars($row1['time']) ."</td>";
                                echo "<td>" . round($row1['grossW'],2). "</td>";
                                echo "<td>" . round($row1['netW'],2). "</td>";
                                echo "<td>" . round($row1['grossA'],0) . "</td>";
                                echo "<td>" . round($row1['netA'],0) . "</td>";
                                echo "<td>" . htmlspecialchars($row1['rate']) . "</td>";
                                echo "<td>" . round($row1['purity'],2) . "</td>";
                                echo "<td>" . htmlspecialchars($row1['status']) . "</td>";

                                // Billed Branch shows trans.branchId
                                echo "<td><span class='label label-default'>". htmlspecialchars($row1['branchId']) ."</span></td>";

                                // Forwarding Branch dropdown (default = last hop)
                                echo "<td>";
                                echo "<select class='form-control fwd-select' name='fwd[".$row1['id']."]'>"; // <-- added fwd-select
                                echo "<option value=''>-- Select Branch --</option>";
                                foreach ($branchOptions as $opt) {
                                    if ($opt['branchId'] === $branchId) continue; // safety guard
                                    $val = $opt['branchId'];
                                    $text = $opt['branchName']." (".$opt['branchId'].")";
                                    $selAttr = ($val === $lastHop) ? " selected" : "";
                                    echo "<option value='".htmlspecialchars($val)."'$selAttr>".htmlspecialchars($text)."</option>";
                                }
                                // ensure HO option exists
                                if (!array_filter($branchOptions, function($o){ return $o['branchId']==='HO'; })) {
                                    $selAttr = ($lastHop === 'HO') ? " selected" : "";
                                    echo "<option value='HO'$selAttr>Head Office (HO)</option>";
                                }
                                echo "</select>";
                                echo "</td>";

                                echo "<td><input type='checkbox' class='row-check' name='mul[]' value='".(int)$row1['id']."'/></td>"; // <-- class row-check
                                echo "<td></td>";
                                echo "</tr>";
                                $i++;
                            }
                            ?>
                            <tr class="totalGold">
                                <?php
                                // Totals for rows whose last hop is this branch
                                $sql2 = mysqli_query($con, "
                                    SELECT ROUND(SUM(netW),2)  AS netW,
                                           ROUND(SUM(grossW),2)AS grossW,
                                           ROUND(SUM(netA),2)  AS netA,
                                           ROUND(SUM(grossA))  AS grossA,
                                           COUNT(id)           AS count,
                                           AVG(rate)           AS rateAvg
                                    FROM trans
                                    WHERE status='Approved'
                                      AND metal='Gold'
                                      AND sta!='Checked'
                                      AND TRIM(SUBSTRING_INDEX(CurrentBranch, ',', -1)) = '$branchId'
                                ");
                                $row2 = mysqli_fetch_assoc($sql2);
                                $pur = ($row2 && ($row2['netW'] ?? 0) != 0)
                                      ? ((($row2['grossA'] ?? 0)/($row2['netW'] ?? 1))/($row2['rateAvg'] ?? 1)*100)
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

                            <tr>
                                <td colspan="14">
                                <button type="submit" class="btn btn-primary" id="btnPrint" formaction="goldTransferPdfReport.php" formtarget="_blank">
                                <i style="color:#fa0" class="fa fa-print"></i> Print Gold Send Report
                                </button>
                                <button type="submit" class="btn btn-success" name="update_forwarding" value="1" id="btnUpdate" disabled>
                                <i class="fa fa-exchange"></i> Update Forwarding
                                </button>
                                &nbsp;
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>

                </div>
            </div>
        </div>
        <div style="clear:both"><br></div>
    </div>
<?php include("footer.php"); ?>
<script>
document.getElementById("search_walkin").addEventListener("click", function(){
    var url = window.location.href.split('?')[0];
    var branch_id = document.getElementById("search_branchId").value;
    if(branch_id === ""){
        alert("Please select a branch");
    } else {
        window.open(url + "?branchId=" + encodeURIComponent(branch_id), "_self");
    }
});
</script>

<script>
(function(){
  // Track whether print was clicked
  var printed = false;
  var btnUpdate = document.getElementById('btnUpdate');
  var btnPrint  = document.getElementById('btnPrint');
  var form      = btnUpdate.closest('form');

  // When Print is clicked, mark printed=true and enable Update
  btnPrint.addEventListener('click', function(){
    printed = true;
    if (btnUpdate) btnUpdate.disabled = false;
  });

  // Hard-guard: if user clicks Update first, block and alert
  btnUpdate.addEventListener('click', function(e){
    if (!printed) {
      e.preventDefault();
      e.stopPropagation();
      alert('Please take the printout first before updating forwarding.');
      return false;
    }
  });

  // Extra safety: if the form is submitted via Enter key with Update focused
  form.addEventListener('submit', function(e){
    // Check if submitter is the Update button (modern browsers)
    var submitter = e.submitter || document.activeElement;
    var isUpdate = submitter && submitter.id === 'btnUpdate';
    if (isUpdate && !printed) {
      e.preventDefault();
      alert('Please take the printout first before updating forwarding.');
      return false;
    }
  });
})();
</script>

<!-- NEW: Select All + First dropdown mirrors to all -->
<script>
(function(){
  function $all(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }

  // --- Select All checkboxes ---
  var checkAll = document.getElementById('checkAll');
  if (checkAll) {
    checkAll.addEventListener('change', function(){
      $all('.row-check').forEach(function(cb){ cb.checked = checkAll.checked; });
    });
  }

  // --- First dropdown controls all others ---
  var allSelects = $all('.fwd-select');
  if (allSelects.length > 0) {
    var first = allSelects[0];
    first.addEventListener('change', function(){
      var val = first.value;
      for (var i = 1; i < allSelects.length; i++) {
        allSelects[i].value = val;
      }
    });
  }
})();
</script>
