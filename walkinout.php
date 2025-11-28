<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master') {
	include("header.php");
	include("menumaster.php");
	}
	else{
	include("logout.php");
	}
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");

	$date=date('Y-m-d');
    
?>
<style>
	#wrapper{
		background-color:#f5f5f5;
	}
	
	#wrapper h2{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
	}
	.box{
		padding:10px;
		transition:.2s all; 
	}
	.box-wrap:hover .box{
		transform: scale(.98);
		box-shadow:none;
	}
	.box-wrap:hover .box:hover{
	    filter:blur(0px);
		transform: scale(1.05);
		box-shadow:0 8px 20px 0px #b8860b;
	}
	.hpanel{
		margin-bottom:5px;
	}
	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-size: 18px;
	}
	.stats-label{
		text-transform:uppercase;
		font-size: 10px;
	}
	.list-item-container h3{
		font-size: 14px;
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
</style>
<div id="wrapper">
	<div class="content">
		<div class="row">
		    <div class="hpanel">
				<div class="panel-heading">
					<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Enquiry Walkin</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> With Gold :</small>
                                    <h4><i class="fa fa-money"></i><?php
										$query = "SELECT count(*) as count FROM walkin where date='$date' and branchId !='' and havingG='with'";
											$query_run = mysqli_query($con,$query);
											if ($query_run) {
												while ($row = mysqli_fetch_array($query_run)) {
												    
													echo " ".$row['count'];
												}
											}
									?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Without Gold:</small>
									<h4><i class="fa fa-money"></i><?php 
											$query1 = "SELECT count(*) as count FROM walkin where date='$date' and branchId !='' and havingG='without'";
											$query_run1 = mysqli_query($con,$query1);
											if ($query_run1) {
												while ($row1 = mysqli_fetch_array($query_run1)) {
												    
													echo " ".$row1['count'];
												}
											}
									
									?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                       <b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>
			
			<!---->
			
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">KA Walkin</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> With Gold :</small>
                                    <h4><i class="fa fa-money"></i><?php
										$query = "SELECT count(*) as count FROM walkin where date='$date' and havingG='with' and branchId !='' and branchId in (select branchId from branch where state='Karnataka')";
											$query_run = mysqli_query($con,$query);
											if ($query_run) {
												while ($row = mysqli_fetch_array($query_run)) {
												    
													echo " ".$row['count'];
												}
											}
									?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Without Gold:</small>
									<h4><i class="fa fa-money"></i><?php 
											$query1 = "SELECT count(*) as count FROM walkin where date='$date' and havingG='without' and branchId !='' and branchId in (select branchId from branch where state='Karnataka')";
											$query_run1 = mysqli_query($con,$query1);
											if ($query_run1) {
												while ($row1 = mysqli_fetch_array($query_run1)) {
												    
													echo " ".$row1['count'];
												}
											}
									
									?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                       <b>Attica Gold Pvt Ltd</b> 
					</div>
				</div>
			</div>
			
			<!---->
			
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">TN Walkin</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> With Gold :</small>
                                    <h4><i class="fa fa-money"></i><?php
										$query = "SELECT count(*) as count FROM walkin where date='$date' and havingG='with' and branchId in (select branchId from branch where state in ('Tamilnadu','Pondicherry'))";
											$query_run = mysqli_query($con,$query);
											if ($query_run) {
												while ($row = mysqli_fetch_array($query_run)) {
												    
													echo " ".$row['count'];
												}
											}
									?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Without Gold:</small>
									<h4><i class="fa fa-money"></i><?php 
											$query1 = "SELECT count(*) as count FROM walkin where date='$date' and havingG='without' and branchId in (select branchId from branch where state in ('Tamilnadu','Pondicherry'))";
											$query_run1 = mysqli_query($con,$query1);
											if ($query_run1) {
												while ($row1 = mysqli_fetch_array($query_run1)) {
												    
													echo " ".$row1['count'];
												}
											}
									
									?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                       <b>Attica Gold Pvt Ltd</b> 
					</div>
				</div>
			</div>
			
			<!---->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">AP/T Walkin</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> With Gold :</small>
                                    <h4><i class="fa fa-money"></i><?php
										$query = "SELECT count(*) as count FROM walkin where date='$date' and havingG='with' and branchId in (select branchId from branch where state in ('Telangana','Andhra Pradesh'))";
											$query_run = mysqli_query($con,$query);
											if ($query_run) {
												while ($row = mysqli_fetch_array($query_run)) {
												    
													echo " ".$row['count'];
												}
											}
									?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Without Gold:</small>
									<h4><i class="fa fa-money"></i><?php 
											$query1 = "SELECT count(*) as count FROM walkin where date='$date' and havingG='without' and branchId in (select branchId from branch where state in ('Telangana','Andhra Pradesh'))";
											$query_run1 = mysqli_query($con,$query1);
											if ($query_run1) {
												while ($row1 = mysqli_fetch_array($query_run1)) {
												    
													echo " ".$row1['count'];
												}
											}
									
									?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                       <b>Attica Gold Pvt Ltd</b> 
					</div>
				</div>
			</div>
			<!---->
			<!---->
			
			
				</div>
			</div>
			<div class="col-sm-12">
				<div class="hpanel">
					<div class="panel-heading">
						<h2> Walkin out </h2>
					</div>
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1" class="text-success"> With Gold </a></li>
						<li class=""><a data-toggle="tab" href="#tab-2" class="text-success"> Without Gold </a></li>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body" style="overflow:hidden;">
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr class="theadRow">
											<th>SlNo</th>
											<th>Branch Name</th>
											<th>Customer Name</th>
											<th>Contact Number</th>
											<th>Type</th>
											<th>Gross Weight</th>
											<th>Remarks</th>									
										</tr>
									</thead>
									<tbody>
										<?php
											$query = mysqli_query($con,"SELECT branch.branchName,walkin.* FROM `walkin`,branch where havingG='with' and date='$date' AND walkin.branchId=branch.branchId");
									if(mysqli_num_rows($query) > 0)
									{	
										$i=1;
										while($row = mysqli_fetch_assoc($query))
										{
											echo "<tr>";
											echo "<td>" . $i .  "</td>";
											echo "<td>" . $row['branchName'] . "</td>";
											echo "<td>" . $row['name'] . "</td>";
											echo "<td>" . $row['mobile'] . "</td>";
											echo "<td>" . $row['gold'] . "</td>";
											echo "<td>" . $row['gwt'] . "</td>";
											echo "<td>" . $row['remarks'] . "</td>";
											echo "</tr>";
											$i++;
										}
									}
										?>
									</tbody>
									
								</table>
							</div>
						</div>
						<div id="tab-2" class="tab-pane">
							<div class="panel-body" style="overflow:hidden;">
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr class="theadRow">
											<th>SlNo</th>
											<th>Branch Name</th>
											<th>Customer Name</th>
											<th>Contact Number</th>
											<th>Type</th>
											<th>Gross Weight</th>
											<th>Remarks</th>									
										</tr>
									</thead>
									<tbody>
										<?php
												$date=date('Y-m-d');
											$query = mysqli_query($con,"SELECT branch.branchName,walkin.* FROM walkin,branch where havingG='without' and date='$date' AND walkin.branchId=branch.branchId");
									if(mysqli_num_rows($query) > 0)
									{	
										$i=1;
										while($row = mysqli_fetch_assoc($query))
										{
											echo "<tr>";
											echo "<td>" . $i .  "</td>";
											echo "<td>" . $row['branchName'] . "</td>";
											echo "<td>" . $row['name'] . "</td>";
											echo "<td>" . $row['mobile'] . "</td>";
											echo "<td>" . $row['gold'] . "</td>";
											echo "<td>" . $row['gwt'] . "</td>";
											echo "<td>" . $row['remarks'] . "</td>";
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
			</div>
			<div style="clear: both;"></div>
		</div>
	</div>
	<?php include("footer.php"); ?>
</div>
</div> 
	