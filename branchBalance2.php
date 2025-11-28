<?php
session_start();
$type = $_SESSION['usertype'] ?? '';

if ($type=='Master') { include("header.php"); include("menumaster.php");
} elseif ($type=='SundayUser') { include("header.php"); include("menuSundayUser.php");
} elseif ($type=='Software') { include("header.php"); include("menuSoftware.php");
} elseif ($type=='Zonal') { include("header.php"); include("menuZonal.php");
} elseif ($type=='SubZonal') { include("header.php"); include("menuSubZonal.php");
} elseif ($type=='ZonalMaster') { include("header.php"); include("menuzonalMaster.php");
} else { include("logout.php"); exit(); }

include("dbConnection.php");

$date   = date("Y-m-d");
$branch = $_SESSION['branchCode'] ?? '';

$extra = "";
if ($type=='SubZonal') {
  if ($branch=="KA")        $extra = " AND b.state = 'Karnataka' ";
  elseif ($branch=="TN")    $extra = " AND b.state IN ('Tamilnadu','Pondicherry') ";
  elseif ($branch=="AP-TS") $extra = " AND b.state IN ('Andhra Pradesh','Telangana') ";
}

/* Per-branch aggregates for today (DATE() on DATE cols) */
$sql = mysqli_query($con, "
  SELECT
    b.branchId,
    b.branchName,
    (CASE WHEN b.state='Andhra Pradesh' THEN 'Andhra_Pradesh' ELSE b.state END) AS state,
    (SELECT SUM(t.cashA)  FROM trans t WHERE DATE(t.date)='{$date}' AND t.branchId=b.branchId AND t.status='Approved') AS cashA,
    (SELECT SUM(t.impsA)  FROM trans t WHERE DATE(t.date)='{$date}' AND t.branchId=b.branchId AND t.status='Approved') AS impsA,
    (SELECT SUM(r.relCash) FROM releasedata r WHERE DATE(r.date)='{$date}' AND r.BranchId=b.branchId AND r.status IN ('Approved','Billed')) AS cashRelA,
    (SELECT SUM(r.relIMPS) FROM releasedata r WHERE DATE(r.date)='{$date}' AND r.BranchId=b.branchId AND r.status IN ('Approved','Billed')) AS impsRelA,
    (SELECT SUM(f.request) FROM fund f WHERE DATE(f.date)='{$date}' AND f.branch=b.branchId AND f.status='Approved') AS fundOnly,
    (SELECT SUM(tr.transferAmount) FROM trare tr WHERE DATE(tr.date)='{$date}' AND tr.branchId=b.branchId AND tr.status='Approved') AS transfer,
    (SELECT SUM(tr.transferAmount) FROM trare tr WHERE DATE(tr.date)='{$date}' AND tr.branchTo=b.branchName AND tr.status='Approved') AS received
  FROM branch b
  WHERE b.Status = 1 AND b.branchId <> 'AGPL000' {$extra}
");

/* Latest closing per branch (opening, expenses, netAg, cheques) */
$closingOpen     = [];
$closingExp      = [];
$closingNetAG    = [];
$closingCheques  = []; /* NEW: cheques */

$closingData = mysqli_query($con, "
  SELECT a.branchId,
         CASE WHEN a.forward='Forward to HO'
              THEN 0
              ELSE CAST(REPLACE(REPLACE(a.balance, ',', ''), ' ', '') AS DECIMAL(15,2))
         END AS opening,
         CAST(REPLACE(REPLACE(COALESCE(a.expenses,'0'),' ',''),',','') AS DECIMAL(15,2)) AS expenses,
         CAST(REPLACE(REPLACE(COALESCE(a.netAg,'0'),' ',''),',','') AS DECIMAL(15,2))    AS netAg,
         /* NEW: cheques latest */
         CAST(REPLACE(REPLACE(COALESCE(a.cheques,'0'),' ',''),',','') AS DECIMAL(15,2))   AS cheques
  FROM closing a
  INNER JOIN (
      SELECT branchId, MAX(date) AS max_date
      FROM closing
      WHERE branchId <> ''
      GROUP BY branchId
  ) latest
    ON latest.branchId = a.branchId AND latest.max_date = a.date
");

while ($row = mysqli_fetch_assoc($closingData)) {
  $closingOpen[$row['branchId']]     = (float)$row['opening'];
  $closingExp[$row['branchId']]      = (float)$row['expenses'];
  $closingNetAG[$row['branchId']]    = (float)$row['netAg'];
  $closingCheques[$row['branchId']]  = (float)($row['cheques'] ?? 0); /* NEW */
}
?>
<style>
  #wrapper h3{ text-transform:uppercase; font-weight:700; font-size:18px; color:#123C69; }
  .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
  .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
  .btn-primary{ background-color:#123C69; }
  .theadRow{ text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }
  .dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
  .btn-success{
    display:inline-block; padding:0.7em 1.4em; margin:0 .3em .3em 0; border-radius:.15em; box-sizing:border-box; text-decoration:none;
    font-size:12px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa; background:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,.17);
    text-align:center; position:relative;
  }
  .fa_Icon{ color:#990000; }
  .modal-title{ font-size:20px; font-weight:600; color:#708090; text-transform:uppercase; }
  .modal-header{ background:#123C69; }
  #wrapper .panel-body{ box-shadow:10px 15px 15px #999; border:1px solid #edf2f9; background:#f5f5f5; border-radius:3px; padding:20px; }
  .td-align-right{ text-align:right; }
  .table-responsive .row{ margin:0; }
</style>

<div id="wrapper">
  <div class="row content">

    <div class="col-lg-12">
      <div class="col-lg-8"><h3><span></span>BRANCH AVAILABLE AMOUNT</h3></div>
      <?php if($type != 'Zonal'){ ?>
      <div class="col-lg-4">
        <select class="form-control m-b" id="state">
          <option selected value="ALL">ALL</option>
          <option value="APT">ANDHRA & TELANGANA</option>
          <option value="KAR">KARNATAKA</option>
          <option value="TN">TAMILNADU</option>
        </select>
      </div>
      <?php } else { ?>
      <div class='col-lg-4'>
        <select class='form-control m-b' id='state'>
          <?php
            if ($branch=="KA")        echo "<option selected value='KAR'>KARNATAKA</option>";
            elseif ($branch=="TN")    echo "<option selected value='TN'>TAMILNADU</option>";
            elseif ($branch=="AP-TS") echo "<option selected value='APT'>ANDHRA & TELANGANA</option>";
            else                      echo "<option selected value='ALL'>ALL</option>";
          ?>
        </select>
      </div>
      <?php } ?>
    </div>

    <!-- Top KPI cards remain unchanged (no new card for cheques) -->
    <div class="col-md-3"><div class="hpanel"><div class="panel-body"><div class="text-center"><h3>OPEN BALANCE</h3><p id="totalOpenBalance"></p></div></div></div></div>
    <div class="col-md-2"><div class="hpanel"><div class="panel-body"><div class="text-center"><h3>FUNDS</h3><p id="totalFunds"></p></div></div></div></div>
    <div class="col-md-2"><div class="hpanel"><div class="panel-body"><div class="text-center"><h3>NET AMOUNT</h3><p id="totalNetA"></p></div></div></div></div>
    <div class="col-md-2"><div class="hpanel"><div class="panel-body"><div class="text-center"><h3>EXPENSE</h3><p id="totalExpense"></p></div></div></div></div>
    <div class="col-md-3"><div class="hpanel"><div class="panel-body"><div class="text-center"><h3>CURRENT BALANCE</h3><p id="totalAvail"></p></div></div></div></div>

    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-body">
          <div class="table-responsive">
            <table id="exampleBalance" class="table table-bordered">
              <thead>
                <tr class="theadRow">
                  <th>BRANCH ID</th>
                  <th>BRANCH NAME</th>
                  <th>OPENING</th>
                  <th>FUND</th>
                  <th>RECEIVED</th>
                  <th>TRANSFERRED</th>
                  <th>NET A</th>
                  <th>EXPENSE</th>
                  <th>CHEQUES</th>
                  <th>BALANCE</th>
                </tr>
              </thead>
              <tbody id="tableBody">
                <?php
                while($row = mysqli_fetch_assoc($sql)){
                  $branchId      = $row['branchId'];

                  $openBalance   = $closingOpen[$branchId]     ?? 0.0; // from closing.balance
                  $netA_fromClose= $closingNetAG[$branchId]    ?? 0.0; // from closing.netAg
                  $expenseClose  = $closingExp[$branchId]      ?? 0.0; // from closing.expenses
                  $remCheques    = $closingCheques[$branchId]  ?? 0; /* NEW: from closing.cheques */

                  $transCash     = (float)($row['cashA']    ?? 0);
                  $transIMPS     = (float)($row['impsA']    ?? 0);
                  $relCash       = (float)($row['cashRelA'] ?? 0);
                  $relIMPS       = (float)($row['impsRelA'] ?? 0);
                  $fundOnly      = (float)($row['fundOnly'] ?? 0);  // for top card
                  $transferred   = (float)($row['transfer'] ?? 0);
                  $received      = (float)($row['received'] ?? 0);

                  $fundDisplay   = $transIMPS + $relIMPS + $fundOnly; /* table column */
                  $balance       = ($openBalance + $fundOnly + $received) - ($transCash + $relCash + $transferred + $expenseClose);

                  echo "<tr class='{$row['state']}' data-fund='".htmlspecialchars((string)$fundOnly,ENT_QUOTES)."' data-remchq='".htmlspecialchars(number_format($remCheques,2,'.',''),ENT_QUOTES)."'>"; /* NEW data-remchq */
                  echo "<td>{$branchId}</td>";
                  echo "<td>{$row['branchName']}</td>";
                  echo "<td class='td-align-right'>".number_format($openBalance,2,'.',',')."</td>";
                  echo "<td class='td-align-right'>".number_format($fundDisplay,2,'.',',')."</td>";
                  echo "<td class='td-align-right'>".number_format($received,2,'.',',')."</td>";
                  echo "<td class='td-align-right'>".number_format($transferred,2,'.',',')."</td>";
                  echo "<td class='td-align-right'>".number_format($netA_fromClose,2,'.',',')."</td>";     // NET A from closing.netAg
                  echo "<td class='td-align-right'>".number_format($expenseClose,2,'.',',')."</td>";        // EXPENSE from closing.expenses
                  /* NEW: Remaining Cheques cell */
                  echo "<td class='td-align-right'>".number_format($remCheques,2,'.',',')."</td>";
                  echo "<td class='td-align-right'>".number_format($balance,2,'.',',')."</td>";
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
	<?php include("footer.php"); ?>
  </div>
</div>

<script>
$(function(){
  $('#exampleBalance').DataTable({
    paging:false, ajax:'',
    dom:"<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
    lengthMenu:[[10,25,50,100,250,-1],[10,25,50,100,250,"All"]],
    buttons:[
      {extend:'copy', className:'btn-sm'},
      {extend:'csv',  title:'ExportReport', className:'btn-sm'},
      {extend:'pdf',  title:'ExportReport', className:'btn-sm'},
      {extend:'print',className:'btn-sm'}
    ]
  });

  const tableRows = Array.from(document.querySelectorAll('#exampleBalance tbody tr'));
  const tdAP  = tableRows.filter(tr => tr.classList.contains('Andhra_Pradesh') || tr.classList.contains('Telangana'));
  const tdKAR = tableRows.filter(tr => tr.classList.contains('Karnataka'));
  const tdTN  = tableRows.filter(tr => tr.classList.contains('Tamilnadu') || tr.classList.contains('Pondicherry'));

  const stateSelect = document.getElementById('state');
  stateSelect.addEventListener('change', () => applyState(stateSelect.value));

  function applyState(code){
    tableRows.forEach(r => r.hidden = false);
    if (code==='APT'){ tdKAR.forEach(r=>r.hidden=true); tdTN.forEach(r=>r.hidden=true); }
    if (code==='KAR'){ tdAP.forEach(r=>r.hidden=true);  tdTN.forEach(r=>r.hidden=true); }
    if (code==='TN') { tdAP.forEach(r=>r.hidden=true);  tdKAR.forEach(r=>r.hidden=true); }

    const rows = tableRows.filter(r => !r.hidden);

    // NOTE: BALANCE column index is now 9 (after inserting REM. CHEQUES)
    let open=0, fundTop=0, net=0, expense=0, balance=0, remchq=0;
    rows.forEach(e => {
      const get = i => parseFloat((e.children[i].innerText || '0').replace(/,/g,'')) || 0;
      open    += get(2);
      fundTop += parseFloat(e.dataset.fund || '0'); // ONLY Fund table for top card
      net     += get(6); // NET A from closing.netAg
      expense += get(7); // EXPENSE from closing.expenses
      balance += get(9); // BALANCE after new column
      remchq  += parseFloat(e.dataset.remchq || '0'); // NEW: Remaining Cheques total (no KPI card shown)
    });

    document.getElementById('totalOpenBalance').innerText = open.toLocaleString('en-IN',{minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('totalFunds').innerText       = fundTop.toLocaleString('en-IN',{minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('totalNetA').innerText        = net.toLocaleString('en-IN',{minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('totalExpense').innerText     = expense.toLocaleString('en-IN',{minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('totalAvail').innerText       = balance.toLocaleString('en-IN',{minimumFractionDigits:2, maximumFractionDigits:2});
    // No dedicated KPI card for cheques; kept the sum in case you want to use it later
  }

  applyState(stateSelect.value);
});
</script>

