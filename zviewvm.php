<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];   
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	} 
	else if ($type == 'VM-AD') {
		include("header.php");
		include("menuvmadd.php");
	}
	else if($type == 'VM-WAIT') {
 	  	include("header.php");
    		include("menuvmWait.php");
	}
	else if ($type == 'Zonal') {
		include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'SubZonal'){
		include("header.php");
        	include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        include("header.php");
        include("menuzonalMaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	$branchList = [];
	$branch = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
	while($row = mysqli_fetch_assoc($branch)){
		$branchList[$row['branchId']] = $row['branchName'];
	}
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 10px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
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
	color:#990000;
	}
</style>
<div id="wrapper">
	<div class="content row">
		<div class="col-lg-12">
			
			<div class="hpanel">
				
				<div class="panel-heading">
					<div class="panel-heading">
						<div class="row">
							<div class="col-sm-8">
								<h3><i class="fa_Icon fa fa-edit" ></i> Virtual Manager Details </h3>
							</div>
							<div class="col-sm-4">
								<input list="branchList" class="form-control" name="bran" id="bran" required placeholder="Branch List" />
								<datalist id="branchList">								
									<?php
										foreach($branchList as $key=>$value){
											echo "<option value=".$key.">".$value."</option>";
										}
									?>
								</datalist>
							</div>
						</div>
					</div>
				</div>
				
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example3" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>Name</th>
									<th>Username</th>
									<th>Password</th>
									<th>Language</th>
									<th>Branch 1</th>
									<th>Branch 2</th>
									<th>Branch 3</th>
									<th>Branch 4</th>
									<th>Branch 5</th>
									<!--<th>Modify</th>-->
									<!--<th>Delete</th>-->
								</tr>
							</thead>
							<tbody>
								<?php
									
									$sql = mysqli_query($con, "SELECT X.id, X.username, X.password, X.language, X.name, Y.branch FROM
									(SELECT * FROM (SELECT id,username,password,employeeId,language FROM users WHERE type='VM-HO') A 
									LEFT JOIN
									(SELECT empId,name FROM employee WHERE designation='VM') B
									ON A.employeeId = B.empId) X 
									LEFT JOIN
									(SELECT DISTINCT * FROM vmagent) Y 
									ON X.employeeId=Y.agentId
									ORDER BY X.id DESC");
									
									$i = 1;
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>". $i ."</td>";
										echo "<td>". $row['name'] ."</td>";
										echo "<td>". $row['username'] ."</td>";
										echo "<td>". $row['password'] ."</td>";
										if($row['language'] == 1){
											echo "<td>Kannada</td>";
										}
										else if($row['language'] == 2){
											echo "<td>Tamil</td>";
										}
										else if($row['language'] == 3){
											echo "<td>Telugu</td>";
										}
										else{
											echo "<td></td>";
										}
										$assigned = explode (",", $row['branch']);
										echo "<td>". $branchList[$assigned[0]] ."</td>";
										echo "<td>". $branchList[$assigned[1]] ."</td>";
										echo "<td>". $branchList[$assigned[2]] ."</td>";
										echo "<td>". $branchList[$assigned[3]] ."</td>";
										echo "<td>". $branchList[$assigned[4]] ."</td>";
								    	// echo "<td><a class='btn btn-success' href='zvmedit.php?empId=" . $row['id'] . "'><i class='fa fa-edit'></i></a></td>";
										// echo "<td><a class='btn btn-danger' href='zvmdelete.php?empId=" . $row['id'] . "'>X</a></td></tr>";
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
