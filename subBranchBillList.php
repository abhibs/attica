<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	$employeeId = $_SESSION['employeeId'];

	if($type=='SubZonal'){
        include("header.php");
        include("menuSubZonal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	border: none;
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
	.fa_Icon {
	color: #b8860b;
	}
</style>
<datalist id="branchList"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1 AND ezviz_vc = '$employeeId'");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"><span class="fa fa-bank" style="color:#990000"></span><b> BRANCH BILL NAME CHANGE</b></h3>
				</div>
				<div class="col-xs-4">
					<form action="" method="GET">
						<div class="input-group">
							<input list="branchList" class="form-control" name="branchId" placeholder="SELECT BRANCH" required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block btn-sm" id="branchIDsearch" type="submit"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<table class="table table-bordered">
						<thead>
							<tr class="theadRow">
								<th>BILL ID</th>
								<th>CUSTOMER ID</th>
								<th>CONTACT</th>
								<th>NAME</th>
								<th>TYPE</th>
								<th>GROSS W</th>
								<th>AMOUNT PAID</th>
								<th style='text-align:center'>UPDATE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['branchId'])){
									$branchId = $_GET['branchId'];
									$transSQL = mysqli_query($con,"SELECT id,customerId,billId,name,phone,grossW,amountPaid,type FROM trans WHERE branchId='$branchId' AND date='$date' ORDER BY id ASC");
									while($row = mysqli_fetch_assoc($transSQL)){
										echo "<tr>
										<form method='POST' action='edit.php'>
										<input type='hidden' name='transId' value='".$row['id']."'>
										<input type='hidden' name='branchId' value='".$branchId."'>";
										echo "<td>".$row['billId']."</td>";
										echo "<td>".$row['customerId']."</td>";
										echo "<td>".$row['phone']."</td>";
										echo "<td><input type='text' class='form-control' name='customerName' value='".$row['name']."'></td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['grossW']."</td>";
										echo "<td>".$row['amountPaid']."</td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm Again');\" class='btn btn-lg' name='subZonalBillNameEdit' type='submit' style='background-color:transparent'><i class='fa fa-pencil-square-o text-success' style='font-size:16px'></i></button></td>";
										echo "</form>";
										echo "</tr>";
									}
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php");?>
