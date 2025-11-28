<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];	
	include("dbConnection.php");
	
	if(isset($_GET['rid'])){
		$relData = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,phone,amount,relCash,relIMPS,status FROM releasedata WHERE rid='$_GET[rid]'"));
		if($relData['status'] != "Terminated"){
			if ($type == 'Branch') {
				include("header.php");
				include("menu.php");
			} 
			else {
				include("logout.php");
			}
		?>
		<style>
			#wrapper{
			background: #f5f5f5;
			}
			#wrapper h2{
			color:#123C69;
			text-transform:uppercase;
			font-weight:600;
			font-size: 20px;
			}
			.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
			background-color:#fffafa;
			}
			.text-success{
			color:#123C69;
			text-transform:uppercase;
			font-weight:900;
			font-size: 12px;
			}
			.btn-primary{
			background-color:#123C69;
			}
			.btn-info{
			background-color:#123C69;
			border-color:#123C69;
			font-size:12px;
			}
			.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
			background-color:#123C69;
			border-color:#123C69;
			}
			.fa_Icon{
			color:#ffa500;
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
			#wrapper .panel-body{
			border: 5px solid #fff;
			border-radius: 10px;
			padding: 20px;
			box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
			background-color: #f5f5f5;
			}
		</style>
		<div id="wrapper">
			<div class="row content">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<h3 class="text-success"><b><i style="color:#900" class="fa fa-edit"></i> Add After Release Info</b></h3>
						</div>
						<form method="POST" action="xaddAfterRelease.php" enctype="multipart/form-data">
							<input type="hidden" name="branchId" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>">
							<input type="hidden" name="rid" value="<?php echo $_GET['rid']; ?>">
							<input type="hidden" name="custPhone" value="<?php echo $relData['phone']; ?>">
							<input type="hidden" name="avail" id="balance">
							
							<div class="col-lg-6">
								<div class="panel-body" style="height:400px">
									<div class="panel-heading">
										<h4 class="text-success" style="font-size:15px;color:#990000"><b> After Release Info</b></h4>
									</div>
									<hr style="margin:0px">
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Customer</label>
										<div class="col-sm-9">
											<input type="text" name="custName" class="form-control" value="<?php echo $relData['name']; ?>" readonly>
										</div>
									</div>
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Released Document</label>
										<div class="col-sm-9">
											<input type="file" name="rel" placeholder="Loan Document" required class="form-control" style="background:#ffcf40">
										</div>
									</div>
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Closing KMs</label>
										<div class="col-sm-9">
											<input type="text" name="ckm" placeholder="KM" class="form-control" required>
										</div>
									</div>
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Remarks</label>
										<div class="col-sm-9">
											<textarea type="text" name="remark" class="form-control" required  placeholder="Any Remarks"></textarea>
										</div>
									</div>
								</div>
							</div>
							
							<div class="col-lg-6">
								<div class="panel-body" style="height:400px">
									<div class="panel-heading">
										<h4 class="text-success" style="font-size:15px;color:#990000"><b> Release Commission</b></h4>
									</div>
									<hr style="margin:0px">
									<label class="col-sm-12"><br></label>
									<div class="col-sm-4">
										<label class="text-success">Cash</label>
										<input type="text" name="relCash" id="relCash" class="form-control" value="<?php echo $relData['relCash']; ?>">
									</div>
									<div class="col-sm-4">
										<label class="text-success">IMPS</label>
										<input type="text" name="relIMPS" id="relIMPS" class="form-control" value="<?php echo $relData['relIMPS']; ?>" readonly>
									</div>
									<div class="col-sm-4">
										<label class="text-success">Release Amount</label>
										<input type="text" name="relAmount" id="relAmount" class="form-control" value="<?php echo $relData['amount']; ?>" readonly>
									</div>
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Commision Percentage (%)</label>
										<div class="col-sm-7">
											<input type="text" id="commPerc" class="form-control" required>
										</div>
										<div class="col-sm-2">
											<button class="btn btn-success" type="button" id="cpc">
												<span style="color:#ffcf40" class="fa fa-calculator"></span>
											</button>
										</div>
									</div>
									<label class="col-sm-12"><br></label>
									<div class="form-group">
										<label class="col-sm-3 text-success">Commision Amount</label>
										<div class="col-sm-9">
											<input type="text" name="commAmount" id="commAmount" class="form-control" readonly required>
										</div>
									</div>
								</div>
							</div>
							
							<div class="col-lg-12">
								<div class="panel-body" style="margin-top:10px">
									<div class="col-sm-8" style="text-align:right">
										<label style="padding-top:7px;padding-right:20px"><input type="checkbox" id="done" ><span class="text-success"> DONE</span></label>
									</div>
									<div class="col-sm-4">
										<button class="btn btn-success" name="submitTerminate" type="submit" id="terminateButton">
											<span style="color:#ffcf40" class="fa fa-times"></span> Terminate ( Gold Released / No Billing)
										</button>
									</div>
								</div>
							</div>
							
						</form>
						
					</div>
				</div>
			</div>
			<div style="clear:both"></div>
			<script>
				$(document).ready(function(){
					
					$('#cpc').click(function(){
						var commPerc = $('#commPerc').val(),
						relAmount = $('#relAmount').val();
						if(commPerc != '' && relAmount !=''){
							calCommAmount(relAmount,parseFloat(commPerc));
						}
						else{
							alert("PLEASE FILL THE DATA");
						}
					});
					
					function calCommAmount(relAmount,commPerc){
						var commAmount = Math.round((commPerc/100)*relAmount);
						$('#commAmount').val(commAmount);
					}
				});
			</script>
			<script>
				$(document).ready(function(){
					$('#relCash').change(function(){
						var relCash = parseInt($('#relCash').val()),
						relIMPS = parseInt($('#relIMPS').val()),
						relAmount = relCash + relIMPS;
						$('#relAmount').val(relAmount);
					});
				});
			</script>
			<script>
				$(document).ready(function(){
					if($("#session_branchID").val()){
						let branchId = $("#session_branchID").val();
						var req = $.ajax({
							url:"xbalance.php",
							type:"POST",
							data:{branchId:branchId},
							dataType:'JSON'
						});
						req.done(function(e){
							$("#balance").val(e.balance);
						});
					}
				});
			</script>
			<script>
				$(document).ready(function(){
					$('#terminateButton').attr("disabled", true);
					$('#done').change(function() {
						if(this.checked) {
							$('#terminateButton').attr("disabled", false); 
						}
						else{
							$('#terminateButton').attr("disabled", true);
						}
					});
				});
			</script>
			<?php include("footer.php"); 
			} 
			else{
				echo header("location:xreleaseStatus.php");
			} 
		}
	?>
