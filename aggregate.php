<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}	
	else if($type=='AccHead') {
		include("header.php");
        include("menuaccHeadPage.php");
	}
	/*else if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
		if($_SESSION['branchCode']=="TN"){
			$extra_query="AND branchId IN (SELECT branchId FROM branch WHERE state IN('Tamilnadu','Pondicherry'))";
		}
		elseif ($_SESSION['branchCode']=="KA"){
			$extra_query="AND branchId IN (SELECT branchId FROM branch WHERE state='Karnataka')";
		}
		elseif ($_SESSION['branchCode']=="AP-TS"){
			$extra_query="AND branchId IN (SELECT branchId FROM branch WHERE state IN('Andhra Pradesh','Telangana'))";
		}
	}*/
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	$from="";
	$to="";
	if(isset($_GET['aaa'])){
		$to=$_GET['dat2'];
		$from=$_GET['dat3'];
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
	.fa_Icon {
	color:#ffa500;
	}
	tbody{
	font-weight: 600;
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
<!--  0 BRANCHES - MODAL  -->
<div class="modal fade" id="myModal5" tabindex="-1" role="dialog"  aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>			
			<div class="modal-header">
				<h4 class="modal-title">NIL Business Branches</h4>
				<div class="close error-close" data-dismiss="modal" style="margin-top: -30px;color:#fff5ee;opacity: 0.7;" title="close"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>				
			</div>
			<div class="modal-body">
				<table id="example7" class="table table-striped table-bordered">
					<thead>
						<tr class="theadRow">
							<th><i class="fa fa-sort-numeric-asc"></i></th>
							<th>Branch Name</th>
							<th>Branch</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$query=mysqli_query($con,"SELECT branchId,branchName FROM branch where branchId IN (SELECT distinct(branch) FROM loginotp where date = '$date' group by branch) and branchId NOT IN (select distinct(branchId) from trans where date = '$date') and status = '1'");
							$count=mysqli_num_rows($query);
							for($i=1;$i<=$count;$i++)
							{
								if($count>0)
								{
									$row=mysqli_fetch_array($query);
									echo "<tr><td>" . $i .  "</td>";
									echo "<td>" . $row['branchId'] . "</td>";
									echo "<td>" . $row['branchName'] . "</td>";
									echo "<td>" . $date . "</td></tr>";
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
<div id="wrapper">
	<div class="content">
		<form action="" method="GET">
			<div class="col-sm-5">
				<h3 class="text-success"><br><b><i class="fa_Icon fa fa-edit"></i> Branch-wise Business</b></h3>
			</div>
			<div class="col-sm-3">
				<label class="text-success">From Date:</label> 
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
					<input type="date" class="form-control" name="dat3" required />
				</div>
			</div>
			<div class="col-sm-3"> 
				<label class="text-success">To Date:</label>
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
					<input type="date" class="form-control" name="dat2" required />
				</div>
			</div>
			<div class="col-sm-1"> 
				<button class="btn btn-success" name="aaa" id="aaa" style="margin-top:23px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
			</div>
		</form>
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div data-toggle="modal" data-target="#myModal5" style="cursor:pointer">
						<h5 align="right" class="text-success"><b><i class="fa fa-print" style="color:#900"></i> 0 Business Branches</b></h5>
					</div>					
					<div class="panel-body" style="background: #f5f5f5;border: 5px solid #fff;border-radius: 10px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;">
						<table id="example3" class="table table-striped table-bordered"><?php if($from=="" && $to=="") { ?>
							<thead>
								<tr class="theadRow">
									<th>SlNo</th>
									<th>Branch Name</th>
									<th>Opening</th>
									<th>Expense</th>
									<th>FundRequest</th>
									<th>Transferred</th>
									<th>Received</th>
									<th>Bills</th>
									<th>grossW</th>
									<th>netW</th>
									<th>grossA</th>
									<th>netA</th>
									<th>RG</th>
									<th>PG</th>
									<th>Closing</th>
								</tr>
							</thead>
							<?php
								$query=mysqli_query($con,"SELECT branchId, 
								(SELECT branchName FROM branch b WHERE branchId=t.branchId) AS Branch,
								(select balance from closing where forward='Carry Forward' and branchId=t.branchId group by date DESC LIMIT 1) as Opening,
								(SELECT sum(amount) FROM expense WHERE branchcode=t.branchId and date = '$date') as Expense,
								(SELECT sum(request) FROM fund WHERE branch=t.branchId and date = '$date' and status = 'Approved') as TotalFund,
								(SELECT sum(transferAmount) FROM trare WHERE branchId=t.branchId and date = '$date' and status = 'Approved') as Transferred,
								(SELECT sum(transferAmount) FROM trare WHERE branchTo=Branch and date = '$date' and status = 'Approved') as Received,
								count(*) as business, round(SUM(grossW),2) as grossW, round(SUM(netW),2) as netW, round(SUM(grossA),2) as grossA,
								round(SUM(releases),2) as releases, round(SUM(amountPaid),2) as amountPaid, round(SUM(netA),2) as netA,
								(SELECT COUNT(type) FROM trans WHERE type='Release Gold' and date = '$date' and branchId = t.branchId AND metal= 'Gold') as RG,
								(SELECT COUNT(type) FROM trans WHERE type='Physical Gold' and date = '$date' and branchId = t.branchId AND metal= 'Gold') as PG 
								FROM trans t where date = '$date' ".$extra_query." AND metal= 'Gold'
								group by branchId");
								$count=mysqli_num_rows($query);
								for($i=1;$i<=$count;$i++)
								{
									if($count>0)
									{
										$row=mysqli_fetch_array($query);
										echo "<tr><td>" . $i .  "</td>";
										echo "<td>" . $row['Branch'] . "</td>";
										$Opening=$row['Opening'];
										if($Opening=='')
										{
											$Opening='0';
										}
										echo "<td>" . $Opening . "</td>";
										$Expense=$row['Expense'];
										if($Expense=='')
										{
											$Expense='0';
										}
										echo "<td>" . $Expense . "</td>";
										$TotalFund=$row['TotalFund'];
										if($TotalFund=='')
										{
											$TotalFund='0';
										}
										echo "<td>" . $TotalFund . "</td>";
										$Transferred=$row['Transferred'];
										if($Transferred=='')
										{
											$Transferred='0';
										}
										echo "<td>" . $Transferred . "</td>";
										$Received=$row['Received'];
										if($Received=='')
										{
											$Received='0';
										}
										echo "<td>" . $Received . "</td>";
										echo "<td>" . $row['business'] . "</td>";
										echo "<td>" . round($row['grossW'],2). "</td>";
										echo "<td>" . round($row['netW'],2). "</td>";
										echo "<td>" . round($row['grossA'],0). "</td>";
										$netA=round($row['netA'],0);
										echo "<td>" . $netA . "</td>";
										echo "<td>" . $row['RG'] . "</td>";
										echo "<td>" . $row['PG'] . "</td>";
										$closing = $Opening - $Expense + $TotalFund + $Received - $Transferred - $netA;
										echo "<td>" . $closing . "</td></tr>";
									}
								}
							}
							else
							{
							?>
							<thead>
								<tr class="theadRow">
									<th>SlNo</th>
									<th>Branch Name</th>
									<th>State</th>
									<th>Expense</th>
									<th>FundRequest</th>
									<th>Transferred</th>
									<th>Received</th>
									<th>Bills</th>
									<th>grossW</th>
									<th>netW</th>
									<th>grossA</th>
									<th>netA</th>
									<th>Margin</th>
									<th>RG</th>
									<th>PG</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$query=mysqli_query($con,"SELECT branchId, 
									(SELECT branchName FROM branch b WHERE branchId=t.branchId) AS Branch,(SELECT state FROM branch c WHERE branchId=t.branchId) AS state,
									(SELECT sum(amount) FROM expense WHERE branchcode=t.branchId and date between '$from' and '$to') as Expense,
									(SELECT sum(request) FROM fund WHERE branch=t.branchId and date between '$from' and '$to' and status = 'Approved') as TotalFund,
									(SELECT sum(transferAmount) FROM trare WHERE branchId=t.branchId and date between '$from' and '$to' and status = 'Approved') as Transferred,
									(SELECT sum(transferAmount) FROM trare WHERE branchTo=Branch and date  between '$from' and '$to' and status = 'Approved') as Received,
									count(*) as business, round(SUM(grossW),2) as grossW, round(SUM(netW),2) as netW, round(SUM(grossA),2) as grossA, round(SUM(releases),2) as releases,
									round(SUM(amountPaid),2) as amountPaid, round(SUM(margin),2) as margin, round(SUM(netA),2) as netA,
									(SELECT COUNT(type) FROM trans WHERE type='Release Gold' and date between '$from' and '$to' and branchId = t.branchId AND metal= 'Gold') as RG,
									(SELECT COUNT(type) FROM trans WHERE type='Physical Gold' and date between '$from' and '$to' and branchId = t.branchId AND metal= 'Gold') as PG 
									FROM trans t where date between '$from' and '$to' ".$extra_query." AND metal= 'Gold' group by branchId");
									$count=mysqli_num_rows($query);
									for($i=1;$i<=$count;$i++)
									{
										if($count>0)
										{
											$row=mysqli_fetch_array($query);
											echo "<tr><td>" . $i .  "</td>";
											echo "<td>" . $row['Branch'] . "</td>";
											echo "<td>" . $row['state'] . "</td>";
											$Expense=$row['Expense'];
											if($Expense=='')
											{
												$Expense='0';
											}
											echo "<td>" . $Expense . "</td>";
											$TotalFund=$row['TotalFund'];
											if($TotalFund=='')
											{
												$TotalFund='0';
											}
											echo "<td>" . $TotalFund . "</td>";
											$Transferred=$row['Transferred'];
											if($Transferred=='')
											{
												$Transferred='0';
											}
											echo "<td>" . $Transferred . "</td>";
											$Received=$row['Received'];
											if($Received=='')
											{
												$Received='0';
											}
											echo "<td>" . $Received . "</td>";
											echo "<td>" . $row['business'] . "</td>";
											echo "<td>" . round($row['grossW'],2). "</td>";
											echo "<td>" . round($row['netW'],2). "</td>";
											echo "<td>" . round($row['grossA'],0). "</td>";
											$netA=round($row['netA'],0);
											echo "<td>" . $netA . "</td>";
											echo "<td>" . $row['margin'] . "</td>";
											echo "<td>" . $row['RG'] . "</td>";
											echo "<td>" . $row['PG'] . "</td></tr>";
										}
									}
								}
							?>
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php"); ?>	