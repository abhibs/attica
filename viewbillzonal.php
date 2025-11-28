<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Zonal') {
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
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	$lastday = date('Y-m-d', strtotime("-10 days"));	
	
?>
<style>
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
	background-color: #fffafa;
	}
	.text-success {
	color: #123C69;
	text-transform: uppercase;
	font-weight: bold;
	font-size: 12px;
	}
	.btn-primary {
	background-color: #123C69;
	}
	.theadRow {
	text-transform: uppercase;
	background-color: #123C69 !important;
	color: #f2f2f2;
	font-size: 11px;
	}
	.btn-success {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 10px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.fa_Icon {
	color: #ffa500;
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
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"><span class="fa fa-money" style="color:#990000"></span><b> TRANSACTION DETAILS</b></h3>
				</div>
				<div class="col-xs-4" style="margin-top:6.5px">
					<form action="" method="POST">
						<div class="col-sm-9">
							<input class="form-control" type="date" name="selectedDate" required min="<?php echo $lastday; ?>" max="<?php echo $date; ?>" onkeypress="return false">
						</div>
						<div class="col-sm-3">
							<button class="btn btn-success" type="submit" style="margin-top:1px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>				
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example1" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Customer</th>
									<th>Gross_W</th>
									<th>Net_W</th>
									<th>Gross_A</th>
									<th>Net_A</th>
									<th>Amt_Paid</th>
									<th>Margin</th>
									<th>Metal</th>
									<th>Bill</th>
									<th>Type</th>
									<th>Imps</th>
									<th>Cash</th>
									<th>Bills</th>
									<th>Date</th>
									<th>Cust</th>
									<th>Bill</th>								
								</tr>
							</thead>
							<tbody>
								<?php
									
									// ZONAL STATE SELECTION
									if ($_SESSION['branchCode'] == "TN") {
										$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Tamilnadu','Pondicherry'))";
									}
									else if ($_SESSION['branchCode'] == "KA") {
										$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka')";
									}
									else if ($_SESSION['branchCode'] == "AP-TS") {
										$extra_query = "AND t.branchId IN (SELECT branchId FROM branch WHERE state IN('Andhra Pradesh','Telangana'))";
									}
									
									// DATE SELECTION
									if (isset($_POST['selectedDate'])) {
										$selDate = $_POST['selectedDate'];
									}
									else{
										$selDate = date('Y-m-d');
									}
									
									$sql = "SELECT b.branchName,t.id,t.name,t.phone,t.billCount,t.releases,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.date,t.time,t.comm,t.margin,t.paymentType,t.cashA,t.impsA,t.status,t.metal,t.type
									FROM trans t,branch b 
									WHERE t.date='$selDate' AND t.status='Approved' AND t.branchId=b.branchId " . $extra_query;
									$query = mysqli_query($con, $sql);
									
									$i = 1;
									while ($row = mysqli_fetch_assoc($query)) {
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['name'] . "<br>( " . $row['phone'] . " )</td>";
										echo "<td>" . round($row['grossW'], 2) . "</td>";
										echo "<td>" . round($row['netW'], 2) . "</td>";
										echo "<td>" . round($row['grossA'], 0) . "</td>";
										echo "<td>" . round($row['netA'], 0) . "</td>";
										echo "<td>" . round($row['amountPaid'], 0) . "</td>";
										echo "<td>" . round($row['margin'], 0) . "<br>(" . round($row['comm'], 2) . "%)</td>";
										echo "<td>" . $row['metal'] . "</td>";
										echo ($row['type'] == 'Physical Gold') ? "<td>Physical</td>" : "<td>Release</td>";
										if($row['paymentType'] == "Cash"){
											echo "<td>" . $row['paymentType'] . "</td>";
										}
										else{
											echo "<td>IMPS</td>";
										}
										echo "<td>" . $row['impsA'] . "</td>";
										echo "<td>" . $row['cashA'] . "</td>";
										echo "<td><a target='_blank' href='existing.php?phone=" . $row['phone'] . "'>" . $row['billCount'] . "</a></td>";
										echo "<td>" . $row['date'] . "<br>" . $row['time'] . "</td>";
										echo "<td><a target='_blank' class='btn' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i class='fa fa-user' style='font-size:15px;font-weight:600;'></i></a></td>";
										echo "<td><a target='_blank' class='btn' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa fa fa-file-pdf-o' style='font-size:15px;font-weight:600;'></i></a></td>";
										
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
