<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Branch') {
		include("header.php");
		include("menu.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$branchId = $_SESSION['branchCode'];
	$date = date('Y-m-d');
?>
<style>
	#wrapper h3{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 17px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.fa_Icon{
	color:#990000;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	font-size:11px;
	}
	.btn-success,.btn-primary{
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
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	border: none;
	}
	#wrapper .table select {
	background-color: #62cb31;
	color: white;
	border: none;
	outline: none;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Gold Release Status</h3>
				</div>
				
				<div class="panel-body" style="padding-top:20px">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Time</th>
								<th>Customer</th>
								<th>Phone</th>
								<th>Release Place</th>
								<th>Amount</th>
								<th>type</th>
								<th class="text-center" width="17%">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$queryA = mysqli_query($con, "SELECT R.rid, R.releaseID, R.BranchId, R.customerId, R.name, R.phone, R.relPlace, R.type, R.amount, R.relCash, R.relIMPS, R.status, R.date, R.time, R.flag
								FROM releasedata R 
								WHERE R.date='$date' AND R.branchId='$branchId'");
								$i = 1;
								while ($rowA = mysqli_fetch_assoc($queryA)) {
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $rowA['time'] . "</td>";
									echo "<td>" . $rowA['name'] . "</td>";
									echo "<td>" . $rowA['phone'] . "</td>";
									echo "<td>" . $rowA['relPlace'] . "</td>";
									echo "<td>" . $rowA['amount'] . "</td>";
									
									echo "<td>";
									if($rowA['type'] == "Cash"){
										echo "<span class='text-success'>CASH</span><br>";
										echo "Cash : ".$rowA['relCash'];
										
									}
									else if($rowA['type'] == "CASH/IMPS"){
										echo "<span class='text-success'>CASH/IMPS</span><br>";
										echo "Cash : ".$rowA['relCash']."</br>";
										echo "Imps : ".$rowA['relIMPS'];
									}
									echo "</td>";
									
									echo "<td class='text-center text-success' style='padding-left:20px; padding-right:20px;'>";
									if ($rowA['status'] == "Begin") {
										echo "<a class='btn btn-sm btn-block' style='background-color: #e67e22; color: #ffffff;' href='xuploadDocsRel.php?rid=".$rowA['releaseID']."&mob=".$rowA['phone']."'>Upload Docs</a>";
									}
									else if($rowA['status'] == "Terminated" || $rowA['status'] == "Billed" || $rowA['status'] == "Rejected" || $rowA['status'] == "Pending"){
										echo $rowA['status'];
									}
									else if ($rowA['status'] == "Approved") {
									?>
									<form method="POST" action="xreleaseContinue.php">
										<input type="hidden" name="rid" value="<?php echo $rowA['rid'] ?>">
										<input type="hidden" name="cid" value="<?php echo $rowA['customerId'] ?>">
										<input type="hidden" name="phone" value="<?php echo $rowA['phone'] ?>">
										<div class="input-group">
											<select class="form-control input-sm" name="forward" required>
												<option selected="true" disabled="disabled" value="">PROCEED TO</option>
												<option value="contB">CONTINUE TO BILLING</option>
												<option value="terminate">TERMINATE (GOLD RELEASED / 5% COMMISSION)</option>
												<!-- <option value="noRelease">NOT RELEASED</option> -->
											</select>	
											<span class="input-group-btn"> 
												<button class="btn btn-sm" style='background-color: #62cb31; color: #ffffff;'  name="relForwardSubmit" type="submit"><i style="color:#ffcf40" class="fa fa-check"></i></button>	
											</span>
										</div>
									</form>
									<?php
									}
									if ($rowA['status'] == "Processing") {
										if ($rowA['flag'] == "0") {
											echo "<button class='btn btn-sm btn-block' style='background-color: #9b59b6; color: #ffffff;' type='button' onClick='releaseDone(this)' data-rid='".$rowA['rid']."'>RELEASE DONE</button>";
										}
										else{
											echo "Processing";
										}
									}
									echo "</td>";
									
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
	<?php include("footer.php"); ?>
	<script>
		function releaseDone(button){
			const rid = button.dataset.rid;
			const tr = button.parentElement;
			var req = $.ajax({
				url: "xaddAfterRelease.php",
				type: "POST",
				data: {
					submitReleaseDone: true,
					rid: rid
				}
			});
			req.done(function(e) {
				if(e == 1){
					tr.innerHTML = "Processing";
				}
				else{
					alert("Please try again !!!");
				}
			});
		}
	</script>
