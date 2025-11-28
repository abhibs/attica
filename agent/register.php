<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='VM-REG'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	include("../dbConnection.php");
?>
<style>
	.sp-input{
	border-radius:0px;
	margin: 0px;
	}
	.no-border-radius{
	border-radius:0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div id="accordion">
					<div class="card">
						<div class="card-header" id="headingOne">
							<h3 class="font-light m-b-xs text-success" >
								<i style="color:#990000; margin-left: 5px;" class="fa fa-pencil-square-o"></i><b> Customer Register Form</b>
							</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<form method="POST" action="submit.php" id="registrationForm">
						<table class="table table-bordered table-striped" style="clear: both">
							<tbody>
								<tr>
									<td width="35%">Branch</td>
									<td width="65%" class="no-padding">
										<input list="branchList" name="branchId" placeholder="Branch" class="form-control sp-input" required>
										<datalist id="branchList">
											<?php 
												$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1");
												while($row = mysqli_fetch_assoc($branches)){
													echo '<option value="'.$row['branchId'].'" label="'.$row['branchName'].'"></option>';
												} 
											?>
										</datalist>
									</td>
								</tr>
								<tr><td colspan="2" width="50%" style="border-left-style: hidden; border-right-style: hidden;"></td></tr>
								<tr>
									<td width="35%">Customer Name</td>
									<td width="65%" class="no-padding">
										<input type="text" class="form-control sp-input" id="customerName" name="customerName" placeholder="Customer Name" required autocomplete="off">
									</td>
								</tr>
								<tr>
									<td>Mobile Number</td>
									<td class="no-padding">
										<div class="input-group">
											<input type="number" class="form-control sp-input" id="customerMobile" name="customerMobile" placeholder="Mobile Number" required maxlength="10" pattern="[0-9]{10}"
											oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" autocomplete="off">
											<span class="input-group-btn"> 
												<button type="button" name="sendOTP" id="sendOTP" class="btn btn-primary no-border-radius">
													Send OTP
												</button> 
											</span>
										</div>
									</td>
								</tr>
								<tr>
									<td>Type</td>
									<td class="no-padding">
										<select name="customerType" id="customerType" class="form-control sp-input" aria-label="Default select example" required>
											<option selected="true" disabled="disabled" value="">Type</option>
											<option value="physical">Physical</option>
											<option value="release">Release</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Gross Weight</td>
									<td class="no-padding">
										<input type="number" class="form-control sp-input" id="customerGrossW" name="customerGrossW" placeholder="Gross Weight" required min="0" max="2000" step="0.001" autocomplete="off">
									</td>
								</tr>
								<tr>
									<td>Preferred Language</td>
									<td class="no-padding">
										<select name="language" id="language" class="form-control sp-input" required>
											<option selected="true" disabled="disabled" value="">Language</option>
											<option value="English">English</option>
											<option value="Hindi">Hindi</option>
											<option value="Kannada">Kannada</option>
											<option value="Tamil">Tamil</option>
											<option value="Telugu">Telugu</option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="table table-bordered table-striped" style="clear: both; margin-top: 40px;">
							<tbody>
								<tr>
									<td width="20%">Enter OTP</td>
									<td width="30%" class="no-padding">
										<input type="number" class="form-control sp-input" id="customerOTP" name="customerOTP" placeholder="Enter OTP" required maxlength="6" pattern="[0-9]{10}"
										oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" autocomplete="off"> 
									</td>
									<td width="40%" style="border-top-style: hidden; border-bottom-style: hidden;" id="validityMessage"></td>
									<td width="10%" class="no-padding">
										<button type="submit" disabled class="btn btn-primary no-border-radius btn-block" name="HoRegistrationSubmit">Register</button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>	
	</div>
	<?php include("footer.php"); ?>	
	<script>
		(function(){
			
			"use strict";
			
			class OTP{
				constructor(){
					
					this.form = document.getElementById("registrationForm");
					
					this.nameInput = this.form.customerName;
					this.mobileInput = this.form.customerMobile;
					this.otpInput = this.form.customerOTP;
					
					this.sendOtpBtn = this.form.sendOTP;
					this.submitBtn = this.form.HoRegistrationSubmit;
					
					this.http = new XMLHttpRequest();
					this.otpURL = "../ot.php";
					this.otpValidURL = "../otpValid.php";
					this.ten_digit_number_regex = /^\d{10}$/;
					
					this.validityMessage = document.getElementById("validityMessage");
				}
				check_mobile_number(mobile){
					return (this.ten_digit_number_regex.test(mobile));
				}
				sendOTP(){
					this.sendOtpBtn.addEventListener("click", ()=>{
						this.otpInput.value = "";
						this.validityMessage.innerHTML = "";
						this.submitBtn.disabled = true;
						
						let name = this.nameInput.value;
						let mobile = this.mobileInput.value;
						
						if(this.check_mobile_number(mobile) && name != ""){
							const object = this;
							this.http.open("POST", object.otpURL);
							this.http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							this.http.onload = function() {
								alert(`OTP is sent to your Mobile`);
							};
							this.http.onerror = function() { 
								alert(`Network Error, Resend OTP`);
							};
							this.http.send(`name=${name}&data=${mobile}`);
						}
						else{
							window.alert("Name & Contact Information is Mandatory");
						}
					});
				}
				verifyOTP(){
					this.otpInput.addEventListener("input", (e)=>{
						let otp = this.otpInput.value;
						if(otp.length == 6){
							const object = this;
							this.http.open("POST", object.otpValidURL);
							this.http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							this.http.onload = function() {
								if (object.http.readyState === object.http.DONE) {
									if (object.http.status === 200) {								
										let response = object.http.responseText;
										if (response == "OTP Validated") {
											object.validityMessage.innerHTML = '<i class="fa fa-check text-success"></i>Valid OTP';
											object.submitBtn.removeAttribute("disabled");
										}
										else{
											console.log(response);
											
											object.validityMessage.innerHTML = '<i class="fa fa-close text-danger"></i> Invalid OTP';
											object.submitBtn.disabled = true;
										}
									}
								}
							};
							this.http.onerror = function() { 
								alert(`Network Error, Retype OTP Again`);
							};
							this.http.send(`data=${otp}`);
						}
					});
				}
			}
			
			const obj = new OTP();
			obj.sendOTP();
			obj.verifyOTP();
			
		})();
	</script>