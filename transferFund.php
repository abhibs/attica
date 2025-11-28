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
	
	// BRANCH NAME & CLOSING DATE
	$closing = mysqli_fetch_assoc(mysqli_query($con, "SELECT date FROM closing WHERE date='$date' AND branchId='$branchId' ORDER BY closingID DESC LIMIT 1"));
	
	// ALL BRANCHES NAME
	$branch = [];
	$branchSQL = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
	while($row = mysqli_fetch_assoc($branchSQL)){
		$branch[$row['branchId']] = $row['branchName'];
	}
	
	// TRANSFERRED AMOUNT
	$transferred = mysqli_query($con, "SELECT transferAmount,branchTo,status,time 
	FROM trare 
	WHERE date='$date' AND branchId='$branchId'");
	
	// RECEIVED AMOUNT
	$rec = mysqli_query($con, "SELECT transferAmount,branchId,status,time
	FROM trare 
	WHERE date='$date' AND branchTo='$branch[$branchId]'");
	
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
	border-radius:10px;
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
	thead {
	text-transform:uppercase;
	background-color:#123C69;	
	}
	thead tr{
	color: #f2f2f2;
	font-size:10px;
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
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#900" class="fa fa-money"></i> Transfer Funds</h3>
				</div>
				<?php if($closing['date'] == $date){ ?>
					<div class="col-lg-12">
						<div class="panel-body" style="margin-bottom: 10px;">
							<h4 class='text-center'>BRANCH IS CLOSED | TO REOPEN CALL TO APPROVAL TEAM : 8925537846</h4>
						</div>
					</div>
					<?php }else{ ?>
					<div class="col-lg-12">
						<div class="panel-body" style="margin-bottom: 10px;">
							<input type="hidden" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>" >
							<form method="POST" class="form-horizontal" action="add.php">
								<div class="col-sm-3">
									<label class="text-success">Available Amount</label>
									<input type="text" name="available" readonly class="form-control" id="available">
								</div>
								<div class="col-sm-3">
									<label class="text-success">Transfer Amount</label>
									<input type="text" name="transfer" id="transfer" class="form-control" required placeholder=" Amount" autocomplete="off">
								</div>
								<div class="col-sm-4">
									<label class="text-success">To Branch</label>
									<select class="to-branch" class="form-control" name="to" id="to" style="width: 100%">
										<option></option>
										<?php
											foreach($branch as $key=>$value){
												echo "<option value='".$value."'>".$value."</option>";
											}
										?>
									</select>
								</div>
								<div class="col-sm-2">
									<button class="btn btn-success btn-block" name="submitTransferFund" type="submit" style="margin-top:23px;"><span style="color:#ffcf40" class="fa fa-share"></span> Transfer</button>
								</div>
							</form>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		
		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading font-bold">
					<span style="color:#990000" class="fa fa-level-up"></span> TRANSFERRED AMOUNT
				</div>
				<div class="panel-body">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Transferred</th>
								<th>Transfer To</th>
								<th>Status</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								while($row = mysqli_fetch_array($transferred)){
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row['transferAmount'] . "</td>";
									echo "<td>" . $row['branchTo'] . "</td>";
									echo "<td>" . $row['status'] . "</td>";
									echo "<td>" . $row['time'] . "</td>";
									echo "</tr>";
									$i++;
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading font-bold">
					<span style="color:#990000" class="fa fa-level-down"></span> RECEIVED AMOUNT
				</div>
				<div class="panel-body">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Received</th>
								<th>From</th>
								<th>Status</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								while($row = mysqli_fetch_array($rec)){
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row['transferAmount'] . "</td>";
									echo "<td>" . $branch[$row['branchId']] . "</td>";
									echo "<td>" . $row['status'] . "</td>";
									echo "<td>" . $row['time'] . "</td>";
									echo "</tr>";
									$i++;
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
	</div>
	
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
		
		$(document).ready(function() {
			$('.to-branch').select2({ 
				placeholder: "SELECT THE BRANCH",
				allowClear: true
			});
		});
	</script>
<?php include("footer.php"); ?>