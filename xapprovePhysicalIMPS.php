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
	else if ($type == 'AccHead') {
		include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type=='Accounts IMPS'){
		include("header.php");
		include("menuimpsAcc.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	$transVerified = [];
	$transApproved = [];
	$transRejected = [];
	$transSQL = mysqli_query($con,"SELECT t.id, t.name, t.phone, t.amountPaid, t.type, t.time, t.paymentType, t.status, t.cashA, t.impsA, t.time, b.branchName, approvetime
	FROM trans t,branch b 
	WHERE t.date='$date' AND t.paymentType IN ('NEFT/RTGS','Cash/IMPS') AND t.branchId=b.branchId AND t.status IN ('Verified','Approved','Rejected')");
	while($row = mysqli_fetch_assoc($transSQL)){
		if($row['status'] == 'Verified'){
			$transVerified[] = $row;
		}
		else if($row['status'] == 'Approved'){
			$transApproved[] = $row;
		}
		else if($row['status'] == 'Rejected'){
			$transRejected[] = $row;
		}
	}
	
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	.tab {
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs {
	padding: 0;
	margin: 0;
	border: none;
	}
	.tab .nav-tabs li a {
	color: #123C69;
	background: #f5f5f5;
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
	.tab .nav-tabs li.active a:hover {
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius: 3px;
	}
	.tab .nav-tabs li a:before {
	content: "";
	background: #f5f5f5;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s, height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before {
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab-content h4 {
	color: #123C69;
	font-weight: 500;
	}
	@media only screen and (max-width: 479px) {
	.tab .nav-tabs {
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li {
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a {
	margin: 0 0 5px;
	}
	}
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	thead {
	text-transform: uppercase;
	background-color: #123C69;
	}
	thead tr {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}
	.btn-primary {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 12px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.text-success {
	font-weight: 600;
	color: #123C69;
	}
	.hpanel .panel-body {
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
	}
	.fa{
	color:#990000;
	}
	.timerDisplay{
	font-family: monospace;
	font-weight: bold;
	letter-spacing: 4px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				
				<div class="panel-heading">
					<h3><i class="trans_Icon fa fa-edit" ></i> Transaction Approval <span style="color:#990000">(IMPS)</span></h3>
				</div>
				<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-primary"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b></button>
				<div class="tab" role="tabpanel">
					
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#Pending" aria-controls="pending" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Pending </a></li>
						<li role="presentation"><a href="#Approved" aria-controls="approved" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Approved</a></li>
						<li role="presentation"><a href="#Rejected" aria-controls="rejected" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-times"></i> Rejected</a></li>
					</ul>
					
					<div class="tab-content tabs">
						
						<!--  PENDING  -->
						<div role="tabpanel" class="tab-pane fade in active" id="Pending">
							<div class="panel-body">
								<table id="example5" class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Branch</th>
											<th>Customer</th>
											<th>Mobile</th>
											<th>Gold Type</th>
											<th>Rate</th>
											<th>Amount</th>
											<th>IMPS</th>
											<th>Cash</th>
											<th>Time</th>
											<th class="text-center">Action</th>
											<th class="text-center">Verified<br>Time</th>
											<th class="text-center">Timer</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											foreach($transVerified as $key=>$value){
												echo "<tr>";
												echo "<td>".$i."</td>";
												echo "<td>".$value['branchName']."</td>";
												echo "<td>".$value['name']."</td>";
												echo "<td>".$value['phone']."</td>";
												echo "<td>".$value['type']."</td>";
												echo ($value['paymentType'] == 'Cash' || $value['paymentType'] == 'Cash/IMPS') ? "<td>Cash Rate</td>" : "<td>IMPS Rate</td>";
												echo "<td>".$value['amountPaid']."</td>";
												echo "<td>".$value['impsA']."</td>";
												echo "<td>".$value['cashA']."</td>";
												echo "<td>".$value['time']."</td>";
												echo "<td style='text-align:center'><a class='btn btn-primary btn-md' href='xapprovePhysicalIMPSData.php?id=".$value['id']."'><i style='color:#ffcf40' class='fa fa-check'></i> Action</a></td>";
												echo "<td class='text-center'>".$value['approvetime']."</td>";
												echo "<td class='timerDisplay text-center' data-starttime=".$value['approvetime']." ></td>";
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						
						<!--  APPROVED  -->
						<div role="tabpanel" class="tab-pane fade" id="Approved">
							<div class="panel-body">
								<table id="example6" class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Branch</th>
											<th>Customer</th>
											<th>Mobile</th>
											<th>Gold Type</th>
											<th>Rate</th>
											<th>Amount</th>
											<th>IMPS</th>
											<th>Cash</th>
											<th>Time</th>
											<th class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											foreach($transApproved as $key=>$value){
												echo "<tr>";
												echo "<td>".$i."</td>";
												echo "<td>".$value['branchName']."</td>";
												echo "<td>".$value['name']."</td>";
												echo "<td>".$value['phone']."</td>";
												echo "<td>".$value['type']."</td>";
												echo ($value['paymentType'] == 'Cash' || $value['paymentType'] == 'Cash/IMPS') ? "<td>Cash Rate</td>" : "<td>IMPS Rate</td>";
												echo "<td>".$value['amountPaid']."</td>";
												echo "<td>".$value['impsA']."</td>";
												echo "<td>".$value['cashA']."</td>";
												echo "<td>".$value['time']."</td>";
												echo "<td style='text-align:center'><a class='btn btn-primary btn-md' href='xapprovePhysicalIMPSData.php?id=".$value['id']."'><i style='color:#ffcf40' class='fa fa-check'></i> View</a></td>";
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						
						<!--  REJECTED  -->
						<div role="tabpanel" class="tab-pane fade" id="Rejected">
							<div class="panel-body">
								<table id="example7" class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Branch</th>
											<th>Customer</th>
											<th>Mobile</th>
											<th>Gold Type</th>
											<th>Rate</th>
											<th>Amount</th>
											<th>IMPS</th>
											<th>Cash</th>
											<th>Time</th>
											<th class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											foreach($transRejected as $key=>$value){
												echo "<tr>";
												echo "<td>".$i."</td>";
												echo "<td>".$value['branchName']."</td>";
												echo "<td>".$value['name']."</td>";
												echo "<td>".$value['phone']."</td>";
												echo "<td>".$value['type']."</td>";
												echo ($value['paymentType'] == 'Cash' || $value['paymentType'] == 'Cash/IMPS') ? "<td>Cash Rate</td>" : "<td>IMPS Rate</td>";
												echo "<td>".$value['amountPaid']."</td>";
												echo "<td>".$value['impsA']."</td>";
												echo "<td>".$value['cashA']."</td>";
												echo "<td>".$value['time']."</td>";
												echo "<td style='text-align:center'><a class='btn btn-primary btn-md' href='xapprovePhysicalIMPSData.php?id=".$value['id']."'><i style='color:#ffcf40' class='fa fa-check'></i> View</a></td>";
												echo "</tr>";
												$i++;
											}
											$transVerified = null;
											$transApproved = null;
											$transRejected = null;
										?>
									</tbody>
								</table>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
	<script>
		const timerDisplay = document.querySelectorAll(".timerDisplay");
		
		const convertMS = (ms)=>{
			var d, h, m, s;
			s = Math.floor(ms / 1000);
			m = Math.floor(s / 60);
			s = s % 60;
			h = Math.floor(m / 60);
			m = m % 60;
			d = Math.floor(h / 24);
			h = h % 24;
			h += d * 24;
			return {
				hour: h,
				min: m,
				sec: s
			}
		}	
		
		const showCountDown = ()=>{
			const current_date_time = new Date();	
			
			timerDisplay.forEach((td)=>{			
				const starttime = td.dataset.starttime;
				const start_time_arr = starttime.split(":");
				
				const tdTime = new Date(current_date_time.getFullYear(), +current_date_time.getMonth(), current_date_time.getDate(), start_time_arr[0], start_time_arr[1], start_time_arr[2]);
				const diff = current_date_time - tdTime;
				
				const { hour, min, sec } = convertMS(diff)	
				
				let timeText = (hour % 12 > 0 ) ? hour % 12 +":" : "";
				timeText += min +":"+ sec;
				
				td.textContent = timeText;
				
				const parentTr = td.parentElement;
				if(hour % 12 == 0){
					if(min < 5){
						parentTr.style.backgroundColor = "#c9df8a";
					}
					else if(min >= 5 && min < 10){
						parentTr.style.backgroundColor = "#ffebaa";
					}
					else if(min >= 10 && min < 15){
						parentTr.style.backgroundColor = "#ffb38a";
					}
					else{
						parentTr.style.backgroundColor = "#ff7b7b";
					}
				}
				else{
					parentTr.style.backgroundColor = "#b6b6b6";
				}
				
			})
		}
		
		const timer = setInterval(showCountDown, 1000);
		showCountDown();
	</script>
