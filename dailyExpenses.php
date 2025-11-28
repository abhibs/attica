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

	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-weight:bold;
		font-size: 12px;
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
					<h3><span style="color:#900" class="fa fa-money"></span> Branch Daily Expenses</h3>
				</div>
				<div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<form method="POST" class="form-horizontal" action="add.php" enctype="multipart/form-data">
						<div class="col-sm-4">
							<label class="text-success">Expense Type</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-rupee"></span></span>
								<select class="form-control" name="expense" id="expense" required>
									<option selected="true" disabled="disabled" value="">SELECT TYPE</option>
									<option value="Daily Expenses">Daily Expenses</option>
									<option value="Petrol Expenses">Petrol Expenses</option>
									<option value="Pooja Expenses">Pooja Expenses</option>
									<option value="Office Rent">Office Rent</option>
									<option value="Travelling Expenses">Travelling Expenses</option>
									<option value="Electricity Bill">Electricity Bill</option>
									<option value="Internet Bill">Internet Bill</option>
									<option value="Telephone Bill">Telephone Bill</option>
									<option value="Water Bill">Water Bill</option>
									<option value="Salary">Salary</option>
									<option value="Salary Advance">Salary Advance</option>
									<option value="Incentives">Incentives</option>
									<option value="Advertisement Expenses">Advertisement Expenses</option>
									<option value="Courier Expenses">Courier Expenses</option>
									<option value="Office Maintenance">Office Maintenance</option>
									<option value="Legal Expense">Legal Expense</option>
									<option value="MD Sir Payment">MD Sir Payment</option>
									<option value="Stationary Expense">Stationary Expense</option>
									<option value="IT Expense">IT Expense</option>
									<option value="Others">Others</option>
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<label class="text-success">Particulars</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-edit"></span></span>
								<input type="text" name="particular" id="particular" class="form-control" required placeholder="Particulars" autocomplete="off">
							</div>
						</div>
						<div class="col-sm-4">
							<label class="text-success">Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-money"></span></span>
								<input type="text" placeholder="Expenses" pattern="[0-9]{1,7}" maxlength="7" title="enter only Numbers" class="form-control" required name="amount" id="amount" autocomplete="off">
							</div>
						</div>
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-4">
							<label class="text-success">Upload Bill</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-file"></span></span>
								<input type="file" style="background:#ffcf40" class="form-control" required name="file" id="file">
							</div>
						</div>
						<br><br>
						<div class="col-sm-4">
							<label class="text-success">Upload Second Bill</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-file"></span></span>
								<input type="file" style="background:#ffcf40" class="form-control" name="file1" id="file1">
							</div>
						</div>
						<div class="col-sm-4" style="text-align:center">
							<button class="btn btn-success" name="submitExpenses" id="submitExpenses" type="submit" style="margin-top:23px;"><span style="color:#ffcf40" class="fa fa-check"></span> Submit</button>
						</div>
					</form>
				</div>
			</div>
			<div class="hpanel">
				<div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th><i class="fa fa-sort-numeric-asc"></i></th>
								<th>Particular</th>
								<th>Type</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Remarks</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								$query=mysqli_query($con,"SELECT particular,type,amount,status,remarks FROM expense WHERE date='$date' AND branchCode='$branchId'");
								while($row = mysqli_fetch_assoc($query)){
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row['particular'] . "</td>";
									echo "<td>" . $row['type'] . "</td>";
									echo "<td>" . $row['amount'] . "</td>";
									echo "<td>" . $row['status'] . "</td>";
									echo "<td>" . $row['remarks'] . "</td>";
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
	<script>
		$(document).ready(function(){
			$("#file").change(function(){
				var fileInput = document.getElementById('file');              
				var filePath = fileInput.value;			  
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}
				var FileSize = file.files[0].size / 1024 / 1024; // in MB
				if (FileSize > .25){
					alert('File size exceeds 250kb');
					$(file).val(''); //for clearing with Jquery
				}
			});
			$("#file1").change(function(){
				var fileInput = document.getElementById('file1');              
				var filePath = fileInput.value;			  
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}				
				var FileSize = file1.files[0].size / 1024 / 1024; // in MB
				if (FileSize > .25){
					alert('File size exceeds 250kb');
					$(file1).val(''); //for clearing with Jquery
				}
			});
		});
	</script>
<?php include("footer.php");?>