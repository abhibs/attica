<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
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
	
	// INITIAL VARIABLES
	$h = 1; $bangalore = "";
	$i = 1;	$karnataka = "";
	$j = 1;	$chennai = "";
	$k = 1;	$tn = "";
	$l = 1;	$hyderabad = "";
	$m = 1;	$apt = "";
		
	$trans = mysqli_query($con, "SELECT b.branchName, b.city, b.state, b.branchName, t.id, t.billId, t.name, t.phone, t.type, t.grossW, t.netW, t.grossA, t.netA, t.amountPaid, t.date, t.time, t.comm, t.margin, t.status
	FROM trans t
	LEFT JOIN branch b ON t.branchId = b.branchId
	WHERE t.date = '$date' AND t.status = 'Approved'
	ORDER BY t.Id");
		
	while($row = mysqli_fetch_assoc($trans)){					
		if($row['city'] == 'Bengaluru'){
			$bangalore .= "<tr>".
			"<td>" . $h . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$h++;
		}
		else if($row['state'] == 'Karnataka' &&  $row['city'] != 'Bengaluru'){
			$karnataka .= "<tr>".
			"<td>" . $i . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$i++;
		}
		else if($row['city'] == 'Chennai'){
			$chennai .= "<tr>".
			"<td>" . $j . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$j++;
		}
		else if(($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry') && $row['city'] != 'Chennai'){
			$tn .= "<tr>".
			"<td>" . $k . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$k++;
		}
		else if($row['city'] == 'Hyderabad'){
			$hyderabad .= "<tr>".
			"<td>" . $l . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$l++;
		}
		else if(($row['state'] == 'Andhra Pradesh' ||  $row['state'] == 'Telangana') &&  $row['city'] != 'Hyderabad'){
			$apt .= "<tr>".
			"<td>" . $m . "</td>".
			"<td>" . $row['branchName'] . "</td>".
			"<td>" . $row['name'] . "</td>".
			"<td>" . $row['phone'] . "</td>".
			"<td>" . $row['grossW'] . "</td>".
			"<td>" . $row['netW'] . "</td>".
			"<td>" . $row['grossA'] . "</td>".
			"<td>" . $row['netA'] . "</td>".
			"<td>" . $row['amountPaid'] . "</td>".			
			"<td>" . $row['margin'] . "<br>(" . $row['comm'] . "%)</td>".			
			"<td>" . $row['type'] . "</td>".			
			"<td>" . $row['status'] . "</td>".			
			"<td>" . $row['date'] . "</td>".			
			"<td>" . $row['time'] . "</td>".						
			"<td><a target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i class='fa_Icon fa fa-file-pdf-o'></i></a></td>".
			"</tr>";
			$m++;
		}						
	}
	
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
	.table_td_waiting{
	color: #990000;
	}
	.table_td_external_link{
	color: #123C69;	
	font-size: 17px;
	}
	.fa_Icon{
	color: #123C69;
	font-weight: 600;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				
				<div class="panel-heading">
					<div class="col-sm-8">
						<h3><i class="fa fa-users" style='color:#990000'></i> ALL BILLED REPORT <span style='color:#990000'><?php echo " - ".$date; ?></span> </h3>
					</div>
					<div class="col-sm-4">
						<form action="" method="POST">
							<div class="row text-center" style="margin-left: -65px;margin-top: 5px;">
								<div class="col-md-9">
									<div class="input-group m-b">
										<span class="input-group-addon text-success">Date</span> 
										<input name="fromDate"  value="" type="date" class=" form-control" required />
									</div>
								</div>
								<div class="col-md-3">
									<input name="submitWalkinData" class="btn btn-primary btn-block" value="Submit" type="submit">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="tab" role="tabpanel">
					
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a href="#Bangalore" aria-controls="Bangalore" role="tab" data-toggle="tab"> Bangalore </a></li>
						<li><a href="#Karnataka" aria-controls="Karnataka" role="tab" data-toggle="tab"> Karnataka</a></li>
						<li><a href="#Chennai" aria-controls="Chennai" role="tab" data-toggle="tab"> Chennai</a></li>
						<li><a href="#Tamilnadu" aria-controls="Tamilnadu" role="tab" data-toggle="tab"> Tamil Nadu</a></li>
						<li><a href="#Hyderabad" aria-controls="Hyderabad" role="tab" data-toggle="tab"> Hyderabad</a></li>
						<li><a href="#aptg" aria-controls="aptg" role="tab" data-toggle="tab"> AP & TS</a></li>					
					</ul>
					
					<div class="tab-content tabs">
						
						<!--  BENGALURU  -->
						<div role="tabpanel" class="tab-pane fade in active" id="Bangalore">
							<div class="panel-body">
								<div class="col-sm-12 table-responsive">
									<table id="example1" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $bangalore;
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  KARNATAKA  -->
						<div role="tabpanel" class="tab-pane fade" id="Karnataka">
							<div class="panel-body">								
								<div class="col-sm-12 table-responsive">
									<table id="example2" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $karnataka;
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  CHENNAI  -->
						<div role="tabpanel" class="tab-pane fade" id="Chennai">
							<div class="panel-body">
								
								<div class="col-sm-12 table-responsive">
									<table id="example3" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $chennai;
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  TAMILNADU  -->
						<div role="tabpanel" class="tab-pane fade" id="Tamilnadu">
							<div class="panel-body">								
								<div class="col-sm-12 table-responsive">
									<table id="example4" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $tn;
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  HYDERABAD  -->
						<div role="tabpanel" class="tab-pane fade" id="Hyderabad">
							<div class="panel-body">								
								<div class="col-sm-12 table-responsive">
									<table id="call1" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $hyderabad;
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  ANDHRA PRADESH & TELANGANA  -->
						<div role="tabpanel" class="tab-pane fade" id="aptg">
							<div class="panel-body">								
								<div class="col-sm-12 table-responsive">
									<table id="call2" class="table table-bordered ">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>												
												<th>GrossW</th>
												<th>NetW</th>
												<th>GrossA</th>
												<th>NetA</th>
												<th>AmountPaid</th>
												<th>Margin</th>
												<th>Type</th>
												<th>Status</th>
												<th>Date</th>
												<th>Time</th>
												<th>Bill</th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $apt;
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
	</script>