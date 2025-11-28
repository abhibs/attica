<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SocialMedia') {
		include("header.php");
		include("menuSocialMedia.php");
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
	else if($type=='Leads'){
	    include("header.php");
		include("menuLeads.php");
	}
	else {
		include("logout.php");
	}
    include("dbConnection.php");
	$date = date('Y-m-d');
    
	
$sql = "SELECT 
            COUNT(*) AS total_count,
            SUM(CASE WHEN status != 'done' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status != 'pending' THEN 1 ELSE 0 END) AS followup_count,
            SUM(CASE WHEN remarks = 'Visited' THEN 1 ELSE 0 END) AS visited_count,
			SUM(CASE WHEN DATE(date) = CURDATE() THEN 1 ELSE 0 END) AS today_count
        FROM enquiry";
$result = mysqli_query($con, $sql);
$count_row = mysqli_fetch_assoc($result);
$total_count = $count_row['total_count'];
$pending_count = $count_row['pending_count'];
$followup_count = $count_row['followup_count'];
$visited_count = $count_row['visited_count'];
$today_count = $count_row['today_count'];








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
	.dlt-button {
    background-color: transparent;
	color:red;
    border: none;
    padding: 3px 8px;
    cursor: pointer;
	}
</style>
<div id="wrapper">
	<div class="row content">
	    
	    <form action="" method="GET">
    <div class="col-sm-5">
        <h3><i class="trans_Icon fa fa-edit"></i> Website Leads <span style='color:#990000'> - <?php echo $date; ?></span></h3>
    </div>
    <div class="col-sm-3">
        <div class="input-group">
            <span class="input-group-addon text-success">From</span>
            <input type="date" class="form-control" name="from" required>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="input-group">
            <span class="input-group-addon text-success">To</span>
            <input type="date" class="form-control" name="to" required>
        </div>
    </div>
    <div class="col-sm-1">
        <button class="btn btn-success btn-block" name="expenseDetailsSubmit"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
    </div>
</form>
		<div class="col-lg-12">
		    
		    <br><br>
		
				<div class="hpanel">
		
				<div class="tab" role="tabpanel">
					
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a href="#Pending" aria-controls="Pending" role="tab" data-toggle="tab"> Pending </a></li>
						<li><a href="#FollowUp" aria-controls="FollowUp" role="tab" data-toggle="tab"> FollowUp</a></li>
						<li><a href="#ComingTommrow" aria-controls="ComingTommrow" role="tab" data-toggle="tab"> Coming Tommrow</a></li>
						<li><a href="#Planing_To_Visit" aria-controls="Planing_To_Visit" role="tab" data-toggle="tab">Planing_To_Visit </a></li>
						<li><a href="#Visited" aria-controls="Visited" role="tab" data-toggle="tab"> Visited</a></li>
					
					</ul>
					
					<div class="tab-content tabs">
						
						<div role="tabpanel" class="tab-pane fade in active" id="Pending">
							<div class="panel-body">								
								<div id="leads_data"></div>
								<label class="col-sm-12" style="margin-top: 10px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example1" class="table table-bordered">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40"
															class="fa_Icon fa fa-sort-numeric-asc"></i></th>
													<th>Customer Name</th>
													<th>Contact number</th>
													<th>Type</th>
													<th>State</th>
													<th>Date</th>
													<th>Time</th>
													<?php if ($type != 'Master') { ?>
													<th class="text-center"><i class="fa fa-edit"></i></th>
													<?php } ?>
													
													<?php if ($type == 'SocialMedia') { ?>
													<th class="text-center"><i class="fa fa-trash"></i></th>
													<?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
$i = 1;



// Default 'from' and 'to' dates to current date if not set
$fromDate = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d');
$toDate = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

$sql = mysqli_query($con, "SELECT *
                           FROM enquiry 
                           WHERE status='Pending' AND date BETWEEN '$fromDate' AND '$toDate'
                           ORDER BY id DESC ");

// $sql = mysqli_query($con, "SELECT *
//                           FROM enquiry 
//                           WHERE status='Pending' and date='$date'
//                           ORDER BY id DESC ");


while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>";
    echo "<td>".$i."</td>";
    echo "<td>".$row['name']."</td>";
    echo "<td>".$row['mobile']."</td>";
    echo "<td>".$row['type']."</td>";
    echo "<td>".$row['state']."</td>";
    echo "<td>".$row['date']."</td>";
    echo "<td>".$row['time']."</td>";
    
    
	if ($type != 'Master') {
    echo '<td class="text-center">';
    echo '<button class="block-button btn-sm openModal" data-toggle="modal" data-target="#myModal'.$i.'"><i class="fa fa-edit fa-lg"></i></button>';
    echo '</td>';
	}
	
	if ($type == 'SocialMedia') {
    echo '<td class="text-center">';
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="issueId" value="' . $row['id'] . '">';
    echo '<button class="dlt-button" type="submit" name="rejectBtn"><i class="fa fa-trash fa-lg" style="color:red;font-size:18px;"></i></button>';
    echo '</form>';
    echo '</td>';
    }
	
	
	
	
    echo "</tr>";

echo '<div class="modal fade" id="myModal' . $i . '" tabindex="-1" role="dialog" aria-labelledby="editModalLabel' . $i . '" aria-hidden="true">';
echo '<div class="modal-dialog" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header" style="background-color: #123C69; color: #fff; padding: 12px;">';
echo '<h2 class="text-success" id="editModalLabel' . $i . '" style="color: white; font-size: 14px; margin-bottom: 0;">Update Remarks</h2>';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
echo '<span aria-hidden="true">&times;</span>';
echo '</button>';
echo '</div>';
echo '<div class="modal-body">';
echo '<form method="POST" action="">'; 
echo '<input type="hidden" name="id" value="' . $row['id'] . '">';


echo '<div class="form-group row">';
echo '<div class="col-sm-6">';
echo '<label for="name" class="col-sm-6 col-form-label">Customer Name:</label>';
echo '<input type="text" class="form-control" id="name" name="name" value="' . $row['name'] . '" readonly>';
echo '</div>';
echo '<div class="col-sm-6">';
echo '<label for="mobile" class="col-sm-6 col-form-label">Contact Number:</label>';
echo '<input type="text" class="form-control" id="mobile" name="mobile" value="' . $row['mobile'] . '" readonly>';
echo '</div>';
echo '</div>';


echo '<div class="form-group row">';
echo '<div class="col-sm-6">';
echo '<label for="type" class="col-sm-6 col-form-label">Type:</label>';
echo '<input type="text" class="form-control" id="type" name="type" value="' . $row['type'] . '" readonly>';
echo '</div>';
echo '<div class="col-sm-6">';
echo '<label for="state" class="col-sm-6 col-form-label">State:</label>';
echo '<input type="text" class="form-control" id="state" name="state" value="' . $row['state'] . '" readonly>';
echo '</div>';
echo '</div>';

echo '<div class="form-group row">';
echo '<div class="col-sm-6">';
echo '<label for="Remarks" class="col-sm-6 col-form-label">Select Remarks:</label>';
echo '<select name="remarks" id="remarks" class="form-control" aria-label="Remarks" required>';
echo '<option selected disabled value="">Select Remarks</option>';
echo '<option value="Comming Tomorrow">Coming Tomorrow</option>';
echo '<option value="Planning To Visit">Planning To Visit</option>';
echo '<option value="Not Interested">Not Interested</option>';
echo '<option value="Not Reachable">Not Reachable</option>';
echo '<option value="Duplicate Number">Duplicate Number</option>';
echo '<option value="Wrong Number">Wrong Number</option>';
echo '<option value="RNR">RNR</option>';
echo '<option value="Busy">Busy</option>';
echo '<option value="Pending">Pending</option>';
echo '<option value="Enquiry">Enquiry</option>';
echo '<option value="Visited">Visited</option>';
echo '</select>';
echo '</div>';

echo '<div class="col-sm-6">';
echo '<label for="followup" class="col-sm-6 col-form-label">Planning to visit</label>';
echo '<input type="date" class="form-control" id="followup" name="followup">';
echo '</div>';
echo '</div>';

echo '<div class="form-group row">';
echo '<div class="col-sm-12">';
echo '<label for="comments" class="col-sm-6 col-form-label">Update Comments:</label>';
echo '<textarea class="form-control" id="comments" name="comments" rows="2" required></textarea>';
echo '</div>';
echo '</div>';


echo '<div class="form-group row">';
echo '<div class="col-sm-12 text-right">';
echo '<button class="btn btn-primary" type="submit" name="submit" style="padding: 12px 24px;">Submit</button>';
echo '</div>';
echo '</div>';

echo '</form>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';


    $i++;
}
if (isset($_POST['submit'])) {
    $remarks = $_POST['remarks'];
	$comments = $_POST['comments'];
    $id = $_POST['id'];
    $followup = $_POST['followup'];
    $currentDate = date('Y-m-d');
    $status = "Done";

   
    if (!empty($followup)) {
        
        if (strtotime($followup) === false) {
            
            echo "Invalid date format";
            exit; 
        } else {
           
            $updateComment = "UPDATE enquiry SET remarks='$remarks',comments='$comments', updateDate='$followup', followup='$currentDate', status='$status' WHERE id='$id'";
        }
    } else {
        
        $updateComment = "UPDATE enquiry SET remarks='$remarks', followup='$currentDate',comments='$comments', status='$status' WHERE id='$id'";
    }

    $res2 = mysqli_query($con, $updateComment);

    if ($res2) {
        echo "<script>alert('Remarks Updated')</script>";
        echo "<script>setTimeout(\"location.href = 'import1.php';\", 150);</script>";
        exit;
    } else {
        echo "<script>setTimeout(\"location.href = 'import1.php'\", 150);</script>";
    }
}


if (isset($_POST['rejectBtn'])) {
    $issueId = $_POST['issueId'];

    $deleteSql = "DELETE FROM enquiry  WHERE id='$issueId'";

    if (mysqli_query($con, $deleteSql)) {
        echo '<script>alert("Customer Data Deleted");</script>';
        echo "<script>setTimeout(\"location.href = 'import1.php';\", 150);</script>";
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}
?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  FollowUp  -->
						<div role="tabpanel" class="tab-pane fade" id="FollowUp">
							<div class="panel-body">								
								<div id="FollowUp_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example2" class="table table-bordered">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40"
															class="fa_Icon fa fa-sort-numeric-asc"></i></th>
													<th>Customer Name</th>
													<th>Contact number</th>
													<th>Type</th>
													<th>State</th>
													<th>date</th>
													<th>Followup_date</th>
													
													<th>Remarks</th>
													<th>Comments</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$i = 1;
												$sql = mysqli_query($con, "SELECT *
                           FROM enquiry 
                           WHERE status!='Pending' 
                           ORDER BY id DESC");

												while($row = mysqli_fetch_assoc($sql)) {
													echo "<tr>";
													echo "<td>".$i."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['mobile']."</td>";
													echo "<td>".$row['type']."</td>";
													echo "<td>".$row['state']."</td>";
													echo "<td>".$row['date']."</td>";
													echo "<td>".$row['followup']."</td>";
													
													echo "<td>".$row['remarks']."</td>";
													echo "<td>".$row['comments']."</td>";
													echo "</tr>";

													$i++;
												}
												?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  ComingTommrow  -->
						<div role="tabpanel" class="tab-pane fade" id="ComingTommrow">
							<div class="panel-body">
								<div id="ComingTommrow_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example3" class="table table-bordered">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40"
															class="fa_Icon fa fa-sort-numeric-asc"></i></th>
													<th>Customer Name</th>
													<th>Contact number</th>
													<th>Type</th>
													<th>State</th>
													<th>date</th>									
													<th>Remarks</th>
													<th>Comments</th>
													<th>Followup_date</th>
											</tr>
										</thead>
										<tbody>
											<?php
															$i = 1;
												$sql = mysqli_query($con, "SELECT *
                           FROM enquiry 
                           WHERE status!='Pending' and remarks='Comming Tomorrow'
                           ORDER BY id DESC");

												while($row = mysqli_fetch_assoc($sql)) {
													echo "<tr>";
													echo "<td>".$i."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['mobile']."</td>";
													echo "<td>".$row['type']."</td>";
													echo "<td>".$row['state']."</td>";
													echo "<td>".$row['date']."</td>";				
													echo "<td>".$row['remarks']."</td>";
													echo "<td>".$row['comments']."</td>";
													echo "<td>".$row['followup']."</td>";
													echo "</tr>";

													$i++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  Planing_To_Visit  -->
						<div role="tabpanel" class="tab-pane fade" id="Planing_To_Visit">
							<div class="panel-body">
								<div id="Planing_To_Visit_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example4" class="table table-bordered">
										<thead>
											<tr>
											<th class="text-center"><i style="color:#ffcf40"
															class="fa_Icon fa fa-sort-numeric-asc"></i></th>
											<th>Customer Name</th>
													<th>Contact number</th>
													<th>Type</th>
													<th>State</th>
													<th>date</th>
													<th>Followup_date</th>													
													<th>Remarks</th>
													<th>visit Date</th>													
													<th>Comments</th>
											</tr>
										</thead>
										<tbody>
											<?php
															$i = 1;
												$sql = mysqli_query($con, "SELECT *
                           FROM enquiry 
                           WHERE status!='Pending' and remarks='Planning To Visit'
                           ORDER BY id DESC");

												while($row = mysqli_fetch_assoc($sql)) {
													echo "<tr>";
													echo "<td>".$i."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['mobile']."</td>";
													echo "<td>".$row['type']."</td>";
													echo "<td>".$row['state']."</td>";
													echo "<td>".$row['date']."</td>";
													echo "<td>".$row['followup']."</td>";													
													echo "<td>".$row['remarks']."</td>";
													echo "<td>".$row['updateDate']."</td>";
													echo "<td>".$row['comments']."</td>";
													echo "</tr>";

													$i++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<!--  Visited  -->
						<div role="tabpanel" class="tab-pane fade" id="Visited">
							<div class="panel-body">
								<div id="hyderabad_data"></div>
								<label class="col-sm-12" style="margin-top: 20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="call1" class="table table-bordered">
										<thead>
											<tr>
											<th class="text-center"><i style="color:#ffcf40"
															class="fa_Icon fa fa-sort-numeric-asc"></i></th>
											<th>Customer Name</th>
													<th>Contact number</th>
													<th>Type</th>
													<th>State</th>
													<th>date</th>
													<th>Followup_date</th>													
													<th>Remarks</th>
													<th>visit date</th>													
													<th>Comments</th>
											</tr>
										</thead>
										<tbody>
											<?php
															$i = 1;
												$sql = mysqli_query($con, "SELECT *
                           FROM enquiry 
                           WHERE status!='Pending' and remarks='Visited'
                           ORDER BY id DESC");

												while($row = mysqli_fetch_assoc($sql)) {
													echo "<tr>";
													echo "<td>".$i."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['mobile']."</td>";
													echo "<td>".$row['type']."</td>";
													echo "<td>".$row['state']."</td>";
													echo "<td>".$row['date']."</td>";
													echo "<td>".$row['followup']."</td>";													
													echo "<td>".$row['remarks']."</td>";
													echo "<td>".$row['updateDate']."</td>";
													echo "<td>".$row['comments']."</td>";
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
		</div>
	</div>
	<div style="clear:both"></div>
	<script>
	
	const leads_data = document.getElementById("leads_data");
		leads_data.innerHTML = '<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Total Leads - <?php echo $total_count; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">Pending - <?php echo $pending_count; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">followup - <?php echo $followup_count; ?></button></div><div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">Visited - <?php echo $visited_count; ?></button></div><div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Today Leads - <?php echo $today_count; ?></button></div>';
	
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