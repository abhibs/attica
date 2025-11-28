<?php	
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT agentId,branch FROM vmagent WHERE agentId='$empId'"));
	$branches = explode(",", $vmBranchList['branch']);
	
	$expPending = [];
	$expVerified = [];
	$expApproved = [];
	$expRejected = [];
	
	$exp = mysqli_query($con,"SELECT b.branchName,a.*
	FROM
	(SELECT * FROM expense WHERE branchCode IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND date='$date' AND branchCode!='') a 
	INNER JOIN
	(SELECT branchId,branchName FROM branch WHERE branchId IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND branchId!='') b
	ON a.branchCode=b.branchId");
	while($row = mysqli_fetch_assoc($exp)){
		if($row['status'] == 'Pending'){
			$expPending[] = $row;
		}
		else if($row['status'] == 'Verified'){
			$expVerified[] = $row;
		}
		else if($row['status'] == 'Approved'){
			$expApproved[] = $row;
		}
		else if($row['status'] == 'Rejected'){
			$expRejected[] = $row;
		}
	}
	
?>
<div id="wrapper">
	<div class="content row">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_Icon fa fa-money"></i> BRANCH EXPENSE DETAILS
						<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b></button>
					</h3>
				</div>
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#tab-1" class="text-success"><i class="fa_Icon fa fa-refresh"></i> PENDING </a></li>
					<li class=""><a data-toggle="tab" href="#tab-2" class="text-success"><i class="fa_Icon fa fa-check"></i> VERIFIED </a></li>
					<li class=""><a data-toggle="tab" href="#tab-3" class="text-success"><i class="fa_Icon fa fa-check-square-o"></i> APPROVED </a></li>
					<li class=""><a data-toggle="tab" href="#tab-4" class="text-success"><i class="fa_Icon fa fa-times"></i> REJECTED </a></li>
				</ul>
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th>#</th>
											<th>BranchName</th>
											<th>Particular</th>
											<th>Type</th>
											<th>Amount</th>
											<th>Date/Time</th>
											<th colspan="2" class="text-center">Bills</th>
											<th colspan="2" class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											$i = 1;
											foreach($expPending as $key=>$value){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $value['branchName'] . "</td>";
												echo "<td>" . $value['type'] . "</td>";
												echo "<td>" . $value['particular'] . "</td>";
												echo "<td>" . $value['amount'] . "</td>";
												echo "<td>" . $value['date'] . "<br>" .$value['time']. "</td>";
												echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill1</a></td>";
												if ($value['file1'] != "") {
													echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file1'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill2</a></td>";
												}
												else {
													echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> Bill2</a></td>";
												}
												echo "<td class='text-center'><b><a class='btn btn-success' href='xviewExpenseDetails.php?id=" . $value['id'] . "'><i style='color:#ffcf40' class='fa fa-check'></i> Verify</a></b></td>";
												echo "<td class='text-center'><a class='btn btn-success' href='deleteexpense.php?id=" . $value['id'] . "' onClick=\"javascript: return confirm('Are you sure, you want to delete the expense ?');\"><i style='color:#ff0000' class='fa fa-times'></i> Delete</a></td>";
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div id="tab-2" class="tab-pane">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th>#</th>
											<th>BranchName</th>
											<th>Type</th>
											<th>Particular</th>
											<th>Requested Amount</th>
											<th>Date/Time</th>
											<th colspan="2" class="text-center">Bills</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											$i = 1;
											foreach($expVerified as $key=>$value){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $value['branchName'] . "</td>";
												echo "<td>" . $value['type'] . "</td>";
												echo "<td>" . $value['particular'] . "</td>";
												echo "<td>" . $value['amount'] . "</td>";
												echo "<td>" . $value['date'] . "<br>" .$value['time']. "</td>";
												echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill1</a></td>";
												if (($value['file1']) != "") {
													echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file1'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill2</a></td>";
												} 
												else {
													echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> Bill2</a></td>";
												}
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div id="tab-3" class="tab-pane">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th>#</th>
											<th>BranchName</th>
											<th>Type</th>
											<th>Particular</th>
											<th>Requested Amount</th>
											<th>Date/Time</th>
											<th colspan="2" class="text-center">Bills</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											$i = 1;
											foreach($expApproved as $key=>$value){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $value['branchName'] . "</td>";
												echo "<td>" . $value['type'] . "</td>";
												echo "<td>" . $value['particular'] . "</td>";
												echo "<td>" . $value['amount'] . "</td>";
												echo "<td>" . $value['date'] . "<br>" .$value['time']. "</td>";
												echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill1</a></td>";
												if (($value['file1']) != "") {
													echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file1'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill2</a></td>";
												}
												else {
													echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> Bill2</a></td>";
												}
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div id="tab-4" class="tab-pane">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th>#</th>
											<th>BranchName</th>
											<th>Type</th>
											<th>Particular</th>
											<th>Requested Amount</th>
											<th>Date/Time</th>
											<th colspan="2" class="text-center">Bills</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											$i = 1;
											foreach($expRejected as $key=>$value){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $value['branchName'] . "</td>";
												echo "<td>" . $value['type'] . "</td>";
												echo "<td>" . $value['particular'] . "</td>";
												echo "<td>" . $value['amount'] . "</td>";
												echo "<td>" . $value['date'] . "<br>" .$value['time']. "</td>";
												echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill1</a></td>";
												if (($value['file1']) != "") {
													echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='../ExpenseDocuments/" . $value['file1'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill2</a></td>";
												}
												else {
													echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> Bill2</a></td>";
												}
												echo "</tr>";
												$i++;
											}
											$expPending = null;
											$expVerified = null;
											$expApproved = null;
											$expRejected = null;
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php include("footerNew.php"); ?>
