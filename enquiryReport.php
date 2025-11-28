<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	
	if($type=='Issuecall'){
		include("header.php");
		include("menuissues.php");
		$extra= "";
		if($_SESSION['branchCode']=="TN"){
			$extra= " w.branchId IN (select branchId from branch where state IN('Tamilnadu','Pondicherry'))";
		}
		elseif ($_SESSION['branchCode']=="KA"){
			$extra= " w.branchId IN (select branchId from branch where state IN('Karnataka'))";
		}
		elseif($_SESSION['branchCode']=="AP-TS"){
			$extra= " w.branchId IN (select branchId from branch where state IN('Andhra Pradesh','Telangana'))";
		}
		elseif($_SESSION['branchCode']=="KA/AP-TS"){
			$extra= " w.branchId IN (select branchId from branch where state IN('Andhra Pradesh','Telangana','Karnataka'))";
		}
	}
	else if($type == 'BD') {
		include("header.php");
		include("menubd.php");
		 $extra = " 1 ";
	}
	else if ($type == 'MIS-Team') {
  		include('header.php');
  		include('menumis.php');
		 $extra = " 1 ";
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	// DATE SELECTION
	if (isset($_GET['submitlo'])) {
		$date = $_GET['date'];
	}
	else {
		$date = date('Y-m-d');
	}
	
	// DATA VARIABLES
	$a = 1; $pending = ''; 
	$b = 1;	$coming_today = '';
	$c = 1;	$planning_to_visit = '';
	$d = 1;	$rnr = '';
	$e = 1;	$enquiry_pending = '';
	$f = 1;	$not_feasible_not_interested_price_issue = '';
	$g = 1; $wrong_number = '';
	$h = 1; $billed = '';
	$i = 1; $sold_outside = '';
	$j = 1; $follow_up = '';
	
	// TOTAL ENQUIRY DATA
	$walkin = mysqli_query($con,"SELECT  w.*, b.branchName, t.phone, e.name AS zonal_name
	FROM walkin w 
	LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
	LEFT JOIN branch b ON w.branchId = b.branchId
	LEFT JOIN employee e ON b.ezviz_vc = e.empId
	WHERE w.issue!='Rejected' AND (w.date='$date' OR w.indate='$date') AND ".$extra."
	ORDER BY w.id ASC");
	
	while($row = mysqli_fetch_assoc($walkin)){ 
		if($row['date'] == $date){
			if($row['mobile'] == $row['phone'] || $row['issue'] == 'Sold in Attica'){
				$billed.= "<tr>".
				"<td>".$h."</td>".
				"<td>".$row['branchName']."</td>".
				"<td>".$row['zonal_name'] . "</td>".
				"<td>".$row['name']."</td>".
				"<td>".$row['mobile']."</td>".
				"<td>".$row['gold']."</td>".
				"<td>".$row['gwt']."</td>".
				"<td>".$row['ramt']."</td>".
				"<td>".$row['remarks']."</td>".
				"<td>".$row['comment']."</td>".
				"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
				"<td>".$row['time']."</td>".
				"</tr>";
				$h++;
			}
			else{
				if($row['status'] == 1){
					$pending .= "<tr>".
					"<td>".$a."</td>".
					"<td>".$row['branchName']."</td>".
					"<td>".$row['zonal_name'] . "</td>".
					"<td>".$row['name']."</td>".
					"<td>".$row['mobile']."</td>".
					"<td>".$row['gold']."</td>".
					"<td>".$row['gwt']."</td>".					
					"<td>".$row['remarks']."</td>".
					"<td>".$row['time']."</td>".
					"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
					"</tr>";
					$a++;
				}
				else{
					if($row['issue'] == 'Coming Today'){
						$coming_today .= "<tr>".
						"<td>".$b."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['time']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
						"</tr>";
						$b++;
					}
					else if($row['issue'] == 'Planning to Visit'){
						$planning_to_visit.= "<tr>".
						"<td>".$c."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['indate']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
						"</tr>";
						$c++;
					}
					else if($row['issue'] == 'RNR'){
						$rnr.= "<tr>".
						"<td>".$d."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
						"</tr>";
						$d++;
					}
					else if($row['issue'] == 'Just Enquiry' || $row['issue'] == 'Pending'){
						$enquiry_pending.= "<tr>".
						"<td>".$e."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
						"</tr>";
						$e++;
					}
					else if($row['issue'] == 'Not Feasible' || $row['issue'] == 'Not Interested'){
						$not_feasible_not_interested_price_issue.= "<tr>".
						"<td>".$f."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['issue']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"</tr>";
						$f++;
					}
					else if($row['issue'] == 'Wrong Number'){
						$wrong_number.= "<tr>".
						"<td>".$g."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"</tr>";
						$g++;
					}
					else if($row['issue'] == 'Sold Outside'){
						$sold_outside.= "<tr>".
						"<td>".$i."</td>".
						"<td>".$row['branchName']."</td>".
						"<td>".$row['zonal_name'] . "</td>".
						"<td>".$row['name']."</td>".
						"<td>".$row['mobile']."</td>".
						"<td>".$row['gold']."</td>".
						"<td>".$row['gwt']."</td>".
						"<td>".$row['ramt']."</td>".
						"<td>".$row['remarks']."</td>".
						"<td>".$row['comment']."</td>".
						"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
						"<td>".$row['time']."</td>".
						"</tr>";
						$i++;
					}
				}
			}
		}
		else if($row['indate'] == $date){
			if($row['mobile'] == $row['phone']){
				$follow_up .= "<tr style='background-color: #ffe6e6'>";
			}
			else{
				$follow_up .= "<tr>";
			}
			$follow_up .= "<td>".$j."</td>".
			"<td>".$row['branchName']."</td>".
			"<td>".$row['zonal_name'] . "</td>".
			"<td>".$row['name']."</td>".
			"<td>".$row['mobile']."</td>".
			"<td>".$row['gold']."</td>".
			"<td>".$row['gwt']."</td>".
			"<td>".$row['ramt']."</td>".
			"<td>".$row['remarks']."</td>".
			"<td>".$row['comment']."</td>".
			"<td>".$row['date']."<br>".$row['time']."</td>".
			"<td>".$row['agent_id']."<br>".$row['followUp']."</td>".
			"<td><b><a class='text-success' href='enquiryComment.php?mobile=".$row['mobile']."&id=".$row['id']."'><span class='fa fa-edit'></span></a></b></td>".
			"</tr>";
			$j++;
		}
	}
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 19px;
	color:#123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
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
	font-size: 11px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:10px;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-7">
						<h3><i style="color:#990000" class="trans_Icon fa fa-users" ></i> ENQUIRY REPORT <span style="color:#990000"><?php echo "( ".$date." )"; ?> </span></h3>
					</div>
					<div class="col-sm-3" >
						<form action="" method="GET">
							<div class="input-group">
								<input name="date" id="text-12" value="" type="date" class="form-control" />
								<span class="input-group-btn"> 
									<button name="submitlo" value="Submit" type="submit" class="btn btn-success"><span style="color:#ffcf40" class="fa fa-search"></span></button>
								</span>
							</div>
						</form>
					</div>
					<div class="col-sm-2">
						<a href="searchWalkin.php" class="btn btn-success btn-block" target="_blank">Lookup Customer</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-top: 20px;">
				
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#tab-1" class="text-success">Pending</a></li>
					<li><a data-toggle="tab" href="#tab-2" class="text-success">Follow-Up</a></li>
					<li><a data-toggle="tab" href="#tab-3" class="text-success">Coming Today</a></li>
					<li><a data-toggle="tab" href="#tab-4" class="text-success">Planning to Visit</a></li>
					<li><a data-toggle="tab" href="#tab-5" class="text-success">RNR</a></li>
					<li><a data-toggle="tab" href="#tab-6" class="text-success">Enquiry / Pending</a></li>
					<li><a data-toggle="tab" href="#tab-7" class="text-success">Not Feasible/ Not Interested/ Price issue</a></li>
					<li><a data-toggle="tab" href="#tab-8" class="text-success">Wrong Number</a></li>
					<li><a data-toggle="tab" href="#tab-9" class="text-success">Billed</a></li>
					<li><a data-toggle="tab" href="#tab-10" class="text-success">Sold Outside</a></li>
				</ul>
				<div class="tab-content">
					
					<!-- PENDING -->
					<div id="tab-1" class="tab-pane active">
						<div class="panel-body">
							<table id="example1" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
										<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>Branch Remarks</th>
										<th>Time</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $pending; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- TODAY FOLLOW-UP -->
					<div id="tab-2" class="tab-pane">
						<div class="panel-body">
							<table id="example2" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
										<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Date</th>
										<th>Agent</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $follow_up; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- COMING TODAY -->
					<div id="tab-3" class="tab-pane">
						<div class="panel-body">
							<table id="example3" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
										<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Time</th>
										<th>Agent</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $coming_today; ?>
								</tbody>
							</table>
						</div>
					</div>	
					
					<!-- PLANNING TO VISIT -->
					<div id="tab-4" class="tab-pane">
						<div class="panel-body">
							<table id="example4" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Coming On</th>
										<th>Agent</th>
										<th>Time</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $planning_to_visit; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- RNR -->
					<div id="tab-5" class="tab-pane">
						<div class="panel-body">
							<table id="example5" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Agent</th>
										<th>Time</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $rnr; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- JUST ENQUIRY / PENDING -->
					<div id="tab-6" class="tab-pane">
						<div class="panel-body">
							<table id="example6" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Agent</th>
										<th>Time</th>
										<th><span class="fa fa-edit"></span></th>
									</tr>
								</thead>
								<tbody>
									<?php echo $enquiry_pending; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- NOT FEASIBLE / NOT INTERESTED / PRICE ISSUE -->
					<div id="tab-7" class="tab-pane">
						<div class="panel-body">
							<table id="example7" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>disposition</th>
										<th>Agent</th>
										<th>Time</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $not_feasible_not_interested_price_issue; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- WRONG NUMBER -->
					<div id="tab-8" class="tab-pane">
						<div class="panel-body">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Agent</th>
										<th>Time</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $wrong_number; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- BILLED -->
					<div id="tab-9" class="tab-pane">
						<div class="panel-body">
							<table id="example9" class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Agent</th>
										<th>Time</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $billed; ?>
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- SOLD OUTSIDE -->
					<div id="tab-10" class="tab-pane">
						<div class="panel-body">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>Branch</th>
	<th>Zonal Name</th>
										<th>Name</th>
										<th>Contact</th>
										<th>Type</th>
										<th>GrossW</th>
										<th>ReleaseA</th>
										<th>Branch Remarks</th>
										<th>CSR Remarks</th>
										<th>Agent</th>
										<th>Time</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $sold_outside; ?>
								</tbody>
							</table>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>	
