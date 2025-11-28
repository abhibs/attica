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
   MASTER ITEM LIST (same as receive / dispatch)
------------------------------------------------ */
$defaultItems = [
    'Aluminium-Clamps-Pack' => 'Aluminium Clamps Pack',
    'Brown-Corner-Beeding' => 'Brown Corner Beeding',
    'Brown-PVC-Panel' => 'Brown PVC Panel',
    'Brown-wall-Panel' => 'Brown Wall Panel',
    'Cash-Counting-Machine' => 'Cash Counting Machine',
    'Electrical-miscellaneous' => 'Electrical Miscellaneous',
    'Floor-Mat(Local)' => 'Floor Mat (Local)',
    'Floor-Mat(Premium)' => 'Floor Mat (Premium)',
    'Karat-Machine' => 'Karat Machine',
    'LED-Roof(Warranty)' => 'LED Roof (Warranty)',
    'LED-Roof(Without Warranty)' => 'LED Roof (Without Warranty)',
    'Printer-Machine' => 'Printer Machine',
    'Red-Fevicol-Box' => 'Red Fevicol Box',
    'System' => 'System',
    'UPS/Battery' => 'UPS / Battery',
    'Weighing-Scale' => 'Weighing Scale',
    'White-PVC-Panel' => 'White PVC Panel',
    'White-Corner-Beeding' => 'White Corner Beeding',
    'Electrical-Item' => 'Electrical Item',
    'MDF-short-table' => 'MDF Short Table',
    'MDF-long-table' => 'MDF Long Table',
    'MDF-support-small-box' => 'MDF Support Small Box',
    'White-Beeding(1*40)' => 'White Beeding (1×40)',
    'Brown-Beeding(1*40)' => 'Brown Beeding (1×40)',
    'Gold-Wall-Panel' => 'Gold Wall Panel',
    'Silicon' => 'Silicon',
];

/* ------------------------------------------------
   FILTERS (Item / Location)
------------------------------------------------ */
$filterStock = isset($_GET['stock']) ? trim($_GET['stock']) : '';
$filterLocation = isset($_GET['location']) ? trim($_GET['location']) : '';

$stockEsc = $filterStock !== '' ? mysqli_real_escape_string($con, $filterStock) : '';
$locationEsc = $filterLocation !== '' ? mysqli_real_escape_string($con, $filterLocation) : '';

/* ------------------------------------------------
   1) OPENING STOCK = all movements BEFORE today
      (Received - Dispatched, before $date)
------------------------------------------------ */
$stockData = []; // final aggregated

$condOpeningReceived = "date < '$date'";
$condOpeningSent = "date < '$date'";

if ($stockEsc !== '') {
    $condOpeningReceived .= " AND stock = '$stockEsc'";
    $condOpeningSent .= " AND stock = '$stockEsc'";
}
if ($locationEsc !== '') {
    $condOpeningReceived .= " AND location = '$locationEsc'";
    $condOpeningSent .= " AND location = '$locationEsc'";
}

$openingQuery = mysqli_query(
    $con,
    "SELECT stock, location,
            SUM(
                CASE WHEN source='received' THEN qty ELSE -qty END
            ) AS opening_balance
     FROM (
            SELECT stock, location, `count` AS qty, 'received' AS source
            FROM stock_received
            WHERE $condOpeningReceived

            UNION ALL

            SELECT stock, location, `count` AS qty, 'sent' AS source
            FROM stockitem
            WHERE $condOpeningSent
        ) AS combined
     GROUP BY stock, location"
);

while ($row = mysqli_fetch_assoc($openingQuery)) {
    $stock = $row['stock'];
    $location = $row['location'];
    $key = $stock . '|' . $location;

    $stockData[$key] = [
        'stock' => $stock,
        'location' => $location,
        'opening' => (int) $row['opening_balance'],
        'received_today' => 0,
        'sent_today' => 0
    ];
}

/* ------------------------------------------------
   2) TODAY RECEIVED (stock_received WHERE date = today)
------------------------------------------------ */
$condTodayReceived = "date = '$date'";
if ($stockEsc !== '') {
    $condTodayReceived .= " AND stock = '$stockEsc'";
}
if ($locationEsc !== '') {
    $condTodayReceived .= " AND location = '$locationEsc'";
}

$todayReceivedQuery = mysqli_query(
    $con,
    "SELECT stock, location, SUM(`count`) AS total_received
     FROM stock_received
     WHERE $condTodayReceived
     GROUP BY stock, location"
);

while ($row = mysqli_fetch_assoc($todayReceivedQuery)) {
    $stock = $row['stock'];
    $location = $row['location'];
    $key = $stock . '|' . $location;

    if (!isset($stockData[$key])) {
        $stockData[$key] = [
            'stock' => $stock,
            'location' => $location,
            'opening' => 0,
            'received_today' => 0,
            'sent_today' => 0
        ];
    }

    $stockData[$key]['received_today'] = (int) $row['total_received'];
}

/* ------------------------------------------------
   3) TODAY DISPATCHED (stockitem WHERE date = today)
------------------------------------------------ */
$condTodaySent = "date = '$date'";
if ($stockEsc !== '') {
    $condTodaySent .= " AND stock = '$stockEsc'";
}
if ($locationEsc !== '') {
    $condTodaySent .= " AND location = '$locationEsc'";
}

$todaySentQuery = mysqli_query(
    $con,
    "SELECT stock, location, SUM(`count`) AS total_sent
     FROM stockitem
     WHERE $condTodaySent
     GROUP BY stock, location"
);

while ($row = mysqli_fetch_assoc($todaySentQuery)) {
    $stock = $row['stock'];
    $location = $row['location'];
    $key = $stock . '|' . $location;

    if (!isset($stockData[$key])) {
        $stockData[$key] = [
            'stock' => $stock,
            'location' => $location,
            'opening' => 0,
            'received_today' => 0,
            'sent_today' => 0
        ];
    }

    $stockData[$key]['sent_today'] = (int) $row['total_sent'];
}

/* ------------------------------------------------
   3B) ENSURE ALL MASTER ITEMS SHOW (EVEN IF 0)
------------------------------------------------ */
if ($filterStock === '' && $filterLocation === '') {
    // No filters → show all master items
    foreach ($defaultItems as $code => $label) {
        $found = false;
        foreach ($stockData as $row) {
            if ($row['stock'] === $code) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $key = $code . '|-';
            $stockData[$key] = [
                'stock' => $code,
                'location' => '-',
                'opening' => 0,
                'received_today' => 0,
                'sent_today' => 0
            ];
        }
    }
} elseif ($filterStock !== '') {
    // Filter by specific item
    $code = $filterStock;
    $found = false;
    foreach ($stockData as $row) {
        if ($row['stock'] === $code) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $loc = $filterLocation !== '' ? $filterLocation : '-';
        $key = $code . '|' . $loc;
        $stockData[$key] = [
            'stock' => $code,
            'location' => $loc,
            'opening' => 0,
            'received_today' => 0,
            'sent_today' => 0
        ];
    }
}

/* ------------------------------------------------
   4) BUILD DROPDOWN OPTIONS (distinct stock & location)
      + ensure master items are present in dropdown
------------------------------------------------ */
$itemOptions = array_keys($defaultItems);  // start with master items
$locOptions = [];

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
   5) SUMMARY TOTALS for FILTER SCOPE
   (Opening, Today Received, Today Dispatched, Available)
------------------------------------------------ */
$totalOpening = 0;
$totalReceivedToday = 0;
$totalSentToday = 0;
$totalAvailable = 0;

foreach ($stockData as $row) {
    $opening = (int) $row['opening'];
    $recvT = (int) $row['received_today'];
    $sentT = (int) $row['sent_today'];
    $avail = $opening + $recvT - $sentT;

    $totalOpening += $opening;
    $totalReceivedToday += $recvT;
    $totalSentToday += $sentT;
    $totalAvailable += $avail;
}

/* ------------------------------------------------
   6) IMAGE MAP (Item + Location -> Latest Image)
------------------------------------------------ */
$imageMap = [];

$imgQuery = mysqli_query(
    $con,
    "(SELECT stock, location, image, date
      FROM stock_received
      WHERE image IS NOT NULL AND image <> '')
     UNION ALL
     (SELECT stock, location, image, date
      FROM stockitem
      WHERE image IS NOT NULL AND image <> '')
     ORDER BY date DESC"
);

if ($imgQuery) {
    while ($img = mysqli_fetch_assoc($imgQuery)) {
        $stock = $img['stock'];
        $location = $img['location'];
        $imgFile = $img['image'];

        if (trim($stock) === '' || trim($location) === '' || trim($imgFile) === '') {
            continue;
        }

        $key = $stock . '|' . $location;

        // first occurrence will be the latest because of ORDER BY date DESC
        if (!isset($imageMap[$key])) {
            $imageMap[$key] = $imgFile;
        }
    }
}
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

    .label-available {
        font-weight: bold;
    }

    .label-positive {
        color: #2e7d32;
        /* green-ish */
    }

    .label-zero {
        color: #555;
    }

    .label-negative {
        color: #c62828;
        /* red-ish */
    }

    .summary-cards {
        margin-bottom: 15px;
    }

    .summary-card {
        background: #fff;
        border-radius: 6px;
        padding: 12px 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        text-align: center;
        margin-bottom: 10px;
        transition: all 0.2s ease-in-out;
    }

    .summary-card-click {
        cursor: pointer;
    }

    .summary-card-click:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }

    .summary-card-click.active {
        border: 2px solid #123C69;
        box-shadow: 0 4px 12px rgba(18, 60, 105, 0.25);
    }

    .summary-card h4 {
        margin: 0;
        font-size: 11px;
        text-transform: uppercase;
        color: #777;
        letter-spacing: 0.5px;
    }

    .summary-card p {
        margin: 3px 0 0;
        font-size: 16px;
        font-weight: 700;
        color: #123C69;
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

    /* Light background colors for the 4 KPI boxes */
    .summary-cards .col-sm-3:nth-child(1) .summary-card {
        background-color: #e3f2fd;
        /* light blue */
    }

    .summary-cards .col-sm-3:nth-child(2) .summary-card {
        background-color: #e8f5e9;
        /* light green */
    }

    .summary-cards .col-sm-3:nth-child(3) .summary-card {
        background-color: #fff3e0;
        /* light orange */
    }

    .summary-cards .col-sm-3:nth-child(4) .summary-card {
        background-color: #fce4ec;
        /* light pink */
    }

    /* Details panels */
    .details-panel {
        background: #ffffff;
        border-radius: 8px;
        padding: 12px 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.10);
        margin-top: 15px;
    }
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">

            <div class="hpanel">
                <div class="panel-heading">
                    <h3>
                        <span style="color:#900" class="fa fa-calendar-check-o"></span>
                        Stock Management - Available Stock
                    </h3>
                </div>

                <div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px,
                                    rgb(0 0 0 / 30%) 0px 3px 7px -3px;
                            border-radius: 10px;">

                    <p class="text-success">
                        <i class="fa fa-info-circle fa_Icon"></i>
                        &nbsp;<b>Available Stock = Opening Stock + Total Received (Today) − Total Dispatched (Today)</b>
                        | Opening stock is derived from <b>all previous days</b> (stock_received &amp; stockitem).
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
                                        $label = isset($defaultItems[$opt]) ? $defaultItems[$opt] : $opt;
                                        echo '<option value="' . htmlspecialchars($opt) . '" ' . $sel . '>' . htmlspecialchars($label) . '</option>';
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
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-success">
                                    <span class="fa fa-filter" style="color:#ffcf40"></span> Apply Filter
                                </button>
                                <a href="stockAvailable.php" class="btn btn-default"
                                    style="margin-left:5px;margin-top:0;">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <div class="row summary-cards">
                        <!-- Opening -->
                        <div class="col-sm-3">
                            <div class="summary-card summary-card-click" id="cardOpening">
                                <h4>Opening Stock (Filtered)</h4>
                                <p><?php echo (int) $totalOpening; ?></p>
                                <small style="font-size:10px;color:#555;display:block;margin-top:4px;">
                                    Click to view detailed opening stock
                                </small>
                            </div>
                        </div>
                        <!-- Received Today -->
                        <div class="col-sm-3">
                            <div class="summary-card summary-card-click" id="cardReceived">
                                <h4>Total Received (Today)</h4>
                                <p><?php echo (int) $totalReceivedToday; ?></p>
                                <small style="font-size:10px;color:#555;display:block;margin-top:4px;">
                                    Click to view today's received details
                                </small>
                            </div>
                        </div>
                        <!-- Dispatched Today -->
                        <div class="col-sm-3">
                            <div class="summary-card summary-card-click" id="cardSent">
                                <h4>Total Dispatched (Today)</h4>
                                <p><?php echo (int) $totalSentToday; ?></p>
                                <small style="font-size:10px;color:#555;display:block;margin-top:4px;">
                                    Click to view today's dispatched details
                                </small>
                            </div>
                        </div>
                        <!-- Available Now -->
                        <div class="col-sm-3">
                            <div class="summary-card summary-card-click" id="cardAvailable">
                                <h4>Available Stock (Now)</h4>
                                <p><?php echo (int) $totalAvailable; ?></p>
                                <small style="font-size:10px;color:#555;display:block;margin-top:4px;">
                                    Click to view available stock details
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- PANELS -->

                    <!-- Opening Stock Panel -->
                    <div id="openingDetailsPanel" class="details-panel" style="display:none;">
                        <div class="section-title">
                            Opening Stock – Detailed View (Filtered, by Item &amp; Location)
                        </div>

                        <table id="example5" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Item / Product</th>
                                    <th>Location</th>
                                    <th>Opening Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                if (!empty($stockData)) {
                                    $rows = array_values($stockData);
                                    usort($rows, function ($a, $b) use ($defaultItems) {
                                        $la = isset($defaultItems[$a['stock']]) ? $defaultItems[$a['stock']] : $a['stock'];
                                        $lb = isset($defaultItems[$b['stock']]) ? $defaultItems[$b['stock']] : $b['stock'];

                                        $cmp = strcasecmp($la, $lb);
                                        if ($cmp !== 0) {
                                            return $cmp;
                                        }
                                        return strcasecmp($a['location'], $b['location']);
                                    });

                                    foreach ($rows as $row) {
                                        $opening = (int) $row['opening'];
                                        if ($opening == 0) {
                                            continue; // show only rows contributing to opening
                                        }

                                        $itemCode = $row['stock'];
                                        $itemLabel = isset($defaultItems[$itemCode]) ? $defaultItems[$itemCode] : $itemCode;

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . htmlspecialchars($itemLabel) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td>" . $opening . "</td>";
                                        echo "</tr>";
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Received Today Panel -->
                    <div id="receivedDetailsPanel" class="details-panel" style="display:none;">
                        <div class="section-title">
                            Total Received (Today) – Detailed View (by Item &amp; Location)
                        </div>

                        <table id="example6" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Item / Product</th>
                                    <th>Location</th>
                                    <th>Received Today</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                if (!empty($stockData)) {
                                    $rows = array_values($stockData);
                                    usort($rows, function ($a, $b) use ($defaultItems) {
                                        $la = isset($defaultItems[$a['stock']]) ? $defaultItems[$a['stock']] : $a['stock'];
                                        $lb = isset($defaultItems[$b['stock']]) ? $defaultItems[$b['stock']] : $b['stock'];

                                        $cmp = strcasecmp($la, $lb);
                                        if ($cmp !== 0) {
                                            return $cmp;
                                        }
                                        return strcasecmp($a['location'], $b['location']);
                                    });

                                    foreach ($rows as $row) {
                                        $receivedT = (int) $row['received_today'];
                                        if ($receivedT == 0) {
                                            continue; // only rows with today's received
                                        }

                                        $itemCode = $row['stock'];
                                        $itemLabel = isset($defaultItems[$itemCode]) ? $defaultItems[$itemCode] : $itemCode;

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . htmlspecialchars($itemLabel) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td>" . $receivedT . "</td>";
                                        echo "</tr>";
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Dispatched Today Panel -->
                    <div id="sentDetailsPanel" class="details-panel" style="display:none;">
                        <div class="section-title">
                            Total Dispatched (Today) – Detailed View (by Item &amp; Location)
                        </div>

                        <table id="example7" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Item / Product</th>
                                    <th>Location</th>
                                    <th>Dispatched Today</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                if (!empty($stockData)) {
                                    $rows = array_values($stockData);
                                    usort($rows, function ($a, $b) use ($defaultItems) {
                                        $la = isset($defaultItems[$a['stock']]) ? $defaultItems[$a['stock']] : $a['stock'];
                                        $lb = isset($defaultItems[$b['stock']]) ? $defaultItems[$b['stock']] : $b['stock'];

                                        $cmp = strcasecmp($la, $lb);
                                        if ($cmp !== 0) {
                                            return $cmp;
                                        }
                                        return strcasecmp($a['location'], $b['location']);
                                    });

                                    foreach ($rows as $row) {
                                        $sentT = (int) $row['sent_today'];
                                        if ($sentT == 0) {
                                            continue; // only rows with today's dispatched
                                        }

                                        $itemCode = $row['stock'];
                                        $itemLabel = isset($defaultItems[$itemCode]) ? $defaultItems[$itemCode] : $itemCode;

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . htmlspecialchars($itemLabel) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td>" . $sentT . "</td>";
                                        echo "</tr>";
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Available Now Panel -->
                    <div id="availableDetailsPanel" class="details-panel" style="display:none;">
                        <div class="section-title">
                            Available Stock (Now) – Detailed View (by Item &amp; Location)
                        </div>

                        <table id="example8" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Item / Product</th>
                                    <th>Location</th>
                                    <th>Available Stock</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                if (!empty($stockData)) {
                                    $rows = array_values($stockData);
                                    usort($rows, function ($a, $b) use ($defaultItems) {
                                        $la = isset($defaultItems[$a['stock']]) ? $defaultItems[$a['stock']] : $a['stock'];
                                        $lb = isset($defaultItems[$b['stock']]) ? $defaultItems[$b['stock']] : $b['stock'];

                                        $cmp = strcasecmp($la, $lb);
                                        if ($cmp !== 0) {
                                            return $cmp;
                                        }
                                        return strcasecmp($a['location'], $b['location']);
                                    });

                                    foreach ($rows as $row) {
                                        $opening = (int) $row['opening'];
                                        $receivedT = (int) $row['received_today'];
                                        $sentT = (int) $row['sent_today'];
                                        $available = $opening + $receivedT - $sentT;

                                        if ($available == 0) {
                                            continue; // only rows with some stock available / pending
                                        }

                                        $itemCode = $row['stock'];
                                        $itemLabel = isset($defaultItems[$itemCode]) ? $defaultItems[$itemCode] : $itemCode;

                                        $imgKey = $row['stock'] . '|' . $row['location'];
                                        $imgCell = '-';
                                        if (isset($imageMap[$imgKey]) && $imageMap[$imgKey] !== '') {
                                            $imgFile = htmlspecialchars($imageMap[$imgKey]);
                                            $imgCell = "<a class='btn btn-success btn-xs' target='_blank' href='StockManagement/" . $imgFile . "'>
                                                        <i style='color:#ffcf40' class='fa fa-file-o'></i> View
                                                    </a>";
                                        }

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . htmlspecialchars($itemLabel) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td>" . $available . "</td>";
                                        echo "<td class='text-center'>" . $imgCell . "</td>";
                                        echo "</tr>";
                                        $i++;
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

<!-- JS to toggle detail panels based on card clicked -->
<script>
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function ($) {

            function hideAllPanels() {
                $('#openingDetailsPanel, #receivedDetailsPanel, #sentDetailsPanel, #availableDetailsPanel').hide();
                $('#cardOpening, #cardReceived, #cardSent, #cardAvailable').removeClass('active');
            }

            function togglePanel(panelSelector, cardSelector) {
                var panel = $(panelSelector);
                var card = $(cardSelector);

                if (card.hasClass('active')) {
                    // if already active, hide everything
                    hideAllPanels();
                    return;
                }

                hideAllPanels();
                panel.slideDown(200);
                card.addClass('active');

                $('html, body').animate({
                    scrollTop: panel.offset().top - 80
                }, 300);
            }

            $('#cardOpening').on('click', function () {
                togglePanel('#openingDetailsPanel', '#cardOpening');
            });

            $('#cardReceived').on('click', function () {
                togglePanel('#receivedDetailsPanel', '#cardReceived');
            });

            $('#cardSent').on('click', function () {
                togglePanel('#sentDetailsPanel', '#cardSent');
            });

            $('#cardAvailable').on('click', function () {
                togglePanel('#availableDetailsPanel', '#cardAvailable');
            });
        });
    }
</script>
