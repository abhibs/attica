<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'BusinessDeveloper') {
  include("header.php");
  include("menuBusiness.php");
} elseif($type=='AccHead') {
  include("header.php");
  include("menuaccHeadPage.php");
} else {
  include("logout.php");
}
include("dbConnection.php");
$date = date("Y-m-d");

/* ------------------ OPENING MAP (latest closing date < today) ------------------ */
$open = [];
$openingData = mysqli_query($con, "
SELECT a.branchId,
       (CASE WHEN a.forward='Forward to HO' THEN 0 ELSE a.balance END) AS open
FROM closing a
INNER JOIN (
    SELECT c.branchId, MAX(c.date) AS date
    FROM closing c
    JOIN branch b ON c.branchId = b.branchId
    WHERE c.branchId <> ''
      AND c.branchId <> 'AGPL000'
      AND b.branchName <> 'HO'
      AND b.Status = 1
      AND c.date < '$date'       -- most recent closing strictly before today
    GROUP BY c.branchId
) b
  ON a.branchId = b.branchId AND a.date = b.date
");
while ($row = mysqli_fetch_assoc($openingData)) {
  $open[$row['branchId']] = (float)$row['open'];
}

/* ------------------ BRANCH DATA (today’s movements) ------------------ */
$q = mysqli_query($con, "
SELECT
  b.branchId,
  b.branchName,
  (CASE WHEN b.state='Andhra Pradesh' THEN 'Andhra_Pradesh' ELSE b.state END) AS state,
  b.city,
  (SELECT SUM(t.cashA) FROM trans t
    WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS cashA,
  (SELECT SUM(t.impsA) FROM trans t
    WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS impsA,
  (SELECT SUM(t.relCash) FROM releasedata t
    WHERE t.date='$date' AND t.BranchId=b.branchId AND t.status IN ('Approved','Billed') GROUP BY t.BranchId) AS cashRelA,
  (SELECT SUM(t.relIMPS) FROM releasedata t
    WHERE t.date='$date' AND t.BranchId=b.branchId AND t.status IN ('Approved','Billed') GROUP BY t.BranchId) AS impsRelA,
  (SELECT SUM(request) FROM fund t
    WHERE t.date='$date' AND t.branch=b.branchId AND t.status='Approved' GROUP BY t.branch) AS fund,
  (SELECT SUM(transferAmount) FROM trare t
    WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS transfer,
  (SELECT SUM(transferAmount) FROM trare t
    WHERE t.date='$date' AND t.branchTo=b.branchName AND t.status='Approved' GROUP BY t.branchTo) AS received,
  (SELECT SUM(amount) FROM expense t
    WHERE t.date='$date' AND t.branchCode=b.branchId AND t.status='Approved' GROUP BY t.branchCode) AS expense
FROM branch b
WHERE b.Status = 1
  AND b.branchId <> 'AGPL000'
  AND b.branchId <> 'AGPL999'
  AND b.branchName <> 'HO'
");

$branches = [];
while ($r = mysqli_fetch_assoc($q)) {
  $id   = $r['branchId'];
  $name = $r['branchName'];

  $openBal = isset($open[$id]) ? (float)$open[$id] : 0.0;

  $cashA     = (float)($r['cashA']    ?? 0);
  $impsA     = (float)($r['impsA']    ?? 0);
  $relCash   = (float)($r['cashRelA'] ?? 0);
  $relIMPS   = (float)($r['impsRelA'] ?? 0);
  $fund      = (float)($r['fund']     ?? 0);
  $transfer  = (float)($r['transfer'] ?? 0);
  $received  = (float)($r['received'] ?? 0);
  $expense   = (float)($r['expense']  ?? 0);

  $netA    = $cashA + $impsA + $relCash + $relIMPS;
  $balance = ($openBal + $fund + $received) - ($cashA + $relCash + $transfer + $expense);

  $branches[$id] = [
    'id'   => $id,
    'name' => $name,
    'state'=> $r['state'],
    'city' => $r['city'],
    'open' => $openBal,
    'fund' => $fund,
    'recv' => $received,
    'tran' => $transfer,
    'netA' => $netA,
    'exp'  => $expense,
    'bal'  => $balance,
  ];
}
?>
<style>
  #wrapper h3{ text-transform:uppercase;font-weight:700;font-size:18px;color:#123C69;margin: 10px 0 10px 0;}

  /* Tiles layout + alignment */
  .cluster-tiles{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
    gap:14px;
    margin-bottom:18px
  }
  .cluster-tile{
    background:#fff;
    border:1px solid #e7ebf3;
    border-radius:12px;
    padding:14px;
    box-shadow:0 1px 2px rgba(0,0,0,.06);
    cursor:pointer;
    transition:transform .08s ease,box-shadow .08s ease
  }
  .cluster-tile:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
  .cluster-title{font-weight:700;color:#123C69;font-size:14px;margin-bottom:6px;text-transform:uppercase}
  .cluster-sub{font-size:11px;color:#6b7280;margin-bottom:8px;display:flex;justify-content:space-between;gap:6px;flex-wrap:wrap}
  .cluster-total{font-size:20px;font-weight:800}
  .rupee::before{content:"₹";margin-right:2px}

  /* Threshold tints for tiles */
  .tile-low-70{ background:#fff8c6; border-color:#e9d88b;}
  .tile-low-50{ background:#ffd6d6; border-color:#e8a4a4;}

  /* Details panel at bottom (hidden initially) */
  .details-panel{
    display:none;
    border:1px solid #e7ebf3;
    border-radius:10px;
    background:#fff;
    box-shadow:0 1px 2px rgba(0,0,0,.06);
    margin-top:14px;
    overflow:hidden;
  }
  .details-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    padding:10px 12px;
    background:#123C69;
    color:#fff;
  }
  .details-header .badge{
    display:inline-block;
    font-size:11px;
    padding:2px 8px;
    border-radius:999px;
    background:rgba(255,255,255,.2);
  }
  .details-body{ padding:10px; }
  .theadRow{ text-transform:uppercase;background-color:#123C69!important;color:#f2f2f2;font-size:11px;}
  #clusterDetailTable th,#clusterDetailTable td{font-size:12px;vertical-align:middle}
  #clusterDetailTable tfoot th{font-weight:800}
  .text-right{ text-align:right; }
  .btn-link-like{
    background:transparent;border:none;padding:6px 10px;color:#fff;text-decoration:underline;cursor:pointer
  }

  /* Universal low-balance row styling */
  .row-low td{
    color:#b91c1c;
    font-weight:700;
    background:#fff0f0;
  }
</style>

<div id="wrapper" class="container-fluid">
  <div class="row">
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-body" style="margin-left:10px">
          <h3>Cluster Balances (Tiles)</h3>
          <div id="clusterTiles" class="cluster-tiles"></div>

          <!-- Bottom Details Panel (hidden initially) -->
          <div id="detailsPanel" class="details-panel">
            <div class="details-header">
              <div>
                <strong id="detailsTitle">Cluster</strong>
                <span class="badge" id="detailsMin"></span>
                <span class="badge" id="detailsPct"></span>
                <span class="badge" id="detailsTotal"></span>
              </div>
              <div>
                <button type="button" class="btn-link-like" id="hideDetailsBtn">Hide</button>
              </div>
            </div>
            <div class="details-body">
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" id="clusterDetailTable">
                  <thead>
                    <tr class="theadRow">
                      <th>Branch ID</th>
                      <th>Branch Name</th>
                      <th class="text-right">Opening</th>
                      <th class="text-right">Fund</th>
                      <th class="text-right">Received</th>
                      <th class="text-right">Transferred</th>
                      <th class="text-right">Net A</th>
                      <th class="text-right">Expense</th>
                      <th class="text-right">Balance</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <tr>
                      <th colspan="2" class="text-right">TOTAL</th>
                      <th class="text-right" id="ftOpen">0</th>
                      <th class="text-right" id="ftFund">0</th>
                      <th class="text-right" id="ftRecv">0</th>
                      <th class="text-right" id="ftTrans">0</th>
                      <th class="text-right" id="ftNetA">0</th>
                      <th class="text-right" id="ftExp">0</th>
                      <th class="text-right" id="ftBal">0</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          <!-- /Bottom Details Panel -->
        </div>
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</div>

<script>
(function(){
  /* ===== Data from PHP ===== */
  const BRANCHES = <?php
    echo json_encode($branches, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
  ?>;

  /* ===== Constants ===== */
  const LOW_THRESHOLD = 1000000; // 10,00,000

  /* ===== Cluster membership ===== */
  const CLUSTERS = {
    "Mysore Cluster": ["AGPL005","AGPL204"],
    "Hosur Cluster":  ["AGPL017","AGPL159","AGPL104","AGPL124","AGPL123"],
    "Coimbatore Cluster": ["AGPL033","AGPL032","AGPL118","AGPL119","AGPL120","AGPL048","AGPL113"],
    "Tirupathi Cluster": ["AGPL072","AGPL137","AGPL026"],
    "Kolar Cluster": ["AGPL059","AGPL170"],
    "Chennai Cluster": ["AGPL030","AGPL029","AGPL056","AGPL031","AGPL055","AGPL054","AGPL080","AGPL081","AGPL082","AGPL079","AGPL128","AGPL126","AGPL127","AGPL131","AGPL130","AGPL129","AGPL175","AGPL176","AGPL209","AGPL036"],
    "Salem Cluster": ["AGPL043","AGPL035","AGPL207"],
    "Hyderabad Cluster": ["AGPL022","AGPL023","AGPL076","AGPL222","AGPL078","AGPL221","AGPL216","AGPL151","AGPL164","AGPL196","AGPL217","AGPL220","AGPL226","AGPL225","AGPL069"],
    "Hubli Cluster": ["AGPL006","AGPL182"],
    "Madurai Cluster": ["AGPL215","AGPL110","AGPL111","AGPL095","AGPL039","AGPL236","AGPL044","AGPL046","AGPL034","AGPL047","AGPL042","AGPL037"],
    "Vijayawada Cluster": ["AGPL061","AGPL147","AGPL089","AGPL063","AGPL199","AGPL027","AGPL138"],
    "Shimoga Cluster": ["AGPL019","AGPL007"],
    "Trichy Cluster": ["AGPL049","AGPL102","AGPL045"],
    "Vizag Cluster": ["AGPL065","AGPL094","AGPL145","AGPL146","AGPL148","AGPL150","AGPL144","AGPL143"]
  };

  /* ===== Min balance by cluster (supports numbers, 'L', 'Cr') ===== */
  const CLUSTER_MIN_BALANCE = {
    "Mysore Cluster": "20L",
    "Hosur Cluster":  "50L",
    "Coimbatore Cluster": "50L",
    "Tirupathi Cluster": "30L",
    "Kolar Cluster": "10L",
    "Chennai Cluster": "200L",
    "Salem Cluster": "30L",
    "Hyderabad Cluster": "150L",
    "Hubli Cluster": "20L",
    "Madurai Cluster": "100L",
    "Vijayawada Cluster": "110L",
    "Shimoga Cluster": "20L",
    "Trichy Cluster": "30L",
    "Vizag Cluster": "50L"
    /* 'Other' has no min; 'Low Cash Branches' shows "Below ₹10,00,000" */
  };

  /* ===== Helpers ===== */
  function inr(n){
    try { return Number(n||0).toLocaleString('en-IN'); }
    catch(e){ return (Math.round((n||0)*100)/100).toString(); }
  }
  // Parse "20L" => 20 * 100000 ; "1.5Cr" => 1.5 * 10000000 ; number => rupees
  function parseRupeeLike(v){
    if (typeof v === 'number') return v;
    const s = String(v||'').trim().toUpperCase().replace(/[, ]/g,'');
    if (!s) return 0;
    let m;
    if ((m = s.match(/^([\d.]+)L$/)))  return parseFloat(m[1]) * 100000;
    if ((m = s.match(/^([\d.]+)CR$/))) return parseFloat(m[1]) * 10000000;
    if (/^\d+(\.\d+)?$/.test(s))       return parseFloat(s);
    return 0;
  }

  function sumCluster(ids){
    let total = 0, present = 0;
    ids.forEach(id=>{
      if (BRANCHES[id]) { total += (BRANCHES[id].bal||0); present++; }
    });
    return { total, present };
  }

  function escapeHtml(s){
    const d=document.createElement('div');
    d.textContent = String(s||'');
    return d.innerHTML;
  }

  /* ======= Build "Other" cluster dynamically ======= */
  (function addOtherCluster(){
    const listed = new Set();
    Object.values(CLUSTERS).forEach(arr => arr.forEach(id => listed.add(id)));
    const otherIds = [];
    Object.keys(BRANCHES).forEach(id=>{
      const b = BRANCHES[id];
      if (!listed.has(id) && (b?.city || '').trim() !== 'Bengaluru') {
        otherIds.push(id);
      }
    });
    if (otherIds.length > 0) {
      CLUSTERS["Other"] = otherIds;
    }
  })();

  /* ======= Build "Low Cash Branches" dynamic tile ======= */
  (function addLowCashTile(){
    const lowIds = [];
    Object.keys(BRANCHES).forEach(id=>{
      const b = BRANCHES[id];
      if (!b) return;
      if ((b.bal||0) < LOW_THRESHOLD) lowIds.push(id);
    });
    CLUSTERS["Low Cash Branches"] = lowIds; // always present (even if 0)
  })();

  /* ======= Details (bottom panel) ======= */
  const panel = document.getElementById('detailsPanel');
  const hideBtn = document.getElementById('hideDetailsBtn');
  const titleEl = document.getElementById('detailsTitle');
  const minEl   = document.getElementById('detailsMin');
  const pctEl   = document.getElementById('detailsPct');
  const totEl   = document.getElementById('detailsTotal');

  hideBtn.addEventListener('click', () => {
    panel.style.display = 'none';
  });

  function openClusterDetails(clusterName, ids){
    const tbody = document.querySelector('#clusterDetailTable tbody');
    tbody.innerHTML = '';
    const rows = [];

    let tOpen=0,tFund=0,tRecv=0,tTran=0,tNetA=0,tExp=0,tBal=0;

    ids.forEach(id=>{
      const r = BRANCHES[id];
      if (!r) return;
      rows.push(r);
    });

    // sort by balance desc
    rows.sort((a,b)=> (b.bal||0) - (a.bal||0));

    rows.forEach(r=>{
      tOpen+=r.open||0; tFund+=r.fund||0; tRecv+=r.recv||0; tTran+=r.tran||0; tNetA+=r.netA||0; tExp+=r.exp||0; tBal+=r.bal||0;

      const isLow = (r.bal||0) < LOW_THRESHOLD;

      const tr = document.createElement('tr');
      if (isLow) tr.classList.add('row-low');
      tr.innerHTML = `
        <td>${r.id}</td>
        <td>${escapeHtml(r.name)}</td>
        <td class="text-right">${inr(r.open)}</td>
        <td class="text-right">${inr(r.fund)}</td>
        <td class="text-right">${inr(r.recv)}</td>
        <td class="text-right">${inr(r.tran)}</td>
        <td class="text-right">${inr(r.netA)}</td>
        <td class="text-right">${inr(r.exp)}</td>
        <td class="text-right">${inr(r.bal)}</td>
      `;
      tbody.appendChild(tr);
    });

    // Footer totals
    document.getElementById('ftOpen').textContent = inr(tOpen);
    document.getElementById('ftFund').textContent = inr(tFund);
    document.getElementById('ftRecv').textContent = inr(tRecv);
    document.getElementById('ftTrans').textContent = inr(tTran);
    document.getElementById('ftNetA').textContent = inr(tNetA);
    document.getElementById('ftExp').textContent  = inr(tExp);
    document.getElementById('ftBal').textContent  = inr(tBal);

    // Header badges
    if (clusterName === 'Low Cash Branches') {
      titleEl.textContent = clusterName;
      minEl.textContent   = `Below ₹${inr(LOW_THRESHOLD)}`;
      pctEl.textContent   = ''; // not meaningful here
      totEl.textContent   = `Total ₹${inr(tBal)}`;
    } else {
      const minBal = parseRupeeLike(CLUSTER_MIN_BALANCE[clusterName] || 0);
      const pct = minBal > 0 ? Math.round((tBal / minBal) * 100) : null;

      titleEl.textContent = clusterName;
      minEl.textContent   = minBal > 0 ? `Min ₹${inr(minBal)}` : 'Min —';
      pctEl.textContent   = pct !== null ? `${pct}% of min` : '';
      totEl.textContent   = `Total ₹${inr(tBal)}`;
    }

    // Show panel and scroll to it
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  /* ======= Tiles ======= */
  function renderTiles(){
    const wrap = document.getElementById('clusterTiles');
    wrap.innerHTML = '';

    Object.entries(CLUSTERS).forEach(([name, ids])=>{
      const { total, present } = sumCluster(ids);

      // Tile visual logic
      let extraClass = '';
      let subRightText = '';
      if (name === 'Low Cash Branches') {
        // No min; show "Below ₹10,00,000"
        subRightText = `Below: ₹${inr(LOW_THRESHOLD)}`;
      } else {
        const minBal = parseRupeeLike(CLUSTER_MIN_BALANCE[name] || 0);
        if (minBal > 0) {
          const ratio = total / minBal;
          if (ratio < 0.5)       extraClass = 'tile-low-50';
          else if (ratio < 0.7)  extraClass = 'tile-low-70';
        }
        subRightText = (minBal > 0) ? `Min: ₹${inr(minBal)}` : 'Min: —';
      }

      const tile = document.createElement('div');
      tile.className = `cluster-tile ${extraClass}`;
      tile.innerHTML = `
        <div class="cluster-title">${name}</div>
        <div class="cluster-sub">
          <span>${present} / ${ids.length} branches</span>
          <span>${subRightText}</span>
        </div>
        <div class="cluster-total rupee">${inr(total)}</div>
      `;
      tile.addEventListener('click', ()=> openClusterDetails(name, ids));
      wrap.appendChild(tile);
    });
  }

  // Initial render
  renderTiles();
})();
</script>

