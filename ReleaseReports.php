<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
} else if ($type == 'Accounts') {
	include("header.php");
	include("menuacc.php");
} else if ($type == 'Accounts IMPS') {
	include("header.php");
	include("menuimpsAcc.php");
} else if ($type == 'AccHead') {
	include("header.php");
	include("menuaccHeadPage.php");
} else if ($type == 'Expense Team') {
	include("header.php");
	include("menuexpense.php");
} else if ($type == 'Zonal') {
	include("header.php");
	include("menuZonal.php");
} else if ($type == "Call Centre") {
	include("header.php");
	include("menuCall.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$date = date('Y-m-d');

$search = "";
$search1 = "";
$search2 = "";
if (isset($_GET['aaa'])) {
	$search = $_REQUEST['dat2'];
	$search1 = $_REQUEST['dat3'];
	$search2 = $_REQUEST['bran'];
}

if ($type == 'Zonal') {
	if ($_SESSION['branchCode'] == "TN") {
		$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN('Tamilnadu','Pondicherry') AND status=1");
		$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Tamilnadu','Pondicherry'))";
	} else if ($_SESSION['branchCode'] == "KA") {
		$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state='Karnataka' AND status=1");
		$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka')";
	} else if ($_SESSION['branchCode'] == "AP-TS") {
		$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1");
		$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Andhra Pradesh','Telangana'))";
	}
} else {
	$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where status=1");
	$extra_query = "";
}

if ($search == "" && $search1 == ""  && $search2 == "") {
	$sql = "SELECT t.name,t.phone,t.relGrossW,t.relNetW,t.relPurity,t.relPlaceType,t.relPlace,t.type,t.status,t.relCash,t.relIMPS,t.amount,t.date,t.time,b.branchName FROM releasedata t,branch b WHERE t.date='$date' AND t.status IN ('Billed','Appproved','Terminated','Carry Forward') AND t.BranchId=b.branchId " . $extra_query . " ORDER BY t.rid DESC";
	$query = mysqli_query($con, $sql);
} else if ($search !== "" && $search1 !== ""  && $search2 != "All Branches") {
	$sql = "SELECT t.name,t.phone,t.relGrossW,t.relNetW,t.relPurity,t.relPlaceType,t.relPlace,t.type,t.relCash,t.status,t.relIMPS,t.amount,t.date,t.time,b.branchName FROM releasedata t,branch b WHERE t.status IN ('Billed','Appproved','Terminated','Carry Forward') AND t.BranchId=b.branchId AND t.date BETWEEN '$search1' AND '$search' AND t.branchId='$search2' " . $extra_query . " ORDER BY t.rid DESC";
	$query = mysqli_query($con, $sql);
} else if ($search !== "" && $search1 !== ""  && $search2 == "All Branches") {
	$sql = "SELECT t.name,t.phone,t.relGrossW,t.status,t.relNetW,t.relPurity,t.relPlaceType,t.relPlace,t.type,t.relCash,t.relIMPS,t.amount,t.date,t.time,b.branchName FROM releasedata t,branch b WHERE t.status IN ('Billed','Appproved','Terminated','Carry Forward') AND t.BranchId=b.branchId AND t.date BETWEEN '$search1' AND '$search' " . $extra_query . " ORDER BY t.rid DESC";
	$query = mysqli_query($con, $sql);
}
?>
<style>
	#wrapper {
		background: #f5f5f5;
	}

	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 20px;
		color: #123C69;
	}

	#wrapper .panel-body {
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
		background-color: #f5f5f5;
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

	.dataTables_empty {
		text-align: center;
		font-weight: 600;
		font-size: 12px;
		text-transform: uppercase;
	}

	.btn-success {
		display: inline-block;
		padding: 0.7em 1.4em;
		margin: 0 0.3em 0.3em 0;
		border-radius: 0.15em;
		box-sizing: border-box;
		text-decoration: none;
		font-size: 10px;
		font-family: 'Roboto', sans-serif;
		text-transform: uppercase;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
		text-align: center;
		position: relative;
	}

	.fa_Icon {
		color: #ffcf40;
	}

	.modal-title {
		font-size: 25px;
		font-weight: 300;
		color: #fff5ee;
		text-transform: uppercase;
	}

	.modal-header {
		background: #123C69;
	}

	.row {
		margin-left: 0px;
		margin-right: 0px;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<option value="All Branches"> All Branches</option>
	<?php
	while ($branchList = mysqli_fetch_array($branches)) {
	?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class="row content">
		<form action="" method="GET">
			<div class="col-sm-3">
				<label class="text-success">Branch Id</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
					<input list="branchList" class="form-control" name="bran" id="bran" placeholder="Branch Id" required />
				</div>
			</div>
			<div class="col-sm-3">
				<label class="text-success">From Date</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
					<input type="date" class="form-control" id="dat3" name="dat3" required />
				</div>
			</div>
			<div class="col-sm-3">
				<label class="text-success">To Date</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
					<input type="date" class="form-control" id="dat2" name="dat2" required />
				</div>
			</div>
			<div class="col-sm-1">
				<button class="btn btn-success btn-block" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
			</div>
		</form>
		<div class="col-sm-2">
			<form action="export.php" enctype="multipart/form-data" method="post">
				<input type="hidden" name="branch" value="<?php if (isset($_GET['bran'])) {
																echo $_GET['bran'];
															} else {
																echo "All Branches";
															} ?>">
				<input type="hidden" name="from" value="<?php if (isset($_GET['dat3'])) {
															echo $_GET['dat3'];
														} else {
															echo $date;
														} ?>">
				<input type="hidden" name="to" value="<?php if (isset($_GET['dat2'])) {
															echo $_GET['dat2'];
														} else {
															echo $date;
														} ?>">
				<!-- <button type="submit" name="exportBills" class="btn btn-success btn-block" value="Export Excel" style="margin-top:23px" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button> -->
			</form>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<h3 class="text-success"><i style="color:#900" class="fa fa-edit"></i> Release Report</h3>
					</div>
					<div class="panel-body">
						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
										<th>Branch</th>
										<th>Name</th>
										<th>Phone</th>
										<th>GrossW</th>
										<th>NetW</th>
										<th>Purity</th>
										<th>Release Type</th>
										<th>Release Place</th>
										<th>Payment Type</th>
										<th>Cash</th>
										<th>IMPS</th>
										<th>Amount</th>
										<th>Status</th>
										<th>Date & Time</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									while ($row = mysqli_fetch_assoc($query)) {

										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['name'] . "</td>";
										echo "<td>" . $row['phone'] . "</td>";
										echo "<td>" . round($row['relGrossW'], 2) . "</td>";
										echo "<td>" . round($row['relNetW'], 2) . "</td>";
										echo "<td>" . round($row['relPurity'], 0) . "</td>";
										echo "<td>" . $row['relPlaceType'] . "</td>";
										echo "<td>" . $row['relPlace'] . "</td>";
										echo "<td>" . $row['type'] . "</td>";
										echo "<td>" . round($row['relCash'], 0) . "</td>";
										echo "<td>" . round($row['relIMPS'], 0) . "</td>";
										echo "<td>" . round($row['amount'], 0) . "</td>";
										echo "<td>" . $row['status'] . "</td>";
										echo "<td>" . $row['date'] . "<br>" . $row['time'] . "</td>";
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