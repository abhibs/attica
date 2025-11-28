<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master') {
        include("header.php");
        include("menumaster.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
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
	font-size: 16px;
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
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
	}
</style>
<datalist id="branchList"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE status=1");
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
					<h3 class="text-success"><span class="fa fa-list" style="color:#990000"></span><b> TRANSACTION DETAILS </b></h3>
				</div>
				<div class="col-xs-4" style="margin-top:6.5px">
					<form action="" method="GET">
						<div class="input-group">
							<input list="branchList" class="form-control" name="branchId" placeholder="SELECT BRANCH" required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block" id="branchIDsearch" type="submit"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<table class="table table-striped table-hover">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>CUSTOMER ID</th>
								<th>BILL ID</th>
								<th>NAME</th>
								<th>PHONE</th>
								<th>GROSS W</th>
								<th>NET A</th>
								<th>AMOUNT PAID</th>
								<th>CASH / IMPS</th>
								<th>METAL</th>
								<th>TYPE</th>
								<th>STATUS</th>
								<?php if($type=='Software'){ ?>
									<th style='text-align:center'>BILL</th>								
									<th style='text-align:center'>CUSTOMER</th>
								<?php } ?>								
								<th style='text-align:center'>DELETE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['branchId'])){
									$branchId = $_GET['branchId'];
									$transSQL = mysqli_query($con,"SELECT id,customerId,billId,name,phone,grossW,netA,amountPaid,metal,type,status,branchId,cashA,impsA FROM trans WHERE branchId='$branchId' AND date='$date' ORDER BY id ASC");
									$i = 1;
									while($row = mysqli_fetch_assoc($transSQL)){
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['customerId']."</td>";
										echo "<td>".$row['billId']."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['phone']."</td>";
										echo "<td>".$row['grossW']."</td>";
										echo "<td>".$row['netA']."</td>";
										echo "<td>".$row['amountPaid']."</td>";
										echo "<td> C : ".$row['cashA']."<br> I : ".$row['impsA']."</td>";
										echo "<td>".$row['metal']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['status']."</td>";
										if($type=='Software'){
											echo "<td style='text-align:center'><a href='editTrans.php?id=".$row['id']."' target='_blank' class='btn' type='button'><i class='fa pe-7s-right-arrow' style='font-size:28px;font-weight:600;color:#006600'></i></button></td>";
											echo "<td style='text-align:center'><a href='editCustomers.php?mobile=".$row['phone']."' target='_blank' class='btn' type='button'><i class='fa pe-7s-user-female' style='font-size:28px;font-weight:600;color:#004080'></i></button></td>";
										}
										echo "<td style='text-align:center'><a onClick=\"javascript: return confirm('DELETE THE BILL ???');\" href='deleteTrans.php?id=".$row['id']."&branchId=".$row['branchId']."' class='btn' type='button'><i class='fa pe-7s-close-circle' style='font-size:28px;font-weight:600;color:red'></i></button></td>";
										echo "</tr>";
										$i++;
									}
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php");?>
