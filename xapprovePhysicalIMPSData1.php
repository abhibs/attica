<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type=='Accounts IMPS'){
		include("header.php");
		include("menuimpsAcc.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	$id="";	
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$billData = mysqli_fetch_assoc(mysqli_query($con,"SELECT t.customerId,t.billId,t.name,t.phone,t.releases,t.grossW,t.netW,t.grossA,t.netA,t.amountPaid,t.date,t.branchId,t.type,t.status,t.cashA,t.impsA,t.releaseID,t.relDate,b.branchName, t.time, t.approvetime
		FROM trans t,branch b 
		WHERE t.id='$id' AND t.branchId=b.branchId"));
		$bmData = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,contact FROM employee where empId=(select employeeId from users where username='$billData[branchId]')"));
		$bankDetails = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM bankdetails WHERE customerId='$billData[customerId]' AND billId='$billData[billId]' AND date='$date'"));
	}
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
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#ffffff;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	.btn-danger{
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
	background-color:#e74c3c;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
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
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}
	.a-extra{
	color: #123C69;
	font-weight: 600;
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
					<input type="hidden" id='session_branchID' value="<?php echo $billData['branchId']; ?>" >
					<div class="pull-right">
						<a class="btn btn-default a-extra"><span style="color:#990000" class="fa fa-bank"></span> | <?php echo $billData['branchName']; ?></a>
						<a class="btn btn-default a-extra"><span style="color:#990000" class="fa fa-user"></span> | <?php echo $bmData['name']; ?></a>
						<a class="btn btn-default a-extra"><span style="color:#990000" class="fa fa-phone"></span> | <?php echo $bmData['contact']; ?></a>
						<a class="btn btn-default a-extra"><span style="color:#990000" class="fa fa-money"></span> | <i id="available"></i></a>
						<?php if($billData['status'] == 'Verified'){ ?>
							<a class="btn btn-default a-extra">&#9203; | <span class="timerDisplay" data-starttime="<?php echo $billData['approvetime']; ?>"></span></a>
						<?php } ?>						
					</div>
					<h3><i style="color:#990000" class="fa fa-rupee"></i> TRANSACTION APPROVAL (IMPS)</h3>
				</div>
				<div class="col-lg-6">
					<div class="panel-body">
						<h5 class="text-center" style="color:#990000">CUSTOMER DETAILS<hr></h5>
						<div class="form-group col-lg-8">
							<label class="text-success">Customer Name</label>
							<input type="text" name="name" class="form-control" readonly value="<?php echo $billData['name']; ?>">
						</div>
						<div class="form-group col-lg-4">
							<label class="text-success">Contact Number</label>
							<input type="text" name="mobile" readonly class="form-control" value="<?php echo $billData['phone']; ?>">
						</div>
						<div class="form-group col-lg-6">
							<label class="text-success">Gross W</label>
							<input type="text" name="grossW" readonly class="form-control" value="<?php echo $billData['grossW']; ?>">
						</div>
						<div class="form-group col-lg-6">
							<label class="text-success">Net W</label>
							<input type="text" name="netW" readonly class="form-control" value="<?php echo $billData['netW']; ?>">
						</div>
						<div class="form-group col-lg-6">
							<label class="text-success">Gross Amount</label>
							<input type="text" name="grossA" readonly class="form-control" value="<?php echo $billData['grossA']; ?>">
						</div>
						<div class="form-group col-lg-6">
							<label class="text-success">Net Amount</label>
							<input type="text" name="netA" readonly class="form-control" value="<?php echo $billData['netA']; ?>" id="netA">
						</div>
						<?php if($billData['type'] == 'Release Gold'){ ?>
							<div class="form-group col-lg-12">
								<label class="text-success">Release Amount</label>
								<input type="text" name="netA" readonly class="form-control" value="<?php echo $billData['releases']; ?>" id="relA">
							</div>
						<?php } ?>
						<div class="form-group col-lg-6">
							<label class="text-success">IMPS Amount</label>
							<input type="text" name="impsA" class="form-control" value="<?php echo $billData['impsA']; ?>" style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);" readonly id="impsA">
						</div>
						<div class="form-group col-lg-6">
							<label class="text-success">Cash Amount</label>
							<input type="text" name="cashA" readonly class="form-control" value="<?php echo $billData['cashA']; ?>" id="cashA">
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="panel-body">
						<h5 class="text-center" style="color:#990000">BANK DETAILS<hr></h5>
						<div class="form-horizontal" style="padding-right: 30px">
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">Bank (Branch)</label>
								<div class="col-sm-9">
									<input type="text" name="bankBranch" readonly class="form-control" value="<?php echo $bankDetails['bank']."  ( ".$bankDetails['branch']." )";?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">Account Holder</label>
								<div class="col-sm-9">
									<input type="text" name="accHolderName" readonly class="form-control" value="<?php echo $bankDetails['accountHolder']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">Relationship</label>
								<div class="col-sm-9">
									<input type="text" name="relationship" readonly class="form-control" value="<?php echo $bankDetails['relationship']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">Account No</label>
								<div class="col-sm-9">
									<input type="text" name="accNo" class="form-control" readonly value="<?php echo $bankDetails['account']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">IFSC Number</label>
								<div class="col-sm-9">
									<input type="text" name="ifsc" readonly class="form-control" value="<?php echo $bankDetails['ifsc']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 text-success control-label">Document</label>
								<div class="col-sm-9">
									<?php if($bankDetails['Bproof'] != ''){ ?>
										<h4>
											<a class='btn btn-success btn-md'target='_blank' href="<?php echo 'BankDocuments/'.$bankDetails['Bproof']; ?>"><span style="color:#ffcf40" class="fa fa-file-text-o"></span> Bank Proof </a>
										</h4>
									<?php }else echo "N/A"; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if($billData['status'] == 'Verified'){ ?>
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-body" style="margin-top:15px">
							<form method="POST" class="form-horizontal" action="xapproveRejectB.php" onkeydown="return event.key != 'Enter';">
								<input type="hidden" name="id" value="<?php echo $id; ?>">
								<input type="hidden" name="custPhone" value="<?php echo $billData['phone']; ?>">
								<input type="hidden" name="goldType" value="<?php echo $billData['type']; ?>">
								<input type="hidden" name="amountPaid" value="<?php echo $billData['amountPaid']; ?>">
								<?php if($billData['type'] == 'Release Gold'){ ?>
									<input type="hidden" value="<?php echo $billData['releaseID']; ?>" name="relID">
									<input type="hidden" value="<?php echo $billData['relDate']; ?>" name="relDate">
								<?php } ?>
								<div class="col-sm-8">
									<button class="btn btn-success" name="ConvertToCash" type="submit" style="float:left; background-color:#34495E; border:none;">
										<span style="color:#ffcf40" class="fa fa-money"></span> Convert To Cash
									</button><br><br>
									<span class="help-block m-b-none">Use only when the cash & IMPS rates are same</span>
								</div>
								<div class="col-sm-1">
									<label style="padding-top:6px;">
										<input type="checkbox" id="done"><span class="text-success"> DONE</span>
									</label>
								</div>
								<div class="col-sm-3" style="float:right;">
									<button class="btn btn-success" name="submitApproveIMPS" type="submit" id="approve">
										<span style="color:#ffcf40" class="fa fa-check"></span> APPROVE
									</button>
									<button class="btn btn-danger" name="submitRejectIMPS" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> REJECT
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
	<script>
		$(document).ready(function(){
			var branchId  = $("#session_branchID").val();
			var req = $.ajax({
				url:"xbalance.php",
				type:"POST",
				data:{branchId:branchId},
				dataType:'JSON'
			});
			req.done(function(e){
				$('#available').text(e.balance);
			});
		});
	</script>
	<script>
		$(document).ready(function(){
			$('#approve').attr("disabled", true);
			$('#done').change(function() {
				if(this.checked) {
					$('#approve').attr("disabled", false); 
				}
				else{
					$('#approve').attr("disabled", true);
				}
			});
		});
	</script>
	<?php include("footer.php");?>
	<?php if($billData['status'] == 'Verified'){ ?>
		<script>
			$(document).ready(function(){
				const timerDisplay = document.querySelector(".timerDisplay");
				
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
					const starttime = timerDisplay.dataset.starttime;
					const start_time_arr = starttime.split(":");
					
					const tdTime = new Date(current_date_time.getFullYear(), +current_date_time.getMonth(), current_date_time.getDate(), start_time_arr[0], start_time_arr[1], start_time_arr[2]);
					const diff = current_date_time - tdTime;
					
					const { hour, min, sec } = convertMS(diff)		
					
					let timeText = (hour % 12 > 0 ) ? hour % 12 +":" : "";
					timeText += min +":"+ sec;
					
					timerDisplay.textContent = timeText;
					
					const parentTr = timerDisplay.parentElement;
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
				}		
				const timer = setInterval(showCountDown, 1000);
				showCountDown();
			});
		</script>
	<?php } ?>
