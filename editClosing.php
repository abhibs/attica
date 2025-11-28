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
?>
<style>
	th{
	text-align:center
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
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
	tbody{
	font-weight: 600;
	}
</style>
<datalist id="branchList"> 
	<?php 
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
		while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class=" row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"><span class="fa fa-lock" style="color:#990000"></span><b> CLOSING DETAILS</b></h3>
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
					<table class="table table-striped  table-hover">
						<thead>
							<tr class="theadRow">
								<th>BRANCH</th>
								<th>DATE<br>TIME</th>
								<th>OPENING</th>
								<th>TOTAL<br>AMOUNT</th>
								<th>NO OF<br>TRANS</th>
								<th>TRANS<br>AMOUNT</th>
								<th>EXPENSE</th>
								<th>BALANCE</th>
								<th>TOTAL</th>
								<th>DIFF</th>
								<th>MORE</th>
								<th>STATUS</th>
								<th>CHOOSE</th>
								<th>UPDATE</th>
								<th>DELETE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['branchId'])){
									$branchId = $_GET['branchId'];
									$closingSQL = mysqli_query($con,"SELECT c.*,b.branchName FROM closing c,branch b WHERE c.branchId=b.branchId AND c.branchId='$branchId' ORDER BY c.date DESC LIMIT 3");
									while($row = mysqli_fetch_assoc($closingSQL)){
										
										echo "<tr>
										<form method='POST' action='edit.php'>
										<input type='hidden' name='cid' value=".$row['closingID'].">
										<input type='hidden' name='branchId' value=".$row['branchId'].">";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['date']."<br>".$row['time']."</td>";
										echo "<td>".$row['open']."</td>";
										echo "<td>".$row['totalAmount']."</td>";
										echo "<td>".$row['transactions']."</td>";
										echo "<td>".$row['transactionAmount']."</td>";
										echo "<td>".$row['expenses']."</td>";
										echo "<td>".$row['balance']."</td>";
										echo "<td class='text-success'>".$row['total']."</td>";
										echo "<td>".$row['diff']."</td>";
										echo "<td><a class='btn btn-lg' type='button' data-toggle='collapse' data-target='#".$row['closingID']."' aria-expanded='false' aria-controls='".$row['closingID']."' style='padding:4px;'><i class='fa fa-chevron-down text-success'></i></a></td>";
										echo "<td class='text-success'>".$row['forward']."</td>";
										echo "<td><div class='form-group'>
										<select class='form-control m-b' name='forward' required>
										<option selected='true' disabled='disabled' value=''>SELECT</option>
										<option value='Forward to HO'>Forward to HO</option>
										<option value='Carry Forward'>Carry Forward</option>
										<option value='Pending Cases'>Pending Cases</option>
										</select>
										</div></td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm Again');\" class='btn btn-lg' name='editClosing' type='submit' style='background-color:transparent'><i class='fa fa-pencil-square-o text-success' style='font-size:18px'></i></button></td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm to  delete');\" class='btn btn-lg' type='submit' name='deleteClosing' style='background-color:transparent'><i class='fa fa-trash text-danger'></i></button></td>";
										echo "</form></tr>";
										
										echo "<tr>";
										echo "<td colspan='15' style='padding: 0px 0px;'>
										<div class='collapse' id='".$row['closingID']."'>
										<table class='table table-striped table-hover table-bordered' style='margin-bottom:0px'>
										<tr class='text-success'>
										<th>2000</th>
										<th>500</th>
										<th>200</th>
										<th>100</th>
										<th>50</th>
										<th>20</th>
										<th>10</th>
										<th>5</th>
										<th>2</th>
										<th>1</th>
										</tr>
										<tr style='text-align:center'>
										<td>".$row['one']."</td>
										<td>".$row['two']."</td>
										<td>".$row['three']."</td>
										<td>".$row['four']."</td>
										<td>".$row['five']."</td>
										<td>".$row['six']."</td>
										<td>".$row['seven']."</td>
										<td>".$row['eight']."</td>
										<td>".$row['nine']."</td>
										<td>".$row['ten']."</td>
										</tr>
										</table>
										</div>
										</td>";
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