<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
$employeeId = $_SESSION['employeeId'];

if ($type == 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else if ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

if (isset($_GET['getReport'])) {
    $branch = $_GET['branch'];

    $state = "";
    switch ($branch) {
        case 'All_Karnataka':
            $state = "b.state IN ('Karnataka') AND b.status=1 AND b.branchId!='AGPL000'";
            break;
        case 'All_APT':
            $state = "b.state IN ('Telangana', 'Andhra Pradesh') AND b.status=1";
            break;
        case 'All_Tamilnadu':
            $state = "b.state IN ('Tamilnadu', 'Pondicherry') AND b.status=1";
            break;

        case "Bengaluru":
            $state = " b.city = 'Bengaluru' AND b.status=1 AND b.branchId!='AGPL000'";
            break;
        case "Karnataka":
            $state = "b.city != 'Bengaluru' AND state IN ('Karnataka') AND b.status=1";
            break;

        case "Chennai":
            $state = " b.city = 'Chennai' AND b.status=1";
            break;
        case "Tamilnadu":
            $state = "b.city != 'Chennai' AND state IN ('Tamilnadu', 'Pondicherry') AND b.status=1";
            break;

        case "Hyderabad":
            $state = " b.city = 'Hyderabad' AND b.status=1";
            break;
        case "APT":
            $state = "b.city != 'Hyderabad' AND state IN ('Telangana', 'Andhra Pradesh') AND b.status=1";
            break;

        case 'All_Branches':
            $state = "b.status=1";
            break;
    }
    $query = "SELECT 
        b.branchName,
        (COALESCE(billed, 0) + COALESCE(enquiry, 0)) as walkin,
        COALESCE(billed, 0) AS billed,
        COALESCE(ROUND(((COALESCE(billed, 0)/(COALESCE(billed, 0) + COALESCE(enquiry, 0))) * 100),2), 0) as billed_cog,
        ROUND(gold_netW, 2) AS gold_netW,
        ROUND(silver_netW, 2) AS silver_netW,
        COALESCE(enquiry, 0) AS enquiry,
        COALESCE(enquiry_physical, 0) AS enquiry_physical,
        COALESCE(enquiry_release, 0) AS enquiry_release,
        ROUND(COALESCE(enquiry_grossW, 0), 2) AS enquiry_grossW,
        ROUND(((COALESCE(enquiry, 0)/(COALESCE(billed, 0) + COALESCE(enquiry, 0))) * 100),2) as enquiry_cog
    FROM
        (SELECT branchId, COUNT(*) AS billed,
            SUM(CASE WHEN metal='Gold' THEN netW ELSE 0 END) as gold_netW,
            SUM(CASE WHEN metal='Silver' THEN netW ELSE 0 END) as silver_netW
        FROM trans
        WHERE date = CURDATE() AND status='Approved'
        GROUP BY branchId) t
    RIGHT JOIN branch b ON t.branchId = b.branchId
    LEFT JOIN
        (SELECT branchId, COUNT(*) AS enquiry,
            SUM(CASE WHEN gold='physical' THEN 1 ELSE 0 END) AS enquiry_physical,
            SUM(CASE WHEN gold='release' THEN 1 ELSE 0 END) AS enquiry_release,
            SUM(gwt) as enquiry_grossW
        FROM walkin
        WHERE date = CURDATE() AND status!='Rejected' AND issue!='Rejected' AND gwt != 0 AND metal='Gold'
        GROUP BY branchId) w
    ON b.branchId = w.branchId
    WHERE $state
    ORDER BY billed_cog DESC";

    $sql = mysqli_query($con, $query);
}

$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND branchId != 'AGPL000'");
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
        <div class="col-sm-4">
            <h3 class="text-success" style="margin-left: 10px;"> <i class="fa_Icon fa fa-money"></i> BM COG REPORTS</h3>
        </div>
        <div class="col-sm-4"></div>

        <form action="" method="GET">
            <div class="col-sm-3">
                <input list="branchList" class="form-control" name="branch" placeholder="City or State" required>
                <datalist id="branchList">
                    <?php if ($type == "Master" || $type == "Software" || $type == "ZonalMaster") { ?>
                        <option value="All_Karnataka" label="All_Karnataka"></option>
                        <option value="All_APT" label="All_APT"></option>
                        <option value="All_Tamilnadu" label="All_Tamilnadu"></option>
                        <option value="Bengaluru" label="Bengaluru"></option>
                        <option value="Karnataka" label="Karnataka"></option>
                        <option value="Chennai" label="Chennai"></option>
                        <option value="Tamilnadu" label="Tamilnadu"></option>
                        <option value="Hyderabad" label="Hyderabad"></option>
                        <option value="APT" label="APT"></option>
                        <option value="All_Branches" label="All_Branches"></option>
                    <?php
                    }
                    ?>
                </datalist>
            </div>

            <div class="col-sm-1">
                <button class="btn btn-success btn-block" name="getReport"><span style="color:#ffcf40" class="fa fa-table"></span> Get</button>
            </div>
        </form>

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="margin-top: 10px;">
                    <div class="table-responsive">
                        <table id="example3" class="table table-bordered">
                            <caption><b><?php if (isset($_GET['getReport'])) {
                                            echo $branch . ", Today";
                                        } ?></b></caption>

                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
                                    <th>Branch Name</th>
                                    <th>Employee</th>
                                    <th>Bill Count</th>
                                    <th>Enquiry Count</th>
                                    <th>Walkin Count</th>
                                    <th>COG</th>
                                    <th>Bill Gross Weight</th>
                                    <th>Enquiry Gross Weight</th>

                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $i = 1;

                                if (isset($_GET['getReport']) && isset($sql)) {
                                    while ($row = mysqli_fetch_assoc($sql)) {
                                        echo "<tr>";
                                        echo "<td>" . $i++ . "</td>";
                                        echo "<td>" . $row['branchName'] . "</td>";

                                        $branchName = $row['branchName'];
                                        $branchId = array_search($branchName, $branchList);

                                        if ($branchId && isset($userList[$branchId])) {
                                            $empId = $userList[$branchId];
                                            $empData = $employeeList[$empId];
                                            echo "<td>" . $empData['name'] . " - (" . $empData['empId'] . ")</td>";
                                        } else {
                                            echo "<td>-</td>";
                                        }

                                        echo "<td>" . $row['billed'] . "</td>";
                                        echo "<td>" . $row['enquiry'] . "</td>";
                                        echo "<td>" . $row['walkin'] . "</td>";
                                        echo "<td>" . $row['billed_cog'] . "%</td>";
                                        echo "<td>" . $row['gold_netW'] . "</td>";
                                        echo "<td>" . $row['enquiry_grossW'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {

                                    foreach ($branchList as $id => $name) {
                                        $empId = $userList[$id];
                                        $empData = $employeeList[$empId];
                                        $bm = $empData['empId'];

                                        $transQuery = mysqli_fetch_assoc(mysqli_query($con, "
                                        SELECT COUNT(*) AS bills, SUM(grossW) as TGW FROM trans
                                        WHERE date = '2025-07-28'
                                        AND flag='$bm' AND status='Approved'
                                    "));

                                        $enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "
                                        SELECT COUNT(DISTINCT mobile) AS enquiry, SUM(gwt) as EGW
                                        FROM walkin
                                        WHERE date = CURDATE()
                                        AND emp_type='$bm' AND issue NOT IN ('Rejected')
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
                                        echo "<td>" . $bills . "</td>";
                                        echo "<td>" . $enquiry . "</td>";
                                        echo "<td>" . ($bills + $enquiry) . "</td>";
                                        echo "<td>" . $cog . "%</td>";
                                        echo "<td>" . $TGW . "</td>";
                                        echo "<td>" . $EGW . "</td>";
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
    </div>
</div>

<?php include("footer.php"); ?>
