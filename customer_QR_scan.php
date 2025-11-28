<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);

	include("dbConnection.php");
	include('libs/phpqrcode/qrlib.php');
	include("header.php");
	$tempDir = 'temp/';
	$date = date('Y-m-d');
	$branch = $_GET['branch'];
	$date = date('Y-m-d');
	

?>
<link rel="stylesheet" href="vm/styles/vmStyle.css">
<style>
	.list-cust{
		list-style-type: square;
		margin: 0;
		padding-left: 15px;
	}
	.li-cust{
		padding-bottom: 15px;
	}
	#wrapper {
		margin: 0px;
		background-image: linear-gradient(to right, #F5F7FA, #B8C6DB);
	}
	.mobile-menu, .navbar-right, .header-link, .hide-menu{
		display:none;
	}
	.navbar-form-custom{
		margin-left:20px;
	}
	#header{
		display: none;
	}
	.center {
		display: block;
		margin-left: auto;
		margin-right: auto;
		width: 50%;
	}
	body{
		/*background:#E3E3E3;*/
		background-image: linear-gradient(to right, #F5F7FA, #B8C6DB);
	}
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-4">
		</div>
		<div class="col-lg-4">
			<div class="hpanel">
				
				<div class="panel-heading hbuilt" style="margin-bottom:10px;background:#990000;background-image: linear-gradient(to right, #A40606, #D98324);">
					<img src="images/atticalogo.png" class="center">								
				</div>
				
				<div class="panel-body" style="padding : 5px 30px 15px 30px;height:500px;background:#ffffff;">
					<div class="panel-heading text-center">
						<h3 class="text-success text-center"><b>SCAN QR CODE</b></span></h3>
					</div>
					<div class="col-sm-12 col-md-12 text-center"><div class="form-group" style="margin-top:10px">
							<label class="col-sm-2 control-label text-success">STATE</label>
							<div class="col-sm-10">
								<select class="form-control m-b" id="state" name="state" required onchange="fetch_branches()">
									<option selected="true" disabled="disabled" value="">Select State</option>
									<option  value="Karnataka">Karnataka</option>
									<option  value="Tamilnadu">Tamilnadu</option>
									<option  value="Pondicherry">Pondicherry</option>
									<option  value="Andhra Pradesh">Andhra Pradesh</option>
									<option  value="Telangana">Telangana</option>

								</select>
							</div>
						</div>						
						<div class="form-group" style="margin-top:10px">
							<label class="col-sm-2 control-label text-success">BRANCH</label>
							<div class="col-sm-10">
								<select class="form-control m-b" id="branchID" name="branchID" required onchange="fetch_qr_code()">
									<option selected="true" disabled="disabled" value="">Select Branch</option>
									<?php
										foreach ($branchData as $key=>$value) {
											echo '<option  value="' . $value['branchId'] . '">' . $value['branchName'] . '</option>';
										}
									?>
								</select>
							</div>
						</div>
					</div>
					<div id="qr_code_display" class="col-sm-12 col-md-12 text-center">

					</div>	
					<div class="col-sm-12 col-md-12 text-center"></div>
				</div>


			</div>
		</div>
		

		
		<div style="clear:both"></div>
	</div>
	<script>
		function generateOTP() {
			var data = $('#mobile').val(),
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
			$('#qr_register_customer').attr("disabled", true);
			$("#xotp").keyup(function() {
				var data = $('#xotp').val();
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
						$("#xotp").val(msg);
						if (msg == "OTP Validated") {
							$('#xotp').attr('readonly', 'true');
							$('#qr_register_customer').attr("disabled", false);
						}
						else if (msg == "Invalid OTP") {
							alert(msg);
						}
					});
				}
			});	
		});
		
		
	function fetch_branches() {
		
		var state = document.getElementById("state").value;

		var req = 	$.ajax({
						url: "add.php",
						type: "POST",
						data: {state:state,fetch_branches:"branch_data"},
					});
					req.done(function(response) {
					
						$("#branchID").html(response);

					});
	}

	function fetch_qr_code() {
		
		var branchID = document.getElementById("branchID").value;
		//alert(branchID)
		var req = 	$.ajax({
						url: "add.php",
						type: "POST",
						data: {branch:branchID,fetch_qr_code:"branch_QR"},
					});
					req.done(function(response) {
						//alert(response)
						$("#qr_code_display").html(response);

					});
	}	

   document.addEventListener('contextmenu',(e)=>{
        e.preventDefault();
    });
    document.onkeydown = function(e) {
        if(event.keyCode == 123) {
         return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
         return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
         return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
         return false;
        }
        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
         return false;
        }
    }
</script>
	<?php include("footerNew.php"); ?>
