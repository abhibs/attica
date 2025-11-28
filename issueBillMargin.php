<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'IssueHeadNew') {
    include("header.php");
    include("menuIssueHeadNew.php");
}
else {
    include("logout.php");
}
include("dbConnection.php");




if (isset($_GET['getReport'])) {
    $branch = $_GET['branch'];
    $input_date = $_GET['date'];
    $from = $input_date;
    $to = $input_date;

    $allowed_start_date = '2025-07-01';

    // Block previous dates
    if ($input_date < $allowed_start_date) {
        echo "<script>alert('Please select a date on or after 01/07/2025 only.');</script>";
        return;
    }
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
    (COALESCE(t.billed, 0) + COALESCE(w.enquiry, 0)) AS walkin,
    COALESCE(t.billed, 0) AS billed,
    COALESCE(w.enquiry, 0) AS enquiry,
    ROUND(t.gold_netW, 2) AS gold_netW,
    ROUND(w.enquiry_grossW, 2) AS enquiry_grossW,
    COALESCE(t.margin_0_05, 0) AS margin_0_05,
    COALESCE(t.margin_05_1, 0) AS margin_05_1,
    COALESCE(t.margin_1_2, 0) AS margin_1_2,
    COALESCE(t.margin_2_3, 0) AS margin_2_3

FROM
(
    SELECT 
        branchId, 
        COUNT(*) AS billed,
        SUM(CASE WHEN metal = 'Gold' THEN netW ELSE 0 END) AS gold_netW,

        SUM(CASE 
            WHEN CAST(grossA AS DECIMAL(10,2)) > 0 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 BETWEEN 0 AND 0.5 
            THEN 1 ELSE 0 
        END) AS margin_0_05,

        SUM(CASE 
            WHEN CAST(grossA AS DECIMAL(10,2)) > 0 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 > 0.5 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 <= 1 
            THEN 1 ELSE 0 
        END) AS margin_05_1,

        SUM(CASE 
            WHEN CAST(grossA AS DECIMAL(10,2)) > 0 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 > 1 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 <= 2 
            THEN 1 ELSE 0 
        END) AS margin_1_2,

        SUM(CASE 
            WHEN CAST(grossA AS DECIMAL(10,2)) > 0 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 > 2 
              AND (CAST(margin AS DECIMAL(10,2)) / CAST(grossA AS DECIMAL(10,2))) * 100 <= 3 
            THEN 1 ELSE 0 
        END) AS margin_2_3

    FROM trans
         WHERE date = '" . $input_date . "' AND status='Approved'
    GROUP BY branchId
) t

RIGHT JOIN branch b ON t.branchId = b.branchId

LEFT JOIN (
    SELECT 
        branchId, 
        COUNT(*) AS enquiry,
        SUM(gwt) AS enquiry_grossW
    FROM walkin

     WHERE date = '" . $input_date . "' 
      AND status != 'Rejected' 
      AND gwt != 0 
      AND metal = 'Gold'
    GROUP BY branchId
) w ON b.branchId = w.branchId

WHERE " . $state . "
ORDER BY billed DESC";



    $sql = mysqli_query($con, $query);
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
        border-radius: 3px;
        padding: 15px;
        background-color: #f5f5f5;
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
        font-size: 10px;
    }

    .btn-success {
        display: inline-block;
        padding: 0.7em 1.4em;
        margin: 0 0.3em 0.3em 0;
        border-radius: 0.15em;
        box-sizing: border-box;
        text-decoration: none;
        font-size: 11px;
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

    .modal-title {
        font-size: 13px;
        font-weight: 600;
        color: #123C69;
        text-transform: uppercase;
    }

    .table-responsive .row {
        margin: 0px;
    }
</style>

<div id="wrapper">
    <div class="row content">

        <div class="col-sm-4">
            <h3 class="text-success" style="margin-left: 10px;"> <i class="fa_Icon fa fa-money"></i> Overall Monthly
                Report</h3>
        </div>
        <form action="" method="GET">
            <div class="col-sm-3">
                <input list="branchList" class="form-control" name="branch" placeholder="City or State" required>
                <datalist id="branchList">
                    <?php if ($type == "Master" || $type == "Software" || $type == "ZonalMaster" || $type == "IssueHeadNew") { ?>
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
                    } else if ($type == "Zonal") {
                        if ($_SESSION['branchCode'] == 'AP-TS') {
                            echo '<option value="All_APT" label="All_APT"></option>';
                            echo '<option value="Hyderabad" label="Hyderabad"></option>';
                            echo '<option value="APT" label="APT"></option>';
                        } else if ($_SESSION['branchCode'] == 'KA') {
                            echo '<option value="All_Karnataka" label="All_Karnataka"></option>';
                            echo '<option value="Bengaluru" label="Bengaluru"></option>';
                            echo '<option value="Karnataka" label="Karnataka"></option>';
                        } else if ($_SESSION['branchCode'] == 'TN') {
                            echo '<option value="All_Tamilnadu" label="All_Tamilnadu"></option>';
                            echo '<option value="Chennai" label="Chennai"></option>';
                            echo '<option value="Tamilnadu" label="Tamilnadu"></option>';
                        }
                    } else if ($type == "SubZonal") {
                        if ($_SESSION['branchCode'] == 'AP-TS') {
                            echo '<option value="All_APT" label="All_APT"></option>';
                            echo '<option value="Hyderabad" label="Hyderabad"></option>';
                            echo '<option value="APT" label="APT"></option>';
                        } else if ($_SESSION['branchCode'] == 'KA') {
                            echo '<option value="All_Karnataka" label="All_Karnataka"></option>';
                            echo '<option value="Bengaluru" label="Bengaluru"></option>';
                            echo '<option value="Karnataka" label="Karnataka"></option>';
                        } else if ($_SESSION['branchCode'] == 'TN') {
                            echo '<option value="All_Tamilnadu" label="All_Tamilnadu"></option>';
                            echo '<option value="Chennai" label="Chennai"></option>';
                            echo '<option value="Tamilnadu" label="Tamilnadu"></option>';
                        }
                    }
                    ?>
                </datalist>
            </div>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="date" class="form-control" name="date" min="2025-07-01" required>
                </div>
            </div>
            <div class="col-sm-1">
                <button class="btn btn-success btn-block" name="getReport"><span style="color:#ffcf40"
                        class="fa fa-table"></span> Get</button>
            </div>
        </form>

        <div class="col-lg-12">
            <div class="hpanel">

                <div class="panel-body">
                    <div class="col-sm-12 table-responsive">
                        <table id="reportTable" class="table table-bordered">
                            <caption>
                                <b><?php if (isset($_GET['getReport'])) {
                                    echo $branch . ", " . $from . " - " . $to;
                                } ?></b>
                            </caption>
                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
                                    <th>Branch</th>
                                    <th>Walkin</th>
                                    <th>Billed</th>
                                    <th>Enquiry</th>
                                    <th>Gold_NetW</th>
                                    <th>grossW</th>
                                    <th>Margin (0 to 0.5 %)</th>
                                    <th>Margin (0.5 to 1 %)</th>
                                    <th>Margin (1 to 2 %)</th>
                                    <th>Margin (2 to 3 %)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($_GET['getReport'])) {
                                    $walkin = 0;
                                    $billed = 0;
                                    $billed_day = 0;
                                    $gold_netw = 0;
                                    $silver_netw = 0;

                                    $enquiry = 0;
                                    $enq_day = 0;
                                    $physical = 0;
                                    $release = 0;
                                    $enq_grossw = 0;

                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($sql)) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . $row['branchName'] . "</td>";
                                        echo "<td>" . $row['walkin'] . "</td>";
                                        echo "<td>" . $row['billed'] . "</td>";
                                        echo "<td>" . $row['enquiry'] . "</td>";
                                        echo "<td>" . $row['gold_netW'] . "</td>";
                                        echo "<td>" . $row['enquiry_grossW'] . "</td>";
                                        echo "<td>" . $row['margin_0_05'] . "</td>";
                                        echo "<td>" . $row['margin_05_1'] . "</td>";
                                        echo "<td>" . $row['margin_1_2'] . "</td>";
                                        echo "<td>" . $row['margin_2_3'] . "</td>";
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
    <?php include("footer.php"); ?>
    <script>
        $('#reportTable').DataTable({
            paging: false,
            "ajax": '',
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
            buttons: [
                { extend: 'copy', className: 'btn-sm' },
                { extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
                { extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
                { extend: 'print', className: 'btn-sm' }
            ]
        });
    </script>
