<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
	include("header.php");
	include("menumaster.php");
} else if ($type == 'IssueHead') {
	include("header.php");
	include("menuIssueHead.php");
} else {
	include("logout.php");
}
include("dbConnection.php");

// if (isset($_POST['submitWalkinData'])) {
// 	$date = $_POST['fromDate'];
// 	$date = $_POST['toDate'];

// } else {
// 	$date = date('Y-m-d');
// }

if (isset($_POST['submitWalkinData'])) {
	$fromDate = $_POST['fromDate'];
	$toDate = $_POST['toDate'];
} else {
	$fromDate = $toDate = date('Y-m-d');
}

$sql = "";
// if ($type == 'Master') {
// 	$sql = "SELECT b.id AS branch_id, b.name AS branch_name, COUNT(e.id) AS enquiry_count
//     FROM branch b
//     LEFT JOIN walkin e ON b.id = e.branch_id
//     GROUP BY b.id, b.name";
// }
// else if ($type == 'IssueHead') {
// 	$sql = "SELECT b.branchName, b.city, b.state, e.customer, e.contact, e.type, e.idnumber, e.reg_type, e.date, e.time, e.status, e.extra, e.quotation
// 	FROM everycustomer e
// 	LEFT JOIN branch b ON e.branch=b.branchId
// 	WHERE e.date = '$date' AND e.status in ('0', 'Begin')
// 	ORDER BY e.Id ASC";
// }


// $sql =  "SELECT b.branchId,b.branchName,COUNT(e.id) AS Count FROM branch b LEFT JOIN walkin e ON b.branchId = e.branchId";
$sql =  "  SELECT 
        b.branchId, 
        b.branchName, 
        COUNT(e.id) AS Count 
    FROM 
        branch b 
    LEFT JOIN 
        walkin e ON b.branchId = e.branchId 
        AND e.date BETWEEN '$fromDate' AND '$toDate' 
        AND e.status = 0 
    WHERE 
        b.Status = 1   
    GROUP BY 
        b.branchId, b.branchName";

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
					<div class="col-sm-12">
						<!-- <h3><i class="fa fa-users" style='color:#990000'></i> ENQUIRY COUNT REPORT <span style='color:#990000'><?php echo " - " . $date; ?></span> </h3> -->
						<h3><i class="fa fa-users" style='color:#990000'></i> ENQUIRY COUNT REPORT
							<span style='color:#990000'>
								<?php echo " - " . $fromDate . " to " . $toDate; ?>
							</span>
						</h3>
					</div>
					<!-- <div class="col-sm-6">
						<form action="" method="POST">
							<div class="col text-center" style="margin-left: -65px;margin-top: 5px;">
								<div class="col-md-9">
									<div class="input-group m-b">
										<span class="input-group-addon text-success">Date</span> 
										<input name="fromDate"  value="" type="date" class=" form-control" required />
									</div>
								</div>
							<div class="col-sm-6">
								<label class="text-success">From Date</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
									<input type="date" class="form-control" id="fromDate" name="fromDate" required />
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">To Date</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
									<input type="date" class="form-control" id="toDate" name="toDate" required />
								</div>
							</div>
							<div class="col-sm-6">
								<input name="submitWalkinData" class="btn btn-primary btn-block" value="Submit" type="submit">
							</div>
						</form>
					</div> -->
					
					<div class="col-sm-12 text-right">
						<form action="" method="POST" class="form-inline" style="justify-content: flex-end;">
							<div class="form-group" style="width: 220px; margin-right: 10px;">
								<label class="text-success" for="fromDate" style="width: 100%; margin-bottom: 5px;">From Date</label>
								<div class="input-group" style="width: 100%;">
									<span class="input-group-addon">
										<i class="fa fa-calendar" style="color:#990000"></i>
									</span>
									<input type="date" class="form-control" id="fromDate" name="fromDate" required />
								</div>
							</div>

							<div class="form-group" style="width: 220px; margin-right: 10px;">
								<label class="text-success" for="toDate" style="width: 100%; margin-bottom: 5px;">To Date</label>
								<div class="input-group" style="width: 100%;">
									<span class="input-group-addon">
										<i class="fa fa-calendar" style="color:#990000"></i>
									</span>
									<input type="date" class="form-control" id="toDate" name="toDate" required />
								</div>
							</div>

							<div class="form-group" style="width: 220px;">
								<label style="visibility: hidden; display: block;">Submit</label>
								<button name="submitWalkinData" class="btn btn-primary btn-block" type="submit">
									Submit
								</button>
							</div>
						</form>
					</div>

				</div>
			</div>

			<div class="row content">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<div class="panel-tools">
								<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							</div>
							<!-- <h3><b><i class="fa_Icon fa fa-institution"></i> Branch Details &nbsp;</b></h3> -->
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table id="example2" class="table table-striped table-bordered table-hover">
									<thead class="theadRow">
										<tr>
											<th>SL</th>
											<th>Branch_ID</th>
											<th>Branch Name</th>
											<th>Count</th>
											<!-- <th>Branch Address</th> -->
											<!-- <th>Contact</th>
									<th>Email</th> -->
											<!-- <th>GST</th>   -->
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 1;
										// $sql = mysqli_query($con, "SELECT b.branchId, b.branchName, COUNT(e.id) AS Count FROM branch b LEFT JOIN walkin e ON b.branchId = e.branchId AND e.date = '$date' AND e.status=0 where b.Status=1   GROUP BY b.branchId, b.branchName");
										$sql = mysqli_query($con, "SELECT b.branchId, b.branchName, COUNT(e.id) AS Count FROM branch b LEFT JOIN walkin e ON b.branchId = e.branchId AND e.date BETWEEN '$fromDate' AND '$toDate'  WHERE b.Status = 1  GROUP BY b.branchId, b.branchName");
										// $sql = mysqli_query($con, "SELECT  b.branchId,  b.branchName, COUNT(e.id) AS Count  FROM   branch b LEFT JOIN walkin e  ON b.branchId = e.branchId WHERE  b.Status = 1 AND (e.date BETWEEN '$fromDate' AND '$toDate'    AND e.status = 0  OR e.id IS NULL   ) GROUP BY b.branchId, b.branchName");
										while ($row = mysqli_fetch_assoc($sql)) {
											echo "<tr>";
											echo "<td>" . $i .  "</td>";
											echo "<td>" . $row['branchId'] . "</td>";
											echo "<td>" . $row['branchName'] . "</td>";
											// echo "<td>" . $row['addr'] . "</td>";
											// echo "<td>" . $row['officeContact'] . "</td>";
											// echo "<td>" . $row['email'] . "</td>";
											// echo "<td>" . $row['gst'] . "</td>";
											echo "<td>" . $row['Count'] . "</td>";
											echo "</tr>";
											$i++;
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
