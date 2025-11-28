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



$date = date("Y-m-d");

$emp_id = $_SESSION['employeeId'] ?? '';



$msg = "";



/* ----------------------------------------

   STANDARD (DEFAULT) ITEM LIST

---------------------------------------- */

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

    'Silicon' => 'Silicon'

];



/* ----------------------------------------

   BASE ITEM → LOCATION MAP (defaults)

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

    "Silicon"                  => "Shivaji Nagar Warehouse"

];



/* ----------------------------------------

   LOAD CUSTOM / OTHER ITEMS FROM DB

---------------------------------------- */

$customItems = [];

$ciRes = mysqli_query(

    $con,

    "SELECT DISTINCT stock 

     FROM stock_received 

     WHERE stock IS NOT NULL AND stock <> '' 

     ORDER BY stock ASC"

);

while ($ciRow = mysqli_fetch_assoc($ciRes)) {

    $name = trim($ciRow['stock']);

    if ($name === '') continue;

    if (!isset($defaultItems[$name])) {

        $customItems[] = $name;

    }

}



/* ----------------------------------------

   ENRICH itemLocationMap FROM DB

   (for custom items and any items with stored location)

---------------------------------------- */

$locRes = mysqli_query(

    $con,

    "SELECT stock, location

     FROM stock_received

     WHERE stock IS NOT NULL AND stock <> ''

       AND location IS NOT NULL AND location <> ''

     ORDER BY id DESC"

);

while ($lr = mysqli_fetch_assoc($locRes)) {

    $s   = trim($lr['stock']);

    $loc = trim($lr['location']);

    if ($s === '' || $loc === '') continue;



    // If not already mapped, use last seen location from DB

    if (!isset($itemLocationMap[$s])) {

        $itemLocationMap[$s] = $loc;

    }

}



/* ------------- HANDLE RECEIVED STOCK SUBMIT ------------- */

if (isset($_POST['submitReceived'])) {



    // Get stock and possible "other" name

    $stock_raw   = trim($_POST['stock'] ?? '');

    $stock_other = trim($_POST['stock_other'] ?? '');



    // If user selected Others, use the custom name

    if ($stock_raw === 'Others') {

        $stock = $stock_other;

    } else {

        $stock = $stock_raw;

    }



    $standards   = trim($_POST['standards'] ?? '');

    $count       = trim($_POST['count'] ?? '');

    $location    = trim($_POST['location'] ?? '');

    $responsible = trim($_POST['responsible'] ?? '');

    $assign      = trim($_POST['assign'] ?? '');

    $vehicle     = trim($_POST['vehicle'] ?? '');

    $branch      = trim($_POST['branch'] ?? '');

    $image_name  = '';



    // Required field check

    if (

        $stock !== '' &&

        $count !== '' &&

        $location !== '' &&

        $responsible !== '' &&

        $assign !== '' &&

        $vehicle !== '' &&

        $branch !== ''

    ) {



        // File upload (photo)

        if (!empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

            $file      = $_FILES['file']['name'];

            $file_loc  = $_FILES['file']['tmp_name'];

            $file_size = $_FILES['file']['size'];

            $file_type = $_FILES['file']['type'];



            $file_extn     = substr($file, strrpos($file, '.') - 1); // includes dot

            $folder        = "StockManagement/";

            $new_file_name = strtolower($file);

            $filename      = date('Ymdhis');

            $final_file    = str_replace($new_file_name, $filename . 'stockmanagement' . $file_extn, $new_file_name);



            if (!is_dir($folder)) {

                @mkdir($folder, 0777, true);

            }



            if (move_uploaded_file($file_loc, $folder . $final_file)) {

                $image_name = $final_file;

            } else {

                $msg = "<div class='alert alert-danger'>File upload failed.</div>";

            }

        }



        // Insert only if no upload error

        if ($msg === '') {

            $insertSql = "

                INSERT INTO stock_received

                (stock, standards, count, location, responsible, assign, vehicle, branch, image, date)

                VALUES

                ('" . mysqli_real_escape_string($con, $stock) . "',

                 '" . mysqli_real_escape_string($con, $standards) . "',

                 '" . mysqli_real_escape_string($con, $count) . "',

                 '" . mysqli_real_escape_string($con, $location) . "',

                 '" . mysqli_real_escape_string($con, $responsible) . "',

                 '" . mysqli_real_escape_string($con, $assign) . "',

                 '" . mysqli_real_escape_string($con, $vehicle) . "',

                 '" . mysqli_real_escape_string($con, $branch) . "',

                 '" . mysqli_real_escape_string($con, $image_name) . "',

                 '" . mysqli_real_escape_string($con, $date) . "')

            ";

            if (mysqli_query($con, $insertSql)) {

                $msg = "<div class='alert alert-success'>Received stock details saved successfully.</div>";

            } else {

                $err = mysqli_error($con);

                $msg = "<div class='alert alert-danger'>Error inserting data: $err</div>";

            }

        }



    } else {

        $msg = "<div class='alert alert-warning'>Please fill all required fields.</div>";

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



    #stockItem {

        border-radius: 4px;

        border: 1px solid #cfd8dc;

        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

        transition: box-shadow 0.2s ease, border-color 0.2s ease;

    }



    #stockItem:focus {

        border-color: #123C69;

        box-shadow: 0 0 0 2px rgba(18, 60, 105, 0.14);

        outline: none;

    }



    #otherStockWrapper .form-control {

        border-radius: 4px;

        border: 1px solid #cfd8dc;

        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);

        transition: box-shadow 0.2s ease, border-color 0.2s ease;

    }



    #otherStockWrapper .form-control:focus {

        border-color: #ffa500;

        box-shadow: 0 0 0 2px rgba(255, 165, 0, 0.14);

    }



    #otherStockWrapper {

        display: none;

    }

</style>



<div id="wrapper">

    <div class="row content">

        <div class="col-lg-12">



            <div class="hpanel">

                <div class="panel-heading">

                    <h3>

                        <span style="color:#900" class="fa fa-calendar-check-o"></span>

                        Stock Management - Receive

                    </h3>

                </div>



                <div class="panel-body"

                    style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">



                    <?php

                    if ($msg != '') {

                        echo $msg;

                    }

                    ?>



                    <form method="POST" class="form-horizontal" action="stockAdd.php" enctype="multipart/form-data">



                        <!-- ITEM / PRODUCT + OTHER NAME + SPEC IN SINGLE ROW -->

                        <div class="col-sm-4">

                            <label class="text-success">Item / Product</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-briefcase"></span></span>

                                <select name="stock" id="stockItem" class="form-control" required>

                                    <option value="">Select Item / Product</option>



                                    <?php

                                    // Default items

                                    foreach ($defaultItems as $val => $label) {

                                        echo '<option value="' . htmlspecialchars($val) . '">' . htmlspecialchars($label) . '</option>';

                                    }



                                    // Custom items loaded from DB (just appended to list)

                                    foreach ($customItems as $cItem) {

                                        echo '<option value="' . htmlspecialchars($cItem) . '">' . htmlspecialchars($cItem) . '</option>';

                                    }

                                    ?>



                                    <option value="Others">Others (New Item)</option>

                                </select>

                            </div>

                        </div>



                        <div class="col-sm-4" id="otherStockWrapper">

                            <label class="text-success">Enter Item / Product Name</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-pencil"></span></span>

                                <input type="text" name="stock_other" id="stock_other" class="form-control"

                                    placeholder="Enter Item / Product Name">

                            </div>

                        </div>



                        <div class="col-sm-4">

                            <label class="text-success">Specification / Standards</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-user"></span></span>

                                <input type="text" name="standards" class="form-control"

                                    placeholder="Specification / Standards">

                            </div>

                        </div>



                        <!-- COUNT -->

                        <label class="col-sm-12 control-label"><br></label>

                        <div class="col-sm-4">

                            <label class="text-success">Received Count</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="count" class="form-control" required

                                    placeholder="Received Count">

                            </div>

                        </div>



                        <!-- LOCATION -->

                        <div class="col-sm-4">

                            <label class="text-success">Location (Store)</label>

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

                            <label class="text-success">Received By (Responsible)</label>

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



                        <label class="col-sm-12 control-label"><br></label>



                        <!-- ASSIGN -->

                        <div class="col-sm-4">

                            <label class="text-success">Received From (Agent)</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="assign" class="form-control" required

                                    placeholder="Branch / Person Sent From">

                            </div>

                        </div>



                        <!-- VEHICLE -->

                        <div class="col-sm-4">

                            <label class="text-success">Vehicle Number</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="vehicle" class="form-control" required

                                    placeholder="Enter Vehicle Number">

                            </div>

                        </div>



                        <!-- BRANCH -->

                        <div class="col-sm-4">

                            <label class="text-success">Vendor</label>

                            <div class="input-group">

                                <span class="input-group-addon"><span class="fa fa-id-card"></span></span>

                                <input type="text" name="branch" class="form-control" required

                                    placeholder="Enter Branch Name">

                            </div>

                        </div>



                        <label class="col-sm-12 control-label"><br></label>



                        <!-- FILE -->

                        <div class="col-sm-4">

                            <label class="text-success">Upload Photo</label>

                            <div class="input-group">

                                <span class="input-group-addon">

                                    <span style="color:#900" class="fa fa-file"></span>

                                </span>

                                <input type="file" style="background:#ffcf40" class="form-control" required name="file"

                                    id="file">

                            </div>

                        </div>



                        <label class="col-sm-12 control-label"><br></label>



                        <div class="col-sm-12" style="text-align:center">

                            <button class="btn btn-success" name="submitReceived" id="submitReceived" type="submit">

                                <span style="color:#ffcf40" class="fa fa-check"></span> Submit

                            </button>

                        </div>



                    </form>

                </div>

            </div>



            <!-- TABLE: TODAY'S RECEIVED STOCK -->

            <div class="hpanel">

                <div class="panel-body"

                    style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">

                    <table id="example5" class="table table-striped table-bordered table-hover">

                        <thead>

                            <tr>

                                <th><i class="fa fa-sort-numeric-asc"></i></th>

                                <th>Item / Product</th>

                                <th>Specification / Standards</th>

                                <th>Received Count</th>

                                <th>Location</th>

                                <th>Received By</th>

                                <th>Received From</th>

                                <th>Vehicle Number</th>

                                <th>Vendor</th>

                                <th>Upload Photo</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $i = 1;

                            $query = mysqli_query($con, "SELECT * FROM `stock_received` WHERE `date`='$date' ORDER BY id DESC");

                            while ($row = mysqli_fetch_assoc($query)) {

                                echo "<tr>";

                                echo "<td>" . $i . "</td>";

                                echo "<td>" . htmlspecialchars($row['stock']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['standards']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['count']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['location']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['responsible']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['assign']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['vehicle']) . "</td>";

                                echo "<td>" . htmlspecialchars($row['branch']) . "</td>";

                                if (!empty($row['image'])) {

                                    echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='StockManagement/" . htmlspecialchars($row['image']) . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Image</a></td>";

                                } else {

                                    echo "<td class='text-center'>-</td>";

                                }

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

    // Item → Location Mapping (PHP → JS, includes default + DB based)

    const itemLocationMap = <?php

        echo json_encode($itemLocationMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ?>;



    const stockSelect      = document.getElementById("stockItem");

    const otherStockWrap   = document.getElementById("otherStockWrapper");

    const otherStockInput  = document.getElementById("stock_other");

    const locationSelect   = document.getElementById("locationSelect");



    stockSelect.addEventListener("change", function () {

        const selectedItem = this.value;



        // Auto-fill location if mapping exists

        const location = itemLocationMap[selectedItem] || "";

        if (locationSelect && location !== "") {

            locationSelect.value = location;

        }



        // Show/hide "Other Item" input

        if (selectedItem === "Others") {

            otherStockWrap.style.display = "block";

            otherStockInput.required = true;

        } else {

            otherStockWrap.style.display = "none";

            otherStockInput.required = false;

            otherStockInput.value = "";

        }

    });

</script>


