<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master') {
		include("header.php");
		include("menumaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
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
	#wrapper .panel-body{
	box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
	border-radius:7px;
	background-color: #ffffff;
	padding: 20px;
	margin-bottom:20px;
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
	.table-bordered {
    margin-bottom: 5px;
	}
	#month-ID > button:active{
	background-color:#34495E;
	}
	
	input[type="radio"] {
	display: none;
	}	
	input[type="radio"]:checked+label {
	color: #123C69;
	font-size:20px;
	transition: .3s ease-out;
	}
</style>
<div id="wrapper">
	<div class="content row">
		<div class="col-lg-12">
			<div class="hpanel hblue">
				<div class="panel-body">
					<div class="panel-header">
						<div class="col-lg-2">
							<h3>Graph</h3>
						</div>
						<div class="col-lg-3">
							<select class="form-control m-b" id="selType">
								<option value="All Branches" selected="true">All Branches</option>
								<option value="Bangalore">Bangalore</option>
								<option value="Karnataka">Karnataka</option>
								<option value="Chennai">Chennai</option>
								<option value="Tamilnadu">Tamilnadu</option>
								<option value="Hyderabad">Hyderabad</option>
								<option value="AP-TS">AP-TS</option>
								<option value="Pondicherry">Pondicherry</option>
								<option value="" disabled="disabled"><br></option>
								<?php
									$branchData = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE Status=1 ORDER BY branchName");
									while($branchList = mysqli_fetch_array($branchData)){ 
									?>
									<option value="<?php echo $branchList['branchId']; ?>"><?php echo $branchList['branchName']; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-lg-3" style="padding-left:30px;text-align:center">
							<input type="radio" name="monthlyORdaily" id="monthly_ID" value="MONTH-WISE" checked><label for="monthly_ID">MONTHLY</label>
							<input type="radio" name="monthlyORdaily" id="daily_ID" value="DAY-WISE"><label for="daily_ID">DAILY</label>
						</div>
						<div class="col-lg-3">
							<div class="input-group">
								<input type="range" min="1" max="400" value="30" class="form-control" id="selDMnum">
								<span class="input-group-addon text-success" id="DMnumValue" style="border-radius:100px">30</span>
							</div>
						</div>
						<div class="col-sm-1">
							<button class="btn btn-success" id="go" style="margin-top:1px"><span class="fa fa-play"></span></button>
						</div>
					</div>
				</div>
				<div class="panel-body" style="padding:0px">
					<div id="chartDiv" style="padding-top:10px">
						<canvas id="singleBarOptions" height="120"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function(){
			
			var myChart;
			barChartGrossWMonthly('All Branches',30);
			
			$(document).on('input change', '#selDMnum', function() {
				$('#DMnumValue').text($(this).val());
			});
			
			// BUTTOM PRESS
			let go = document.getElementById('go');
			go.addEventListener('click',()=>{
				let branchId = document.getElementById('selType').value,
				mod = document.querySelector('input[name="monthlyORdaily"]:checked').value,
				modNum = document.getElementById('selDMnum').value;
				if(mod == 'MONTH-WISE'){
					barChartGrossWMonthly(branchId,modNum);
				}
				else if(mod == 'DAY-WISE'){
					barChartGrossWDaily(branchId,modNum);
				}
				
			});
			
			// BRANCH CHANGE
			let selBranch = document.getElementById('selType');
			selBranch.addEventListener('change',(e)=>{
				let branchId = e.target.value;
				mod = document.querySelector('input[name="monthlyORdaily"]:checked').value,
				modNum = document.getElementById('selDMnum').value;
				if(mod == 'MONTH-WISE'){
					barChartGrossWMonthly(branchId,modNum);
				}
				else if(mod == 'DAY-WISE'){
					barChartGrossWDaily(branchId,modNum);
				}
			});
					
			function barChartGrossWDaily(branchId,days){
				
				$.ajax({
					url:"enquiryChartData.php",
					type:"POST",
					data:{type:'Daily',branchId:branchId,days:days},
					dataType:'JSON',
					success: function(e){
						var month = [];
						var grossW = [];

						for(var i in e){
							month.push(e[i][0]);
							grossW.push(e[i][1]);

						}
						
						const data = {
							labels: month,
							datasets: [
							{
								label: 'Enquiry Gross Weight Count',
								data: grossW,
								borderColor: 'rgba(18, 60, 105)',
								backgroundColor: 'rgba(18, 60, 105)',
								borderWidth: 0.90,
								pointStyle: 'rectRot',
								radius: 2,
								yAxisID: 'y'
							}
							]
						};
						
						const config = {
							type: 'line',
							data: data,
							options: {
								responsive: true,
								interaction: {
									mode: 'index',
									intersect: false,
								},
								stacked: false,
								scales: {
									x: {
										title: {
											display: true,
											text: 'DATE',
											color: 'rgba(0, 0, 0)',
											font: {
												size: 12,
												weight: 'bold'
											}
										}
									},
									y: {
										type: 'linear',
										display: true,
										position: 'left',
										title: {
											display: true,
											text: 'Enquiry Gross Weight Count',
											color: 'rgba(18, 60, 105)',
											font: {
												size: 12,
												weight: 'bold'
											}
										}
									},
								}
							}
						};
						
						var ctx = document.getElementById("singleBarOptions").getContext("2d");
						if(myChart){
							myChart.destroy();
						}
						myChart = new Chart(ctx, config);
					},
					error: function(){
						$('#chartDiv').html("<h4 style='text-align:center' class='text-success'><b>OOps , Something went wrong :)</b></h4>");
					}
				});
				
			}
			
			function barChartGrossWMonthly(branchId,months){
				
				$.ajax({
					url:"enquirygwtChartData.php",
					type:"POST",
					data:{type:'Monthly',branchId:branchId,months:months},
					dataType:'JSON',
					success: function(e){
						var month = [];
						var grossW = [];
						for(var i in e){
							month.push(e[i][0]);
							grossW.push(e[i][1]);
						}
						
						const data = {
							labels: month,
							datasets: [
							{
								label: 'Enquiry Gross Weight Count',
								data: grossW,
								backgroundColor: 'rgba(153, 0, 0)',
								borderColor: 'rgba(153, 0, 0)',
								borderWidth: 0.80,
								pointStyle: 'rect',
								radius: 3
							}
							]
						};
						
						const config = {
							type: 'line',
							data: data,
							options: {
								responsive: true,
								plugins: {
									legend: {
										position: 'top',
									}
								},
								scales: {
									x: {
										title: {
											display: true,
											text: 'MONTH-YEAR',
											color: 'rgba(18, 60, 105)',
											font: {
												size: 12,
												weight: 'bold'
											}
										}
									},
									y: {
										title: {
											display: true,
											text: 'Enquiry Gross Weight Count',
											color: 'rgba(153, 0, 0)',
											font: {
												size: 12,
												weight: 'bold'
											}
										}
									}
								}
							}
						};
						
						var ctx = document.getElementById("singleBarOptions").getContext("2d");
						if(myChart){
							myChart.destroy();
						}
						myChart = new Chart(ctx, config);
					},
					error: function(){
						$('#chartDiv').html("<h4 style='text-align:center' class='text-success'><b>OOps , Something went wrong :)</b></h4>");
					}
				});
			}
			
		})();
		
	</script>
	<script src="vendor/chartjs/Chart36.min.js"></script>
<?php include("footer.php"); ?>
