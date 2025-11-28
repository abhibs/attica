<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master'){
	    include("header.php");
		include("menumaster.php");
	}
	else if($type=='Accounts'){
	    include("header.php");
		include("menuacc.php");
	}
	else if($type=='Accounts IMPS'){
	    include("header.php");
		include("menuimpsAcc.php");
	}
	else if($type=='AccHead'){
	    include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type=='Expense Team'){
	    include("header.php");
		include("menuexpense.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else if($type=='Zonal'){
	    include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'ZonalMaster') {
        include("header.php");
        include("menuzonalMaster.php");
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
		$search = $_REQUEST['dat2'];
		$search1 = $_REQUEST['dat3'];
		$search2 = $_REQUEST['bran'];
	}
?>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList"> 
	<option value="All Branches"> All Branches</option>
	<?php
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
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
				<div class="col-sm-4">
					<label class="text-success">Branch Id</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
						<input list="branchList" required class="form-control" name="bran" id="bran" placeholder="Branch Id" />  
					</div>
				</div>			
				<div class="col-sm-3">
					<label class="text-success">From Date</label> 
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date"  class="form-control" required id="dat3" name="dat3" />
					</div>
				</div>
				<div class="col-sm-3"> 
					<label class="text-success">To Date</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date" class="form-control" required id="dat2" name="dat2" />
					</div>
				</div>
				<div class="col-sm-2">
					<button class="btn btn-success btn-block" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</div>
			</form>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-money"></i> Funds Transfer Report</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>Amount</th>
									<th>Branch From</th>
									<th>Branch To</th>
									<th>Date</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if($search=="" && $search1=="" && $search2==""){
										$query=mysqli_query($con,"SELECT trare.*,branch.branchName FROM trare,branch WHERE trare.date='$date' AND trare.status='Approved' AND trare.branchId=branch.branchId ORDER BY trare.id DESC");
									}
									else if($search!="" && $search1!="" && $search2=="All Branches"){
										$query=mysqli_query($con,"SELECT trare.*,branch.branchName FROM trare,branch WHERE trare.date BETWEEN '$search1' AND '$search' AND trare.status='Approved' AND trare.branchId=branch.branchId ORDER BY trare.id DESC");
									}
									else if($search!="" && $search1!="" && $search2 != "All Branches"){
										$query=mysqli_query($con,"SELECT trare.*,branch.branchName FROM trare,branch WHERE trare.date BETWEEN '$search1' AND '$search' AND trare.status='Approved' AND trare.branchId='$search2' AND trare.branchId=branch.branchId ORDER BY trare.id DESC");
									}
									$i=1;
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";
										echo "<td>" . $i .  "</td>";
										echo "<td>" . $row['transferAmount'] . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['branchTo'] . "</td>";
										echo "<td>" . $row['date'] . "</td>";
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
<?php include("footer.php");?>
