<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");
$row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM stock"));
?>
<style>
    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 16px;
        color: #123C69;
    }

    #wrapper .panel-body {
        border: 5px solid #fff;
        padding: 15px;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
        background-color: #f5f5f5;
        border-radius: 3px;
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

    tbody {
        font-weight: 600;
    }

    .trInput:focus-within {
        outline: 3px solid #990000;
    }

    .fa {
        color: #34495e;
        font-size: 16px;
    }

    .btn {
        background-color: transparent;
    }
</style>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <h3 class="text-success"><span class="fa fa-pencil-square" style="color:#990000"></span><b> Stock
                            Details</b></h3>
                </div>
                <div class="panel-body">
                    <table id="user" class="table table-bordered table-striped" style="clear: both">
                        <tbody>
                   
                            <input type="hidden" name="id" id="id" class="form-control"
                                value="<?php echo $row['id']; ?>" readonly>
                    
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Aluminium Clamps Pack</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Aluminium-Clamps-Pack" class="form-control"
                                            value="<?php echo $row['Aluminium-Clamps-Pack']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Brown Corner Beeding</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Brown-Corner-Beeding" class="form-control"
                                            value="<?php echo $row['Brown-Corner-Beeding']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Brown PVC Panel</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Brown-PVC-Panel" class="form-control"
                                            value="<?php echo $row['Brown-PVC-Panel']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Brown wall Panel</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Brown-wall-Panel" class="form-control"
                                            value="<?php echo $row['Brown-wall-Panel']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Cash Counting Machine</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Cash-Counting-Machine" class="form-control"
                                            value="<?php echo $row['Cash-Counting-Machine']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Electrical miscellaneous
                                </th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Electrical-miscellaneous" class="form-control"
                                            value="<?php echo $row['Electrical-miscellaneous']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Floor Mat(Local)</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Floor-Mat(Local)" class="form-control"
                                            value="<?php echo $row['Floor-Mat(Local)']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Floor Mat(Premium)</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Floor-Mat(Premium)" class="form-control"
                                            value="<?php echo $row['Floor-Mat(Premium)']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Karat Machine</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Karat-Machine" class="form-control"
                                            value="<?php echo $row['Karat-Machine']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">LED Roof(Warranty)</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="LED-Roof(Warranty)" class="form-control"
                                            value="<?php echo $row['LED-Roof(Warranty)']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">LED Roof(Without Warranty)
                                </th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="LED-Roof(Without Warranty)" class="form-control"
                                            value="<?php echo $row['LED-Roof(Without Warranty)']; ?>"
                                            autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Printer Machine</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Printer-Machine" class="form-control"
                                            value="<?php echo $row['Printer-Machine']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Red Fevicol Box</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Red-Fevicol-Box" class="form-control"
                                            value="<?php echo $row['Red-Fevicol-Box']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">System</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="System" class="form-control"
                                            value="<?php echo $row['System']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">UPS/Battery</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="UPS/Battery" class="form-control"
                                            value="<?php echo $row['UPS/Battery']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Weighing Scale</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Weighing-Scale" class="form-control"
                                            value="<?php echo $row['Weighing-Scale']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">White PVC Panel</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="White-PVC-Panel" class="form-control"
                                            value="<?php echo $row['White-PVC-Panel']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">White Corner Beeding</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="White-Corner-Beeding" class="form-control"
                                            value="<?php echo $row['White-Corner-Beeding']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
			    </tr>
	<tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Electrical Item</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Electrical-Item" class="form-control"
                                            value="<?php echo $row['Electrical-Item']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">MDF short table</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="MDF-short-table" class="form-control"
                                            value="<?php echo $row['MDF-short-table']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">MDF long table</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="MDF-long-table" class="form-control"
                                            value="<?php echo $row['MDF-long-table']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">MDF support small box</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="MDF-support-small-box" class="form-control"
                                            value="<?php echo $row['MDF-support-small-box']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">White Beeding (1*40)</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="White-Beeding(1*40)" class="form-control"
                                            value="<?php echo $row['White-Beeding(1*40)']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Brown Beeding (1*40)</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Brown-Beeding(1*40)" class="form-control"
                                            value="<?php echo $row['Brown-Beeding(1*40)']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
			    </tr>
			    <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Gold Wall Panel</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Gold-Wall-Panel" class="form-control"
                                            value="<?php echo $row['Gold-Wall-Panel']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Silicon</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Silicon" class="form-control"
                                            value="<?php echo $row['Silicon']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateStockData(this)"><i
                                                    class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>

                        </tbody>               
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
    <script>
        let id = document.getElementById('id').value;

        function updateStockData(button) {
            let colValue = button.parentNode.previousElementSibling.value,
                colName = button.parentNode.previousElementSibling.name;
            $.ajax({
                url: "editAjax.php",
                type: "POST",
                data: {
                    editStock: 'editStock',
                    id: id,
                    colName: colName,
                    colValue: colValue
                },
                success: function (e) {
                    if (e == '1') {
                        alert('Successfully Updated');
                    } else {
                        alert('Successfully Updated');
                    }
                }
            });
        }
    </script>
    <?php include("footer.php"); ?>
