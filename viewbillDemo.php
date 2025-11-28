<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
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
	else if($type=='Zonal'){
        include("header.php");
        include("menuZonal.php");
	}
    else{
        include("logout.php");
	}
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	$search = "";
	$search1 = "";
	$search2 = "";
	if(isset($_GET['aaa'])){
		$search = $_GET['dat2'];
		$search1 = $_GET['dat3'];
		$search2 = $_GET['bran'];
	}
	
	if($type=='Zonal'){
		if($_SESSION['branchCode']=="TN"){
			$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE state IN('Tamilnadu','Pondicherry') AND status=1");
			$extra_query="AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Tamilnadu','Pondicherry'))";
		}
		else if ($_SESSION['branchCode']=="KA"){
			$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE state='Karnataka' AND status=1");
			$extra_query="AND t.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka')";
		}
		else if ($_SESSION['branchCode']=="AP-TS"){
			$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1");
			$extra_query="AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Andhra Pradesh','Telangana'))";
		}
	}
	else{
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
		$extra_query="";
	}
	
	if($search=="" && $search1==""  && $search2==""){
		$sql = "SELECT b.branchName,t.id,t.billId,t.name,t.phone,t.billCount,t.releases,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.date,t.time,t.branchId,t.comm,t.margin,t.status FROM trans t,branch b WHERE t.date='$date' AND t.status='Approved' AND t.branchId=b.branchId ".$extra_query." ORDER BY t.id DESC";
		$query=mysqli_query($con,$sql);
	}
	else if($search!=="" && $search1!==""  && $search2!="All Branches"){
		$sql = "SELECT b.branchName,t.id,t.billId,t.name,t.phone,t.billCount,t.releases,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.date,t.time,t.branchId,t.comm,t.margin,t.status FROM trans t,branch b WHERE t.status='Approved' AND t.branchId=b.branchId AND t.date BETWEEN '$search1' AND '$search' AND t.branchId='$search2' ".$extra_query." ORDER BY t.id DESC";
		$query = mysqli_query($con,$sql);
	}
	else if($search!=="" && $search1!==""  && $search2=="All Branches"){
		$sql = "SELECT b.branchName,t.id,t.billId,t.name,t.phone,t.billCount,t.releases,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.date,t.time,t.branchId,t.comm,t.margin,t.status FROM trans t,branch b WHERE t.status='Approved' AND t.branchId=b.branchId AND t.date BETWEEN '$search1' AND '$search' ".$extra_query." ORDER BY t.id DESC";
		$query = mysqli_query($con,$sql);
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
	color:#ffa500;
	}
	fieldset {
	margin-top: 1.5rem;
	margin-bottom: 1.5rem;
	border: none;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}
	legend{
	margin-left:8px;
	width:300px;
	background-color: #123C69;
	padding: 5px 15px;
	line-height:30px;
	font-size: 18px;
	color: white;
	text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
	transform: translateX(-1.1rem);
	box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
	margin-bottom:0px;
	letter-spacing: 2px;
	text-transform:uppercase;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	
	legend:after {
	content: "";
	height: 0;
	width:0;
	background-color: transparent;
	border-top: 0.0rem solid transparent;
	border-right:  0.35rem solid black;
	border-bottom: 0.45rem solid transparent;
	border-left: 0.0rem solid transparent;
	position:absolute;
	left:-0.075rem;
	bottom: -0.45rem;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<?php
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
					<label class="text-success">Branch Id</label>
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
						<input list="branchList"  class="form-control" name="bran" id="bran" placeholder="Branch Id" required/>  
					</div>
				</div>
				<div class="col-sm-3">
					<label class="text-success">From Date</label> 
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date"  class="form-control" id="dat3" name="dat3" required/>
					</div>
				</div>
				<div class="col-sm-3"> 
					<label class="text-success">To Date</label>
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date" class="form-control"  id="dat2" name="dat2" required/>
					</div>
				</div>
				<div class="col-sm-1">
					<button class="btn btn-success" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</div>
			</form>
			<div class="col-sm-2">
				<form action="export.php" enctype="multipart/form-data" method="post">
					<button type="submit" name="exports" class="btn btn-success btn-block" value="Export Excel" style="margin-top:23px" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
				</form>
			</div>
			<div style="clear:both"></div>
			<div class="hpanel">
				<fieldset>
					<legend> <i style="padding-top:15px" class="fa_Icon fa fa-eye"></i> VIEW TRANSACTIONS </legend>
					<div class="panel-body" style="background-color:#f5f5f5;border:none;"> 
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>Branch Name</th>
									<th>Customer</th>
									<th>Gross Weight</th>
									<th>Net Weight</th>
									<th>Gross Amount</th>
									<th>Net Amount</th>
									<th>Amount Paid</th>
									<th>Margin</th>
									<th>Bills</th>
									<th>View</th>
									<th>Bill</th>
									<th>Date/Time</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName']. "</td>";
										echo "<td>" . $row['name']. "</td>";
										echo "<td>" . round($row['grossW'],2). "</td>";
										echo "<td>" . round($row['netW'],2). "</td>";
										echo "<td>" . round($row['grossA'],0). "</td>";
										echo "<td>" . round($row['netA'],0). "</td>";
										echo "<td>" . round($row['amountPaid'],0). "</td>";
										echo "<td>" . round($row['margin'],0)."(".round($row['comm'],2)."%)</td>";
										echo "<td><a target='_blank' href='existing.php?phone=".$row['phone']."'>" . $row['billCount']. "</a></td>";
										echo "<td><a class='btn btn-success' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'>View</a></td>";
										echo "<td><a class='btn btn-success' target='_blank' href='InvoiceDemo.php?billId=".$row['billId']."&id=".$row['id']."'><i style='color:#ffcf40' class='fa fa-eye'></i>  View</a></td>";
										echo "<td>" . $row['date']. "<br>" . $row['time']. "</td>";
										echo "<td>" . $row['status'] . "</td>";
										echo "</tr>";
										$i++;
									}
								?>
							</tbody>
						</table>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php"); ?>