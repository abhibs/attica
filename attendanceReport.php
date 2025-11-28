<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='HR'){
		include("header.php");
		include("menuhr.php");
	}

	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	if(isset($_GET['monReport'])){
		
		$branch = $_GET['bran'];
		$monthYear = $_GET['empMonth'];
		
		// GET NUMBER OF DAYS IN A MONTH;
		$splitDate = explode("-",$monthYear,2);
		// $totalDays = cal_days_in_month(CAL_GREGORIAN, $splitDate[1], $splitDate[0]);
		
		// SUNDAYS IN THE GIVEN MONTH
		function getSundays($y,$m){ 
			$date = "$y-$m-01";
			$first_day = date('N',strtotime($date));
			$first_day = 7 - $first_day + 1;
			$last_day =  date('t',strtotime($date));
			$days = [];
			for($i=$first_day; $i<=$last_day; $i=$i+7){
				$days[] = $i;
			}
			return count($days);
		}
		$totalSundays = getSundays($splitDate[0],$splitDate[1]);
		$monthName = '';
		
		switch($branch){
			case "All Branches": $state=''; break;
			case "Bangalore"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')"; break;
			case "Karnataka"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')"; break;
			case "Chennai"     : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Chennai')"; break;
			case "Tamilnadu"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')"; break;
			case "Hyderabad"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')"; break;
			case "AP-TS"       : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')"; break;
			case "Pondicherry" : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')"; break;
			default            : $state=" AND branchId = '$branch'"; break;
		}
		switch($splitDate[1]){
			case '01' : $monthName='Jan';break;
			case '02' : $monthName='Feb';break;
			case '03' : $monthName='Mar';break;
			case '04' : $monthName='April';break;
			case '05' : $monthName='May';break;
			case '06' : $monthName='June';break;
			case '07' : $monthName='July';break;
			case '08' : $monthName='Aug';break;
			case '09' : $monthName='Sep';break;
			case '10' : $monthName='Oct';break;
			case '11' : $monthName='Nov';break;
			case '12' : $monthName='Dec';break;
		}
		
		$i = 0;
		$empIdArray = [];
		$allData = [];
		$report = [];
		
		$sql = mysqli_query($con,"SELECT empId,name,branchId,branch,date,time
		FROM attendance
		WHERE DATE_FORMAT(date,'%Y-%m')='$monthYear' AND name!='' AND branch!=''".$state."
		GROUP BY date,empId
		ORDER BY empId");
		while($row = mysqli_fetch_assoc($sql)){
			$empIdArray[] = $row['empId'];
			$allData[] = $row;
		}
		
		$uniqueEmpId = array_unique($empIdArray);
		foreach($uniqueEmpId as $key=>$val){
			$name = '';
			$count = 0;
			$branch = [];
			foreach($allData as $key1=>$val1){
				if($val1['empId'] == $val){
					$name = $val1['name'];
					$branch[] = $val1['branch'];
					$count++;
				} 
			}
			$report[] = ['Id'=>$val,'name'=>$name,'count'=>$count,'branch'=>array_unique($branch)];
		}
	}
	
	/*   BRANCH DATA   */
	$branchData = [];
	$branchQuery = mysqli_query($con, "SELECT branchId, branchName, city, state FROM branch WHERE status = 1");
	while($row = mysqli_fetch_assoc($branchQuery)){
		$branchData[$row['branchId']] = $row;
	}
?>
<style>
	#wrapper{
	background-color: #e6e6fa;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
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
	tbody .btn{
	padding: 0px;
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
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 20px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	background-color: #f5f5f5;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList"> 
	<option value="All Branches"> All Branches</option>
	<option value="Bangalore"> Bangalore</option>
	<option value="Karnataka"> Karnataka</option>
	<option value="Chennai"> Chennai</option>
	<option value="Tamilnadu"> Tamilnadu</option>
	<option value="Hyderabad"> Hyderabad</option>
	<option value="AP-TS"> AP-TS</option>
	<option value="Pondicherry"> Pondicherry</option>
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
					<div class="col-sm-4">
						<h3 style="margin-top:25px"> Monthly Report <span style="color:#990000"><?php if(isset($_GET['monReport'])){ echo $monthName." - ".$splitDate[0]; } ?></span></h3>
					</div>
					<form action="" method="GET">
						<div class="col-sm-3">
							<label class="text-success">Branch</label>
							<div class="input-group"><span class="input-group-addon"><span class="fa_icon fa fa-address-book-o"></span></span>
								<input list="branchList"  class="form-control" name="bran" id="bran" placeholder="Branch" required autocomplete="off">  
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Month</label> 
							<div class="input-group"><span class="input-group-addon"><span class="fa_icon fa fa-calendar"></span></span>
								<input type="month"  class="form-control" name="empMonth" required>
							</div>
						</div>
						<div class="col-sm-2"> 
							<label><br></label> 
							<button type="submit" class="btn btn-success btn-block" name="monReport"><span class="fa_Icon fa fa-search"></span> Search</button>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<div class="table-responsive project-list">
						<table id="example2" class="table table-striped table-hover table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Employee Id</th>
									<th>Employee Name</th>
									<th>Branch</th>
									<!--<th>Days</th>-->
									<th>Present</th>
									<th>Sunday</th>
									<!--<th>Total</th>-->
									<th style="text-align:center">Details</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if(isset($_GET['monReport'])){
										$i = 1;
										foreach($report as $key1=>$val1){
											echo "<tr>";
											echo "<td>" . $i . "</td>";
											echo "<td>" . $val1['Id'] . "</td>";
											echo "<td>" . $val1['name'] . "</td>";
											echo "<td>" . implode(",<br>",$val1['branch']). "</td>";
											//echo "<td>" . $totalDays . "</td>";
											echo "<td>" . $val1['count'] . "</td>";
											echo "<td>" . $totalSundays . "</td>";
											//echo "<td><span class='badge badge-primary'>" . ($val1['count']+$totalSundays) . "</span></td>";
											echo "<td style='text-align:center'><a href='employeeMonthlyReport.php?empId=".$val1['Id']."&empMonth=".$monthYear."&empReport=' class='btn' type='button' target='_BLANK'><i class='fa fa-external-link text-success'style='font-size:16px'></i></a></td>";
											echo "</tr>";
											$i++;
										}
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
