<?php

error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");

//Date
if (isset($_GET['date'])) {
	$date = $_GET['date'];
} else {
	$date = date('Y-m-d');
}

/* *************    TRANS DATA   ************* */
$newCustomer = 0;
$marginAmount = 0;
$margin = 0;
$cashCount = 0;
$impsCount = 0;
$cashAmount = 0;
$impsAmount = 0;
$busBranches = [];

/*   BANGALORE   */
$bang_bills = 0;
$bang_grossW = 0;
$bang_grossA = 0;
$bang_netA = 0;
/*   KARNATAKA   */
$kar_bills = 0;
$kar_grossW = 0;
$kar_grossA = 0;
$kar_netA = 0;
/*   CHENNAI   */
$chn_bills = 0;
$chn_grossW = 0;
$chn_grossA = 0;
$chn_netA = 0;
/*   TANILNADU & PONDICHERRY   */
$tn_bills = 0;
$tn_grossW = 0;
$tn_grossA = 0;
$tn_netA = 0;
/*   HYDERABAD   */
$hyd_bills = 0;
$hyd_grossW = 0;
$hyd_grossA = 0;
$hyd_netA = 0;
/*   ANDHRA PRADESH & TELANGANA   */
$apt_bills = 0;
$apt_grossW = 0;
$apt_grossA = 0;
$apt_netA = 0;

/*   SILVER   */
$sil_bills = 0;
$sil_grossW = 0;
$sil_netA = 0;

$transQuery = mysqli_query($con, "SELECT *
	FROM
	(SELECT billCount,ROUND(grossW,2) AS grossW,ROUND(netA,2) AS netA,ROUND(grossA,2) AS grossA,branchId,type,margin,metal,paymentType,cashA,impsA FROM trans WHERE status='Approved' AND date='$date') A
	INNER JOIN 
	(SELECT branchId,state,city FROM branch WHERE status=1) B
	ON A.branchId=B.branchId");
while ($row = mysqli_fetch_assoc($transQuery)) {

	/* NEW CUSTOMERS */
	if ($row['billCount'] == 0) {
		$newCustomer++;
	}

	/* CASH/IMPS COUNT */
	if ($row['paymentType'] == 'Cash') {
		$cashCount++;
	} else if ($row['paymentType'] == 'NEFT/RTGS') {
		$impsCount++;
	}
	$cashAmount += $row['cashA'];
	$impsAmount += $row['impsA'];

	/* CITY/STATE WISE DATA */
	if ($row['metal'] == 'Gold') { /* GOLD DATA */

		/* MARGIN AMOUNT */
		$marginAmount += $row['margin'];

		if ($row['city'] == 'Bengaluru') {
			$bang_bills++;
			$bang_grossW += $row['grossW'];
			$bang_grossA += $row['grossA'];
			$bang_netA += $row['netA'];
		} else if ($row['city'] == 'Chennai') {
			$chn_bills++;
			$chn_grossW += $row['grossW'];
			$chn_grossA += $row['grossA'];
			$chn_netA += $row['netA'];
		} else if ($row['city'] == 'Hyderabad') {
			$hyd_bills++;
			$hyd_grossW += $row['grossW'];
			$hyd_grossA += $row['grossA'];
			$hyd_netA += $row['netA'];
		} else if ($row['city'] != 'Bengaluru' && $row['state'] == 'Karnataka') {
			$kar_bills++;
			$kar_grossW += $row['grossW'];
			$kar_grossA += $row['grossA'];
			$kar_netA += $row['netA'];
		} else if ($row['city'] != 'Chennai' && ($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry')) {
			$tn_bills++;
			$tn_grossW += $row['grossW'];
			$tn_grossA += $row['grossA'];
			$tn_netA += $row['netA'];
		} else if ($row['city'] != 'Hyderabad' && ($row['state'] == 'Telangana' || $row['state'] == 'Andhra Pradesh')) {
			$apt_bills++;
			$apt_grossW += $row['grossW'];
			$apt_grossA += $row['grossA'];
			$apt_netA += $row['netA'];
		}
	} else if ($row['metal'] == 'Silver') { /* SILVER DATA */
		$sil_bills++;
		$sil_grossW += $row['grossW'];
		$sil_netA += $row['netA'];
	}

	/* BUSINESS BRANCHES */
	$busBranches[] = $row['branchId'];
}

/* *************    BRANCH LIST   ************* */
$branches = [];
$activeBranches = 0;
$branchQuery = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
while ($row = mysqli_fetch_assoc($branchQuery)) {
	$activeBranches++;
	$branches[] = $row;
}

/* *************    OTHER DATA   ************* */
$otherData = mysqli_fetch_assoc(mysqli_query($con, "SELECT
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='with' AND branchId !='' AND status!='Rejected' ) AS withGold,
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='without' AND branchId !='' AND status!='Rejected' ) AS withoutGold,
	(SELECT SUM(amount) FROM expense WHERE date='$date' AND status='Approved') AS Expense,
	(SELECT SUM(relCash) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relCash,
	(SELECT SUM(relIMPS) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relIMPS,
	(SELECT cash FROM gold WHERE type='Gold' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldCashRate,
	(SELECT transferRate FROM gold WHERE type='Gold' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldIMPSRate,
	(SELECT cash FROM gold WHERE type='Silver' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS silverRate
	"));

?>
<datalist id="branchList">
	<?php foreach ($branches as $key => $val) { ?>
		<option value="<?php echo $val['branchId']; ?>"><?php echo $val['branchName']; ?></option>
	<?php } ?>
</datalist>
<style>
	#wrapper {
		background: #f5f5f5;
	}

	.hpanel {
		margin-bottom: 5px;
		border-radius: 10px;
		box-shadow: 5px 5px 5px #999;
	}

	.text-success {
		color: #123C69;
		text-transform: uppercase;
		font-size: 20px;
	}

	.stats-label {
		text-transform: uppercase;
		font-size: 10px;
	}

	.list-item-container h3 {
		font-size: 14px;
	}

	.panel-footer {
		border-radius: 0px 0px 10px 10px;
		text-align: center;
	}

	.panel-footer>b {
		color: #990000;
	}

	#wrapper .panel-body {
		border-radius: 10px 10px 0px 0px;
	}

	.fa {
		color: #990000;
	}
</style>

<meta http-equiv="refresh" content="300">
<div id="wrapper">
	<div class="content">

		<div class="row">

			<!--  DATE SELECTOR  -->

			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold text-center text-success">Date : <?php echo $date; ?></h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-calendar fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<form action="" method="GET">
									<div class="col-xs-9">
										<input type="date" class="form-control" name="date" placeholder="Branch"
											required>
									</div>
									<div class="col-xs-3">
										<button class="btn btn-success"><i style="color:#FFFFFF"
												class="fa fa-search"></i></button>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp;
						<!-- <i class="fa fa-angle-double-right"></i> -->
						<!-- <a href="#"> View Details</a> -->
					</div>
				</div>
			</div>

			<!--  TODAYS RATE  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Today's Rate</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-rupee fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-8">
									<small class="stats-label">Gold Rate:</small>
									<h4><i class="fa fa-rupee"></i>
										<?php echo $otherData['goldCashRate'] . " / " . $otherData['goldIMPSRate']; ?>
									</h4>
								</div>
								<div class="col-xs-4">
									<small class="stats-label">Silver Rate:</small>
									<h4><i class="fa fa-rupee"></i> <?php echo $otherData['silverRate']; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp;
						<!-- <i class="fa fa-angle-double-right"></i> 
						<a href="#">View Details</a> -->
					</div>
				</div>
			</div>

			<!--  TOTAL CUSTOMERS  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Customers</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins"></span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">New Customers</small>
									<h4><i class="fa fa-user"></i> <?php echo $newCustomer; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Total Bills</small>
									<h4><i class="fa fa-edit"></i>
										<?php echo ($bang_bills + $chn_bills + $hyd_bills + $kar_bills + $tn_bills + $apt_bills + $sil_bills); ?>
									</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp;
						<!-- <i class="fa fa-angle-double-right"></i> <a
							href="viewCustomers.php">View Details</a> -->
					</div>
				</div>
			</div>

			<!--  ENQUIRY  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Enquiry</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-comments fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> With Gold</small>
									<h4><?php echo $otherData['withGold']; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> Without Gold</small>
									<h4><?php echo $otherData['withoutGold']; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="walkinout.php">View Details</a>
					</div>
				</div>
			</div>

		</div>

		<div class="row">

			<!--  BRANCH REPORT  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Report</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-university fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Business Branches</small>
									<h4><i class="fa fa-institution"></i>
										<?php echo count(array_unique($busBranches)); ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Golden Ducks</small>
									<h4><i class="fa fa-institution"></i>
										<?php echo $activeBranches - count(array_unique($busBranches)); ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="aggregate.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  KARNATAKA BILLS  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">KA
									Bills<?php echo " (" . ($bang_bills + $kar_bills) . ")"; ?></h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins"></span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Bangalore</small>
									<h4><i class="fa fa-user"></i> <?php echo $bang_bills; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> Karnataka</small>
									<h4><i class="fa fa-user"></i> <?php echo $kar_bills; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  TAMILNADU BILLS  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">TN
									Bills<?php echo " (" . ($chn_bills + $tn_bills) . ")"; ?></h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins"></span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Chennai</small>
									<h4><i class="fa fa-user"></i> <?php echo $chn_bills; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> TamilNadu</small>
									<h4><i class="fa fa-user"></i> <?php echo $tn_bills; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  AP/T BILLS  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">AP/T
									Bills<?php echo " (" . ($hyd_bills + $apt_bills) . ")"; ?></h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins"></span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Hyderabad</small>
									<h4><i class="fa fa-user"></i> <?php echo $hyd_bills; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> AP/T</small>
									<h4><i class="fa fa-user"></i> <?php echo $apt_bills; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

		</div>

		<div class="row">

			<!--  TOTAL GOLD  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Total Gold</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Gross Weight</small>
									<h4> <?php echo number_format(($bang_grossW + $chn_grossW + $hyd_grossW + $kar_grossW + $tn_grossW + $apt_grossW), 2); ?>
									</h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Net Amount</small>
									<h4><?php echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", ($bang_netA + $chn_netA + $hyd_netA + $kar_netA + $tn_netA + $apt_netA)); ?>
									</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="aggregate.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  KARNATAKA GOLD  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success"> KA Gold</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Bangalore</small>
									<h4>
										<?php
										$margin = ($bang_grossA == 0) ? 0 : ROUND((($bang_grossA - $bang_netA) / $bang_grossA) * 100, 1);
										echo $bang_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> Karnataka</small>
									<h4>
										<?php
										$margin = ($kar_grossA == 0) ? 0 : ROUND((($kar_grossA - $kar_netA) / $kar_grossA) * 100, 1);
										echo $kar_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  TN GOLD  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success"> TN Gold</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Chennai</small>
									<h4>
										<?php
										$margin = ($chn_grossA == 0) ? 0 : ROUND((($chn_grossA - $chn_netA) / $chn_grossA) * 100, 1);
										echo $chn_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> Tamilnadu</small>
									<h4>
										<?php
										$margin = ($tn_grossA == 0) ? 0 : ROUND((($tn_grossA - $tn_netA) / $tn_grossA) * 100, 1);
										echo $tn_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  AP/T GOLD  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success"> AP/T Gold</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label"> Hyderabad</small>
									<h4>
										<?php
										$margin = ($hyd_grossA == 0) ? 0 : ROUND((($hyd_grossA - $hyd_netA) / $hyd_grossA) * 100, 1);
										echo $hyd_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label"> AP/TS</small>
									<h4>
										<?php
										$margin = ($apt_grossA == 0) ? 0 : ROUND((($apt_grossA - $apt_netA) / $apt_grossA) * 100, 1);
										echo $apt_grossW . " (" . $margin . ")";
										?>
									</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

		</div>

		<div class="row">

			<!--  TOTAL SILVER  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Total Silver</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-balance-scale  fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Gross Weight</small>
									<h5><i class="fa fa-balance-scale"></i> <?php echo $sil_grossW; ?></h5>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Net Amount</small>
									<h5><?php echo $sil_netA; ?></h5>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="silverReports.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  IMPS/CASH  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Transaction</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins"></span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Cash</small>
									<h5><?php echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", ($cashAmount + $otherData['relCash'])); ?>
									</h5>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">IMPS</small>
									<h5><?php echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", ($impsAmount + $otherData['relIMPS'])); ?>
									</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

			<!-- Margin -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Margin</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-rupee fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Margin</small>
									<h5>
										<?php
										$totalGrossA = $bang_grossA + $chn_grossA + $hyd_grossA + $kar_grossA + $tn_grossA + $apt_grossA;
										$margin = ($totalGrossA == 0) ? 0 : ROUND(($marginAmount / $totalGrossA) * 100, 1);
										echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $marginAmount) . " (" . $margin . ")";
										?>
									</h5>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Expenses</small>
									<h5><?php echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $otherData['Expense']); ?>
									</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>

			<!--  CASH/IMPS TOTAL TYPE  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">Type</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-users fa-2x"></i>
							</div>
						</div>
						<span class="font-bold no-margins">
						</span>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Cash</small>
									<h5><i class="fa fa-money"></i> <?php echo $cashCount; ?></h5>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">IMPS</small>
									<h5><i class="fa fa-bank"></i> <?php echo $impsCount; ?></h5>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="viewbill.php">View Details</a>
					</div>
				</div>
			</div>

		</div>

		<div class="row" style="margin-bottom: 20px">

			<!--   ALL STATE ZONAL WISE ENQUIRY   -->
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Today's Zonal Wise Enquiry
					</div>
					<div class="panel-body list">
						<div class="list-item-container">
							<?php
							// today's date
							$today = date("Y-m-d");

							// Fetch enquiry count zone-wise with employee name
							$zonalEnquiry = mysqli_query($con, "
							SELECT
								COALESCE(e.name, 'No Zone Assigned') AS zoneName,
								COALESCE(COUNT(DISTINCT w.id), 0) AS enquiryCount
							FROM branch b
							LEFT JOIN employee e
								ON b.ezviz_vc = e.empId
							LEFT JOIN walkin w
								ON w.branchId = b.branchId
							AND DATE(w.date) = '$today'
							AND w.issue <> 'Rejected'
							GROUP BY e.empId, e.name
							ORDER BY enquiryCount DESC, zoneName ASC
						");


							$i = 1;
							while ($rowEnquiry = mysqli_fetch_assoc($zonalEnquiry)) {
								?>
								<div class="list-item"
									style="display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #eee;">
									<span><b><?php echo $i; ?>.</b> <?php echo $rowEnquiry['zoneName']; ?></span>
									<span>Enquiries: <b><?php echo $rowEnquiry['enquiryCount']; ?></b></span>
								</div>
								<?php
								$i++;
							}
							?>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>&nbsp;
					</div>
				</div>
			</div>


			<!--   ALL STATE ZONAL WISE GRAM COG   -->

			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Today's Zonal Wise COG
					</div>
					<div class="panel-body list">
						<div class="list-item-container">
							<?php
							// today's date
							$today = date("Y-m-d");

							$zonalCog = mysqli_query($con, "
                    SELECT 
                        IFNULL(e.name, 'No Zone Assigned') AS zoneName,
						  COALESCE(
                        ROUND(
                            (COALESCE(SUM(t.totalNetW), 0) / 
                            NULLIF(SUM(w.totalWalkinNwt) + SUM(t.totalNetW), 0)) * 100, 
                            2
							),
                        0) AS cogPercent
                    FROM branch b
                    LEFT JOIN employee e 
                        ON b.ezviz_vc = e.empId

                    -- Aggregate walkin per branch
                    LEFT JOIN (
                        SELECT branchId, SUM(nwt) AS totalWalkinNwt
                        FROM walkin
                        WHERE DATE(date) = '$today'
                          AND issue != 'Rejected'
                        GROUP BY branchId
                    ) w ON b.branchId = w.branchId

                    -- Aggregate trans per branch
                    LEFT JOIN (
                        SELECT branchId, SUM(netW) AS totalNetW
                        FROM trans
                        WHERE DATE(date) = '$today'
                          AND status = 'Approved'
                        GROUP BY branchId
                    ) t ON b.branchId = t.branchId

                    GROUP BY e.empId, e.name
                    ORDER BY cogPercent DESC
                ");

							$i = 1;
							while ($row = mysqli_fetch_assoc($zonalCog)) {
								?>
								<div class="list-item"
									style="display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #eee;">
									<span><b><?php echo $i; ?>.</b> <?php echo $row['zoneName']; ?></span>
									<span>COG : <b><?php echo $row['cogPercent']; ?>%</b></span>
								</div>
								<?php
								$i++;
							}
							?>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>&nbsp; <i class="fa fa-angle-double-right"></i>
						<a href="overallReport.php">View Details</a>
					</div>
				</div>
			</div>

			<!--  EACH BRANCH GOLD REPORT  -->
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Branch Details
					</div>
					<div class="panel-body list">
						<div class="stats-title pull-left">
							<div class="col-xs-10">
								<input list="branchList" class="form-control" id="brname" placeholder="Branch" />
							</div>
							<div class="col-xs-2">
								<a class="btn btn-success" id="branchIDsearch"><i style="color:#FFFFFF"
										class="fa fa-search"></i></a>
							</div>
						</div>
						<div class="list-item-container" style="margin-top:23px;">
							<div id="branchName_Id" style="text-align:center;">

							</div>
							<div class="list-item">
								<h6 class="no-margins font-extra-bold">Gross Weight</h6>
								<div class="pull-right"><i class="fa fa-balance-scale"></i><b><span
											id="grossWeight"></span></b></div>
							</div>
							<div class="list-item">
								<h6 class="no-margins font-extra-bold">Net Amount</h6>
								<div class="pull-right"><i class="fa fa-rupee"></i><b><span id="netAMount"></span></b>
								</div>
							</div>
							<div class="list-item">
								<h6 class="no-margins font-extra-bold">Gold Stock</h6>
								<div class="pull-right"><i class="fa fa-balance-scale"></i><b><span
											id="goldRemain"></span></b></div>
							</div>
							<div class="list-item">
								<h6 class="no-margins font-extra-bold">Balance Amount</h6>
								<div class="pull-right"><i class="fa fa-rupee"></i><b><span id="balance"></span></b>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="dailyClosingR.php">View Details</a>
					</div>
				</div>
			</div>

			
			<!--  TOP BRANCHES EXPENSE   -->
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Top Expense Branch
					</div>
					<div class="panel-body list">
						<div class="list-item-container">
							<?php
							$topExpense = mysqli_query($con, "SELECT ROUND(SUM(amount),2) AS expense,b.branchName,e.branchCode FROM expense e,branch b WHERE e.branchCode=b.branchId AND e.date='$date' AND e.status='Approved' GROUP BY branchCode,b.branchName ORDER BY expense DESC LIMIT 3");
							$i = 1;
							while ($rowExpense = mysqli_fetch_assoc($topExpense)) {
								$rowEmployee2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT e.name,e.contact FROM employee e,users u WHERE u.username='$rowExpense[branchCode]' AND u.employeeId=e.empId"));
								?>
								<div class="list-item">
									<h3 class="no-margins font-extra-bold text-success">
										<?php echo $i . ". " . $rowExpense['branchName']; ?>
									</h3>
									<small><b><?php echo $rowEmployee2['name']; ?></b> <a
											href="tel:<?php echo $rowEmployee2['contact']; ?>"><i style="color:#009900"
												class="fa fa-phone-square fa-2x"></i></a></small>
									<div class="pull-right"><i class="fa fa-rupee"></i>
										<b><?php echo $rowExpense['expense']; ?></b>
									</div>
								</div>
								<?php $i++;
							} ?>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>&nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="topbranches.php">View Details</a>
					</div>
				</div>
			</div>

				<!--   TOP GOLD REMAINING BRANCH   -->
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Gold Remaining Branches
					</div>
					<div class="panel-body list">
						<div class="list-item-container">
							<?php
							$goldRemainBranch = mysqli_query($con, "SELECT ROUND(SUM(t.grossW),2) AS nw,b.branchName,t.branchId FROM trans t,branch b WHERE t.branchId=b.branchId AND t.sta='' AND t.staDate='' AND t.status='Approved' AND t.metal='Gold' GROUP BY t.branchId,b.branchName ORDER BY nw DESC LIMIT 3");
							$i = 1;
							while ($rowGoldRemaining = mysqli_fetch_assoc($goldRemainBranch)) {
								$rowEmployee3 = mysqli_fetch_assoc(mysqli_query($con, "SELECT e.name,e.contact FROM employee e,users u WHERE u.username='$rowGoldRemaining[branchId]' AND u.employeeId=e.empId"));
								?>
								<div class="list-item">
									<h3 class="no-margins font-extra-bold text-success">
										<?php echo $i . ". " . $rowGoldRemaining['branchName']; ?>
									</h3>
									<small><b><?php echo $rowEmployee3['name']; ?></b> <a
											href="tel:<?php echo $rowEmployee3['contact']; ?>"><i style="color:#009900"
												class="fa fa-phone-square fa-2x"></i></a></small>
									<div class="pull-right"><i class="fa fa-balance-scale"></i>
										<b><?php echo $rowGoldRemaining['nw']; ?></b>
									</div>
								</div>
								<?php $i++;
							} ?>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>&nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="topbranches.php">View Details</a>
					</div>
				</div>
			</div>


			<!--  TOP BRANCHES  -->
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						Top Business Branch
					</div>
					<div class="panel-body list">
						<div class="list-item-container">
							<?php
							$topBranch = mysqli_query($con, "SELECT ROUND(SUM(t.grossW),2) AS gw,b.branchName,t.branchId FROM trans t,branch b WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' AND t.metal='Gold' GROUP BY t.branchId,b.branchName ORDER BY gw DESC LIMIT 3");
							$i = 1;
							while ($rowTB = mysqli_fetch_assoc($topBranch)) {
								$rowEmployee1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT e.name,e.contact FROM employee e,users u WHERE u.username='$rowTB[branchId]' AND u.employeeId=e.empId"));
								?>
								<div class="list-item">
									<h3 class="no-margins font-extra-bold text-success">
										<?php echo $i . ". " . $rowTB['branchName']; ?>
									</h3>
									<small><b><?php echo $rowEmployee1['name']; ?></b> <a
											href="tel:<?php echo $rowEmployee1['contact']; ?>"><i style="color:#009900"
												class="fa fa-phone-square fa-2x"></i></a></small>
									<div class="pull-right"><i class="fa fa-balance-scale"></i>
										<b><?php echo $rowTB['gw']; ?></b>
									</div>
								</div>
								<?php $i++;
							} ?>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a
							href="topbranches.php">View Details</a>
					</div>
				</div>
			</div>

		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function () {
			$("#branchIDsearch").click(function () {
				var c = $('#brname').val();
				if (c != '') {
					var branchName = $('#branchList option[value=' + c + ']').text();
					$('#branchName_Id').html("<b>" + branchName + "</b>");
					$('#brname').val('');
					var $req = $.ajax({
						url: "dashboardAjax.php?branchId=" + c,
						type: "GET",
						dataType: 'JSON',
						success: function (response) {
							var len = response.length;
							for (var i = 0; i < len; i++) {
								var gross = response[i].gross;
								var net = response[i].net;
								var gold = response[i].gold;
								var balance = response[i].balance;
								$("#grossWeight").html(gross);
								$("#netAMount").html(net);
								$("#goldRemain").html(gold);
								$("#balance").html(balance);
							}
						}
					});
				}
			});
		});
	</script>
	<?php include("footer.php"); ?>
