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
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT agentId,branch FROM vmagent WHERE agentId='$empId'"));
	$branches = explode(",", $vmBranchList['branch']);
	
	$bmData = mysqli_query($con, "SELECT Y.branchId,Y.branchName,X.name,X.contact,Y.meet FROM
	(SELECT A.branch,B.empId,B.name,B.contact FROM
	(SELECT branch,employeeId FROM users WHERE branch IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]')) A
	INNER JOIN
	(SELECT empId,name,contact FROM employee) B
	ON A.employeeId = B.empId) X
	INNER JOIN
	(SELECT branchId,branchName,meet FROM branch) Y 
	ON X.branch = Y.branchId");
	
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h3 class="text-success"><i class="fa_Icon fa fa-user-circle-o"></i> BM Details </h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>Branch</th>
									<th>Branch Manager</th>
									<th>Meet URL</th>
								</tr>
							</thead>
							<tbody>
								<?php
									while($row = mysqli_fetch_assoc($bmData)){
										echo "<tr class='parent'>";
										echo "<td><span class='text-success'>".$row['branchName']."</span><br><small>".$row['branchId']."<small></td>";
										echo "<td>".$row['name']."<br><div style='margin-top: 10px;'><i class='fa fa-phone' style='color: #990000'></i> ".$row['contact']."<div></td>";
										echo "<td><span class='text-success'>Link : </span><span class='url_content'>".$row['meet']."</span>
										<br>
										<div class='form-group' style='margin-top: 10px;'>
										<div class='input-group'>
										<input type='text' class='url_data form-control' style='background-color: transparent;' placeholder='Paste the new url'>	
										<span class='input-group-btn'>
										<button class='btn btn-primary' onclick='updateURL(this)' data-branch='".$row['branchId']."'>ok</button>
										</span>
										</div>
										</div>
										</td>";
										echo "</tr>";
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
	<script>
		function updateURL(button){
			const parent_tr = button.closest(".parent");	
			const url_input = parent_tr.querySelector(".url_data");
			const url_content = parent_tr.querySelector(".url_content");
			
			const url = url_input.value;
			const branchId = button.dataset.branch;
			
			if(url !== ""){
				var req = $.ajax({
					url: "zvmn.php",
					type: "POST",
					data: {
						url: url,
						branchId: branchId,
						updateBranchMeetURL: true
					}
				});
				req.done(function(e) {
					if(e == 'SUCCESS'){
						url_input.value = "";
						url_content.textContent = url;
						alert("Meet URL succesfully updated");
					}
					else if(e == 'ERROR'){
						alert("Error Occurred !!! Please Try Again");
					}
					else{
						console.log(e);
					}
				});
			}			
			
		}
	</script>