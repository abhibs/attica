<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	
	if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['encData']) && !empty($_GET['encData']) && $type == 'Branch') {
		$id = $_GET['id'];
		include("dbConnection.php");
		$custDetails = mysqli_fetch_assoc(mysqli_query($con, "SELECT e.customer,e.contact,e.image,b.branchName,e.block_counter,e.time 
		FROM everycustomer e,branch b 
		WHERE e.id='$id' AND e.branch=b.branchId"));
		if(base64_decode($_GET['encData']) == date("Y-m-d").$custDetails['time']){
			include("header.php");
			include("menu.php");
			
			//$branch = $_SESSION['branchCode'];
			$_SESSION['mobile'] = $custDetails['contact'];
			unset($_SESSION['bill']);
		?>
		<style>
			#results img{
			width:100px;
			}
			#wrapper{
	        background:#E3E3E3;
        	}
			#wrapper h3{
			text-transform:uppercase;
			font-weight:600;
			font-size: 18px;
			color:#123C69;
			}
			.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
			background-color:#fffafa;
			}
			.text-success{
			color:#123C69;
			text-transform:uppercase;
			font-weight:700;
			font-size: 12px;
			}
			.fa_Icon {
			color:#800000;
			}
			.btn-success{
			display:inline-block;
			padding:0.6em 1.4em;
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
			.branchName{
			text-transform:uppercase;
			color: #900;
			}
			#wrapper .panel-body{
			box-shadow:10px 15px 15px #999;
			border: 1px solid #edf2f9;
			border-radius:7px;
			background-color: #f5f5f5;
			padding: 20px;
			margin-top:20px;
			}
			.font-weight-bold{
			font-weight:bold;
			}
			dt{
			margin-bottom:25px;
			}
			dt{
			margin-bottom:25px;
			}
		</style>
		<div id="wrapper">
			<div class="row content">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="col-xs-8">
							<h3> &nbsp; <span class="fa_Icon fa fa-user"></span> &nbsp; NEW CUSTOMER </h3> 
						</div>
						<div class="col-xs-4 input-group" >
							<span class="input-group-addon"><span class="fa_Icon fa fa-bank"></span></span>
							<input type="text" class="form-control branchName text-center font-weight-bold" readonly value="<?php echo $custDetails['branchName']; ?>">
						</div>					
						<div class="panel-body">
							<form method="POST" action="xsubmit.php" enctype="multipart/form-data" autocomplete="off">
								<input type="hidden" name="cusId" value="<?php $custId = "ATTICA-" . rand(10000, 99999);$_SESSION['customerID'] = $custId; echo $custId; ?>">
								
								<!-- ---------------    PERSONAL DATA   ----------------- -->
								<div class="form-group col-xs-3" style="padding-right:50px">
									<label class="text-success">Customer Photo</label>
									<div id="results" style="position:absolute;"></div>
									<a onClick="take_snapshot()">
										<div id="my_camera"></div>
										<i style="position:absolute; top:40%; left:20%; font-size:15px; font-weight:900; color:#900">CLICK HERE</i>
									</a>
									<input type="text" name="image" class="image-tag" required style="opacity:0; width:0; float:left;"><br>
								</div>
								<div class="col-xs-9">
									<div class="form-group col-sm-4">
										<label class="text-success">Contact Number</label>
										<input type="text" name="mobile" id="mobile" placeholder="Contact Number" maxlength="10" class="form-control" value="<?php echo $custDetails['contact']; ?>" readonly>
									</div>
									<div class="form-group col-sm-8">
										<label class="text-success">Customer Name</label>
										<input type="text" name="name" required id="name" class="form-control" autocomplete="off" placeholder="Customer Name">
									</div>
									<div class="form-group col-sm-2">
										<label class="text-success">DOB : Day</label>
										<select class="form-control" name="day" id="day" required>
											<option selected="true" disabled="disabled" value="">DD</option>
											<?php for ($i = 1; $i <= 31; $i++) { echo "<option value=" . $i . ">" . $i . "</option>"; } ?>
										</select>
									</div>
									<div class="form-group col-sm-2">
										<label class="text-success">Month</label>
										<select class="form-control" name="month" id="month" required>
											<option selected="true" disabled="disabled" value="">MM</option>
											<option value="1">01 - Jan</option>
											<option value="2">02- Feb</option>
											<option value="3">03 - Mar</option>
											<option value="4">04 - Apr</option>
											<option value="5">05 - May</option>
											<option value="6">06 - June</option>
											<option value="7">07 - July</option>
											<option value="8">08 - Aug</option>
											<option value="9">09 - Sept</option>
											<option value="10">10 - Oct</option>
											<option value="11">11 - Nov</option>
											<option value="12">12 - Dec</option>
										</select>
									</div>
									<div class="form-group col-sm-2">
										<label class="text-success">Year</label>
										<select class="form-control" name="year" id="year" required>
											<option selected="true" disabled="disabled" value="">YYYY</option>
											<?php for ($i = 2006; $i >= 1950; $i--) { echo "<option value=" . $i . ">" . $i . "</option>"; } ?>
										</select>
									</div>
									<div class="form-group col-sm-6" style="line-height:25px" align="center">
										<label class="text-success">Customer Gender</label><br>
										<b style="color:#990000">
											<input name="gender" value="Male" class="i-checks" type="radio" required> MALE
											<input name="gender" value="Female" class="i-checks" type="radio"> FEMALE
											<input name="gender" value="Others" class="i-checks" type="radio"> OTHERS
										</b>
									</div>
									<label class="col-sm-12 control-label"></label>
									<div class="form-group col-sm-4">
										<label class="text-success">Additional Contacts</label>
										<select class="form-control" name="relation" required id="addContact">
											<option selected="true" disabled="disabled" value="">ADDITIONAL CONTACT</option>
											<option value="Father/Mother">Father/Mother</option>
											<option value="Husband/Wife">Husband/Wife </option>
											<option value="Others">Others</option>
										</select>
									</div>
									<div class="form-group col-sm-4">
										<label class="text-success">Contact</label>
										<input type="number" name="rcontact" required id="rContact" placeholder="Contact" class="form-control" maxlength="10" pattern="[0-9]{10}" oninput="javascript											: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" autocomplete="off">
									</div>
									<!--<div class="col-sm-2" style="padding-top:22px">
										<a onClick="generateOTP()" class="btn btn-success btn-block"><i class="fa_icon fa fa-paper-plane"></i> Send OTP</a>
										</div>
										<div class="col-sm-2" style="padding-top:22px">
										<input type="text" placeholder="Enter OTP" class="form-control" maxlength="6" required name="otp" id="xotp" autocomplete="off">
									</div>-->
								</div>
								<!-- ---------------    END OF PERSONAL DATA   ----------------- -->
								
								<!-- ---------------    CURRENT ADDRESS   ----------------- -->
							<!--	<label class="col-sm-12 control-label">
									<h3 class="text-success"><hr><i style="color:#900" class="fa fa-map-marker"></i> Current Address</h3>
								</label>
								<div class="form-group col-sm-3">
									<label class="text-success">Address Line</label>
									<input type="text" name="caline" required class="form-control" maxlength="100"  autocomplete="off" placeholder="Address">
								</div>
								<!--
								<div class="form-group col-sm-3">
									<label class="text-success">Area / Locality</label>
									<input type="text" name="clocality" required class="form-control" autocomplete="off" placeholder="Area">
								</div>
								<div class="form-group col-sm-3">
									<label class="text-success">Landmark</label>
									<input type="text" name="cland" required class="form-control" autocomplete="off" placeholder="Landmark">
								</div>
								-->
							<!--	<div class="form-group col-sm-3">
									<label class="text-success">State</label>
									<select name="cstate" id="state" class="form-control"><option>Select State</option></select>
								</div>
								<div class="form-group col-sm-3">
									<label class="text-success">City</label>
									<select name="ccity" id="city" class="form-control"></select>
								</div>
								<div class="form-group col-sm-3">  
									<label class="text-success">Pincode</label>
									<input type="number" name="cpin" required class="form-control" autocomplete="off" placeholder="Pincode" maxlength="6" pattern="[0-9]{6}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" autocomplete="off">

								</div>
								<!-- ---------------   END OF CURRENT ADDRESS   ----------------- -->
								
								<!-- ---------------    DOCUMENTS   ----------------- -->
								<label class="col-sm-12 control-label"><hr></label>
								<div class="col-sm-3">
									<label class="text-success">ID Proof</label>
									<select class="form-control" name="idProof" id="idProof" required>
										<option selected="true" disabled="disabled" value="">ID PROOF</option>
										<option value="Voter Id">Voter Id</option>
										<option value="Aadhar Card">Aadhar Card</option>
									
										<option value="Pan Card">Pan Card</option>
										
										<option value="Driving License">Driving License</option>
										<option value="Others">Others</option>
									</select>
									<input type="text" class="form-control" style="padding:5px 10px;background:#e6e6e6;" name="idProofNum" id="idProofNum" pattern="^[a-zA-Z0-9]+$" minlength="9" maxlength="20" placeholder=" * Proof Number" required autocomplete="off">

									<span class="font-weight-bold text-danger">Please avoid space or any special characters like (+!@#$%^&*{}?/-)</span>
								</div>
								<div class="col-sm-3">
									<label class="text-success">Address Proof</label>
									<select class="form-control" name="addProof" style="padding:0px 2px" id="addProof" required>
										<option selected="true" disabled="disabled" value="">ADDRESS PROOF</option>
										<option value="Voter Id">Voter Id</option>
										<option value="Aadhar Card">Aadhar Card</option>
										<option value="Driving License">Driving License</option>
										<option value="Others">Others</option>
									</select>
									<input type="text" class="form-control" style="padding:5px 10px;background:#e6e6e6;" name="addProofNum" id="addProofNum" pattern="^[a-zA-Z0-9]+$" minlength="9" maxlength="20" placeholder=" * Proof Number" required autocomplete="off">
									<span class="font-weight-bold text-danger">Please avoid space or any special characters like (+!@#$%^&*{}?/-)</span>
								</div>
								<div class="col-sm-3">
									<label class="text-success">Type Of Transaction</label>
									<select class="form-control m-b" name="typeGold" id="typeGold" required>
										<option selected="true" disabled="disabled" value="">TYPE</option>
										<option value="physical">Physical </option>
										<option value="release">Release </option>
									</select>
								</div>
								<div class="col-sm-2" align="right" style="padding-top:22px">
									<input type="hidden" name="block_counter" id="block_counter" value="<?php echo $custDetails['block_counter']; ?>">
									<button class="btn btn-success" name="submitCustomer" id="submitCustomer1" type="submit">
										<span style="color:#ffcf40" class="fa fa-save"></span> Submit
									</button>
								</div>
								<!-- ---------------    END OF DOCUMENTS   ----------------- -->
							</form>
						</div>
					</div>
				</div>
			</div>
			<div style="clear:both"></div>
			<?php include("footer.php"); ?>
			<script src="scripts/webcam.min.js"></script>
			<script src="scripts/states.js"></script>
			<script language="JavaScript">
				
				// WEBCAM RELATED 
				Webcam.set({
					width: 210,
					height: 160,
					image_format: 'jpeg',
					jpeg_quality: 100
				});
				Webcam.attach('#my_camera');
				function take_snapshot() {
					Webcam.snap(function(data_uri) {
						$(".image-tag").val(data_uri);
						document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
					});
				}
				
				// CUSTOMER OTP AUTHENTICATION
				/* 				function generateOTP() {
					var data = $('#mobile').val();
					var name = $('#name').val();
					console.log(data);
					console.log(name);
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
					});
					}							
					$(document).ready(function () {
					$('#submitCustomer').attr("disabled", false);
					$("#xotp").change(function () {
					var data = $('#xotp').val();
					var req = $.ajax({
					url: "otpValid.php",
					type: "POST",
					data: {
					data
					},
					});
					req.done(function (msg) {
					$("#xotp").val(msg);
					if (msg == "OTP Validated") {
					$('#xotp').attr('readonly', 'true'),
					$('#submitCustomer').attr("disabled", false);
					}
					else if (msg == "Invalid OTP") {
					alert(msg);
					}
					});
					});
				}); */
				
				
				function check_number(proofNo) {
					if (proofNo.match(/^(\d)\1+$/g)) {
						return 1;
						} else {
						return 0;
					}
				}
				var customerType = "New";
				$('#idProofNum,#addProofNum').on('change', function() {
					var proofNo = $(this).val();
					res = check_number(proofNo);
					if (res == '1') {
						alert("PLEASE ENTER PROPER NUMBER");
						}else {
						var contactNo = $("#mobile").val();
						var block_counter = $("#block_counter").val();
						if (block_counter < 2) {
				// 			$.ajax({
				// 				url: "xTransactionAjax.php",
				// 				type: "post",
				// 				data: {
				// 					customerType: customerType,
				// 					proofNumber: this.value,
				// 					contactNo: contactNo
				// 				},
				// 				success: function(response) {
				// 					if (response == 'available') {
				// 						alert("THE CUSTOMER HAS BEEN BLOCKED FROM BILLING PLEASE CONTACT APPROVAL TEAM");
				// 						window.location.href = "xeveryCustomer.php";
				// 					}
				// 				}
				// 			});
						}
					}
				});
			</script>
			<?php
			}
			else{
				header("Location: xeveryCustomer.php");
			}
		}
		else{
			include("logout.php");
		}
	?>
