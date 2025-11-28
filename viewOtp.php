<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else if($type == 'Accounts'){
		include("header.php");
		include("menuacc.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type == 'Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	 else if($type == 'SubZonal'){
                include("header.php");
                include("menuSubZonal.php");
        }
	else if ($type == 'ZonalMaster') {
		include("header.php");
		include("menuzonalMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	$date = (isset($_GET['otpDate'])) ? $_GET['otpDate'] : date('Y-m-d');
	$time = date("d/m/Y", strtotime($date));
	$emp_id = $_SESSION['employeeId'];
?>
<style>
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	border: none;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	}
	.fa_Icon{
	color: #990000;
	}
	.text-success{
	font-weight:600;
	color: #123C69;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	.shown-row{
	background-color: #D9DEE0;
	}
	table th, td{
	text-align: center;
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
					<h3 class="text-success"> CUSTOMER REGISTRATION OTP - <span class="fa_Icon"><?php echo $time; ?></span></h3>
				</div>
				<div class="col-xs-4" style="margin-top:6.5px">
					<?php if($type == 'Master'){ ?>
						<form action="" method="GET">
							<div class="input-group">
								<input type="date" class="form-control" name="otpDate" required>
								<span class="input-group-btn">
									<button class="btn btn-success btn-block" type="submit"><i class="fa fa-search"></i></button>
								</span>
							</div>
						</form>
					<?php } ?>
				</div>
				<div style="clear:both"></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="<?php echo ($type == 'Master') ? 'example1' : 'example5'; ?>" class="table table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Customer</th>
									<th>Mobile</th>
									<th>OTP</th>
									<th>Time</th>
									<th style="width: 28%" class="text-center">Show OTP</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									if($type == 'Master'){
										$query = mysqli_query($con,"SELECT * FROM otp Where date='$date' AND MOBILE BETWEEN 6000000000 AND 9999999999 ORDER BY otpid DESC");
									}
									else{
										$query = mysqli_query($con,"SELECT * FROM otp Where date='$date' AND MOBILE BETWEEN 6000000000 AND 9999999999 ORDER BY otpid DESC LIMIT 20");
									}
									while($row = mysqli_fetch_assoc($query)){
										$otp_id = $row['otpid'];
										
										echo ($row['flag'] == 1) ? "<tr id='row_$otp_id' class='shown-row'>" : "<tr id='row_$otp_id'>";
										
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['customerName'] . "</td>";
										echo "<td>" . $row['mobile'] . "</td>";
										
										if($row['flag']==1){
											echo "<td class='text-success'>" . $row['otp'] . "</td>";
										}
										else{
											echo "<td><span id='hideOtp_$otp_id'>******</span><span id='showOtp_$otp_id' style='display:none;'>". base64_encode($row['otp']) ."</span></td>";
										}
										
										echo "<td>" . $row['time'] . "</td>";
										
										if($row['flag']==1){
											echo '<td style="padding-top:12px; padding-bottom:12px;">'.$row['remarks'].'</td>';
										}
										else{
											echo "<td class='button-to-remarks'>
											<div class='input-group'>
											<input type='text' class='form-control' placeholder='Remarks...' name='otpDate' required>
											<span class='input-group-btn'>
											<button class='btn btn-default text-success' type='button' onclick='executeOtp(this, $otp_id, $emp_id)'><span class='fa fa-thumbs-o-up
											'></span></button>
											</span>
											</div>
											</td>";
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
	</div>
	<?php include("footer.php"); ?>
	<script>	
		function executeOtp(button, otp_id, emp_id){
			const row = document.querySelector('#row_'+otp_id);
			const showMe = document.getElementById("showOtp_"+otp_id);
			const hideMe = document.getElementById("hideOtp_"+otp_id);
			const remarks = row.querySelector('input').value;
			
			if(remarks.replace(/\s/g, '') != ''){
				showMe.classList.add("show");
				hideMe.classList.add("hide");
				showMe.innerHTML = atob(showMe.textContent);
				$.ajax({
					type: "POST",
					url: "searchBillAjax.php",
					data: {action:"flag_otp", otp_id:otp_id, emp_id:emp_id, remarks:remarks},
					success: function(response){
						row.querySelector('.button-to-remarks').innerHTML = remarks;
						row.style.backgroundColor = '#E7BBAA';
					}
				});
			}
			else{
				alert('Remarks is Required');
			}
		}
	</script>
