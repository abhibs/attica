<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$branchStatus = mysqli_fetch_assoc(mysqli_query($con,"SELECT 
	(SELECT COUNT(*) FROM branch WHERE Status=1 AND branchId != 'AGPL000') AS active,
	(SELECT COUNT(*) FROM branch WHERE Status=0) AS closed,
	(SELECT branchId FROM branch ORDER BY id DESC LIMIT 1) AS lastID"));
	$lastID = (int) filter_var($branchStatus['lastID'], FILTER_SANITIZE_NUMBER_INT);
	$lastID = $lastID + 1;
	
?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight: 600;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
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
	.fa_Icon {
	color: #990000;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius: 3px;
	padding: 15px;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>

<!--   ADD BRANCH MODAL   -->
<div class="modal fade" id="addBranchModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width:1050px;">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
				<h3>ADD NEW BRANCH</h3>
			</div>
			<div class="modal-body" style="padding-right: 40px; background-color: #f5f5f5;">
				<form method="POST" class="form-horizontal" action="add.php">
					<div class="row content">
						<div class="col-sm-4">
							<label class="text-success">BRANCH ID</label>
							<input type="text" name="branchId" class="form-control" value="<?php echo "AGPL".$lastID; ?>" readonly required >
						</div>
						<div class="col-sm-4">
							<label class="text-success">BRANCH NAME</label>
							<input type="text" name="branchName" class="form-control" autocomplete="off" required>						
						</div>
						<div class="col-sm-4">
							<label class="text-success">BRANCH AREA</label>						
							<input type="text" name="branchArea" class="form-control" autocomplete="off" required>							
						</div>
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-12">
							<label class="text-success">BRANCH ADDRESS</label>
							<textarea type="text" name="branchAddress" class="form-control" autocomplete="off" required></textarea>						
						</div>
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-3">
							<label class="text-success">CITY</label>
							<input type="text" name="city" class="form-control" autocomplete="off" required>							
						</div>
						<div class="col-sm-3">
							<label class="text-success">STATE</label>							
							<select  class="form-control" name="state" id="branchState" style="padding:0px 2px" required>
								<option selected="true" disabled="disabled" value="">SELECT STATE</option>
								<option value="Karnataka">Karnataka</option>
								<option value="Tamilnadu">Tamilnadu</option>
								<option value="Telangana">Telangana</option>
								<option value="Andhra Pradesh">Andhra Pradesh</option>
								<option value="Pondicherry">Pondicherry</option>
							</select>						
						</div>
						<div class="col-sm-3">
							<label class="text-success">PINCODE</label>
							<input type="text" name="pincode" class="form-control" maxlength="6" autocomplete="off" required>						
						</div>
						<div class="col-sm-3">
							<label class="text-success">GST</label>
							<input type="text" name="gst" id="branchgst" class="form-control" autocomplete="off" required readonly>						
						</div>
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-3">
							<label class="text-success">PHONE</label>
							<input type="text" name="phone" class="form-control" value="<?php echo "8880300300"; ?>" autocomplete="off" required>						
						</div>
						<div class="col-sm-6">
							<label class="text-success">WEBMAIL</label>							
							<input type="text" name="email" class="form-control" autocomplete="off">							
						</div>
						<div class="col-sm-3">
							<label class="text-success">URL</label>						
							<input type="text" name="url" class="form-control" autocomplete="off" placeholder="bit.ly Format">							
						</div>
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-3">
							<label class="text-success">PRICE ID</label>						
							<select  class="form-control" name="priceId"  style="padding:0px 2px" required>
								<option selected="true" disabled="disabled" value="">SELECT PRICEID</option>
								<option value="1">Bengaluru</option>
								<option value="2">Karnataka</option>
								<option value="3">Andhra Pradesh</option>
								<option value="4">Telangana</option>
								<option value="5">Pondicherry</option>
								<option value="6">Tamilnadu</option>
							</select>							
						</div>
						<div class="col-sm-9" align="right" style="padding-top:22px;">
							<button class="btn btn-success" name="submitBranch" id="submitBranch" type="submit">
								<span style="color:#ffcf40" class="fa fa-plus"></span> ADD BRANCH
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>


<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="card">
					<div class="card-header" id="headingOne">
						<h3 class="font-light m-b-xs text-success" >
							<b><i class="fa_Icon fa fa-institution"></i> BRANCH DETAILS</b>
						</h3>
					</div>
					<div class="card-body container-fluid" style="margin-top:24px;padding:0px;align:right">
						<div class="col-lg-2">
							<div class="panel-body text-center" style="margin-bottom:0px">
								<h3 class="m-xs" style="color: #990000;">
									<b><?php echo $branchStatus['active']; ?></b>
								</h3>
								<h5 class="font-extra-bold no-margins text-success">
									ACTIVE
								</h5>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="panel-body text-center" style="margin-bottom:0px">
								<h3 class="m-xs" style="color: #990000;">
									<b><?php echo $branchStatus['closed']; ?></b>
								</h3>
								<h5 class="font-extra-bold no-margins text-success">
									CLOSED
								</h5>
							</div>
						</div>
						<div class="col-lg-2">
							<a data-toggle="modal" data-target="#addBranchModal">
								<div class="panel-body text-center" style="margin-bottom:0px">
									<h3 class="m-xs" style="color: #990000;">
										<i class='fa fa-plus'></i>
									</h3>
									<h5 class="font-extra-bold no-margins text-success">
										ADD NEW BRANCH
									</h5>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example1" class="table table-hover table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>ID</th>
									<th>BRANCH NAME</th>
									<th>STATE</th>
									<th>ADDRESS</th>								
									<th>WEBMAIL</th>
									<th>WS</th>
									<th style="text-align:center;">STATUS</th>
									<th style="text-align:center;">MEET</th>
									<th style="text-align:center;">EDIT</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									$sql = mysqli_query($con,'SELECT branchId,branchName,addr,state,email,Status,ws_access,meet FROM branch ORDER BY branchId');
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchId'] . "</td>";									
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['state'] . "</td>";
										echo "<td>" . $row['addr'] . "</td>";									
										echo "<td>" . $row['email'] . "</td>";
										if($row['ws_access'] == 1){
											echo "<td><a class='btn btn-lg' type='button'><i class='fa fa-balance-scale text-success' style='font-size:15px'></i></button></td>";
										}
										else{										
											echo "<td></td>";
										}
										if($row['Status'] == 1){
											echo "<td style='text-align:center'><a class='btn' type='button' title='Active'><i class='fa fa-circle' style='color: green'></i></button></td>";
										}
										else{
											echo "<td style='text-align:center'><a class='btn' type='button' title='Closed'><i class='fa fa-circle text-danger' ></i></button></td>";
										}
										echo "<td style='text-align:center'><a target='_blank' href='".$row['meet']."' class='btn' type='button'><i class='fa fa-video-camera text-success'style='font-size:16px'></i></a></td>";
										echo "<td style='text-align:center'><a href='editBranch.php?id=".$row['branchId']."' class='btn' type='button'><i class='fa fa-pencil-square-o text-success'style='font-size:16px'></i></a></td>";
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
		$(document).ready(function(){
			$('#branchState').change(function(){
				let state = $('#branchState').val();
				const choice = state.split(" ");
				const gst = {
					Karnataka : "29AANCA2059B1ZS",
					Andhra : "37AANCA2059B1ZV",
					Telangana : "36AANCA2059B1ZX",
					Tamilnadu : "33AANCA2059B1Z3",
					Pondicherry : "33AANCA2059B1Z3"
				};
				$('#branchgst').val(gst[choice[0]]);
			});
		});
	</script>
<?php include("footer.php"); ?>
