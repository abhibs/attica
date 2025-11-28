<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
} else if ($type == 'ApprovalTeam') {
	include("header.php");
	include("menuapproval.php");
} else if ($type == 'AccHead') {
	include("header.php");
	include("menuaccHeadPage.php");
} else if ($type == 'Zonal') {
	include("header.php");
	include("menuZonal.php");
} else if ($type == 'SubZonal') {
	include("header.php");
	include("menuSubZonal.php");
} else if ($type == 'ZonalMaster') {
	include("header.php");
	include("menuzonalMaster.php");
} else if ($type == 'SundayUser') {
	include("header.php");
	include("menuSundayUser.php");
} else if ($type == 'Accounts IMPS') {
	include("header.php");
	include("menuimpsAcc.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$date = date('Y-m-d');
?>
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

	button {
		transform: none;
		box-shadow: none;
	}

	button:hover {
		background-color: gray;
		cursor: pointer;
	}

	.panel-title {
		color: #123C69;
		text-transform: uppercase;
		font-weight: bold;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<?php if ($type != 'ApprovalTeam' && $type != 'Accounts IMPS') { ?>

			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading hbuilt">
						<i class="fa_Icon fa fa-money"></i> <span style="color:#123C69;"> TODAY'S GOLD PRICE</span>
					</div>
					<div class="panel-body">
						<h1 class="text-center">Karnataka</h1>
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Gold">
							<input type="hidden" name="user" value="<?= $_SESSION['employeeId']; ?>">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange="javascript:cal0(this.form);" autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers" autocomplete="off" required placeholder="IMPS">
							</div>

							<label class="col-sm-12 control-label"><br></label>

							<div class="col-lg-12">
								<p class="text-success"><b>BRANCHES</b></p>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" id="checkAllBranchesKar">
										<label class="text-success"><b> Select All </b></label>
									</div>
								</div>

								<?php

								$query = "SELECT branchId, branchName FROM branch WHERE status = 1 AND state = 'Karnataka' ORDER BY branchName ASC";
								$result = mysqli_query($con, $query);
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="' . $row['branchId'] . '" class="CheckBranchKar">
										<label> ' . $row['branchName'] . ' </label>
									</div>
								</div>';
								}
								?>
							</div>

							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>
						</form>

					</div>



					<div class="panel-body">
						<h1 class="text-center">Andra Pradesh</h1>
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Gold">
							<input type="hidden" name="user" value="<?= $_SESSION['employeeId']; ?>">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange="javascript:cal0(this.form);" autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers" autocomplete="off" required placeholder="IMPS">
							</div>

							<label class="col-sm-12 control-label"><br></label>

							<div class="col-lg-12">
								<p class="text-success"><b>BRANCHES</b></p>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" id="checkAllBranchesApt">
										<label class="text-success"><b> Select All </b></label>
									</div>
								</div>

								<?php

								$query = "SELECT branchId, branchName FROM branch WHERE status = 1 AND state in('Telangana', 'Andhra Pradesh') ORDER BY branchName ASC";
								$result = mysqli_query($con, $query);
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<div class="col-lg-3">
										<div class="checkbox checkbox-success checkbox-inline">
											<input type="checkbox" name="place[]" value="' . $row['branchId'] . '" class="CheckBranchApt">
											<label> ' . $row['branchName'] . ' </label>
										</div>
									</div>';
								}
								?>
							</div>

							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>
						</form>

					</div>



					<div class="panel-body">
						<h1 class="text-center">Tamilnadu</h1>
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Gold">
							<input type="hidden" name="user" value="<?= $_SESSION['employeeId']; ?>">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange="javascript:cal0(this.form);" autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers" autocomplete="off" required placeholder="IMPS">
							</div>

							<label class="col-sm-12 control-label"><br></label>

							<div class="col-lg-12">
								<p class="text-success"><b>BRANCHES</b></p>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" id="checkAllBranchesTam">
										<label class="text-success"><b> Select All </b></label>
									</div>
								</div>

								<?php

								$query = "SELECT branchId, branchName FROM branch WHERE status = 1 AND state in('Tamilnadu', 'Pondicherry') ORDER BY branchName ASC";
								$result = mysqli_query($con, $query);
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="' . $row['branchId'] . '" class="CheckBranchTam">
										<label> ' . $row['branchName'] . ' </label>
									</div>
								</div>';
								}
								?>
							</div>

							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>
						</form>

					</div>
				</div>
			</div>

			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading hbuilt">
						<i class="fa_Icon fa fa-money"></i> <span style="color:#123C69;"> TODAY'S SILVER PRICE</span>
					</div>
					<div class="panel-body">
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Silver">
							<input type="hidden" name="user" value="<?= $_SESSION['employeeId']; ?>">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange=javascript:cal0(this.form); autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers" autocomplete="off" required placeholder="IMPS">
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-lg-3">
								<div class="checkbox checkbox-success checkbox-inline">
									<input type="checkbox" id="checkAllBranchesSil">
									<label class="text-success"><b> Select All </b></label>
								</div>
							</div>

							<?php

							$query = "SELECT branchId, branchName FROM branch WHERE status = 1 ORDER BY branchName ASC";
							$result = mysqli_query($con, $query);
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="' . $row['branchId'] . '" class="CheckBranchSil">
										<label> ' . $row['branchName'] . ' </label>
									</div>
								</div>';
							}
							?>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading">
					<b class="panel-title">TODAY'S GOLD RATE DETAILS</b>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>Time</th>
								<th>Name</th>
								<th>City</th>
								<th>Cash Rate</th>
								<th>Transfer Rate</th>
								<th>Edit</th>

							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							$sql1 = mysqli_query($con, "SELECT 
								g.id,
								e.name,
								g.cash,
								g.transferRate,
								b.branchName AS city,
								g.time
							FROM gold g
							JOIN branch b 
								ON b.branchId = g.city
							JOIN employee e 
    								ON g.user = e.empId 
							WHERE g.date = '$date'
							AND g.type = 'Gold'
							ORDER BY g.id DESC;
							");
							while ($row1 = mysqli_fetch_assoc($sql1)) {
								echo "<tr>";
								echo "<td>" . $row1['time'] . "</td>";
								echo "<td>" . $row1['name'] . "</td>";
								echo "<td>" . $row1['city'] . "</td>";
								echo "<td>" . $row1['cash'] . "</td>";
								echo "<td>" . $row1['transferRate'] . "</td>";
								echo "<td style='text-align:center'><a href='editGoldRate.php?id=" . $row1['id'] . "' target = '_blank' class='btn' type='button'><i class='fa fa-pencil-square-o text-success'style='font-size:16px'></i></a></td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading">
					<b class="panel-title">TODAY'S SILVER RATE DETAILS</b>
				</div>
				<div class="panel-body">
					<table id="example6" class="table table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>Time</th>
								<th>Name</th>
								<th>City</th>
								<th>Cash Rate</th>
								<th>Transfer Rate</th>
  								 <th>Edit</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							$sql2 = mysqli_query($con, "SELECT 
								g.id,
								e.name,
								g.cash,
								g.transferRate,
								b.branchName AS city,
								g.time
							FROM gold g
							JOIN branch b 
								ON b.branchId = g.city
							JOIN employee e
                                                                ON g.user = e.empId
							WHERE g.date = '$date'
							AND g.type = 'Silver'
							ORDER BY g.id DESC;
							");
							while ($row2 = mysqli_fetch_assoc($sql2)) {
								echo "<tr>";
								echo "<td>" . $row2['time'] . "</td>";
								echo "<td>" . $row2['name'] . "</td>";
								echo "<td>" . $row2['city'] . "</td>";
								echo "<td>" . $row2['cash'] . "</td>";
								echo "<td>" . $row2['transferRate'] . "</td>";
								echo "<td style='text-align:center'><a href='editGoldRate.php?id=" . $row2['id'] . "' target = '_blank' class='btn' type='button'><i class='fa fa-pencil-square-o text-success'style='font-size:16px'></i></a></td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
	<script>
		$(document).ready(function() {

			$('#checkAllBranchesKar').change(function() {
				if (this.checked) {
					$('.CheckBranchKar').prop('checked', this.checked);
				} else {
					$('.CheckBranchKar').prop('checked', false);
				}
			});

			$('#checkAllBranchesApt').click(function() {
				if (this.checked) {
					$('.CheckBranchApt').prop('checked', this.checked);
				} else {
					$('.CheckBranchApt').prop('checked', false);
				}
			});

			$('#checkAllBranchesTam').change(function() {
				if (this.checked) {
					$('.CheckBranchTam').prop('checked', this.checked);
				} else {
					$('.CheckBranchTam').prop('checked', false);
				}
			});

			$('#checkAllBranchesSil').click(function() {
				if (this.checked) {
					$('.CheckBranchSil').prop('checked', true);
				} else {
					$('.CheckBranchSil').prop('checked', false);
				}
			});

		});
	</script>
	<?php include("footer.php"); ?>
