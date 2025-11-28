<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type == 'ZonalMaster') {
        include("header.php");
        include("menuzonalMaster.php");
	}
	else if($type == 'AccHead') {
        include("header.php");
        include("menuaccHeadPage.php");
	}
	else if($type == 'Zonal') {
		include("header.php");
		include("menuZonal.php");
	}
	else if ($type == 'SubZonal') {
		include("header.php");
		include("menuSubZonal.php");
	}
	else if ($type == 'VM-WAIT') {
		include("header.php");
		include("menuvmWait.php");
	}
	else {
		include("logout.php");
	}
	$date = date('Y-m-d');
	
	
	$user = "";
	$everyCustomerQuery = "";
	$branchQuery = "";
	
	if($type == 'SubZonal') {
		$user = "ZONAL";
		$state = "";
		if($_SESSION['branchCode']=="TN"){
			$state = "state IN ('Tamilnadu', 'Pondicherry')";
		}
		elseif($_SESSION['branchCode']=="AP-TS"){
			$state = "state IN ('Andhra Pradesh', 'Telangana')";
		}
		elseif($_SESSION['branchCode']=="KA"){
			$state = "state IN ('Karnataka')";
		}
		$everyCustomerQuery ="SELECT customer, contact, branch, date, time, status
		FROM everycustomer
		WHERE reg_type = 'ZONAL' AND date='$date' AND branch IN (SELECT branchId FROM branch WHERE ".$state.")";
		$branchQuery = "SELECT branchId, branchName FROM branch WHERE status = 1 AND ".$state;
	}
	else if($type == 'Master'){
		$user = "MASTER";
		$everyCustomerQuery = "SELECT customer, contact, branch, date, time, status
		FROM everycustomer
		WHERE reg_type = 'MASTER' AND date='$date'";
		$branchQuery = "SELECT branchId, branchName FROM branch WHERE status = 1";
	}
	else if($type == 'VM-WAIT'){
		$user = "VM-WAIT";
		$everyCustomerQuery = "SELECT customer, contact, branch, date, time, status
		FROM everycustomer
		WHERE reg_type = 'VM-WAIT' AND date='$date'";
		$branchQuery = "SELECT branchId, branchName FROM branch WHERE status = 1";
	}
	else if($type == 'ZonalMaster' || $type == 'SundayUser'){
		$user = "ZonalMaster";
		$everyCustomerQuery = "SELECT customer, contact, branch, date, time, status
		FROM everycustomer
		WHERE reg_type = 'ZonalMaster' AND date='$date'";
		$branchQuery = "SELECT branchId, branchName FROM branch WHERE status = 1";
	}
	else if($type == 'AccHead'){
		$user = "ACCOUNTS";
		$everyCustomerQuery = "SELECT customer, contact, branch, date, time, status
		FROM everycustomer
		WHERE reg_type = 'ACCOUNTS' AND date='$date'";
		$branchQuery = "SELECT branchId, branchName FROM branch WHERE status = 1";
	}
	
	$everyCustomerData = mysqli_query($con, $everyCustomerQuery);
	$branchList = mysqli_query($con, $branchQuery);
	
	$branchData = [];
	while($row = mysqli_fetch_assoc($branchList)){
		$branchData[$row['branchId']] = $row['branchName'];
	}
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	#wrapper .hbuilt{
	background-color: #f5f5f5;
	border-radius:3px;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	}
	thead{
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:10px;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 12px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	form button[type="submit"]{
	color: #fffafa;
	background-color: #123C69;
	}
	form h5{
	color: #123C69;
	text-transform: uppercase;
	font-size: 12px;
	}
</style>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-5">
			<div class="hpanel">
				<div class="panel-heading hbuilt text-center">
					<h3 class="text-success">Add New Customer</h3>
				</div>
				<div class="panel-body">
					<form name="add-new-customer" method="POST" class="form-horizontal" action="xsubmit.php" onsubmit="vmSubmitNC.disabled = true; return true;">
						<input type="hidden" name="VMsubmitNCHidden" value="true">
						<input type="hidden" name="userType" value="<?php echo $user; ?>">
						
						<div class="col-sm-12">
							<select class="form-control m-b" name="branchID" required>
								<option selected="true" disabled="disabled" value="">Select Branch</option>
								<?php
									foreach ($branchData as $key=>$value) {
										echo '<option  value="' . $key . '">' . $value . '</option>';
									}
								?>
							</select>
						</div>
						
						<div class="col-sm-6">
							<h5>Name</h5>
							<input type="text" name="name" id="name" placeholder="Customer Name" class="form-control" autocomplete="off" required>
						</div>
						<div class="col-sm-6">
							<h5>Mobile</h5>
							<input type="text" name="contact" id="mobileZonal" placeholder="Contact Number" maxlength="10" class="form-control" autocomplete="off" required>
						</div>
						
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-4">
							<select class="form-control m-b" name="type" id="billType" required>
								<option selected="true" disabled="disabled" value="">Type</option>
								<option value="physical">Physical</option>
								<option value="release">Release</option>
							</select>
						</div>
						<div id="typeData"></div>
						
						<label class="col-sm-12 control-label"></label>
						<div class="col-sm-4">
							<h5>No of Ornaments</h5>
							<input type="text" name="itemCount" placeholder="Count" required class="form-control" autocomplete="off">
						</div>
						<div class="col-sm-4">
							<h5>Gross Weight</h5>
							<input type="text" name="grossW" placeholder="Gross W" required class="form-control" autocomplete="off">
						</div>
						<div class="col-sm-4">
							<h5>Hallmark</h5>
							<select class="form-control m-b" name="hallmark" required>
								<option selected="true" disabled="disabled" value="">Select</option>
								<option value="yes">Hallmark</option>
								<option value="no">Non Hallmark</option>
							</select>
						</div>
						<label class="col-sm-12 control-label"></label>
						<div class="col-sm-12">
							<textarea name="remarks" placeholder="Remarks" class="form-control" autocomplete="off"></textarea>
						</div>
						
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-4">
							<a onClick="zonalGenerateOTP()" id="btn" class="btn btn-success btn-block"><i class="fa_Icon fa fa-paper-plane"></i> OTP</a>
						</div>
						<div class="col-sm-4">
							<input type="text" placeholder="OTP" class="form-control" maxlength="6" required name="otp" id="xotpZonal">
						</div>
						<div class="col-sm-4">
							<button class="btn btn-success btn-block" id="updateCustomer" name="zonalSubmitNC" type="submit"><span class="fa_Icon fa fa-save"></span> SUBMIT</button>
						</div>
						
					</form>
				</div>
			</div>
		</div>
		
		<div class="col-lg-7">
			<div class="hpanel">
				<div class="panel-heading hbuilt text-center">
					<h3 class="text-success"><?php echo $user; ?> Added Customers</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Customer</th>
									<th>Mobile</th>
									<th>Date</th>
									<th>Time</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									while($row = mysqli_fetch_assoc($everyCustomerData)){
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$branchData[$row['branch']]."</td>";
										echo "<td>".$row['customer']."</td>";
										echo "<td>".$row['contact']."</td>";
										echo "<td>".$row['date']."</td>";
										echo "<td>".$row['time']."</td>";
										echo "<td>".$row['status']."</td>";
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
	<script>
		const type = document.getElementById("billType");
		const typeData = document.getElementById('typeData');
		
		const physicalData = "<div class='col-sm-8 text-center'><div class='radio radio-info radio-inline'><input type='radio' value='with' name='withMetal' checked=''><label> With Gold </label></div><div class='radio radio-info radio-inline'><input type='radio' value='without' name='withMetal'><label> Without Gold </label></div></div><div class='col-sm-8 text-center' style='margin-bottom: 20px; margin-top: 10px;'><div class='radio radio-info radio-inline'><input type='radio' value='no' name='pledge' checked=''><label> Billing </label></div><div class='radio radio-info radio-inline'><input type='radio' value='yes' name='pledge'><label> Pledge </label></div></div>";
		
		const releaseData = "<div class='col-sm-4'><select class='form-control m-b' name='relSlips' required><option selected='true' disabled='disabled' value=''>Rel Slips</option><option value='yes'>With Slips</option><option value='no'>Without Slips</option></select></div><div class='col-sm-4'><input type='text' name='relAmount' placeholder='Rel Amount' class='form-control' autocomplete='off' required></div>";
		
		type.addEventListener('change', (e)=>{
			let billType = type.value;
			if(billType == 'physical'){
				typeData.innerHTML = physicalData;
			}
			else if(billType == 'release'){
				typeData.innerHTML = releaseData;
			}
		});
	</script>
	<script>
		function zonalGenerateOTP() {
			var data = $('#mobileZonal').val(),
			name = $('#name').val(),
			mobileCount = (data,count=0)=>{
				if(data){
					return mobileCount(Math.floor(data / 10), ++count);
				}
				return count;
			};
			if(mobileCount(data) == 10){
				var req1 = $.ajax({
					url: "ot.php",
					type: "POST",
					data: {
						data: data,
						name: name
					},
				});
				req1.done(function(msg) {
					alert("OTP is sent to customer's mobile");
					$('#btn').attr("disabled", true);
				});
			}
			else{
				alert('Wrong Contact Information!!!');
			}
		}
		
		$(document).ready(function() {
			// DISABLE / ENABLE SUBMIT BUTTON
			$('#updateCustomer').attr("disabled", true);
			$("#xotpZonal").keyup(function() {
				var data = $('#xotpZonal').val();
				var count = data.toString().length;
				if(count==6){
					var req = $.ajax({
						url: "otpValid.php",
						type: "POST",
						data: {
							data
						},
					});
					req.done(function(msg) {
						$("#xotpZonal").val(msg);
						if (msg == "OTP Validated") {
							$('#xotpZonal').attr('readonly', 'true');
							$('#updateCustomer').attr("disabled", false);
						}
						else if (msg == "Invalid OTP") {
							alert(msg);
						}
					});
				}
			});	
		});
	</script>
<?php include("footer.php"); ?>
