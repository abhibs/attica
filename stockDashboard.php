<?php

session_start();

error_reporting(E_ERROR | E_PARSE);



// ACCESS CONTROL: only Master / Admin

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != 'Master') {

    include("logout.php");

    exit;

}



include("header.php");

include("menumaster.php");

include("dbConnection.php");



/* ----------------------------------------

   DATE & FILTERS

---------------------------------------- */

$today = date("Y-m-d");



$toDate   = isset($_GET['to_date'])   && $_GET['to_date']   != '' ? $_GET['to_date']   : $today;

$fromDate = isset($_GET['from_date']) && $_GET['from_date'] != '' ? $_GET['from_date'] : date('Y-m-d', strtotime('-6 days', strtotime($toDate)));



$filterStock    = isset($_GET['stock'])    ? trim($_GET['stock'])    : '';

$filterLocation = isset($_GET['location']) ? trim($_GET['location']) : '';

$filterType     = isset($_GET['tx_type'])  ? trim($_GET['tx_type'])  : 'All'; // All / Received / Dispatched



$fromEsc = mysqli_real_escape_string($con, $fromDate);

$toEsc   = mysqli_real_escape_string($con, $toDate);



$stockEsc    = $filterStock    !== '' ? mysqli_real_escape_string($con, $filterStock)    : '';

$locationEsc = $filterLocation !== '' ? mysqli_real_escape_string($con, $filterLocation) : '';



/* ----------------------------------------

   COMMON WHERE SNIPPETS (for range)

---------------------------------------- */

$whereRec = "date BETWEEN '$fromEsc' AND '$toEsc'";

$whereSent = $whereRec;



if ($stockEsc !== '') {

    $whereRec  .= " AND stock = '$stockEsc'";

    $whereSent .= " AND stock = '$stockEsc'";

}

if ($locationEsc !== '') {

    $whereRec  .= " AND location = '$locationEsc'";

    $whereSent .= " AND location = '$locationEsc'";

}



/* ----------------------------------------

   0) EXPORT TO CSV (Recent movements)

---------------------------------------- */

if (isset($_GET['export']) && $_GET['export'] === 'csv') {



    $txWhereRec = $whereRec;

    $txWhereSent = $whereSent;



    if ($filterType === 'Received') {

        $unionSQL = "

            SELECT 

                date, 'Received' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

            FROM stock_received

            WHERE $txWhereRec

            ORDER BY date DESC

        ";

    } elseif ($filterType === 'Dispatched') {

        $unionSQL = "

            SELECT 

                date, 'Dispatched' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

            FROM stockitem

            WHERE $txWhereSent

            ORDER BY date DESC

        ";

    } else {

        $unionSQL = "

            (SELECT 

                date, 'Received' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

             FROM stock_received

             WHERE $txWhereRec)

            UNION ALL

            (SELECT 

                date, 'Dispatched' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

             FROM stockitem

             WHERE $txWhereSent)

            ORDER BY date DESC

        ";

    }



    $txRes = mysqli_query($con, $unionSQL);



    header('Content-Type: text/csv; charset=utf-8');

    header('Content-Disposition: attachment; filename=stock_movements_'.$fromDate.'_to_'.$toDate.'.csv');



    $output = fopen('php://output', 'w');

    fputcsv($output, ['Date','Type','Item','Location','Qty','Responsible','From / To','Vehicle','Branch / Vendor']);



    if ($txRes) {

        while ($row = mysqli_fetch_assoc($txRes)) {

            fputcsv($output, [

                $row['date'],

                $row['tx_type'],

                $row['stock'],

                $row['location'],

                $row['qty'],

                $row['responsible'],

                $row['assign'],

                $row['vehicle'],

                $row['branch'],

            ]);

        }

    }

    fclose($output);

    exit;

}



/* ----------------------------------------

   1) KPI QUERIES (for range)

---------------------------------------- */



// TOTAL RECEIVED IN RANGE

$recRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT IFNULL(SUM(`count`),0) AS total

     FROM stock_received WHERE $whereRec"

));

$totalReceivedRange = (int)$recRow['total'];



// TOTAL DISPATCHED IN RANGE

$sentRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT IFNULL(SUM(`count`),0) AS total

     FROM stockitem WHERE $whereSent"

));

$totalDispatchedRange = (int)$sentRow['total'];



// TOTAL AVAILABLE OVERALL (global, not date-limited)

$availRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT

        (SELECT IFNULL(SUM(`count`),0) FROM stock_received) -

        (SELECT IFNULL(SUM(`count`),0) FROM stockitem) AS available"

));

$totalAvailableGlobal = (int)$availRow['available'];



// TOTAL DISTINCT ITEM TYPES (global)

$itemTypeRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT COUNT(DISTINCT stock) AS cnt FROM stock_received WHERE stock IS NOT NULL AND stock <> ''"

));

$totalItemTypes = (int)$itemTypeRow['cnt'];



// TOTAL LOCATIONS (global)

$locRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT COUNT(DISTINCT location) AS cnt FROM stock_received WHERE location IS NOT NULL AND location <> ''"

));

$totalLocations = (int)$locRow['cnt'];



// TOTAL TRANSACTIONS IN RANGE

$recTxRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT COUNT(*) AS cnt FROM stock_received WHERE $whereRec"

));

$sentTxRow = mysqli_fetch_assoc(mysqli_query($con,

    "SELECT COUNT(*) AS cnt FROM stockitem WHERE $whereSent"

));

$totalTxRange = (int)$recTxRow['cnt'] + (int)$sentTxRow['cnt'];



/* ----------------------------------------

   2) LOW STOCK ALERTS (GLOBAL)

---------------------------------------- */

$lowAlertRes = mysqli_query($con,

"SELECT stock, location,

        (SUM(`count`) - (

            SELECT IFNULL(SUM(si.`count`),0)

            FROM stockitem si

            WHERE si.stock = sr.stock AND si.location = sr.location

        )) AS available

 FROM stock_received sr

 GROUP BY stock, location

 HAVING available <= 5 AND available >= 0

 ORDER BY available ASC

 LIMIT 5"

);



/* ----------------------------------------

   3) FAST MOVING ITEMS (FOR RANGE)

---------------------------------------- */

$fastRes = mysqli_query($con,

"SELECT stock, SUM(qty) AS total_moved FROM (

    SELECT stock, SUM(`count`) AS qty

    FROM stock_received

    WHERE $whereRec

    GROUP BY stock

    UNION ALL

    SELECT stock, SUM(`count`) AS qty

    FROM stockitem

    WHERE $whereSent

    GROUP BY stock

) AS t

GROUP BY stock

ORDER BY total_moved DESC

LIMIT 5"

);



/* ----------------------------------------

   4) TREND GRAPH DATA (LAST N DAYS IN RANGE)

---------------------------------------- */

$trendMaxDays = 14; // show up to 14 days

$start = $fromDate;

$end   = $toDate;



// If range more than trendMaxDays, cut start to last N days

$daysDiff = (strtotime($end) - strtotime($start)) / 86400;

if ($daysDiff + 1 > $trendMaxDays) {

    $start = date('Y-m-d', strtotime("-".($trendMaxDays-1)." days", strtotime($end)));

}



$trendLabels   = [];

$trendReceived = [];

$trendSent     = [];



$cur = $start;

while ($cur <= $end) {

    $trendLabels[] = $cur;



    $dEsc = mysqli_real_escape_string($con, $cur);

    $wRec = "date = '$dEsc'";

    $wSent = "date = '$dEsc'";



    if ($stockEsc !== '') {

        $wRec  .= " AND stock = '$stockEsc'";

        $wSent .= " AND stock = '$stockEsc'";

    }

    if ($locationEsc !== '') {

        $wRec  .= " AND location = '$locationEsc'";

        $wSent .= " AND location = '$locationEsc'";

    }



    $rRow = mysqli_fetch_assoc(mysqli_query($con,

        "SELECT IFNULL(SUM(`count`),0) AS t FROM stock_received WHERE $wRec"

    ));

    $sRow = mysqli_fetch_assoc(mysqli_query($con,

        "SELECT IFNULL(SUM(`count`),0) AS t FROM stockitem WHERE $wSent"

    ));



    $trendReceived[] = (int)$rRow['t'];

    $trendSent[]     = (int)$sRow['t'];



    $cur = date('Y-m-d', strtotime("+1 day", strtotime($cur)));

}



/* ----------------------------------------

   5) RECENT MOVEMENTS TABLE (FOR RANGE + FILTER TYPE)

---------------------------------------- */

$txWhereRec = $whereRec;

$txWhereSent = $whereSent;



if ($filterType === 'Received') {

    $recentSQL = "

        SELECT date, 'Received' AS tx_type, stock, location,

               `count` AS qty, responsible, `assign`, vehicle, branch

        FROM stock_received

        WHERE $txWhereRec

        ORDER BY date DESC, id DESC

        LIMIT 100

    ";

} elseif ($filterType === 'Dispatched') {

    $recentSQL = "

        SELECT date, 'Dispatched' AS tx_type, stock, location,

               `count` AS qty, responsible, `assign`, vehicle, branch

        FROM stockitem

        WHERE $txWhereSent

        ORDER BY date DESC, id DESC

        LIMIT 100

    ";

} else {

    $recentSQL = "

        (SELECT date, 'Received' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

         FROM stock_received

         WHERE $txWhereRec)

        UNION ALL

        (SELECT date, 'Dispatched' AS tx_type, stock, location,

                `count` AS qty, responsible, `assign`, vehicle, branch

         FROM stockitem

         WHERE $txWhereSent)

        ORDER BY date DESC, tx_type ASC

        LIMIT 100

    ";

}

$recentTxRes = mysqli_query($con, $recentSQL);



/* ----------------------------------------

   6) DROPDOWN OPTIONS (GLOBAL)

---------------------------------------- */

$itemOptions = [];

$locOptions  = [];



$optRes = mysqli_query($con,

"SELECT DISTINCT stock FROM stock_received WHERE stock IS NOT NULL AND stock <> ''

 UNION

 SELECT DISTINCT stock FROM stockitem WHERE stock IS NOT NULL AND stock <> ''"

);

while ($or = mysqli_fetch_assoc($optRes)) {

    $s = trim($or['stock']);

    if ($s !== '' && !in_array($s, $itemOptions, true)) {

        $itemOptions[] = $s;

    }

}

sort($itemOptions);



$locRes = mysqli_query($con,

"SELECT DISTINCT location FROM stock_received WHERE location IS NOT NULL AND location <> ''

 UNION

 SELECT DISTINCT location FROM stockitem WHERE location IS NOT NULL AND location <> ''"

);

while ($lr = mysqli_fetch_assoc($locRes)) {

    $l = trim($lr['location']);

    if ($l !== '' && !in_array($l, $locOptions, true)) {

        $locOptions[] = $l;

    }

}

sort($locOptions);



?>

<style>

    #wrapper { background: #f5f5f5; }

    #wrapper h3 {

        text-transform: uppercase;

        font-weight: 600;

        font-size: 20px;

        color: #123C69;

    }

    .kpi-card {

        background:#fff;

        padding:15px 18px;

        margin-bottom:10px;

        border-radius:8px;

        box-shadow:0 3px 10px rgba(0,0,0,0.06);

        text-align:center;

    }

    .kpi-title {

        font-size:11px;

        text-transform:uppercase;

        color:#777;

        margin:0;

    }

    .kpi-value {

        font-size:20px;

        font-weight:700;

        color:#123C69;

        margin:3px 0 0;

    }

    .kpi-sub {

        font-size:11px;

        color:#999;

    }

    .alert-low {

        background:#fff3cd;

        border-left:4px solid #d39e00;

        padding:8px 12px;

        font-size:12px;

        margin:12px 0;

        border-radius:4px;

    }

    .filter-row {

        background:#fff;

        padding:12px 15px;

        border-radius:8px;

        box-shadow:0 2px 8px rgba(0,0,0,0.06);

        margin-bottom:15px;

    }

    .filter-row label {

        font-size:11px;

        font-weight:600;

        text-transform:uppercase;

        color:#123C69;

    }

    .section-box {

        background:#fff;

        padding:15px;

        border-radius:8px;

        box-shadow:0 2px 8px rgba(0,0,0,0.06);

        margin-bottom:15px;

    }

    .section-title {

        font-size:13px;

        font-weight:700;

        text-transform:uppercase;

        color:#123C69;

        margin-bottom:8px;

    }

    .badge-received {

        background:#e3f2fd;

        color:#1565c0;

        padding:3px 7px;

        border-radius:4px;

        font-size:11px;

    }

    .badge-dispatched {

        background:#ffebee;

        color:#c62828;

        padding:3px 7px;

        border-radius:4px;

        font-size:11px;

    }

</style>



<div id="wrapper">

    <div class="row content">

        <div class="col-lg-12">



            <div class="hpanel">

                <div class="panel-heading">

                    <h3>

                        <span style="color:#900" class="fa fa-dashboard"></span>

                        Stock Dashboard - Admin View

                    </h3>

                </div>



                <div class="panel-body"

                     style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px,

                                    rgb(0 0 0 / 30%) 0px 3px 7px -3px;

                            border-radius: 10px;">



                    <!-- FILTERS -->

                    <div class="filter-row row">

                        <form method="GET" class="form-horizontal">

                            <div class="col-sm-2">

                                <label>From Date</label>

                                <input type="date" name="from_date" class="form-control"

                                       value="<?php echo htmlspecialchars($fromDate); ?>">

                            </div>

                            <div class="col-sm-2">

                                <label>To Date</label>

                                <input type="date" name="to_date" class="form-control"

                                       value="<?php echo htmlspecialchars($toDate); ?>">

                            </div>

                            <div class="col-sm-3">

                                <label>Item / Product</label>

                                <select name="stock" class="form-control">

                                    <option value="">All Items</option>

                                    <?php foreach($itemOptions as $opt){

                                        $sel = ($opt === $filterStock) ? 'selected' : '';

                                        echo '<option value="'.htmlspecialchars($opt).'" '.$sel.'>'.htmlspecialchars($opt).'</option>';

                                    } ?>

                                </select>

                            </div>

                            <div class="col-sm-3">

                                <label>Location</label>

                                <select name="location" class="form-control">

                                    <option value="">All Locations</option>

                                    <?php foreach($locOptions as $loc){

                                        $sel = ($loc === $filterLocation) ? 'selected' : '';

                                        echo '<option value="'.htmlspecialchars($loc).'" '.$sel.'>'.htmlspecialchars($loc).'</option>';

                                    } ?>

                                </select>

                            </div>

                            <div class="col-sm-2">

                                <label>Type</label>

                                <select name="tx_type" class="form-control">

                                    <option value="All"       <?php if($filterType=='All') echo 'selected'; ?>>All</option>

                                    <option value="Received"  <?php if($filterType=='Received') echo 'selected'; ?>>Received</option>

                                    <option value="Dispatched"<?php if($filterType=='Dispatched') echo 'selected'; ?>>Dispatched</option>

                                </select>

                            </div>

                     <div class="col-sm-12" style="margin-top:10px; text-align:center;">



    <button type="submit" class="btn btn-success">

        <i class="fa fa-filter" style="color:#ffcf40"></i> Apply Filters

    </button>



    <a href="stockDashboard.php" class="btn btn-default">

        Reset

    </a>



    <!-- 

    <a href="stockDashboard.php?export=csv&from_date=<?php echo urlencode($fromDate); ?>&to_date=<?php echo urlencode($toDate); ?>&stock=<?php echo urlencode($filterStock); ?>&location=<?php echo urlencode($filterLocation); ?>&tx_type=<?php echo urlencode($filterType); ?>"

       class="btn btn-primary" style="margin-left:5px;">

        <i class="fa fa-download"></i> Download CSV

    </a>



    <a href="stockAvailable.php" class="btn btn-info" style="margin-left:5px;">

        <i class="fa fa-list"></i> View Full Stock Available Sheet

    </a>

    -->



</div>



                        </form>

                    </div>



                 

                     



                    <!-- LOW STOCK ALERTS -->

                    <?php if ($lowAlertRes && mysqli_num_rows($lowAlertRes) > 0) { ?>

                    <div class="alert-low">

                        <strong>⚠ Low Stock Alerts (Global, ≤ 5 Units)</strong><br>

                        <?php while($ls = mysqli_fetch_assoc($lowAlertRes)) {

                            echo htmlspecialchars($ls['stock'])." @ ".htmlspecialchars($ls['location'])." (".$ls['available']."), ";

                        } ?>

                    </div>

                    <?php } ?>



                    <!-- TREND CHART + FAST MOVING -->

                    <div class="row">

                        <div class="col-sm-8">

                            <div class="section-box">

                                <div class="section-title">

                                    Movement Trend (Received vs Dispatched)

                                </div>

                                <canvas id="trendChart" height="120"></canvas>

                            </div>

                        </div>

                        <div class="col-sm-4">

                            <div class="section-box">

                                <div class="section-title">

                                    Fast Moving Items (Range)

                                </div>

                                <table class="table table-condensed table-striped">

                                    <thead>

                                        <tr>

                                            <th>#</th>

                                            <th>Item</th>

                                            <th>Total Moved</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                    <?php

                                    $rank = 1;

                                    if ($fastRes && mysqli_num_rows($fastRes) > 0) {

                                        while ($fm = mysqli_fetch_assoc($fastRes)) {

                                            echo "<tr>";

                                            echo "<td>".$rank."</td>";

                                            echo "<td>".htmlspecialchars($fm['stock'])."</td>";

                                            echo "<td>".(int)$fm['total_moved']."</td>";

                                            echo "</tr>";

                                            $rank++;

                                        }

                                    } else {

                                        echo "<tr><td colspan='3' class='text-center'>No movement in this range.</td></tr>";

                                    }

                                    ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>



                    <!-- RECENT MOVEMENTS TABLE -->

                    <div class="section-box">

                        <div class="section-title">

                            Recent Stock Movements (Filtered Range)

                        </div>

                        <table id="exampleDashboard" class="table table-striped table-bordered table-hover">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Date</th>

                                    <th>Type</th>

                                    <th>Item / Product</th>

                                    <th>Location</th>

                                    <th>Qty</th>

                                    <th>Responsible</th>

                                    <th>From / To</th>

                                    <th>Vehicle</th>

                                    <th>Branch / Vendor</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                $idx = 1;

                                if ($recentTxRes && mysqli_num_rows($recentTxRes) > 0) {

                                    while ($tx = mysqli_fetch_assoc($recentTxRes)) {

                                        $badge = $tx['tx_type'] === 'Received'

                                            ? '<span class="badge-received">Received</span>'

                                            : '<span class="badge-dispatched">Dispatched</span>';

                                        echo "<tr>";

                                        echo "<td>".$idx."</td>";

                                        echo "<td>".htmlspecialchars($tx['date'])."</td>";

                                        echo "<td>".$badge."</td>";

                                        echo "<td>".htmlspecialchars($tx['stock'])."</td>";

                                        echo "<td>".htmlspecialchars($tx['location'])."</td>";

                                        echo "<td>".(int)$tx['qty']."</td>";

                                        echo "<td>".htmlspecialchars($tx['responsible'])."</td>";

                                        echo "<td>".htmlspecialchars($tx['assign'])."</td>";

                                        echo "<td>".htmlspecialchars($tx['vehicle'])."</td>";

                                        echo "<td>".htmlspecialchars($tx['branch'])."</td>";

                                        echo "</tr>";

                                        $idx++;

                                    }

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



<!-- Chart.js (CDN) -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    const trendLabels   = <?php echo json_encode($trendLabels); ?>;

    const trendReceived = <?php echo json_encode($trendReceived); ?>;

    const trendSent     = <?php echo json_encode($trendSent); ?>;



    const ctx = document.getElementById('trendChart').getContext('2d');



    new Chart(ctx, {

        type: 'line',

        data: {

            labels: trendLabels,

            datasets: [

                {

                    label: 'Received',

                    data: trendReceived,

                    borderWidth: 2,

                    tension: 0.2

                },

                {

                    label: 'Dispatched',

                    data: trendSent,

                    borderWidth: 2,

                    tension: 0.2

                }

            ]

        },

        options: {

            responsive: true,

            plugins: {

                legend: { position: 'bottom' }

            },

            scales: {

                x: {

                    ticks: { autoSkip: true, maxRotation: 0 }

                },

                y: {

                    beginAtZero: true

                }

            }

        }

    });

</script>

<?php // end of file ?>


