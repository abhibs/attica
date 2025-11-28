<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	
	if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['encData']) && !empty($_GET['encData']) && $type == 'Branch') {
		$id = $_GET['id'];
		include("dbConnection.php");
		$custDetails = mysqli_fetch_assoc(mysqli_query($con,"SELECT c.customerId,c.name,c.gender,c.dob,c.mobile,e.image,e.block_counter,caline,ccity,cstate,cpin,cland,clocality,e.time
		FROM customer c,everycustomer e 
		WHERE e.Id='$id' AND e.contact=c.mobile LIMIT 1"));
		
		if(base64_decode($_GET['encData']) == date("Y-m-d").$custDetails['time']){
			include("header.php");
			include("menu.php");
			
			$branch = $_SESSION['branchCode'];
			$branchNameQuery = mysqli_fetch_array(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$_SESSION[branchCode]'"));
			$_SESSION['customerID'] = $custDetails['customerId'];
			$_SESSION['mobile'] = $custDetails['mobile'];
			unset($_SESSION['bill']);
		?>
		<style>
			#results img{
			width:100px;
			}
			#wrapper h3{
			text-transform:uppercase;
			font-weight:600;
			font-size: 18px;
			color:#123C69;
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
			#wrapper .panel-body{
			box-shadow: 10px 15px 15px #999;
			border: 1px solid #edf2f9;
			background-color: #f5f5f5;
			border-radius:3px;	
			padding: 20px;			
			}
			.font-weight-bold{
			font-weight:bold;
			}
			.a-extra{
			color: #123C69;
			font-weight: 600;
			background-color: #f5f5f5;
			box-shadow: 10px 15px 15px #999;
			}
		</style>
		<div id="wrapper">
			<div class="row content">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<div class="col-xs-8">
								<h3><span class="fa_Icon fa fa-users"></span> EXISTING CUSTOMER </h3>
							</div>
							<div class="col-xs-4 input-group text-right">								
								<a class="btn btn-default a-extra"><span class="fa_Icon fa fa-bank"></span> | <?php echo $branchNameQuery['branchName']; ?></a>
							</div>
						</div>
						<div class="panel-body">
							<form method="POST" action="xsubmit.php" enctype="multipart/form-data" autocomplete="off">
								<input type="hidden" name="cusId" value="<?php echo $custDetails['customerId']; ?>">
								
								<!-- ---------------    PERSONAL DATA   ----------------- -->
								<div class="form-group col-xs-3" style="padding-right:50px">
									<label class="text-success">Customer Photo</label>
									<div id="results" style="position:absolute;"></div>
									<a onClick="take_snapshot()">
										<div id="my_camera"></div>
										<i style="position:absolute; top:40%; left:20%; font-size:15px; font-weight:900; color:#900">CLICK HERE</i>
									</a>
									<input type="text" name="image" class="image-tag"  required style="opacity:0; width:0; float:left;"><br>
								</div>
								<div class="col-xs-9">
									<div class="form-group col-sm-12">
										<label class="text-success">Customer Name</label>
										<input type="text" name="name" readonly class="form-control" value="<?php echo $custDetails['name']; ?>">
									</div> 
									<div class="form-group col-sm-4">
										<label class="text-success">Contact Number</label>
										<input type="number" name="mobile" style="padding:0px 5px" id="mobile" readonly placeholder="Contact Number" maxlength="10" class="form-control" value="<?php echo $custDetails['mobile']; ?>">
									</div>
									<div class="form-group col-sm-4">
										<label class="text-success">Date of Birth</label>
										<input type="text" name="dob" id="dob" readonly class="form-control" value="<?php echo $custDetails['dob'];?>">
									</div>
									<div class="form-group col-sm-4">
										<label class="text-success">Gender</label>
										<input type="text" name="gender" id="gender" readonly class="form-control" value="<?php echo $custDetails['gender']; ?>">
									</div>
									<div class="form-group col-sm-4">
										<label class="text-success">Additional Contacts<span style="color:red;"> *</span></label>
										<select class="form-control" name="relation" required id="addContact" >
											<option selected="true" disabled="disabled" value="">ADDITIONAL CONTACT</option>
											<option value="Father/Mother">Father/Mother</option>
											<option value="Husband/Wife">Husband/Wife </option>
											<option value="Others">Others</option>
						
										</select>
									</div>
									<div class="form-group col-sm-4">
										<label class="text-success">Contact<span style="color:red;"> *</span></label>
										<input type="number" name="rcontact" required id="rcontact" placeholder="Contact" class="form-control" pattern="[0-9]{10}" maxlength="10" autocomplete="off">
									</div>									
								</div>
								<!-- ---------------   END OF PERSONAL DATA   ----------------- -->
								
								<!-- ---------------    CURRENT ADDRESS   ----------------- -->
						<!-- <label class="col-sm-12 control-label">
									<h3 class="text-success"><i style="color:#900" class="fa fa-map-marker"></i> Current Address</h3>
								</label>
								<div class="form-group col-sm-3">
									<label class="text-success">Address Line</label>
									<input type="text" name="caline" required class="form-control" maxlength="100" autocomplete="off" placeholder="Address">
								</div>
								<!-- <div class="form-group col-sm-4">
									<label class="text-success">Area / Locality</label>
									<input type="text" name="clocality" required class="form-control" autocomplete="off" placeholder="Area">
								</div>
								<div class="form-group col-sm-4">
									<label class="text-success">Landmark</label>
									<input type="text" name="cland" required class="form-control" autocomplete="off" placeholder="Near">
								</div>-->
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
									<input type="number" name="cpin" required class="form-control" maxlength="6" autocomplete="off" placeholder="Pincode">
								</div> 
								<!-- ---------------   END OF CURRENT ADDRESS   ----------------- -->
								
								<label class="col-sm-12 control-label"><hr></label>
								
								<!-- ---------------    DOCUMENTS   ----------------- -->
								<div class="col-sm-3">
									<label class="text-success">ID Proof <span style="color:red;"> *</span></label>
									<select class="form-control" name="idProof" id="idProof" required>
										<option selected="true" disabled="disabled" value="">ID PROOF</option>
										<option value="Voter Id">Voter Id</option>
										<option value="Aadhar Card">Aadhar Card</option>
										
										<option value="Pan Card">Pan Card</option>
									
										<option value="Driving License">Driving License</option>
										<option value="Others">Others</option>
									</select>
									<input type="text" class="form-control" style="padding:5px 10px;background:#e6e6e6;" name="idProofNum" id="idProofNum" pattern="^[a-zA-Z0-9]+$" minlength="9" placeholder=" * Proof Number" required autocomplete="off">
									<span class="font-weight-bold text-danger">Please avoid space or any special characters like (+!@#$%^&*{}?/-)</span>
								</div>
								<div class="col-sm-3">
									<label class="text-success">Address Proof <span style="color:red;"> *</span></label>
									<select class="form-control" name="addProof"  style="padding:0px 2px" id="addProof" required>
										<option selected="true" disabled="disabled" value="">ADDRESS PROOF</option>
										<option value="Voter Id">Voter Id</option>
										<option value="Aadhar Card">Aadhar Card</option>
									
							
										<option value="Driving License">Driving License</option>
										
										<option value="Others">Others</option>
									</select>
									<input type="text" class="form-control" style="padding:5px 10px;background:#e6e6e6;" name="addProofNum" id="addProofNum" pattern="^[a-zA-Z0-9]+$" minlength="9" placeholder=" * Proof Number" required autocomplete="off">
									<span class="font-weight-bold text-danger">Please avoid space or any special characters like (+!@#$%^&*{}?/-)</span>
								</div>
								<div class="col-sm-3">
									<label class="text-success">Type Of Transaction <span style="color:red;"> *</span></label>
									<select required class="form-control m-b" name="typeGold" id="typeGold" required>
										<option selected="true" disabled="disabled" value="">TYPE</option> 
										<option value="physical">Physical </option>
										<option value="release">Release </option>
									</select>
								</div>
								<div class="col-sm-2" align="right" style="padding-top:22px">
									<input type="hidden" name="block_counter" id="block_counter" value="<?php echo $custDetails['block_counter'];?>">
									<button class="btn btn-success" name="updateCustomer" id="updateCustomer"  type="submit">
										<span class="fa_icon fa fa-save" style='color:#ffa500'></span> SUBMIT
									</button>
								</div>
								<!-- ---------------   END OF DOCUMENTS   ----------------- -->
								
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
				Webcam.set
				({
					width: 210,
					height: 180,
					image_format: 'jpeg',
					jpeg_quality: 100
				});
				Webcam.attach( '#my_camera' );
				function take_snapshot(){
					Webcam.snap(function(data_uri){
						$(".image-tag").val(data_uri);
						document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
					});
				}
				
				function check_number(proofNo){
					if(proofNo.match(/^(\d)\1+$/g)){
						return 1;
						}else{
						return 0;
					}
				}
				var customerType = "Existing";
				$('#idProofNum,#addProofNum').on('change',function() {
					var proofNo=$(this).val(); 
					res = check_number(proofNo);
					if (res == '1') {
						alert("PLEASE ENTER PROPER NUMBER");
						}/*else {
						var contactNo=$("#mobile").val();
						var block_counter=$("#block_counter").val();
						if(block_counter<2){
						$.ajax({
						url: "xTransactionAjax.php",
						type: "post",
						data: {customerType:customerType,proofNumber: this.value,contactNo:contactNo},
						success: function(response){
						if(response=='available'){
						alert("THE CUSTOMER HAS BEEN BLOCKED FROM BILLING PLEASE CONTACT APPROVAL TEAM");
						window.location.href = "xeveryCustomer.php";
						}
						}
						});
						}
					}*/
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
