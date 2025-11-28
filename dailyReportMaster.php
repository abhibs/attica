<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date("Y-m-d");
	if(isset($_GET['getReport'])){
		$date = $_GET['date'];
	}
	
	// ---------------- BILLS DATA --------------------
	
	$cityBillsQuery = mysqli_fetch_all(mysqli_query($con, "SELECT b.city, COUNT(t.id) AS bills, COALESCE(SUM(netW), 0) AS netW, COALESCE(SUM(netA), 0) AS netA 
	FROM trans t 
	RIGHT JOIN branch b ON (t.branchId=b.branchId  AND t.date='$date' AND t.status='Approved' AND t.metal='Gold')
	WHERE b.city IN ('Bengaluru', 'Chennai', 'Hyderabad')
	GROUP BY b.city"), true);
	
	$stateBillsQuery = mysqli_fetch_all(mysqli_query($con, "SELECT b.state, COUNT(t.id) AS bills, COALESCE(SUM(netW), 0) AS netW, COALESCE(SUM(netA), 0) AS netA
	FROM trans t 
	RIGHT JOIN branch b ON (t.branchId=b.branchId  AND t.date='$date' AND t.status='Approved' AND t.metal='Gold')
	WHERE b.city NOT IN ('Bengaluru', 'Chennai', 'Hyderabad')
	GROUP BY b.state"), true);
	
	$totalBills = 0;
	$totalNetAmount = 0;
	$totalNetWeight = 0;
	$cityBillData = [];
	$stateBillData = [];
	foreach ($cityBillsQuery as $key => $value) {
		$totalBills += $value['bills'];
		$totalNetWeight += $value['netW'];
		$totalNetAmount += $value['netA'];
		
		$cityBillData[$value['city']] = $value;
	}
	foreach ($stateBillsQuery as $key => $value) {
		$totalBills += $value['bills'];
		$totalNetWeight += $value['netW'];
		$totalNetAmount += $value['netA'];
		
		$stateBillData[$value['state']] = $value;
	}
	
	// ---------------- WALKING DATA --------------------
	
	$stateEnquiryQuery = mysqli_fetch_all(mysqli_query($con, "SELECT b.state, COUNT(DISTINCT mobile) AS enquiry
	FROM walkin w
	RIGHT JOIN branch b ON (w.branchId=b.branchId AND w.date='$date' AND w.issue!='Rejected')
	GROUP BY b.state"), true);
	
	$totalEnquiry = 0;
	$stateEnquiryData = [];
	foreach ($stateEnquiryQuery as $key => $value) {
		$totalEnquiry += $value['enquiry'];
		$stateEnquiryData[$value['state']] = $value;
	}
	
	// ---------------- ZONAL STATE --------------------
	
	$stateWiseZonal = [];
	$zonalStateQuery =  mysqli_fetch_all(mysqli_query($con, "SELECT employeeId, branch 
	FROM users
	WHERE type='Zonal'"), true);
	foreach ($zonalStateQuery as $key => $value) {
		$stateWiseZonal[$value['branch']][] = $value['employeeId'];
	}
	
	$zonalBillData = [];
	$zonalBillsQuery = mysqli_fetch_all(mysqli_query($con, "SELECT b.ezviz_vc, e.name, COUNT(t.id) AS bills
	FROM trans t 
	RIGHT JOIN branch b ON (t.branchId=b.branchId  AND t.date='$date' AND t.status='Approved')
	JOIN employee e ON b.ezviz_vc=e.empId
	WHERE b.ezviz_vc != ''
	GROUP BY b.ezviz_vc, e.name"), true);
	foreach ($zonalBillsQuery as $key => $value) {
		$zonalBillData[$value['ezviz_vc']] = $value;
	}
	
	$zonalEnquiryData = [];
	$zonalEnquiryQuery = mysqli_fetch_all(mysqli_query($con, "SELECT b.ezviz_vc, COUNT(DISTINCT mobile) AS enquiry
	FROM walkin w
	RIGHT JOIN branch b ON (w.branchId=b.branchId  AND w.date='$date' AND w.issue!='Rejected')
	WHERE b.ezviz_vc != ''
	GROUP BY b.ezviz_vc"), true);
	foreach ($zonalEnquiryQuery as $key => $value) {
		$zonalEnquiryData[$value['ezviz_vc']] = $value;
	}
	
	// GOLD RATE (BENGALURU)
	$cityGoldRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash FROM gold WHERE date='$date' AND type='Gold' AND city='Bangalore' ORDER BY id DESC LIMIT 1"));
	
?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 80px 50px;
	background-color: #f5f5f5;
	color: #454d5b;
	font-weight: bold;
	font-family: Helvetica;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	
	.heading{
	color: #483f38;
	font-size: 30px;
	font-weight: 900;
	}
	.main-table{
	width: 100%;		
	border: 1px solid #454d5b;
	margin-top: 10px;
	border-collapse: true;
	font-size: 30px;
	}
	.main-table th, .main-table td{
	color: #483f38;
	border: 1px solid #454d5b;
	padding-left: 10px;
	padding-top: 5px;
	padding-bottom: 5px;
	}
	.kar-table{
	background-color: #e4d3cb;
	}
	.apt-table{
	background-color: #d0cbda;
	}
	.tn-table{
	background-color: #a2bdce;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">										
					<div class="col-lg-9">
						<h3>DAILY REPORT</h3>
					</div>					
					<div class="col-lg-3">
						<form method="GET" action="">
							<div class="input-group">
								<input class="form-control" type="date" required name="date">
								<div class="input-group-btn">
									<button type="submit" name="getReport" class="btn btn-default"><i class="fa fa-search"></i></button>
								</div>						
							</div>		
						</form>
					</div>						
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body" style="background-color: #e8eeeb;">
					<div class="col-sm-12 heading">
						Daily Report
						<span style="float: right;"><?php echo date("d-m-Y", strtotime($date)); ?></span>
					</div>
					
					<div class="col-sm-6">
						<table class="main-table" >
							<tr>
								<th width="50%">Walkin</th>
								<td><?php echo $totalBills + $totalEnquiry; ?></td>
							</tr>
							<tr>
								<th width="50%">Bills</th>
								<td><?php echo $totalBills; ?></td>								
							</tr>
							<tr>
								<th width="50%">COG</th>								
								<td><?php echo (($totalBills + $totalEnquiry) > 0) ? ROUND((($totalBills/($totalBills + $totalEnquiry))*100), 1) : 0 ?>%</td>
							</tr>
						</table>
					</div>
					
					<div class="col-sm-6">
						<table class="main-table" >
							<tr>
								<th width="50%">BLR Rate</th>
								<td><?php echo $cityGoldRate['cash']; ?></td>
							</tr>
							<tr>							
								<th width="50%">Net Amt</th>	
								<td><?php echo preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $totalNetAmount); ?></td>										
							</tr>
							<tr>						
								<th width="50%">Net Wgt</th>		
								<td><?php echo ROUND($totalNetWeight); ?></td>				
							</tr>							
						</table>
					</div>
					
					<div class="col-sm-12"><br></div>
					
					<!-- KARNATAKA -->
					<div class="col-sm-6" style="padding-right: 0px">
						<table class="main-table  kar-table" >
							<tr>
								<th width="50%">Bengaluru</th>
								<td ><?php echo ROUND($cityBillData['Bengaluru']['netW']); ?></td>	
							</tr>
							<tr>
								<th width="50%">Karnataka</th>
								<td><?php echo ROUND($stateBillData['Karnataka']['netW']); ?></td>							
							</tr>
							<tr style="background-color: #e99269">
								<th width="50%" >Total</th>								
								<td><?php echo ROUND($cityBillData['Bengaluru']['netW'] + $stateBillData['Karnataka']['netW']); ?></td>		
							</tr>
						</table>
					</div>
					<div class="col-sm-6" style="padding-left: 0px;">
						<table class="main-table kar-table" >
							<tr>
								<th width="50%">Walkin</th>
								<td><?php echo $cityBillData['Bengaluru']['bills'] + $stateBillData['Karnataka']['bills'] + $stateEnquiryData['Karnataka']['enquiry'] ?></td>
							</tr>
							<tr>
								<th width="50%">Bills</th>
								<td><?php echo $cityBillData['Bengaluru']['bills'] + $stateBillData['Karnataka']['bills']; ?></td>						
							</tr>
							<tr style="background-color: #e99269">
								<th width="50%">COG</th>								
								<td>
									<?php
										if(($cityBillData['Bengaluru']['bills'] + $stateBillData['Karnataka']['bills'] + $stateEnquiryData['Karnataka']['enquiry']) > 0){
											echo ROUND( (($cityBillData['Bengaluru']['bills'] + $stateBillData['Karnataka']['bills'])/($cityBillData['Bengaluru']['bills'] + $stateBillData['Karnataka']['bills'] + $stateEnquiryData['Karnataka']['enquiry'])) * 100, 1);
										}
										else{
											echo 0;
										}
									?>
									%							
								</td>	
							</tr>
						</table>
					</div>
					<!-- END OF KARNATAKA -->
					
					<div class="col-sm-12"><br></div>
					
					<!-- ANDHRA PRADESH AND TELANGANA -->
					<div class="col-sm-6" style="padding-right: 0px">
						<table class="main-table apt-table">
							<tr>
								<th width="50%">Hyderabad</th>
								<td><?php echo ROUND($cityBillData['Hyderabad']['netW']); ?></td>		
							</tr>
							<tr>
								<th width="50%">AP & TS</th>
								<td><?php echo ROUND($stateBillData['Andhra Pradesh']['netW'] + $stateBillData['Telangana']['netW']); ?></td>							
							</tr>
							<tr style="background-color: #9f7fe0">
								<th width="50%">Total</th>								
								<td><?php echo ROUND($cityBillData['Hyderabad']['netW'] + $stateBillData['Andhra Pradesh']['netW'] + $stateBillData['Telangana']['netW']); ?></td>		
							</tr>
						</table>
					</div>
					<div class="col-sm-6" style="padding-left: 0px;">
						<table class="main-table apt-table">
							<tr>
								<th width="50%">Walkin</th>
								<td><?php echo $cityBillData['Hyderabad']['bills'] + $stateBillData['Andhra Pradesh']['bills'] + $stateBillData['Telangana']['bills'] + $stateEnquiryData['Andhra Pradesh']['enquiry'] + $stateEnquiryData['Telangana']['enquiry']; ?></td>
							</tr>
							<tr>
								<th width="50%">Bills</th>
								<td><?php echo $cityBillData['Hyderabad']['bills'] + $stateBillData['Andhra Pradesh']['bills'] + $stateBillData['Telangana']['bills']; ?></td>						
							</tr>
							<tr style="background-color: #9f7fe0">
								<th width="50%">COG</th>								
								<td>
									<?php
										if(($cityBillData['Hyderabad']['bills'] + $stateBillData['Andhra Pradesh']['bills'] + $stateBillData['Telangana']['bills'] + $stateEnquiryData['Andhra Pradesh']['enquiry'] + $stateEnquiryData['Telangana']['enquiry']) > 0){
											echo ROUND((($cityBillData['Hyderabad']['bills'] + $stateBillData['Andhra Pradesh']['bills'] + $stateBillData['Telangana']['bills'])/ ($cityBillData['Hyderabad']['bills'] + $stateBillData['Andhra Pradesh']['bills'] + $stateBillData['Telangana']['bills'] + $stateEnquiryData['Andhra Pradesh']['enquiry'] + $stateEnquiryData['Telangana']['enquiry']))* 100, 1);
										}
										else{
											echo 0;
										}
									?>
									%							
								</td>	
							</tr>
						</table>
					</div>	
					<!-- END OF ANDHRA PRADESH AND TELANGANA -->
					
					<div class="col-sm-12"><br></div>
					
					<!-- TAMILNADU AND PONDICHERRY -->
					<div class="col-sm-6" style="padding-right: 0px">
						<table class="main-table tn-table">
							<tr>
								<th width="50%">Chennai</th>
								<td><?php echo ROUND($cityBillData['Chennai']['netW']); ?></td>		
							</tr>
							<tr>
								<th width="50%">Tamilnadu</th>	
								<td><?php echo ROUND($stateBillData['Tamilnadu']['netW'] + $stateBillData['Pondicherry']['netW']); ?></td>									
							</tr>
							<tr style="background-color: #5fa6d3">
								<th width="50%">Total</th>								
								<td><?php echo ROUND($cityBillData['Chennai']['netW'] + $stateBillData['Tamilnadu']['netW'] + $stateBillData['Pondicherry']['netW']); ?></td>		
							</tr>
						</table>
					</div>
					<div class="col-sm-6" style="padding-left: 0px;">
						<table class="main-table tn-table">
							<tr>
								<th width="50%">Walkin</th>
								<td><?php echo $cityBillData['Chennai']['bills'] + $stateBillData['Tamilnadu']['bills'] + $stateBillData['Pondicherry']['bills'] + $stateEnquiryData['Tamilnadu']['enquiry'] + $stateEnquiryData['Pondicherry']['enquiry']; ?></td>
							</tr>
							<tr>
								<th width="50%">Bills</th>
								<td><?php echo $cityBillData['Chennai']['bills'] + $stateBillData['Tamilnadu']['bills'] + $stateBillData['Pondicherry']['bills']; ?></td>					
							</tr>
							<tr style="background-color: #5fa6d3">
								<th width="50%">COG</th>								
								<td>
									<?php
										if(($cityBillData['Chennai']['bills'] + $stateBillData['Tamilnadu']['bills'] + $stateBillData['Pondicherry']['bills'] + $stateEnquiryData['Tamilnadu']['enquiry'] + $stateEnquiryData['Pondicherry']['enquiry']) > 0){
											echo ROUND( (($cityBillData['Chennai']['bills'] + $stateBillData['Tamilnadu']['bills'] + $stateBillData['Pondicherry']['bills']) / ($cityBillData['Chennai']['bills'] + $stateBillData['Tamilnadu']['bills'] + $stateBillData['Pondicherry']['bills'] + $stateEnquiryData['Tamilnadu']['enquiry'] + $stateEnquiryData['Pondicherry']['enquiry'])) * 100 , 1);
										}
										else{
											echo 0;
										}
									?>
									%							
								</td>	
							</tr>
						</table>
					</div>	
					<!-- END OF TAMILNADU AND PONDICHERRY -->
					
					<div class="col-sm-12"><br></div>
					
					<!-- ZONAL DATA -->
					<?php foreach($stateWiseZonal as $key => $value){ ?>
						<div class="col-sm-4">
							<table class="main-table">
								<caption><?php echo $key; ?></caption>
								<?php foreach($value as $key1 => $value1){ ?>
									<tr class="table-body">
										<td width="60%" style="font-size: 20px;"><?php echo $zonalBillData[$value1]['name'] ?></td>
										<td width="40%" style="font-size: 25px;">
											<?php 
												
												$bills =  $zonalBillData[$value1]['bills'];
												$enquiry = $zonalEnquiryData[$value1]['enquiry'];
												
												if($bills + $enquiry){
													echo ROUND((($bills) / ($bills + $enquiry)) * 100 , 1);
												}
												else{
													echo 0;
												}
											?>									
										</td>
									</tr>
								<?php } ?>							
							</table>
						</div>
					<?php } ?>
					<!-- END OF ZONAL DATA -->
					
				</div>
			</div>
		</div>
		
	</div>
	<?php include("footer.php"); ?>
