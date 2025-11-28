<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$date = date('Y-m-d');
$relType = "ALL";

$sql = "SELECT B.branchName, R.rid, R.time, R.name, R.phone, R.type, R.amount, R.relPlace, R.status
		FROM releasedata R
		JOIN branch B ON R.BranchId = B.branchId
		WHERE R.date = '$date'
		ORDER BY R.rid ASC";

$releaseData = mysqli_query($con, $sql);
$result = mysqli_fetch_all($releaseData, MYSQLI_ASSOC);

$approvedRows = [];
$rejectedRows = [];

foreach ($result as $row) {
	if (in_array($row['status'], ["Approved", "Billed", "Terminated"])) {
		$approvedRows[] = $row;
	} elseif ($row['status'] == "Rejected") {
		$rejectedRows[] = $row;
	}
}
?>
<style>
	.tab .nav-tabs {
		border: none;
	}

	.tab .nav-tabs li a {
		color: #123C69;
		background: #E3E3E3;
		font-size: 12px;
		font-weight: 600;
		text-align: center;
		text-transform: uppercase;
		padding: 7px 10px;
		margin: 5px 5px 0 0;
		border-bottom: 3px solid #123C69;
		transition: all 0.3s;
	}

	.tab .nav-tabs li.active a {
		background: #123C69;
		color: #fff;
		border-bottom: 3px solid #ffa500;
		border-radius: 3px;
	}

	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 20px;
		color: #123C69;
	}

	thead {
		background-color: #123C69;
		font-size: 10px;
		color: #f2f2f2;
		text-transform: uppercase;
	}

	.btn-success {
		padding: 0.5em 1.0em;
		font-size: 10px;
		text-transform: uppercase;
		background-color: #123C69;
		color: #fff;
	}

	.btn-success:hover {
		background: #1c6eaf;
	}

	.hpanel .panel-body {
		box-shadow: 5px 8px 12px #999;
		border-radius: 3px;
		padding: 15px;
		background-color: #f5f5f5;
	}
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3><i class="fa fa-edit" style="color:#990000"></i> Gold Release (<?php echo $relType; ?>)</h3>
				</div>

				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active">
							<a href="#approved" aria-controls="approved" role="tab" data-toggle="tab">
								<i class="fa fa-check" style="color:#990000"></i> Approved
							</a>
						</li>
						<li role="presentation">
							<a href="#rejected" aria-controls="rejected" role="tab" data-toggle="tab">
								<i class="fa fa-times" style="color:#990000"></i> Rejected
							</a>
						</li>
					</ul>

					<div class="tab-content tabs">

						<div role="tabpanel" class="tab-pane fade active in" id="approved">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example7" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Time</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Amount</th>
												<th>Release Place</th>
												<th>Status</th>
												<th class="text-center">Details</th>
											</tr>
										</thead>
										<tbody>
											<?php $i = 1;
											foreach ($approvedRows as $row): ?>
												<tr>
													<td><?= $i++; ?></td>
													<td><?= $row['branchName']; ?></td>
													<td><?= $row['time']; ?></td>
													<td><?= $row['name']; ?></td>
													<td><?= $row['phone']; ?></td>
													<td><?= $row['type']; ?></td>
													<td><?= $row['amount']; ?></td>
													<td><?= $row['relPlace']; ?></td>
													<td><?= $row['status']; ?></td>
													<td class="text-center">
														<a class="btn btn-success btn-sm" href="xapproveReleaseData.php?id=<?= $row['rid']; ?>">
															<i class="fa fa-eye" style="color:#ffcf40"></i> View
														</a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<div role="tabpanel" class="tab-pane fade" id="rejected">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example8" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Time</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Amount</th>
												<th>Release Place</th>
											</tr>
										</thead>
										<tbody>
											<?php $i = 1;
											foreach ($rejectedRows as $row): ?>
												<tr>
													<td><?= $i++; ?></td>
													<td><?= $row['branchName']; ?></td>
													<td><?= $row['time']; ?></td>
													<td><?= $row['name']; ?></td>
													<td><?= $row['phone']; ?></td>
													<td><?= $row['type']; ?></td>
													<td><?= $row['amount']; ?></td>
													<td><?= $row['relPlace']; ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<?php include("footer.php"); ?>
	</div>
</div>
