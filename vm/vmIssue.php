<?php
session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
    $username = $_SESSION['username'];

if (isset($_POST['submit'])) {
	
	$sql = mysqli_fetch_assoc(mysqli_query($con,"SELECT employeeId,username FROM users WHERE employeeId='$empId'"));
	
	   if(isset($sql['username'])){
	$empId = $_POST['empId'];
	$username = $sql['username'];
	$issueType = $_POST['issueType'];
	$remarks = $_POST['remarks'];
	$anydesk=$_POST['anydesk'];
	$date = date("Ymd");
	$time = date('h:i:s');
	$branchId ="AGPL000";
	$branchName="HO";
	$priority="";
    $itname="";
	$contact="";
	$status = "Pending";
	$insertSql = "INSERT into `it_issue`(branchId,branchName,empId,username,issueType,priority,remarks,date,time,contact,anydesk,status,itname,rslvDate)
	   values('$branchId','$branchName','$empId ','$username','$issueType','$priority','$remarks','$date','$time','$contact','$anydesk','$status','$itname','$rslvDate')";
	if (mysqli_query($con, $insertSql)) {
	} else {
		echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";

		echo "<script>setTimeout(\"location.href = 'vmIssue.php'\",150);</script>";
		exit;
	}
}

}

?>
<style>
	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 20px;
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
		font-weight: 800;
		font-size: 12px;
		margin: 0px 0px 0px;
	}

	.btn-primary {
		background-color: #123C69;
	}

	thead {
		text-transform: uppercase;
		background-color: #123C69;
	}

	thead tr {
		color: #f2f2f2;
		font-size: 10px;
	}

	.btn-success {
		display: inline-block;
		padding: 0.5em 1.4em;
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

	#wrapper .panel-body {
		box-shadow: 10px 15px 15px #999;
		border: 1px solid #edf2f9;
		background-color: #f5f5f5;
		border-radius: 3px;
		padding: 20px;
	}

	.tooltip .tooltiptext {
		visibility: hidden;
		width: 120px;
		background-color: #990000;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px 0;
		position: absolute;
		z-index: 1;
		top: -5px;
		right: 105%;
	}

	.tooltip:hover .tooltiptext {
		visibility: visible;
	}

	.fa-icon-color {
		color: #990000;
	}

	.block-button {
		background-color: #123C69;
		color: red;
		border: none;
		padding: 3px 8px;
		cursor: pointer;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3><span style="color:#900" class="fa fa-ticket"> </span> IT ISSUES </h3>
				</div>
				<div class="panel-body"
					style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<form method="POST" class="form-horizontal" action="vmIssue.php" enctype="multipart/form-data">
						<div class="col-sm-3">
							<label class="text-success">EmployeeID</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900"
										class="fa fa-institution"></span></span>
								<input type="text" name="empId" value="<?php echo $empId; ?>"
									readonly class="form-control" id="branchCode">
							</div>
						</div>
						
						
						<div class="col-sm-4">
							<label class="text-success">Type of issue</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900"
										class="fa fa-bug"></span></span>
								<select class="form-control" name="issueType" id="issueType" required>
									<option selected="true" disabled="disabled" value="">SELECT TYPE</option>
									<option value="Internet Issue">Internet not working</option>
									<option value="Karat_Machine Issue">Karat Machine Issue</option>
									<option value="Printer Issue">Printer not working</option>
									<option value="Scanner Issue">Scanner not working</option>
									<option value="Web Camera Issue">Web Camera Issue</option>
									<option value="Weighing Scale Issues">Weighing Scale Issue</option>
									<option value="Counting Machine Issue">Counting Machine Issue</option>
									<option value="Excel Issue">Excel Issue</option>
									<option value="Webmail Issue">Webmail Issue</option>
									<option value="Software Not Working">Software not working</option>
									<option value="System Not Working">System not working</option>
									<option value="Computer is too slow">Computer is too slow</option>
									<option value="Keyboard / Mouse Issue">Keyboard / Mouse Issue</option>
									<option value="Hardware failures">Hardware failures</option>
									<option value="USB Port Issue">USB Port Issue</option>
									<option value="Application Issue">Application Issue [Eg: Chrome etc.]</option>
									<option value="Blue screen of death">Blue screen of death</option>
									<option value="Branch Issue">Branch Issue</option>
									<option value="Billing Issue">Billing Issue</option>
									<option value="Others">Others</option>
								</select>
							</div>
						</div>
						
							<div class="col-sm-3">
							<label class="text-success">Anydesk ID</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900"
										class="fa fa-desktop"></span></span>
								<input type="text" name="anydesk" required class="form-control" autocomplete="off" placeholder="Anydesk Number">
							</div>
						</div>
						
						<label class="col-sm-12 control-label"><br></label>
						<div class="col-sm-10">
							<label class="text-success">Describe the issue </label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900"
										class="fa fa-question-circle"></span></span>
								<textarea name="remarks" id="remarks" rows="1" placeholder="Describe the issue"
									class="form-control" required></textarea>
							</div>
						</div>
						<br><br>

						<div class="col-sm-14" style="text-align:center">
							<button class="btn btn-success" name="submit" id="submit" type="submit"
								style="margin-top:21px;"><span style="color:#ffcf40" class="fa fa-check"></span> Submit
								ticket</button>
						</div>
					</form>
				</div>
			</div>
			<div class="hpanel">
				<div class="panel-heading font-bold">
					<span style="color:#990000" class="fa fa-eye"></span> Status of Ticket
				</div>
				<div class="panel-body"
					style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th style="width: 2%;"><i class="fa fa-sort-numeric-asc"></i></th>
								<th style="width: 10%;">Type of issue</th>
								<th style="width: 6%;">Date</th>
								<!--<th style="width: 5%;">Contact No</th>-->
								<th style="width: 8%;">Anydesk ID</th>
								<th style="width: 20%;">Remarks</th>
								<th style="width: 5%;">Status</th>
								<th style="width: 6%;">Resolve date</th>
								<th style="width: 6%;">Confirm status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							$query = mysqli_query($con, "SELECT id,branchId, issueType,contact,anydesk,date, remarks, status, rslvDate FROM it_issue WHERE  empId='$empId' AND status != 'cleared' and status!='Rejected'");
							while ($row = mysqli_fetch_assoc($query)) {
								echo "<tr>";
								echo "<td>" . $i . "</td>";
								echo "<td>" . $row['issueType'] . "</td>";
								echo "<td>" . $row['date'] . "</td>";
								//echo "<td>" . $row['contact'] . "</td>";
								echo "<td>" . $row['anydesk'] . "</td>";
								echo "<td>" . $row['remarks'] . "</td>";
								echo "<td>" . $row['status'] . "</td>";
								echo "<td>" . $row['rslvDate'] . "</td>";
								echo "<td class='text-center'>";
								echo '<form method="POST" action="">';
								echo '<input type="hidden" name="issueId" value="' . $row['id'] . '">';
								if ($row['status'] == 'Done') {
									echo '<button class="block-button" type="submit" name="confirmBtn"><i class="fa fa-check fa-lg" style="color:red"></i></button>';
								}
								echo '</form>';
								echo "</td>";
								echo "</tr>";
								$i++;
							}
							if (isset($_POST['confirmBtn'])) {
								$issueId = $_POST['issueId'];
								$updateSql = "UPDATE it_issue SET status='Cleared' WHERE id='$issueId'";

								if (mysqli_query($con, $updateSql)) {
									$confirmationMessage = "Your confirmation has been updated.";
									echo '<script>alert("Your confirmation has been updated.");</script>';
									echo "<script>setTimeout(\"location.href = 'vmIssue.php';\", 150);</script>";
								} else {
									$confirmationMessage = "Error updating status: " . mysqli_error($con);
									echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		
		</div>
		<div style="clear:both"></div>
	</div>
	<?php include("footerNew.php"); ?>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script>
   $(document).ready(function(){
   window.history.replaceState("","",window.location.href)
   });
</script>