<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'] ?? '';
if ($type === 'Branch') {
    include("header.php");
    include("menu.php");
} elseif($type=='AccHead') {
    include("header.php");
    include("menuaccHeadPage.php");
} elseif ($type === 'Master') {
    include("header.php");
    include("menumaster.php");
} else {
    include("logout.php");
    exit();
}

include("dbConnection.php");

$date       = date('Y-m-d');
$branchId   = $_SESSION['branchCode'] ?? '';
$branchRow  = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branchId'"));
$branchName = $branchRow['branchName'] ?? '';

$searchTo   = isset($_GET['dat2']) ? $_GET['dat2'] : '';
$searchFrom = isset($_GET['dat3']) ? $_GET['dat3'] : '';

$from = ($searchFrom !== "" ? $searchFrom : $date);
$to   = ($searchTo   !== "" ? $searchTo   : $date);

/* ---------- Branch name map (active) ---------- */
$branchNameMap = [];
$qB = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1");
while ($r = mysqli_fetch_assoc($qB)) {
  $branchNameMap[$r['branchId']] = $r['branchName'];
}
$branchNameMap['HO'] = $branchNameMap['HO'] ?? 'Head Office';

/* ===================== Pull ALL rows & derive LAST HOP ===================== */
$allRows = [];
$qAll = mysqli_query($con, "
    SELECT t.id, t.billId, t.date, t.time, t.grossW, t.netW, t.grossA, t.netA, t.rate, t.purity,
           t.status, t.branchId AS billedBranch, t.CurrentBranch, t.received
    FROM trans t
    WHERE t.metal='Gold'
      AND t.status='Approved'
      AND t.date BETWEEN '$from' AND '$to'
      AND IFNULL(TRIM(t.CurrentBranch),'') <> ''   /* exclude NULL/empty CurrentBranch rows */
    ORDER BY t.date DESC, t.time DESC, t.id DESC
");

while ($r = mysqli_fetch_assoc($qAll)) {
    // Parse CurrentBranch as a trail: "AGPL001, AGPL169, AGPL001, HO"
    $trail = array_filter(array_map('trim', explode(',', (string)$r['CurrentBranch']))); 
$trail = array_values($trail);

// Replace branchId with branchName from the branch table
$trailWithNames = [];
foreach ($trail as $branchId) {
    $branchName = isset($branchNameMap[$branchId]) ? $branchNameMap[$branchId] : $branchId; // Use branchName from map
    $trailWithNames[] = $branchName; // Add the branchName to the trail
}

// Remove consecutive duplicates in the travel path
$uniqueTrail = [];
foreach ($trailWithNames as $branch) {
    if (empty($uniqueTrail) || end($uniqueTrail) !== $branch) {
        $uniqueTrail[] = $branch;
      }
  }

    // Set the cleaned-up travel string
    $r['_travelStr'] = $uniqueTrail ? implode(' -> ', $uniqueTrail) : '—';

    $lastHop = $trail ? end($trail) : ''; // keep original case for display
    $r['_lastHop']     = $lastHop;
    $r['_lastHopNorm'] = strtoupper(trim($lastHop)); // normalized for checks

    // ---------- STATUS (use these rules also for KPIs) ----------
    $billedNorm = strtoupper(trim($r['billedBranch'] ?? ''));

    if ((int)$r['received'] === 1) {
        $r['_derivedStatus'] = 'Received in HO';
    } elseif ($r['_lastHopNorm'] === 'HO') {
        $r['_derivedStatus'] = 'Sent to HO';
    } elseif ($r['_lastHopNorm'] !== '' && $r['_lastHopNorm'] !== $billedNorm) {
        $r['_derivedStatus'] = 'In Transit';
    } else {
        $r['_derivedStatus'] = 'In Branch';
    }

    $allRows[] = $r;
}

/* ===================== KPIs (same rules as above) ===================== */
$inBranchCount     = 0;
$sentToHOCount     = 0;
$receivedInHOCount = 0;
$inTransitCount    = 0;

foreach ($allRows as $r) {
    $lastHopNorm  = $r['_lastHopNorm'];
    $billedBranch = strtoupper(trim($r['billedBranch'] ?? ''));
    $received     = (int)$r['received'];

    if ($received === 1) {
        $receivedInHOCount++;
    } elseif ($lastHopNorm === 'HO') {
        $sentToHOCount++;
    } elseif ($lastHopNorm !== '' && $lastHopNorm !== $billedBranch) {
        $inTransitCount++;
    } else {
        $inBranchCount++;
    }
}
$totalTracked = count($allRows);

/* ===================== Summary grid: group by LAST HOP (exclude HO) ===================== */
$summaryCount       = [];     // lastHop => count (non-HO only)
$detailsByLastHop   = [];     // lastHop => rows   (non-HO only)
foreach ($allRows as $r) {
    $lh     = ($r['_lastHop'] !== '' ? $r['_lastHop'] : $r['billedBranch']);
    $lhNorm = ($r['_lastHopNorm'] !== '' ? $r['_lastHopNorm'] : strtoupper($r['billedBranch']));
    if ($lhNorm === 'HO') continue; // do not list HO in the summary grid
    $summaryCount[$lh] = ($summaryCount[$lh] ?? 0) + 1;
    $detailsByLastHop[$lh][] = $r;
}
arsort($summaryCount);
$summary = [];
foreach ($summaryCount as $lh => $cnt) {
    $summary[] = ['branchId' => $lh, 'cnt' => $cnt];
}
?>
<style>
    #wrapper{ background:#f5f5f5; }
    #wrapper h3{ text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
    .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
    .btn-success{ display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em;
        text-decoration:none; font-size:12px; text-transform:uppercase; color:#fffafa; background-color:#123C69;
        box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative; }
    .dash-title{ margin:0 0 12px; color:#123C69; text-transform:uppercase; font-weight:700; }
    .branch-link{ color:#123C69; cursor:pointer; text-decoration:underline; font-weight:700; }
    .kpi-row{ display:flex; flex-wrap:wrap; gap:10px; margin:10px 0 18px; }
    .kpi{ background:#fff; border:1px solid #e5e8f0; border-left:4px solid #123C69; border-radius:10px;
          box-shadow:0 10px 30px rgba(15,23,42,.06); padding:10px 14px; min-width:220px; }
    .kpi .k{ font-size:12px; color:#334155; text-transform:uppercase; font-weight:700; }
    .kpi .v{ font-size:22px; font-weight:800; color:#111827; }
    .details-box{ display:none; margin-top:14px; background:#fff; border:1px solid #e5e8f0;
        border-left:4px solid #123C69; border-radius:10px; box-shadow:0 10px 30px rgba(15,23,42,.06); padding:12px 12px 16px; }
    .details-title{ font-weight:800; color:#123C69; margin:2px 0 10px; }
    .details-box table thead th{ background:#f4c542; color:#111; }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="col-sm-12">
                        <h3><span style="color:#900" class="fa fa-file-text"></span> GOLD SEND REPORT</h3>
                    </div>
                    <div style="clear:both"></div>

                    <form action="" method="GET">
                        <div class="col-sm-3">
                            <label class="text-success">FROM DATE:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
                                <input type="date" class="form-control" id="dat3" name="dat3" value="<?php echo htmlspecialchars($searchFrom); ?>" required/>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label class="text-success">TO DATE:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
                                <input type="date" class="form-control" id="dat2" name="dat2" value="<?php echo htmlspecialchars($searchTo); ?>" required/>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-success btn-block" name="aaa" id="aaa" style="margin-top:23px">
                                <span style="color:#ffcf40" class="fa fa-search"></span> SEARCH
                            </button>
                        </div>
                    </form>
                    <div style="clear:both"></div>
                </div>

                <div class="panel-body" style="border:5px solid #fff;border-radius:10px;padding:20px;box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);background-color:#F5F5F5;">

                    <!-- KPIs (clickable for detail pop-ins) -->
                    <div class="kpi-row">
                        <div class="kpi">
                            <div class="k">Total Packets Tracked</div>
                            <div class="v"><?php echo (int)$totalTracked; ?></div>
                        </div>
                        <div class="kpi" id="kpiInBranch" style="cursor:pointer;">
                            <div class="k">Packets in Branch</div>
                            <div class="v"><?php echo (int)$inBranchCount; ?></div>
                        </div>
                        <div class="kpi" id="kpiSentHO" style="cursor:pointer;">
                            <div class="k">Packets sent to HO</div>
                            <div class="v"><?php echo (int)$sentToHOCount; ?></div>
                        </div>
                        <div class="kpi" id="kpiRecvHO" style="cursor:pointer;">
                            <div class="k">Packets received in HO</div>
                            <div class="v"><?php echo (int)$receivedInHOCount; ?></div>
                        </div>
                        <div class="kpi" id="kpiTransit" style="cursor:pointer;">
                            <div class="k">Packets in Transit</div>
                            <div class="v"><?php echo (int)$inTransitCount; ?></div>
                        </div>
                    </div>

                    <!-- Summary (by last hop, excluding HO) -->
                    <h4 class="dash-title">
                        <i class="fa fa-sitemap"></i> Gold Packet Dashboard (<?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?>)
                    </h4>
                    <table id="summaryTable" class="display table table-striped table-bordered" style="width:100%; background:#fff;">
                        <thead>
                            <tr>
                                <th>Current Location (Last Hop)</th>
                                <th>Branch Name</th>
                                <th>Number of Packets</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($summary as $s):
                            $bid = $s['branchId'];
                            $bname = $branchNameMap[$bid] ?? $bid;
                            $cnt = (int)$s['cnt'];
                        ?>
                            <tr data-branch="<?php echo htmlspecialchars($bid); ?>">
                                <td><span class="branch-link"><?php echo htmlspecialchars($bid); ?></span></td>
                                <td><?php echo htmlspecialchars($bname); ?></td>
                                <td><?php echo $cnt; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Detail Box -->
                    <div id="detailsBox" class="details-box">
                        <div class="details-title" id="detailsTitle">Packet Details</div>
                        <div id="detailsTableWrap"></div>
                    </div>

                </div>
            </div>
        </div>
        <div style="clear:both"><br></div>
    </div>
<?php include("footer.php"); ?>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
// Data for details table (grouped by last hop, excluding HO)
window.detailsByLastHop = <?php
  echo json_encode($detailsByLastHop, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
?>;
window.branchNames = <?php
  echo json_encode($branchNameMap, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
?>;
const rangeFrom = <?php echo json_encode($from); ?>;
const rangeTo   = <?php echo json_encode($to); ?>;

// Also expose all rows to drive KPI drilldowns:
window.allRows = <?php echo json_encode($allRows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

const summaryDT = new DataTable('#summaryTable', { pageLength: 25 });

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

function buildDetailsTableHtml(title, rows){
  let html = `
    <table id="details_dynamic" class="display" style="width:100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Bill ID</th>
          <th>Date &amp; Time</th>
          <th>Gross Wt</th>
          <th>Net Wt</th>
          <th>Gross Amt</th>
          <th>Net Amt</th>
          <th>Rate</th>
          <th>Purity</th>
          <th>Billed Branch</th>
          <th>Current Location (Last)</th>
          <th>Travelled Through</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
  `;
  let i = 1;
  for (const r of rows){
    const billed = r.billedBranch || '';
    const currentLoc = r._lastHop || billed;
    const travelStr = r._travelStr || '—';
    const statusTxt = r._derivedStatus || 'In Branch';

    html += `
      <tr>
        <td>${i++}</td>
        <td>${escapeHtml(r.billId ?? '')}</td>
        <td>${escapeHtml((r.date ?? '')+' '+(r.time ?? ''))}</td>
        <td>${Number(r.grossW ?? 0).toFixed(2)}</td>
        <td>${Number(r.netW ?? 0).toFixed(2)}</td>
        <td>${Math.round(Number(r.grossA ?? 0))}</td>
        <td>${Math.round(Number(r.netA ?? 0))}</td>
        <td>${escapeHtml(String(r.rate ?? ''))}</td>
        <td>${Number(r.purity ?? 0).toFixed(2)}</td>
        <td>${escapeHtml(billed)}</td>
        <td>${escapeHtml(currentLoc)}</td>
        <td>${escapeHtml(travelStr)}</td>
        <td>${escapeHtml(statusTxt)}</td>
      </tr>
    `;
  }
  html += `</tbody></table>`;
  return html;
}

// Summary row click -> details
$('#summaryTable tbody').on('click', 'td:first-child .branch-link', function(){
  const $tr  = $(this).closest('tr');
  const loc  = $tr.data('branch');
  const bname = window.branchNames[loc] || loc;
  const rows = (window.detailsByLastHop && window.detailsByLastHop[loc]) ? window.detailsByLastHop[loc] : [];

  $('#detailsTitle').text(`Gold packets at ${bname} (${loc}) — ${rangeFrom} to ${rangeTo}`);
  const html = buildDetailsTableHtml(``, rows);
  const $wrap = $('#detailsTableWrap');
  const oldTbl = $wrap.find('table');
  if (oldTbl.length && $.fn.dataTable.isDataTable(oldTbl)) oldTbl.DataTable().destroy(true);
  $wrap.html(html);

  const $box = $('#detailsBox');
  if (!$box.is(':visible')) $box.slideDown(150);

  new DataTable('#details_dynamic', {
    dom: 'Bfrtip',
    buttons: [
      { extend: 'csv',  title: `Details_${loc}_${rangeFrom}_to_${rangeTo}` },
      { extend: 'pdf',  title: `Details_${loc}_${rangeFrom}_to_${rangeTo}` },
      { extend: 'print', title: `Details for ${loc} (${rangeFrom} to ${rangeTo})` }
    ],
    pageLength: 25
  });
});

// KPI drilldowns
function filterRowsBy(predicate){
  return (window.allRows || []).filter(predicate);
}

function showKpiDetails(title, rows){
  $('#detailsTitle').text(title);
  const html = buildDetailsTableHtml(``, rows);
  const $wrap = $('#detailsTableWrap');
  const oldTbl = $wrap.find('table');
  if (oldTbl.length && $.fn.dataTable.isDataTable(oldTbl)) oldTbl.DataTable().destroy(true);
  $wrap.html(html);
  const $box = $('#detailsBox');
  if (!$box.is(':visible')) $box.slideDown(150);
  new DataTable('#details_dynamic', {
    dom: 'Bfrtip',
    buttons: [
      { extend: 'csv',  title: title.replace(/\s+/g,'_') },
      { extend: 'pdf',  title: title },
      { extend: 'print', title: title }
    ],
    pageLength: 25
  });
}

$('#kpiSentHO').on('click', function(){
  const rows = filterRowsBy(r => r._lastHopNorm === 'HO' && parseInt(r.received,10) !== 1);
  showKpiDetails(`Gold packets SENT TO HO (last hop = HO, not yet received) — ${rangeFrom} to ${rangeTo}`, rows);
});

$('#kpiRecvHO').on('click', function(){
  const rows = filterRowsBy(r => parseInt(r.received,10) === 1);
  showKpiDetails(`Gold packets RECEIVED IN HO — ${rangeFrom} to ${rangeTo}`, rows);
});

$('#kpiInBranch').on('click', function(){
  const rows = filterRowsBy(r => {
    const billed = (r.billedBranch || '').toUpperCase().trim();
    const last   = (r._lastHopNorm || '').toUpperCase().trim();
    return r.received != 1 && (last === '' || last === billed);
  });
  showKpiDetails(`Gold packets IN BRANCH — ${rangeFrom} to ${rangeTo}`, rows);
});

$('#kpiTransit').on('click', function(){
  const rows = filterRowsBy(r => {
    const billed = (r.billedBranch || '').toUpperCase().trim();
    const last   = (r._lastHopNorm || '').toUpperCase().trim();
    return r.received != 1 && (last !== '' && last !== billed && last !== 'HO');
  });
  showKpiDetails(`Gold packets IN TRANSIT — ${rangeFrom} to ${rangeTo}`, rows);
});
</script>
