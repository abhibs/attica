<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
$employeeId = $_SESSION['employeeId'];

if ($type == 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND branchId != 'AGPL000' AND ezviz_vc = '$employeeId'");
$branchList = [];
while ($row = mysqli_fetch_assoc($branchListQuery)) {
    $branchList[$row['branchId']] = $row['branchName'];
}

$employeeListQuery = mysqli_query($con, "SELECT id, empId, name, contact FROM employee WHERE designation != 'VM'");
$employeeList = [];
while ($row = mysqli_fetch_assoc($employeeListQuery)) {
    $employeeList[$row['empId']] = $row;
}

$usersQuery = mysqli_query($con, "SELECT username, employeeId FROM users WHERE type='Branch'");
$userList = [];
while ($row = mysqli_fetch_assoc($usersQuery)) {
    $userList[$row['username']] = $row['employeeId'];
}
?>

<style>
    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 18px;
        color: #123C69;
    }

    .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border: 1px solid #edf2f9;
        background-color: #f5f5f5;
        border-radius: 3px;
        padding: 20px;
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

    .btn-primary {
        background-color: #123C69;
    }

    .theadRow {
        text-transform: uppercase;
        background-color: #123C69 !important;
        color: #f2f2f2;
        font-size: 11px;
    }

    .btn-success {
        display: inline-block;
        padding: 0.7em 1.4em;
        margin: 0 0.3em 0.3em 0;
        border-radius: 0.15em;
        box-sizing: border-box;
        text-decoration: none;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        text-transform: uppercase;
        color: #fffafa;
        background-color: #123C69;
        box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
        text-align: center;
        position: relative;
    }

    .fa_Icon {
        color: #990000;
    }

    .table-responsive .row {
        margin: 0px;
    }
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="margin-top: 10px;">
                    <div class="table-responsive">
                        <table id="example3" class="table table-bordered">
                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
                                    <th>Branch Name</th>
                                    <th>Employee</th>
                                    <th>Walkin Count</th>
                                    <th>Bill Count</th>
                                    <th>Enquiry Count</th>
                                    <th>COG</th>
                                    <th>Bill Gross Weight</th>
                                    <th>Enquiry Gross Weight</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($branchList as $id => $name) {
                                    $empId = $userList[$id];
                                    $empData = $employeeList[$empId];
                                    $bm = $empData['empId'];



                                    $transQuery = mysqli_fetch_assoc(mysqli_query($con, "
                                        SELECT COUNT(*) AS bills, SUM(grossW) as TGW FROM trans
                                        WHERE date = CURDATE()
                                        AND flag='$bm' AND status='Approved' AND metal='Gold'
                                    "));

                                    $enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "
                                        SELECT COUNT(DISTINCT mobile) AS enquiry, SUM(gwt) as EGW
                                         FROM walkin
                                        WHERE date = CURDATE()
                                        AND emp_type='$bm' AND issue NOT IN ('Rejected') AND metal='Gold'
                                    "));

                                    $bills = $transQuery['bills'] ?? 0;
                                    $enquiry = $enquiryQuery['enquiry'] ?? 0;
                                    $TGW = $transQuery['TGW'] ?? 0;
                                    $EGW = $enquiryQuery['EGW'] ?? 0;
                                    $cog = ($bills == 0 && $enquiry == 0) ? 0 : round(($bills / ($bills + $enquiry)) * 100, 2);

                                    echo "<tr>";
                                    echo "<td>" . $i . "</td>";
                                    echo "<td>" . $name . " - (" . $id . ")</td>";
                                    echo "<td>" . $empData['name'] . " - (" . $empData['empId'] . ")</td>";
                                    echo "<td>" . $bills + $enquiry . "</td>";
                                    echo "<td>" . $bills . "</td>";
                                    echo "<td>" . $enquiry ."</td>";

                                    echo "<td>" . $cog . "%</td>";
                                    echo "<td>" . round($TGW , 2) . "</td>";

                                    echo "<td>" . round( $EGW, 2) . "</td>";

                                    echo "</tr>";

                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
