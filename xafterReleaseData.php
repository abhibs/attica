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
$branchId = $_SESSION['branchCode'];
$date = date('Y-m-d');

//Get Customer Data
$id = $_GET['id'];
$rowA = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM releasedata WHERE rid='$id'"));
$_SESSION['customerID'] = $rowA['customerId'];
$_SESSION['mobile'] =  $rowA['phone'];
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h2 class="text-success"><b><i style="color:#900" class="fa fa-edit"></i> Add Terminate Release Info</b></h2>
				</div>
				<div class="panel-body">
					<form method="POST" action="xaddAfterRelease.php" enctype="multipart/form-data">
						<input type="hidden" name="id" value="<?php echo $id; ?>">
						<div class="col-sm-3">
							<label class="text-success"> Released Document</label>
							<div class="input-group">
								<span class="input-group-addon" style="background:#ffcf40"><span style="color:#990000" class="fa fa-file"></span></span>
								<input type="file" name="rel" placeholder="Loan Document" required class="form-control" style="background:#ffcf40">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success"> Closing KMs</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
								<input type="text" name="ckm" placeholder="KM" class="form-control">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success"> Remarks</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
								<input type="text" name="remark" class="form-control" placeholder="Any Remarks">
							</div>
						</div>
						<div class="col-sm-2" style="margin-top:23px;">
							<button class="btn btn-primary" name="submitTerminate" type="submit">
								<span style="color:#ffcf40" class="fa fa-times"></span> Terminate
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>