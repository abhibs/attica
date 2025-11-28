<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Zonal'){
		include("header.php");
        include("menuZonal.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
    else{
        include("logout.php");
	}
	
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	if($type=='Zonal'){
		if($_SESSION['branchCode']=="TN"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND state IN ('Tamilnadu','Pondicherry')");
		}
		else if($_SESSION['branchCode']=="KA"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND state='Karnataka' AND branchId != 'AGPL000'");
			
		}
		else if($_SESSION['branchCode']=="AP-TS"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND state IN ('Andhra Pradesh','Telangana')");
		}
	}
	else{
		$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1 AND branchId != 'AGPL000'");
	}
	
	$notiSQL = "SELECT * FROM notification WHERE date='$date' AND (sender='$type' OR receiver='$type') ORDER BY id DESC";
	$notiQuery = mysqli_query($con, $notiSQL);
	$notiData = mysqli_fetch_all($notiQuery, MYSQLI_ASSOC);
	
?>
<style>
	.tab .nav-tabs {
	padding: 0;
	margin: 0;
	border: none;
	}
	.tab .nav-tabs li a {
	color: #123C69;
	background: #E3E3E3;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover {
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius: 3px;
	}
	.tab .nav-tabs li a:before {
	content: "";
	background: #E3E3E3;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s, height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before {
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab-content h4 {
	color: #123C69;
	font-weight: 500;
	}
	@media only screen and (max-width: 479px) {
	.tab .nav-tabs {
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li {
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a {
	margin: 0 0 5px;
	}
	}	
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	thead {
	text-transform: uppercase;
	background-color: #123C69;
	font-size: 10px;
	}
	thead tr {
	color: #f2f2f2;
	}
	.btn-primary {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 12px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 10px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}	
	.text-success {
	font-weight: 600;
	color: #123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.fa_Icon{
	color: #990000;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
		
			<div class="hpanel">
				<div class="panel-heading">
					<h3><i class="fa_Icon fa fa-edit"></i> Notifications </h3>
				</div>
				<?php if($type == 'Zonal'){ ?>
					<div class="panel-body">
						<form class="form-horizontal" action="notificationUpdate.php" method="POST" autocomplete="off">
							<input type="hidden" name="sender" value="<?php echo $type; ?>" >
							<div class="col-sm-4">
								<label class="text-success">To Department</label>
								<input type="text" class="form-control" name="receiver" required placeholder="Customer Name" value="ApprovalTeam" readonly>
							</div>
							<div class="col-sm-4">
								<label class="text-success">Branch</label>
								<input list="branchList"  class="form-control" name="branch" required placeholder="Select Branch" />
								<datalist id="branchList"> 
									<?php while ($row = mysqli_fetch_assoc($branchListQuery)){  ?>
										<option value="<?php echo $row['branchName']; ?>" label="<?php echo $row['branchName']; ?>"></option>
									<?php } ?>
								</datalist>
							</div>
							<div class="col-sm-4">
								<label class="text-success">Customer Name</label>
								<input type="text" class="form-control" name="info" required placeholder="Customer Name" autocomplete="off">
							</div>
							<div class="col-sm-12"><br></div>
							<div class="col-sm-11">
								<label class="text-success">Remarks (Below 256 Characters)</label>
								<input type="text" class="form-control" name="remarks" required placeholder="Remarks" autocomplete="off">
							</div>
							<div class="col-sm-1">
								<label class="text-success"><br></label>
								<button type="submit" class="btn btn-success btn-block" name="insertNotification"><span style="color:#ffcf40" class="fa fa-check"></span> OK</button>
							</div>
						</form>
					</div>
				<?php } ?>
			</div>			
			
			<div class="hpanel">
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="<?php echo ($type == 'ApprovalTeam') ? "active" : "" ?>">
							<a href="#received" aria-controls="pending" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Recieved </a>
						</li>
						<li role="presentation" class="<?php echo ($type == 'Zonal') ? "active" : "" ?>">
							<a href="#sent" aria-controls="approved" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Sent</a>
						</li>
					</ul>
					<div class="tab-content tabs">
						<div role="tabpanel" class="tab-pane fade in <?php echo ($type == 'ApprovalTeam') ? "active" : "" ?>" id="received">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example5" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>From</th>
												<th>Branch</th>
												<th>Info</th>
												<th>Remarks</th>
												<th>Time</th>
												<th class='text-center'>Status</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$i = 1;
												foreach ($notiData as $key=>$val) {
													if($val['receiver'] == $type){
														echo ($val['status'] == 'Done') ? "<tr style='background-color: #C3F4AF'>" : "<tr>";														
														echo "<td>".$i."</td>";
														echo "<td>".$val['sender']."</td>";
														echo "<td>".$val['branch']."</td>";
														echo "<td>".$val['info']."</td>";
														echo "<td>".$val['remarks']."</td>";
														echo "<td>".$val['time']."</td>";
														if($val['status'] == 'Pending'){
															echo "<td class='text-center'><button class='doneButton' data-id=".$val['id'].">".$val['status']."</button></td>";
														}
														else{
															echo "<td class='text-center'>".$val['status']."</td>";
														}
														echo "</tr>";
														$i++;
													}
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div role="tabpanel" class="tab-pane fade in <?php echo ($type == 'Zonal') ? "active" : "" ?>" id="sent">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example6" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>To</th>
												<th>Branch</th>
												<th>Info</th>
												<th>Remarks</th>
												<th>Time</th>
												<th class='text-center'>Status</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$i = 1;
												foreach ($notiData as $key=>$val) {
													if($val['sender'] == $type){
														echo ($val['status'] == 'Done') ? "<tr style='background-color: #C3F4AF'>" : "<tr>";	
														echo "<td>".$i."</td>";
														echo "<td>".$val['receiver']."</td>";
														echo "<td>".$val['branch']."</td>";
														echo "<td>".$val['info']."</td>";
														echo "<td>".$val['remarks']."</td>";
														echo "<td>".$val['time']."</td>";
														echo "<td class='text-center'>".$val['status']."</td>";
														echo "</tr>";
														$i++;
													}
												}
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
	</div>
	<?php include("footer.php"); ?>
	<script>
		$(document).ready(function(){
			
			const doneButton = document.querySelectorAll(".doneButton");
			doneButton.forEach((btn)=>{
				btn.addEventListener("click", async (e)=>{
					const id = btn.dataset.id;
					const td = btn.parentElement;
					const tr = td.parentElement;
					
					const response = await fetch("notificationUpdate.php?updateNotificationUpdate=true&rowid="+id);
					const result = await response.json();
					
					if(result.error){
						alert("Something went wrong, Please try again later");
						return;
					}								
					td.textContent = "Done";
					tr.style.backgroundColor = "#C3F4AF";
				})
			})
			
		});
	</script>		
