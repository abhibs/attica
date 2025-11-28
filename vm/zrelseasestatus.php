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
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Gold Release (Cash/IMPS)
						<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-success"><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</button>
					</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>Branch Name</th>
									<th>Time</th>
									<th>Name</th>
									<th>Type</th>
									<th>Amount</th>
									<th>Release Type</th>
									<th>Release Place</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
								    $i = 1;
									$query = mysqli_query($con,"SELECT b.branchName,r.name,r.relPlaceType,r.relPlace,r.type,r.amount,r.status,r.time 
									FROM releasedata r,branch b 
									WHERE r.date='$date' AND r.branchId IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND r.branchId!='' AND r.branchId=b.branchId");
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['time'] . "</td>";
										echo "<td>" . $row['name'] . "</td>";										
										echo "<td>" . $row['type'] . "</td>";
										echo "<td>" . $row['amount'] . "</td>";
										echo "<td>" . $row['relPlaceType'] . "</td>";
										echo "<td>" . $row['relPlace'] . "</td>";
										echo "<td>" . $row['status'] . "</td>";
										echo "</tr>";
										$i++;
									}
								?>
							</tbody>
							<div style="clear:both"></div>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footerNew.php"); ?>
