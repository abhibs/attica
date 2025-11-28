<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
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
	font-size: 18px;
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
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"><span class="fa fa-list" style="color:#990000"></span><b> SEARCH CUSTOMER BY ID <?php if(isset($_GET['custIDsearch'])){ echo "- ".$_GET['customerID']; } ?></b></h3>
				</div>
				<div class="col-xs-4" style="margin-top:6.5px">
					<form action="" method="GET">
						<div class="input-group">
							<input type="text" class="form-control" name="customerID" placeholder="ENTER ID NUMBER" autocomplete="off" required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block" name="custIDsearch" type="submit"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<table id="example5" class="table table-striped table-hover">
						<h3 class="text-success"><b> CUSTOMER</b></h3>
						<thead>
							<tr class="theadRow">
								<th>Name</th>
								<th>Mobile</th>
								<th>ID Proof</th>
								<th>ID Number</th>
								<th>Add Proof</th>
								<th>Add Number</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['custIDsearch'])){
									$id = $_GET['customerID'];
									$query1 = mysqli_query($con, "SELECT name, mobile, idProof, addProof, idNumber, addNumber FROM customer WHERE idNumber='$id' OR addNumber='$id'");
									while($row = mysqli_fetch_assoc($query1)){
										echo "<tr>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['idProof']."</td>";
										echo "<td>".$row['idNumber']."</td>";
										echo "<td>".$row['addProof']."</td>";
										echo "<td>".$row['addNumber']."</td>";
										echo "</tr>";
									}
								}
							?>
						</tbody>
					</table>
					<hr>
					<table id="example6" class="table table-striped table-hover">
						<h3 class="text-success"><b> CUSTOMER INFO</b></h3>
						<thead>
							<tr class="theadRow">
								<th>Mobile</th>
								<th>Branch ID</th>
								<th>BILL ID</th>
								<th>ID Number</th>
								<th>Add Number</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['custIDsearch'])){
									$id = $_GET['customerID'];
									$query2 = mysqli_query($con, "SELECT mobile, branchId, billId, idNum, addNum, date FROM customerinfo WHERE idNum='$id' OR addNum='$id'");
									while($row = mysqli_fetch_assoc($query2)){
										echo "<tr>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['branchId']."</td>";
										echo "<td>".$row['billId']."</td>";
										echo "<td>".$row['idNum']."</td>";
										echo "<td>".$row['addNum']."</td>";
										echo "<td>".$row['date']."</td>";
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
	<div style="clear:both"></div>
<?php include("footer.php");?>