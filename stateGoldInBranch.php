<?php
session_start();
$type = $_SESSION['usertype'] ?? '';
$code = $_SESSION['branchCode'] ?? '';

if ($type !== 'SubZonal') {
	include("logout.php");
	exit;
}

include("header.php");
include("menuSubZonal.php");
include("dbConnection.php");

function h($s)
{
	return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* --- State filter from session --- */
$stateFilter = '';
if ($code === "TN") {
	$stateFilter = "AND b.state IN ('Tamilnadu','Pondicherry')";
} elseif ($code === "KA") {
	$stateFilter = "AND b.state = 'Karnataka'";
} elseif ($code === "AP-TS") {
	$stateFilter = "AND b.state IN ('Andhra Pradesh','Telangana')";
}

/* --- Optional: filter for today (set your real date column) --- */
// $stateFilter .= " AND DATE(t.tranDate) = CURDATE()";

/* --- Branch-wise rows incl. State --- */
$sql = "
    SELECT 
        b.branchId,
        b.branchName,
        b.state,
        COUNT(*)                         AS bills,
        ROUND(COALESCE(SUM(t.grossW),0), 2) AS grossW
    FROM trans t
    JOIN branch b 
      ON b.branchId = t.branchId
    WHERE t.sta = '' 
	  AND t.staDate = ''
      AND t.status = 'Approved'
      AND t.metal = 'Gold'
      AND b.status = 1
      AND b.branchId NOT IN ('AGPL000','AGPL999')
      $stateFilter
    GROUP BY b.branchId, b.branchName, b.state
    ORDER BY b.state, b.branchName
";

$result = mysqli_query($con, $sql);
$sql_error = $result ? '' : mysqli_error($con);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>State-wise Details</title>
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

		.theadRow {
			text-transform: uppercase;
			background-color: #123C69 !important;
			color: #f2f2f2;
			font-size: 11px;
		}

		tfoot tr td {
			font-weight: 600;
			background: #fafafa;
		}

		.fa_Icon {
			color: #990000;
		}
	</style>
</head>

<body>
	<div id="wrapper">
		<div class="row content">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<div class="panel-tools"><a class="showhide"><i class="fa fa-chevron-up"></i></a></div>
						<h3><b><i class="fa_Icon fa fa-institution"></i> State-wise Details &nbsp;</b></h3>
					</div>
					<div class="panel-body">

						<?php if (!$result): ?>
							<div class='alert alert-danger'>Query error: <?= h($sql_error ?: 'Unknown error') ?></div>
						<?php endif; ?>

						<?php
						$totalBills = 0;
						$totalGross = 0.0;
						?>

						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered table-hover">
								<thead class="theadRow">
									<tr>
										<th>#</th>
										<th>Branch ID</th>
										<th>Branch Name</th>
										<th>State</th>
										<th>Packets</th>
										<th>Gold (gms)</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($result && mysqli_num_rows($result) > 0) {
										$i = 1;
										while ($row = mysqli_fetch_assoc($result)) {
											$branchId   = $row['branchId'] ?? '';
											$branchName = $row['branchName'] ?? '';
											$state      = $row['state'] ?? 'Unknown';
											$bills      = (int)($row['bills'] ?? 0);
											$gross      = (float)($row['grossW'] ?? 0);

											$totalBills += $bills;
											$totalGross += $gross;

											echo "<tr>";
											echo "<td>" . ($i++) . "</td>";
											echo "<td>" . h($branchId) . "</td>";
											echo "<td>" . h($branchName) . "</td>";
											echo "<td>" . h($state) . "</td>";
											echo "<td>" . h($bills) . "</td>";
											echo "<td>" . number_format($gross, 2) . "</td>";
											echo "</tr>";
										}
									} else {
										echo "<tr><td colspan='6' class='text-center'>No records found.</td></tr>";
									}
									?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4" class="text-center">Total</td>
										<td><?= h($totalBills) ?></td>
										<td><?= number_format($totalGross, 2) ?></td>
									</tr>
								</tfoot>
							</table>
						</div>

					</div>
				</div>
			</div>
		</div>
		<?php include("footer.php"); ?>
	</div>
</body>

</html>
