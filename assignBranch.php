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
	else if ($type == 'BD') {
		include("header.php");
		include("menubd.php");
	}
	else if($type=='Accounts IMPS'){
	    include("header.php");
		include("menuimpsAcc.php");
	}
	else if ($type == 'SundayUser') {
	    include("header.php");
    	    include("menuSundayUser.php");
	}
	else if($type=='Expense Team'){
	    include("header.php");
	    include("menuexpense.php");
	}
	else if($type=='Zonal'){
		include("header.php");
        	include("menuZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
    	else if($type=='Assets'){
		include("header.php");
        	include("menuassets.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else if($type=='Legal'){
		include("header.php");
		include("menulegal.php");
	}
	else if($type=='Call Centre'){
		include("header.php");
		include("menuCall.php");
	}
	else if($type == 'CashMovers') {
                include("header.php");
                include("menuCashMover.php");
        }
	else if ($type == 'BusinessDeveloper') {
 		 include("header.php");
 		 include("menuBusiness.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='Leads'){
	    include("header.php");
	    include("menuLeads.php");
	}
	else if ($type == 'BusinessDeveloper') {
 		 include("header.php");
 		 include("menuBusiness.php");
	}
	else if ($type == 'ITMaster') {
                 include("header.php");
                 include("menuItMaster.php");
        }
   	else{
       		include("logout.php");
	}
	include("dbConnection.php");
	$date=date('Y-m-d');
	
	if($type=='Zonal'){
		if($_SESSION['branchCode']=="TN"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName,state, city  FROM branch WHERE status=1 AND state IN ('Tamilnadu','Pondicherry')");
		}
		else if($_SESSION['branchCode']=="KA"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName,state, city FROM branch WHERE status=1 AND state='Karnataka' AND branchId != 'AGPL000'");
			
		}
		else if($_SESSION['branchCode']=="AP-TS"){
			$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName,state, city FROM branch WHERE status=1 AND state IN ('Andhra Pradesh','Telangana')");
		}
	}
	else{
		$branchListQuery =  mysqli_query($con, "SELECT branchId, branchName,state, city FROM branch WHERE status=1 AND branchId != 'AGPL000'");
	}
	
	$branchList = [];
	while($row = mysqli_fetch_assoc($branchListQuery)){
		$branchList[$row['branchId']] = ['branchName' => $row['branchName'], 'state' => $row['state'], 'city' => $row['city']];
		//$branchList[$row['branchId']] = $row['branchName'];
	}
	
	$employeeListQuery = mysqli_query($con, "SELECT id, empId, name, contact FROM employee WHERE designation != 'VM'");
	$employeeList = [];
	while($row = mysqli_fetch_assoc($employeeListQuery)){
		$employeeList[$row['empId']] = $row;
	}
	
	$usersQuery = mysqli_query($con, "SELECT username, employeeId FROM users WHERE type='Branch'");
	$userList = [];
	while($row = mysqli_fetch_assoc($usersQuery)){
		$userList[$row['username']] = $row['employeeId'];
	}
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
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
	font-size: 12px;
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
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3><i class="fa_Icon fa fa-edit"></i> Branch Manager List</h3>
				</div>
				<?php if($type=='Zonal' || $type=='SubZonal'|| $type=='ZonalMaster' || $type=='Master' || $type == 'SundayUser'){ ?>
					<div class="panel-body">
						<form class="form-horizontal" action="edit.php" method="POST" autocomplete="off">
							<div class="col-sm-5">
								<label class="text-success">Branch</label>
								<input list="branchList"  class="form-control" name="bran" id="bran" required placeholder="Select Branch" />
								<datalist id="branchList"> 
									<?php foreach ($branchList as $key=>$value) { ?>
										<option value="<?php echo $key; ?>" label="<?php echo $value['branchName']; ?>"></option>
									<?php } ?>
								</datalist>
							</div>
							<div class="col-sm-5">
								<label class="text-success">Employee ID</label>
								<input list="bm" class="form-control" name="empl" id="empl" required placeholder="Select Employee">
								<datalist id="bm">
									<?php foreach ($employeeList as $key=>$row) { ?>
										<option value="<?php echo $key; ?>" label="<?php echo $row['name']; ?>"></option>
									<?php } ?>
								</datalist>
							</div>
							<div class="col-sm-2">
								<label class="text-success"></label>
								<button class="btn btn-success btn-block" name="assignBranch" id="assignBranch"><span style="color:#ffcf40" class="fa fa-check"></span> Assign Staff</button>							
							</div>
						</form>
					</div>
				<?php } ?>
				<div class="panel-body" style="margin-top: 10px;">
					<div class="table-responsive">
						<table id="example3" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch Name</th>
									<th>State</th> 
									<th>City</th> 
									<th>Employee</th>
									<th>contact</th>
									<?php if($type=='Zonal'|| $type=='SubZonal'|| $type=='ZonalMaster' || $type=='Master' ||  $type == 'SundayUser'){ ?>
										<th>Modify</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									foreach ($branchList as $id=>$data) {
										$empData = $employeeList[$userList[$id]];
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $data['branchName'] . " - (" . $id . ")</td>";
										echo "<td>" . $data['state'] . "</td>";
										echo "<td>" . $data['city'] . "</td>";
										echo "<td>" . $empData['name'] . " - (" . $empData['empId'] . ")</td>";
										echo "<td>" . $empData['contact']. "</td>";
										 if($type=='Zonal'|| $type=='SubZonal'|| $type=='ZonalMaster' || $type=='Master' ||  $type == 'SundayUser'){
											echo "<td><a class='btn' href='addEmployee.php?empId=".$empData['id']."'><i class='fa fa-edit' ></i></a></td>";
										}
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
