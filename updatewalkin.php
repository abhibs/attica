<?php
	session_start();
	include("header.php");
	$type=$_SESSION['usertype'];
	if($type=='MT')
	{
		include("menumt.php");
	}
	else if($type=='Branch')
	{
		include("menu.php");
	}
	else
    {
	    include("logout.php");
    }
	include("dbConnection.php");
	$branchCode=$_SESSION['branchCode'];
	$walkinid=$_GET['walkinid'];
	$sql=mysqli_query($con,"select * from walkin where id =".$walkinid);
	$row = mysqli_fetch_array($sql);
?>
<div id="wrapper">
	<div class="content">
		<div class="row-content">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<h3 class="text-success"><b><i style="color:#990000" class="fa fa-rupee"></i> Customer Daily visit Report</b></h3>
					</div>
					<div class="panel-body" style="box-shadow:10px 15px 15px #999;">
						<form method="POST" class="form-horizontal" action="edit.php?walkinid=<?php echo $walkinid; ?>">
							<div class="col-sm-3"><label class="text-success">Customer Name</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="cusname" placeholder="Customer Name" readonly required id="cusname" class="form-control" value="<?php echo $row['name']; ?>">
								</div>
							</div>
							<div class="col-sm-3" hidden><label class="text-success">Contact Number</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="mob" style="padding:0px 5px" id="mob" pattern="[0-9]{10,11}" readonly placeholder="Contact Number" maxlength="11" required class="form-control" value="<?php echo $row['mobile']; ?>">
								</div>
							</div>
							
							<div class="col-sm-3">
							   
				 <label class="text-success">Branch Id</label>
			<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
				 <input type="text" class="form-control" readonly name="bran" id="bran" placeholder="Branch Id" value="<?php echo $row['branchId']; ?>" />
				 </div>
				 </div>
							<div class="col-sm-3"><label class="text-success">Alternate Number</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="amob" maxlength="11" pattern="[0-9]{10,11}" style="padding:0px 2px" placeholder="Alternate Number" id="amob" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Gold Type</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
									<select name="goldtype" id="goldtype" class="form-control">
										<option selected="true" disabled="disabled">Select Gold Type</option>
										<option value="Physical Gold">Physical Gold</option>
										<option value="Release Gold">Release Gold</option>
									</select>
								</div>
							</div>
							<div class="col-sm-2"><label class="text-success">Gross weight</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="gwt" style="padding:0px 2px" placeholder="Gross weight" id="gwt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-2"><label class="text-success">Net Weight</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="nwt" style="padding:0px 2px" placeholder="Net Weight" id="nwt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-2"><label class="text-success">purity</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="pty" style="padding:0px 2px" placeholder="purity" id="pty" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Gross Amount</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="gamt" style="padding:0px 2px" placeholder="Gross Amount" id="gamt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Net Amount</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="netamnt" style="padding:0px 2px" placeholder="Net Amount" id="netamnt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Release Amount</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="rlamnt" style="padding:0px 2px" placeholder="Release Amount" id="rlamnt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Expected Amount</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="eamt" style="padding:0px 2px" placeholder="Expected Amount" id="eamt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">Offer amount</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="oamt" style="padding:0px 2px" placeholder="Offer amount" id="oamt" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-3"><label class="text-success">issue Type</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
									<select name="issuetype" id="issuetype" class="form-control">
										<option selected="true" disabled="disabled">Select issue Type</option>
										<option value="Enquiry">Enquiry</option>
										<option value="Pending">Pending</option>
										<option value="Not satify">Not satify</option>
										<option value="price issue">price issue</option>
										
									</select>
								</div>
							</div>
							<div class="col-sm-5"><label class="text-success">Remarks</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="remarks" required style="padding:0px 2px" placeholder="Remarks" id="remarks" class="form-control" style="padding:6px 5px;">
								</div>
							</div>
							<div class="col-sm-1"><label>________________</label><br>
								<!--button class="btn btn-success" type="reset"><span class="fa fa-spinner"></span>  Reset</button--> 
								<button class="btn btn-success" name="updatewalkin" id="updatewalkin" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Update Walkin</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>
</div>