<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
// 	if ($type == 'VM-HO') {
// 		include("headervc.php");
// 		include("menuvc.php");
// 	}
// 	else {
		include("logout.php");
// 	}
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
	
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT agentId,language,grade FROM vmagent WHERE agentId='$empId'"));
	$languages = explode(",", $vmBranchList['language']);
	
	$query = mysqli_query($con, "SELECT e.Id, e.customer, e.contact, e.extra, e.time, e.branch, e.status, b.branchName, b.grade, b.ezviz_vc 
	FROM everycustomer e 
	JOIN branch b ON e.branch = b.branchId
	WHERE agent='$empId' AND e.date='$date'
	ORDER BY e.Id DESC");
?>
<style>
	form h5{
	color: #123C69;
	text-transform: uppercase;
	font-size: 12px;
	}
	.data-A{
	background-color: #62cb31;
	border: #62cb31;
	}
	.data-B{
	background-color: #3498db;
	border: #3498db;
	}
	.data-C{
	background-color: #e74c3c;
	border: #e74c3c;
	}
	.row-data-A{
	background-color: #d5efcc;
	}
	.row-data-B{
	background-color: #eaeaae;
	}
	.row-data-C{
	background-color: #f1cbc8;
	}
</style>

<div id="wrapper">	
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-bottom: 0px;">
				<div class="panel-body" style="padding: 9px;">
					<div class="col-lg-1">
						<?php
							if($vmBranchList['grade'] == "A"){
								echo '<button class="btn btn-success data-A" type="button"><i class="fa fa-star"></i> <span class="bold">'.$vmBranchList['grade'].' GRADE</span></button>';
							}
							else if($vmBranchList['grade'] == "B"){
								echo '<button class="btn btn-success data-B" type="button"><i class="fa fa-star"></i> <span class="bold">'.$vmBranchList['grade'].' GRADE</span></button>';
							}
							else if($vmBranchList['grade'] == "C"){
								echo '<button class="btn btn-success data-C" type="button"><i class="fa fa-star"></i> <span class="bold">'.$vmBranchList['grade'].' GRADE</span></button>';
							}
						?>
					</div>
					<div class="col-lg-11 text-right">
						<?php
							foreach($languages as $lang){
								echo '<button type="button" class="btn btn-outline btn-info" style="margin-right: 5px;">'.$lang.'</button>';
							}
						?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h4 class="text-success">
						<i class="fa_Icon fa fa-check-square-o"></i> Registered Customers
					</h4>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="registeredTable" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th class="text-center">Grade</th>
									<th>Branch</th>
									<th>Customer</th>
									<th>Mobile</th>
									<th>Language</th>
									<th>GrossW</th>
									<th>Time</th>
									<th>Code</th>
									<th style="text-align:center;">Action</th>
									<th class="text-center">Status</th>
								</tr>
							</thead>
							<tbody>	
								<?php 
									$i = 1;
									while($row = mysqli_fetch_assoc($query)){
										$extra = json_decode($row['extra'], true);
										
										if($row['grade'] == "A"){
											echo "<tr class='row-data-A'>";	
										}
										else if($row['grade'] == "B"){
											echo "<tr class='row-data-B'>";	
										}
										else if($row['grade'] == "C"){
											echo "<tr class='row-data-C'>";
										}
										
										echo "<td>" . $i . "</td>";	
										echo "<td class='text-center text-success'><b>" . $row['grade'] . "</b></td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['customer'] . "</td>";
										echo "<td>" . $row['contact'] . "</td>";
										echo "<td>" . $extra['Language'] ."</td>";
										echo "<td>" . $extra['GrossW'] ."</td>";
										echo "<td>" . $row['time'] . "</td>";
										echo "<td><span class='ezviz' style='cursor: pointer'>" . $row['ezviz_vc'] . "</span></td>";
										
										if($row['status'] == "Begin"){
											echo "<td style='text-align:center'><a style='padding: 0px;' href='updateRegistered.php?id=".$row['Id']."' class='btn' type='button'><i class='fa fa-pencil-square-o text-success'style='font-size:16px'></i></a></td>";
										}
										else{
											echo "<td style='text-align:center'><a style='padding: 0px;' href='updateRegistered.php?id=".$row['Id']."' ><b>View</b></a></td>";
										}
										
										echo "<td class='text-center'>";
										if($row['status'] == "Begin"){
											echo "<b style='color: green'>Registered<b>";
										}
										else if($row['status'] == "0"){
											echo "<i style='color: red'>Waiting<i>";
										}
										else{
											echo $row['status'];
										}
										echo "</td>";
										
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
	<?php include("footerNew.php"); ?>
	<script>
		$('#registeredTable').DataTable( {
			paging: false,
			"ajax": '',
			dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
			"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
			buttons: []
		} );
	</script>
	<script>
		(function(){
			const ezviz = document.querySelectorAll(".ezviz");
			ezviz.forEach((ez, i)=>{
				ez.addEventListener("click", (e)=>{
					var inp =document.createElement('input');
					document.body.appendChild(inp);
					inp.value = ez.textContent;
					inp.select();
					document.execCommand('copy',false);
					inp.remove();
				})
			})
		})();
	</script>	