<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
    include("dbConnection.php");
	$date = date('Y-m-d');
	$branchId = $_SESSION['branchCode'];
	
	
	
	
	
	
	// Closing Disable
$closing = "SELECT date FROM `closing` WHERE date ='$date' AND branchId ='$branchId' ORDER BY `closing`.`closingID` DESC LIMIT 1";
$closingtoday = mysqli_fetch_assoc(mysqli_query($con, $closing));
$todayclosinge =$closingtoday['date'];
	
?>
<style>
	#results img{
		width:100px;
	}
	#wrapper{
		background: #f5f5f5;
	}
	
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
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
		font-weight:bold;
		font-size: 12px;
	}
	.btn-primary{
		background-color:#123C69;
	}
	.btn-info{
		background-color:#123C69;
		border-color:#123C69;
		font-size:12px;
	}	
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
		background-color:#123C69;
		border-color:#123C69;
	}
	.fa_Icon{
		color:#ffa500;
	}
	thead {
		text-transform:uppercase;
		background-color:#123C69;

	}
	thead tr{
		color: #f2f2f2;
		font-size:12px;
	}
	
	.dataTables_empty{
		text-align:center;
		font-weight:600;
		font-size:12px;
		text-transform:uppercase;
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
	#available{
		text-transform:uppercase;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
		    <?php
		 if($todayclosinge == $date){
// 			if('2022-02-19' == $date){
			?>
			<div class="col-xs-12 bg-danger mb-5" style="text-align:center; padding:70px;margin-bottom:30px;margin-top:30px;">
				 <h2 class='text-bold btn-shine' style='font-weight: bold;'>Branch is Closed <br><hr>To Reopen Call To Approval Team : 8925537846</h1>
			</div>
			<?php
			}else{
			?>
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"> <i style="color:#990000" class="fa fa-rupee"></i> Request Funds </h3>
				</div>
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<form method="POST" class="form-horizontal" action="add.php">
						<input type="hidden" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>" >
						<div class="col-sm-2">
							<label class="text-success">Available Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee" ></span></span>
								<input type="text" name="available" readonly class="form-control" id="available">
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">Requested Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="requested" class="form-control" required autocomplete="off">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Payment Type</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
								<select class="form-control" required name="type2" id="type2" onchange=type(this.form);>
									<option selected="true" disabled="disabled" value="">Select Type </option>
									<option value="By Cash">By Cash</option>
									<option value="By Cheque">Cheque</option>
									<option value="repledge">Re-pledged Commission</option>
									<option value="recovery">Recovery Amount</option>
									<option value="others">Others</option>
								</select>
							</div>
						</div>
						<div id="one">
							<div class="col-sm-2"
							><label class="text-success">Bank Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-bank"></span></span>
									<input type="text" class="form-control" name="bank" placeholder="Bank Name" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-3">
								<label class="text-success">Bank Branch</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-bank"></span></span>
									<input type="text" placeholder="Branch Name" class="form-control" name="branch" autocomplete="off">
								</div>
							</div>
						</div>
						<div id="two">
							<label class="col-lg-12"><br></label>
							<div class="col-sm-3">
								<label class="text-success">Account Number</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>
									<input type="text" placeholder="Account Number" class="form-control" name="account" autocomplete="off">
								</div>
							</div>
							<div class="col-lg-3">
								<label class="text-success">Account Holder Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-card"></span></span>
									<input type="text" class="form-control" name="holder" placeholder="Account Holder Name" autocomplete="off">
								</div>
							</div>
							<div class="col-lg-2">
								<label class="text-success">IFSC Code</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-alpha-asc"></span></span>
									<input type="text" class="form-control" name="ifsc" placeholder="IFSC Code" autocomplete="off">
								</div>
							</div>
							<div class="col-lg-2">
								<label class="text-success">Customer Contact</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
									<input type="text" class="form-control" maxlength="10" name="mobile" placeholder="Mobile Number" autocomplete="off">
								</div>
							</div>
						</div>
						<div id="three">
							<div class="col-sm-3">
								<label class="text-success">Fund Requester Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" placeholder="Requester Name" style="padding:0px 10px" class="form-control" name="chrequester" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-2">
								<label class="text-success">Cheque Number</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>
									<input type="text" style="padding:0px 9px" placeholder="Cheque Number" class="form-control" name="number" autocomplete="off">
								</div>
							</div>
							<label class="col-lg-12"><br></label>
							<div class="col-sm-3">
								<label class="text-success">Cheque Date</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
									<input type="date" class="form-control" name="date" placeholder="Date">
								</div>
							</div>
						</div>
						<div id="four">
							<div class="col-lg-2">
								<label class="text-success">Customer Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-card"></span></span>
									<input type="text" class="form-control" style="padding:0px 8px" name="requester" placeholder="Name" autocomplete="off">
								</div>
							</div>
							<div class="col-lg-2">
								<label class="text-success">Customer Contact</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
									<input type="text" class="form-control" maxlength="10" name="mob" placeholder="Contact" autocomplete="off">
								</div>
							</div>
						</div>
						<div class="col-sm-1">
							<button class="btn btn-success" name="submitFund" type="submit" style="margin-top:23px;"><span style="color:#ffcf40" class="fa fa-save"></span> Submit</button>
						</div>
					</form>
				</div>
			</div>
			<div class="hpanel">
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th><i class="fa fa-sort-numeric-asc"></i></th>
								<th>Available</th>
								<th>Request</th>
								<th>Type</th>
								<th>Customer</th>
								<th>Customer Contact</th>								
								<th>Date</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								$query=mysqli_query($con,"SELECT available,request,type,customerName,customerMobile,status FROM fund WHERE date='$date' AND branch='$branchId'");
								while($row=mysqli_fetch_array($query)){
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row['available'] . "</td>";
									echo "<td>" . $row['request'] . "</td>";
									echo "<td>" . $row['type'] . "</td>";
									echo "<td>" . $row['customerName'] . "</td>";
									echo "<td>" . $row['customerMobile'] . "</td>";
									echo "<td>" . $date . "</td>";
									echo "<td>" . $row['status'] . "</td>";
									echo "</tr>";
									$i++;
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
				}
				?>
		</div>
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>
	<script>
		$(document).ready(function(){
		    var branchId  = $("#session_branchID").val();
			var req = $.ajax({
				url:"xbalance.php",
				type:"POST",
				data:{branchId:branchId},
				dataType:'JSON'
			});
			req.done(function(e){
				$("#available").val(e.balance);
			});
		});
	</script>
