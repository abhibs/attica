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
					<h3 class="text-success"><span class="fa fa-list-alt" style="color:#990000"></span><b> TRARE DETAILS</b></h3>
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
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>DATE<br>TIME</th>
								<th>AVAILABLE AMOUNT</th>
								<th>FROM BRANCH</th>
								<th>TO BRANCH</th>
								<th>AMOUNT TRANSFER</th>
								<th>STATUS</th>
								<th>UPDATE</th>
								<th>DELETE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['branchId'])){
									$branchId = $_GET['branchId'];
									$today = date('Y-m-d');
									$sql = mysqli_query($con,"SELECT t.*,b.branchName FROM trare t,branch b WHERE t.branchId=b.branchId AND t.branchId='$branchId' and date='$today' ORDER BY t.date DESC");
									while($row = mysqli_fetch_assoc($sql)){
										
										echo "<tr>
										<form method='POST' action='edit.php'>
										<input type='hidden' name='id' value=".$row['id'].">
										<input type='hidden' name='branchId' value=".$row['branchId'].">";
										echo "<td>".$row['date']."<br>".$row['time']."</td>";										
										echo "<td>".$row['avai']."</td>";
										echo "<td>".$row['branchName']."<br>".$row['branchId']."</td>";
										echo "<td><input type='text' name='branchTo' id='branchTo_".$row['id']."' class='form-control' value='".$row['branchTo']."'></td>";
										echo "<td><input type='text' name='transferAmount' id='transferAmount_".$row['id']."' class='form-control' value='".$row['transferAmount']."'></td>";
										echo "<td><input type='text' name='status' id='status_".$row['id']."' class='form-control' value='".$row['status']."'></td>";

										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm Again');\" class='btn btn-lg' name='editTrare' type='submit'><i class='fa fa-pencil-square-o text-success' style='font-size:16px'></i></button></td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm to delete');\" class='btn btn-lg' type='submit' name='deleteTrare'><i class='fa fa-trash text-danger'></i></button></td>";
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
	<div style="clear:both"></div>	
<?php include("footer.php");?>