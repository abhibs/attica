<?php
	session_start();
	include("dbConnection.php");
	include('libs/phpqrcode/qrlib.php');
	include("header.php");
	$tempDir = 'temp/';
	$date = date('Y-m-d');

	//$branch = $_GET['branch'];
	//$branch = $_SESSION['attica_branch'];
	
	$branch = base64_decode($_GET['branch']);
	$date = date('Y-m-d');
	
    $response="";
	
	if (isset($_GET['branch'])) {
		$response="";		
	}else{		
		$response="<p style='color:red;'> PLEASE SCAN BRANCH QR CODE FOR REGISTRATION </p>";
	}
	

	
	if (isset($_POST['VMsubmitNCHidden'])) {
	
		$cusname = strtoupper(trim($_POST['cusname']));
		$mob = $_POST['cusmob'];
		$type = $_POST['type'];
		$branchID = $_POST['branchID'];
		//$idnumber = $_POST['accomBy'];
		$idnumber = "";
		
		$extra = [];
		$extra['GrossW'] =  $_POST['grossW'];
		//$extra['NetW'] =  $_POST['netW'];
		$extra['NetW'] =  (isset($_POST['netW'])) ? $_POST['netW'] : 0;
		$extra['Rel-Amount'] =  (isset($_POST['relAmount'])) ? $_POST['relAmount'] : 0;
		$extra = json_encode($extra);
		
		$date = date('Y-m-d');
		$time = date("h:i:s");
		
		$row_count=mysqli_num_rows(mysqli_query($con, "SELECT contact FROM `everycustomer` WHERE date=CURRENT_DATE and contact=$mob"));
		if($row_count<=3){
		
			/* ======================== Check whether the customer has billed in less than 20days ======================= */
			$yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
			$start_date = date('Y-m-d', strtotime($yesterday . ' -20 day'));
			$billCount = mysqli_num_rows(mysqli_query($con, "SELECT phone FROM trans WHERE phone='$mob' AND date BETWEEN '$start_date' AND '$yesterday' and status='Approved'"));
			if ($billCount > 0) {
				$status = "Blocked";  // block the customer from billing 
				$remark = "This customer has billed recently in less than 20days";
			}
			else {
				$custQuery = mysqli_query($con, "SELECT mobile FROM customer WHERE rcontact='$mob'");
				$cRow = mysqli_fetch_assoc($custQuery);
				if (isset($cRow["mobile"])) {
					$additionalContactNo = $cRow["mobile"];
					$cbillCount = mysqli_num_rows(mysqli_query($con, "SELECT phone FROM trans WHERE phone='$additionalContactNo' AND date BETWEEN '$start_date' AND '$yesterday' and status='Approved'"));
					if ($cbillCount > 0) {
						$status = "Blocked";  // block the customer from billing 
						$remark = "This customer has billed recently with different number ($additionalContactNo) in less than 20days";
					} 
					else {
						$status = 0;
						$remark = "";
					}
				} 
				else {
					$status = 0;
					$remark = "";
				}
			}		
			
			$fName1 = '';
			$inscon = "INSERT INTO everycustomer(customer,contact,type,idnumber,branch,image,quotation,date,time,status,status_remark,remark,block_counter,extra,reg_type) VALUES ('$cusname','$mob','$type','$idnumber','$branchID','','$fName1','$date','$time','$status','','$remark','0','$extra','QR')";
			if (mysqli_query($con, $inscon)) {

				$response="<p style='color:green;text-align:center;'> THANK YOU <br> REGISTERED SUCCESSFULLY </p>";
				echo "<script>setTimeout(\"window.location.replace('http://atticagoldcompany.com');\",5000);</script>";
			} 
			else {
				
				$response="<p style='color:red;'>SOMETHING WENT WRONG,PLEASE SCAN QR CODE AGAIN</p>";
			}
		}else{
			
			$response="<p style='color:red;'>YOU HAVE BEEN ALREADY REGISTERED. PLEASE CONTACT/CALL BRANCH MANAGER</p>";
			echo "<script>setTimeout(\"window.location.replace('http://atticagoldcompany.com');\",5000);</script>";
		}
	}
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
	
	body,.hbuilt{
		background:#E3E3E3;
	}
	

</style>



<div id="wrapper">
	<div class="row content">
		<div class="col-lg-4">
		</div>
		<div class="col-lg-4">
			
			<div class="hpanel">
			
				<?php if($response==""){ ?>
					
				<div class="panel-heading hbuilt" style="margin-bottom:10px;background:#E3E3E3">
					<img src="images/favicon.png" class="center">	
					<h3 class="text-success text-center">CUSTOMER REGISTRATION</b></span></h3>
				</div>
				
				<div class="panel-body">

					<form name="add-new-customer" method="POST" class="form-horizontal" action="customer_registration.php" onsubmit="submit_details.disabled = true; return true;">
						<input type="hidden" name="VMsubmitNCHidden" value="true">
						<input type="hidden" name="branchID" value="<?php echo $branch;?>">
						
						<div class="form-group">
							<label class="col-sm-2 control-label text-success">BRANCH</label>
								<?php if($_GET['branch']==""){ ?>

								<div class="col-sm-10" style="margin-top: 5px;">
									<b style="color:red;">BRANCH UNAVAILABLE !! PLEASE SCAN QR CODE AGAIN</b>
								</div>
								
								<?php
								}else{
									
								$branchSQL = mysqli_query($con,"SELECT branchName FROM branch WHERE branchId= '$branch'");
								$row = mysqli_fetch_assoc($branchSQL);
								$branchName = $row["branchName"];
								?>
								<div class="col-sm-10">
									<input type="text" name="branchName" value="<?php echo $branchName;?>" readonly class="form-control" autocomplete="off">
								</div>						
								<?php
								}?>
							

						</div>
						

						<div class="form-group">
							<label class="col-sm-2 control-label text-success">NAME</label>
							<div class="col-sm-10">
								<input type="text" id="name" name="cusname" placeholder=" Enter your name " required class="form-control" autocomplete="off">
							</div>
						</div>
						
						<div class="form-group" style="margin-top:30px">
							<label class="col-sm-2 control-label text-success">MOBILE</label>
							<div class="col-sm-10">
								<input type="text" name="cusmob" id="mobile" placeholder=" Enter your mobile number " required maxlength="10" class="form-control" autocomplete="off">
							</div>
						</div>
						
						<div class="form-group" style="margin-top:30px">
							<!--<div class="col-sm-6">
								<select class="form-control m-b" name="accomBy" required>
									<option selected="true" disabled="disabled" value="">ACCOMPANIED BY</option>
									<option value="Spouse">Spouse</option>
									<option value="Brother">Brother</option>
									<option value="Sister">Sister</option>
									<option value="Mother">Mother</option>
									<option value="Father">Father</option>
									<option value="Son">Son</option>
									<option value="Daughter">Daughter</option>
									<option value="Friend">Friend</option>
									<option value="No One">No One</option>
								</select>
							</div>-->
							<div class="col-sm-6">
								<select class="form-control m-b" name="type" required>
									<option selected="true" disabled="disabled" value="">TYPE</option>
									<option value="Physical">Physical</option>
									<option value="Release">Release</option>
									<option value="Enquiry">Enquiry</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-4">
								<input type="text" name="grossW" placeholder="Gross Weight" required class="form-control" autocomplete="off">
							</div>
							<div class="col-sm-4">
								<input type="text" name="netW" placeholder="Net Weight" class="form-control" autocomplete="off">
							</div>
							<div class="col-sm-4">
								<input type="text" name="relAmount" placeholder="Release Amt" class="form-control" autocomplete="off">
							</div>
						</div>
						<hr>
						<div class="form-group">
							<div class="col-sm-4">
								<a onClick="generateOTP()" id="btn" class="btn btn-success btn-block"><i class="fa_Icon fa fa-paper-plane"></i> OTP</a>
							</div>
							<div class="col-sm-4">
								<input type="text" placeholder="OTP" class="form-control" maxlength="6" required name="otp" id="xotp">
							</div>
							<div class="col-sm-4">
								<button class="btn btn-success btn-block" id="qr_register_customer" name="submit_details" type="submit"><span class="fa_Icon fa fa-save"></span> SUBMIT</button>
							</div>
						</div>
					</form>
				</div>
				
				<?php }else{ ?>
				
				<div class="panel-heading hbuilt" style="margin-bottom:10px;margin-top:20px;">
					<img src="images/favicon.png" class="center">	
					<h3 class="text-success text-center"><?php echo $response;?></b></span></h3>
				</div>
				
				<?php } ?>
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
