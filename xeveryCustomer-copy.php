<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Asia/Calcutta');
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	$branchCode = $_SESSION['branchCode'];
	$date = date('Y-m-d');
	
	/*  BRANCH DATA  */
	$branchData = mysqli_fetch_assoc(mysqli_query($con,"SELECT 
	(SELECT branchName FROM branch WHERE branchId='$branchCode') AS branchName,
	(SELECT COUNT(DISTINCT(contact)) AS totalWalkin FROM everycustomer WHERE date='$date' AND branch='$branchCode' AND status NOT IN ('Double Entry','Wrong Entry')) AS totalWalkin,
	(SELECT COUNT(id) AS totalSold FROM trans WHERE date='$date' AND branchId='$branchCode' AND status='Approved') AS totalSold,
	(SELECT date FROM closing WHERE branchId='$branchCode' ORDER BY date DESC LIMIT 1) AS ClosingDate,
	(SELECT COUNT(*) FROM trans WHERE date BETWEEN (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)) AND '$date' AND status='Approved' AND branchId='$branchCode') AS cogBills,
	(SELECT COUNT(*) FROM walkin WHERE date BETWEEN (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)) AND '$date' AND branchId='$branchCode') AS cogWalk
	"));
	
	/*  TODAY'S RATE  */
	$rateQuery = "SELECT cash,transferRate 
	FROM gold
	WHERE type='Gold' AND date='$date' AND city=(
	SELECT
	(CASE
 	WHEN priceId=1 THEN 'Bangalore'
 	WHEN priceId=2 THEN 'Karnataka'
 	WHEN priceId=3 THEN 'Andhra Pradesh'
 	WHEN priceId=4 THEN 'Telangana'
 	WHEN priceId=5 THEN 'Pondicherry'
    WHEN priceId=6 THEN 'Tamilnadu'
	END) AS city
	FROM branch 
	WHERE branchId='$branchCode')
	ORDER BY id DESC
	LIMIT 1";
	$rate = mysqli_fetch_assoc(mysqli_query($con,$rateQuery));
	
	/*   BRANCH COG REPORT   */
	if($branchData['cogWalk']!=0){
		$cog = ($branchData['cogBills']/($branchData['cogBills']+$branchData['cogWalk']))*100;
		$cog = is_nan($cog)?0:$cog;
		if ($cog > 90) {
			$ok = ', EXCELLENT';
		}
		elseif ($cog > 80) {
			$ok = ', VERY GOOD';
		}
		elseif ($cog > 70) {
			$ok = ', GOOD';
		}
		elseif ($cog > 60) {
			$ok = ', FAIR';
		}
		elseif ($cog > 50) {
			$ok = ', POOR';
		}
		elseif ($cog > 40) {
			$ok = ', VERY BAD';
		}
	}
	else{
		$ok = ', VERY BAD';
	}
	
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
	font-size: 14px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.quotation h3{
	color:#123C69;
	font-size: 18px!important;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	margin: 0px 0px 0px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	font-size:12px;
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
	.modaldesign {
	float: right;
	cursor: pointer;
	padding: 5px;
	background:none;
	color: #f0f8ff;
	border-radius: 5px;
	margin: 15px;
	font-size: 20px;
	}
	#wrapper .panel-body{
	box-shadow:10px 15px 15px #999;
	border: 1px solid #edf2f9;
	border-radius:7px;
	background-color: #f5f5f5;
	padding: 20px;
	}
	.tooltip .tooltiptext {
	visibility: hidden;
	width: 120px;
	background-color: #990000;
	color: #fff;
	text-align: center;
	border-radius: 6px;
	padding: 5px 0;
	position: absolute;
	z-index: 1;
	top: -5px;
	right: 105%;
	}
	.tooltip:hover .tooltiptext {
	visibility: visible;
	}
	.panel-box{
	margin: 20px;
	border: 4px solid #fff;
	border-radius:10px;
	padding: 20px;
	overflow: hidden;
	background: #990000;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec');
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec')";
	-moz-box-shadow: 0 0 2px rgba(0, 0, 0, 0.35), 0 85px 180px 0 #fff, 0 12px 8px -5px rgba(0, 0, 0, 0.85);
	-webkit-box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 810px -68px #fff, 0 12px 8px -5px rgb(0 0 0 / 65%);
	box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);	
	}
</style>

<!--   EVERY CUSTOMER MODAL   -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
				<h3>NEW CUSTOMER</h3>
			</div>
			<div class="modal-body">
				<form method="POST" class="form-horizontal" action="xsubmit.php" enctype="multipart/form-data">
					<input type="hidden" value="<?php echo $branchCode; ?>" class="form-control" name="branchid">
					<div class="form-group row" style = "padding-left:50px;">
						<div class="col-xs-4">
							<br>
							<div id="results" style="position:absolute;"></div>
							<div id="my_camera"></div>
							<input type="hidden" name="image" class="image-tag">
							<br>
							<a onClick="take_snapshot()" class="btn btn-success"><i class="fa fa-camera" style="color:#ffcf40"></i> Capture Customer Photo</a>
						</div>
						<div class="col-xs-8" style="padding-left:70px;">
							<div class="col-sm-9">
								<label class="text-success">Customer Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="cusname" placeholder="Customer Name" required id="cusname" class="form-control" autocomplete="off">
								</div>
							</div>
							<label class="col-sm-12"></label>
							<label class="col-sm-12"></label>
							<div class="col-sm-9">
								<label class="text-success">Contact Number</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
									<input type="text" name="cusmob" id="cusmob" pattern="[0-9]{10}" required placeholder="Contact Number" maxlength="10" required class="form-control" autocomplete="off">
								</div>
							</div>
							<label class="col-sm-12"></label>
							<label class="col-sm-12"></label>
							<div class="col-sm-9"><br>
								<button class="btn btn-success btn-block" name="submitNC" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Submit</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

<!--   QUOTATION  MODAL   -->
<div class="modal fade" id="quotationModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width:1050px;">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
				<h3>QUOTATION</h3>
			</div>
			<form method="POST" action="quotationImageUpload.php" enctype="multipart/form-data" id="quotForm">
				<input type="hidden" name="branchId" value="<?php echo $branchCode; ?>">
				<input type="hidden" id="everycustomerID" name="ecID">
				<input type="hidden" id="quotationImage" name="quotationImage">
				<input type="hidden" id="quotRate" value="<?php echo $rate['cash']; ?>">
				<div class="panel-body">
					<div class="panel-heading">
						<h3 class="panel-title">
							<a class="btn btn-success btn-user" style="color:#fff" data-toggle="collapse" data-parent="#accordion" href="#collapse1">Physical Gold</a>
							<a class="btn btn-success btn-user" style="color:#fff" data-toggle="collapse" data-parent="#accordion" href="#collapse2">Release Gold</a>
						</h3>
					</div>
					<div class="panel-group" id="accordion">
						<div class="panel panel-default" >
							<div id="collapse1" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-xs-12"> <!--  QUOTATION PHYSICAL GOLD  -->
										<div class="container-fluid" style="padding:0px;">
											<div class="col-xs-2" style="border:2px solid #cccccc;padding:10px;background-color:#ddd;border-radius:3px;">
												<select class="form-control" id="ornType">
													<option selected="true" disabled="disabled" value="">Ornament</option>
													<option Value="22 carot Biscuit (91.6">22 carot Biscuit (91.6)</option>
													<option Value="24 carot Biscuit(99.9)">24 carot Biscuit (99.9)</option>
													<option Value="22 carot Coin (91.6">22 carot Coin (91.6)</option>
													<option Value="24 carot Coin (99.9)">24 carot Coin (99.9)</option>
													<option Value="Anklets">Anklets</option>
													<option Value="Armlets">Armlets</option>
													<option Value="Baby Bangles">Baby Bangles</option>
													<option Value="Bangles">Bangles</option>
													<option Value="Bracelet">Bracelet</option>
													<option Value="Broad Bangles">Broad Bangles</option>
													<option Value="Chain">Chain</option>
													<option Value="Chain with Locket">Chain with Locket</option>
													<option Value="Chain with Black Beads">Chain with Black Beads</option>
													<option Value="Drops">Drops</option>
													<option Value="Ear Rings">Ear Rings</option>
													<option Value="Gold Bar">Gold Bar</option>
													
													<option Value="Head Locket">Head Locket</option>
													<option Value="Locket">Locket</option>
													<option Value="Matti">Matti</option>
													<option Value="Necklace">Necklace</option>
													<option Value="Ring">Ring</option>
													<option Value="Silver Bar">Silver Bar</option>
													<option Value="Silver Items">Silver Items</option>
													<option Value="Small Gold Piece">Small Gold Piece</option>
													<option Value="Studs">Studs</option>
													<option Value="Studs And Drops">Studs And Drops</option>
													<option Value="Thala/Mangalya Chain">Thali Chain</option>
													<option Value="Thala/Mangalya Chain with Black Beads">Thali Chain with Black Beads</option>
													<option Value="Waist Belt/Chain">Waist Belt/Chain</option>
													<option Value="Others">Others</option>
												</select>
												<input type="text" id="PhysicalGrossW" placeholder="Gross wgt" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="text" id="PhysicalStoneW" placeholder="Stone wgt" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="text" id="PhysicalNetW" placeholder="Net wgt" class="form-control" autocomplete="off" readonly style="margin-top:7px">
												<input type="text" id="PhysicalPurity" placeholder="Given Purity" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="text" id="SystemPurity" placeholder="System Purity" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="button" class="btn btn-primary btn-block" value="+" id="addPhysicalOrnament" style="margin-top:7px">
											</div>
											<div class="col-xs-10" id="physicalGold">
												<div class="col-xs-12" style="border:1px solid #cccccc;padding:10px; background-color:#ddd; border-radius:3px; margin-bottom:5px">
													<div class="col-xs-4" style="text-align:center;">
														<label class="text-success" style="font-weight:800">Physical Gold</label>
													</div>
													<div class="col-xs-4" style="text-align:center;">
														<label class="text-success" style="font-weight:800">Branch : <?php echo $branchData['branchName'];?></label>
													</div>
													<div class="col-xs-4" style="text-align:center;">
														<label class="text-success" style="font-weight:800">Customer : <span class="quotName"></span></label>
													</div>
												</div>
												<div class="col-sm-12" style="margin-bottom:5px;padding:0px">
													<table class="table table-bordered table-hover" id="tablePhysical">
														<thead>
															<tr>
																<th>Ornament</th>
																<th>Gross Weight</th>
																<th>Stone Weight</th>
																<th>Net Weight</th>
																<th>Given Purity (%)</th>
																<th>System Purity (%)</th>
																<th>Gross Amount</th>
																<th>Delete</th>
															</tr>
														</thead>
														<tbody id="tbodyPhysical" style="background-color:#F5F5F5">
														</tbody>
														<tfoot style="background-color:#ddd;font-weight:600;">
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
														</tfoot>
													</table>
												</div>
												<div class="col-sm-12" style="border:1px solid #cccccc;padding:10px; background-color:#ddd; border-radius:3px; margin-bottom:5px">
													<div class="col-sm-3">
														<label class="text-success">Gross Amount</label>
														<input type="text" id="grossAPhysical" readonly class="form-control">
													</div>
													<div class="col-sm-3">
														<label class="text-success">Margin (%)</label>
														<input type="text" id="marginPhysical" class="form-control">
													</div>
													<div class="col-sm-3">
														<label class="text-success">Margin Amount</label>
														<input type="text" id="marginAPhysical" readonly class="form-control">
													</div>
													<div class="col-sm-3">
														<label class="text-success">Net Amount</label>
														<input type="text" id="netAPhysical" readonly class="form-control">
													</div>
												</div>
												<div class="col-sm-12" style="padding-right:0px;padding-left:0px;">
													<textarea  style="resize:none;height:100%;width:100%;margin:0px;" placeholder="Remarks"></textarea>
												</div>
											</div>
										</div>
										<div class="col-xs-12" align="right">
											<button type="button" class="btn btn-success" style="margin-top:10px;" id="sendPhysicalQuotButton"><i class="fa fa-camera" style="color:#ffcf40"></i> Save Quotation</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div id="collapse2" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-xs-12">  <!--  QUOTATION RELEASE  -->
										<div class="container-fluid" id="releaseGold" style="padding:0px">
											<div class="col-xs-12" style="border:1px solid #cccccc;padding:10px; background-color:#ddd; border-radius:3px;margin-bottom:5px">
												<div class="col-xs-4" style="text-align:center;">
													<label class="text-success" style="font-weight:800">Release Gold</label>
												</div>
												<div class="col-xs-4" style="text-align:center;">
													<label class="text-success" style="font-weight:800">Branch : <?php echo $branchData['branchName'];?> </label>
												</div>
												<div class="col-xs-4" style="text-align:center;">
													<label class="text-success" style="font-weight:800">Customer : <span class="quotName"></span></label>
												</div>
											</div>
											<div class="col-xs-4" style="border:1px solid #cccccc;padding:10px; background-color:#ddd; border-radius:3px;">
												<div class="col-xs-6">
													<label class="text-success">Rate</label>
													<input type="text" value="<?php echo $rate['cash']; ?>" class="form-control" readonly>
												</div>
												<div class="col-xs-6">
													<label class="text-success">Release Amount</label>
													<input type="text" id="releaseAmount" class="form-control" placeholder="Release Amount">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Gross Weight</label>
													<input type="text" id="grossWRelease" class="form-control" placeholder="Gross Weight">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Net Weight</label>
													<input type="text" id="netWRelease" class="form-control" placeholder="Net Weight">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Release Purity (%)</label>
													<input type="text" id="purityRelease" class="form-control" placeholder="Purity %" readonly>
												</div>
												<div class="col-xs-6" style="text-align:center;margin-top:22px">
													<button class="btn btn-success" type="button" id="quotation-button-release">
														<span style="color:#ffcf40" class="fa fa-calculator"></span>
													</button>
												</div>
											</div>
											<div class="col-xs-8">
												<table class="table table-bordered table-hover" id="tableRelease">
													<thead>
														<tr>
															<th style="text-align:center;">Purity</th>
															<th style="text-align:center;">Gross Amount</th>
															<th style="text-align:center;">Net Amount Payable (2%)</th>
															<th style="text-align:center;">Net Amount Payable (3%)</th>
														</tr>
													</thead>
													<tbody style="text-align:center;background-color:#F5F5F5">
														<tr id="quot91">
															<td> 91% </td>
															<td></td>
															<td></td>
															<td></td>
														</tr>
														<!--<tr id="quot88">
															<td> 88% </td>
															<td></td>
															<td></td>
															<td></td>
														</tr>
														<tr id="quot84">
															<td> 84% </td>
															<td></td>
															<td></td>
															<td></td>
														</tr>-->
													</tbody>
												</table>
											</div>
										</div>
										<div class="col-xs-12" align="right">
											<button type="button" class="btn btn-success" style="margin-top:15px;" id="sendReleaseQuotButton"><i class="fa fa-camera" style="color:#ffcf40"></i> Save Quotation</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div style="clear:both"></div>
</div>


<div id="wrapper">
	<div class="row content">
		
		<?php if($branchData['ClosingDate'] == $date){ ?>
			<div class="col-xs-12 panel-body" style="text-align:center; padding:70px;margin-bottom:30px;margin-top:30px;">
				<h3 class='text-success' style='font-weight: bold;'>BRANCH IS CLOSED <br><hr>TO REOPEN CALL TO APPROVAL TEAM : 8925537846</h3>
			</div>
			<?php }else{ ?>
			
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-body" style="height:120px">
						<div class="stats-title pull-left">
							<h3 class="text-success">New Customer</h3>
						</div>
						<div class="stats-icon pull-right">
							<?php 
								/* 
									NEW CUSTOMER BUTTON
									24-HOUR FORMAT OF AN HOUR WITHOUT LEADING ZEROS (0 THROUGH 23) 
								*/
								$Hour = date('G');
								$days = date('l');
								if ($days == 'Thursday' || ($Hour >= 17 || $Hour <= 0) ) {
								?>
								<button title="Click To Add New Customer" data-toggle="modal" data-target="#myModal" class='btn' style="background-color:#123C69;padding:5px 8px 5px 8px;box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);">
									<i style="color:#ffa500" class="pe-7s-add-user fa-2x"></i>
								</button>
								<?php }else{ ?>
								<i style="color:#990000" class="pe-7s-add-user fa-3x"></i>
							<?php } ?>
						</div>
						<div class="m-t-xl" style="margin-top:50px">
							<span class="label" style="background-color:#C0e8b1;color:black;font-size:11px">YOUR COG IS <span><?php echo round($cog) . ' %'; ?></span> <?php echo $ok;
							?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-body" style="height:120px">
						<div class="stats-title pull-left">
							<h3 class="text-success"><?php echo $branchCode; ?></h3>
						</div>
						<div class="stats-icon pull-right">
							<i style="color:#990000" class="pe-7s-culture fa-3x"></i>
						</div>
						<div class="m-t-xl" style="margin-top:50px">
							<p class="font-bold no-margins">
								<?php echo $branchData['branchName']; ?>
							</p>
						</div>
					</div>
				</div>
			</div>			
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body" style="height:120px">
						<div class="stats-title pull-left">
							<h3 class="text-success">Rate</h3>
						</div>
						<div class="stats-icon pull-right">
							<i style="color:#990000" class="pe-7s-cash fa-3x"></i>
						</div>
						<div class="clearfix"></div>
						<div class="col-xs-5" style="padding:0px">
							<small class="stat-label font-extra-bold" style="color:#636464">CASH</small>
							<h4><?php echo $rate['cash']; ?></h4>
						</div>
						<div class="col-xs-7" style="padding:0px">
							<small class="stat-label font-extra-bold" style="color:#636464">IMPS</small>
							<h4><?php echo $rate['transferRate']; ?></h4>
						</div>
					</div>
				</div>
			</div>			
			<div class="col-lg-3">
				<div class="hpanel stats">
					<div class="panel-body" style="height:120px">
						<div class="stats-title pull-left">
							<h3 class="text-success">Business</h3>
						</div>
						<div class="stats-icon pull-right">
							<i style="color:#990000" class="pe-7s-users fa-3x"></i>
						</div>
						<div class="clearfix"></div>
						<div class="col-xs-6" style="padding:0px">
							<small class="stat-label font-extra-bold" style="color:#636464">WALKIN</small>
							<h4><?php echo $branchData['totalWalkin']; ?></h4>
						</div>
						<div class="col-xs-6" style="padding:0px">
							<small class="stat-label font-extra-bold" style="color:#636464">SOLD</small>
							<h4><?php echo $branchData['totalSold']; ?></h4>
						</div>
						
					</div>
				</div>
			</div>
			
			<!--   ENQUIRY / PROCEED TO BUSINESS   -->
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-body">
						<div class="table-responsive project-list">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th></th>
										<th>Customer</th>
										<th>Mobile</th>
										<th style='text-align:center;' colspan="2">Quotation</th>
										<th style='text-align:center;'>Action</th>
										<th style='text-align:center;'>Info</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$query = mysqli_query($con,"SELECT Id,customer,contact,quotation,status,time FROM everycustomer WHERE date='$date' AND branch='$branchCode' AND status IN ('0','Blocked') ORDER BY Id DESC");
										while($row = mysqli_fetch_assoc($query)){
											
											//CHECK PHONE NUMBER 
											$contact = $row['contact'];
											$checkQ1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS countT FROM fraud WHERE phone='$contact'"));
											if ($checkQ1['countT'] > 0){
												echo "<tr>";
												echo "<td class='text-danger'><i class='fa fa-caret-right'></i></td>";
												echo "<td class='text-danger'>" . $row['customer'] . "</td>";
												echo "<td class='text-danger'>" . $row['contact'] . "</td>";
												echo "<td style='text-align:center' colspan='4'><button class='btn btn-danger'> CALL HO ( THIEF / FRAUD )</button></td>";
												echo "</tr>";
											}
											else{
												echo "<tr>";
												echo "<td><i class='fa fa-caret-right'></i></td>";
												echo "<td>".$row['customer']."</td>";
												echo "<td>".$row['contact']."</td>";
												
												// QUOTATION BUTTON
												echo "<td style='text-align:right'><a class='btn btn-success btn-user' data-toggle='modal' data-target='#quotationModal' data-todo='{\"id\":\"".$row['Id']."\",\"name\":\"".$row['customer']."\"}'><i class='fa fa-inr' style='font-size:15px;color:#ffa500'></i></a></td>";
												
												// VIEW QUOTATION IMAGE
												if($row['quotation']==''){
													echo "<td></td>";
												}
												else{
													$decoded = json_decode($row['quotation'],true);
													echo "<td><a target='_BLANK' href='QuotationImage/".$decoded['image']."'><button class='btn btn-circle' type='button'><i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69' ></i></button></a></td>";
												}
												
												// ENQUIRY
												if($row['quotation']==''){
													echo "<td style='text-align:center'>
													<button disabled class='btn btn-success btn-user tooltip' style='margin-right:25px'>
													<i class='fa fa-comments' style='color:#ffa500'></i>
													<span class='tooltiptext'>Quotation Not Given</span>
													<b> ENQUIRY</b>
													</button>";
												}
												else{
													echo "<td style='text-align:center'><a href='xbranchEnquiry.php?id=".$row['Id']."' class='btn btn-success btn-user' style='margin-right:25px'><i class='fa fa-comments' style='color:#ffa500'></i><b> ENQUIRY</b></a>";
												}
												
												// BILLING
												if($row['status']=="Blocked"){ 
													// BLOCK THE CUSTOMER FROM BILLING UPON BRANCH VISIT AGAIN WITH PREVIOUSLY BILLED IN LESS THAN 20DAYS
													echo "<button class='btn btn-danger'><span style='color:#ffcf40' class='fa fa-phone'></span>  CALL BILL APPROVAL TEAM TO PROCEED </button></td>";
												}
												else{
													echo "<a href='xcheckCustomer.php?contact=".$row['contact']."&Id=".$row['Id']."&encTime=".$row['time']."' class='btn btn-success btn-user'><i class='fa fa-arrow-right' style='color:#ffa500'></i><b> BILLING</b></a></td>";
												}
												
												// CUSTOMER'S INFO
												$checkRow = mysqli_fetch_assoc(mysqli_query($con,"SELECT (CASE WHEN EXISTS(SELECT mobile FROM walkin WHERE mobile='$contact') THEN 1 ELSE 0 END) AS walkin ,(CASE WHEN EXISTS(SELECT phone FROM trans WHERE phone='$contact') THEN 1 ELSE 0 END) AS trans"));
												if($checkRow['walkin']=='0' && $checkRow['trans']=='0'){
													echo "<td style='text-align:center' class='text-success'><b>New Customer</b></td>";
												}
												else if($checkRow['walkin']=='0' && $checkRow['trans']=='1'){
													echo "<td style='text-align:center' class='text-success'><b> Existing Customer</b></td>";
												}
												else if($checkRow['walkin']=='1' && $checkRow['trans']=='0'){
													echo "<td style='text-align:center'><a target='_BLANK' class='btn btn-success' href='xeveryCustomerDetails.php?id=".$row['contact']."' style='color:white;'><span style='color:#ffcf40' class='fa fa-info'></span> Enquiry Customer </a></td>";
												}
												else if($checkRow['walkin']=='1' && $checkRow['trans']=='1'){
													echo "<td style='text-align:center'><a target='_BLANK' class='btn btn-success' href='xeveryCustomerDetails.php?id=".$row['contact']."' style='color:white;'><span style='color:#ffcf40' class='fa fa-check-circle-o '></span> Existing Customer </a></td>";
												}
												echo "</tr>";
											}
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			
		<?php } ?>
		
		<!-- ===================================== RENEWAL EXPIRY ALERT ================================== -->
		<div class="col-lg-2"></div>
		<?php
			//$today = date('Y-m-d');
			$current_date = strtotime($date);
			$renewalQuery="SELECT renewal_date FROM branch WHERE branchId='$branchCode'";
			$bRow = mysqli_fetch_assoc(mysqli_query($con,$renewalQuery));
			
			if($bRow["renewal_date"]!="" && $bRow["renewal_date"]!="0000-00-00" && $bRow["renewal_date"]!="00-00-0000" && $bRow["renewal_date"]!=null && $bRow["renewal_date"]!=0){
				
				$renewal_date = strtotime($bRow["renewal_date"]);
				$difference = ($renewal_date - $current_date)/60/60/24;	
				
				if($difference<=5 && $difference>0){
					echo "<div class='col-lg-8 panel-box'>";
					echo "<h5 align='center' style='color:#ffd700;'>WEIGHING SCALE RENEWAL ALERT</h5>";
					echo "<h5 align='center' style='color:#faebd7;'> RENEWAL DATE EXPIRY IS APPROACHING IN ".$difference." DAYS </h5>";
					echo "</div>";
					}elseif($difference==0){
					echo "<div class='col-lg-8 panel-box'>";
					echo "<h5 align='center' style='color:#ffd700;'>WEIGHING SCALE RENEWAL ALERT</h5>";
					echo "<h5 align='center' style='color:#faebd7;'> RENEWAL DATE EXPIRING TODAY </h5>";
					echo "</div>";
					}elseif($difference<0){
					echo "<div class='col-lg-8 panel-box'>";
					echo "<h5 align='center' style='color:#ffd700;'>WEIGHING SCALE LICENSE RENEWAL ALERT</h5>";
					echo "<h5 align='center' style='color:#faebd7;'> RENEWAL DATE ALREADY EXPIRED ON ".date("d-m-Y", strtotime($bRow["renewal_date"]))." </h5>";
					echo "</div>";
				}
				
			}	 	
		?>		
		<div class='col-lg-2'></div>
		<!-- ********************************************************************************************** -->	
	</div>
	<script>
		$(document).ready(function(){
			$('#quotationModal').on('show.bs.modal', function (event) {
				var button = $(event.relatedTarget),
				id = button.data('todo').id,
				name = button.data('todo').name,
				modal = $(this);
				modal.find('#everycustomerID').val(id);
				modal.find('.quotName').text(name);
			});
			$("#sendPhysicalQuotButton").click(function() {
				html2canvas($("#physicalGold"), {
					onrendered: function(canvas) {
						var dataURL = canvas.toDataURL();
						$('#quotationImage').val(dataURL);
						var form = document.getElementById("quotForm");
						form.submit();
					}
				});
			});
			$("#sendReleaseQuotButton").click(function() {
				html2canvas($("#releaseGold"), {
					onrendered: function(canvas) {
						var dataURL = canvas.toDataURL();
						$('#quotationImage').val(dataURL);
						var form = document.getElementById("quotForm");
						form.submit();
					}
				});
			});
		});
	</script>
	<script>
		/*  QUOTATION RELEASE GOLD  */
		$(document).ready(function(){
			$('#quotation-button-release').click(function(){
				let rateRel = $('#quotRate').val(),
				relAmount = $('#releaseAmount').val(),
				releaseNW = $('#netWRelease').val();
				if(rateRel!=null && rateRel!='' && releaseAmount!='' && releaseNW!=''){
					let xAmount = releaseNW * rateRel;
					$('#quot91').html("<td>91%</td><td>"+(xAmount*0.91).toFixed()+"</td><td>"+((xAmount*0.91*0.98)-relAmount).toFixed()+"</td><td>"+((xAmount*0.91*0.97)-relAmount).toFixed()+"</td>");
					//$('#quot88').html("<td>88%</td><td>"+(xAmount*0.88).toFixed()+"</td><td>"+((xAmount*0.88*0.98)-relAmount).toFixed()+"</td><td>"+((xAmount*0.88*0.97)-relAmount).toFixed()+"</td>");
					//$('#quot84').html("<td>84%</td><td>"+(xAmount*0.84).toFixed()+"</td><td>"+((xAmount*0.84*0.98)-relAmount).toFixed()+"</td><td>"+((xAmount*0.84*0.97)-relAmount).toFixed()+"</td>");
					
					let relPurity = (((relAmount/releaseNW)/rateRel)*100).toFixed(2);
					if(relPurity <= 70){
						$('#purityRelease').val(relPurity).css({"background-color": "green","color":"white"});
					}
					else if(relPurity > 70 && relPurity <= 80){
						$('#purityRelease').val(relPurity).css({"background-color": "yellow","color":"black"});
					}
					else if(relPurity > 80 && relPurity <= 85){
						$('#purityRelease').val(relPurity).css({"background-color": "red","color":"white"});
					}
					else{
						$('#purityRelease').val(relPurity).css({"background-color": "DarkRed","color":"white"});
					}
				}
				else{
					alert('PLEASE FILL ALL THE DATA');
				}
			});
		});
		/*  QUOTATION PHYSICAL GOLD  */
		$(document).ready(function(){
			
			$('#PhysicalStoneW').change(function(){
				let grossw = $('#PhysicalGrossW').val(),
				stone = $('#PhysicalStoneW').val(),
				netw = +grossw - stone;
				$('#PhysicalNetW').val(netw.toFixed(2));
			});
			
			$('#addPhysicalOrnament').click(function(){
				let rate1 = $('#quotRate').val(),
				ornType = $('#ornType').val(),
				grossw1 = $('#PhysicalGrossW').val(),
				stonew1 = $('#PhysicalStoneW').val(),
				netw1 = $('#PhysicalNetW').val(),
				purity1 = $('#PhysicalPurity').val(),
				systemPurity = $('#SystemPurity').val(),
				grossAmount = (netw1 * rate1 * (purity1/100)).toFixed();
				if(rate1!=null && rate1!='' && netw1!='' && purity1!='' && ornType!='' && ornType!=null){
					
					$('#ornType,#PhysicalGrossW,#PhysicalStoneW,#PhysicalNetW,#PhysicalPurity,#SystemPurity').val('');
					
					let newLine = "<tr><td>"+ornType+"</td><td>"+grossw1+"</td><td>"+stonew1+"</td><td>"+netw1+"</td><td>"+purity1+"</td><td>"+systemPurity+"</td><td>"+grossAmount+"</td><td style='text-align:center'><input type='button' value='X' class='btn btn-danger btn-xs delRow'></td></tr>";
					$('#tbodyPhysical').append(newLine);
					
					calculateColumn();
				}
				else{
					alert('PLEASE FILL ALL THE DATA');
				}
			});
			
			$('#tbodyPhysical').on('click','.delRow', function() {
				$(this).parent().parent().remove();
				calculateColumn();
			});
			
			$('#marginPhysical').change(function(){
				let marginPerc = $('#marginPhysical').val();
				if(marginPerc != ''){
					let grossAtotal = $('#grossAPhysical').val(),
					netATotal = (grossAtotal * ((100-marginPerc)/100)).toFixed(),
					marginTotal = grossAtotal - netATotal;
					$('#marginAPhysical').val(marginTotal);
					$('#netAPhysical').val(netATotal);
				}
			});
			
			function calculateColumn(){
				const THeq = [1,2,3,6];
				for (const index of THeq) {
					var total = 0;
					$('#tablePhysical tbody tr').each(function () {
						var value = parseFloat($('td', this).eq(index).text());
						if (!isNaN(value)){
							total += value;
						}
					});
					$('#tablePhysical tfoot td').eq(index).text(total.toFixed(2));
				}
				let totalGA = parseFloat($('#tablePhysical tfoot td').eq(6).text()),
				totalNA = (totalGA * 0.97).toFixed(),
				marginA = (totalGA - totalNA).toFixed();
				
				$('#grossAPhysical').val(totalGA);
				$('#marginPhysical').val(3);
				$('#marginAPhysical').val(marginA);
				$('#netAPhysical').val(totalNA);
			}
			
		});
	</script>
	<?php include("footer.php"); ?>
	<script src="scripts/webcam.min.js"></script>
	<script type="text/javascript" src="scripts/html2canvas.js"></script>
	<script language="JavaScript">
		Webcam.set
		({
			width: 210,
			height: 160,
			image_format: 'jpeg',
			jpeg_quality: 100
		});
		Webcam.attach('#my_camera');
		function take_snapshot()
		{
			Webcam.snap(function(data_uri)
			{
				$(".image-tag").val(data_uri);
				document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
			});
		}
	</script>			