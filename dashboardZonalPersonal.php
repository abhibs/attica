<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	date_default_timezone_set("Asia/Kolkata");
	
	$type = $_SESSION['usertype'];
	if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
	// if($empId == "1000043"){
	// 	$empId = "1000423";
	// }
	
	$branchListStr = "";
	$branchDataArr = [];
	$branchQuery = mysqli_query($con, "SELECT branchId, branchName
	FROM branch 
	WHERE ezviz_vc='$empId' AND status=1");
	while($row = mysqli_fetch_assoc($branchQuery)){
		$branchDataArr[$row['branchId']] = $row['branchName'];
		$branchListStr .= "'$row[branchId]',";
	}
	$branchListStr = substr($branchListStr, 0, -1);
	
	// Zonal Details
	$zonalQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM employee WHERE empId='$_SESSION[employeeId]' LIMIT 1"));
	
	// Bill Data
	$transQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS bills,
	SUM(CASE WHEN metal='Gold' THEN grossW ELSE 0 END) AS gold_grossw,
	SUM(CASE WHEN metal='Gold' THEN netW ELSE 0 END) AS gold_netw,
	SUM(CASE WHEN metal='Gold' THEN grossA ELSE 0 END) AS gold_grossA,
	SUM(CASE WHEN metal='Gold' THEN netA ELSE 0 END) AS gold_netA,
	SUM(CASE WHEN metal='Silver' THEN grossW ELSE 0 END) AS silver_grossw,
	SUM(CASE WHEN metal='Silver' THEN netW ELSE 0 END) AS silver_netw,
	AVG(rate) AS rate
	FROM trans
	WHERE date='$date' AND status='Approved' AND branchId IN (".$branchListStr.")"));
	$margin = 0;
	if($transQuery['gold_grossA'] > 0){
		$margin = ROUND((($transQuery['gold_grossA'] - $transQuery['gold_netA']) / $transQuery['gold_grossA']) * 100, 2);
	}
	$avgPurity =  ($transQuery['gold_netw'] > 0 && $transQuery['rate'] > 0) ? ROUND(((($transQuery['gold_grossA'] / $transQuery['gold_netw']) / $transQuery['rate']) * 100), 2) : 0;
	
	// Enquiry Data
	$enqQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS enquiry
	FROM walkin
	WHERE date='$date' AND issue != 'Rejected' AND branchId IN (".$branchListStr.")"));
	
	// Gold Rate
	if($_SESSION['branchCode'] == 'AP-TS'){
		$city = "Hyderabad";
		$state = 'AP & TS';	
		$cityGoldName = 'Telangana';
		$stateGoldName = 'Andhra Pradesh';
	}
	else if($_SESSION['branchCode'] == 'KA'){
		$city = "Bengaluru";
		$state = 'Karnataka';
		$cityGoldName = 'Bangalore';
		$stateGoldName = 'Karnataka';
	}
	else if($_SESSION['branchCode'] == 'TN'){
		$city = "Chennai";
		$state = 'TN & PY';
		$cityGoldName = 'Chennai';
		$stateGoldName = 'Tamilnadu';
	}
	
?>
<style type="text/css">
	.hpanel{
	margin-bottom:5px;
	border-radius: 10px;
	box-shadow:5px 5px 5px #999;
	}
	#wrapper .panel-body{
	background-color: #f5f5f5;
	border-radius: 10px 10px 0px 0px;
	padding: 20px;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-size: 20px;
	}
	.stats-label{
	text-transform:uppercase;
	font-size: 10px;
	}
	.panel-footer{
	border-radius: 0px 0px 10px 10px ;	
	text-align: center;
	}
	.panel-footer > b{
	color: #990000;
	}
	.fa{
	color:#990000;
	}
	.stats-icon > .fa{
	margin-right: 10px;
	}
</style>
<link rel="stylesheet" href="vendor/sweetalert/lib/sweet-alert.css" />

<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog"  aria-hidden="true">
	<div class="modal-dialog modal-sm" style="width:500px;">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header"></div>
			<div class="modal-body" style="padding: 40px;">
				<table class="table table-bordered table-striped table-hover">
					<thead style="background-color: transparent;">
						<tr>
							<th>#</th>
							<th>BranchId</th>
							<th>BranchName</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 1;
							foreach($branchDataArr as $key=>$value){
								echo "<tr>";
								echo "<td>".$i."</td>";
								echo "<td>".$key."</td>";
								echo "<td>".$value."</td>";
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

<div id="wrapper">
	<div class="content">
		<div class="row">
			
			<div class="col-lg-3">
				<div class="hpanel ">
					<div class="panel-body h-200">
						<div class="stats-title pull-left">
							<h4 style="font-weight: 900">Zonal Information</h4>
						</div>
						<div class="stats-icon pull-right">
							<i class="pe-7s-user fa-4x"></i>
						</div>
						<div class="m-t-xl">
							<h3 class="font-bold text-success"><?php echo $zonalQuery['name']; ?></h3>
							<span class="font-bold no-margins">
								<?php echo $_SESSION['employeeId']; ?>
							</span>
							<div class="progress m-t-xs full progress-small"></div>
						</div>
						<div class="row" style="padding-left: 10px; padding-top: 1px;">
							<div class="col-xs-4">
								<small class="stats-label font-bold">Today</small>
								<div class="font-bold" id="todayCOG" data-empid="<?php echo $empId; ?>" style="padding-top: 5px; color: #990000; cursor: pointer; text-decoration: underline; font-style: italic;">
									COG
								</div>
							</div>
							<div class="col-xs-4">
								<small class="stats-label font-bold">30 Days</small>
								<div class="font-bold" id="monthCOG" data-empid="<?php echo $empId; ?>" style="padding-top: 5px; color: #990000; cursor: pointer; text-decoration: underline; font-style: italic;">
									COG
								</div>
							</div>
							<div class="col-xs-4">
								<small class="stats-label font-bold">Your Branches</small>
								<div class="font-bold"  style="padding-top: 5px; color: #990000; cursor: pointer; text-decoration: underline; font-style: italic;" data-toggle="modal" data-target="#addCustomerModal">
									Click Here
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>				
			</div>
			
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body h-200">
						<div class="stats-title pull-left">
							<h4 style="font-weight: 900">Walkin Details</h4>
						</div>
						<div class="stats-icon pull-right">
							<i class="pe-7s-global fa-4x"></i>
						</div>
						<div class="m-t-xl">
							<h3 class="font-bold text-success">
								<?php 
									$bills = $transQuery['bills'] ? $transQuery['bills'] : 0;
									$enquiry = $enqQuery['enquiry'] ? $enqQuery['enquiry'] : 0;
									echo $bills + $enquiry;
								?>
							</h3>
							<span class="font-bold no-margins">
								Total
							</span>
							<div class="progress m-t-xs full progress-small" style="background-color: darkgray;">
								<div style="width: <?php echo ($bills / ($bills + $enquiry + 1)) * 100; ?>%;" role="progressbar" class=" progress-bar progress-bar-success">
									<span class="sr-only"><?php echo $bills; ?>% Complete (success)</span>
								</div>
							</div>
							<div class="row" style="padding-left: 10px;">
								<div class="col-xs-6">
									<small class="stats-label font-bold">Bills</small>
									<h5><?php echo $bills; ?></h5>
								</div>
								
								<div class="col-xs-6">
									<small class="stats-label font-bold">Enquiry</small>
									<h5><?php echo $enquiry; ?></h5>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="enquiryWalkinReport.php">View Details</a>
					</div>
				</div>
			</div>
			
			<div class="col-lg-3">
				<div class="hpanel stats">				
					<div class="panel-body h-200">
						<div class="stats-title pull-left">
							<h4 style="font-weight: 900">Total Gold</h4>
						</div>
						<div class="stats-icon pull-right">
							<i class="pe-7s-medal fa-4x"></i>
						</div>
						<div class="m-t-xl">						
						</div>
						<div class="row m-t-md" style="padding-top: 60px; padding-left: 10px;">
							<div class="col-lg-6">
								<h3 class="no-margins font-extra-bold text-success">
									<?php echo $transQuery['gold_grossw'] ? ROUND($transQuery['gold_grossw'], 2) : 0;  ?>
									(<?php echo $margin;  ?>)
								</h3>
								<div class="font-bold">Gross Weight </div>
							</div>
							<div class="col-lg-6">
								<h3 class="no-margins font-extra-bold text-success"><?php echo $transQuery['gold_netw'] ? ROUND($transQuery['gold_netw'], 2) : 0; ?></h3>
								<div class="font-bold">Net Weight</div>
							</div>
						</div>						
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>
			
			<div class="col-lg-3">
				<div class="hpanel stats">				
					<div class="panel-body h-200">
						<div class="stats-title pull-left">
							<h4 style="font-weight: 900">Total Silver</h4>
						</div>
						<div class="stats-icon pull-right">
							<i class="pe-7s-medal fa-4x"></i>
						</div>
						<div class="m-t-xl">						
						</div>
						<div class="row m-t-md" style="padding-top: 60px; padding-left: 10px;">
							<div class="col-lg-6">
								<h3 class="no-margins font-extra-bold text-success"><?php echo $transQuery['silver_grossw'] ? ROUND($transQuery['silver_grossw'], 2) : 0; ?></h3>
								<div class="font-bold">Gross Weight</div>
							</div>
							<div class="col-lg-6">
								<h3 class="no-margins font-extra-bold text-success"><?php echo $transQuery['silver_grossw'] ? ROUND($transQuery['silver_grossw'], 2) : 0; ?></h3>
								
								<div class="font-bold">Net Weight</div>
							</div>
						</div>						
					</div>
					<div class="panel-footer">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>
			
			<div class="col-lg-12">
				<div class="col-lg-3">
					<div class="hpanel">
						<div class="panel-body text-center">						
							<h3 class="m-xs"><?php echo $avgPurity; ?>%</h3>
							<h5 class="font-extra-bold no-margins text-success">
								Average Purity
							</h5>
						</div>
						<div class="panel-footer">
							<b>Attica Gold Pvt Ltd</b>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		
	</div>
	<?php include("footer.php"); ?>
	<script src="vendor/sweetalert/lib/sweet-alert.min.js"></script>
	<script type="text/javascript">
		
		(function () {
			
			const todayCOG = document.getElementById("todayCOG");
			todayCOG.addEventListener("click", getTodayCOG);
			async function getTodayCOG(){
				todayCOG.textContent = "Loading...";
				todayCOG.removeEventListener("click", getTodayCOG);;
				
				const empId = todayCOG.dataset.empid;
				const response = await fetch("utils/misc/getCOG.php?zonalOneDayCOG=true&zonalEmpId="+empId);
				const result = await response.text();
				
				todayCOG.textContent = "COG";
				todayCOG.addEventListener("click", getTodayCOG);
				
				swal({
					title: result+"%",
					text: "Todays Zonal Performance"
				});
			}
			
			const monthCOG = document.getElementById("monthCOG");
			monthCOG.addEventListener("click", getMonthCOG);
			async function getMonthCOG(){
				monthCOG.textContent = "Loading...";
				monthCOG.removeEventListener("click", getMonthCOG);;
				
				const empId = monthCOG.dataset.empid;
				const response = await fetch("utils/misc/getCOG.php?zonalThirtyDayCOG=true&zonalEmpId="+empId);
				const result = await response.text();
				
				monthCOG.textContent = "COG";
				monthCOG.addEventListener("click", getMonthCOG);
				
				swal({
					title: result+"%",
					text: "30 Days Zonal Performance"
				});
			}
			
		})();
		
	</script>
