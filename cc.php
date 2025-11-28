<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	$type=$_SESSION['usertype'];
	
	if($type=='CallDisplay' || $type == 'Master'){
	?>
	<!DOCTYPE html>
	<html>
		
		<!-- Mirrored from webapplayers.com/homer_admin-v2.0/light-shadow/register.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Nov 2017 04:58:07 GMT -->
		<head>
			
			<meta http-equiv="refresh" content="900">
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			
			<!-- Page title -->
			<title>Attica Gold</title>
			
			<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
			<link rel="shortcut icon" type="image/png" href="images/favicon.png" />
			
			<!-- Vendor styles -->
			<link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.css" />
			<link rel="stylesheet" href="vendor/metisMenu/dist/metisMenu.css" />
			<link rel="stylesheet" href="vendor/animate.css/animate.css" />
			<link rel="stylesheet" href="vendor/bootstrap/dist/css/bootstrap.css" />
			
			<!-- App styles -->
			<link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
			<link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/helper.css" />
			<link rel="stylesheet" href="styles/style.css">
			
			<style>
				.heading-text{
				color: #760107;
				font-size: 40px;
				}
				.container .panel-body{
				box-shadow: 10px 15px 15px #999;
				border: none;
				background-color: #f5f5f5;
				border-radius:3px;	
				padding: 0px;
				}
				thead {
				text-transform:uppercase;
				background-color:#123C69;
				}
				thead tr{
				color: #f2f2f2;
				}
				.table tbody td, .table thead th{
				font-size:30px;
				font-weight: 900;
				text-transform: uppercase;
				text-align: center
				}
				.tr-rank{
				background-color: #D6EFD8;
				}
			</style>
		</head>
		<body class="blank">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="text-center m-b-md">
							<h1 class="text-center heading-text font-extra-bold">Attica Gold</h1>
							<p><b><?php date_default_timezone_set("Asia/Kolkata"); echo date("l / d-M-Y"); ?></b></p>
						</div>
						<div class="hpanel">
							<div class="panel-body">
								<div class="table-responsive">
									<table cellpadding="1" cellspacing="1" class="table">
										<thead>
											<tr>																						
												<th width="24%">Rank</th>
												<th width="25%">Agent Id</th>
												<th width="25%">Name</th>
												<th width="25%">Sold</th>
											</tr>
										</thead>
										<tbody id="table-body">											
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 text-right">
						<a href="logout.php" title="Logout">
							<b>Logout</b>
						</a>
					</div>
				</div>
			</div>
			
			<!-- Vendor scripts -->
			<script src="vendor/jquery/dist/jquery.min.js"></script>
			<script src="vendor/jquery-ui/jquery-ui.min.js"></script>
			<script src="vendor/slimScroll/jquery.slimscroll.min.js"></script>
			<script src="vendor/bootstrap/dist/js/bootstrap.min.js"></script>
			<script src="vendor/metisMenu/dist/metisMenu.min.js"></script>
			<script src="vendor/iCheck/icheck.min.js"></script>
			<script src="vendor/sparkline/index.js"></script>
			
			<!-- App scripts -->
			<script src="scripts/homer.js"></script>
			
			<script>
				$(document).ready(function(){
					
					const url = "https://14.97.3.234/Alpha_Attica/Api/sold_out_count";
					const headers = {
						method: "POST",
						From_Billing_Date: '<?php echo date('Y-m-d'); ?>',
						To_Billing_Date: '<?php echo date('Y-m-d'); ?>'
					}
					let tableHtml = "";
					
					const tableBody = document.getElementById("table-body");
					const agentData = {
						"8001" : "Mamatha Y",
						"8002" : 'Mamatha.s',
						"8003" : 'Mamatha.s',
						"8004" : 'Mamatha.s',
						"8005" : 'Mamatha.s',
					};
					const result = {
						"8001" : 10,
						"8002" : 5,
						"8003" : 20,
						"8004" : 0,
						"8005" : 1,
					}
					
					async function getData(){
						// const response = await fetch(url, headers);
						// const result = await response.json();
						
						// if(result){
						// }
						
						let i = 1;
						let tableData = "";
						let data = [];
						let agentName = "";
						for(agent in result){
							agentName = (agentData.hasOwnProperty(agent)) ? agentData[agent] : "";
							data.push([agent, agentName, result[agent]]);
						}
						data
						.sort((a, b)=>{
							return b[2] - a[2];
						})
						.forEach((d)=>{
							if(i <= 3 && +d[2] > 0){
								tableData += "<tr class='tr-rank'><td>"+ i +"</td><td>"+ d[0] +"</td><td>"+ d[1] +"</td><td>"+ d[2] +"</td></tr>";
							}
							else{
								tableData += "<tr><td>"+ i +"</td><td>"+ d[0] +"</td><td>"+ d[1] +"</td><td>"+ d[2] +"</td></tr>";
							}
							i++;
						})
						tableBody.innerHTML = tableData;
					}
					
					getData();
					setInterval(()=>{
						getData();
					}, 5000);
					
				});
			</script>
		</body>
	</html>
	<?php 
	}
	else{
		include("logout.php");
	}
?>
