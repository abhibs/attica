<?php
	ob_start();
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Zonal') {
		include("header.php");
		include("menuZonal.php");
	}
	else if ($type == 'SubZonal') {
		include("header.php");
		include("menuSubZonal.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	
	if (isset($_POST['submitSave'])){
		$walkinId = $_POST['walkinId'];
		$zonal_remarks = $_POST['zonal_remarks'];
		
		if(isset($_POST['coming_on']) && $_POST['coming_on'] != ''){
			$coming_on = $_POST['coming_on'];
			$sql = "UPDATE walkin 
			SET zonal_remarks='$zonal_remarks', issue='Planning to Visit', indate='$coming_on'
			WHERE id='$walkinId'";
		}
		else{
			$sql = "UPDATE walkin 
			SET zonal_remarks='$zonal_remarks'
			WHERE id='$walkinId'";
		}
		
		if (mysqli_query($con, $sql)) {
			echo "<script>alert('Data updated successfully!');</script>";
			header("Location:subEnquiryWalkinReport.php");
		}
		else {
			echo "<script>alert('ERROR ADDING COMMENT!!!. RESUBMIT PROPERLY AGAIN');</script>";
			header("Location:enquiryZonalRemark.php?id=$walkinId");
		}
	}
	
	if (isset($_GET['id'])) {
		$enquiryData = mysqli_fetch_assoc(mysqli_query($con, "SELECT w.*, b.branchName
		FROM walkin w,branch b 
		WHERE w.id=" . $_GET['id'] . " AND w.branchId=b.branchId"));
		
		$vmData = mysqli_query($con, "SELECT idnumber, extra
		FROM everycustomer
		WHERE contact='$enquiryData[mobile]' AND date='$enquiryData[date]'");
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
										<a style="background-color:#123C69; color:#ffffff; padding:5px 15px 5px 15px;" target='_BLANK' href='QuotationImage/<?php echo $enquiryData['quotation']; ?>'>Quotation <span class='fa fa-external-link' style="color:#ffcf40; margin-left: 7px;"></span>
										</a>
										<?php }else{ ?>
										No Quotation Given
									<?php } ?>
								</td>
<td class="text-right" style="border-top: hidden; border-right: hidden;">
									<?php if($enquiryData['enquiry_image'] !=''){  ?>
										<a style="background-color:#123C69; color:#ffffff; padding:5px 15px 5px 15px;" target='_BLANK' href='EnquiryImage/<?php echo $enquiryData['enquiry_image']; ?>'>Gold Image <span class='fa fa-external-link' style="color:#ffcf40; margin-left: 7px;"></span>
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
								<td><a onclick="copy(this)" id="myInput" data-mobile="<?php echo  $enquiryData['mobile']; ?>" class="table-text-decoration"><?php echo  'XXXXXX'.substr($enquiryData['mobile'], 6); ?></a></td>
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
					<form method="POST" action="">
						<input type="hidden" name="walkinId" value=<?php echo $enquiryData['id']; ?>>
						<table class="table table-bordered">
							<tbody>
								<tr>
									<td class="text-success table-th">CSR Remark</td>
									<td class="text-success table-th">Zonal Remark</td>
								</tr>
								<tr>
									<td><?php echo $enquiryData['comment']; ?></td>
									<td style="padding: 0;">
										<textarea name="zonal_remarks" autocomplete="off" class="form-control" rows="3"><?php echo $enquiryData['zonal_remarks']; ?></textarea>
										<input type="date" name="coming_on" class="form-control">
									</td>
								</tr>
								<tr>
									<td colspan="3" class="text-right" style="border-bottom-style: hidden; border-right-style: hidden; border-left-style: hidden;">
										<button class="btn btn-primary" name="submitSave" type="submit" style="margin-top:15px">
											<span style="color:#ffcf40" class="fa fa-check"></span> Submit
										</button>
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
			inp.value = that.dataset.mobile;
			inp.select();
			document.execCommand('copy',false);
			inp.remove();
		}
	</script>
<?php include("footer.php"); ?>
