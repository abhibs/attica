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
	else{
		include("logout.php");
	}
	
	$search = '';
	$search1 = '';
    if(isset($_GET['aaa'])){
		$search=$_REQUEST['dat2'];
		$search1=$_REQUEST['dat3'];
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
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
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
	.btn-danger{
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
	background-color:#e74c3c;
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
	
	fieldset {
	margin-top: 1.5rem;
	margin-bottom: 1.5rem;
	border: none;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}
	legend{
	margin-left:8px;
	width:350px;
	background-color: #123C69;
	padding: 5px 15px;
	line-height:30px;
	font-size: 18px;
	color: white;
	text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
	transform: translateX(-1.1rem);
	box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
	margin-bottom:0px;
	letter-spacing: 2px;
	text-transform:uppercase;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	
	legend:after {
	content: "";
	height: 0;
	width:0;
	background-color: transparent;
	border-top: 0.0rem solid transparent;
	border-right:  0.35rem solid black;
	border-bottom: 0.45rem solid transparent;
	border-left: 0.0rem solid transparent;
	position:absolute;
	left:-0.075rem;
	bottom: -0.45rem;
	}
</style>
<div id="wrapper">
    <div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3> Attendance Details</h3>
					<form action="" method="GET">
						<div class="col-sm-4">
							<label class="text-success">From Date</label> 
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
								<input type="date" class="form-control" id="dat3" name="dat3" required/>
							</div>
						</div>
						<div class="col-sm-4"> 
							<label class="text-success">To Date</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
								<input type="date" class="form-control"  id="dat2" name="dat2" required/>
							</div>
						</div>
						<div class="col-sm-2"> 
							<label class="text-success"><br></label>
							<button class="btn btn-success btn-block" name="aaa" id="aaa"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
						</div>
					</form>
					<div class="col-sm-2" style="margin-top:23px">
						<form action="export.php" enctype="multipart/form-data" method="post">
							<input type="hidden" name="from" value="<?php if(isset($_GET['dat3'])){ echo $_GET['dat3']; }else { echo $date; } ?>">
							<input type="hidden" name="to" value="<?php if(isset($_GET['dat2'])){ echo $_GET['dat2']; }else { echo $date; } ?>">
							<?php if($search=="" && $search1==""){ ?>
								<button type="submit" name="exportCurrentAttend" class="btn btn-success btn-block" value="Export Excel" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
								<?php }else {?>
								<button type="submit" name="exportSearchAttend" class="btn btn-success btn-block" value="Export Excel" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
							<?php } ?>
						</form>
					</div>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<table id="example2" class="table table-striped table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>SLNO</th>
								<th>EmployeeId</th>
								<th>Employee Name</th>
								<th>Branch</th>
								<th>Present Days</th>
								<th>LoginBefore9:30</th>
								<th>Login9:30-9:40</th>
								<th>Login9:40-10:00</th>
								<th>Login10:00-10:30</th>
								<th>After10:30</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if($search=="" && $search1==""){
									$query = mysqli_query($con, "SELECT empId,name,branch,COUNT(DISTINCT(date)) AS Attendance,
									SUM(CASE WHEN time <= '09:30:59' THEN 1 ELSE 0 END) AS Login930,
									SUM(CASE WHEN time BETWEEN '09:31:00' AND '09:40:59' THEN 1 ELSE 0 END) AS Login940,
									SUM(CASE WHEN time BETWEEN '09:41:00' AND '10:00:59' THEN 1 ELSE 0 END) AS Login10,
									SUM(CASE WHEN time BETWEEN '10:01:00' AND '10:30:59' THEN 1 ELSE 0 END) AS Login1030,
									SUM(CASE WHEN time >= '10:31:00' THEN 1 ELSE 0 END) AS Login1031 from
									(SELECT empId,name,time,branch,date FROM attendance WHERE MONTH(date) = MONTH(CURRENT_DATE) GROUP BY empId,date) q GROUP BY empId");
								}
								else{
									$query = mysqli_query($con, "SELECT empId,name,branch,COUNT(DISTINCT(date)) AS Attendance,
									SUM(CASE WHEN time <= '09:30:59' THEN 1 ELSE 0 END) AS Login930,
									SUM(CASE WHEN time BETWEEN '09:31:00' AND '09:40:59' THEN 1 ELSE 0 END) AS Login940,
									SUM(CASE WHEN time BETWEEN '09:41:00' AND '10:00:59' THEN 1 ELSE 0 END) AS Login10,
									SUM(CASE WHEN time BETWEEN '10:01:00' AND '10:30:59' THEN 1 ELSE 0 END) AS Login1030,
									SUM(CASE WHEN time >= '10:31:00' THEN 1 ELSE 0 END) AS Login1031 from
									(SELECT empId,name,time,branch,date FROM attendance WHERE date between '$search1' and '$search' GROUP BY empId,date) q GROUP BY empId");
								}
								$i = 1;
								while($row=mysqli_fetch_array($query)){
									$row1=mysqli_fetch_array(mysqli_query($con,"SELECT name FROM employee WHERE empid='$row[empId]'"));
									echo "<tr>";
									echo "<td>". $i.  "</td>";
									echo "<td>". $row['empId'] . "</td>";
									echo "<td>". $row1['name'] . "</td>";
									echo "<td>". $row['branch'] . "</td>";
									echo "<td>". $row['Attendance'] . "</td>";
									echo "<td>". $row['Login930'] . "</td>";
									echo "<td>". $row['Login940'] ."</td>";
									echo "<td>". $row['Login10'] ."</td>";
									echo "<td>". $row['Login1030'] ."</td>";
									echo "<td>". $row['Login1031'] ."</td>";
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
	<div style="clear:both"></div>
<?php include("footer.php"); ?>