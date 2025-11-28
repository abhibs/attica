<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	} 
	else if ($type == 'Accounts') {
		include("header.php");
		include("menuacc.php");
	}
	else if ($type == 'Accounts IMPS') {
		include("header.php");
		include("menuimpsAcc.php");
	} 
	else if ($type == 'ApprovalTeam') {
		include("header.php");
		include("menuapproval.php");
	} 
	else if ($type == 'SubZonal') {
		include("header.php");
		include("menuSubZonal.php");
	}
        else if ($type == 'SundayUser') {
                include("header.php");
                include("menuSundayUser.php");
	}
	else if ($type == 'ZonalMaster') {
		include("header.php");
		include("menuzonalMaster.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	
	//RELEASE DATA
	$id = "";
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		
		if (isset($_GET['status'])) {
			$sqlStatus = mysqli_query($con, "UPDATE releasedata SET status='Processing' WHERE rid='$id'");
		}
		
		$releasedata = mysqli_fetch_assoc(mysqli_query($con, "SELECT B.branchName AS branchN,R.* 
		FROM releasedata R,branch B 
		WHERE R.rid='$id' AND R.BranchId=B.branchId"));
		
		$bmData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact
		FROM employee 
		where empId=(select employeeId from users where branch='$releasedata[BranchId]' AND type='Branch')"));
		
		$teData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, contact, designation
		FROM employee 
		WHERE empId='$releasedata[TEempId]'"));
	}
	
?>
<style>
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	#wrapper h4 {
	text-transform: uppercase;
	font-weight: 900;
	font-size: 11px;
	color: #123C69;
	padding-bottom: 0px;
	margin-bottom: 0px;
	margin-top: 0px;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
	background-color: #fffafa;
	}
	.text-success {
	color: #123C69;
	text-transform: uppercase;
	font-weight: bold;
	font-size: 12px;
	}
	.btn-primary {
	background-color: #123C69;
	}
	.btn-success {
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
	.fa_Icon {
	color: #990000;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<input type="hidden" id='session_branchID' value="<?php echo $billData['branchId']; ?>" >
					<div class="pull-right">
						<a class="btn btn-default a-extra"><span class="fa fa-bank fa_Icon"></span> | <?php echo $releasedata['branchN']; ?></a>
						<a class="btn btn-default a-extra"><span class="fa fa-user fa_Icon"></span> | <?php echo $bmData['name']; ?></a>
						<a class="btn btn-default a-extra"><span class="fa fa-phone fa_Icon"></span> | <?php echo $bmData['contact']; ?></a>
						<a class="btn btn-default a-extra"><span class="fa fa-money fa_Icon"></span> | <i id="available"></i></a>
					</div>
					<h3><i style="color:#990000" class="fa fa-shield"></i> GOLD RELEASE INFO</h3>
				</div>
			</div>
		</div>
		
		<form method="POST" action="xapproveRejectR.php">
			<input type="hidden" name="id" value="<?php echo $releasedata['rid']; ?>">
			
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<h4><span class="fa fa-id-card-o fa_Icon"></span> Customer Details</h4>
					</div>
					<div class="panel-body">
						<div class="form-group col-lg-12">
							<label class="text-success">Customer Name</label>
							<input type="text" class="form-control" value="<?php echo $releasedata['name']; ?>" readonly>
						</div>
						<div class="form-group col-lg-12">
							<label class="text-success">Contact</label>
							<input type="text" class="form-control" value="<?php echo $releasedata['phone']; ?>" readonly>
						</div>
						<div class="form-group col-lg-12">
							<label class="text-success">Release Place</label>
							<input type="text" class="form-control" value="<?php echo $releasedata['relPlace']; ?>" readonly>
						</div>
						
						<?php if ($releasedata['type'] == 'Cash') { ?>
							<div class="form-group col-lg-12">
								<label class="text-success">Release Amount</label>
								<input type="text" class="form-control" value="<?php echo $releasedata['amount']; ?>" readonly style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);">
							</div>
							<?php } else if ($releasedata['type'] == 'CASH/IMPS') { ?>
							<div class="form-group col-lg-12">
								<label class="text-success">Total Amount</label>
								<input type="text" name="totalAmount" id="totalAmount" class="form-control" value="<?php echo $releasedata['amount']; ?>" readonly>
							</div>
							<div class="form-group col-lg-6">
								<label class="text-success">IMPS Amount</label>
								<input type="text" name="impsA" id="impsA" class="form-control" value="<?php echo $releasedata['relIMPS']; ?>" style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);">
							</div>
							<div class="form-group col-lg-6">
								<label class="text-success">Cash Amount</label>
								<input type="text" name="cashA" id="cashA" class="form-control" value="<?php echo $releasedata['relCash']; ?>" style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);">
							</div>
						<?php } ?>
						
						<div class="col-sm-12">
							<label class="text-success">Release Documents</label>
							<div class="input-group">
								<h4>
									<?php if ($releasedata['relDoc1'] != '') { ?>
										<a style='background-color: #123C69; color: #ffffff' class='btn btn-sm' target='_blank' href="<?php echo 'ReleaseDocuments/' . $releasedata['relDoc1']; ?>"><span style="color:#ffcf40" class="fa fa-file-text-o"></span> Doc 1</a>
										<?php } else echo "N/A";
										if ($releasedata['relDoc2'] != '') { ?>
										<a style='background-color: #123C69; color: #ffffff' class='btn btn-sm' target='_blank' href="<?php echo 'ReleaseDocuments/' . $releasedata['relDoc2']; ?>"><span style="color:#ffcf40" class="fa fa-file-text-o"></span> Doc 2</a>
									<?php } else echo "N/A"; ?>
								</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<?php if ($releasedata['type'] == 'CASH/IMPS') { ?>
				<div class="col-lg-4">
					<div class="hpanel">
						<div class="panel-heading">
							<h4><span class="fa fa-bank fa_Icon"></span> IMPS Details</h4>
						</div>
						<div class="panel-body">
							<?php if ($releasedata['relWith'] == 'NBFC') {  ?>
								<div class="form-group col-sm-12">
									<label class="text-success">No of Pledges</label>
									<input type="text" class="form-control" value="<?php echo $releasedata['pledgeSlips']; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success">Cheque Leaf Proof</label>
									<div class="input-group">
										<?php if ($releasedata['cProof'] != '') { ?>
											<h4><a style='background-color: #123C69; color: #ffffff' class='btn btn-sm' target='_blank' href="<?php echo 'ReleaseDocuments/' . $releasedata['cProof']; ?>"> <span style="color:#ffcf40" class="fa fa-file-text-o"></span> Cheque Leaf</a></h4>
											<?php } else {
												echo "N/A";
											} ?>
									</div>
								</div>
								<?php } else if ($releasedata['relWith'] == 'BANK') { ?>
								<div class="form-group col-sm-12">
									<label class="text-success">Bank (Branch) </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['bankName']."  (".$releasedata['branchName'].")"; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success"> Account Holder Name </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['accountHolder']; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success"> Relationship </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['relationship']; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success"> Loan Account Number </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['loanAccNo']; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success"> Account NO </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['accountNo']; ?>" readonly>
								</div>
								<div class="form-group col-sm-12">
									<label class="text-success"> IFSC NO </label>
									<input type="text" class="form-control" value="<?php echo $releasedata['IFSC']; ?>" readonly>
								</div>
								<div class="col-sm-12">
									<label class="text-success">Bank Account Proof</label>
									<div class="input-group">
										<?php if ($releasedata['bProof'] != '') { ?>
											<h4><a style='background-color: #123C69; color: #ffffff' class='btn btn-sm' target='_blank' href="<?php echo 'ReleaseDocuments/' . $releasedata['bProof']; ?>"> <span style="color:#ffcf40" class="fa fa-file-text-o"></span> Bank Proof</a></h4>
											<?php } else {
												echo "N/A";
											} ?>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<h4><span class="fa fa-male fa_Icon"></span> TE Details</h4>
					</div>
					<div class="panel-body">
						<div class="form-group col-lg-12">
							<label class="text-success">TE Name</label>
							<input type="text" class="form-control" value="<?php echo $teData['name']; ?> " readonly>
						</div>
						<div class="form-group col-lg-12">
							<label class="text-success">TE Contact</label>
							<input type="text" class="form-control" value="<?php echo $teData['contact']; ?> " readonly>
						</div>
						<div class="form-group col-lg-12">
							<label class="text-success">Designation</label>
							<input type="text" class="form-control" value="<?php echo $teData['designation']; ?> " readonly>
						</div>
						<div class="col-sm-12">
							<label class="text-success">TE Reached</label>
							<span style="padding-left: 16px;"><input value="Reached" type="checkbox" id="teReached"></span>
						</div>
					</div>
				</div>
			</div>
			
			<?php if(($releasedata['status'] == 'Pending' || $releasedata['status'] == 'Processing') && ($type=='ApprovalTeam' || $type=='Master' || $type=='Accounts IMPS' || $type=='SubZonal' || $type=='SundayUser' || $type=='ZonalMaster')) { ?>
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-body" onkeydown="return event.key != 'Enter';">
							<div class="col-sm-2" style="margin-top:23px">
								<button class="btn btn-sm btn-outline btn-primary2 font-extra-bold" type="button" data-relid="<?php echo $releasedata['rid']; ?>" onclick="updateReleaseUpload(this)">
									Re-upload Documents
								</button>
							</div>
							<div class="col-sm-6">
								<label class="text-success"> Remarks</label>
								<input type="textarea" name="remark" class="form-control" placeholder="Enter your Remarks">
							</div>
							<?php if ($releasedata['type'] == 'Cash') { ?>
								<div class="col-sm-2 text-right" style="margin-top:23px">
									<button class="btn btn-success" name="submitApproveCash" type="submit" id="approve">
										<span style="color:#ffcf40" class="fa fa-check"></span> Approve
									</button>
								</div>
								<div class="col-sm-2" style="margin-top:23px">
									<button class="btn btn-danger" name="submitRejectCash" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> Reject
									</button>
								</div>
								<?php } else if ($releasedata['type'] == 'CASH/IMPS') { ?>
								<div class="col-sm-2 text-right" style="margin-top:23px">
									<button class="btn btn-success" name="submitApproveIMPS" type="submit" id="approve">
										<span style="color:#ffcf40" class="fa fa-check"></span> Approve
									</button>
								</div>
								<div class="col-sm-2" style="margin-top:23px">
									<button class="btn btn-danger" name="submitRejectIMPS" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> Reject
									</button>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
			
		</form>
		
	</div>
	<?php if ($releasedata['type'] == 'CASH/IMPS') { ?>
		<script>	
			$(document).ready(function() {
				const totalAmount = document.getElementById("totalAmount");
				const cashA = document.getElementById("cashA");
				const impsA = document.getElementById("impsA");
				impsA.addEventListener('change', (event)=>{
					let cash = cashA.value;
					let imps = impsA.value;
					totalAmount.value = +cash + +imps;
				});
				cashA.addEventListener('change', (event)=>{
					let cash = cashA.value;
					let imps = impsA.value;
					totalAmount.value = +cash + +imps;
				});
			});
		</script>
	<?php } ?>
	<script>
		$(document).ready(function() {
			$('#approve').attr("disabled", true);
			$('#teReached').change(function() {
				if (this.checked) {
					$('#approve').attr("disabled", false);
					} else {
					$('#approve').attr("disabled", true);
				}
			});
		});
	</script>
	<script>
		$(document).ready(function() {
			var branchId = '<?php echo $releasedata['BranchId']; ?>';
			var req = $.ajax({
				url: "xbalance.php",
				type: "POST",
				data: {
					branchId: branchId
				},
				dataType: 'JSON'
			});
			req.done(function(e) {
				$("#available").text(e.balance);
			});
		});
	</script>
	<script>
		async function updateReleaseUpload(btn){
			const conf = confirm("Are you sure, you want BM to reupload the documents ??");
			if(conf){
				const relid = btn.dataset.relid;
				
				const form = new FormData();
				form.append("relid", relid);
				form.append("updateReleaseUpload", true);
				
				const response =  await fetch("editAjax.php", {
					method: "POST",
					body: form
				});
				const result = await response.json();
				
				if(result['message'] == "success"){
					alert("BM can Reupload the documents");
					window.location = "xapproveRelease.php";					
				}
				else{
					console.log(result);
					alert("Something went wrong, please try again later");
					return;
				}					
			}
		}
	</script>
<?php include("footer.php"); ?>		
