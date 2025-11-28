<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];

if ($type == 'StockManager') {
    include("header.php");
    include("menuStockManage.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

$date = date("Y-m-d");
$emp_id = $_SESSION['employeeId'];
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
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">

            <div class="hpanel">
                <div class="panel-heading">
                    <h3><span style="color:#900" class="fa fa-calendar-check-o"></span> Stock Management</h3>
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
                                    <option value="Aluminium-Clamps-Pack">Aluminium Clamps Pack</option>
                                    <option value="Brown-Corner-Beeding">Brown Corner Beeding</option>
                                    <option value="Brown-PVC-Panel">Brown PVC Panel</option>
                                    <option value="Brown-wall-Panel">Brown wall Panel</option>
                                    <option value="Cash-Counting-Machine">Cash Counting Machine</option>
                                    <option value="Electrical-miscellaneous">Electrical miscellaneous</option>
                                    <option value="Floor-Mat(Local)">Floor Mat(Local)</option>
                                    <option value="Floor-Mat(Premium)">Floor Mat(Premium)</option>
                                    <option value="Karat-Machine">Karat Machine</option>
                                    <option value="LED-Roof(Warranty)">LED Roof(Warranty)</option>
                                    <option value="LED-Roof(Without Warranty)">LED Roof(Without Warranty)</option>
                                    <option value="Printer-Machine">Printer Machine</option>
                                    <option value="Red-Fevicol-Box">Red Fevicol Box</option>
                                    <option value="System">System</option>
                                    <option value="UPS/Battery">UPS/Battery</option>
                                    <option value="Weighing-Scale">Weighing Scale</option>
                                    <option value="White-PVC-Panel">White PVC Panel</option>
                                    <option value="White-Corner-Beeding">White Corner Beeding</option>
                                    <option value="Electrical-Item">Electrical Item</option>
                                    <option value="MDF-short-table">MDF short table</option>
                                    <option value="MDF-long-table">MDF long table</option>
                                    <option value="MDF-support-small-box">MDF support small box</option>
                                    <option value="White-Beeding(1*40)">White Beeding (1*40)</option>
				    <option value="Brown-Beeding(1*40)">Brown Beeding (1*40)</option>
       				    <option value="Gold-Wall-Panel">Gold Wall Panel</option>
                                    <option value="Silicon">Silicon</option>
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
                                <input type="text" name="count" class="form-control" required placeholder="Item Count">
                            </div>
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
                                    <!--<option value="Imran">Imran</option>
                                    <option value="Nayakaru">Nayakaru</option>-->
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
                            $query = mysqli_query($con, "SELECT * FROM `stockitem` WHERE `date`='$date'");
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
    // Item → Location Mapping
    const itemLocationMap = {
        "Aluminium-Clamps-Pack": "Shivaji Nagar Warehouse",
        "Brown-Corner-Beeding": "Shivaji Nagar Warehouse",
        "Brown-PVC-Panel": "Shivaji Nagar Warehouse",
        "Brown-wall-Panel": "Shivaji Nagar Warehouse",
        "Cash-Counting-Machine": "Queens Road Warehouse",
        "Electrical-miscellaneous": "Queens Road Warehouse",
        "Floor-Mat(Local)": "Shivaji Nagar Warehouse",
        "Floor-Mat(Premium)": "Shivaji Nagar Warehouse",
        "Karat-Machine": "Queens Road Warehouse",
        "LED-Roof(Warranty)": "Shivaji Nagar Warehouse",
        "LED-Roof(Without Warranty)": "Shivaji Nagar Warehouse",
        "Printer-Machine": "Queens Road Warehouse",
        "Red-Fevicol-Box": "Shivaji Nagar Warehouse",
        "System": "Queens Road Warehouse",
        "UPS/Battery": "Shivaji Nagar Warehouse",
        "Weighing-Scale": "Queens Road Warehouse",
        "White-PVC-Panel": "Shivaji Nagar Warehouse",
        "White-Corner-Beeding": "Shivaji Nagar Warehouse",
        "Electrical-Item": "HO",
        "MDF-short-table": "Shivaji Nagar Warehouse",
        "MDF-long-table": "Shivaji Nagar Warehouse",
        "MDF-support-small-box": "Shivaji Nagar Warehouse",
        "White-Beeding(1*40)": "Shivaji Nagar Warehouse",
	"Brown-Beeding(1*40)": "Shivaji Nagar Warehouse",
	"Gold-Wall-Panel": "Shivaji Nagar Warehouse",
        "Silicon": "Shivaji Nagar Warehouse"
    };

    // Auto-fill Location
    document.getElementById("stockItem").addEventListener("change", function () {
        const selectedItem = this.value;
        const location = itemLocationMap[selectedItem] || "";
        document.getElementById("locationSelect").value = location;
    });
</script>

