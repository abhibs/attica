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
	
	$branch = "";
	$from = "";
	$to = "";
	if(isset($_GET['expenseDetailsSubmit'])){
		$branch = $_GET['branch'];
		$from = $_GET['from'];
		$to = $_GET['to'];
	}
?>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<!-- <option value="All Branches"> All Branches</option> -->
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
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
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
	font-size: 10px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<form action="" method="GET">
				<div class="col-sm-3">
					<div class="input-group"><span class="input-group-addon text-success">Branch</span>
						<input list="branchList" class="form-control" name="branch" placeholder="Branch ID" required>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="input-group"><span class="input-group-addon text-success">From</span>
						<input type="date" class="form-control" name="from" required>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="input-group"><span class="input-group-addon text-success">To</span>
						<input type="date" class="form-control" name="to" required>
					</div>
				</div>
				<div class="col-sm-1">
					<button class="btn btn-success btn-block" name="expenseDetailsSubmit"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</div>
			</form>
			<div class="col-sm-2">
				<form action="export.php" enctype="multipart/form-data" method="post">
					<input type="hidden" name="branch" value="<?php if(isset($_GET['branch'])){ echo $_GET['branch']; }else { echo "All Branches"; } ?>">
					<input type="hidden" name="from" value="<?php if(isset($_GET['from'])){ echo $_GET['from']; }else { echo $date; } ?>">
					<input type="hidden" name="to" value="<?php if(isset($_GET['to'])){ echo $_GET['to']; }else { echo $date; } ?>">
					<button type="submit" name="exportExpense" class="btn btn-success btn-block" value="Export Excel" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
				</form>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-money"></i> Expense Details</h3>
				</div>
				<div class="panel-body">
					<table id="example1" class="table table-striped table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th><i class="fa fa-sort-numeric-asc"></i></th>
								<th>BranchName</th>
								<th>Type</th>
								<th>Particular</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Date/Time</th>
								<th class="text-center">Bill 1</th>
								<th class="text-center">Bill 2</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if($branch=="" && $from=="" && $to==""){
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE ezviz_vc = '$employeeId' AND expense.date='$date' AND expense.status IN ('Approved','Rejected') AND expense.branchCode=branch.branchId");
								}
								else if($branch=="All Branches" && $from!="" && $to!=""){
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE ezviz_vc = '$employeeId' AND expense.date BETWEEN '$from' AND '$to' AND expense.status IN ('Approved','Rejected') AND expense.branchCode=branch.branchId");
								}
								if($branch!="All Branches" && $from!="" && $to!=""){
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE ezviz_vc = '$employeeId' AND expense.date BETWEEN '$from' AND '$to' AND expense.status IN ('Approved','Rejected') AND expense.branchCode='$branch' AND expense.branchCode=branch.branchId");
								}


								// $branchSQL = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status = 1 AND ezviz_vc = '$employeeId'");

								$i = 1;
								while($row = mysqli_fetch_assoc($sql)){
									echo "<tr>";
									echo "<td>" . $i ."</td>";
									echo "<td>" . $row['branchName'] . "</td>";
									echo "<td>" . $row['type'] . "</td>";
									echo "<td>" . $row['particular'] . "</td>";
									echo "<td>" . $row['amount'] . "</td>";
									echo "<td>" . $row['status'] . "</td>";
									echo "<td>" . $row['date'] . "<br>" . $row['time'] . "</td>";
									echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$row['file']."'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill 1</a></td>";
									if ($row['file1'] != "") {
										echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$row['file1']."'><i style='color:#ffcf40' class='fa fa-file-o'></i> Bill 2</a></td>";
									}
									else {
										echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> Bill 2</a></td>";
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
<?php include("footer.php"); ?>
