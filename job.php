<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];		
    	if($type == 'SocialMedia'){
	    include("header.php");
		include("menuSocialMedia.php");		
    	}
    	else if ($type == 'Master') {
        include("header.php");
        include("menumaster.php");
	}
	else if ($type == 'HR') {
		include("header.php");
		include("menuhr.php");
	}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	
	$sql = "SELECT *
	FROM job 
	ORDER BY id DESC";
	
	if(isset($_GET['getdone'])){
		$date = $_GET['date'];
		
		$sql = "SELECT *
		FROM job 
		WHERE status='Done' AND DATE_FORMAT(date,'%Y-%m')='$date'
		ORDER BY id DESC";
	}
	
	$result = mysqli_query($con, $sql);
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	.table-responsive .row{
	margin: 0px;
	}
	.submit-button {
	font-size:20px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-6">
						<h3 class="text-success">Candidates Details</h3>
					</div>
					<div class="col-sm-2">
						<a href="job.php" class="btn btn-success btn-block" name="getAttendance" ><span class="fa_Icon fa fa-spinner"></span> Get Pending</a>
					</div>
					<form action="" method="GET" >
						<div class="col-sm-4">
							<div class="input-group">
								<input type="month" name="date" class="form-control" required>
								<span class="input-group-btn"> 
									<button type="submit" name="getdone" class="btn btn-success"><span class="fa_Icon fa fa-check-circle-o"></span> Get Done</button>
								</span>
							</div>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="jobTable" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th class="text-center">#</th>
									<th width="10%">Location</th>
									<th>Candidate</th>
									<th>Contact</th>
									<th>email</th>
                                                                        <th>position</th>
									<th>Date</th>
									<th>Status</th>
									<th>Resume</th>
									<?php if(!isset($_GET['getdone'])){ ?>
										<th>Done</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									while($row = mysqli_fetch_assoc($result)) {
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['location']."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['email']."</td>";
                                                                                echo "<td>".$row['type']."</td>";
										echo "<td>".$row['date']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "<td class='text-center'><a class='submit-button' target='_blank' href='jobDocuments/" . urlencode($row['resume']) . "'><i class='fa fa-file-text-o' style='color:#900;'></i></a></td>";
										if(!isset($_GET['getdone'])){
											echo '<td class="text-center"><button data-id="'.$row['id'].'" onclick="updateJobStatus(this)"><i class="fa fa-check" style="color:green;"></button></td>';
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
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
	<script>
		const dt = $('#jobTable').dataTable({
			"ajax": '',
			dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
			"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
			buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' }		
			]
		});
		
		async function updateJobStatus(btn){
			const jobId = btn.dataset.id;	
			const row = btn.parentElement.parentElement;
			
			row.style.backgroundColor = "#ce9191";
			
			const form = new FormData();
			form.append("jobId", jobId);
			form.append("updateJobStatus", true);
			
			const response = await fetch("editAjax.php", {
				method: "POST",
				body: form
			});
			const result = await response.json();
			if(result['message'] == "Error"){
				alert("Something went wrong, Please try again later");
				return;
			}
			
			row.remove();
			const Pos = dt.fnGetPosition(row);
			dt.fnDeleteRow(Pos);
		}
	</script>
