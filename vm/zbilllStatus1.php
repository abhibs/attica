<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
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
	
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT branch FROM vmagent WHERE agentId='$empId'"));
	$branches = explode(",", $vmBranchList['branch']);
	
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Assigned Branch Transaction Details</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">									
									<th>Branch</th>
									<th>Name</th>
									<th>Type</th>
									<th>Net Weight</th>
									<th>Gross Weight</th>
									<th>Net Amount</th>
									<th>Gross Amount</th>
									<th>Amount Paid</th>
									<th>Margin Amount</th>
									<th>Time</th>
									<th>Payment Type</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$query = mysqli_query($con,"SELECT b.branchName,t.id,t.name,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.margin,t.time,t.type,t.paymentType,t.status FROM trans t,branch b WHERE t.date='$date' AND t.branchId IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND t.branchId !='' AND t.branchId=b.branchId");
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";										
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['netW']."</td>";
										echo "<td>".$row['grossW']."</td>";
										echo "<td>".$row['netA']."</td>";
										echo "<td>".$row['grossA']."</td>";
										echo "<td>".$row['amountPaid']."</td>";
										echo "<td>".$row['margin']."</td>";
										echo "<td>".$row['time']. "</td>";
										echo ($row['paymentType'] == 'NEFT/RTGS') ? "<td>IMPS</td>" : "<td>".$row['paymentType']."</td>";
										echo "<td>".$row['status']."</td>";
										if ($row['status'] == "Approved") {
											$pdf = "../Invoice.php?id=".base64_encode($row['id']);
											echo "<td><a class='btn btn-success' target='_blank' href='../Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></td>";
										}
										else if($row['status'] == "Begin"){
											echo "<td>Uploading Docs</td>";
										}
										else{
											echo "<td>".$row['status']."</td>";
										}
										echo "</tr>";										
									}
									
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
	</div>
	<?php include("footerNew.php"); ?>
	<script>
		let print = (doc) => {
			let objFra = document.createElement('iframe');
			objFra.style.visibility = 'hidden';
			objFra.src = doc; 
			document.body.appendChild(objFra);	
			objFra.contentWindow.focus();
			objFra.contentWindow.print();
		}
	</script>		
