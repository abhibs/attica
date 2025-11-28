<?php
    session_start();
	error_reporting(E_ERROR | E_PARSE);
    $type = $_SESSION['usertype'];
    if($type == 'Master'){
        include("header.php");
		include("menumaster.php");
    }
    else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else if($type == 'AccHead'){
	    include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type == 'Accounts'){
	    include("header.php");
	    include("menuacc.php");
	}
	else if($type == 'Accounts IMPS'){
	    include("header.php");
		include("menuimpsAcc.php");
	}
	else if($type == 'Expense Team'){
	    include("header.php");
		include("menuexpense.php");
	}	
	else if($type == 'ApprovalTeam'){
		include("header.php");
	    include("menuapproval.php");
	}
	else if($type == 'Zonal'){
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
	$date=date('Y-m-d');
	
	$sql = mysqli_query($con, "SELECT B.branchName,F.id,F.request,F.type,F.customerName,F.customerMobile,F.time,F.status
	FROM fund F,branch B 
	WHERE F.date='$date' AND F.branch=B.branchId
	ORDER BY F.id ASC");
	
	$result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
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
	font-size: 18px;
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
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
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
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
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
	.fa_Icon{
	color: #990000;
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
					<h3><i class="fa_Icon fa fa-money"></i> Fund Request </h3> 
				</div>
				<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b></button>
               	<div class="tab" role="tabpanel">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#pending" aria-controls="pending" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Pending</a></li>
						<li role="presentation"><a href="#approved" aria-controls="approved" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Approved</a></li>
						<li role="presentation"><a href="#rejected" aria-controls="rejected" role="tab" data-toggle="tab"><i class="fa_Icon fa fa-times"></i> Rejected</a></li>
					</ul>
					<div class="tab-content tabs" style="min-height:300px;">
						
						<div role="tabpanel" class="tab-pane fade in active" id="pending">
							<div class="panel-body">
								<div class="col-sm-12 table-responsive">
									<table id="example5" class="table table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>BranchName</th>
												<th>Requested Amount</th>
												<th>Type</th>
												<th>Customer</th>
												<th>Time</th>
												<th class="text-center">Approve</th>
												<th class="text-center">Reject</th>
											</tr>
										</thead>
										<tbody>											
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['status'] == "Pending"){
														echo "<tr>";
														echo "<td>" . $i . "</td>";
														echo "<td>" . $row['branchName'] . "</td>";
														echo "<td>" . $row['request'] . "</td>";
														echo "<td>" . $row['type'] . "</td>";
														echo "<td>" . $row['customerName'] . "<br>".$row['customerMobile']."</td>";
														echo "<td>" . $row['time'] . "</td>";
														echo "<td class='text-center'><a onClick=\"javascript: return confirm('Please confirm to Approve');\" class='btn btn-success' href='approveRejectDeleteFund.php?approveId=".$row['id']."'><i style='color:#ffcf40' class='fa fa-check'></i>  Approve</a></td>";
														echo "<td class='text-center'><a onClick=\"javascript: return confirm('Please confirm to Reject');\" class='btn btn-success' href='approveRejectDeleteFund.php?rejectId=".$row['id']."'><i style='color:#ff0000' class='fa fa-times'></i>  Reject</a></td>";
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
						
						<div role="tabpanel" class="tab-pane fade" id="approved">
							<div class="panel-body">
								<div class="col-sm-12 table-responsive">
									<table id="example6" class="table table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>BranchName</th>
												<th>Requested Amount</th>
												<th>Type</th>
												<th>Customer</th>
												<th>Time</th>
												<th class="text-center">Delete</th>
											</tr>
										</thead>
										<tbody>										
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['status'] == "Approved"){
														echo "<tr>";
														echo "<td>" . $i . "</td>";
														echo "<td>" . $row['branchName'] . "</td>";
														echo "<td>" . $row['request'] . "</td>";
														echo "<td>" . $row['type'] . "</td>";
														echo "<td>" . $row['customerName'] . "<br>".$row['customerMobile']."</td>";
														echo "<td>" . $row['time'] . "</td>";
														echo "<td class='text-center'><a onClick=\"javascript: return confirm('Please confirm to Delete');\" class='btn btn-success' href='approveRejectDeleteFund.php?deleteId=".$row['id']."' onClick=\"javascript: return confirm('Please confirm deletion');\"><i style='color:#ff0000' class='fa fa-times'></i> Delete</a></td>";
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
						
						<div role="tabpanel" class="tab-pane fade" id="rejected">
							<div class="panel-body">
								<div class="col-sm-12 table-responsive">
									<table id="example7" class="table table-bordered">
										<thead>
											<tr class="theadRow">
												<th>#</th>
												<th>BranchName</th>
												<th>Requested Amount</th>
												<th>Type</th>
												<th>Requester</th>
												<th>Time</th>
												<th class="text-center">Delete</th>
											</tr>
										</thead>
										<tbody>											
											<?php
												$k = 0;
												$i = 1;
												while($k < $totalLength){
													$row = $result[$k]; 
													if($row['status'] == "Rejected"){
														echo "<tr style='text-decoration:line-through;'>";
														echo "<td>" . $i . "</td>";
														echo "<td>" . $row['branchName'] . "</td>";
														echo "<td>" . $row['request'] . "</td>";
														echo "<td>" . $row['type'] . "</td>";
														echo "<td>" . $row['customerName'] . "<br>".$row['customerMobile']."</td>";
														echo "<td>" . $row['time'] . "</td>";
														echo "<td class='text-center'><a onClick=\"javascript: return confirm('Please confirm to Delete');\" class='btn btn-success' href='approveRejectDeleteFund.php?deleteId=".$row['id']."' onClick=\"javascript: return confirm('Please confirm deletion');\"><i style='color:#ff0000' class='fa fa-times'></i> Delete</a></td>";
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
	</div>
<?php include("footer.php"); ?>
