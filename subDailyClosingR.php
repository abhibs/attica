<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	$employeeId=$_SESSION['employeeId'];

	if($type=='SubZonal'){
	    require("header.php");
        include("menuSubZonal.php");
	}
    else{
        include("logout.php");
	}
	
	include("dbConnection.php");
	$date = date('Y-m-d');
	$branchList = '';
	if($type=='SubZonal'){
        	$branchList = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE ezviz_vc = '$employeeId' AND status=1");
	}

	if(isset($_GET['bus'])){
		$branchId = $_GET['bus'];
		$branchData = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branchId'"));
		
		$totalTrans = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(amountPaid) AS total,SUM(releases) AS rel FROM trans WHERE branchId='$branchId' AND status='Approved' AND date='$date'"));
		
		$goldSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) AS goldCount,ROUND(SUM(grossW),2) AS grossW,ROUND(SUM(netW),2) AS netW,ROUND(SUM(grossA),2) AS grossA,ROUND(SUM(netA),2) AS netA FROM trans WHERE branchId='$branchId' AND status='Approved' AND metal='Gold' AND date='$date'"));
		
		$silverSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) AS silverCount,ROUND(SUM(grossW),2) AS grossW,ROUND(SUM(netW),2) AS netW,ROUND(SUM(grossA),2) AS grossA,ROUND(SUM(netA),2) AS netA FROM trans WHERE branchId='$branchId' AND status='Approved' AND metal='Silver' AND date='$date'"));
	}
	
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
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
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
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.fa_Icon {
	color:#ffcf40;
	}
	.row{
	margin-left:0px;
	margin-right:0px;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<?php
        while($branchL = mysqli_fetch_array($branchList)){
		?>
		<option value="<?php echo $branchL['branchId']; ?>" label="<?php echo $branchL['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<input type="hidden" id="session_branchID" value="<?php if(isset($_GET['bus'])){ echo $_GET['bus']; } ?>">
					<form action="" method="GET">
						<div class="col-sm-4">
							<h3 class="text-success no-margins" style="padding-top:23px"><span style="color:#990000" class="fa fa-file-text"></span><b> BRANCH REPORT</b></h3>
						</div>
						<div class="col-sm-3">
							<label class="text-success">BRANCH NAME</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-bank"></span></span>
							<input type="text" readonly class="form-control" value="<?php if(isset($_GET['bus'])){ echo $branchData['branchName']; }?>"></div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">SELECT BRANCH</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
								<input list="branchList" class="form-control" name="bus" placeholder="BRANCH ID" required>
							</div>
						</div>

				
						<div class="col-sm-2">
							<button class="btn btn-success btn-block" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> SEARCH</button>
						</div>
					</form>
				</div>
				<div style="clear:both"></div>
				<br>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<div class="col-sm-3">
						<label class="text-success">OPENING BALANCE</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" readonly class="form-control" name="open" id="open">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">FUND REQUESTED</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="totalamount" readonly id="totalamount" class="form-control">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">FUND RECEIVED</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="fundR" readonly id="fundR" class="form-control" >
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">FUND TRANSFERED</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="fundTranfer" readonly id="fundTranfer" class="form-control" >
						</div>
					</div>
					
					
					<div class="col-sm-3">
						<label class="text-success">EXPENSE</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
							<input type="text" name="todaysExpense" readonly id="todaysExpense" class="form-control">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">TRANSACTION AMOUNT</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="totalTranAmount" readonly id="totalTranAmount" class="form-control" value="<?php echo $totalTrans['total']+$totalTrans['rel']+0; ?>">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">TOTAL TRANSACTIONS</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-edit"></span></span>
							<input type="text" name="totalTran" readonly id="totalTran" class="form-control" value="<?php echo $goldSQL['goldCount']+$silverSQL['silverCount']+0; ?>">
						</div>
					</div>
					
					<div class="col-sm-3">
						<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">GOLD</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
							<input type="text" name="grossWeightG" id="grossWeightG" readonly class="form-control" value="<?php echo $goldSQL['grossW']+0; ?>" >
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">NET WEIGHT (<span style="color:#b8860b">GOLD</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
							<input type="text" name="netWeightG" id="netWeightG" readonly class="form-control" value="<?php echo $goldSQL['netW']+0; ?>" >
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">GROSS AMOUNT(<span style="color:#b8860b">GOLD</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="grossAmountG" id="grossAmountG" readonly class="form-control" value="<?php echo $goldSQL['grossA']+0; ?>" >
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">NET AMOUNT(<span style="color:#b8860b">GOLD</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="netAmountG" id="netAmountG" readonly class="form-control" value="<?php echo $goldSQL['netA']+0; ?>" >
						</div>
					</div>
					
					<div class="col-sm-3">
						<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">SILVER</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
							<input type="text" name="grossWeightS" id="grossWeightS" readonly class="form-control" value="<?php echo $silverSQL['grossW']+0; ?>">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">NET WEIGHT(<span style="color:#b8860b">SILVER</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
							<input type="text" name="netWeightS" id="netWeightS" readonly class="form-control" value="<?php echo $silverSQL['netW']+0; ?>">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">GROSS AMOUNT(<span style="color:#b8860b">SILVER</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="grossAmountS" id="grossAmountS" readonly class="form-control" value="<?php echo $silverSQL['grossA']+0; ?>">
						</div>
					</div>
					<div class="col-sm-3">
						<label class="text-success">NET AMOUNT(<span style="color:#b8860b">SILVER</span>)</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
							<input type="text" name="netAmountS" id="netAmountS" readonly class="form-control" value="<?php echo $silverSQL['netA']+0; ?>">
						</div>
					</div>
					<div class="col-sm-3" style="float:right">
						<label class="text-success">CLOSING BALANCE</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
							<input readonly type="text" name="balance" id="balance" class="form-control">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
	<script>
		$(document).ready(function(){
			if($("#session_branchID").val()){
				let branchId = $("#session_branchID").val();
				var req = $.ajax({
					url:"xbalance.php",
					type:"POST",
					data:{branchId:branchId},
					dataType:'JSON'
				});
				req.done(function(e){
					$("#open").val(e.open);
					$("#totalamount").val(e.totalFund);
					$("#fundR").val(e.fundRec);
					$("#fundTranfer").val(e.fundTransfer);
					$("#todaysExpense").val(e.expense);
					$("#balance").val(e.balance);
				});
			}
		});
	</script>
	<!--<script>
	$(document).ready(function() {
		// Check if a branch ID is already stored in session
		if($("#session_branchID").val()) {
			let branchId = $("#session_branchID").val();
			var req = $.ajax({
				url:"xbalance.php",
				type:"POST",
				data:{branchId:branchId},
				dataType:'JSON'
			});
			req.done(function(e){
				$("#open").val(e.open);
				$("#totalamount").val(e.totalFund);
				$("#fundR").val(e.fundRec);
				$("#fundTranfer").val(e.fundTransfer);
				$("#todaysExpense").val(e.expense);
				$("#balance").val(e.balance);
			});
		}

		// Add validation when form is submitted
		$("form").submit(function(event) {
			let branchIdInput = $("input[name='bus']").val(); // Get the branch ID entered by the user
			let assignedBranches = <?php echo json_encode(array_column(mysqli_fetch_all($branchList, MYSQLI_ASSOC), 'branchId')); ?>; // PHP array of assigned branches

			// Check if the entered branch exists in the assigned branches list
			if ($.inArray(branchIdInput, assignedBranches) === -1) {
				// Prevent form submission if the branch is not assigned
				event.preventDefault();
				alert('This branch is not assigned to you. Please select a valid branch.');
			}
		});
	});
	</script>-->
<?php include("footer.php");?>
