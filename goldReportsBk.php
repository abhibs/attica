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
	$branchName = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branchId'"));
	$search = "";
	$search1 = "";
	if(isset($_GET['aaa'])){
		$search = $_REQUEST['dat2'];
		$search1 = $_REQUEST['dat3'];
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
		font-size: 12px;
		font-family:'Roboto',sans-serif;
		text-transform:uppercase;
		color:#fffafa;
		background-color:#123C69;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
		text-align:center;
		position:relative;
	}
	
	.totalGold td{
	    font-weight:bold;
	}
</style>
<div id="wrapper">
	<div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
					<div class="col-sm-12">
						<h3><span style="color:#900" class="fa fa-file-text"></span> GOLD SEND REPORT </h3>
					</div>
					<div style="clear:both"></div>
					<form action="" method="GET">
						<div class="col-sm-4">
							<div class="input-group" style="margin-top:23px">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-institution"></span></span>
								<input style="font-weight:bold;color:#900;text-transform:uppercase;" type="text" readonly class="form-control" value="Branch : <?php echo $branchName['branchName']." (".$branchId.")" ?>">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">FROM DATE:</label> 
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
								<input type="date"  class="form-control" id="dat3" name="dat3" required />
							</div>
						</div>
						<div class="col-sm-3"> 
							<label class="text-success">TO DATE:</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
								<input type="date" class="form-control"  id="dat2" name="dat2" required />
							</div>
						</div>
						<div class="col-sm-2">
							<button class="btn btn-success btn-block" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> SEARCH</button>
						</div>
					</form>
					<div style="clear:both"></div>
				</div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius:10px;padding:20px;box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);background-color:#F5F5F5;">
					<?php if($search=="" && $search1==""){ ?>
						<table id="example2" class="table table-striped table-bordered table-hover">
							<tbody>
								<tr align="center" class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<td><b>Bill ID</b></td>
									<td><b>Date & Time</b></td>
									<td><b>Gross Weight</b></td>
									<td><b>Net Weight</b></td>
									<td><b><span class="fa fa-money"></span> Gr Amt</b></td>
									<td><b><span class="fa fa-money"></span> Net Amt</b></td>
									<td><b><span class="fa fa-money"></span> Gold Rate</b></td>
									<td><b><span class="fa fa-money"></span> Purity</b></td>
									<td><b><span class="fa fa-money"></span> Status</b></td>
									<td style="width:90px"><b><span class="fa fa-money"></span> Action</b></td>
									<td style="width:120px"><b><span class="fa fa-money"></span> Remarks</b></td>
								</tr>
								<?php
									$i = 1;
									$sql1 = mysqli_query($con,"SELECT billId,grossW,netW,grossA,netA,rate,purity,status,date,time FROM trans WHERE date='$date' AND status='Approved' AND branchId='$branchId' AND metal='Gold' AND sta!='Checked'");
									while($row1 = mysqli_fetch_assoc($sql1)){
										echo "<tr>";
										echo "<td style='width:50px'>" . $i .  "</td>";
										echo "<td>" . $row1['billId'] . "</td>";
										echo "<td>" . $row1['date'] . "<br>". $row1['time'] ."</td>";
										echo "<td>" . round($row1['grossW'],2). "</td>";
										echo "<td>" . round($row1['netW'],2). "</td>";
										echo "<td>" . round($row1['grossA'],0) . "</td>";
										echo "<td>" . round($row1['netA'],0) . "</td>";
										echo "<td>" . $row1['rate'] . "</td>";
										echo "<td>" . round($row1['purity'],2) . "</td>";
										echo "<td>" . $row1['status'] . "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "</tr>";
										$i++;
									}
								?>
								<tr class="totalGold">
									<?php
										$sql2=mysqli_query($con,"SELECT ROUND(SUM(netW),2) AS netW,ROUND(SUM(grossW),2) AS grossW , ROUND(SUM(netA),2) AS netA,ROUND(SUM(grossA)) AS grossA,COUNT(id) AS count,AVG(rate) AS rateAvg FROM trans WHERE date='$date' AND branchId='$branchId' AND status='Approved' AND metal='Gold' AND sta!='Checked'");
										$row2 = mysqli_fetch_assoc($sql2);
										if($row2['netW']!=0){
											$pur = ($row2['grossA']/$row2['netW'])/$row2['rateAvg']*100;
										}else{
											$pur=0;
										}
									?>
									<th colspan="2" class="text-success">Gross Weight</th>
									<td><?php echo round($row2['grossW'],2);?></td>
									<th class="text-success">Net Weight</th>
									<td><?php echo round($row2['netW'],2);?></td>
									<th class="text-success">Gross Amount</th>
									<td><?php echo round($row2['grossA'],0);?></th>
									<th class="text-success">Net Amount</th>
									<td><?php echo round($row2['netA'],2);?></td>
									<th class="text-success">Average Purity</th>
									<td><?php echo round($pur,2);?> %</td>
									<th class="text-success">Packets: <?php echo $row2['count'];?></th>
								</tr>
							</tbody>
						</table>
						<?php } else if($search!="" && $search1!=""){ ?>
						<form method="POST" action="newCheck.php">
							<table id="example2" class="table table-striped table-bordered table-hover">
								<tbody>
									<tr align="center" class="theadRow">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<td><b>Bill ID</b></td>
										<td><b>Date & Time</b></td>
										<td><b>Gross Weight</b></td>
										<td><b>Net Weight</b></td>
										<td><b><span class="fa fa-money"></span> Gr Amt</b></td>
										<td><b><span class="fa fa-money"></span> Net Amt</b></td>
										<td><b><span class="fa fa-money"></span> Gold Rate</b></td>
										<td><b><span class="fa fa-money"></span> Purity</b></td>
										<td><b><span class="fa fa-money"></span> Status</b></td>
										<td style="width:90px"><b><span class="fa fa-money"></span> Action</b></td>
										<td style="width:120px"><b><span class="fa fa-money"></span> Remarks</b></td>
									</tr>
									<?php
										$i = 1;
										$sql1 = mysqli_query($con,"SELECT id,billId,grossW,netW,grossA,netA,rate,purity,status,date,time FROM trans WHERE date BETWEEN '$search1' AND '$search' AND branchId='$branchId' AND metal='Gold' AND status = 'Approved' AND sta!='Checked'");
										while($row1 = mysqli_fetch_assoc($sql1)){
											echo "<tr>";
											echo "<td style='width:50px'>" . $i . "</td>";
											echo "<td>" . $row1['billId'] . "</td>";
											echo "<td>" . $row1['date'] . "<br>". $row1['time'] ."</td>";
											echo "<td>" . round($row1['grossW'],2). "</td>";
											echo "<td>" . round($row1['netW'],2). "</td>";
											echo "<td>" . round($row1['grossA'],0) . "</td>";
											echo "<td>" . round($row1['netA'],0) . "</td>";
											echo "<td>" . $row1['rate'] . "</td>";
											echo "<td>" . round($row1['purity'],2) . "</td>";
											echo "<td>" . $row1['status'] . "</td>";
											echo "<td><input type='checkbox' id='mul[]' name='mul[]' value='".$row1['id']."'/></td>";
											echo "<td></td>";
											echo "</tr>";
											$i++;
										}
									?>
								    <tr class="totalGold">
										<?php
											$sql2=mysqli_query($con,"SELECT ROUND(SUM(netW),2) AS netW, ROUND(SUM(grossW),2) AS grossW , ROUND(SUM(netA),2) AS netA, ROUND(SUM(grossA)) AS grossA,COUNT(id) AS count,AVG(rate) AS rateAvg FROM trans WHERE branchId='$branchId' AND status='Approved' AND metal='Gold' AND sta!='Checked' AND date BETWEEN '$search1' AND '$search'");
											$row2 = mysqli_fetch_assoc($sql2);
											if($row2['netW'] != 0){
											    $pur = ($row2['grossA']/$row2['netW'])/$row2['rateAvg']*100;
											}
											else{
											    $pur = 0;
											}
										?>
										<th colspan="2" class="text-success">Gross Weight</th>
										<td><?php echo round($row2['grossW'],2);?></td>
										<th class="text-success">Net Weight</th>
										<td><?php echo $row2['netW'];?></td>
										<th class="text-success">Gross Amount</th>
										<td><?php echo round($row2['grossA'],0);?></th>
										<th class="text-success">Net Amount</th>
										<td><?php echo round($row2['netA'],2);?></td>
										<th class="text-success">Average Purity</th>
										<td><?php echo round($pur,2);?> %</td>
										<th class="text-success">Packets: <?php echo $row2['count'];?></th>
									</tr>
									<?php echo "<tr><td colspan='12'><input type='submit' class='btn btn-success' id='del' name='del' value='Print Report' > <a href='goldSendPdf.php' target='_blank' class='btn btn-primary'><i style='color:#fa0' class='fa fa-print'></i> Re Print Gold Send Report</a></td></tr>"; ?>
								</tbody>
							</table>
						</form>
					<?php } ?>
				</div>
			</div>
		</div>
		<div style="clear:both"><br></div>
	</div>
<?php include("footer.php"); ?>