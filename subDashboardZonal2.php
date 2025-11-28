<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
$empId = $_SESSION['employeeId'];
if ($type == 'SubZonal') {
	include("header.php");
	include("menuSubZonal.php");
} else {
	include("logout.php");
}
date_default_timezone_set("Asia/Kolkata");
$date = date('Y-m-d');
include("dbConnection.php");

$cityCount = 0;
$stateCount = 0;

$cityGrossW = 0;
$cityNetW = 0;
$cityGrossA = 0;
$cityNetA = 0;

$stateGrossW = 0;
$stateNetW = 0;
$stateGrossA = 0;
$stateNetA = 0;

$silverCount = 0;
$silverGrossW = 0;
$silverNetW = 0;

if ($_SESSION['branchCode'] == 'AP-TS') {
	$stateFull = "('Telangana','Andhra Pradesh')";
	$city = "Hyderabad";
	$state = 'AP & TS';
	$cityGoldName = 'Telangana';
	$stateGoldName = 'Andhra Pradesh';
} else if ($_SESSION['branchCode'] == 'KA') {
	$stateFull = "('Karnataka')";
	$city = "Bengaluru";
	$state = 'Karnataka';
	$cityGoldName = 'Bangalore';
	$stateGoldName = 'Karnataka';
} else if ($_SESSION['branchCode'] == 'TN') {
	$stateFull = "('Tamilnadu','Pondicherry')";
	$city = "Chennai";
	$state = 'TN & PY';
	$cityGoldName = 'Chennai';
	$stateGoldName = 'Tamilnadu';
}


/* TRANS DATA */
$transSQL = "SELECT ROUND(t.grossW, 2) AS grossW, ROUND(t.netW, 2) AS netW, ROUND(t.grossA, 2) AS grossA, ROUND(t.netA, 2) AS netA, t.metal, b.state, b.city
	FROM  trans t, branch b
	WHERE t.branchId=b.branchId AND t.date='$date' AND t.status='Approved' AND b.state IN " . $stateFull;
$transQuery = mysqli_query($con, $transSQL);
while ($row = mysqli_fetch_assoc($transQuery)) {
	if ($row['metal'] == 'Gold') {
		if ($row['city'] == $city) {
			$cityCount++;
			$cityGrossW += $row['grossW'];
			$cityNetW += $row['netW'];
			$cityGrossA += $row['grossA'];
			$cityNetA += $row['netA'];
		} else {
			$stateCount++;
			$stateGrossW += $row['grossW'];
			$stateNetW += $row['netW'];
			$stateGrossA += $row['grossA'];
			$stateNetA += $row['netA'];
		}
	} else if ($row['metal'] == 'Silver') {
		$silverCount++;
		$silverGrossW += $row['grossW'];
		$silverNetW += $row['netW'];
	}
}

/* WALKIN DATA */
$walkin = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS walkin
	FROM walkin 
	WHERE date='$date' AND branchId IN (SELECT branchId FROM branch WHERE state IN " . $stateFull . ")"));

$totalWalkout = mysqli_fetch_assoc(mysqli_query($con, "
	SELECT COUNT(*) AS sold FROM
	(SELECT DISTINCT mobile FROM walkin WHERE date='$date' AND branchId IN (SELECT branchId FROM branch WHERE state IN " . $stateFull . ")) A
	INNER JOIN
	(SELECT DISTINCT phone FROM trans WHERE status='Approved' AND date='$date') B
	on A.mobile = B.phone"));

/* GOLD RATE */
$cityGoldRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash, transferRate FROM gold WHERE date='$date' AND type='Gold' AND city='" . $cityGoldName . "' ORDER BY id DESC LIMIT 1"));
$stateGoldRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash, transferRate FROM gold WHERE date='$date' AND type='Gold' AND city='" . $stateGoldName . "' ORDER BY id DESC LIMIT 1"));
$citySilverRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash FROM gold WHERE date='$date' AND type='Silver' AND city='" . $cityGoldName . "' ORDER BY id DESC LIMIT 1"));
$stateSilverRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash FROM gold WHERE date='$date' AND type='Silver' AND city='" . $stateGoldName . "' ORDER BY id DESC LIMIT 1"));


if (isset($_GET['weekday']) && isset($_GET['branch']) && !empty($_GET['weekday']) && !empty($_GET['branch'])) {
	$weekday = $_GET['weekday'];
	$branchId = $_GET['branch'];

	// Assuming you already have your DB connection in $conn
	$stmt = $con->prepare("
        SELECT AVG(grossW) AS avgTop12GrossW
        FROM (
            SELECT grossW
            FROM trans
            WHERE DAYNAME(date) = ?
              AND branchId = ?
              AND grossW IS NOT NULL
            ORDER BY grossW DESC
            LIMIT 12
        ) AS top12
    ");

	$stmt->bind_param("ss", $weekday, $branchId);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result && $row = $result->fetch_assoc()) {
		$average = $row['avgTop12GrossW'];
		echo "<h3>Average of Top 12 grossW for <strong>$weekday</strong> (Branch: <strong>$branchId</strong>): " . number_format($average, 2) . "</h3>";
	} else {
		echo "<p>No data found for the selected day and branch.</p>";
	}

	$stmt->close();
}

$otherData = mysqli_fetch_assoc(mysqli_query($con, "SELECT
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='with' AND branchId !='' AND status!='Rejected') AS withGold,
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='without' AND branchId !='' AND status!='Rejected') AS withoutGold,
	(SELECT SUM(amount) FROM expense WHERE date='$date' AND status='Approved') AS Expense,
	(SELECT SUM(relCash) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relCash,
	(SELECT SUM(relIMPS) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relIMPS,
	(SELECT cash FROM gold WHERE type='Gold' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldCashRate,
	(SELECT transferRate FROM gold WHERE type='Gold' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldIMPSRate,
	(SELECT cash FROM gold WHERE type='Silver' AND city='Bangalore' AND date='$date' ORDER BY id DESC LIMIT 1) AS silverRate
	"));

	$zonalQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, empId FROM employee WHERE empId='$_SESSION[employeeId]' LIMIT 1"));


?>
<style type="text/css">
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

	.stats-icon>.fa {
		margin-right: 10px;
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row m-t-md box-wrap">
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body text-center" style="padding-bottom: 36px;">
						<h3 class="font-bold text-success"><?php echo $zonalQuery['name']; ?></h3>
						<h3 class="font-bold text-success"><?php echo $zonalQuery['empId']; ?></h3>

						<a class="text-success" href="subDashboardZonalPersonal.php">Your Dashboard <i class="fa fa-angle-double-right"></i></a>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>


			<!--  ZONAL COG  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success"><?php echo $city; ?> Rate</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-rupee fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Gold</small>
									<h4><i class="fa fa-rupee"></i> <?php echo $cityGoldRate['cash'] . " / " . $cityGoldRate['transferRate']; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Silver</small>
									<h4><i class="fa fa-rupee"></i> <?php echo $citySilverRate['cash']; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="#">View Details</a>
					</div>
				</div>
			</div>

			<!--  STATE RATE  -->
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success"><?php echo $state; ?> Rate</h3>
							</div>
							<div class="stats-icon pull-right">
								<i class="fa fa-rupee fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">Gold</small>
									<h4><i class="fa fa-rupee"></i> <?php echo $stateGoldRate['cash'] . " / " . $stateGoldRate['transferRate']; ?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">Silver</small>
									<h4><i class="fa fa-rupee"></i> <?php echo $stateSilverRate['cash']; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="#">View Details</a>
					</div>
				</div>
			</div>
	<?php include("footer.php"); ?>
