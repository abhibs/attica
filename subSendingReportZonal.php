<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
$employeeId=$_SESSION['employeeId'];

 if ($type == 'SubZonal') {
	include("header.php");
	include("menuSubZonal.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$date = date('Y-m-d');
$sBranch = '';
$sFrom = '';
$sTo = '';
$sMetal = '';
if (isset($_GET['sMetal'])) {
	$sBranch = $_GET['sBranch'];
	$sFrom = $_GET['sFrom'];
	$sTo = $_GET['sTo'];
	$sMetal = $_GET['sMetal'];
	$branchDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$sBranch'"));
}
?>
<datalist id="branchList">
	<?php
	$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where status=1 AND ezviz_vc = '$employeeId'");
	while ($branchList = mysqli_fetch_array($branches)) {
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<style>
	.tab {
		font-family: 'Titillium Web', sans-serif;
	}

	.tab .nav-tabs {
		padding: 0;
		margin: 0;
		border: none;
	}

	.tab .nav-tabs li a {
		color: #123C69;
		background: #f8f8ff;
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
		background: #f8f8ff;
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

	.tab .tab-content {
		color: #0C1115;
		background: #f8f8ff;
		font-size: 12px;
		/* outline: 2px solid rgba(19,99,126,0.2); */
		position: relative;
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 5px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}

	.tab-content h4 {
		color: #123C69;
		font-weight: 500;
	}

	.tab-pane {
		background: #f8f8ff;
		padding: 10px;
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
		color: #ffa500;
	}

	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 5px;
		box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}

	legend {
		margin-left: 8px;
		width: 300px;
		background-color: #123C69;
		padding: 5px 15px;
		line-height: 30px;
		font-size: 18px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.8);
		margin-bottom: 0px;
		letter-spacing: 2px;
		text-transform: uppercase;
	}

	button {
		transform: none;
		box-shadow: none;
	}

	button:hover {
		background-color: gray;
		cursor: pointer;
	}

	legend:after {
		content: "";
		height: 0;
		width: 0;
		background-color: transparent;
		border-top: 0.0rem solid transparent;
		border-right: 0.35rem solid black;
		border-bottom: 0.45rem solid transparent;
		border-left: 0.0rem solid transparent;
		position: absolute;
		left: -0.075rem;
		bottom: -0.45rem;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<form action="" method="GET">
				<div class="col-sm-3">
					<label class="text-success">Branch Id</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000"
								class="fa fa-address-book-o"></span></span>
						<input list="branchList" class="form-control" name="sBranch" placeholder="Branch Id" required />
					</div>
				</div>
				<div class="col-sm-3">
					<label class="text-success">From Date:</label>
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000"
								class="fa fa-calendar"></span></span>
						<input type="date" class="form-control" id="sFrom" name="sFrom" >
					</div>
				</div>
				<div class="col-sm-3">
					<label class="text-success">To Date:</label>
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000"
								class="fa fa-calendar"></span></span>
						<input type="date" class="form-control" id="sTo" name="sTo">
					</div>
				</div>
				<div class="col-sm-3" align="center" style="margin-top:23px">
					<button class="btn btn-success" name="sMetal" value="Gold"><span style="color:#ffcf40"
							class="fa fa-search"></span> Search Gold</button> &nbsp;
					<button class="btn btn-success" name="sMetal" value="Silver"><span style="color:#ffcf40"
							class="fa fa-search"></span> Search Silver</button>
							<br><br>
				</div>
				
			</form>
			</div>
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><b><i style="color:#990000;margin-top:20px;" class="fa fa-edit"></i>
							Transaction Report</b></h3>
				</div>
				<br>
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#done" aria-controls="done" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Move To HO </a></li>
						<li role="presentation"><a href="#pending" aria-controls="pending" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Pending </a></li>
					</ul>
					<div class="tab-content tabs" style="min-height:300px;">
						<div role="tabpanel" class="tab-pane fade in active" id="done">
							<h4>Move To HO </h4>
							<table id="example5" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th style='text-align:center'><i class="fa fa-sort-numeric-asc"></i></th>
										<th style='text-align:center'>Branch Name</th>
										<th style='text-align:center'>GrossW</th>
										<th style='text-align:center'>NetW</th>
										<th style='text-align:center'>GrossA</th>
										<th style='text-align:center'>NetA</th>
										<th style='text-align:center'>Packets</th>
										<th style='text-align:center'>Send_Date</th>
										<th style='text-align:center'> Send_Report</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									$totalGrossW = 0;
									$totalNetW = 0;
									$totalGrossA = 0;
									$totalNetA = 0;
									$total_P = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(netW) AS netW,SUM(grossW) AS grossW , SUM(netA) AS netA,SUM(grossA) AS grossA,date,COUNT(id) AS id,AVG(rate) AS rateAvg FROM trans WHERE metal='" . $_GET['sMetal'] . "' AND branchId='$sBranch' AND date BETWEEN '$sFrom' AND '$sTo' AND status='Approved' GROUP BY staDate"));
									$query = mysqli_query($con, "SELECT SUM(grossW) AS totalGrossW, SUM(netW) AS totalNetW, SUM(grossA) AS totalGrossA, SUM(netA) AS totalNetA,date,sta,staDate,COUNT(id) AS packetCount FROM trans WHERE date BETWEEN '$sFrom' AND '$sTo' AND metal='" . $_GET['sMetal'] . "' AND branchId='$sBranch' AND status='Approved' GROUP BY staDate");
									while ($row = mysqli_fetch_assoc($query)) {
										if ($row['sta'] == 'Checked') {
											echo "<tr>";
											echo "<td class='text-center'>" . $i . "</td>";
											echo "<td class='text-center'>" . $branchDetails['branchName'] . "</td>";
											echo "<td class='text-center'>" . round($row['totalGrossW'], 2) . "</td>";
											echo "<td class='text-center'>" . round($row['totalNetW'], 2) . "</td>";
											echo "<td class='text-center'>" . round($row['totalGrossA'], 0) . "</td>";
											echo "<td class='text-center'>" . round($row['totalNetA'], 0) . "</td>";
											echo "<td class='text-center'>" . $row['packetCount'] . "</td>";
											echo "<td class='text-center'>" . $row['staDate'] . "</td>";
											echo "<td class='text-center'>";
											if ($row['sta'] == 'Checked') {
												?>
												<form action="SendReportPdfMasterZonal.php" method="POST" target="_blank">
													<input type="hidden" name="sBranch" value="<?php echo $sBranch; ?>">
													<input type="hidden" name="sMetal" value="<?php echo $sMetal; ?>">
													<input type="hidden" name="staDate" value="<?php echo $row['staDate']; ?>">
													<button type="submit" class="btn btn-success"><span class='fa fa-eye'
															style="color:#ffcf40;"></span> View</button>
												</form>
												<?php
											}
											$i++;
											echo "</td>";
										}
										echo "</tr>";
										$totalGrossW += $row['totalGrossW'];
										$totalNetW += $row['totalNetW'];
										$totalGrossA += $row['totalGrossA'];
										$totalNetA += $row['totalNetA'];
										$totalPackets[$row['staDate']] = $row['packetCount'];
									}
									?>
								</tbody>
							</table>
						</div>
						<div role="tabpanel" class="tab-pane fade" id="pending">
							<h4>Pending </h4>
							<form method="POST" action="">
								<table id="example6" class="table table-striped table-bordered">
									<input type="hidden" name='sBranch' value="<?php echo $sBranch; ?>">
									<input type="hidden" name='sMetal' value="<?php echo $sMetal; ?>">
									<thead>
										<tr class="theadRow">
											<th class='text-center'><i style="color:#ffcf40"
													class="fa fa-sort-numeric-asc"></i></th>
											<th class='text-center'>Branch</th>
											<th class='text-center'>Name</th>
											<th class='text-center'>Phone</th>
											<th class='text-center'>GrossW</th>
											<th class='text-center'>NetW</th>
											<th class='text-center'>GrossA</th>
											<th class='text-center'>NetA</th>
											<th class='text-center'>trans_Date</th>
											<th class='text-center'>Bill</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$totalQ = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(netW) AS netW,SUM(grossW) AS grossW , SUM(netA) AS netA,SUM(grossA) AS grossA,date,COUNT(id) AS id,AVG(rate) AS rateAvg FROM trans WHERE metal='" . $_GET['sMetal'] . "' AND branchId='$sBranch' AND  status='Approved' and sta!='Checked'"));
										$pur = ($totalQ['netW'] == null) ? 0 : ($totalQ['grossA'] / $totalQ['netW']) / $totalQ['rateAvg'] * 100;
										$i = 1;
										$query = mysqli_query($con, "SELECT id as invoice_id,name,phone,grossW,netW,grossA,netA,date,sta,staDate FROM trans WHERE   metal='" . $_GET['sMetal'] . "' AND branchId='$sBranch' AND status='Approved'");
										while ($row = mysqli_fetch_assoc($query)) {
											if ($row['sta'] != 'Checked') {
												echo "<tr>";
												echo "<td class='text-center'>" . $i . "</td>";
												echo "<td class='text-center'>" . $branchDetails['branchName'] . "</td>";
												echo "<td class='text-center'>" . $row['name'] . "</td>";
												echo "<td class='text-center'>" . $row['phone'] . "</td>";
												echo "<td class='text-center'>" . round($row['grossW'], 2) . "</td>";
												echo "<td class='text-center'>" . round($row['netW'], 2) . "</td>";
												echo "<td class='text-center'>" . round($row['grossA'], 0) . "</td>";
												echo "<td class='text-center'>" . round($row['netA'], 0) . "</td>";
												echo "<td class='text-center'>". $row['date'] ."</td>";
												echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='Invoice.php?id=" . base64_encode($row['invoice_id']) . "'><i class='fa_Icon fa fa-eye'></i> Bill</a></b></td>";
												$i++;
											}
											echo "</tr>";
										}
										?>
									</tbody>
									<tfoot>
										<tr class="theadRow">
											<th class='text-center' colspan="2">Branch Name</th>
											<th class='text-center' >Gross Weight</th>
											<th class='text-center'>Net Weight</th>
											<th class='text-center' colspan="2">Gross Amount</th>
											<th class='text-center'  colspan="2">Net Amount</th>
											<th class='text-center'>Average Purity</th>
											<th class='text-center' >Packets</th>
										</tr>
										<tr style="background-color:#e6e6fa;">
											<td class='text-center' colspan="2" style="font-weight: 800;">
												<?php echo $branchDetails['branchName']; ?>
											</td>
											<td class='text-center'  style="font-weight: 800;">
												<?php echo round($totalQ['grossW'], 2); ?>
											</td>
											<td class='text-center' style="font-weight: 800;">
												<?php echo round($totalQ['netW'], 2); ?>
											</td>
											<td class='text-center' colspan="2" style="font-weight: 800;">
												<?php echo round($totalQ['grossA'], 0); ?>
												</th>
											<td class='text-center'  colspan="2" style="font-weight: 800;">
												<?php echo round($totalQ['netA'], 2); ?>
											</td>
											<td class="text-center" style="font-weight: 800;">
												<?php echo round($pur, 2); ?> %
											</td>
											<td class="text-center" style="font-weight: 800;">
												<?php echo $totalQ['id']; ?>
											</td>
										</tr>
									</tfoot>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
		<?php include("footer.php"); ?>
