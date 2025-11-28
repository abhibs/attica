<?php
session_start();
$type = $_SESSION['usertype'];
$employeeId = $_SESSION['employeeId'];

if ($type == 'SubZonal') {
	include("header.php");
	include("menuSubZonal.php");
} else {
	include("logout.php");
}
include("dbConnection.php");

if (isset($_GET['getImpsData'])) {
	$date = $_GET['date'];
} else {

	$date = date('Y-m-d');
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
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="col-sm-8">
				<h3 class="text-success"><i style="color:#900" class="fa fa-bank"></i> IMPS Details (<?php echo $date; ?>)</h3>
			</div>
			<form action="" method="GET">
				<div class="col-sm-3" style="margin-top:5px">
					<div class="input-group"><span class="input-group-addon"><span style="color:#900" class="fa fa-calendar"></span></span>
						<input type="date"  class="form-control" name="date" />

				

					</div>
				</div>
				<div class="col-sm-1" style="margin-top:5px">
					<button class="btn btn-success" name="getImpsData"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</div>
			</form>
		</div>
		<label class="col-sm-12"><br></label>
		<div class="col-lg-12">
			<div class="hpanel">
				<ul class="nav nav-tabs">
					<li class="active">
						<a data-toggle="tab" href="#tab-1" class="text-success"><i style="color:#990000" class="fa fa-check"></i> PHYSICAL </a>
					</li>
					<li class="">
						<a data-toggle="tab" href="#tab-2" class="text-success"><i style="color:#990000" class="fa fa-check"></i> RELEASE </a>
					</li>
				</ul>
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">
						<div class="panel-body">
							<table id="example1" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
										<th>BranchID</th>
										<th>Branch Name</th>
										<th>Cash Amount</th>
										<th>IMPS Amount</th>
										<th>Account Holder</th>
										<th>Bank (branch)</th>
										<th>Account No</th>
										<th>IFSC</th>
										<th>Time</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									$sqlA = mysqli_query($con, "SELECT a.branchId,a.branchName,a.cashA,a.impsA,b.* FROM
										(SELECT t.customerId,t.billId,t.date,t.time,t.branchId,t.cashA,t.impsA,b.branchName
										FROM trans t,branch b
										WHERE b.ezviz_vc = '$employeeId' AND t.paymentType IN ('NEFT/RTGS','Cash/IMPS') AND t.status='Approved' AND t.branchId=b.branchId AND t.date='$date') a
										LEFT JOIN 
										(SELECT * FROM bankdetails) b
										ON a.customerId=b.customerId AND a.billId=b.billID AND a.date=b.date");
									while ($rowA = mysqli_fetch_assoc($sqlA)) {
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $rowA['branchId'] . "</td>";
										echo "<td>" . $rowA['branchName'] . "</td>";
										echo "<td>" . $rowA['cashA'] . "</td>";
										echo "<td>" . $rowA['impsA'] . "</td>";
										echo "<td>" . $rowA['accountHolder'] . "</td>";
										echo "<td>" . $rowA['bank'] . "<br>(" . $rowA['branch'] . ")</td>";
										echo "<td>" . $rowA['account'] . "</td>";
										echo "<td>" . $rowA['ifsc'] . "</td>";
										echo "<td>" . $rowA['time'] . "</td>";
										echo "</tr>";
										$i++;
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
					<div id="tab-2" class="tab-pane">
						<div class="panel-body">
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
										<th>BranchID</th>
										<th>Branch Name</th>
										<th>Cash Amount</th>
										<th>IMPS Amount</th>
										<th>Account Holder</th>
										<th>Bank (branch)</th>
										<th>Account No</th>
										<th>IFSC</th>
										<th>Date (Time)</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									$sqlB = mysqli_query($con, "SELECT r.BranchId,r.relCash,r.relIMPS,r.accountHolder,r.relWith,r.relPlace,r.bankName,r.branchName,r.accountNo,r.IFSC,r.time,b.branchName FROM releasedata r,branch b WHERE b.ezviz_vc = '$employeeId' AND r.status IN ('Approved','Billed','Terminated','Carry Forward') AND r.date='$date' AND r.type='CASH/IMPS' AND r.BranchId=b.branchId");
									while ($rowB = mysqli_fetch_assoc($sqlB)) {
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $rowB['BranchId'] . "</td>";
										echo "<td>" . $rowB['branchName'] . "</td>";
										echo "<td>" . $rowB['relCash'] . "</td>";
										echo "<td>" . $rowB['relIMPS'] . "</td>";
										echo "<td>" . $rowB['accountHolder'] . "</td>";
										if ($rowB['relWith'] == "NBFC") {
											echo "<td>" . $rowB['relPlace'] . "</td>";
										} else {
											echo "<td>" . $rowB['bankName'] . "<br>(" . $rowB['branchName'] . ")</td>";
										}
										echo "<td>" . $rowB['accountNo'] . "</td>";
										echo "<td>" . $rowB['IFSC'] . "</td>";
										echo "<td>" . $rowB['time'] . "</td>";
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
