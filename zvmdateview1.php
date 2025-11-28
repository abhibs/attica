<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'VM-AD') {
		include("header.php");
		include("menuvmadd.php");
	}
	else if ($type == 'Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	if(isset($_GET['getVMlogs'])){
		$sql = mysqli_query($con, "SELECT v.*, e.name
		FROM vm_log v, employee e 
		WHERE v.date BETWEEN '$_GET[from]' AND '$_GET[to]' AND v.branchId LIKE '%$_GET[branchId]%' AND v.empId=e.empId
		ORDER BY v.date ASC");
		$branchName = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$_GET[branchId]'"));
	}
	
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	h4{
	font-weight: 600;
	color: #990000;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 10px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
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
	.fa_Icon {
	color:#990000;
	}
</style>
<div id="wrapper">
	<div class="content row">
		<div class="col-lg-12">
			<div class="hpanel">
				
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-2">
							<h3 style="padding-left:10px"><i class="fa_Icon fa fa-clock-o" ></i> VM Logs </h3>
						</div>
						<div class="col-sm-10">
							<form action="" method="GET">
								<div class="col-sm-4">
									<input list="branchList" class="form-control" name="branchId" placeholder="Branch ID" required>
									<datalist id="branchList">
										<?php
											$branchList = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status=1");
											while($row = mysqli_fetch_assoc($branchList)){
												echo "<option value=".$row['branchId'].">".$row['branchName']."</option>";
											}
										?>
									</datalist>
								</div>
								<div class="col-sm-6">
									<div class="input-group">
										<input type="date" class="form-control" name="from" required>
										<span class="input-group-addon">to</span>
										<input type="date" class="form-control" name="to" required>
									</div>
								</div>
								<div class="col-sm-2">
									<button class="btn btn-success btn-block" name="getVMlogs"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="panel-heading">
					<?php
						if(isset($_GET['getVMlogs'])){
							echo "<h4>".$branchName['branchName']."</h4>";
						}
					?>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-striped table-bordered">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>Employee ID</th>
								<th>Employee Name</th>
								<th>Date</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['getVMlogs'])){
									$i = 1;
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>". $i ."</td>";
										echo "<td>". $row['empId'] ."</td>";
										echo "<td>". $row['name'] ."</td>";
										echo "<td>". $row['date'] ."</td>";
										echo "<td>". $row['time'] ."</td>";
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