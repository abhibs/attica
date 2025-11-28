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
} else if ($type == 'ZonalMaster' || $type == 'Zonal') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

// date filter
if (isset($_POST['submitWalkinData'])) {
    $date = $_POST['fromDate'];
} else {
    $date = date('Y-m-d');
}

// fetch all walkin data
$walkin = mysqli_query($con, "
    SELECT w.*, b.branchName, b.city, b.state, t.phone, e.name AS zonal_name
    FROM walkin w
    LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
    LEFT JOIN branch b ON w.branchId = b.branchId
    LEFT JOIN employee e ON b.ezviz_vc = e.empId
    WHERE w.date='$date'
    ORDER BY w.id DESC
");
$result = mysqli_fetch_all($walkin, MYSQLI_ASSOC);
$totalLength = count($result);

// fetch distinct zonal names
$zonals = mysqli_query($con, "
    SELECT DISTINCT e.name AS zonal_name
    FROM branch b
    LEFT JOIN employee e ON b.ezviz_vc = e.empId
    WHERE e.name IS NOT NULL
    ORDER BY e.name
");
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
        position: relative;
        transition: all .3s ease;
    }

    .tab .nav-tabs li.active a,
    .tab .nav-tabs li a:hover {
        color: #fff;
        background: #123C69;
        border: none;
        border-bottom: 3px solid #ffa500;
        border-radius: 3px;
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
        padding: .7em 1.4em;
        font-size: 12px;
        color: #fff;
        background: #123C69;
    }

    .text-success {
        font-weight: 600;
        color: #123C69;
    }

    .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border-radius: 3px;
        padding: 15px;
        background: #f5f5f5;
    }


    /* Tabs wrapper reset */
    .tab .nav-tabs {
        padding: 0;
        margin: 0;
        border: none;
        display: flex;
        flex-wrap: wrap;
    }

    /* Each tab item */
    .tab .nav-tabs li {
        list-style: none;
        margin: 0 4px 0 0;
    }

    /* Tab links default (inactive) */
    .tab .nav-tabs li a {
        display: inline-block;
        color: #123C69;
        background: #E3E3E3;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 7px 12px;
        border: none;
        border-radius: 3px 3px 0 0;
        transition: all .3s ease;
    }

    /* Active tab link */
    .tab .nav-tabs li.active a {
        background-color: #123C69 !important;
        /* dark blue background */
        color: #fff !important;
        /* white text */
        border: none !important;
        border-radius: 3px 3px 0 0;
        box-shadow: inset 0 -3px 0 #ffa500;
        /* orange underline inside tab */
    }

    /* Hover effect */
    .tab .nav-tabs li a:hover {
        background-color: #345f8c;
        color: #fff;
    }

    /* Kill Bootstrap's unwanted active block */
    .tab .nav-tabs li.active,
    .tab .nav-tabs li.active a,
    .tab .nav-tabs li a:focus {
        outline: none !important;
        border: none !important;
        background-image: none !important;
        box-shadow: none !important;
    }
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="col-sm-8">
                        <h3>
                            <i class="fa fa-edit"></i>
                            ENQUIRY REPORT
                            <span style="color:#990000"><?php echo " - " . $date; ?></span>
                        </h3>
                    </div>
                    <div class="col-sm-3" style="margin-top:5px;">
                        <form action="" method="POST">
                            <div class="input-group">
                                <input name="fromDate" value="" type="date" class="form-control" required />
                                <span class="input-group-btn">
                                    <input name="submitWalkinData" class="btn btn-primary btn-block" value="Search"
                                        type="submit">
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-1" style="margin-top:5px;">
                        <form action="export.php" method="post">
                            <input type="hidden" name="walkin_date" value="<?php echo $date; ?>">
                            <input name="exportWalkinData" class="btn btn-primary btn-block" value="Export"
                                type="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nav tabs -->
        <div class="col-lg-12">
            <div class="hpanel" style="margin-top:15px;">
                <div class="tab" role="tabpanel">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                        $z = 0;
                        $zonalList = [];
                        while ($zonal = mysqli_fetch_assoc($zonals)) {
                            $zname = trim($zonal['zonal_name']);
                            if (!empty($zname) && !in_array($zname, $zonalList)) {
                                $zonalList[] = $zname;
                                $active = (count($zonalList) == 1) ? "active" : "";
                                echo "<li class='$active'><a href='#zonal_" . md5($zname) . "' role='tab' data-toggle='tab'>" . $zname . "</a></li>";
                            }
                        }

                        ?>
                    </ul>

                    <div class="tab-content tabs">
                        <?php
                        $z = 0;
                        foreach ($zonalList as $zname) {
                            $active = ($z == 0) ? "in active" : "";
                            $zoneId = md5($zname);
                            ?>
                            <div role="tabpanel" class="tab-pane fade <?php echo $active; ?>"
                                id="zonal_<?php echo $zoneId; ?>">
                                <div class="panel-body">
                                    <div class="col-sm-12 table-responsive">
                                        <table class="table table-bordered">
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
                                                    <th><span class="fa fa-edit"></span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                                foreach ($result as $row) {
                                                    if ($row['zonal_name'] == $zname && $row['issue'] !== "Rejected") {
                                                        echo ($row['bills'] > 0) ? "<tr style='color:red'>" : "<tr>";
                                                        echo "<td>" . $i . "</td>";
                                                        echo "<td>" . $row['branchName'] . "</td>";
                                                        echo "<td>" . $row['zonal_name'] . "</td>";
                                                        echo "<td>" . $row['name'] . "</td>";
                                                        echo "<td>" . $row['mobile'] . "</td>";
                                                        echo "<td>" . $row['gold'] . "</td>";
                                                        echo "<td>" . $row['havingG'] . "</td>";
                                                        echo "<td>" . $row['metal'] . "</td>";
                                                        echo "<td>" . $row['gwt'] . "</td>";
                                                        echo "<td>" . $row['ramt'] . "</td>";
                                                        echo "<td>" . $row['quot_rate'] . "</td>";
                                                        echo "<td>" . $row['remarks'] . "</td>";
                                                        echo "<td>" . $row['zonal_remarks'] . "</td>";
                                                        echo "<td>" . $row['issue'] . "</td>";
                                                        echo "<td>" . $row['comment'] . "</td>";
                                                        echo "<td>" . $row['agent_id'] . "</td>";
                                                        echo "<td>" . $row['bills'] . "</td>";
                                                        echo "<td>" . $row['date'] . "</td>";
                                                        echo "<td>" . $row['time'] . "</td>";
                                                        echo "<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>";
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
                            <?php $z++;
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
