<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	if($_SESSION['branchCode']=="TN"){
		$extra= " AND w.branchId IN (select branchId from branch where state IN ('Tamilnadu','Pondicherry'))";
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Tamilnadu','Pondicherry')");
	}
	elseif($_SESSION['branchCode']=="KA"){
		$extra= " AND w.branchId IN (select branchId from branch where state IN ('Karnataka'))";
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Karnataka')");
	}
	elseif($_SESSION['branchCode']=="AP-TS"){
		$extra= " AND w.branchId IN (select branchId from branch where state IN ('Andhra Pradesh','Telangana'))";
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Andhra Pradesh','Telangana')");
	}
	
	$branchData = [];
	while($row = mysqli_fetch_assoc($branchSQL)){
		$branchData[$row['branchId']] = $row['branchName'];
	}
	
	if(isset($_GET['submitlo'])){
		$date = $_GET['date'];
		if(isset($_GET['branchId']) && $_GET['branchId'] != ''){
			$walkinSQl = "SELECT w.id, w.name, w.mobile as mobile_temp, concat('XXXXXX', right(w.mobile, 4)) as mobile, w.gold, w.havingG, w.metal, w.gwt, w.ramt, w.remarks, w.issue, w.comment, w.agent_id, w.followUp, w.date, w.time, w.branchId, w.zonal_remarks, t.phone
			FROM walkin w 
			LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
			WHERE w.issue!='Rejected' AND (w.date='$date' OR w.indate='$date') AND w.branchId='$_GET[branchId]'".$extra.
			"ORDER BY w.id DESC";
		}
		else{
			$walkinSQl = "SELECT w.id, w.name, w.mobile as mobile_temp, concat('XXXXXX', right(w.mobile, 4)) as mobile, w.gold, w.havingG, w.metal, w.gwt, w.ramt, w.remarks, w.issue, w.comment, w.agent_id, w.followUp, w.date, w.time, w.branchId,w.zonal_remarks, t.phone
			FROM walkin w 
			LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
			WHERE w.issue!='Rejected' AND (w.date='$date' OR w.indate='$date')".$extra.
			"ORDER BY w.id DESC";
		}
	}
	else{
		$date = date('Y-m-d');
		$walkinSQl = "SELECT w.id, w.name, w.mobile as mobile_temp, concat('XXXXXX', right(w.mobile, 4)) as mobile, w.gold, w.havingG, w.metal, w.gwt, w.ramt, w.remarks, w.issue, w.comment, w.agent_id, w.followUp, w.date, w.time, w.branchId, w.zonal_remarks,t.phone
		FROM walkin w
		LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
		WHERE w.issue!='Rejected' AND (w.date='$date' OR w.indate='$date')".$extra.
		"ORDER BY w.id DESC";
	}
	
	$query = mysqli_query($con, $walkinSQl);	
	$result = mysqli_fetch_all($query, MYSQLI_ASSOC);
	$totalLength = count($result);
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	background-color: #f5f5f5;
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
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
	color:#990000;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<datalist id="branchList"> 
	<?php foreach($branchData as $key=>$item ){ ?>
		<option value="<?php echo $key; ?>" label="<?php echo $item; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-5">
						<h3><i class="fa_Icon fa fa-users" ></i> WALKIN REPORT <span class="fa_Icon"><?php echo " ( ".$date." )"; ?> </span></h3>
					</div>
					<form action="" method="GET">
						<div class="col-sm-3">
							<input list="branchList" name="branchId" placeholder="BRANCH" class="form-control">
						</div>
						<div class="col-sm-3">
							<div class="input-group">
								<input name="date" type="date" required class="form-control" />
								<span class="input-group-btn"> 
									<button name="submitlo" value="Submit" type="submit" class="btn btn-success"><span style="color:#ffcf40" class="fa fa-search"></span></button>
								</span>
							</div>
						</form>
					</div>
					<div class="col-sm-1">
						<a href="searchWalkin.php" class="btn btn-success" target="_BLANK">Customer <span style="color:#ffcf40" class="fa fa-search"></span></a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-top: 20px;">
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa_Icon fa fa-refresh"></i> Pending</a></li>
						<li class=""><a data-toggle="tab" href="#tab-2"><i class="fa_Icon fa fa-commenting"></i> Done</a></li>
						<li class=""><a data-toggle="tab" href="#tab-3"><i class="fa_Icon fa fa-check"></i> Billed</a></li>
						<li class=""><a data-toggle="tab" href="#tab-4"><i class="fa_Icon fa fa-calendar-check-o"></i> Billing Today</a></li>
						<li class=""><a data-toggle="tab" href="#tab-5"><i class="fa_Icon fa fa-calendar-check-o"></i> Release Today</a></li>
					</ul>
					<div class="tab-content">
						
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example1" class="table table-striped table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Branch Remark</th>
												<th>Disposition</th>
												<th>HO Remark</th>
												<th>Agent</th>
												<th>Date</th>
												<th>Time</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['date'] == $date) && ($row['mobile_temp'] != $row['phone']) && ($row['zonal_remarks'] == '')){
														
														echo ($row['bills'] > 0) ? "<tr style='color: red;'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $branchData[$row['branchId']] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryZonalRemark.php?id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
														"</tr>";
														
														$i++;
													}
													$k++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div id="tab-2" class="tab-pane">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example2" class="table table-striped table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>HO Remark</th>
												<th>Agent</th>
												<th>Date</th>
												<th>Time</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['date'] == $date) && ($row['mobile_temp'] != $row['phone']) && ($row['zonal_remarks'] != '')){
														
														echo ($row['bills'] > 0) ? "<tr style='color: red;'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $branchData[$row['branchId']] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryZonalRemark.php?id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
														"</tr>";
														
														$i++;
													}
													$k++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div id="tab-3" class="tab-pane">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example3" class="table table-striped table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>HO Remark</th>
												<th>Agent</th>
												<th>Date</th>
												<th>Time</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['mobile_temp'] == $row['phone'] && $row['date'] == $date){
														
														echo ($row['bills'] > 0) ? "<tr style='color: red;'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $branchData[$row['branchId']] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryZonalRemark.php?id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
														"</tr>";
														
														$i++;
													}
													$k++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div id="tab-4" class="tab-pane">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example4" class="table table-striped table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>HO Remark</th>
												<th>Agent</th>
												<th>Date</th>
												<th>Time</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['date'] != $date) && ($row['gold'] == 'physical')){
														
														echo ($row['phone'] == $row['mobile_temp']) ? "<tr style='background-color: #ffe6e6;'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $branchData[$row['branchId']] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryZonalRemark.php?id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
														"</tr>";
														
														$i++;
													}
													$k++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div id="tab-5" class="tab-pane">
							<div class="panel-body">
								<div class="table-responsive">
									<table id="example5" class="table table-striped table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>HO Remark</th>
												<th>Agent</th>
												<th>Date</th>
												<th>Time</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['date'] != $date) && ($row['gold'] == 'release')){
														
														echo ($row['phone'] == $row['mobile_temp']) ? "<tr style='background-color: #ffe6e6;'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $branchData[$row['branchId']] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryZonalRemark.php?id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
														"</tr>";
														
														$i++;
													}
													$k++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>
