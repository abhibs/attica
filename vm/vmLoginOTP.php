<?php
	session_start();
	$type = $_SESSION['usertype'];	
	if ($type == 'VM-HO') {
		//include("headervc.php");
		//include("menuvc.php");
		include("logout.php");
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
				<div class="panel-heading text-success">
					<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Login OTP <button style="float:right" onclick="window.location.reload();" class="btn btn-success"><i style="color:#ffcf40" class="fa fa-refresh"></i> Reload</button></h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead class="theadRow">
								<tr>
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th> Branch Name</th>
									<th> Employee</th>
									<th> OTP</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
								    $i = 1;
									$query = mysqli_query($con,"SELECT L.branch,L.empid,L.otp,L.time,B.branchName FROM loginotp L,branch B WHERE L.date='$date' AND L.branch=B.branchId AND L.branch IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND L.branch !='' ORDER by L.id DESC LIMIT 20");
									while($row = mysqli_fetch_assoc($query)){
										$empData = mysqli_fetch_assoc(mysqli_query($con,"SELECT name FROM employee WHERE empId='$row[empid]'"));
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . " - (" . $row['branch'] . ")</td>";
										echo "<td>" . $empData['name'] . " - (" . $row['empid'] . ")</td>";
										echo "<td>" . $row['otp'] . "</td>";
										echo "<td>" . $row['time'] . "</td>";
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
	<div style="clear:both"></div>
<?php include("footerNew.php"); ?>