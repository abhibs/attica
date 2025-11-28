<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
if ($type == 'ApprovalTeam') {
    include("header.php");
    include("menuapproval.php");
} else {
    include("logout.php");
}
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");
$date = date('Y-m-d');

// Walking
$Walking = mysqli_fetch_assoc(mysqli_query($con, "SELECT DISTINCT(contact),COUNT(*) AS waiting FROM `everycustomer` WHERE date=CURRENT_DATE AND  status ='0' AND branch IN (SELECT branchId from branch WHERE city='Bengaluru')"));
// Billed
$Billed = mysqli_fetch_assoc(mysqli_query($con, "SELECT DISTINCT(contact),COUNT(*) AS billed FROM `everycustomer` WHERE date=CURRENT_DATE AND  status IN ('Billed','Release') AND branch IN (SELECT branchId from branch WHERE city='Bengaluru')"));
// Enq
$Enq = mysqli_fetch_assoc(mysqli_query($con, "SELECT DISTINCT(contact),COUNT(*) AS enq FROM `everycustomer` WHERE date=CURRENT_DATE AND  status IN ('Enquiry') AND branch IN (SELECT branchId from branch WHERE city='Bengaluru')"));


$waiting = $Walking['waiting'];
$billed = $Billed['billed'];
$enq = $Enq['enq'];
$overall = $waiting + $billed + $enq;


?>
<style>
    #wrapper {
        background-color: #e6e6fa;
    }

    .box {
        padding: 10px;
        transition: .2s all;
    }

    .box-wrap:hover .box {
        transform: scale(.98);
        box-shadow: none;
    }

    .box-wrap:hover .box:hover {
        filter: blur(0px);
        transform: scale(1.05);
        box-shadow: 0 8px 20px 0px #b8860b;
    }

    .hpanel {
        margin-bottom: 5px;
        border-radius: 10px;
    }

    .text-success {
        color: #123C69;
        text-transform: uppercase;
        font-size: 20px;
    }

    .stats-label {
        text-transform: uppercase;
        font-size: 10px;
    }

    .list-item-container h3 {
        font-size: 14px;
    }

    .panel-footer {
        border-radius: 0px 0px 10px 10px;
    }
</style>

<meta http-equiv="refresh" content="300">
<div id="wrapper" style="top:50px">
    <div class="content animate-panel">
        <div class="row m-t-md">

            <!--  KARNATAKA BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="border-radius: 10px 10px 0px 0px;">
                        <div style="color:#990000;" align="center">
                            <h5 class=""><b>Overall Customer</b></h5>
                            <h1 class="font-extra-bold"><?php echo $overall;   ?></h1>
                        </div>
                    </div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b>
                    </div>
                </div>
            </div>


            <!--  KARNATAKA 2 BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="border-radius: 10px 10px 0px 0px;">
                        <div style="color:#990000;" align="center">
                            <h5 class=""><b>Total Waiting Customer</b></h5>
                            <h1 class="font-extra-bold"><?php echo $waiting;   ?></h3>
                        </div>
                    </div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b>
                    </div>
                </div>
            </div>

            <!--  TAMILNADU BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="border-radius: 10px 10px 0px 0px;">
                        <div style="color:#990000;" align="center">
                            <h5 class=""><b>Total Billed Customer</b></h5>
                            <h1 class="font-extra-bold"><?php echo $billed;   ?></h3>
                        </div>
                    </div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b>
                    </div>
                </div>
            </div>

            <!--  AP/T BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="border-radius: 10px 10px 0px 0px;">
                        <div style="color:#990000;" align="center">
                            <h5 class=""><b>Total Enquiry Customer</b></h5>

                            <h1 class="font-extra-bold "><?php echo $enq;   ?></h3>
                        </div>
                    </div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-t-md">
            <!--  KARNATAKA BILLS  -->
            <div class="col-lg-12">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="border-radius: 10px 10px 0px 0px;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center"><span class="fa fa-sort-numeric-asc"></span></th>
                                    <th class="text-center">Branch Name</th>
                                    <!-- <th class="text-center">Overall</th> -->
                                    <th class="text-center">Waiting</th>
                                    <!-- <th class="text-center">Billed</th>
                                    <th class="text-center">Enquiry</th> -->
                                    <th class="text-center"><span class="fa fa-edit"></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $query = mysqli_query($con, "SELECT branchName, (SELECT COUNT(*) FROM `everycustomer` WHERE branch=b.branchId AND date='$date' AND status ='0' AND status !='Wrong Entry' GROUP by branchId) as waiting FROM `branch` b WHERE status = 1 AND city ='Bengaluru' ORDER BY `waiting` DESC");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    // $overal = $row['waiting'] + $row['billed'] + $row['enq'];
                                    echo "<tr>";
                                    echo "<td class='text-center'>" . $i . "</td>";
                                    echo "<td><b> " . $row['branchName'] . "</b></td>";
                                    // echo "<td class='text-center'><b>" . $overal . "</td>";
                                    echo "<td class='text-center'><b>" . round($row['waiting']) . "</b></td>";
                                    // echo "<td class='text-center'><b>" . round($row['billed']) . "</b></td>";
                                    // echo "<td class='text-center'><b>" . round($row['enq']) . "</b></td>";
                                    echo "<td class='text-center'><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>";
                                    echo "</tr>";
                                    $i++;
                                }
                                ?>
                            </tbody>
                            <div style="clear:both"></div>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include("footer.php");  ?>
    </div>
</div>