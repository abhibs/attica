<?php
session_start();
$type = $_SESSION['usertype'] ?? '';

if ($type !== 'ZonalMaster') {
    include("logout.php");
    exit;
}

include("header.php");
include("menuzonalMaster.php");
include("dbConnection.php");

// Helper to escape output safely
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Build the query (today only)
$sql = "
    SELECT 
        b.branchId,
        b.branchName,
        COUNT(*) AS bill,
        ROUND(SUM(t.grossW), 2) AS grossW
    FROM trans t
    JOIN branch b 
      ON b.branchId = t.branchId
    WHERE t.sta = ''
      AND t.status = 'Approved'
      AND t.metal = 'Gold'
      AND b.status = 1
      AND b.branchId NOT IN ('AGPL000','AGPL999')
    GROUP BY b.branchId
    ORDER BY b.branchId
";

$result = mysqli_query($con, $sql);

if (!$result) {
    $sql_error = mysqli_error($con);
    echo "SQL Error: " . $sql_error;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Branch Details</title>

    <style>
        #wrapper h3 {
            text-transform: uppercase;
            font-weight: 600;
            font-size: 20px;
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

        tfoot tr td {
            font-weight: 600;
            background: #fafafa;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <div class="row content">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        </div>
                        <h3><b><i class="fa_Icon fa fa-institution"></i> Branch Details &nbsp;</b></h3>
                    </div>
                    <div class="panel-body">

                        <?php
                        // Show query error (if any)
                        if ($result === false) {
                            echo "<div class='alert alert-danger'>Query error: " . h($sql_error ?? 'Unknown error') . "</div>";
                        }
                        ?>

                        <div class="table-responsive">
                            <table id="example2" class="table table-striped table-bordered table-hover">
                                <thead class="theadRow">
                                    <tr>
                                        <th>#</th>
                                        <th>Branch_ID</th>
                                        <th>Branch Name</th>
                                        <th>Packets</th>
                                        <th>Gold In Branch (gms)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $totalBills = 0;
                                    $totalGross = 0.0;

                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $branchId = $row['branchId'];
                                            $branchName = $row['branchName'];
                                            $bill     = (int)$row['bill'];
                                            $grossW   = (float)($row['grossW'] ?? 0);

                                            $totalBills += $bill;
                                            $totalGross += $grossW;

                                            echo "<tr>";
                                            echo "<td>" . $i++ . "</td>";
                                            echo "<td>" . h($branchId) . "</td>";
                                            echo "<td>" . h($branchName) . "</td>";
                                            echo "<td>" . h($bill) . "</td>";
                                            echo "<td>" . number_format($grossW, 2) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No records found for today.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-center">Total</td>
                                        <td><?php echo h($totalBills); ?></td>
                                        <td><?php echo number_format($totalGross, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>



                    </div>
                </div>
            </div>
        </div>
        <?php include("footer.php"); ?>
    </div>


</body>

</html>
