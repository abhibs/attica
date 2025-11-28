<?php
	session_start();
	$type=$_SESSION['usertype'];	
	if($type=='ITMaster'){
		include("header.php");
	    include("menuItMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	
	$branch = "";
	$from = "";
	$to = "";
	if(isset($_GET['it_expense_report'])){
		$branch = $_GET['branch'];
		$from = $_GET['from'];
		$to = $_GET['to'];
	}	
?>

<style>
	.tab{
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
	padding: 0;
	margin: 0;
	border: none;
	}   
	.tab .nav-tabs li a{
	color: #123C69;
	background: #f8f8ff;
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
	.tab .nav-tabs li.active a:hover{
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius:3px;
	}
	.tab .nav-tabs li a:before{
	content: "";
	background: #f8f8ff;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
	color: #0C1115;
	background: #f8f8ff;
	font-size: 12px;
	/* outline: 2px solid rgba(19,99,126,0.2); */
	position: relative;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
	color: #123C69;
	font-weight:500;
	}	
	.tab-pane{
	background: #f8f8ff;
	padding:10px;
	}	
	@media only screen and (max-width: 479px){
	.tab .nav-tabs{
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li{
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a{ margin: 0 0 5px; }
	}
	#wrapper{
	background: #f5f5f5;
	}	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
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
	color:#ffcf40;
	}
	.fa_icon{
	color:#990000;
	}
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	.btn-danger{
	background-color: #990000;		
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<option value="All Branches"> All Branches</option>
	<?php
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch");
		while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
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
					<div class="input-group"><span class="input-group-addon text-success"> From </span>
						<input type="date" class="form-control" name="from" required>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="input-group"><span class="input-group-addon text-success"> To </span>
						<input type="date" class="form-control" name="to" required>
					</div>
				</div>
				<div class="col-sm-1">
					<button class="btn btn-success btn-block" name="it_expense_report"><span style="color:#ffcf40" class="fa fa-search"></span> Search </button>
				</div>
			</form>

		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-money"></i> IT Expense Details</h3>
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
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.date='$date' AND expense.status IN ('Approved','Rejected') AND expense.branchCode=branch.branchId AND expense.type='IT Expense'");
								}
								else if($branch=="All Branches" && $from!="" && $to!=""){
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.date BETWEEN '$from' AND '$to' AND expense.status IN ('Approved','Rejected') AND expense.branchCode=branch.branchId AND expense.type='IT Expense'");
								}
								else if($branch!="All Branches" && $from!="" && $to!=""){
									$sql = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.date BETWEEN '$from' AND '$to' AND expense.status IN ('Approved','Rejected') AND expense.branchCode='$branch' AND expense.branchCode=branch.branchId AND expense.type='IT Expense'");
								}
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
	<div style="clear:both"></div>
<?php include("footer.php");?>