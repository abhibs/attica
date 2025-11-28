<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Kolkata');

$type = $_SESSION['usertype'] ?? '';

if ($type === 'Master') {
    include("header.php");
    include("menumaster.php");
} elseif ($type === 'Zonal') {
    include("header.php");
    include("menuZonal.php");
} else {
    include("logout.php");
    exit(); // Prevent further execution if the user is not authenticated
}

include("dbConnection.php");

$currentDate = date('Y-m-d');

?>

	<style>
	#results img{
		width:100px;
	}
	#wrapper{
		background: #f5f5f5;
	}
	
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
		background-color:#fffafa;
	}
	.quotation h3{
		color:#123C69;
		font-size: 18px!important;
	}
	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-weight:bold;
		font-size: 12px;
	}
	.btn-primary{
		background-color:#123C69;
	}
	.btn-info{
		background-color:#123C69;
		border-color:#123C69;
		font-size:12px;
	}	
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
		background-color:#123C69;
		border-color:#123C69;
	}
	.fa_Icon{
		color:#ffa500;
	}
	thead {
		text-transform:uppercase;
		background-color:#123C69;

	}
	thead tr{
		color: #f2f2f2;
		font-size:12px;
	}
	
	.dataTables_empty{
		text-align:center;
		font-weight:600;
		font-size:12px;
		text-transform:uppercase;
	}

	.btn-success{
		display:inline-block;
		padding:0.7em 1.4em;
		margin:0 0.3em 0.3em 0;
		border-radius:0.15em;
		box-sizing: border-box;
		text-decoration:none;
		font-size: 12px;
		font-family:'Roboto',sans-serif;
		text-transform:uppercase;
		color:#fffafa;
		background-color:#123C69;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
		text-align:center;
		position:relative;
	}

	.modaldesign {
		float: right;
		cursor: pointer;
		padding: 5px;
		background:none;
		color: #f0f8ff;
		border-radius: 5px;
		margin: 15px;
		font-size: 20px;
	}
	#available{
		text-transform:uppercase;
	}
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <h3 class="text-success"><i style="color:#990000" class="fa fa-ticket"></i> PLEDGE FUND All Data</h3>
                </div>
                <div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px; border-radius:10px;">
                    <form method="POST" class="form-horizontal" action="">
                        <div class="hpanel">
                            <div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px; border-radius:10px;">
                                <table id="example5" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr style="background-color:#123C69; color: white;">
                                            <th>#</th>
                                            <th>Bill ID</th>
                                            <th>Customer Name</th>
                                            <th>Contact</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Branch ID</th>
                                            <th>Employee ID</th>
                                            <th>Employee Name</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = $type === 'Master' ? "SELECT * FROM pledge_fund WHERE status='Released'" : "SELECT * FROM pledge_fund";
                                        $result = mysqli_query($con, $query);

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            $count = 1;
                                            while ($res = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                echo "<td>{$count}</td>";
                                                echo "<td>{$res['billId']}</td>";
                                                echo "<td>{$res['name']}</td>";
                                                echo "<td>{$res['contact']}</td>";
                                                echo "<td>{$res['paidamount']}</td>";
                                                echo "<td>{$res['status']}</td>";
                                                echo "<td>{$res['branchId']}</td>";
                                                echo "<td>{$res['empId']}</td>";
                                                echo "<td>{$res['empName']}</td>";
                                                echo "<td>{$res['date']}</td>";
                                                echo "<td>{$res['time']}</td>";
                                                echo "</tr>";
                                                $count++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='11'>No data found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
    <?php include("footer.php"); ?>
</div>


