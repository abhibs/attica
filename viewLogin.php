<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master'){
	    include("header.php");
		include("menumaster.php");
	}	
	else if($type=='Accounts'){
	    include("header.php");
		include("menuacc.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else if($type=='Zonal'){
        include("header.php");
        include("menuZonal.php");
	}
	else if($type=='SubZonal'){
        	include("header.php");
       		include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
    	else if($type=='ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
    	else{
        	include("logout.php");
	}
	include("dbConnection.php");
	$date=date('Y-m-d');
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	}
	.fa_Icon{
	color: #ffd700;
	}	
	.text-success{
	font-weight:600;
	color: #123C69;
	}
	.panel-heading h3{
	text-transform:uppercase;
	}
	.hpanel .panel-body{
	padding: 5px 15px 5px;
	}	
	.dataTables_wrapper .row{
	margin-right: 0px;
	margin-left: 0px;
	}
	button {
	transform: none;
	box-shadow: none;
	}	
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"> LOGIN ATTEMPTS</h3>
				</div>
				<div style="clear:both"></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Branch Name</th>
									<th>Employee</th>
									<th>OTP</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									if($type=='Master' ||  $type=='Software'){
										$query = mysqli_query($con,"SELECT L.branch,L.empid,L.otp,L.time,B.branchName 
										FROM loginotp L LEFT JOIN branch B ON L.branch=B.branchId
										WHERE L.date='$date'
										ORDER by L.id DESC;");
									}	
									else{
										$query = mysqli_query($con,"SELECT L.branch,L.empid,L.otp,L.time,B.branchName 
										FROM loginotp L,branch B 
										WHERE L.date='$date' AND L.branch=B.branchId ORDER by L.id DESC LIMIT 20");
									}
									
									while($row = mysqli_fetch_assoc($query)){
										$empData = mysqli_fetch_assoc(mysqli_query($con,"SELECT name FROM employee WHERE empId='$row[empid]'"));
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . " - (" . $row['branch'] . ")</td>";
										echo "<td>" . $empData['name'] . " - (" . $row['empid'] . ")</td>";
										echo "<td>" . $row['otp'] . "</td>";
										echo "<td>" . $row['time'] . "</td>";
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
	<?php include("footer.php"); ?>		
