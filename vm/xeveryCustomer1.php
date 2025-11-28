<?php
	session_start();
	$type = $_SESSION['usertype'];
	if($type == 'VM-HO'){
		include("headervc.php");
		include("menuvc.php");
		$branchCode = base64_decode($_GET['mn']);
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	/*  BRANCH DATA  */
	$branchData = mysqli_fetch_assoc(mysqli_query($con,"SELECT 
	(SELECT branchName FROM branch WHERE branchId='$branchCode') AS branchName,
	(SELECT COUNT(DISTINCT(contact)) AS totalWalkin FROM everycustomer WHERE date='$date' AND branch='$branchCode' AND status NOT IN ('Double Entry','Wrong Entry')) AS totalWalkin,
	(SELECT COUNT(id) AS totalSold FROM trans WHERE date='$date' AND branchId='$branchCode' AND status='Approved') AS totalSold,
	(SELECT SUM(netA) FROM trans WHERE branchId='$branchCode' AND status='Approved' AND date='$date') AS totalTransNet,
	(SELECT ROUND(SUM(grossW),2) FROM trans WHERE branchId='$branchCode' AND status='Approved' AND metal='Gold' AND date='$date') AS GgrossW,
	(SELECT ROUND(SUM(grossW),2) FROM trans WHERE branchId='$branchCode' AND status='Approved' AND metal='Silver' AND date='$date') AS SgrossW
	"));
	
	/*  TODAY'S RATE  */
// 	$rateQuery = "SELECT cash,transferRate 
// 	FROM gold
// 	WHERE type='Gold' AND date='$date' AND city=(
// 	SELECT
// 	(CASE
//  	WHEN priceId=1 THEN 'Bangalore'
//  	WHEN priceId=2 THEN 'Karnataka'
//  	WHEN priceId=3 THEN 'Andhra Pradesh'
//  	WHEN priceId=4 THEN 'Telangana'
//  	WHEN priceId=5 THEN 'Pondicherry'
//     WHEN priceId=6 THEN 'Tamilnadu'
// 	END) AS city
// 	FROM branch 
// 	WHERE branchId='$branchCode')
// 	ORDER BY id DESC
// 	LIMIT 1";
// 	$rate = mysqli_fetch_assoc(mysqli_query($con,$rateQuery));
	
	$remaingold = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS bill, ROUND(COALESCE(SUM(grossW),0),2) AS grossW FROM trans WHERE status='Approved' AND sta ='' AND metal='Gold' AND branchId='$branchCode' AND branchId=CurrentBranch"));
	
	$closingData = mysqli_fetch_assoc(mysqli_query($con, "SELECT one, two, three, four, five, six, seven, eight, nine, ten,balance,total,diff,date,forward FROM closing WHERE branchId='$branchCode' ORDER BY date DESC LIMIT 1"));
	if($closingData['date'] == $date){
		$todayBalance = $closingData['total'];
		$todayDiff = $closingData['diff'];
		$todayForward = $closingData['forward'];
	}
	else{
		$todayBalance = '';
		$todayDiff = '';
		$todayForward = '';
	}
	
?>
<link rel="stylesheet" href="../vendor/fooTable/css/footable.core.min.css" />

<!--   MODAL - VERIFY ATTENDANCE   -->
<div class="modal fade" id="verifyAttend" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal" style="margin-top: 20px;margin-right: 20px;font-size: 20px;background: none;"></span>
			<div class="modal-header" style="background-color:#123C69;color:#f5f5f5;">
				<h4 style="text-transform:uppercase;">Verify Attendance Of , <span id="empName"></span></h4>
			</div>
			<div class="modal-body" style="min-height:100px;">
				<input type="hidden" value="<?php echo $branchCode; ?>" class="form-control" name="branchid">
				<div class="col-sm-12">
					<div class="col-sm-4">
						<input type="hidden" name="vmempID" id="vmempID" class="form-control" autocomplete="off" value="<?php echo $_SESSION['employeeId']; ?>">
						<input type="hidden" name="empID" id="empID" class="form-control" autocomplete="off" value="">
					</div>
					<div class="col-sm-4">
						<label class="text-success">SELECT TIME</label>
						<div class="input-group vmTime">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-clock-o"></span></span>
							<input type="time" name="vmTime" style="padding:0px 5px" id="vmTime" required placeholder="Select Time" maxlength="10" required class="form-control" autocomplete="off">
						</div>
						<span class="vmTime-error text-danger"> Please select the time </span>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success pull-right" onClick="verifyAttendance()" name="verifyAttendance" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> VERIFY </button>
			</div>
		</div>
	</div>
</div>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-4">
			<div class="hpanel stats">
				<div class="panel-body list">
					<div class="stats-title pull-left" style="color:#990000;font-size:15px;">
						<h4><?php echo $branchCode; ?></h4>
					</div>
					<div class="stats-icon pull-right">
						<i class="pe-7s-culture fa-3x" style="color:#990000"></i>
					</div>
					<div class="m-t-xl">
						<span class="font-bold no-margins text-success">
							<?php echo $branchData['branchName']; ?>
						</span>
						<hr/>
					</div>
					<!--<div class="row m-t-md">-->
					<!--	<div class="col-lg-6">-->
					<!--		<h3 class="no-margins"><?php echo $rate['cash']; ?></h3>-->
					<!--		<div>Cash Rate</div>-->
					<!--	</div>-->
					<!--	<div class="col-lg-6">-->
					<!--		<h3 class="no-margins"><?php echo $rate['transferRate']; ?></h3>-->
					<!--		<div>IMPS Rate</div>-->
					<!--	</div>-->
					<!--</div>-->
					<div class="row m-t-md">
						<div class="col-lg-6">
							<h3 class="no-margins"><?php echo $branchData['totalWalkin']; ?></h3>
							<div>Walkin</div>
						</div>
						<div class="col-lg-6">
							<h3 class="no-margins"><?php echo $branchData['totalSold']; ?></h3>
							<div>Sold</div>
						</div>
					</div>
					<div class="row m-t-md">
						<div class="col-lg-6">
							<h3 class="no-margins"><?php echo $remaingold['bill']; ?></h3>
							<div>Packets</div>
						</div>
						<div class="col-lg-6">
							<h3 class="no-margins"><?php echo $remaingold['grossW']; ?></h3>
							<div>Gold In Branch</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-8">
			<div class="hpanel">
				<ul class="nav nav-tabs">
					<li class="active font-bold"><a data-toggle="tab" href="#tab-1"> CUSTOMER</a></li>
					<li class="font-bold"><a data-toggle="tab" href="#tab-2">EMPLOYEE</a></li>
				</ul>
				<div class="tab-content ">
					<div id="tab-1" class="tab-pane active">
						<div class="panel-body list">
							<div class="table-responsive project-list">
								<table id="customerFooTable" class="footable table table-stripped toggle-arrow-tiny" data-filter=#filter data-page-size="30">
									<thead>
										<tr class="theadRow">
											<th data-toggle="true">Customer</th>
											<th>Mobile</th>
											<th>Time</th>
											<th>Type</th>
											<th class="text-center">Status</th>
											<th></th>
											<th data-hide="all">Gross Weight</th>
											<th data-hide="all">No Of Ornaments</th>
											<th data-hide="all">Hallmark</th>
											<th data-hide="all">With Metal</th>
											<th data-hide="all">Release</th>
											<th data-hide="all">Remarks</th>
											<th data-hide="all">Bills</th>
											<th data-hide="all">Remarks 2</th>

										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											$query = mysqli_query($con, "SELECT Id,customer,concat('XXXXXX', right(contact, 4)) as contact,type,status,time,extra,idnumber, remark1, walkinType
											FROM everycustomer 
											WHERE date='$date' AND branch='$branchCode'");
											while ($row = mysqli_fetch_assoc($query)) {
												$contact = $row['contact'];
												echo "<tr>";
												echo "<td>".$row['customer']."</td>";
												echo "<td>".$row['contact']."</td>";
												echo "<td>".$row['time']."</td>";
												echo "<td>".$row['type']."</td>";
																																		
												if ($row['status'] == '0') {
													echo "<td class='text-center'><i>Waiting<i></td>
													<td >
													<form method='POST' action='vmSubmit.php'>
													<input type='hidden' name='id' value='".$row['Id']."'>	
													<div style='display: flex; align-items: center; gap: 8px;'>
													<input type='text' name='remark1' placeholder = 'Remark' class='form-control' required>
													<button title='Wrong Entry' type='submit' name='customerStatusChange' style='border:none; background:transparent;' onClick=\"javascript: return confirm('IS THIS A WRONG ENTRY???');\" >&#10060</button>
													</div>
													</form>
													</td>";
												} 
												else {
													echo "<td class='text-center'>".$row['status']."</td><td></td>";
												}
												
												$extra = json_decode($row['extra'],true);
												echo "<td>".$extra['GrossW']."</td>";
												echo "<td>".$extra['itemCount']."</td>";
												echo "<td>".$extra['Hallmark']."</td>";
												echo "<td>".$extra['With']."</td>";
												echo "<td>Amount : ".$extra['RelAmount'].", With Slips : ".$extra['RelSlips']."</td>";
												echo "<td>".$row['idnumber']."</td>";
												echo "<td>".$extra['bills']."</td>";
												echo "<td>".$row['remark1']."</td>";
												echo "<td>".$row['walkinType']."</td>";
												echo "</tr>";
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div id="tab-2" class="tab-pane">
						<div class="panel-body list">
							<div class="table-responsive project-list">
								<table class="table table-striped">
									<thead>
										<tr class="theadRow">
											<th>Employee</th>
											<th>Login Time</th>
											<th class="text-center">Photo</th>
											<th class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = mysqli_query($con, "SELECT * FROM attendance WHERE attendance.date='$date' AND branchId='$branchCode'");
											while ($row = mysqli_fetch_assoc($query)) {
												echo "<tr>";
												echo "<td>" . $row['name'] . "<br/><small>" . $row['empId'] . "</small></td>";
												echo "<td>" . $row['time'] . "</td>";
												echo "<td width='80px' class='text-center'><a target='_blank' href='../AttendanceImage/" . $row['photo'] . "' class='btn btn-warning'><i class='fa fa-eye'></i> VIEW</a></td>";
												if ($row['vmStatus'] == 0) {
													echo "<td class='text-center'><button class='btn btn-primary' data-toggle='modal' data-target='#verifyAttend' id='btn_" . $row['empId'] . "' onClick='attendanceModal(\"" . $row['empId'] . "\",\"" . $row['name'] . "\")'>VERIFY</button></td>";
												}
												else {
													echo "<td class='text-center'><button class='btn btn-success' disabled>Verified</button><br/><small>" . $row['vmTime'] . "</small></td>";
												}
												echo "</tr>";
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-2 border-right" style="padding:0 10px 10px 0">
							<h3 class="text-center">DAILY CLOSING<hr></h3>
							<label class="text-success">OPENING BALANCE</label>
							<input type="text" readonly class="form-control" name="open" id="open">
							<label class="text-success">FUND REQUESTED</label>
							<input type="text" name="totalamount" readonly id="totalamount" class="form-control">
							<label class="text-success">FUND RECEIVED</label>
							<input type="text" name="fundR" readonly id="fundR" class="form-control">
							<label class="text-success">FUND TRANSFERED</label>
							<input type="text" name="fundTranfer" readonly id="fundTranfer" class="form-control">
							<label class="text-success">EXPENSE</label>
							<input type="text" name="todaysExpense" readonly id="todaysExpense" class="form-control">
							<label class="text-success">TRANSACTION AMOUNT</label>
							<input type="text" name="totalTranAmount" readonly id="totalTranAmount" class="form-control" value="<?php echo $branchData['totalTransNet'] + 0; ?>">
							<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">GOLD</span>)</label>
							<input type="text" name="grossWeightG" id="grossWeightG" readonly class="form-control" value="<?php echo $branchData['GgrossW'] + 0; ?>">
							<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">SILVER</span>)</label>
							<input type="text" name="grossWeightS" id="grossWeightS" readonly class="form-control" value="<?php echo $branchData['SgrossW'] + 0; ?>">
						</div>
						<div class="col-lg-10">
							<div class="col-lg-12" style="padding:0 0px 0 10px">
								<h3 class="text-center">
									<?php 
										if($closingData['date'] == $date){
											echo "<span style='color:#990000'>Branch Closed Today</span>";
										}
										else{
											echo "Previous Denomination ( ".$closingData['date']." )";
										}
									?>
									<hr>
								</h3>
								<table class="table table-striped table-bordered table-hover">
									<tbody>
										<tr class="text-success" align="center">
											<td><b><span style="color:#990000" class="fa fa-money"></span> 2000 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 500 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 200 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 100 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 50 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 20 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 10 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 5 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 2 X</b></td>
											<td><b><span style="color:#990000" class="fa fa-money"></span> 1 X</b></td>
										</tr>
										<tr>
											<td><input type="text" value="<?php echo $closingData['one']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['two']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['three']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['four']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['five']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['six']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['seven']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['eight']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['nine']; ?>" readonly class="form-control"></td>
											<td><input type="text" value="<?php echo $closingData['ten']; ?>" readonly class="form-control"></td>
										</tr>
										<td colspan="3" align="center">
											<b class="text-success">CLOSING BALANCE</b>
											<input readonly type="text" value="<?php echo $closingData['balance']; ?>" class="form-control">
										</td>
										<td colspan="2" align="center">
											<b class="text-success">DENOMINATION TOTAL</b>
											<input type="text" readonly class="form-control" value="<?php echo $closingData['total']; ?>">
										</td>
										<td colspan="3" align="center">
											<b class="text-success">DIFFERENCE IN DENOMINATION</b>
											<input type="text" readonly class="form-control" value="<?php echo $closingData['diff']; ?>">
										</td>
										<td colspan="3" align="center">
											<b style="color:#f00" class="text-success"><?php echo $closingData['date']; ?></b>
											<input type="text" readonly class="form-control" required value="<?php echo $closingData['forward'] ?>">
										</td>
									</tbody>
								</table>
							</div>
							<div class="col-lg-12" style="padding:0 0 0 10px">
								<h3 class="text-center">Today's Denomination<hr></h3>
								<form class="text-center">
									<input type="hidden" name="branchId" id='session_branchID' value="<?php echo $branchCode; ?>">
									<table class="table table-striped table-bordered table-hover">
										<tbody>
											<tr class="text-success" align="center">
												<td><b><span style="color:#990000" class="fa fa-money"></span> 2000 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 500 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 200 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 100 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 50 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 20 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 10 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 5 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 2 X</b></td>
												<td><b><span style="color:#990000" class="fa fa-money"></span> 1 X</b></td>
											</tr>
											<tr>
												<td><input type="text" name="aa" id="aa" class="form-control" onchange=javascript:calls1(this.form);></td>
												<td><input type="text" name="cc" id="cc" class="form-control" onchange=javascript:calls3(this.form);></td>
												<td><input type="text" name="bb" id="bb" class="form-control" onchange=javascript:calls2(this.form);></td>
												<td><input type="text" name="dd" id="dd" class="form-control" onchange=javascript:calls4(this.form);></td>
												<td><input type="text" name="ee" id="ee" class="form-control" onchange=javascript:calls5(this.form);></td>
												<td><input type="text" name="jj" id="jj" class="form-control" onchange=javascript:calls10(this.form);></td>
												<td><input type="text" name="ff" id="ff" class="form-control" onchange=javascript:calls6(this.form);></td>
												<td><input type="text" name="gg" id="gg" class="form-control" onchange=javascript:calls7(this.form);></td>
												<td><input type="text" name="hh" id="hh" class="form-control" onchange=javascript:calls8(this.form);></td>
												<td><input type="text" name="ii" id="ii" class="form-control" onchange=javascript:calls9(this.form);></td>
											</tr>
											<tr>
												<td><input type="text" id="aaa" name="aaa" readonly class="form-control"></td>
												<td><input type="text" id="ccc" name="ccc" readonly class="form-control"></td>
												<td><input type="text" id="bbb" name="bbb" readonly class="form-control"></td>
												<td><input type="text" id="ddd" name="ddd" readonly class="form-control"></td>
												<td><input type="text" id="eee" name="eee" readonly class="form-control"></td>
												<td><input type="text" id="jjj" name="jjj" readonly class="form-control"></td>
												<td><input type="text" id="fff" name="fff" readonly class="form-control"></td>
												<td><input type="text" id="ggg" name="ggg" readonly class="form-control"></td>
												<td><input type="text" id="hhh" name="hhh" readonly class="form-control"></td>
												<td><input type="text" id="iii" name="iii" readonly class="form-control"></td>
											</tr>
											<tr>
												<td colspan="3">
													<b class="text-success">CLOSING BALANCE</b>
													<input readonly type="text" name="balance" id="balance" class="form-control">
												</td>
												<td colspan="2">
													<b class="text-success">DENOMINATION TOTAL</b>
													<input type="text" readonly class="form-control" name="total" id="total">
													<input type="text" readonly class="form-control" value="<?php echo $todayBalance; ?>">
												</td>
												<td colspan="3">
													<b class="text-success">DIFFERENCE IN DENOMINATION</b>
													<input type="text" readonly class="form-control" required name="diff" id="diff">
													<input type="text" readonly class="form-control" required value="<?php echo $todayDiff; ?>">
												</td>
												<td colspan="3">
													<b style="color:#f00" class="text-success">DAILY CLOSING</b>
													<input type="text" readonly class="form-control" required value="<?php echo $todayForward; ?>">
												</td>
											</tr>
										</tbody>
									</table>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	<div style="clear:both"></div>
	<script>
		$(document).ready(function() {
			if ($("#session_branchID").val()) {
				let branchId = $("#session_branchID").val();
				var req = $.ajax({
					url: "../xbalance.php",
					type: "POST",
					data: {branchId: branchId},
					dataType: 'JSON'
				});
				req.done(function(e) {
					$("#open").val(e.open);
					$("#totalamount").val(e.totalFund);
					$("#fundR").val(e.fundRec);
					$("#fundTranfer").val(e.fundTransfer);
					$("#todaysExpense").val(e.expense);
					$("#balance").val(e.balance);
				});
			}
		});
		
		function attendanceModal(id, name) {
			$(".vmTime-error").hide();
			$("#vmTime").removeClass("error");
			document.getElementById("empID").value = id;
			document.getElementById("empName").innerHTML = name;
		}
		
		function verifyAttendance() {
			$(".vmTime-error").hide();
			$("#vmTime").removeClass("error");
			var empID = document.getElementById("empID").value;
			var vmTime = document.getElementById("vmTime").value;
			var vmempID = document.getElementById("vmempID").value;
			if (vmTime == "") {
				$(".vmTime-error").show();
				$("#vmTime").addClass("error");
			} 
			else {
				$.ajax({
					url: "zvmn.php",
					type: "post",
					data: {
						verifyAttendance: "verifyAttendance",
						vmTime: vmTime,
						vmempID: vmempID,
						empID: empID
					},
					success: function(response) {
						if (response == "SUCCESS") {
							$("#verifyAttend").modal("hide");
							$("#btn_" + empID).html("VERIFIED");
							$("#btn_" + empID).prop("disabled", true);
						} 
						else {
							$(".vmTime-error").show();
							$("#vmTime").addClass("error");
						}
					}
				});
			}
		}
	</script>
	<?php include("footerNew.php"); ?>
	<script src="../vendor/fooTable/dist/footable.all.min.js"></script>
	<script>		
		$(function () {
			$('#customerFooTable').footable();		
		});		
	</script>

