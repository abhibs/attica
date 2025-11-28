<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

if (isset($_POST['submitWalkinData'])) {
    $date = $_POST['fromDate'];
} else {
    $date = date('Y-m-d');
}

$sql = "";
$sql = "SELECT * FROM everycustomer WHERE date='$date'";

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
        margin: 5px 5px 0px 0px;
        border: none;
        border-bottom: 3px solid #123C69;
        border-radius: 0;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease 0.1s;
    }

    .tab .nav-tabs li.active a,
    .tab .nav-tabs li a:hover,
    .tab .nav-tabs li.active a:hover {
        color: #f2f2f2;
        background: #123C69;
        border: none;
        border-bottom: 3px solid #ffa500;
        font-weight: 600;
        border-radius: 3px;
    }

    .tab .nav-tabs li a:before {
        content: "";
        background: #E3E3E3;
        height: 100%;
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
        z-index: -1;
        transition: clip-path 0.3s ease 0s, height 0.3s ease 0.2s;
        clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
    }

    .tab .nav-tabs li.active a:before,
    .tab .nav-tabs li a:hover:before {
        height: 0;
        clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
    }

    .tab-content h4 {
        color: #123C69;
        font-weight: 500;
    }

    @media only screen and (max-width: 479px) {
        .tab .nav-tabs {
            padding: 0;
            margin: 0 0 15px;
        }

        .tab .nav-tabs li {
            width: 100%;
            text-align: center;
        }

        .tab .nav-tabs li a {
            margin: 0 0 5px;
        }
    }

    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 20px;
        color: #123C69;
    }

    thead {
        text-transform: uppercase;
        background-color: #123C69;
        font-size: 10px;
    }

    thead tr {
        color: #f2f2f2;
    }

    .btn-primary {
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

    .text-success {
        font-weight: 600;
        color: #123C69;
    }

    .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border-radius: 3px;
        padding: 15px;
        background-color: #f5f5f5;
    }

    .table_td_waiting {
        color: #990000;
    }

    .table_td_reg {
        color: #840bde;
    }

    .table_td_external_link {
        color: #123C69;
        font-size: 17px;
    }

    .table-responsive .row {
        margin: 0px;
    }
</style>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="col-sm-9">
                        <h3><i class="fa fa-users" style='color:#990000'></i> Wrong Entry Report
                            <span style='color:#990000'><?php echo " - " . $date; ?></span>
                        </h3>
                    </div>
                    <div class="col-sm-3" style="margin-top: 5px;">
                        <form action="" method="POST">
                            <div class="input-group">
                                <input name="fromDate" value="" type="date" class=" form-control" required />
                                <span class="input-group-btn">
                                    <input name="submitWalkinData" class="btn btn-primary btn-block" value="Search" type="submit" style="font-size: 11px;">
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row content">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-heading">
                            <div class="panel-tools">
                                <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="example2" class="table table-striped table-bordered table-hover">
                                    <thead class="theadRow">
                                        <tr>
                                            <th>SL</th>
                                            <th>Branch ID</th>
                                            <th>Branch Name</th>
                                            <th>Zonal Name</th>
                                            <th>Customer Name</th>
                                            <th>Contact</th>
                                            <th>Type</th>
					    <th>Remark1</th>
				  		<th>Remark2</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = mysqli_query($con, "
	SELECT 
		ec.*, 
		b.branchId, 
		b.branchName, 
		e.name AS zonalName
	FROM everycustomer ec
	JOIN branch b ON ec.branch = b.branchId
	LEFT JOIN employee e ON b.ezviz_vc = e.empId
	WHERE ec.date = '$date' AND ec.status = 'Wrong Entry'
");

                                        while ($row = mysqli_fetch_assoc($sql)) {
                                            echo "<tr>";
                                            echo "<td>{$i}</td>";
                                            echo "<td>{$row['branchId']}</td>";
                                            echo "<td>{$row['branchName']}</td>";
                                            echo "<td>{$row['zonalName']}</td>";
                                            echo "<td>{$row['customer']}</td>";
                                            echo "<td>{$row['contact']}</td>";
                                            echo "<td>{$row['type']}</td>";
					    echo "<td>{$row['idnumber']}</td>"; 
					    echo "<td>{$row['remark1']}</td>";
                                            echo "<td>{$row['date']}</td>";
                                            echo "<td>{$row['time']}</td>";
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
        <script>
            $('#call1').dataTable({
                "ajax": '',
                dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
                "lengthMenu": [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, "All"]
                ],
                buttons: [{
                        extend: 'copy',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'csv',
                        title: 'ExportReport',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'pdf',
                        title: 'ExportReport',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'print',
                        className: 'btn-sm'
                    }
                ]
            });
            $('#call2').dataTable({
                "ajax": '',
                dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
                "lengthMenu": [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, "All"]
                ],
                buttons: [{
                        extend: 'copy',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'csv',
                        title: 'ExportReport',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'pdf',
                        title: 'ExportReport',
                        className: 'btn-sm'
                    },
                    {
                        extend: 'print',
                        className: 'btn-sm'
                    }
                ]
            });
        </script>
