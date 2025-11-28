<?php
	error_reporting(E_ERROR | E_PARSE);
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
	else if($type=='AccHead'){
		include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	$sBranch = '';
	$sFrom = '';
	$sTo = '';
	$sMetal = '';
	if(isset($_GET['sMetal'])){
		$sBranch = $_GET['sBranch'];
		$sFrom = $_GET['sFrom'];
		$sTo = $_GET['sTo'];
		$sMetal = $_GET['sMetal'];
		$branchDetails = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$sBranch'"));
	}
?>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<?php
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
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
	
	#wrapper .panel-body{
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 20px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	background-color: #f5f5f5;
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
	font-size: 10px;
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
	.modal-title {
	font-size: 25px;
	font-weight: 300;
	color:#fff5ee;
	text-transform:uppercase;
	}
	.modal-header{
	background: #123C69;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<form action="" method="GET">
				<div class="col-sm-3">
					<label class="text-success">Branch Id</label>
					<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
						<input list="branchList"  class="form-control" name="sBranch" placeholder="Branch Id" required />
					</div>
				</div>
				<div class="col-sm-3">
					<label class="text-success">From Date:</label> 
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date"  class="form-control" id="sFrom" name="sFrom" required />
					</div>
				</div>
				<div class="col-sm-3"> 
					<label class="text-success">To Date:</label>
					<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
						<input type="date" class="form-control"  id="sTo" name="sTo" required />
					</div>
				</div>				
				<div class="col-sm-3" align="center" style="margin-top:23px">
					<button class="btn btn-success" name="sMetal" value="Gold"><span style="color:#ffcf40" class="fa fa-search"></span> Search Gold</button> &nbsp; 
					<button class="btn btn-success" name="sMetal" value="Silver"><span style="color:#ffcf40" class="fa fa-search"></span> Search Silver</button>
				</div>
			</form>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-edit"></i> Transaction Report</h3>
				</div>
				<div class="panel-body">
					<form method="POST" action="SendReportPdfMaster.php">
						<table id="example5" class="table table-striped table-bordered table-hover">
							<input type="hidden" name='sBranch' value="<?php echo $sBranch; ?>">
							<input type="hidden" name='sMetal' value="<?php echo $sMetal; ?>">
							<thead>
								<tr class="theadRow">
									<th><i style="color:#ffcf40" class="fa fa-sort-numeric-asc"></i></th>
									<th>Branch</th>
									<th>Name</th>
									<th>Phone</th>
									<th>GrossW</th>
									<th>NetW</th>
									<th>GrossA</th>
									<th>NetA</th>
									<th>Trans_Date</th>
									<th>Send_Date</th>
									<th>Move_to_HO</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									$query = mysqli_query($con,"SELECT name,phone,grossW,netW,grossA,netA,date,sta,staDate FROM trans WHERE date BETWEEN '$sFrom' AND '$sTo' AND metal='".$_GET['sMetal']."' AND branchId='$sBranch' AND status='Approved'");
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";
										echo "<td>". $i ."</td>";
										echo "<td>". $branchDetails['branchName'] ."</td>";
										echo "<td>". $row['name'] ."</td>";
										echo "<td>". $row['phone'] ."</td>";
										echo "<td>". round($row['grossW'],2) . "</td>";
										echo "<td>". round($row['netW'],2) . "</td>";
										echo "<td>". round($row['grossA'],0) . "</td>";
										echo "<td>". round($row['netA'],0) . "</td>";
										echo "<td>". $row['date'] ."</td>";
										echo "<td>". $row['staDate'] ."</td>";
										if($row['sta'] == 'Checked'){
											echo "<td><b style='color:#08347d'>Moved to HO</b></td></tr>";
										}
										else{
											echo "<td><b style='color:#08347d'>Pending</b></td></tr>";
										}
										echo "</tr>";
										$i++;
									}
									$totalQ = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(netW) AS netW,SUM(grossW) AS grossW , SUM(netA) AS netA,SUM(grossA) AS grossA,date,COUNT(id) AS id,AVG(rate) AS rateAvg FROM trans WHERE metal='".$_GET['sMetal']."' AND branchId='$sBranch' AND date BETWEEN '$sFrom' AND '$sTo' AND status='Approved'"));
									$pur = ($totalQ['netW'] == null) ? 0 : ($totalQ['grossA']/$totalQ['netW'])/$totalQ['rateAvg']*100;
								?>
							</tbody>
							<tfoot>
								<tr class="theadRow">
									<th colspan="2">Branch</th>
									<th>Gross Weight</th>
									<th>Net Weight</th>
									<th>Gross Amount</th>
									<th>Net Amount</th>
									<th>Average Purity</th>
									<th>Packets</th>
									<th colspan="2">Sending Date</th>
									<th><?php if($_GET['aaa']){ echo $_GET['aaa'];} ?> Send Report</th>
								</tr>
								<tr style="background-color:#e6e6fa;">
									<td colspan="2"><?php echo $branchDetails['branchName'];?></td>
									<td><?php echo round($totalQ['grossW'],2); ?></td>
									<td><?php echo round($totalQ['netW'],2); ?></td>
									<td><?php echo round($totalQ['grossA'],0); ?></th>
									<td><?php echo round($totalQ['netA'],2); ?></td>
									<td class="text-success"><?php echo round($pur,2);?> %</td>
									<td class="text-success"><?php echo $totalQ['id'];?></td>
									<td colspan="2"><input type="date" class="form-control" name="sDate" required /></td>
									<td><button type="submit" class="btn btn-success" formtarget="_blank" id="del" name="del" value="<?php if($_GET['aaa']){ echo $_GET['aaa'];} ?>" >View Report</button></td>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>