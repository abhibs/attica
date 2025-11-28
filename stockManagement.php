<?php

session_start();

error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];



if ($type == 'StockManager') {

    include("header.php");

    include("menuStockManage.php");

} else {

    include("logout.php");

    exit;

}



include("dbConnection.php");



$date   = date("Y-m-d");

$emp_id = $_SESSION['employeeId'] ?? '';



/* ----------------------------------------

   1) STANDARD (DEFAULT) ITEM LIST

---------------------------------------- */

$defaultItems = [

    'Aluminium-Clamps-Pack'   => 'Aluminium Clamps Pack',

    'Brown-Corner-Beeding'    => 'Brown Corner Beeding',

    'Brown-PVC-Panel'         => 'Brown PVC Panel',

    'Brown-wall-Panel'        => 'Brown wall Panel',

    'Cash-Counting-Machine'   => 'Cash Counting Machine',

    'Electrical-miscellaneous'=> 'Electrical miscellaneous',

    'Floor-Mat(Local)'        => 'Floor Mat(Local)',

    'Floor-Mat(Premium)'      => 'Floor Mat(Premium)',

    'Karat-Machine'           => 'Karat Machine',

    'LED-Roof(Warranty)'      => 'LED Roof(Warranty)',

    'LED-Roof(Without Warranty)' => 'LED Roof(Without Warranty)',

    'Printer-Machine'         => 'Printer Machine',

    'Red-Fevicol-Box'         => 'Red Fevicol Box',

    'System'                  => 'System',

    'UPS/Battery'             => 'UPS/Battery',

    'Weighing-Scale'          => 'Weighing Scale',

    'White-PVC-Panel'         => 'White PVC Panel',

    'White-Corner-Beeding'    => 'White Corner Beeding',

    'Electrical-Item'         => 'Electrical Item',

    'MDF-short-table'         => 'MDF short table',

    'MDF-long-table'          => 'MDF long table',

    'MDF-support-small-box'   => 'MDF support small box',

    'White-Beeding(1*40)'     => 'White Beeding (1*40)',

    'Brown-Beeding(1*40)'     => 'Brown Beeding (1*40)',

    'Gold-Wall-Panel'         => 'Gold Wall Panel',

    'Silicon'                 => 'Silicon',

];



/* ----------------------------------------

   2) BASE ITEM → LOCATION MAP (defaults)

---------------------------------------- */

$itemLocationMap = [

    "Aluminium-Clamps-Pack"    => "Shivaji Nagar Warehouse",

    "Brown-Corner-Beeding"     => "Shivaji Nagar Warehouse",

    "Brown-PVC-Panel"          => "Shivaji Nagar Warehouse",

    "Brown-wall-Panel"         => "Shivaji Nagar Warehouse",

    "Cash-Counting-Machine"    => "Queens Road Warehouse",

    "Electrical-miscellaneous" => "Queens Road Warehouse",

    "Floor-Mat(Local)"         => "Shivaji Nagar Warehouse",

    "Floor-Mat(Premium)"       => "Shivaji Nagar Warehouse",

    "Karat-Machine"            => "Queens Road Warehouse",

    "LED-Roof(Warranty)"       => "Shivaji Nagar Warehouse",

    "LED-Roof(Without Warranty)" => "Shivaji Nagar Warehouse",

    "Printer-Machine"          => "Queens Road Warehouse",

    "Red-Fevicol-Box"          => "Shivaji Nagar Warehouse",

    "System"                   => "Queens Road Warehouse",

    "UPS/Battery"              => "Shivaji Nagar Warehouse",

    "Weighing-Scale"           => "Queens Road Warehouse",

    "White-PVC-Panel"          => "Shivaji Nagar Warehouse",

    "White-Corner-Beeding"     => "Shivaji Nagar Warehouse",

    "Electrical-Item"          => "HO",

    "MDF-short-table"          => "Shivaji Nagar Warehouse",

    "MDF-long-table"           => "Shivaji Nagar Warehouse",

    "MDF-support-small-box"    => "Shivaji Nagar Warehouse",

    "White-Beeding(1*40)"      => "Shivaji Nagar Warehouse",

    "Brown-Beeding(1*40)"      => "Shivaji Nagar Warehouse",

    "Gold-Wall-Panel"          => "Shivaji Nagar Warehouse",

    "Silicon"                  => "Shivaji Nagar Warehouse",

];



/* ----------------------------------------

   3) LOAD CUSTOM / NEW ITEMS + LOCATION

      (from stock_received)

---------------------------------------- */

$customItems = [];

$locRes = mysqli_query(

    $con,

    "SELECT sr.stock, sr.location

     FROM stock_received sr

     WHERE sr.stock IS NOT NULL AND sr.stock <> ''

       AND sr.location IS NOT NULL AND sr.location <> ''

     ORDER BY sr.id DESC"

);



while ($lr = mysqli_fetch_assoc($locRes)) {

    $s   = trim($lr['stock']);

    $loc = trim($lr['location']);

    if ($s === '' || $loc === '') continue;



    if (!isset($defaultItems[$s]) && !in_array($s, $customItems, true)) {

        $customItems[] = $s;

    }

    if (!isset($itemLocationMap[$s])) {

        $itemLocationMap[$s] = $loc;

    }

}



/* ----------------------------------------

   4) BUILD AVAILABLE STOCK MAP (by stock+location)

---------------------------------------- */

$stockData = []; // key: stock|location



// RECEIVED

$receivedQuery = mysqli_query(

    $con,

    "SELECT stock, location, SUM(`count`) AS total_received

     FROM stock_received

     GROUP BY stock, location"

);

while ($row = mysqli_fetch_assoc($receivedQuery)) {

    $stock    = $row['stock'];

    $location = $row['location'];

    $key      = $stock . '|' . $location;



    $stockData[$key] = [

        'stock'    => $stock,

        'location' => $location,

        'received' => (int)$row['total_received'],

        'sent'     => 0

    ];

}



// SENT / DISPATCHED

$sentQuery = mysqli_query(

    $con,

    "SELECT stock, location, SUM(`count`) AS total_sent

     FROM stockitem

     GROUP BY stock, location"

);

while ($row = mysqli_fetch_assoc($sentQuery)) {

    $stock    = $row['stock'];

    $location = $row['location'];

    $key      = $stock . '|' . $location;

    $sentCount = (int)$row['total_sent'];



    if (isset($stockData[$key])) {

        $stockData[$key]['sent'] = $sentCount;

    } else {

        $stockData[$key] = [

            'stock'    => $stock,

            'location' => $location,

            'received' => 0,

            'sent'     => $sentCount

        ];

    }

}



// AVAILABLE MAP + "where stock exists" per item

$availableMap            = [];             // "stock|location" -> qty

$itemLocationsWithStock  = [];             // "stock" -> [loc1, loc2,...]



foreach ($stockData as $k => $v) {

    $available = (int)$v['received'] - (int)$v['sent'];

    if ($available < 0) $available = 0;



    $availableMap[$k] = $available;



    if ($available > 0) {

        $s   = $v['stock'];

        $loc = $v['location'];

        if (!isset($itemLocationsWithStock[$s])) {

            $itemLocationsWithStock[$s] = [];

        }

        if (!in_array($loc, $itemLocationsWithStock[$s], true)) {

            $itemLocationsWithStock[$s][] = $loc;

        }

    }

}

?>

<style>

    #wrapper { background: #f5f5f5; }

    #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 20px; color: #123C69; }

    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color: #fffafa; }

    .text-success { color: #123C69; text-transform: uppercase; font-weight: bold; font-size: 12px; }

    .fa_Icon { color: #ffa500; }

    thead { text-transform: uppercase; background-color: #123C69; }

    thead tr { color: #f2f2f2; font-size: 12px; }

    .dataTables_empty { text-align: center; font-weight: 600; font-size: 12px; text-transform: uppercase; }

    .btn-success {

        display: inline-block; padding: 0.7em 1.4em; margin: 0 0.3em 0.3em 0;

        border-radius: 0.15em; box-sizing: border-box; text-decoration: none; font-size: 12px;

        color: #fffafa; background-color: #123C69;

        box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17); text-align: center;

    }

    .available-hint {

        margin-top: 4px;

        font-size: 11px;

        color: #123C69;

        font-weight: 600;

    }

</style>



<div id="wrapper">

    <div class="row content">

        <div class="col-lg-12">



            <div class="hpanel">

                <div class="panel-heading">

                    <h3><span style="color:#900" class="fa fa-calendar-check-o"></span> Stock Management - Dispatch </h3>

                </div>



                <div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">

                    <form method="POST" class="form-horizontal" action="xsubmit.php" enctype="multipart/form-data">



                        <!-- ITEM / PRODUCT -->

                        <div class="col-sm-4">

                            <label class="text-success">Item / Product</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-briefcase"></span></span>

                                <select name="stock" id="stockItem" class="form-control" required>

                                    <option value="">Item / Product</option>

                                    <?php

                                    foreach ($defaultItems as $value => $label) {

                                        echo '<option value="'.htmlspecialchars($value).'">'.htmlspecialchars($label).'</option>';

                                    }

                                    foreach ($customItems as $cItem) {

                                        echo '<option value="'.htmlspecialchars($cItem).'">'.htmlspecialchars($cItem).'</option>';

                                    }

                                    ?>

                                </select>

                            </div>

                        </div>



                        <!-- SPEC -->

                        <div class="col-sm-4">

                            <label class="text-success">Specification / Standards</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-user"></span></span>

                                <input type="text" name="standards" class="form-control"  placeholder="Specification / Standards">

                            </div>

                        </div>



                        <!-- COUNT -->

                        <div class="col-sm-4">

                            <label class="text-success">Item Count</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="number" min="1" name="count" id="itemCount" class="form-control" required placeholder="Item Count">

                            </div>

                            <div id="availableHint" class="available-hint"></div>

                        </div>



                        <label class="col-sm-12 control-label"><br></label>



                        <!-- LOCATION -->

                        <div class="col-sm-4">

                            <label class="text-success">Location</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-briefcase"></span></span>

                                <select name="location" id="locationSelect" class="form-control" required>

                                    <option value="">Choose Storage Location</option>

                                    <option value="HO">HO</option>

                                    <option value="Queens Road Warehouse">Queens Road Warehouse</option>

                                    <option value="Shivaji Nagar Warehouse">Shivaji Nagar Warehouse</option>

                                </select>

                            </div>

                        </div>



                        <!-- RESPONSIBLE -->

                        <div class="col-sm-4">

                            <label class="text-success">Responsible</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-briefcase"></span></span>

                                <select name="responsible" class="form-control" required>

                                    <option value="">Choose Responsible Person</option>

                                    <option value="Lakshmi">Lakshmi</option>

                                    <option value="Ambu">Ambu</option>

                                    <option value="Syed">Syed</option>

                                </select>

                            </div>

                        </div>



                        <!-- ASSIGN -->

                        <div class="col-sm-4">

                            <label class="text-success">Assign To</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="assign" class="form-control" required placeholder="Enter Assign Person">

                            </div>

                        </div>



                        <label class="col-sm-12 control-label"><br></label>



                        <!-- VEHICLE -->

                        <div class="col-sm-4">

                            <label class="text-success">Vehicle Number</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="vehicle" class="form-control" required placeholder="Enter Vehicle Number">

                            </div>

                        </div>



                        <!-- BRANCH -->

                        <div class="col-sm-4">

                            <label class="text-success">Branch Name</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="branch" class="form-control" required placeholder="Enter Branch Name">

                            </div>

                        </div>



                        <!-- FILE -->

                        <div class="col-sm-4">

                            <label class="text-success">Upload Photo</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span style="color:#900" class="fa fa-file"></span></span>

                                <input type="file" style="background:#ffcf40" class="form-control" required name="file" id="file">

                            </div>

                        </div>



                        <label class="col-sm-12 control-label"><br></label>



                        <div class="col-sm-12" style="text-align:center">

                            <button class="btn btn-success" name="submitStock" id="submitStock" type="submit">

                                <span style="color:#ffcf40" class="fa fa-check"></span> Submit

                            </button>

                        </div>



                    </form>

                </div>

            </div>



            <!-- TABLE -->

            <div class="hpanel">

                <div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">

                    <table id="example5" class="table table-striped table-bordered table-hover">

                        <thead>

                            <tr>

                                <th><i class="fa fa-sort-numeric-asc"></i></th>

                                <th>Item / Product</th>

                                <th>Specification / Standards</th>

                                <th>Item Count</th>

                                <th>Location</th>

                                <th>Responsible</th>

                                <th>Assign To</th>

                                <th>Vehicle Number</th>

                                <th>Branch</th>

                                <th>Upload Photo</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $i = 1;

                            $query = mysqli_query(

                                $con,

                                "SELECT * FROM `stockitem`

                                 WHERE `date`='$date'

                                 ORDER BY `id` DESC"

                            );

                            while ($row = mysqli_fetch_assoc($query)) {

                                echo "<tr>";

                                echo "<td>" . $i . "</td>";

                                echo "<td>" . $row['stock'] . "</td>";

                                echo "<td>" . $row['standards'] . "</td>";

                                echo "<td>" . $row['count'] . "</td>";

                                echo "<td>" . $row['location'] . "</td>";

                                echo "<td>" . $row['responsible'] . "</td>";

                                echo "<td>" . $row['assign'] . "</td>";

                                echo "<td>" . $row['vehicle'] . "</td>";

                                echo "<td>" . $row['branch'] . "</td>";

                                echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='StockManagement/" . $row['image'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Image</a></td>";

                                echo "</tr>";

                                $i++;

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



<script>

    // PHP → JS mappings

    const itemLocationMap = <?php

        echo json_encode($itemLocationMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ?>;



    const availableMap = <?php

        echo json_encode($availableMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ?>;



    const itemLocationsWithStock = <?php

        echo json_encode($itemLocationsWithStock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ?>;



    const stockSelect    = document.getElementById("stockItem");

    const locationSelect = document.getElementById("locationSelect");

    const countInput     = document.getElementById("itemCount");

    const availableHint  = document.getElementById("availableHint");



    // 1. Keep default mapping, but switch only if no stock at default location

    function updateLocationFromItem() {

        const selectedItem = stockSelect.value;



        // Step 1: set default location from static map

        const defaultLoc = itemLocationMap[selectedItem] || "";

        if (defaultLoc) {

            locationSelect.value = defaultLoc;

        } else {

            locationSelect.value = "";

        }



        // Step 2: check stock at default location

        let chosenLoc = locationSelect.value;

        let keyDefault = selectedItem + '|' + chosenLoc;

        let availableDefault = null;



        if (availableMap.hasOwnProperty(keyDefault)) {

            availableDefault = parseInt(availableMap[keyDefault]);

            if (isNaN(availableDefault)) availableDefault = null;

        }



        // If no / zero stock at default location, switch to some location that has stock

        if (availableDefault === null || availableDefault <= 0) {

            const locList = itemLocationsWithStock[selectedItem];

            if (Array.isArray(locList) && locList.length > 0) {

                // Pick first location with stock

                locationSelect.value = locList[0];

            }

        }

    }



    function updateAvailableInfo() {

        const stock    = stockSelect.value;

        const location = locationSelect.value;

        const key      = stock + '|' + location;



        const available = (availableMap.hasOwnProperty(key))

            ? parseInt(availableMap[key])

            : null;



        if (available !== null && !isNaN(available)) {

            availableHint.textContent = "Available stock at this location: " + available;

            countInput.max = available;

        } else {

            availableHint.textContent = "";

            countInput.removeAttribute("max");

        }

    }



    // Restrict input > available

    countInput.addEventListener("input", function () {

        const max = parseInt(countInput.max);

        let val   = parseInt(countInput.value);

        if (!isNaN(max) && !isNaN(val) && val > max) {

            alert("You cannot dispatch more than available stock: " + max);

            countInput.value = max;

        }

        if (val <= 0 || isNaN(val)) {

            countInput.value = "";

        }

    });



    stockSelect.addEventListener("change", function () {

        updateLocationFromItem();

        updateAvailableInfo();

    });



    locationSelect.addEventListener("change", function () {

        updateAvailableInfo();

    });

</script>


