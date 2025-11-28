<?php
        session_start();
        $type = $_SESSION['usertype'];
        if($type=='Master') {
                include("header.php");
                include("menumaster.php");
        } else if($type=='Software'){
                include("header.php");
                include("menuSoftware.php");
        } elseif ($type == 'BusinessDeveloper') {
                include("header.php");
                include("menuBusiness.php");
        } elseif($type=='AccHead') {
                include("header.php");
                include("menuaccHeadPage.php");
        } else if($type == 'CashMovers') {
                include("header.php");
                include("menuCashMover.php");
        } else{
                include("logout.php");
        }
        include("dbConnection.php");

        /* ----------------- Dates & Weekday Setup ----------------- */
        $todayDate = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("-1 day", strtotime($todayDate)));
        $start3m   = date("Y-m-d", strtotime("-3 months", strtotime($todayDate)));
        $end3m     = date("Y-m-d", strtotime("-1 day", strtotime($todayDate))); // exclude today

        // MySQL WEEKDAY(): 0=Mon ... 6=Sun. PHP 'N' => 1..7 (Mon..Sun) → subtract 1
        $wd0 = ((int)date('N')) - 1;              // today’s weekday in MySQL scale
        $wd1 = ($wd0 + 1) % 7;                    // tomorrow
        $wd2 = ($wd0 + 2) % 7;                    // day after tomorrow

        $extra = "";

        /* ----------------- FAST Pre-aggregations -----------------
           We precompute daily sums and averages once and join, instead of N subqueries/row.
        */

        // today: trans cashA / impsA (by branchId)
        $qTransToday = "
                SELECT t.branchId,
                           SUM(COALESCE(t.cashA,0))  AS cashA,
                           SUM(COALESCE(t.impsA,0))  AS impsA
                FROM trans t
                WHERE t.date = '{$todayDate}' AND t.status='Approved'
                GROUP BY t.branchId
        ";
        $transToday = mysqli_query($con, $qTransToday);

        $mapTransToday = [];
        while($r = mysqli_fetch_assoc($transToday)){
                $mapTransToday[$r['branchId']] = [
                        'cashA' => (float)$r['cashA'],
                        'impsA' => (float)$r['impsA'],
                ];
        }

        // today: releasedata cash / imps (by BranchId)
        $qRelToday = "
                SELECT r.BranchId,
                           SUM(COALESCE(r.relCash,0)) AS cashRelA,
                           SUM(COALESCE(r.relIMPS,0)) AS impsRelA
                FROM releasedata r
                WHERE r.date = '{$todayDate}' AND r.status IN ('Approved','Billed')
                GROUP BY r.BranchId
        ";
        $relToday = mysqli_query($con, $qRelToday);
        $mapRelToday = [];
        while($r = mysqli_fetch_assoc($relToday)){
                $mapRelToday[$r['BranchId']] = [
                        'cashRelA' => (float)$r['cashRelA'],
                        'impsRelA' => (float)$r['impsRelA'],
                ];
        }

        // today: fund by branch code (branch)
        $qFundToday = "
                SELECT f.branch AS branchId,
                           SUM(COALESCE(f.request,0)) AS fund
                FROM fund f
                WHERE f.date = '{$todayDate}' AND f.status='Approved'
                GROUP BY f.branch
        ";
        $fundToday = mysqli_query($con, $qFundToday);
        $mapFundToday = [];
        while($r = mysqli_fetch_assoc($fundToday)){
                $mapFundToday[$r['branchId']] = (float)$r['fund'];
        }

        // today: trare sent from branchId
        $qTransferToday = "
                SELECT tr.branchId,
                           SUM(COALESCE(tr.transferAmount,0)) AS transfer
                FROM trare tr
                WHERE tr.date = '{$todayDate}' AND tr.status='Approved'
                GROUP BY tr.branchId
        ";
        $transferToday = mysqli_query($con, $qTransferToday);
        $mapTransferToday = [];
        while($r = mysqli_fetch_assoc($transferToday)){
                $mapTransferToday[$r['branchId']] = (float)$r['transfer'];
        }

        // today: trare received to branchName (join by name)
        $qReceiveToday = "
                SELECT tr.branchTo AS branchName,
                           SUM(COALESCE(tr.transferAmount,0)) AS received
                FROM trare tr
                WHERE tr.date = '{$todayDate}' AND tr.status='Approved'
                GROUP BY tr.branchTo
        ";
        $receiveToday = mysqli_query($con, $qReceiveToday);
        $mapReceiveToday = [];
        while($r = mysqli_fetch_assoc($receiveToday)){
                $mapReceiveToday[$r['branchName']] = (float)$r['received'];
        }

        // today: expense by branchCode
        $qExpenseToday = "
                SELECT e.branchCode,
                           SUM(COALESCE(e.amount,0)) AS expense
                FROM expense e
                WHERE e.date = '{$todayDate}' AND e.status='Approved'
                GROUP BY e.branchCode
        ";
        $expenseToday = mysqli_query($con, $qExpenseToday);
        $mapExpenseToday = [];
        while($r = mysqli_fetch_assoc($expenseToday)){
                $mapExpenseToday[$r['branchCode']] = (float)$r['expense'];
        }

        // Yesterday gold cash rate per branchId (city)
        // Using max(id) of that date for each city for speed.
        $qGoldYday = "
                SELECT g1.city, CAST(g1.cash AS DECIMAL(12,2)) AS cash_rate
                FROM gold g1
                INNER JOIN (
                        SELECT city, MAX(id) AS maxid
                        FROM gold
                        WHERE date = '{$yesterday}' AND type='Gold'
                        GROUP BY city
                ) t ON g1.city = t.city AND g1.id = t.maxid
        ";
        $goldYday = mysqli_query($con, $qGoldYday);
        $mapGoldRate = [];
        while($r = mysqli_fetch_assoc($goldYday)){
                $mapGoldRate[$r['city']] = (float)$r['cash_rate']; // change to transferRate if desired
        }

        // Last 3 months avg(netW) per weekday (today wd, tomorrow wd, day+2 wd)
        // We first aggregate to DAILY netW per branch/date, then average those day totals
        $qAvgNetW = "
            SELECT
                d.branchId,
                AVG(CASE WHEN WEEKDAY(d.date) = {$wd0} THEN CAST(d.netW_day AS DECIMAL(12,3)) END) AS avg0,
                AVG(CASE WHEN WEEKDAY(d.date) = {$wd1} THEN CAST(d.netW_day AS DECIMAL(12,3)) END) AS avg1,
                AVG(CASE WHEN WEEKDAY(d.date) = {$wd2} THEN CAST(d.netW_day AS DECIMAL(12,3)) END) AS avg2
            FROM (
                SELECT
                    t.branchId,
                    t.date,
                    SUM(COALESCE(t.NetW,0)) AS netW_day
                FROM trans t
                WHERE t.status = 'Approved'
                  AND t.metal  = 'Gold'
                  AND t.date BETWEEN '{$start3m}' AND '{$end3m}'
                GROUP BY t.branchId, t.date
            ) d
            GROUP BY d.branchId
        ";
        $avgNetW = mysqli_query($con, $qAvgNetW);
        $mapAvgNetW = [];
        while($r = mysqli_fetch_assoc($avgNetW)){
            $mapAvgNetW[$r['branchId']] = [
                'avg0' => (float)$r['avg0'], // today's weekday avg netW/day
                'avg1' => (float)$r['avg1'], // tomorrow's weekday avg netW/day
                'avg2' => (float)$r['avg2'], // day+2 weekday avg netW/day
            ];
        }

        /* ----------------- Opening & Cheques (unchanged) ----------------- */
        $open = [];
        $cheques = [];

        $openingData = mysqli_query($con,"
          SELECT a.branchId,
                         (CASE WHEN a.forward='Forward to HO' THEN 0 ELSE a.balance END) AS open,
                         COALESCE(a.cheques, 0) AS cheques
          FROM closing a
          INNER JOIN (
                SELECT c.branchId, MAX(c.date) AS date
                FROM closing c, branch b
                WHERE c.branchId!='' AND b.status=1 AND c.branchId=b.branchId
                  AND c.date < '{$todayDate}'
                GROUP BY c.branchId
          ) b
          ON a.branchId = b.branchId AND a.date = b.date
        ");

        while($row = mysqli_fetch_assoc($openingData)){
          $open[$row['branchId']]     = (float)$row['open'];
          $cheques[$row['branchId']]  = (int)$row['cheques'];
        }

        /* ----------------- Fetch Branches ----------------- */
        $branches = mysqli_query($con, "
                SELECT b.branchId,
                           b.branchName,
                           (CASE WHEN b.state='Andhra Pradesh' THEN 'Andhra_Pradesh' ELSE b.state END) AS state
                FROM branch b
                WHERE b.status = 1
                  AND b.branchId NOT IN ('AGPL000','AGPL999')
                {$extra}
        ");

        /* ----------------- Dropdown data for Action Modal ----------------- */
        // Branch list
        $branchListRes = mysqli_query($con, "
            SELECT branchId, branchName
            FROM branch
            WHERE status=1 AND branchId NOT IN ('AGPL000','AGPL999')
            ORDER BY branchName
        ");
        $branchOptions = [];
        while ($x = mysqli_fetch_assoc($branchListRes)) {
          $branchOptions[] = [
            'id'   => $x['branchId'],
            'name' => $x['branchName'],
            'text' => $x['branchId'].' — '.$x['branchName'],
          ];
        }
        // TE list
        $teListRes = mysqli_query($con, "
          SELECT empId, name, contact
          FROM employee
          WHERE designation='TE'
          ORDER BY name
        ");
        $teOptions = [];
        while ($x = mysqli_fetch_assoc($teListRes)) {
          $mobile = preg_replace('/\D+/', '', (string)$x['contact']);
          $teOptions[] = [
            'id'     => (string)$x['empId'],
            'name'   => $x['name'],
            'mobile' => $mobile,
            'text'   => $x['name'].' — '.$mobile.' — '.$x['empId'],
          ];
        }
        /* ----------------- Approver list from users (type='CashMovers') ----------------- */
        $approverRes = mysqli_query($con, "
        SELECT id, username, employeeId
        FROM users
        WHERE type='CashMovers'
        ORDER BY username
        ");

        $approverOptions = [];
        while ($u = mysqli_fetch_assoc($approverRes)) {
        $id   = (string)$u['id'];
        $user = (string)$u['username'];
        $eid  = trim((string)$u['employeeId']);
        // Shown text: Username (EmpID) if employeeId exists
        $text = $eid !== '' ? ($user.' — '.$eid) : $user;
        if ($id && $user) {
            $approverOptions[] = ['id' => $id, 'text' => $text];
        }
        }
?>
<script>
  // define once
  window._BRANCH_OPTIONS   = <?php echo json_encode($branchOptions, JSON_UNESCAPED_UNICODE); ?>;
  window._TE_OPTIONS       = <?php echo json_encode($teOptions, JSON_UNESCAPED_UNICODE); ?>;
  window._APPROVER_OPTIONS = <?php echo json_encode($approverOptions, JSON_UNESCAPED_UNICODE); ?>;
</script>

<style>
        #wrapper h3{ text-transform:uppercase; font-weight:700; font-size:18px; color:#123C69; }
        .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
        .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
        .btn-primary{ background-color:#123C69; }
        .theadRow { text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }
        .dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
        .btn-success{ display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box; text-decoration:none; font-size:12px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa; background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative; }
        .fa_Icon { color:#990000; }
        .modal-title { font-size:20px; font-weight:600; color:#708090; text-transform:uppercase; }
        .modal-header{ background:#123C69; }
        #wrapper .panel-body{ box-shadow:10px 15px 15px #999; border:1px solid #edf2f9; background-color:#f5f5f5; border-radius:3px; padding:20px; }
        .td-align-right{ text-align:right; }
        .table-responsive .row{ margin:0; }

        /* Row alert colors */
        tr { background: #88ff00 !important; } 
        tr.low-light { background: #ffd000 !important; }   /* light yellow (70–80%) */
        tr.low-dark  { background: #ffd000 !important; }   /* dark yellow  (60–70%) */
        tr.low-red   { background: #ff3300 !important; }   /* red          (50–60%) */
        tr.low-bright{ background: #ff3300 !important; }   /* bright red   (<50%)  */

        /* Legend KPI tiles */
        .kpi-legend .legend-box {
                display:inline-block; width:14px; height:14px; margin-right:6px; border:1px solid #888; vertical-align:middle;
        }
        /* Select2 height tweak */
        .select2-container .select2-selection--single { height: 34px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 34px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 34px; }
</style>

<div id="wrapper">
        <div class="row content">

                <div class="col-lg-12">
                        <div class="col-lg-8">
                                <h3><span></span>BRANCH AVAILABLE AMOUNT</h3>
                        </div>
                </div>

                <!-- KPI: Current Balance Total -->
                <div class="col-md-4">
                <div class="hpanel">
                    <div class="panel-body" style="min-height:160px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    <div class="text-center">
                        <h3 style="margin:0 0 8px 0;">CURRENT BALANCE</h3>
                        <p id="totalAvail" style="margin:0;"></p>
                    </div>
                    </div>
                </div>
                </div>

                <!-- KPI: Color Legend -->
                <div class="col-md-4">
                <div class="hpanel kpi-legend">
                    <div class="panel-body" style="min-height:160px;display:flex;flex-direction:column;justify-content:center;">                    
                    <div style="margin-top:6px;"><span class="legend-box" style="display:inline-block;width:14px;height:14px;margin-right:6px;border:1px solid #888;vertical-align:middle;background:#88ff00;"></span> &gt;80% funded </div>
                    <div style="margin-top:6px;"><span class="legend-box" style="display:inline-block;width:14px;height:14px;margin-right:6px;border:1px solid #888;vertical-align:middle;background:#ffd000;"></span> 60–80% funded </div>
                    <div style="margin-top:6px;"><span class="legend-box" style="display:inline-block;width:14px;height:14px;margin-right:6px;border:1px solid #888;vertical-align:middle;background:#ff3300;"></span> &lt;60% funded </div>
                    </div>
                </div>
                </div>

                <div class="col-md-4">
                <div class="hpanel kpi-legend">
                    <div class="panel-body" style="min-height:160px;display:flex;flex-direction:column;justify-content:center;">
                    <?php if($type != 'Zonal'){ ?>
                        <div class="col-lg-12" style="padding:0;">
                        <select class="form-control m-b" id="state" style="height:34px;">
                            <option selected="true" value="ALL">ALL</option>
                            <option value="APT">ANDHRA & TELANGANA</option>
                            <option value="KAR">KARNATAKA</option>
                            <option value="TN">TAMILNADU</option>
                        </select>
                        </div>
                    <?php } else { ?>
                        <div class='col-lg-4' style="padding:0;">
                        <select class='form-control m-b' id='state' style="height:34px;">
                            <?php
                            if($branch=="KA"){ echo "<option selected='true' value='KAR'>KARNATAKA</option>"; }
                            elseif($branch=="TN"){ echo "<option selected='true' value='TN'>TAMILNADU</option>"; }
                            elseif($branch=="AP-TS"){ echo "<option selected='true' value='APT'>ANDHRA & TELANGANA</option>"; }
                            ?>
                        </select>
                        </div>
                    <?php } ?>
                    </div>
                </div>
                </div>

                <div class="col-lg-12">
                        <div class="hpanel">
                                <div class="panel-body">
                                        <div class="table-responsive">
                                                <table id="exampleBalance" class="table table-bordered">
                                                        <thead>
                                                                <tr class="theadRow">
                                                                        <th>BRANCH ID</th>
                                                                        <th>BRANCH NAME</th>
                                                                        <th>BALANCE</th>
                                                                        <th>REQUIRED CASH</th>
                                                                        <th>GOLD RATE</th>
                                                                        <th>TODAY's AVG NETW</th>
                                                                        <th>TOMORROW's AVG NETW</th>
                                                                        <th>Day+2 AVG NETW</th>
                                                                        <th>TODAY CASH REQ</th>
                                                                        <th>TOMORROW CASH REQ</th>
                                                                        <th>DAY+2 CASH REQ</th>
                                                                        <th>ACTION</th>
                                                                </tr>
                                                        </thead>
                                                        <tbody id="tableBody">
                                                            <?php
                                                            function format_inr($num, $decimals = 0){
                                                                $neg = ($num < 0);
                                                                $n   = abs((float)$num);

                                                                // Keep exact decimal places first
                                                                $parts = explode('.', number_format($n, $decimals, '.', ''));
                                                                $int   = $parts[0];
                                                                $dec   = isset($parts[1]) ? $parts[1] : '';

                                                                // Grouping: last 3, then every 2
                                                                if (strlen($int) > 3) {
                                                                    $last3 = substr($int, -3);
                                                                    $rest  = substr($int, 0, -3);
                                                                    $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
                                                                    $int   = $rest . ',' . $last3;
                                                                }

                                                                $res = $int . ($decimals > 0 ? '.' . $dec : '');
                                                                return $neg ? '-' . $res : $res;
                                                            }
                                                            ?>

                                                                <?php
                                                                        while($b = mysqli_fetch_assoc($branches)){
                                                                                $branchId   = $b['branchId'];
                                                                                $branchName = $b['branchName'];
                                                                                $stateClass = $b['state'];

                                                                                $openBalance     = isset($open[$branchId]) ? $open[$branchId] : 0;
                                                                                $cashA           = isset($mapTransToday[$branchId]['cashA']) ? $mapTransToday[$branchId]['cashA'] : 0;
                                                                                $impsA           = isset($mapTransToday[$branchId]['impsA']) ? $mapTransToday[$branchId]['impsA'] : 0; // not used in balance
                                                                                $cashRelA        = isset($mapRelToday[$branchId]['cashRelA']) ? $mapRelToday[$branchId]['cashRelA'] : 0;
                                                                                $impsRelA        = isset($mapRelToday[$branchId]['impsRelA']) ? $mapRelToday[$branchId]['impsRelA'] : 0; // not used in balance
                                                                                $fundAmount      = isset($mapFundToday[$branchId]) ? $mapFundToday[$branchId] : 0;
                                                                                $transferAmount  = isset($mapTransferToday[$branchId]) ? $mapTransferToday[$branchId] : 0;
                                                                                $receivedAmount  = isset($mapReceiveToday[$branchName]) ? $mapReceiveToday[$branchName] : 0;
                                                                                $expenseAmount   = isset($mapExpenseToday[$branchId]) ? $mapExpenseToday[$branchId] : 0;

                                                                                // Balance (same formula as before)
                                                                                $balance = ($openBalance + $fundAmount + $receivedAmount) - ($cashA + $cashRelA + $transferAmount + $expenseAmount);

                                                                                // Gold rate yesterday (kept as-is)
                                                                                $goldRateCash = isset($mapGoldRate[$branchId]) ? $mapGoldRate[$branchId] : 0.0;

                                                                                // Avg NetW by weekday (last 3 months)
                                                                                $avgNw0 = isset($mapAvgNetW[$branchId]['avg0']) ? $mapAvgNetW[$branchId]['avg0'] : 0.0;
                                                                                $avgNw1 = isset($mapAvgNetW[$branchId]['avg1']) ? $mapAvgNetW[$branchId]['avg1'] : 0.0;
                                                                                $avgNw2 = isset($mapAvgNetW[$branchId]['avg2']) ? $mapAvgNetW[$branchId]['avg2'] : 0.0;

                                                                                // Per-day requirements
                                                                                $req0 = $avgNw0 * $goldRateCash; // today
                                                                                $req1 = $avgNw1 * $goldRateCash; // tomorrow
                                                                                $req2 = $avgNw2 * $goldRateCash; // day+2

                                                                                // Combined Required Cash after Balance column
                                                                                $required = $req0 + (0.60 * $req1) + (0.20 * $req2);

                                                                                // Coverage (balance vs required)
                                                                                $coverage = ($required > 0) ? ($balance / $required) : 1.0;

                                                                                echo "<tr class='".htmlspecialchars($stateClass,ENT_QUOTES)."' data-balance='".htmlspecialchars($balance,ENT_QUOTES)."' data-required='".htmlspecialchars($required,ENT_QUOTES)."' data-coverage='".htmlspecialchars($coverage,ENT_QUOTES)."'>";

                                                                                echo "<td>".htmlspecialchars($branchId)."</td>";
                                                                                echo "<td>".htmlspecialchars($branchName)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($balance, 0)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($required, 0)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($goldRateCash, 2)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($avgNw0, 3)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($avgNw1, 3)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($avgNw2, 3)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($req0, 0)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($req1, 0)."</td>";
                                                                                echo "<td class='td-align-right'>".format_inr($req2, 0)."</td>";

                                                                                // ACTION button (TO is fixed = current row branch; FROM will be selected in modal)
                                                                                echo "<td class='td-align-center'>
                                                                                        <button type='button'
                                                                                                class='btn btn-success btn-xs btn-move-cash'
                                                                                                data-to-id='".htmlspecialchars($branchId,ENT_QUOTES)."'
                                                                                                data-to-name='".htmlspecialchars($branchName,ENT_QUOTES)."'>
                                                                                        Move Cash
                                                                                        </button>
                                                                                    </td>";

                                                                                echo "</tr>";
                                                                        }
                                                                ?>
                                                        </tbody>
                                                </table>
                                        </div><!-- /.table-responsive -->
                                                        
                                </div>
                        </div>
                </div>
<?php include("footer.php"); ?>
                <!-- Move Cash Modal -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
                <div class="modal fade" id="moveCashModal" tabindex="-1" role="dialog" aria-labelledby="moveCashLabel">
                  <div class="modal-dialog" role="document">
                    <form id="moveCashForm" class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title" id="moveCashLabel">Move Cash</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label>From Branch</label>
                          <select class="form-control" id="fromBranch" name="fromBranchId" required></select>
                          <small>Available (before sending): ₹ <span id="fromAvailText">0</span></small>
                          <input type="hidden" id="fromAvail" name="fromAvail" value="0">
                        </div>
                        <div class="form-group">
                          <label>To Branch</label>
                          <select class="form-control" id="toBranch" name="toBranchId" required></select>
                        </div>
                        <div class="form-group">
                          <label>Transaction Executive (TE)</label>
                          <select class="form-control" id="teId" name="teId" required></select>
                        </div>
                        <div class="form-group">
                          <label>Amount (₹)</label>
                          <input type="number" step="1" min="1" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="form-group">
                            <label>Approved By</label>
                            <select class="form-control" id="approvedBy" name="approvedBy" required></select>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Approve & Send</button>
                      </div>
                    </form>
                  </div>
                </div>

        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script>
// Build a map of branchId -> balance by reading the table once
window._BALANCE_MAP = {};
(function(){
  const tbl = document.getElementById('exampleBalance');
  if(!tbl) return;
  const rows = tbl.tBodies[0]?.rows || [];
  for(let i=0;i<rows.length;i++){
    const tr = rows[i];
    // Col 0: BRANCH ID, Col 2: BALANCE (formatted)
    const bidCell = tr.cells[0];
    const balCell = tr.cells[2];
    if(!bidCell || !balCell) continue;
    const bid = (bidCell.textContent || '').trim();
    const bal = parseFloat((balCell.textContent || '').replace(/[ ,]/g,'')) || 0;
    if(bid) window._BALANCE_MAP[bid] = bal;
  }
})();
</script>

<!-- DataTables + state filter + current balance total + row coloring -->
<script>
  $(document).ready(() => {
    // Map UI select values to normalized state slugs in row className
    const stateMap = {
      'ALL': null, // no filtering
      'APT': ['andhrapradesh','telangana'],
      'KAR': ['karnataka'],
      'TN' : ['tamilnadu','tamil_nadu','tamil nadu']
    };

    let selectedStates = null;

    const dt = $('#exampleBalance').DataTable({
      paging: false,
      ajax: '',
      dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
      lengthMenu: [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, 'All']],
      buttons: [
        { extend: 'copy', className: 'btn-sm' },
        { extend: 'csv',  title: 'ExportReport', className: 'btn-sm' },
        { extend: 'pdf',  title: 'ExportReport', className: 'btn-sm' },
        { extend: 'print', className: 'btn-sm' }
      ],
      // Numeric render/sort columns (BALANCE idx 2; REQUIRED idx 3; rest numeric)
      columnDefs: [
        {
          targets: [2,3,4,5,6,7,8,9,10],
          render: function(data, type) {
            const num = parseFloat((data + '').replace(/[ ,]/g, '')) || 0;
            if (type === 'sort' || type === 'type') return num;
            return data;
          }
        }
      ]
    });

    // Custom filter by row class (state)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      if (settings.nTable.id !== 'exampleBalance') return true;
      if (!selectedStates) return true;
      const node = dt.row(dataIndex).node();
      if (!node) return true;
      const cls = (node.className || '').toLowerCase().replace(/\s+/g, '');
      return selectedStates.some(s => cls.includes(s));
    });

    function updateTotal() {
      const colIndex = 2; // BALANCE column
      const data = dt.column(colIndex, { filter: 'applied' }).data();
      let total = 0;
      for (let i = 0; i < data.length; i++) {
        const val = parseFloat((data[i] + '').replace(/[ ,]/g, '')) || 0;
        total += val;
      }
      document.getElementById('totalAvail').textContent = total.toLocaleString('en-IN');
    }

    // Apply row color coding based on coverage (balance / required)
    function colorRows() {
      dt.rows({page: 'current'}).every(function() {
        const node = this.node();
        if (!node) return;
        // remove old classes
        node.classList.remove('low-light','low-dark','low-red','low-bright');

        const coverage = parseFloat(node.getAttribute('data-coverage')) || 0;
        if (coverage < 0.5) {
          node.classList.add('low-bright'); // < 50%
        } else if (coverage < 0.6) {
          node.classList.add('low-red');    // 50–60%
        } else if (coverage < 0.7) {
          node.classList.add('low-dark');   // 60–70%
        } else if (coverage < 0.8) {
          node.classList.add('low-light');  // 70–80%
        }
      });
    }

    function refreshAll() {
      updateTotal();
      colorRows();
    }

    dt.on('draw', refreshAll);
    refreshAll();

    const stateSelect = document.getElementById('state');
    stateSelect.addEventListener('change', () => {
      const val = stateSelect.value;
      selectedStates = stateMap[val];
      dt.draw(); // triggers refreshAll via 'draw'
    });
  });
</script>

<!-- Single, consolidated modal logic (no duplicate handlers) -->
<script>
(function(){
  function fillSelect($sel, items){
    $sel.empty();
    (items || []).forEach(it => {
      const txt = it.text || (it.id + (it.name ? (' — ' + it.name) : ''));
      const opt = new Option(txt, it.id);
      $(opt).data('obj', it);
      $sel.append(opt);
    });
  }

  const $from = $('#fromBranch');
  const $to   = $('#toBranch');
  const $te   = $('#teId');
  const $appr = $('#approvedBy');
  const $amount = $('#amount');
  const $modal  = $('#moveCashModal');
  const $form   = $('#moveCashForm');
  const $submitBtn = $form.find('button[type="submit"]');

  // initialize selects only once when modal first shows
  let selectsInitialized = false;
  $modal.on('shown.bs.modal', function(){
    if (!selectsInitialized) {
      if (!$from.data('select2')) $from.select2({width:'100%'});
      if (!$to.data('select2'))   $to.select2({width:'100%'});
      if (!$te.data('select2'))   $te.select2({width:'100%'});
      if (!$appr.data('select2')) $appr.select2({width:'100%'});
      selectsInitialized = true;
    }
  });

  // build options once on ready
  $(document).ready(function(){
    fillSelect($from, window._BRANCH_OPTIONS);
    fillSelect($to,   window._BRANCH_OPTIONS);
    fillSelect($te,   window._TE_OPTIONS);
    fillSelect($appr, window._APPROVER_OPTIONS);
  });

  // read balance from table map
  function updateFromAvailByBranchId(fromId){
    const bal = (window._BALANCE_MAP && window._BALANCE_MAP[fromId]) ? Number(window._BALANCE_MAP[fromId]) : 0;
    $('#fromAvailText').text((bal||0).toLocaleString('en-IN'));
    $('#fromAvail').val(bal||0);
  }
  $('#fromBranch').on('change', function(){
    updateFromAvailByBranchId($(this).val());
  });

  // open modal from table button; lock TO
  $(document).on('click', '.btn-move-cash', function(){
    const toId   = $(this).data('to-id')   || '';

    $to.val(toId).trigger('change');
    $to.prop('disabled', true);   // keep TO fixed

    $from.val(null).trigger('change');  // let user choose FROM
    updateFromAvailByBranchId(null);    // clear/zero shown availability

    $amount.val('');
    $appr.val(null).trigger('change');

    $modal.modal('show');
  });

  // prevent double submit: bind once with in-flight guard
  let inFlight = false;
  $form.off('submit').on('submit', function(e){
    e.preventDefault();
    if (inFlight) return;

    const payload = {
      fromBranchId: $from.val(),
      toBranchId:   $to.val(),
      teId:         $te.val(),
      amount:       $amount.val(),
      approvedBy:   $appr.val(),
      fromAvail:    $('#fromAvail').val()
    };

    if (!payload.fromBranchId || !payload.toBranchId || !payload.teId || !payload.amount || !payload.approvedBy) {
      alert('Please fill all fields.');
      return;
    }
    if (payload.fromBranchId === payload.toBranchId){
      alert('From and To branch cannot be the same.');
      return;
    }

    inFlight = true;
    $submitBtn.prop('disabled', true);

    $.post('cash_move_submit.php', payload, function(resp){
      if (resp && resp.ok){
        $modal.modal('hide');
        alert('Cash move recorded & TE notified.');
        location.reload();
      } else {
        alert((resp && resp.error) ? resp.error : 'Failed to submit.');
      }
    }, 'json').fail(function(){
      alert('Server error. Please try again.');
    }).always(function(){
      inFlight = false;
      $submitBtn.prop('disabled', false);
    });
  });

  // when modal closes, allow picking a new TO next time
  $modal.on('hidden.bs.modal', function(){
    $to.prop('disabled', false);
    inFlight = false;
    $submitBtn.prop('disabled', false);
  });
})();
</script>

