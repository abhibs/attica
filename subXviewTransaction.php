<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	$employeeId = $_SESSION['employeeId'];

	if($type == 'SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
?>
<style>	
	.tab .nav-tabs {
	padding: 0;
	margin: 0;
	border: none;
	}
	.tab .nav-tabs li a {
	color: #123C69;
	background: #E3E3E3;
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
	background: #E3E3E3;
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
	font-size: 10px;
	}
	thead tr {
	color: #f2f2f2;
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
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
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
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}	
	.text-success {
	font-weight: 600;
	color: #123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.fa_Icon{
	color: #990000;
	}
	.table-responsive .row{
	margin: 0px;
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
					<h3><i class="fa_Icon fa fa-edit"></i> Cash Transactions </h3>
				</div>
				<!--<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-primary">-->
				<!--	<i style="color:#ffcf40" class="fa fa-spinner"></i> RELOAD-->
				<!--</button>-->
				
	            <div class="tab" role="tabpanel">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#pending" aria-controls="pending" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Pending </a></li>
						<li role="presentation"><a href="#approved" onclick="subTransactionResult('approved')" aria-controls="approved" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Approved</a></li>
						<li role="presentation"><a href="#rejected" onclick="subTransactionResult('rejected')" aria-controls="rejected" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-times"></i> Rejected</a></li>
					</ul>
					
					<div class="tab-content tabs">
						<div role="tabpanel" class="tab-pane fade in active" id="pending">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="pending-datatable" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Time</th>												
												<th width="15%">Customer</th>
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>Amt_Paid</th>
												<th>Type</th>
												<th>Rate</th>
												<th>Margin</th>
												<th>Bills</th>
												<th>Action</th>
												<th>Bill</th>
												<th class="text-center">Timer</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$i = 1;
												$sql = mysqli_query($con,"SELECT B.branchName, T.id, T.name, T.phone, T.billCount, ROUND(T.grossW, 2) AS grossW, ROUND(T.netW, 2) AS netW, ROUND(T.grossA) AS grossA, ROUND(T.netA) As netA, ROUND(T.amountPaid) AS amountPaid, T.rate, T.time, T.date, T.type, ROUND(T.comm, 2) AS comm, ROUND(T.margin) AS margin, T.releaseID 
												FROM trans T,branch B 
												WHERE T.date='$date' AND T.status='ZONAL-VERIFIED' AND T.paymentType='Cash' AND T.branchId=B.branchId AND B.ezviz_vc = '$employeeId'
												ORDER BY T.time");
												
												while($row = mysqli_fetch_assoc($sql)){
													echo ($row['billCount'] > 2) ? "<tr style='color:#f00'>" : "<tr>";
													echo "<td>".$i."</td>";
													echo "<td>".$row['branchName']."</td>";
													echo "<td>".$row['time']."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['grossW']."</td>";
													echo "<td>".$row['netW']."</td>";
													echo "<td>".$row['grossA']."</td>";
													echo "<td>".$row['netA']."</td>";
													echo "<td>".$row['amountPaid']."</td>";
													echo ($row['type'] ==  "Physical Gold") ? "<td>Physical<br>" : "<td>Release<br>";
													if($row['type'] == 'Release Gold'){
														$relType = mysqli_fetch_assoc(mysqli_query($con,"SELECT type,relCash FROM releasedata WHERE releaseID='$row[releaseID]' AND date='$date'"));
														if($relType['type'] == 'CASH/IMPS'){
															echo ($relType['relCash'] == 0) ? '(IMPS)</td>' : '(Cash/IMPS)</td>';
														}
														else{
															echo "(Cash)</td>";
														}
													}
													else{
														echo"</td>";
													}
													echo "<td>".$row['rate']."</td>";
													echo "<td>".$row['margin']."<br>(".$row['comm']."%)</td>";
													if($row['billCount'] > 1){
														echo "<td><a target='_blank' href='existing.php?phone=".$row['phone']."'>".$row['billCount']."</a></td>";
													}
													else{
														echo "<td>".$row['billCount']."</td>";
													}
													echo "<td><a class='btn btn-success btn-md' href='xviewCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'><i style='color:#ffcf40' class='fa fa-check'></i> Action</a></b></td>";
													echo "<td><a class='btn btn-success btn-md' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a></b></td>";
													echo "<td class='timerDisplay text-center' data-starttime=".$row['time']." ></td>";
													echo "</tr>";
													$i++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div role="tabpanel" class="tab-pane fade" id="approved">	
							<div class="panel-body">
								<div class="table-responsive">
									<table id="approved-datatable" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Time</th>
												<th width="15%">Customer</th>
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>Amt_Paid</th>
												<th>Type</th>
												<th>Margin</th>
												<th>Customer</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody id="approved-result">
											
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div role="tabpanel" class="tab-pane fade" id="rejected">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="rejected-datatable" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th width="15%">Customer</th>
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>Amt_Paid</th>
												<th>Margin</th>
												<!--<th>Delete</th>-->
												<th>Customer</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody id="rejected-result">
											
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	
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
	<script>
		$('#pending-datatable').DataTable({
			responsive: true,
			paging: false
		});
		function subTransactionResult(trans_status){
			$('#'+trans_status+'-datatable').DataTable().clear().destroy();
			$.ajax({
				url: "xSubTransactionAjax.php",
				type: "post",
				data: { cash_transaction_status: trans_status},
				success: function(response){
					$('#'+trans_status+'-result').html(response);
					$('#'+trans_status+'-datatable').DataTable({
						responsive: true
					});
				}
			}); 
		}
	</script>

