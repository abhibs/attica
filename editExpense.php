<?php
	session_start();
	$type=$_SESSION['usertype'];	
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
		}
	else if($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
	}
	else if($type=='Accounts IMPS'){
		include("header.php");
		include("menuimpsAcc.php");
	}
	else if($type=='Expense Team'){
		include("header.php");
		include("menuexpense.php");
	}
	else if($type=='AccHead'){
		include("header.php");
	    include("menuaccHeadPage.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$expData = mysqli_fetch_assoc(mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.branchCode=branch.branchId AND expense.id='$id'"));
	}
	
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><b><i class="fa fa-edit"></i> Edit Expense </b></h3>
				</div>
				<div class="panel-body" style="box-shadow:0px 0px 25px #333">
					<form method="POST" action='expUpdate.php' enctype="multipart/form-data" onSubmit="return confirm('Do you want to Update?')">
						<input type="hidden" name="id" value="<?php echo $expData['id'] ?>" >
						<table class="table table-user-information">
							<tbody>
								<tr>
									<th><h4 class="text-success"><b>Branch</b></h4></th>
									<th><h4 class="text-success"><b>Expense Type</b></h4></th>
									<th><h4 class="text-success"><b>Particular</b></h4></th>
									<th><h4 class="text-success"><b>Requested amount</b></h4></th>
								</tr>
								<tr>
									<td><?php echo $expData['branchName'] ;?></td>
									<td><?php echo $expData['type'] ;?></td>
									<td><?php echo $expData['particular'] ;?></td>
									<td><input type="text" name="amount"  id="amount" class="form-control" value="<?php echo $expData['amount']; ?>"></td>
								</tr>
								<tr>
									<td colspan="3">
										<div class="col-sm-12"><label class="text-success">Remarks</label>
											<div class="input-group"><span class="input-group-addon"><span class="fa fa-edit"></span></span>
												<input type="text" name="remarks"  id="remarks" class="form-control" placeholder="Any Remarks">
											</div>
										</div>
									</td>
									<td style="text-align:center;">
										<button class="btn btn-success" name="editExpense" style="margin-top:23px;"><span style="color:#ffcf40" class="fa fa-save"></span> Edit Expense </button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php");?>