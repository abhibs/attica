<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
}
else if ($type == 'IssueHead') {
	include("header.php");
	include("menuIssueHead.php");
} else {
	include("logout.php");
}
include("dbConnection.php");

if (isset($_POST['submitWalkinData'])) {
	$date = $_POST['fromDate'];
} else {
	$date = date('Y-m-d');
}

$sql = "";
if ($type == 'Master') {
	$sql = "SELECT b.branchName, b.city, b.state, e.customer, e.contact, e.type, e.issue_remark, e.idnumber, e.reg_type, e.date, e.time, e.status, e.extra, e.quotation
		FROM everycustomer e
		LEFT JOIN branch b ON e.branch=b.branchId
		WHERE e.date = '$date'
		ORDER BY (CASE WHEN e.status in ('0', 'Begin') THEN 0 ELSE 1 END), e.Id ASC";
} else if ($type == 'IssueHead') {
	$sql = "SELECT b.branchName, b.city, b.state, e.customer, e.contact, e.type, e.idnumber, e.reg_type, e.date, e.time, e.status, e.extra, e.quotation
		FROM everycustomer e
		LEFT JOIN branch b ON e.branch=b.branchId
		WHERE e.date = '$date' AND e.status in ('0', 'Begin')
		ORDER BY e.Id ASC";
}
$everyCustomer = mysqli_query($con, $sql);
$result = mysqli_fetch_all($everyCustomer, MYSQLI_ASSOC);
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

	.table_td_waiting {
		color: #990000;
	}

	.table_td_reg {
		color: #840bde;
	}

	.table_td_external_link {
		color: #123C69;
		font-size: 17px;
	}

	.table-responsive .row {
		margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">

				<div class="panel-heading">
					<div class="col-sm-8">
						<h3><i class="fa fa-users" style='color:#990000'></i> ALL CUSTOMER REPORT <span style='color:#990000'><?php echo " - " . $date; ?></span> </h3>
					</div>
					<div class="col-sm-4">
						<form action="" method="POST">
							<div class="row text-center" style="margin-left: -65px;margin-top: 5px;">
								<div class="col-md-9">
									<div class="input-group m-b">
										<span class="input-group-addon text-success">Date</span>
										<input name="fromDate" value="" type="date" class=" form-control" required />
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
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if ($row['city'] == 'Bengaluru') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}
													echo "<td>" . $row['issue_remark'] . "</td>";
													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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
								<div class="col-sm-12 table-responsive">
									<table id="example2" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if ($row['state'] == 'Karnataka' &&  $row['city'] != 'Bengaluru') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}
													echo "<td>" . $row['issue_remark'] . "</td>";

													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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

								<div class="col-sm-12 table-responsive">
									<table id="example3" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if ($row['city'] == 'Chennai') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}
													echo "<td>" . $row['issue_remark'] . "</td>";

													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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
								<div class="col-sm-12 table-responsive">
									<table id="example4" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if (($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry') && $row['city'] != 'Chennai') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}
													echo "<td>" . $row['issue_remark'] . "</td>";

													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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
								<div class="col-sm-12 table-responsive">
									<table id="call1" class="table table-bordered">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if ($row['city'] == 'Hyderabad') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}
													echo "<td>" . $row['issue_remark'] . "</td>";

													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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
								<div class="col-sm-12 table-responsive">
									<table id="call2" class="table table-bordered ">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Name</th>
												<th>Contact</th>
												<th>Type</th>
												<th>GrossW</th>
												<th>Count</th>
												<th>Hallmark</th>
												<th>WithMetal</th>
												<th>RelAmount</th>
												<th>Status</th>
												<th>Issue Remark</th>
												<th>Date</th>
												<th>Time</th>
												<th>Quot</th>
												<th>Reg_By</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$k = 0;
											$i = 1;
											while ($k < $totalLength) {
												$row = $result[$k];
												if (($row['state'] == 'Andhra Pradesh' ||  $row['state'] == 'Telangana') &&  $row['city'] != 'Hyderabad') {
													$json = json_decode($row['extra'], true);
													echo "<td>" . $i . "</td>" .
														"<td>" . $row['branchName'] . "</td>" .
														"<td>" . $row['customer'] . "</td>" .
														"<td>" . $row['contact'] . "</td>" .
														"<td>" . $row['type'] . "</td>" .
														"<td>" . $json['GrossW'] . "</td>" .
														"<td>" . $json['itemCount'] . "</td>" .
														"<td>" . $json['Hallmark'] . "</td>" .
														"<td>" . $json['With'] . "</td>";

													echo ($row['type'] == 'release') ? "<td>" . $json['RelAmount'] . "<br>Slips: " . $json['RelSlips'] . "</td>" : "<td></td>";

													if ($row['status'] == 'Begin') {
														echo "<td class='table_td_reg'>Registered</td>";
													} else if ($row['status'] == '0') {
														echo "<td class='table_td_waiting'>Waiting</td>";
													} else {
														echo "<td>" . $row['status'] . "</td>";
													}

													echo "<td>" . $row['issue_remark'] . "</td>";

													echo "<td>" . $row['date'] . "</td>" .
														"<td>" . $row['time'] . "</td>";

													if ($row['quotation'] != '') {
														$q = json_decode($row['quotation'], true);
														echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $q['image'] . "'><span class='fa fa-external-link'></span></a></td>";
													} else {
														echo "<td></td>";
													}

													echo "<td>" . $row['reg_type'] . "</td>";
													echo "</tr>";
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
		<th>Issue Remark</th>

	</div>
	<?php include("footer.php"); ?>
	<script>
		$('#call1').dataTable({
			"ajax": '',
			dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
			"lengthMenu": [
				[10, 25, 50, 100, 250, -1],
				[10, 25, 50, 100, 250, "All"]
			],
			buttons: [{
					extend: 'copy',
					className: 'btn-sm'
				},
				{
					extend: 'csv',
					title: 'ExportReport',
					className: 'btn-sm'
				},
				{
					extend: 'pdf',
					title: 'ExportReport',
					className: 'btn-sm'
				},
				{
					extend: 'print',
					className: 'btn-sm'
				}
			]
		});
		$('#call2').dataTable({
			"ajax": '',
			dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
			"lengthMenu": [
				[10, 25, 50, 100, 250, -1],
				[10, 25, 50, 100, 250, "All"]
			],
			buttons: [{
					extend: 'copy',
					className: 'btn-sm'
				},
				{
					extend: 'csv',
					title: 'ExportReport',
					className: 'btn-sm'
				},
				{
					extend: 'pdf',
					title: 'ExportReport',
					className: 'btn-sm'
				},
				{
					extend: 'print',
					className: 'btn-sm'
				}
			]
		});
	</script>
