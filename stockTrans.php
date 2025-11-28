<?php

session_start();

error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];



if ($type == 'StockManager') {

    include("header.php");

    include("menuStockManage.php");

} else if ($type == 'Master') {

    include("header.php");

    include("menumaster.php");

} else {

    include("logout.php");

    exit;

}



include("dbConnection.php");



$date = date("Y-m-d");

$emp_id = $_SESSION['employeeId'] ?? '';



/* ------------------------------------------------

   FILTERS (Item / Location)

------------------------------------------------ */

$filterStock    = isset($_GET['stock'])    ? trim($_GET['stock'])    : '';

$filterLocation = isset($_GET['location']) ? trim($_GET['location']) : '';



$stockEsc    = $filterStock    !== '' ? mysqli_real_escape_string($con, $filterStock)    : '';

$locationEsc = $filterLocation !== '' ? mysqli_real_escape_string($con, $filterLocation) : '';



/* ------------------------------------------------

   BUILD DROPDOWN OPTIONS (distinct stock & location)

------------------------------------------------ */

$itemOptions = [];

$locOptions  = [];



// from stock_received

$res = mysqli_query(

    $con,

    "SELECT DISTINCT stock, location

     FROM stock_received

     WHERE stock IS NOT NULL AND stock <> ''"

);

while ($r = mysqli_fetch_assoc($res)) {

    $s = trim($r['stock']);

    $l = trim($r['location']);

    if ($s !== '' && !in_array($s, $itemOptions, true)) {

        $itemOptions[] = $s;

    }

    if ($l !== '' && !in_array($l, $locOptions, true)) {

        $locOptions[] = $l;

    }

}



// from stockitem also (to cover all)

$res2 = mysqli_query(

    $con,

    "SELECT DISTINCT stock, location

     FROM stockitem

     WHERE stock IS NOT NULL AND stock <> ''"

);

while ($r2 = mysqli_fetch_assoc($res2)) {

    $s = trim($r2['stock']);

    $l = trim($r2['location']);

    if ($s !== '' && !in_array($s, $itemOptions, true)) {

        $itemOptions[] = $s;

    }

    if ($l !== '' && !in_array($l, $locOptions, true)) {

        $locOptions[] = $l;

    }

}



sort($itemOptions);

sort($locOptions);



/* ------------------------------------------------

   RECENT TRANSACTION LOG (Last 50 Records)

   (now also fetching image)

------------------------------------------------ */

$txWhereReceived = "1=1";

$txWhereSent     = "1=1";



if ($stockEsc !== '') {

    $txWhereReceived .= " AND stock = '$stockEsc'";

    $txWhereSent     .= " AND stock = '$stockEsc'";

}

if ($locationEsc !== '') {

    $txWhereReceived .= " AND location = '$locationEsc'";

    $txWhereSent     .= " AND location = '$locationEsc'";

}



$txQuery = mysqli_query(

    $con,

    "(SELECT 

        'Received' AS tx_type,

        date,

        stock,

        location,

        `count`,

        responsible,

        `assign`,

        vehicle,

        branch,

        image

      FROM stock_received

      WHERE $txWhereReceived)

     UNION ALL

     (SELECT

        'Dispatched' AS tx_type,

        date,

        stock,

        location,

        `count`,

        responsible,

        `assign`,

        vehicle,

        branch,

        image

      FROM stockitem

      WHERE $txWhereSent)

     ORDER BY date DESC, tx_type ASC

     LIMIT 50"

);

?>

<style>

    #wrapper {

        background: #f5f5f5;

    }



    #wrapper h3 {

        text-transform: uppercase;

        font-weight: 600;

        font-size: 20px;

        color: #123C69;

    }



    .form-control[disabled],

    .form-control[readonly],

    fieldset[disabled] .form-control {

        background-color: #fffafa;

    }



    .text-success {

        color: #123C69;

        text-transform: uppercase;

        font-weight: bold;

        font-size: 12px;

    }



    .fa_Icon {

        color: #ffa500;

    }



    thead {

        text-transform: uppercase;

        background-color: #123C69;

    }



    thead tr {

        color: #f2f2f2;

        font-size: 12px;

    }



    .dataTables_empty {

        text-align: center;

        font-weight: 600;

        font-size: 12px;

        text-transform: uppercase;

    }



    .btn-success {

        display: inline-block;

        padding: 0.7em 1.4em;

        margin: 0 0.3em 0.3em 0;

        border-radius: 0.15em;

        box-sizing: border-box;

        text-decoration: none;

        font-size: 12px;

        color: #fffafa;

        background-color: #123C69;

        box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);

        text-align: center;

    }



    .filter-row {

        margin-bottom: 15px;

        padding: 10px 15px;

        background: #fff;

        border-radius: 6px;

        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);

    }



    .filter-row label {

        font-size: 11px;

        font-weight: 600;

        text-transform: uppercase;

        color: #123C69;

    }



    .filter-row .btn {

        margin-top: 18px;

    }



    .section-title {

        font-size: 13px;

        font-weight: 700;

        text-transform: uppercase;

        color: #123C69;

        margin-bottom: 8px;

    }



    /* BADGES FOR RECEIVED / DISPATCHED */

    .badge-received {

        background: #e3f2fd;

        color: #1565c0;

        padding: 3px 7px;

        border-radius: 4px;

        font-size: 11px;

        font-weight: 600;

        display: inline-block;

    }



    .badge-dispatched {

        background: #ffebee;

        color: #c62828;

        padding: 3px 7px;

        border-radius: 4px;

        font-size: 11px;

        font-weight: 600;

        display: inline-block;

    }

</style>



<div id="wrapper">

    <div class="row content">

        <div class="col-lg-12">



            <div class="hpanel">

                <div class="panel-heading">

                    <h3>

                        <span style="color:#900" class="fa fa-exchange"></span>

                        Stock Transactions - Recent Movements

                    </h3>

                </div>



                <div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px,

                                    rgb(0 0 0 / 30%) 0px 3px 7px -3px;

                            border-radius: 10px;">



                    <p class="text-success">

                        <i class="fa fa-info-circle fa_Icon"></i>

                        &nbsp;Below table shows the <b>last 50 stock movements</b> across Received &amp; Dispatched,

                        with optional filters for Item &amp; Location.

                    </p>



                    <!-- FILTERS -->

                    <div class="filter-row row">

                        <form method="GET" class="form-horizontal">

                            <div class="col-sm-4">

                                <label>Filter by Item / Product</label>

                                <select name="stock" class="form-control">

                                    <option value="">All Items</option>

                                    <?php

                                    foreach ($itemOptions as $opt) {

                                        $sel = ($opt === $filterStock) ? 'selected' : '';

                                        echo '<option value="' . htmlspecialchars($opt) . '" ' . $sel . '>' . htmlspecialchars($opt) . '</option>';

                                    }

                                    ?>

                                </select>

                            </div>

                            <div class="col-sm-4">

                                <label>Filter by Location</label>

                                <select name="location" class="form-control">

                                    <option value="">All Locations</option>

                                    <?php

                                    foreach ($locOptions as $loc) {

                                        $sel = ($loc === $filterLocation) ? 'selected' : '';

                                        echo '<option value="' . htmlspecialchars($loc) . '" ' . $sel . '>' . htmlspecialchars($loc) . '</option>';

                                    }

                                    ?>

                                </select>

                            </div>

                            <div class="col-sm-4" style="margin-top:10px; text-align:center;">

                                <button type="submit" class="btn btn-success">

                                    <span class="fa fa-filter" style="color:#ffcf40"></span> Apply Filter

                                </button>

                                <a href="stockTrans.php" class="btn btn-default" style="margin-left:5px;margin-top:0;">

                                    Reset

                                </a>

                            </div>

                        </form>

                    </div>



                    <!-- RECENT TRANSACTIONS TABLE -->

                    <div class="section-title">

                        Recent Transactions (Last 50 Records)

                    </div>

                    <table id="example2" class="table table-striped table-bordered table-hover">

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

                                <th>Image</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $j = 1;

                            if ($txQuery) {

                                while ($tx = mysqli_fetch_assoc($txQuery)) {



                                    // Badge for Received / Dispatched

                                    $badge = $tx['tx_type'] === 'Received'

                                        ? '<span class="badge-received">Received</span>'

                                        : '<span class="badge-dispatched">Dispatched</span>';



                                    // Image link (if available)

                                    $imgCell = '-';

                                    if (!empty($tx['image'])) {

                                        $imgFile = htmlspecialchars($tx['image']);

                                        $imgCell = "

                                            <a class='btn btn-success btn-xs' target='_blank' href='StockManagement/" . $imgFile . "'>

                                                <i style='color:#ffcf40' class='fa fa-file-o'></i> View

                                            </a>

                                        ";

                                    }



                                    echo "<tr>";

                                    echo "<td>" . $j . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['date']) . "</td>";

                                    echo "<td>" . $badge . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['stock']) . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['location']) . "</td>";

                                    echo "<td>" . (int)$tx['count'] . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['responsible']) . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['assign']) . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['vehicle']) . "</td>";

                                    echo "<td>" . htmlspecialchars($tx['branch']) . "</td>";

                                    echo "<td class='text-center'>" . $imgCell . "</td>";

                                    echo "</tr>";

                                    $j++;

                                }

                            }

                            ?>

                        </tbody>

                    </table>



                </div>

            </div>



        </div>

        <?php include("footer.php"); ?>

    </div>

</div>


