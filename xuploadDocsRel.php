<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Branch') {
	include("header.php");
	include("menu.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
//include('libs/phpqrcode/qrlib.php');
//$tempDir = 'temp/';
$date = date('Y-m-d');
$branch = $_SESSION['branchCode'];

/*  CUSTOMER TRANSACTION DETAILS  */
if (isset($_GET['rid']) && isset($_GET['mob'])) {
	$relId = $_GET['rid'];
	$mobile = $_GET['mob'];
	$billData = mysqli_fetch_array(mysqli_query($con, "SELECT  r.rid,r.releaseID,r.customerId,r.name,r.phone,r.relPlaceType,r.relPlace,r.relWith,r.amount,r.type,c.idProof,c.addProof
		FROM releasedata r,customer c 
		WHERE r.releaseID='$relId' AND r.phone='$mobile' AND r.date='$date' AND r.phone=c.mobile"));
}
?>
<style>
	#results img {
		width: 100px;
	}

	#results1 img {
		width: 100px;
	}

	#results2 img {
		width: 100px;
	}

	#results3 img {
		width: 100px;
	}

	#results4 img {
		width: 100px;
	}

	#results5 img {
		width: 100px;
	}

	#wrapper {
		background: #f5f5f5;
	}

	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 16px;
		color: #123C69;
	}

	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
		background-color: #fffafa;
	}

	.quotation h3 {
		color: #123C69;
		font-size: 18px !important;
	}

	.text-success {
		color: #123C69;
		text-transform: uppercase;
		font-weight: 600;
		font-size: 12px;
	}

	.btn-primary {
		background-color: #123C69;
	}

	.btn-info {
		background-color: #123C69;
		border-color: #123C69;
		font-size: 12px;
	}

	.btn-info:hover,
	.btn-info:focus,
	.btn-info:active,
	.btn-info.active {
		background-color: #123C69;
		border-color: #123C69;
	}

	.fa_Icon {
		color: #ffa500;
	}

	thead {
		text-transform: uppercase;
		background-color: #123C69;
	}

	thead tr {
		color: #f2f2f2;
		font-size: 12px;
	}

	.dataTables_empty {
		text-align: center;
		font-weight: 600;
		font-size: 12px;
	}

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

	.modaldesign {
		float: right;
		cursor: pointer;
		padding: 5px;
		background: none;
		color: #f0f8ff;
		border-radius: 5px;
		margin: 15px;
		font-size: 20px;
	}

	#available {
		text-transform: uppercase;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"> &nbsp; <b><span style="color:#900" class="fa fa-cloud-upload "></span> Upload Documents </b></h3>
					<input type="hidden" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>">
					<input type="hidden" id='billRow' value="<?php echo $billData['rid']; ?>">
				</div>
				<div style="clear:both"></div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<div class="col-sm-3">
						<label class="text-success">Customer Name</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
							<input type="text" name="name" class="form-control" style="padding:0px 5px" value="<?php echo $billData['name']; ?>" readonly>
						</div>
					</div>
					<div class="col-sm-2">
						<label class="text-success">Mobile</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
							<input type="text" name="phone" id="custPhone" class="form-control" style="padding:0px 5px" value="<?php echo $billData['phone']; ?>" readonly>
						</div>
					</div>
					<div class="col-sm-2">
						<label class="text-success">Type</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
							<input type="text" name="phone" class="form-control" style="padding:0px 5px" value="<?php echo $billData['relPlaceType']; ?>" readonly>
						</div>
					</div>
					<?php if ($billData['type'] == 'CASH/IMPS') { ?>
						<div class="col-sm-2">
							<label class="text-success">Transfer To</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
								<input type="text" name="phone" class="form-control" style="padding:0px 5px" value="<?php echo $billData['relWith']; ?>" readonly>
							</div>
						</div>
					<?php } ?>
					<div class="col-sm-3">
						<label class="text-success">Amount Paid</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
							<input type="text" name="phone" class="form-control" style="padding:0px 5px" value="<?php echo $billData['amount']; ?>" readonly>
						</div>
					</div>

					<div class="col-lg-12"><br></div>


					<!--  VIA COMPUTER  -->
					<div class="col-lg-12" id="viaComputerDiv">
						<div style="background:#f5f5f5;border-radius:5px" class="panel-body">
							<div class="panel-heading text-success" style="color:#990000">
								<h3 align="center"><b>UPLOAD VIA COMPUTER</b></h3>
								<hr>
							</div>
							<form method="POST" action="xallDocUpload.php" enctype="multipart/form-data">
								<input type="hidden" name='billRow' value="<?php echo $billData['rid']; ?>">
								<input type="hidden" name='phone' value="<?php echo $billData['phone']; ?>">
								<input type="hidden" name='cid' value="<?php echo $billData['customerId']; ?>">
								<input type="hidden" name='type' value="<?php echo $billData['relWith']; ?>">
								<div class="col-lg-12">
									<div class="col-md-4" align="center">
										<label class="text-success">ID Proof</label>
										<div class="input-group">
											<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
											<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="idFile" id="idFile" required>
										</div>
									</div>
									<div class="col-md-4" align="center">
										<label class="text-success">Address Proof</label>
										<div class="input-group">
											<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
											<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="addFile" id="addFile" required>
										</div>
									</div>
									<div class="col-md-4" align="center">
										<label class="text-success"> Release Doc</label>
										<div class="input-group">
											<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
											<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="reldoc" id="reldoc" required>
										</div>
									</div>
									<?php if ($billData['relWith'] == 'BANK') { ?>
										<div class="col-md-4" align="center" style="margin-top: 20px;">
											<label class="text-success">Customer Bank Proof</label>
											<div class="input-group">
												<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
												<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="bankproof" id="bankproof" required>
											</div>
										</div>
									<?php } ?>
									<?php if ($billData['relWith'] == 'NBFC') { ?>
										<div class="col-md-4" align="center" style="margin-top: 20px;">
											<label class="text-success"> NBFC Doc</label>
											<div class="input-group">
												<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
												<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="nbfcproof" id="nbfcproof" required>
											</div>
										</div>
									<?php } ?>
								</div>
						
								<div class="col-lg-12">
									<div class="col-xs-6" style="margin-top: 50px;" align="center">
										<label class="text-success">Capture Signature</label>
										<div id="results3" style="position:absolute;"></div>
										<div id="my_camera3"></div>
										<input type="hidden" name="image3" required class="image-tag3">
										<a onClick="take_snapshot4()" class="btn btn-primary"><i class="fa fa-camera" style="color:#ffcf40"></i> Capture Signature</a>
									</div>
								<!--	<div class="col-xs-6" style="margin-top: 50px;" align="center">
										<label class="text-success"> Capture Thumb Impression</label>
										<div id="results4" style="position:absolute;"></div>
										<div id="my_camera4"></div>
										<input type="hidden" name="image4" required class="image-tag4">
										<a onClick="take_snapshot5()" class="btn btn-primary"><i class="fa fa-camera" style="color:#ffcf40"></i> Capture Thumb Impression</a>
									</div>-->
								</div>
							
								<div class="col-sm-12">
									<hr>
								</div>
								<div class="col-sm-12" align='right'>
									<button class="btn btn-primary" name="uploadViaComputerRel" onclick="setTimeout(() => {this.disabled = true;}, 5000);"  type="submit">
										<span style="color:#ffcf40" class="fa fa-upload"></span> Upload Docs
									</button>
								</div>
							</form>
						</div>
					</div>



				</div>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
	<script type="text/javascript">

		// <!--  GET UPLOADED DOCS  -->
		$(document).ready(function() {
			let rid = $("#billRow").val();
			let phone = $("#phone").val();
			$('#displayDocs').click(function() {
				var req = $.ajax({
					url: "xgetUploadedDocsRel.php",
					type: "POST",
					data: {
						rid: rid,
						mobile: phone
					},
					dataType: 'JSON'
				});
				req.done(function(response) {
					// ID PROOF
					if (response.idFile != 0) {
						$("#idProofDiv").html("<a href='CustomerDocuments/" + response.idFile + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> ID PROOF</b></a>");
					} else {
						$("#idProofDiv").html("<b style='color:red'>File Not Uploaded</b>");
					}
					// ADDRESS PROOF
					if (response.addFile != 0) {
						$("#addProofDiv").html("<a href='CustomerDocuments/" + response.addFile + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> ADDRESS PROOF</b></a>");
					} else {
						$("#addProofDiv").html("<b style='color:red'>File Not Uploaded</b>");
					}


					// Signature PROOF
					if (response.custSign != 0) {
						$("#signature").html("<a href='CustomerSignature/" + response.custSign + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> Signature</b></a>");
					} else {
						$("#signature").html("<b style='color:red'>File Not Uploaded</b>");
					}

					// Thumb PROOF
				/*	if (response.cusThump != 0) {
						$("#thumb").html("<a href='CustomerThumb/" + response.cusThump + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> Thumb Print</b></a>");
					} else {
						$("#thumb").html("<b style='color:red'>File Not Uploaded</b>");
					}*/
					// BANK PROOF
					if (response.relDoc1 != 0) {
						$("#releaseProofDiv").html("<a href='ReleaseDocuments/" + response.relDoc1 + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> Release Doc</b></a>");
					} else {
						$("#releaseProofDiv").html("<b style='color:red'>File Not Uploaded</b>");
					}
					// plslip PROOF
					if (response.cProof != 0) {
						$("#plslip").html("<a href='ReleaseDocuments/" + response.cProof + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> Pledge Slip</b></a>");
					} else {
						$("#plslip").html("<b style='color:red'>File Not Uploaded</b>");
					}
					// bProof PROOF
					if (response.bProof != 0) {
						$("#bankProofDiv").html("<a href='ReleaseDocuments/" + response.bProof + "' target='_blank'><b><i class='fa fa-file-text' style='font-size:40px;color:#990000'></i><br> <i class='fa fa-eye'></i> Bank Details</b></a>");
					} else {
						$("#bankProofDiv").html("<b style='color:red'>File Not Uploaded</b>");
					}
				});
			});
		});
	</script>
	<script>
		// <!--  UPLOAD FILE SIZE RESTRICTION  -->
		$(document).ready(function() {

			// ID PROOF
			$("#idFile").change(function() {
			   var fileInput = document.getElementById('idFile');              
				var filePath = fileInput.value;
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}
				var FileSize = idFile.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#idFile").val('');
				}
			});

			// ADDRESS PROOF
			$("#addFile").change(function() {
			   var fileInput = document.getElementById('addFile');              
				var filePath = fileInput.value;          
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}
				var FileSize = addFile.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#addFile").val('');
				}
			});

			// RELEASE DOCUMENT
			$("#reldoc").change(function() {
				var fileInput = document.getElementById('reldoc');              
				var filePath = fileInput.value;          
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}			    
				var FileSize = reldoc.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#reldoc").val('');
				}
			});

			// NBFC PROOF
			$("#nbfcproof").change(function() {
			    var fileInput = document.getElementById('nbfcproof');              
				var filePath = fileInput.value;          
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}
				var FileSize = nbfcproof.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#nbfcproof").val('');
				}
			});

			// WITH BANK PROOF
			$("#bankproof").change(function() {
			    var fileInput = document.getElementById('bankproof');              
				var filePath = fileInput.value;  
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}
				var FileSize = bankproof.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#bankproof").val('');
				}
			});

		});
	</script>
	
	<?php include("footer.php"); ?>
	<script src="scripts/webcam.min.js"></script>
	<script language="JavaScript">
		Webcam.set({
			width: 280,
			height: 300,
			image_format: 'jpeg',
			jpeg_quality: 100
		});

		Webcam.attach('#my_camera3');
		Webcam.attach('#my_camera4');

		function take_snapshot4() {
			Webcam.snap(function(data_uri3) {
				$(".image-tag3").val(data_uri3);
				document.getElementById('results3').innerHTML = '<img src="' + data_uri3 + '" />';
			});
		}

		function take_snapshot5() {
			Webcam.snap(function(data_uri4) {
				$(".image-tag4").val(data_uri4);
				document.getElementById('results4').innerHTML = '<img src="' + data_uri4 + '"/>';
			});
		}
	</script>

