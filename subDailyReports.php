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
$date = date('Y-m-d');

$search = "";
$search1 = "";
$search2 = "";
if (isset($_GET['aaa'])) {
	$search = $_GET['dat2'];
	$search1 = $_GET['dat3'];
	$search2 = $_GET['bran'];
}

if (isset($_POST['Deleteclos'])) {
	$id1 = $_POST['id1'];
	$inscon = "DELETE FROM `closing` WHERE closingID='$id1'";
	$query_run = mysqli_query($con, $inscon);
	echo "<script type='text/javascript'>alert('Reopen Successfully')</script>";
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
</style>
<!-- MODAL - PENDING BRANCHES -->
<div class="modal fade" id="myModal5" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>

			<div class="modal-header">
				<h5 class="modal-title">Pending Branches</h5>
				<div class="close error-close" data-dismiss="modal" style="margin-top: -30px;color:#fff5ee;opacity: 0.7;" title="close">
					<div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div>
				</div>
			</div>
			<div class="modal-body" style="background: #faf6f2;">
				<table id="example6" class="table table-striped table-bordered">
					<thead>
						<tr class="theadRow">
							<th><i class="fa fa-sort-numeric-asc"></i></th>
							<th>Branch Name</th>
							<th>Branch</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$openBranches = mysqli_query($con, "SELECT branchId,branchName FROM branch b WHERE branchId NOT IN (SELECT branchId FROM closing WHERE date='$date') AND b.ezviz_vc = '$employeeId' AND Status=1");
						while ($row = mysqli_fetch_assoc($openBranches)) {
							echo "<tr>";
							echo "<td>" . $i .  "</td>";
							echo "<td>" . $row['branchId'] . "</td>";
							echo "<td>" . $row['branchName'] . "</td>";
							echo "</tr>";
							$i++;
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>
<!-- DATA LIST -->
<datalist id="branchList">
	<option value="All Branches" label="All Branches"></option>
	<?php
	$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where ezviz_vc='$employeeId' AND  status=1");
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
				<div class="input-group"><span class="input-group-addon"><span style="color:#900" class="fa fa-address-book-o"></span></span>
					<input list="branchList" class="form-control" name="bran" id="bran" placeholder="Branch Id" required />
				</div>
			</div>
			<div class="col-sm-3">
				<label class="text-success">From Date</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#900" class="fa fa-calendar"></span></span>
					<input type="date" class="form-control" id="dat3" name="dat3" required />
				</div>
			</div>
			<div class="col-sm-3">
				<label class="text-success">To Date</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#900" class="fa fa-calendar"></span></span>
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
				<button type="submit" name="exportClosingData" class="btn btn-success btn-block" value="Export Excel" style="margin-top:23px" required>
					<span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel
				</button>
			</form>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<br>
				<div data-toggle="modal" data-target="#myModal5" style="cursor:pointer" class="btn btn-success"><span class="fa_Icon fa fa-key"></span> OPEN BRANCHES</div>
				<br>
				<div class="panel-body" style="background: #f5f5f5;border: 5px solid #fff;border-radius: 10px;padding: 10px 15px 50px 15px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;">
					<?php if ($search == "" && $search1 == "" && $search2 == "") { ?>
						<table id="example5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
									<th>Branch</th>
									<th>Time</th>
									<th>Bill</th>
									<th>Total Funds</th>
									<th>Trans Amount</th>
									<th>Gross WG</th>
									<th>Net WG</th>
									<th>Gross AG</th>
									<th>Net AG</th>
									<th>Exp</th>
									<th>Closing Amount</th>
									<th>Status</th>
									<th>Report</th>
									<th>ReOpen</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$sql1 = mysqli_query($con, "SELECT closing.*,branch.branchName FROM closing,branch WHERE ezviz_vc = '$employeeId' AND closing.date='$date' AND closing.branchId=branch.branchId");
								while ($row1 = mysqli_fetch_assoc($sql1)) {
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row1['branchName'] . "(" . $row1['branchId'] . ")</td>";
									echo "<td>" . $row1['time'] . "</td>";
									echo "<td>" . $row1['transactions'] . "</td>";
									echo "<td>" . $row1['totalAmount'] . "</td>";
									echo "<td>" . $row1['transactionAmount'] . "</td>";
									echo "<td>" . $row1['grossWG'] . "</td>";
									echo "<td>" . $row1['netWG'] .  "</td>";
									echo "<td>" . $row1['grossAG'] . "</td>";
									echo "<td>" . $row1['netAG'] . "</td>";
									echo "<td>" . $row1['expenses'] . "</td>";
									echo "<td>" . $row1['balance'] .  "</td>";
									echo "<td>Closed</td>";
									echo "<td><a target='_blank' class='btn btn-success' href='dailyPdf1.php?branch=" . $row1['branchId'] . "&date=" . $row1['date'] . "&a=" . $row1['one'] . "&b=" . $row1['two'] . "&c=" . $row1['three'] . "&d=" . $row1['four'] . "&e=" . $row1['five'] . "&f=" . $row1['six'] . "&g=" . $row1['seven'] . "&h=" . $row1['eight'] . "&i=" . $row1['nine'] . "&j=" . $row1['ten'] . "'><i style='color:#ffcf40' class='fa fa-eye'></i> View </a></td>";
								?>
									<form method="POST" action="">
										<input type="hidden" class="form-control col-sm-2" name="id1" value="<?php echo $row1['closingID'] ?>">
										<td><input type="submit" class="form-control btn btn-danger" name="Deleteclos" value="☢"></td>
									</form>
								<?php

									echo "</tr>";
									$i++;
								}
								?>
							</tbody>
						</table>
					<?php } else if ($search != "" && $search1 != ""  && $search2 != "") {	?>
						<table id="example5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th>SLNo</th>
									<th>Branch</th>
									<th>Date</th>
									<th>Time</th>
									<th>Total Funds</th>
									<th>Bill</th>
									<th>Trans Amount</th>
									<th>Balance</th>
									<th>Gross WG</th>
									<th>Net WG</th>
									<th>Gross AG</th>
									<th>Net AG</th>
									<th>Expenses</th>
									<th>Total</th>
									<th>Diff</th>
									<th>Closing Report</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($search2 == "All Branches") {
								$sql2 = mysqli_query($con, "SELECT closing.*,branch.branchName FROM closing,branch WHERE closing.branchId=branch.branchId AND closing.date BETWEEN '$search1' AND '$search'");
							} else {
								$sql2 = mysqli_query($con, "SELECT closing.*,branch.branchName FROM closing,branch WHERE closing.branchId=branch.branchId AND closing.date BETWEEN '$search1' AND '$search' AND closing.branchId='$search2'");
							}
							$i = 1;
							while ($row2 = mysqli_fetch_assoc($sql2)) {
								echo "<tr>";
								echo "<td>" . $i .  "</td>";
								echo "<td>" . $row2['branchName'] . "(" . $row2['branchId'] . ")</td>";
								echo "<td>" . $row2['date'] . "</td>";
								echo "<td>" . $row2['time'] . "</td>";
								echo "<td>" . $row2['totalAmount'] . "</td>";
								echo "<td>" . $row2['transactions'] . "</td>";
								echo "<td>" . $row2['transactionAmount'] . "</td>";
								echo "<td>" . $row2['balance'] . "</td>";
								echo "<td>" . $row2['grossWG'] . "</td>";
								echo "<td>" . $row2['netWG'] .  "</td>";
								echo "<td>" . $row2['grossAG'] . "</td>";
								echo "<td>" . $row2['netAG'] . "</td>";
								echo "<td>" . $row2['expenses'] . "</td>";
								echo "<td>" . $row2['total'] .  "</td>";
								echo "<td>" . $row2['diff'] . "</td>";
								echo "<td><a target='_blank' class='btn btn-success' href='dailyPdf1.php?branch=" . $row2['branchId'] . "&date=" . $row2['date'] . "&a=" . $row2['one'] . "&b=" . $row2['two'] . "&c=" . $row2['three'] . "&d=" . $row2['four'] . "&e=" . $row2['five'] . "&f=" . $row2['six'] . "&g=" . $row2['seven'] . "&h=" . $row2['eight'] . "&i=" . $row2['nine'] . "&j=" . $row2['ten'] . "'><i class='fa fa-eye' style='color:#ffcf40'></i> View </a></td>";
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
	<?php include("footer.php"); ?>
