<?php
	session_start();
	$type=$_SESSION['usertype'];	
	$employeeId = $_SESSION['employeeId'];

	if($type=='SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');	
	
	$search = "";
	$search1 = "";
	$search2 = "";
	if(isset($_GET['aaa'])){
		$search = $_GET['dat2'];
		$search1 = $_GET['dat3'];
		$search2 = $_GET['bran'];
	}
?>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<!-- <option value="All Branches"> Assigned Branches</option> -->
	<?php
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1 AND ezviz_vc = '$employeeId'");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
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
	
	#wrapper .panel-body{
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
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
	.dataTables_empty{
		text-align:center;
		font-weight:600;
		font-size:12px;
		text-transform:uppercase;
	}
	.btn-success{
		display:inline-block;
		padding:0.7em 1.4em;
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
	.fa_Icon {
		color:#ffcf40;
	}
	.modal-title {
		font-size: 25px;
		font-weight: 300;
		color:#fff5ee;
		text-transform:uppercase;
	}
	.modal-header{
		background: #123C69;
	}
	.row{
	    margin-left:0px;
	    margin-right:0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<form action="" method="GET">
				<div class="col-sm-3">
					<label class="text-success">Branch Id</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
						<input list="branchList" class="form-control" name="bran" id="bran" placeholder="Branch Id" required/>  
					</div>
				</div>
				<div class="col-sm-3">
					<label class="text-success">From Date</label> 
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date"  class="form-control" id="dat3" name="dat3" required/>
					</div>
				</div>
				<div class="col-sm-3"> 
					<label class="text-success">To Date</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date" class="form-control" id="dat2" name="dat2" required/>
					</div>
				</div>
				<div class="col-sm-1">
					<button class="btn btn-success btn-block" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</div>
			</form>
			<div class="col-sm-2">
				<form action="export.php" enctype="multipart/form-data" method="post">
					<input type="hidden" name="branch" value="<?php if(isset($_GET['bran'])){ echo $_GET['bran']; }else { echo "All Branches"; } ?>">
					<input type="hidden" name="from" value="<?php if(isset($_GET['dat3'])){ echo $_GET['dat3']; }else { echo $date; } ?>">
					<input type="hidden" name="to" value="<?php if(isset($_GET['dat2'])){ echo $_GET['dat2']; }else { echo $date; } ?>">
					<button type="submit" name="exportFund" class="btn btn-success btn-block" value="Export Excel" required style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
				</form> 
			</div>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-money"></i> Funds Report</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
									<th>Available</th>
									<th>Request</th>
									<th>Type</th>
									<th>BranchID</th>
									<th>Branch</th>
									<th>Customer</th>
									<th>Date</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if($search=="" && $search1=="" && $search2==""){
										$sql = mysqli_query($con,"SELECT f.available,f.request,f.type,f.branch,f.customerName,f.customerMobile,f.date,f.status,b.branchName FROM fund f,branch b WHERE b.ezviz_vc = '$employeeId' AND f.date='$date' AND f.branch=b.branchId ORDER BY f.id DESC");
									}
									else if($search!="" && $search1!="" && $search2=="All Branches"){
										$sql = mysqli_query($con,"SELECT f.available,f.request,f.type,f.branch,f.customerName,f.customerMobile,f.date,f.status,b.branchName FROM fund f,branch b WHERE b.ezviz_vc = '$employeeId' AND f.date BETWEEN '$search1' AND '$search' AND f.branch=b.branchId ORDER BY f.id DESC");
									}
									else if($search!="" && $search1!="" && $search2!="All Branches"){
										$sql = mysqli_query($con,"SELECT f.available,f.request,f.type,f.branch,f.customerName,f.customerMobile,f.date,f.status,b.branchName FROM fund f,branch b WHERE b.ezviz_vc = '$employeeId' AND f.date BETWEEN '$search1' AND '$search' AND f.branch='$search2' AND f.branch=b.branchId ORDER BY f.id DESC");
									}
									$i = 1;
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>" . $i ."</td>";
										echo "<td>" . $row['available'] . "</td>";
										echo "<td>" . $row['request'] . "</td>";
										echo "<td>" . $row['type'] . "</td>";
										echo "<td>" . $row['branch'] . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['customerName'] . "<br>".$row['customerMobile']."</td>";
										echo "<td>" . $row['date'] . "</td>";
										echo "<td>" . $row['status'] . "</td>";
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
<?php include("footer.php"); ?>
