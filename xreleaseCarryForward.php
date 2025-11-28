<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];	
	include("dbConnection.php");
	
	if(isset($_GET['rid'])){
		$relData = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,amount,relCash,relIMPS FROM releasedata WHERE rid='$_GET[rid]'"));		
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
						<h3 class="text-success" style="font-size:18px"><b><i style="color:#900;" class="fa fa-edit"></i> UPDATE GOLD RELEASE INFO & CARRY FORWARD</b></h3>
					</div>
					<form method="POST" action="xaddAfterRelease.php">
						<input type="hidden" name="rid" value="<?php echo $_GET['rid']; ?>">
						
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
								<label class="col-sm-12"><br><hr></label>
								<div class="form-group">
									<label class="col-sm-3 text-success" style="padding-top:7px;"><input type="checkbox" id="done" > DONE</label>
									<div class="col-sm-9" style="text-align:right">
										<button class="btn btn-success" name="submitCarryForward" type="submit" id="carryForwardButton">
											<span style="color:#ffcf40" class="fa fa-fast-forward"></span> Carry Forward
										</button>
									</div>
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
				$('#carryForwardButton').attr("disabled", true);
				$('#done').change(function() {
					if(this.checked) {
						$('#carryForwardButton').attr("disabled", false); 
					}
					else{
						$('#carryForwardButton').attr("disabled", true);
					}
				});
			});
		</script>
		<?php include("footer.php");
		}
	?>