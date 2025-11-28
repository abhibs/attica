<?php
	session_start();
	$type=$_SESSION['usertype'];
	
	$extra = "";
	$branchSQL = "";
	
	if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
		if($_SESSION['branchCode']=="TN"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Tamilnadu','Pondicherry'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Tamilnadu','Pondicherry')");
		}
		elseif ($_SESSION['branchCode']=="KA"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Karnataka'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Karnataka')");
		}
		elseif($_SESSION['branchCode']=="AP-TS"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Andhra Pradesh','Telangana'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Andhra Pradesh','Telangana')");
		}
	}
	else if($type=='Issuecall'){
		include("header.php");
		include("menuissues.php");
		if($_SESSION['branchCode']=="TN"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Tamilnadu','Pondicherry'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Tamilnadu','Pondicherry')");
		}
		elseif ($_SESSION['branchCode']=="KA"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Karnataka'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Karnataka')");
		}
		elseif($_SESSION['branchCode']=="AP-TS"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Andhra Pradesh','Telangana'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Andhra Pradesh','Telangana')");
		}
		elseif($_SESSION['branchCode']=="KA/AP-TS"){
			$extra= " AND W.branchId IN (select branchId from branch where state IN('Andhra Pradesh','Telangana','Karnataka'))";
			$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1 AND state IN ('Karnataka','Andhra Pradesh','Telangana')");
		}
	}
	else if($type=='Call Centre'){
		include("header.php");
		include("menuCall.php");
		$extra= "";
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1");
	}
	else if($type=='ZonalMaster' || $type == 'SundayUser' ){
		include("header.php");
		include("menuzonalMaster.php");
		$extra= "";
		$branchSQL = mysqli_query($con,"SELECT branchId,branchName FROM branch where status = 1");
	}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	
	$branchData = [];
	while($row = mysqli_fetch_assoc($branchSQL)){
		$branchData[$row['branchId']] = $row['branchName'];
	}
	
	$state = ["All", "Bangalore", "Karnataka", "Chennai", "Tamilnadu", "Hyderabad", "Andhra And Telangana"];
	$sql = "";
	$date_string = "";
	if (isset($_POST['submitlo'])) {
		$from = $_POST['from'];
		$to = $_POST['to'];
		$date_string = "(".$from." to ".$to.")";
		
		if(isset($_POST['branchId']) && $_POST['branchId'] != ''){
			$branchId = $_POST['branchId'];			
			if(array_search($branchId, $state) !== false){
				$ext = "";
				switch($branchId){
				    case 'All' : $ext = ""; break;
					case 'Bangalore': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE city = 'Bengaluru')"; break;
					case 'Karnataka': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE state = 'Karnataka' AND city != 'Bengaluru')"; break;
					case 'Chennai': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE city = 'Chennai')"; break;
					case 'Tamilnadu': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE state IN ('Tamilnadu','Pondicherry') AND city != 'Chennai')"; break;
					case 'Hyderabad': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE city = 'Hyderabad')"; break;
					case 'Andhra And Telangana': $ext = "AND W.branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city != 'Hyderabad')"; break;
				}
				$sql = mysqli_query($con,"SELECT W.*, B.branchName, E.name AS zonal_name
				FROM walkin W 
				LEFT JOIN branch B ON W.branchId = B.branchId
				LEFT JOIN employee E ON B.ezviz_vc = E.empId
				WHERE W.date BETWEEN '$from' AND '$to' AND W.issue NOT IN ('Rejected')".$ext."
				ORDER BY W.id ASC");
			}
			else{
				$sql = mysqli_query($con,"SELECT W.*, B.branchName, E.name AS zonal_name
				FROM walkin W 
				LEFT JOIN branch B ON W.branchId = B.branchId
				LEFT JOIN employee E ON B.ezviz_vc = E.empId
				WHERE W.branchId='$branchId' AND W.date BETWEEN '$from' AND '$to' AND W.issue NOT IN ('Rejected')
				ORDER BY W.id ASC");
			}						
		}
		else{
			$sql = mysqli_query($con,"SELECT W.*, B.branchName, E.name AS zonal_name
			FROM walkin W 
			LEFT JOIN branch B ON W.branchId = B.branchId
			LEFT JOIN employee E ON B.ezviz_vc = E.empId
			WHERE W.date BETWEEN '$from' AND '$to' AND W.issue NOT IN ('Rejected')".$extra."
			ORDER BY W.id ASC");
		}
	} 
	else {
		$date = date('Y-m-d');
		$date_string = "(".$date.")";
		$sql = mysqli_query($con,"SELECT W.*, B.branchName, E.name AS zonal_name
		FROM walkin W
		LEFT JOIN branch B ON W.branchId = B.branchId
		LEFT JOIN employee E ON B.ezviz_vc = E.empId
		WHERE W.date='$date' AND W.issue NOT IN ('Rejected')".$extra."
		ORDER BY W.id ASC");
	}
	
	?>
	<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	#wrapper .panel-body{
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
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.fa_Icon {
	color: #990000;
	}
</style>

<datalist id="branchList"> 
	<?php if($type == 'Call Centre'){ ?>
	    <option value="All" label="All"></option>
		<option value="Bangalore" label="Bangalore"></option>
		<option value="Karnataka" label="Karnataka"></option>
		<option value="Chennai" label="Chennai"></option>
		<option value="Tamilnadu" label="Tamilnadu"></option>
		<option value="Hyderabad" label="Hyderabad"></option>
		<option value="Andhra And Telangana" label="Andhra And Telangana"></option>
	<?php } ?>
	<?php foreach($branchData as $key=>$item ){ ?>		
		<option value="<?php echo $key; ?>" label="<?php echo $item; ?>"></option>
	<?php } ?>
</datalist>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading" >
					<div class="col-sm-5">
						<h3><i class="fa_Icon fa fa-check-square-o" ></i> ENQUIRY REPORT 
							<span style="color:#990000">
								<?php echo $date_string; ?>
							</span>
						</h3>
					</div>
					<form action="" method="POST">
						<div class="col-sm-2">
							<input list="branchList" name="branchId" placeholder="BRANCH" class="form-control">
						</div>
						<div class="col-sm-4">
							<div class="input-group">
								<input type="date" class="form-control" name="from" required>
								<span class="input-group-addon">to</span>
								<input type="date" class="form-control" name="to" required>
							</div>
						</div>
						<div class="col-sm-1">
							<button name="submitlo" value="Submit" type="submit" class="btn btn-success btn-block"><span style="color:#ffcf40" class="fa fa-search"></span></button>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example1" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Zonal Name</th>
									<th>Name</th>
									<th>Contact</th>
									<th>Type</th>
									<th>Having</th>
									<th>Metal</th>
									<th>GrossW</th>
									<th>NetW</th>
									<th>ReleaseA</th>
									<th>Branch_Remark</th>
									<th>Zonal_Remark</th>
									<th>CSR_Remark</th>
									<th>Disposition</th>
									<th>Agent</th>
									<th>Date</th>
									<th>Time</th>
									<th><span class="fa fa-edit"></span></th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i = 1;
									while ($row = mysqli_fetch_assoc($sql)) {
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $branchData[$row['branchId']] . "</td>";
										echo "<td>" . $row['zonal_name'] . "</td>";
										echo "<td>" . $row['name'] . "</td>";
										echo "<td>" . $row['mobile'] . "</td>";
										echo "<td>" . $row['gold'] . "</td>";
										echo "<td>" . $row['havingG'] . "</td>";
										echo "<td>" . $row['metal'] . "</td>";
										echo "<td>" . $row['gwt'] . "</td>";
										echo "<td>" . $row['nwt'] . "</td>";
										echo "<td>" . $row['ramt'] . "</td>";
										echo "<td>" . $row['remarks'] . "</td>";
										echo "<td>" . $row['zonal_remarks'] . "</td>";
										echo "<td>" . $row['comment'] . "</td>";
										echo "<td>" . $row['issue'] . "</td>";
										echo "<td>" . $row['agent_id']."<br>". $row['followUp'] ."</td>";
										echo "<td>" . $row['date'] . "</td>";
										echo "<td>" . $row['time'] . "</td>";
										if($type=='Zonal'){
											echo "<td><b><a class='text-success' href='enquiryZonalRemark.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>";
										}
										else{
											echo "<td><b><a class='text-success' href='enquiryComment.php?mobile=" . $row['mobile'] . "&id=" . $row['id'] . "'><span class='fa fa-edit'></span></a></b></td>";
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
