<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'Issuecall') {
		include("header.php");
		include("menuissues.php");
	}
	else if ($type == 'ZonalMaster') {
                include("header.php");
                include("menuzonalMaster.php");
        }

	else if ($type == 'Call Centre') {
		include("header.php");
		include("menuCall.php");
	}
	else if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else if ($type == 'IssueHeadNew') {
		include("header.php");
		include("menuIssueHeadNew.php");
	}
	else if ($type == 'IssueHead') {
		include("header.php");
		include("menuIssueHead.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date("Y-m-d");
	
	if(isset($_GET['mobile']) && isset($_GET['id'])){
		$enquiryData = mysqli_fetch_assoc(mysqli_query($con, "SELECT w.*, b.branchName
		FROM walkin w,branch b 
		WHERE w.id=" . $_GET['id'] . " AND w.branchId=b.branchId"));
		
		$vmData = mysqli_query($con, "SELECT idnumber, extra
		FROM everycustomer
		WHERE contact='$_GET[mobile]' AND date='$enquiryData[date]'");
	}
	
	if(isset($_GET['submitSave'])){
		$walkinId = $_GET['walkinId'];
		$comment = $_GET['comment'];
		$issue = $_GET['remarks'];
		$agent = $_GET['agent'];
		$indate = $_GET['indate'];
		
		if ($issue == 'Planning to Visit') {
			$sql = "UPDATE walkin 
			SET agent_id='$agent', followUp='$date', comment='$comment', status=1, issue='$issue', indate='$indate' 
			WHERE id='$walkinId'";
		}
		else {
			$sql = "UPDATE walkin 
			SET agent_id='$agent', followUp='$date', comment='$comment', status=1, issue='$issue' 
			WHERE id='$walkinId'";
		}
		
		if (mysqli_query($con, $sql)) {
			echo "<script>alert('ENQUIRY DONE');</script>";
			if ($type == 'VM-HO'){
				echo "<script>window.location.href='vmEnquiryReport.php';</script>";
			}
			else{
				echo "<script>window.location.href='vmEnquiryReport.php';</script>";
			}
		}
		else {
			echo "<script>alert('ERROR ADDING COMMENT!!! RESUBMIT PROPERLY AGAIN');</script>";
			if ($type == 'VM-HO'){
				echo "<script>window.location.href='vmEnquiryReport.php';</script>";
			}
			else{
				echo "<script>window.location.href='vmEnquiryReport.php';</script>";
			}
		}
	}
	
?>
<style>
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 18px;
	color: #123C69;
	}
	.btn-success,
	.btn-primary {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 12px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.btn-success:active:hover,
	.btn-success.active:hover,
	.btn-success:active.focus,
	.btn-success.active.focus,
	.btn-success:hover,
	.btn-success:focus,
	.btn-success:active,
	.btn-success.active {
	background: #285187;
	border-color: #285187;
	border: 1px solid #285187;
	color: #fffafa;
	}
	.fa_Icon {
	color: #990000;
	}
	.text-success {
	font-weight: 600;
	color: #123C69;
	text-transform: uppercase;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	table td{
	text-transform: uppercase;
	}
	.table-text-decoration{
	text-decoration: none; 
	border-bottom: dashed 1px #0088CC;
	cursor: pointer;
	}
	.table-th{
	width: 15%;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-primary">
						<h3 class="text-primary"><i class="fa_Icon fa fa-vcard"></i> Customer Enquiry </h3>
					</h3>
				</div>
				<div class="panel-body">
					<table class="table table-bordered">
						<tbody>
							<tr>
								<td class="text-success table-th">branch</td>
								<td colspan="2"><?php echo $enquiryData['branchName']; ?></td>
								<td class="text-right" style="border-top: hidden; border-right: hidden;">
									<?php if($enquiryData['quotation'] !=''){  ?>
										<a style="background-color:#123C69; color:#ffffff; padding:5px 15px 5px 15px;" target='_BLANK' href='../QuotationImage/<?php echo $enquiryData['quotation']; ?>'>Quotation <span class='fa fa-external-link' style="color:#ffcf40; margin-left: 7px;"></span>
										</a>
										<?php }else{ ?>
										No Quotation Given
									<?php } ?>
								</td>
								<td class="text-right" style="border-top: hidden; border-right: hidden;">
									<?php if($enquiryData['enquiry_image'] !=''){  ?>
										<a style="background-color:#123C69; color:#ffffff; padding:5px 15px 5px 15px;" target='_BLANK' href='../EnquiryImage/<?php echo $enquiryData['enquiry_image']; ?>'>Gold Image <span class='fa fa-external-link' style="color:#ffcf40; margin-left: 7px;"></span>
										</a>
										<?php }else{ ?>
										No Image
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="text-success table-th">Customer Name</td>
								<td><?php echo $enquiryData['name']; ?></td>
								<td class="text-success table-th">Mobile</td>
								<td><a onclick="copy(this)" id="myInput" class="table-text-decoration"><?php echo $enquiryData['mobile']; ?></a></td>
							</tr>
						</tbody>
					</table>
					<table class="table table-bordered">
						<tbody>
							<tr>
								<td class="text-success table-th">Type</td>
								<td><?php echo $enquiryData['gold']; ?></td>
								<td class="text-success table-th">Gross Weight</td>
								<td><?php echo $enquiryData['gwt']; ?></td>
								<td class="text-success" width="40%">Branch Remark</td>
							</tr>
							<tr>
								<td class="text-success table-th">Metal</td>
								<td><?php echo $enquiryData['metal']; ?></td>
								<td class="text-success table-th">Purity</td>
								<td><?php echo $enquiryData['purity']." %"; ?></td>
								<td rowspan="2"><?php echo $enquiryData['remarks']; ?></td>
							</tr>
							<tr>
								<td class="text-success table-th">Having</td>
								<td><?php echo $enquiryData['havingG']; ?></td>
								<td class="text-success table-th">Release Amount</td>
								<td><?php echo $enquiryData['ramt']; ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="col-lg-3">
			<div class="hpanel">
				<div class="panel-heading" style="padding-top: 0px;">
					<small><i class="fa_Icon fa fa-headphones"></i><b  style="color: #123C69;"> VM DETAILS</b></small>
				</div>
				<div class="panel-body">
					<?php 
						while($row = mysqli_fetch_Assoc($vmData)){
							$x = json_decode($row['extra'],true);
							echo "<table class='table table-bordered'>";
							echo "<tr><th width='30%' class='text-success'>Gross W</th><td>".$x['GrossW']."</td></tr>";
							echo "<tr><th class='text-success'>Count</th><td>".$x['itemCount']."</td></tr>";
							echo "<tr><th class='text-success'>Hallmark</th><td>".$x['Hallmark']."</td></tr>";
							echo "<tr><th class='text-success'>RelAmount</th><td>".$x['RelAmount']."</td></tr>";
							echo "<tr><th class='text-success'>RelSlips</th><td>".$x['RelSlips']."</td></tr>";
							echo "<tr><th class='text-success'>Remarks</th><td>".$row['idnumber']."</td></tr>";
							echo "</table>";
						}
					?>
				</div>
			</div>
		</div>
		
		<div class="col-lg-9">
			<div class="hpanel">
				<div class="panel-heading" style="padding-top: 0px;">
					<small><i class="fa_Icon fa fa-building"></i><b style="color: #123C69;"> HO DETAILS</b></small>
				</div>
				<div class="panel-body">
					<form method="GET" action="">
						<input type="hidden" name="walkinId" value=<?php echo $enquiryData['id']; ?>>
						<input type="hidden" name="mobile" value=<?php echo $enquiryData['mobile']; ?>>
						<table class="table table-bordered">
							<tbody>
								<tr>
									<td class="text-success table-th">Zonal Remark</td>
									<td class="text-success table-th">CSR Remark</td>
								</tr>
								<tr>
									<td><?php echo $enquiryData['zonal_remarks']; ?></td>
									<td style="padding: 0;">
										<textarea name="comment" autocomplete="off" class="form-control" rows="3"><?php echo $enquiryData['comment']; ?></textarea>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="table" style="border-top: hidden">
							<tbody>
								<tr>
									<td>
										<label class="text-success">PRESENT FOLLOW-UP BY<sup style="color: #990000">*</sup></label>
										<input type="text" name="agent" class="form-control" required>
										<p class="small text-center" style="margin-top:8px;background-color:#E3E3E3;"><?php echo $date; ?></p>
									</td>
									<td>
										<label class="text-success">REMARKS<sup style="color: #990000">*</sup></label>
										<select required class="form-control" name="remarks" id="remarks" required>
											<option selected="true" disabled="disabled" value="">SELECT REMARKS</option>
											<option value="Coming Today">Coming Today</option>
											<option value="Planning to Visit">Planning To Visit</option>
											<option value="Pending">Pending</option>
											<option value="Not Feasible">Not Feasible</option>
											<option value="Not Interested">Not Interested / Price Issue</option>
											<option value="RNR">RNR</option>
											<option value="Just Enquiry">Just Enquiry</option>
											<option value="Sold in Attica">Sold in Attica</option>
											<option value="Sold Outside">Sold Outside</option>
											<option value="Wrong Number">Wrong Number</option>
										</select>
										<input type="date" name="indate" id="indate" class="form-control" style="display:none;">
									</td>
									<td colspan="3" class="text-right">
										<?php if($type == 'Issuecall' || $type == 'Call Centre' || $type == 'IssueHead' || $type='Master'){ ?>
											<button class="btn btn-primary" name="submitSave" type="submit" style="margin-top:17px">
												<span style="color:#ffcf40" class="fa fa-check"></span> Submit
											</button>
										<?php } ?>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<script>
		function copy(that){
			var inp =document.createElement('input');
			document.body.appendChild(inp)
			inp.value =that.textContent;
			inp.select();
			document.execCommand('copy',false);
			inp.remove();
		}
	</script>
	<script>
		const disp = document.getElementById("remarks");
		const indate = document.getElementById("indate");
		disp.addEventListener("change", (e)=>{
			if(disp.value == "Planning to Visit"){
				indate.style.display = "block";
				indate.setAttribute("required", "true");
			}
			else{
				indate.style.display = "none";
				indate.removeAttribute("required");
			}
		});
	</script>
<?php include("footer.php"); ?>

