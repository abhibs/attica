<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if ($type == 'AccHead') {
		include("header.php");
		include("menuaccHeadPage.php");
	}
	else if ($type == 'IssueHead') {
		include("header.php");
		include("menuIssueHead.php");
	}
	else if($type == 'Call Centre'){
	    include("header.php");
		include("menuCall.php");
	}
	else if($type=='Issuecall'){
	    include("header.php");
		include("menuissues.php");
	}
	else if ($type == 'ZonalMaster') {
		include("header.php");
		include("menuzonalMaster.php");
	}
	else {
		include("logout.php");
	}
    include("dbConnection.php");

    
	if(isset($_POST['submitWalkinData'])) {
		$date = $_POST['fromDate'];
	}
	else {
		$date = date('Y-m-d');
	}
	
	$data_count = [
	"bangalore_physical" => 0, "bangalore_release" => 0, "bangalore_with" => 0, "bangalore_without" => 0, "bangalore_billed" => 0,
	"karnataka_physical" => 0, "karnataka_release" => 0, "karnataka_with" => 0, "karnataka_without" => 0, "karnataka_billed" => 0,
	
	"chennai_physical" => 0, "chennai_release" => 0, "chennai_with" => 0, "chennai_without" => 0, "chennai_billed" => 0,
	"tn_physical" => 0, "tn_release" => 0, "tn_with" => 0, "tn_without" => 0, "tn_billed" => 0, "tn_billed" => 0,
	
	"hyderabad_physical" => 0, "hyderabad_release" => 0, "hyderabad_with" => 0, "hyderabad_without" => 0, "hyderabad_billed" => 0,
	"apt_physical" => 0, "apt_release" => 0, "apt_with" => 0, "apt_without" => 0, "apt_billed" => 0
	];
	
	// TOTAL ENQUIRY DATA
	$walkin = mysqli_query($con,"SELECT  w.*, b.branchName, b.city, b.state, t.phone, e.name AS zonal_name, vm_emp.name AS vmname
	FROM walkin w 
	LEFT JOIN trans t ON (w.mobile = t.phone AND t.date='$date' AND t.status='Approved')
	LEFT JOIN branch b ON w.branchId = b.branchId
	LEFT JOIN employee e ON w.ezviz_vc = e.empId
	LEFT JOIN vmagent vma ON FIND_IN_SET(w.branchId, REPLACE(vma.branch, ' ', '')) > 0
	LEFT JOIN employee vm_emp ON vma.agentId = vm_emp.empId
	WHERE w.date='$date'
	ORDER BY w.id DESC");
	
	$result = mysqli_fetch_all($walkin, MYSQLI_ASSOC);
	$totalLength = count($result);
	
?>
<style>
	.tab .nav-tabs {
	padding: 0;
	margin: 0;
	border: none;
	}
	.tab .nav-tabs li a {
	color: #123C69;
	background: #E3E3E3;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover {
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius: 3px;
	}
	.tab .nav-tabs li a:before {
	content: "";
	background: #E3E3E3;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s, height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before {
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab-content h4 {
	color: #123C69;
	font-weight: 500;
	}
	@media only screen and (max-width: 479px) {
	.tab .nav-tabs {
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li {
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a {
	margin: 0 0 5px;
	}
	}
	#wrapper {
	background-color: #E3E3E3;
	}
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	thead {
	text-transform: uppercase;
	background-color: #123C69;
	font-size: 10px;
	}
	thead tr {
	color: #f2f2f2;
	}
	.btn-primary {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 12px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.text-success {
	font-weight: 600;
	color: #123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">	
				<div class="panel-heading">
					<div class="col-sm-8">
						<h3>
							<i class="trans_Icon fa fa-edit" ></i> 
							ENQUIRY REPORT 
							<span style='color:#990000'><?php echo " - ".$date; ?></span>
						</h3>
					</div>
					<div class="col-sm-3" style="margin-top: 5px;">
						<form action="" method="POST">
							<div class="input-group">
								<input name="fromDate"  value="" type="date" class=" form-control" required />
								<span class="input-group-btn"> 
									<input name="submitWalkinData" class="btn btn-primary btn-block" value="Search" type="submit" style="font-size: 11px;">
								</span>
							</div>
						</form>
					</div>
					<div class="col-sm-1" style="margin-top: 5px;">
						<form action="export.php" enctype="multipart/form-data" method="post">
							<input type="hidden" name="walkin_date" value="<?php echo $date; ?>">
							<input name="exportWalkinData" class="btn btn-primary btn-block" value="Export" type="submit" style="font-size: 11px;">
						</form> 
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-top: 15px;">
				<div class="tab" role="tabpanel">
					
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a href="#Bangalore" aria-controls="Bangalore" role="tab" data-toggle="tab"> Bangalore </a></li>
						<li><a href="#Karnataka" aria-controls="Karnataka" role="tab" data-toggle="tab"> Karnataka</a></li>
						<li><a href="#Chennai" aria-controls="Chennai" role="tab" data-toggle="tab"> Chennai</a></li>
						<li><a href="#Tamilnadu" aria-controls="Tamilnadu" role="tab" data-toggle="tab"> Tamil Nadu</a></li>
						<li><a href="#Hyderabad" aria-controls="Hyderabad" role="tab" data-toggle="tab"> Hyderabad</a></li>
						<li><a href="#aptg" aria-controls="aptg" role="tab" data-toggle="tab"> AP & TS</a></li>
						<li><a href="#billed" aria-controls="billed" role="tab" data-toggle="tab" class='all-billed'> Billed</a></li>
						<li><a href="#Rejected" aria-controls="billed" role="tab" data-toggle="tab"> Rejected</a></li>
					</ul>
					
					<div class="tab-content tabs">
						
						<!--  BENGALURU  -->
						<div role="tabpanel" class="tab-pane fade in active" id="Bangalore">
							<div class="panel-body">
								<div id="bangalore_data"></div>
								<label class="col-sm-12" style="margin-top: 10px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example1" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['city'] == 'Bengaluru' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["bangalore_physical"]++ : $data_count["bangalore_release"]++;
														($row['havingG'] == 'with')? $data_count["bangalore_with"]++ : $data_count["bangalore_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
													        "<td>" . $row['mobile'] . "</td>". 
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  KARNATAKA  -->
						<div role="tabpanel" class="tab-pane fade" id="Karnataka">
							<div class="panel-body">
								<div id="karnataka_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example2" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['state'] == 'Karnataka' &&  $row['city'] != 'Bengaluru' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["karnataka_physical"]++ : $data_count["karnataka_release"]++;
														($row['havingG'] == 'with')? $data_count["karnataka_with"]++ : $data_count["karnataka_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  CHENNAI  -->
						<div role="tabpanel" class="tab-pane fade" id="Chennai">
							<div class="panel-body">
								<div id="chennai_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example3" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['city'] == 'Chennai' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["chennai_physical"]++ : $data_count["chennai_release"]++;
														($row['havingG'] == 'with')? $data_count["chennai_with"]++ : $data_count["chennai_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  TAMILNADU  -->
						<div role="tabpanel" class="tab-pane fade" id="Tamilnadu">
							<div class="panel-body">
								<div id="tn_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example4" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry') && $row['city'] != 'Chennai' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["tn_physical"]++ : $data_count["tn_release"]++;
														($row['havingG'] == 'with')? $data_count["tn_with"]++ : $data_count["tn_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  HYDERABAD  -->
						<div role="tabpanel" class="tab-pane fade" id="Hyderabad">
							<div class="panel-body">
								<div id="hyderabad_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="call1" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['city'] == 'Hyderabad' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["hyderabad_physical"]++ : $data_count["hyderabad_release"]++;
														($row['havingG'] == 'with')? $data_count["hyderabad_with"]++ : $data_count["hyderabad_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  ANDHRA PRADESH & TELANGANA  -->
						<div role="tabpanel" class="tab-pane fade" id="aptg">
							<div class="panel-body">
								<div id="andhra_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="call2" class="table table-bordered ">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>ECR</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if(($row['state'] == 'Andhra Pradesh' ||  $row['state'] == 'Telangana') &&  $row['city'] != 'Hyderabad' && $row['issue'] !== "Rejected" && $row['mobile'] != $row['phone']){
														($row['gold'] == 'physical')? $data_count["apt_physical"]++ : $data_count["apt_release"]++;
														($row['havingG'] == 'with')? $data_count["apt_with"]++ : $data_count["apt_without"]++;
														
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['ecr'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  BILLED  -->
						<div role="tabpanel" class="tab-pane fade" id="billed">
							<div class="panel-body">							
								<div id="billed_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="call3" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['mobile'] == $row['phone']){
														if($row['city'] == 'Bengaluru'){
															$data_count["bangalore_billed"]++;
															$billed = "<tr class='billed' data-region='Bengaluru' style='visibility: visible;'>";
														}
														else if($row['state'] == 'Karnataka' &&  $row['city'] != 'Bengaluru'){
															$data_count["karnataka_billed"]++;
															$billed = "<tr class='billed' data-region='Karnataka' style='visibility: visible;'>";
														}
														else if($row['city'] == 'Chennai'){
															$data_count["chennai_billed"]++;
															$billed = "<tr class='billed' data-region='Chennai' style='visibility: visible;'>";
														}
														else if(($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry') && $row['city'] != 'Chennai'){
															$data_count["tn_billed"]++;
															$billed = "<tr class='billed' data-region='Tamilnadu' style='visibility: visible;'>";
														}
														else if($row['city'] == 'Hyderabad'){
															$data_count["hyderabad_billed"]++;
															$billed = "<tr class='billed' data-region='Hyderabad' style='visibility: visible;'>";
														}
														else if(($row['state'] == 'Andhra Pradesh' ||  $row['state'] == 'Telangana') &&  $row['city'] != 'Hyderabad'){
															$data_count["apt_billed"]++;
															$billed = "<tr class='billed' data-region='APT' style='visibility: visible;'>";
														}													
														
														echo $billed;
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
						
						<!--  REJECTED  -->
						<div role="tabpanel" class="tab-pane fade" id="Rejected">
							<div class="panel-body">
								<div class="col-sm-12 table-responsive">
									<table class="table table-bordered ">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Zonal Name</th>
												<th>VM Name</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>Having Gold</th>
												<th>Metal</th>
												<th>GrossW</th>
												<th>ReleaseA</th>
												<th>Rate</th>
												<th>Branch Remark</th>
												<th>Zonal Remark</th>
												<th>Disposition</th>
												<th>CSR Remark</th>
												<th>Agent Name</th>
												<th>Bills</th>
												<th>Date</th>
												<th>Time</th>
												<th><span class="fa fa-edit"></span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['issue'] == "Rejected"){
														echo ($row['bills'] > 0) ? "<tr style='color: red'>" : "<tr>";
														echo "<td>" . $i . "</td>".
														"<td>" . $row['branchName'] . "</td>".
														"<td>" . $row['zonal_name'] . "</td>".
														"<td>" . $row['vmname'] . "</td>".
														"<td>" . $row['name'] . "</td>".
														"<td>" . $row['mobile'] . "</td>".
														"<td>" . $row['gold'] . "</td>".
														"<td>" . $row['havingG'] . "</td>".
														"<td>" . $row['metal'] . "</td>".
														"<td>" . $row['gwt'] . "</td>".
														"<td>" . $row['ramt'] . "</td>".
														"<td>" . $row['quot_rate'] . "</td>".
														"<td>" . $row['remarks'] . "</td>".
														"<td>" . $row['zonal_remarks'] . "</td>".
														"<td>" . $row['issue'] . "</td>".
														"<td>" . $row['comment'] . "</td>".
														"<td>" . $row['agent_id'] . "</td>".
														"<td>" . $row['bills'] . "</td>".
														"<td>" . $row['date'] . "</td>".
														"<td>" . $row['time'] . "</td>".
														"<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>".
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
	<div style="clear:both"></div>
	<script>
		
		const bangalore_data = document.getElementById("bangalore_data");
		bangalore_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["bangalore_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["bangalore_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["bangalore_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["bangalore_without"]; ?></button></div>';
		
		
		const karnataka_data = document.getElementById("karnataka_data");
		karnataka_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["karnataka_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["karnataka_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["karnataka_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["karnataka_without"]; ?></button></div>';
		
		const chennai_data = document.getElementById("chennai_data");
		chennai_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["chennai_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["chennai_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["chennai_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["chennai_without"]; ?></button></div>';
		
		const tn_data = document.getElementById("tn_data");
		tn_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["tn_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["tn_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["tn_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["tn_without"]; ?></button></div>';
		
		const hyderabad_data = document.getElementById("hyderabad_data");
		hyderabad_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["hyderabad_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["hyderabad_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["hyderabad_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["hyderabad_without"]; ?></button></div>';
		
		const andhra_data = document.getElementById("andhra_data");
		andhra_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Physical - <?php echo $data_count["apt_physical"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Release - <?php echo $data_count["apt_release"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">With Gold - <?php echo $data_count["apt_with"]; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Without Gold - <?php echo $data_count["apt_without"]; ?></button></div>';
		
		const billed_data = document.getElementById("billed_data");
		billed_data.innerHTML = '<div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="Bengaluru">Bangalore - <?php echo $data_count["bangalore_billed"]; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="Karnataka">Karnataka - <?php echo $data_count["karnataka_billed"]; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="Chennai">Chennai - <?php echo $data_count["chennai_billed"]; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="Tamilnadu">Tamilnadu - <?php echo $data_count["tn_billed"]; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="Hyderabad">Hyderabad - <?php echo $data_count["hyderabad_billed"]; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success state-select" type="button" data-state="APT">AP & T - <?php echo $data_count["apt_billed"]; ?></button></div>';
		
		
	</script>
	<script>
		$(document).ready(()=>{
			const billed = document.querySelectorAll(".billed");
			
			const state_select = document.querySelectorAll(".state-select");
			state_select.forEach((button)=>{
				button.addEventListener('click', (e)=>{
					const region = button.dataset.state;
					billed.forEach((row)=>{
						if(row.dataset.region == region){
							row.style.visibility = 'visible';
						}
						else{
							row.style.visibility = 'collapse';
						}
					});
				});
			});
			
			const all_billed = document.querySelector("a[class=all-billed]");
			all_billed.addEventListener("click", ()=>{
				billed.forEach((row)=>{
					row.style.visibility = 'visible';
				});
			});
		});
	</script>
	<?php include("footer.php"); ?>	
	<script>
		$('#call1').dataTable( {
            "ajax": '',
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
            buttons: [
			{extend: 'copy',className: 'btn-sm'},
			{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
			{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
			{extend: 'print',className: 'btn-sm'}
            ]
		});
		$('#call2').dataTable( {
            "ajax": '',
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
            buttons: [
			{extend: 'copy',className: 'btn-sm'},
			{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
			{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
			{extend: 'print',className: 'btn-sm'}
			]
		});
		$('#call3').dataTable( {
            paging: false,
			"ajax": '',
			dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
			"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
			buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
			]
		});
	</script>
