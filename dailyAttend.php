<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}	
	else if($type=='HR'){
		include("header.php");
		include("menuhr.php");
	}
	else if ($type == 'BusinessDeveloper') {
		include("header.php");
		include("menuBusiness.php");
	}
	else if ($type == 'BD') {
                include("header.php");
                include("menubd.php");
        }
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	if (isset($_POST['block_empId']) && isset($_POST['block_date'])) {
		$blockEmpId = $_POST['block_empId'];
		$blockDate = $_POST['block_date'];
		
		// Update the status to '0' for the specified employee ID and date
		$updateQuery = "UPDATE attendance SET status = 1 WHERE empId = '$blockEmpId' AND date = '$blockDate'";
		mysqli_query($con, $updateQuery);
		
		echo "<script>alert('Attendence blocked.!');</script>";
        echo "<script>setTimeout(\"location.href = 'dailyAttend.php';\", 150);</script>";
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------
	
	/* MULTIPLE BRANCH */
	$multiBranchData = [];
	$multiBranchQuery = mysqli_query($con, "SELECT city FROM branch WHERE status=1 GROUP BY city HAVING COUNT(*) > 1");
	while($row = mysqli_fetch_assoc($multiBranchQuery)){
		array_push($multiBranchData, $row['city']);
	}
	
	/*   BRANCH DATA   */
	$branchData = [];
	$branchQuery = mysqli_query($con, "SELECT branchId, branchName, city, state FROM branch WHERE status = 1");
	while($row = mysqli_fetch_assoc($branchQuery)){
		$branchData[$row['branchId']] = $row;
	}
	
	// ---------------------------------------------------------------------------------------------------------------
	
	$attendanceQuery = '';
	$from = '';
	$to = '';
	if(isset($_GET['getAttendance'])){
		$branch = $_GET['branchId'];
		$from = $_GET['from'];
		$to = $_GET['to'];
		
		$state = "";
		if($branch == "All Branches"){
			$state='';
		}
		else if(array_search($branch, $multiBranchData) !== false){
			$state=" AND branchId IN (SELECT branchId FROM branch WHERE city='".$branch."')";
		}
		else{
			$state=" AND branchId='$branch'";
		}
			$attendanceQuery = mysqli_query($con, "SELECT a.empId, a.name, a.branchId, a.branch, a.date, a.time, a.photo, e.designation, e.contact FROM attendance a LEFT JOIN employee e ON e.empId = a.empId WHERE a.date BETWEEN '$from' AND '$to' $state AND a.status = 0 ORDER BY a.id");
	}
	else{
			$attendanceQuery = mysqli_query($con, "SELECT a.empId, a.name, a.branchId, a.branch, a.date, a.time, a.photo, e.designation, e.contact FROM attendance a LEFT JOIN employee e ON e.empId = a.empId WHERE a.date = '$date' AND a.status = 0 ORDER BY a.id");
	}
		
	$data = [];
	while($row = mysqli_fetch_assoc($attendanceQuery)){
		$data[$row['empId'].$row['date']][] = $row;
	}	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	margin: 0px;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
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
	font-size:10px;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
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
	.fa_Icon {
	color:#ffd700;
	}
	.fa_icon{
	color:#990000;
	}
	.block-button {
    background-color: transparent;
	color:red;
    border: none;
    padding: 0;
    cursor: pointer;
	}	
	.table-responsive .row{
	margin: 0px;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList"> 
	<option value="All Branches"> All Branches</option>
	<?php 
		foreach($multiBranchData as $val){
			echo "<option value='$val' label='$val'></option>";
		}	
	?>
	<option value="~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" label="~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"></option>
	<?php 
		foreach($branchData as $row){
			echo "<option value='$row[branchId]' label='$row[branchName]'></option>";
		}
	?>
</datalist>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-3">
					<h3 class="text-success">Daily Attendance</h3>
					<small style='color: #990000'>(
						<?php 
							echo (isset($_GET['getAttendance'])) ? $_GET['from']." to ".$_GET['to'] : $date;
						?>
					)</small>
				</div>
				<form action="" method="GET" >
					<div class="col-sm-3">
						<input list="branchList" class="form-control" name="branchId"  placeholder="Branch Id" required/>					
					</div>
					<div class="col-sm-4">
						<div class="input-group">
							<input type="date" class="form-control" name="from"/>
							<span class="input-group-addon">to</span>
							<input type="date" class="form-control" name="to"/>
						</div>
					</div>
					<div class="col-sm-1" style="margin-top: 1px;">
						<button class="btn btn-success btn-block" name="getAttendance" ><span class="fa_Icon fa fa-search"></span> Search</button>
					</div>
				</form>
				<div class="col-sm-1" style="margin-top: 1px;">
					<form action="export.php" enctype="multipart/form-data" method="post">
						<input type="hidden" name="branch" value="<?php if(isset($_GET['getAttendance'])){ echo $_GET['branchId']; }else { echo "All Branches"; } ?>">
						<input type="hidden" name="from" value="<?php if(isset($_GET['getAttendance'])){ echo $_GET['from']; }else { echo $date; } ?>">
						<input type="hidden" name="to" value="<?php if(isset($_GET['getAttendance'])){ echo $_GET['to']; }else { echo $date; } ?>">
						<button type="submit" name="exportDailyAttend" class="btn btn-success btn-block" value="Export Excel" required><span class="fa_Icon fa fa-download"></span>  Export</button>
					</form> 
				</div>
			</div>
			<div style="clear:both"><br></div>
			<div class="panel-body">
				<div class="table-responsive">
					<table id="example1" class="table table-hover table-bordered">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>Employee_Id</th>
								<th>Employee_Name</th>
								<th>Designation</th>
								<th>Contact</th>
								<th>Branch</th>
								<th>City</th>
								<th>State</th>
								<th>Date</th>
								<th>Time_In</th>
								<th>Photo</th>
								<th>Time_Out</th>
								<th>Photo</th>
								<?php
									if ($type !== 'BusinessDeveloper' && $type !== 'BD') {
										echo '<th>Block</th>';

									}		
								?>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								foreach ($data as $row) {
									
									$loginPhoto = isset($row[0]['photo']) ? 'AttendanceImage/'.$row[0]['photo'] : '';
									$logoutPhoto = isset($row[1]['photo']) ? 'AttendanceImage/'.$row[1]['photo'] : '';
									echo "<tr>";
									echo "<td>".$i."</td>";
									echo "<td>".$row[0]['empId']."</td>";
									echo "<td>".$row[0]['name']."</td>";
									echo "<td>".(!empty($row[0]['designation']) ? $row[0]['designation'] : "-")."</td>";
									echo "<td>".(!empty($row[0]['contact']) ? $row[0]['contact'] : "-")."</td>";
									if($row[0]['branchId']=='AGPL089'){
										echo "<td>Vijayawada-Bhavanipuram</td>";
									}
									else {
										echo "<td>" . $row[0]['branch'] . "</td>";
									}
									echo "<td>".$branchData[$row[0]['branchId']]['city']."</td>";
									echo "<td>".$branchData[$row[0]['branchId']]['state']."</td>";
									echo "<td>".$row[0]['date']."</td>";
									echo "<td>".$row[0]['time']."</td>";
									echo "<td style='margin:0px;padding:0px;width: 60px;'>
									<a target='_blank' href='".$loginPhoto."'><img width='100%' src='".$loginPhoto."'></a>
									</td>";
									echo "<td>".$row[1]['time']."</td>";
									echo "<td style='margin:0px;padding:0px;width: 60px;'>
									<a target='_blank' href='".$logoutPhoto."'><img width='100%' src='".$logoutPhoto."'></a>
									</td>";
									
									if ($type !== 'BusinessDeveloper' && $type !== 'BD'){
										echo "<td style='text-align: center; vertical-align: middle;'>";
										echo "<form method='POST'>";
										echo "<input type='hidden' name='block_empId' value='{$row[0]['empId']}'>";
										echo "<input type='hidden' name='block_date' value='{$row[0]['date']}'>";
										echo "<button type='submit' class='block-button'><i class='fa fa-ban' style='font-size:20px; font-weight: bold;'></i></button>";
										echo "</form>";
										echo "</td>";
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
<div style="clear:both"></div>
<?php include("footer.php"); ?>
