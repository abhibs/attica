<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} else if ($type == 'AccHead') {
    include("header.php");
    include("menuaccHeadPage.php");
} else if ($type == 'IssueHead') {
    include("header.php");
    include("menuIssueHead.php");
} else if ($type == 'Call Centre') {
    include("header.php");
    include("menuCall.php");
} else if ($type == 'Issuecall') {
    include("header.php");
    include("menuissues.php");
} else if ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

// today’s date
$today = isset($_POST['fromDate']) ? $_POST['fromDate'] : date('Y-m-d');

// Zonal wise enquiry count
$zonalEnquiry = mysqli_query($con, "
    SELECT
        COALESCE(e.name, CONCAT('No Zone (', z.ezviz_vc, ')')) AS zoneName,
        COALESCE(COUNT(DISTINCT w.id), 0) AS enquiryCount
    FROM (
        SELECT '1000211' AS ezviz_vc UNION ALL
        SELECT '1000036' UNION ALL
	SELECT '1000336' UNION ALL
	SELECT '1005678' UNION ALL
        SELECT '1000423' UNION ALL
        SELECT '1002063' UNION ALL
        SELECT '1001627' UNION ALL
        SELECT '1000735'
    ) AS z
    LEFT JOIN employee e
        ON e.empId = z.ezviz_vc
    LEFT JOIN branch b
        ON b.ezviz_vc = z.ezviz_vc
    LEFT JOIN walkin w
        ON w.branchId = b.branchId
        AND DATE(w.date) = '$today'
        AND w.issue <> 'Rejected'
    GROUP BY z.ezviz_vc, e.name
    ORDER BY enquiryCount DESC, zoneName ASC
");

// Store zonal counts
$zonal_counts = [];
while ($row = mysqli_fetch_assoc($zonalEnquiry)) {
    $zonal_counts[$row['zoneName']] = $row['enquiryCount'];
}

// Fetch walkin data
$walkin = mysqli_query($con, "
    SELECT w.*, b.branchName, b.city, b.state, t.phone, e.name AS zonal_name
    FROM walkin w
    LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$today' AND t.status='Approved')
    LEFT JOIN branch b ON w.branchId = b.branchId
    LEFT JOIN employee e ON w.ezviz_vc = e.empId
    WHERE w.date='$today' AND gwt != 0
    ORDER BY w.id DESC
");
$result = mysqli_fetch_all($walkin, MYSQLI_ASSOC);
?>

<style>
    .tab .nav-tabs {
        padding: 0;
        margin: 0;
        border: none;
    }
    

    .tab .nav-tabs li a {
        color: #123C69;
        background: #E3E3E3;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 7px 10px 6px;
        margin: 5px 5px 0 0;
        border: none;
        border-bottom: 3px solid #123C69;
        border-radius: 0;
        transition: all 0.3s ease 0.1s;
    }

    .tab .nav-tabs li.active a,
    .tab .nav-tabs li a:hover {
        color: #f2f2f2;
        background: #123C69;
        border: none;
        border-bottom: 3px solid #ffa500;
        border-radius: 3px;
    }

    .tab-content h4 {
        color: #123C69;
        font-weight: 500;
    }

    #wrapper {
        background: #E3E3E3;
    }

    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 20px;
        color: #123C69;
    }

    thead {
        text-transform: uppercase;
        background: #123C69;
        font-size: 10px;
    }

    thead tr {
        color: #f2f2f2;
    }

    .btn-primary {
        font-size: 12px;
        background: #123C69;
        color: #fffafa;
    }

    .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border-radius: 3px;
        padding: 15px;
        background: #f5f5f5;
    }

    /* ✅ Prevent footer overlap */
    .dataTables_wrapper {
        margin-bottom: 30px;
        /* enough space so footer never overlaps pagination */
    }

    footer {
        clear: both;
        position: relative;
        z-index: 1;
        display: none;
    }
    
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="col-sm-8">
                        <h3><i class="fa fa-edit"></i> ENQUIRY REPORT <span style="color:#990000"> -
                                <?php echo $today; ?></span></h3>
                    </div>
                    <div class="col-sm-3" style="margin-top:5px;">
                        <form action="" method="POST">
                            <div class="input-group">
                                <input name="fromDate" type="date" class="form-control" required />
                                <span class="input-group-btn">
                                    <input name="submitWalkinData" class="btn btn-primary btn-block" value="Search"
                                        type="submit">
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-1" style="margin-top:5px;">
                        <form action="export.php" method="post">
                            <input type="hidden" name="walkin_date" value="<?php echo $today; ?>">
                            <input name="exportWalkinData" class="btn btn-primary btn-block" value="Export"
                                type="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="col-lg-12">
            <div class="hpanel" style="margin-top:15px;">
                <div class="tab" role="tabpanel">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                        $z = 0;
                        foreach ($zonal_counts as $zoneName => $count) {
                            $active = ($z == 0) ? "active" : "";
                            echo "<li class='$active'><a href='#zone_" . md5($zoneName) . "' role='tab' data-toggle='tab'>$zoneName</a></li>";
                            $z++;
                        }
                        ?>
                        <li><a href="#billed" role="tab" data-toggle="tab" class="all-billed">Billed</a></li>
                        <li><a href="#Rejected" role="tab" data-toggle="tab">Rejected</a></li>
                    </ul>

                    <div class="tab-content tabs">
                        <?php
                        $z = 0;
                        foreach ($zonal_counts as $zoneName => $count) {
                            $active = ($z == 0) ? "in active" : "";
                            $zoneId = md5($zoneName);
                            ?>
                            <!-- Zonal Tab -->
                            <div role="tabpanel" class="tab-pane fade <?php echo $active; ?>"
                                id="zone_<?php echo $zoneId; ?>">
                                <div class="panel-body">
                               
                                    <div class="col-sm-12 table-responsive">
                                        <table id="table_<?php echo $zoneId; ?>" class="table table-bordered zonal-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Branch</th>
                                                    <th>Zonal Name</th>
                                                    <th>Name</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Having Gold</th>
                                                    <th>Metal</th>
                                                    <th>GrossW</th>
                                                    <th>ReleaseA</th>
                                                    <th>Rate</th>
                                                    <th>Branch Remark</th>
                                                    <th>Zonal Remark</th>
                                                    <th>Disposition</th>
                                                    <th>CSR Remark</th>
                                                    <th>Agent Name</th>
                                                    <th>Bills</th>
                                                    <th>ECR</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Edit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                                foreach ($result as $row) {
                                                    if ($row['zonal_name'] == $zoneName && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']) {
                                                        echo ($row['bills'] > 0) ? "<tr style='color:red'>" : "<tr>";
                                                        echo "<td>" . $i . "</td>
                                <td>" . $row['branchName'] . "</td>
                                <td>" . $row['zonal_name'] . "</td>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['mobile'] . "</td>
                                <td>" . $row['gold'] . "</td>
                                <td>" . $row['havingG'] . "</td>
                                <td>" . $row['metal'] . "</td>
                                <td>" . $row['gwt'] . "</td>
                                <td>" . $row['ramt'] . "</td>
                                <td>" . $row['quot_rate'] . "</td>
                                <td>" . $row['remarks'] . "</td>
                                <td>" . $row['zonal_remarks'] . "</td>
                                <td>" . $row['issue'] . "</td>
                                <td>" . $row['comment'] . "</td>
                                <td>" . $row['agent_id'] . "</td>
                                <td>" . $row['bills'] . "</td>
                                <td>" . $row['ecr'] . "</td>
                                <td>" . $row['date'] . "</td>
                                <td>" . $row['time'] . "</td>
                                <td><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></td>
                              </tr>";
                                                        $i++;
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php $z++;
                        } ?>

                        <!-- Billed Tab -->
                        <div role="tabpanel" class="tab-pane fade" id="billed">
                            <div class="panel-body">
                                <div class="col-sm-12 table-responsive">
                                    <table id="billed_table" class="table table-bordered zonal-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Branch</th>
                                                <th>Zonal Name</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Type</th>
                                                <th>Having Gold</th>
                                                <th>Metal</th>
                                                <th>GrossW</th>
                                                <th>ReleaseA</th>
                                                <th>Rate</th>
                                                <th>Branch Remark</th>
                                                <th>Zonal Remark</th>
                                                <th>Disposition</th>
                                                <th>CSR Remark</th>
                                                <th>Agent Name</th>
                                                <th>Bills</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            foreach ($result as $row) {
                                                if ($row['mobile'] == $row['phone']) {
                                                    echo "<tr class='billed' data-region='" . $row['zonal_name'] . "' style='visibility:visible'>";
                                                    echo "<td>" . $i . "</td>
                                <td>" . $row['branchName'] . "</td>
                                <td>" . $row['zonal_name'] . "</td>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['mobile'] . "</td>
                                <td>" . $row['gold'] . "</td>
                                <td>" . $row['havingG'] . "</td>
                                <td>" . $row['metal'] . "</td>
                                <td>" . $row['gwt'] . "</td>
                                <td>" . $row['ramt'] . "</td>
                                <td>" . $row['quot_rate'] . "</td>
                                <td>" . $row['remarks'] . "</td>
                                <td>" . $row['zonal_remarks'] . "</td>
                                <td>" . $row['issue'] . "</td>
                                <td>" . $row['comment'] . "</td>
                                <td>" . $row['agent_id'] . "</td>
                                <td>" . $row['bills'] . "</td>
                                <td>" . $row['date'] . "</td>
                                <td>" . $row['time'] . "</td>
                                <td><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></td>
                              </tr>";
                                                    $i++;
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Rejected Tab -->
                        <div role="tabpanel" class="tab-pane fade" id="Rejected">
                            <div class="panel-body">
                                <div class="col-sm-12 table-responsive">
                                    <table id="rejected_table" class="table table-bordered zonal-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Branch</th>
                                                <th>Zonal Name</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Type</th>
                                                <th>Having Gold</th>
                                                <th>Metal</th>
                                                <th>GrossW</th>
                                                <th>ReleaseA</th>
                                                <th>Rate</th>
                                                <th>Branch Remark</th>
                                                <th>Zonal Remark</th>
                                                <th>Disposition</th>
                                                <th>CSR Remark</th>
                                                <th>Agent Name</th>
                                                <th>Bills</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            foreach ($result as $row) {
                                                if ($row['issue'] == "Rejected") {
                                                    echo "<tr>";
                                                    echo "<td>" . $i . "</td>
                                <td>" . $row['branchName'] . "</td>
                                <td>" . $row['zonal_name'] . "</td>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['mobile'] . "</td>
                                <td>" . $row['gold'] . "</td>
                                <td>" . $row['havingG'] . "</td>
                                <td>" . $row['metal'] . "</td>
                                <td>" . $row['gwt'] . "</td>
                                <td>" . $row['ramt'] . "</td>
                                <td>" . $row['quot_rate'] . "</td>
                                <td>" . $row['remarks'] . "</td>
                                <td>" . $row['zonal_remarks'] . "</td>
                                <td>" . $row['issue'] . "</td>
                                <td>" . $row['comment'] . "</td>
                                <td>" . $row['agent_id'] . "</td>
                                <td>" . $row['bills'] . "</td>
                                <td>" . $row['date'] . "</td>
                                <td>" . $row['time'] . "</td>
                                <td><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></td>
                              </tr>";
                                                    $i++;
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div><!-- tab-content -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("footer.php"); ?>
<script>
    $(document).ready(function () {
        // Apply DataTable to ALL tables with class "zonal-table"
        var tables = $('.zonal-table').DataTable({
            paging: true,
            "pageLength": 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
            buttons: [
                { extend: 'copy', className: 'btn-sm' },
                { extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
                { extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
                { extend: 'print', className: 'btn-sm' }
            ]
        });

        // ✅ Ensure proper redraw when switching tabs
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
        });
    });
</script>


