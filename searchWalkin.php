<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type=='Issuecall'){
		include("header.php");
		include("menuissues.php");
	}
	else if ($type == 'Call Centre') {
		include("header.php");
		include("menuCall.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	font-size: 10px;
	}
	thead tr{
	color: #f2f2f2;
	}
	.btn-success, .btn-primary{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 12px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #285187;
	border-color: #285187;
	border: 1px solid #285187;
	color: #fffafa;
	}	
	.fa_Icon{
	color: #990000;
	}	
	.text-success{
	font-weight:bold;
	color: #123C69;
	text-transform:uppercase;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">	
					<form action="" method="GET">
						<div class="row">
							<div class="col-sm-8"> 								
								<h3><i class="fa_Icon fa fa-search"></i> Search Customer </h3>
							</div>
							<div class="col-sm-4">							
								<div class="input-group">
									<input type="text" class="form-control" name="custph" required autocomplete="off" />
									<span class="input-group-btn"> 
										<button class="btn btn-success">
											<span style="color:#ffcf40" class="fa fa-search"></span>
										</button>
									</span>
								</div>								
							</div>
						</div>
					</form>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Contact</th>
								<th>Branch</th>
								<th>Type</th>
								<th>GrossW</th>
								<th>ReleaseA</th>
								<th>Branch Remarks</th>
								<th>Zonal Remarks</th>
								<th>Comments</th>
								<th>Date</th>
								<?php
									if($type == 'Call Centre'){
										echo "<th><span class='fa fa-edit'></span></th>";
									}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
								if (isset($_GET['custph'])){
									$i=1;
									$sql = mysqli_query($con,"SELECT W.*,B.branchName 
									FROM walkin W,branch B 
									WHERE W.branchId=B.branchId AND W.mobile='$_GET[custph]' 
									ORDER BY W.date DESC");
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['gold']."</td>";
										echo "<td>".$row['gwt']."</td>";
										echo "<td>".$row['namt']."</td>";
										echo "<td>".$row['remarks']."</td>";
										echo "<td>".$row['zonal_remarks']."</td>";
										echo "<td>".$row['comment']."</td>";
										echo "<td>".$row['date']."</td>";
										if($type == 'Call Centre'){
											echo "<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>";
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
	</div>
<?php include("footer.php"); ?>