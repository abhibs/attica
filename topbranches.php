<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	$date=date('Y-m-d');
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
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
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
	.fa_Icon {
		color:#ffcf40;
	}
	.modal-title {
		font-size: 25px;
		font-weight: 300;
		color:#fff5ee;
		text-transform:uppercase;
	}
	.modal-header{
		background: #123C69;
	}
	.row{
	    margin-left:0px;
	    margin-right:0px;
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading" >
						<h3 class="text-success"><i style="color:#990000" class="fa fa-edit"></i> Top Branch</h3>
					</div>
					<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-success">
						<b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b>
					</button>
					<ul class="nav nav-tabs">
						<li class="active">
							<a data-toggle="tab" href="#tab-1" class="text-success"><i style="color:#990000" class="fa fa-check"></i> Top Business Branches </a>
						</li>
						<li class="">
							<a data-toggle="tab" href="#tab-2" class="text-success"><i style="color:#990000" class="fa fa-check"></i> Top Expense Branches </a>
						</li>
						<li class="">
							<a data-toggle="tab" href="#tab-3" class="text-success"><i style="color:#990000" class="fa fa-check"></i> Top Gold Unchecked Branches </a>
						</li>
					</ul>
					
					
					<!--Top Business Branches-->
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body" style="box-shadow:10px 15px 15px #999;">
								<table id="example3" class="table table-striped table-bordered">
									<thead>
										<tr class="theadRow">
											<th><i class="fa fa-sort-numeric-asc"></i></th>
											<th>Branch Name</th>
											<th>BM Name</th>
											<th>BM Contact</th>
											<th>Gross W</th>
											<!--<th>View</th>-->
										</tr>
									</thead>
									<tbody>
									<?php

										$query = mysqli_query($con, "select round(sum(grossW),2) as gw,branchId from trans where date = '$date' and metal = 'Gold' group by branchId order by gw DESC;");
										$count = mysqli_num_rows($query);
										for ($i = 1; $i <= $count; $i++) {
											if ($count > 0) {
												$row2 = mysqli_fetch_array($query);               
												
												$result219 = mysqli_query($con,"SELECT branchName from branch where branchId = '".$row2['branchId']."'");
												$row219= mysqli_fetch_array($result219);
												$result319 = mysqli_query($con,"SELECT employeeId from users where username = '".$row2['branchId']."'");
												$row319= mysqli_fetch_array($result319);
												$result419 = mysqli_query($con,"SELECT name,contact from employee where empId = '".$row319['employeeId']."'");
												$row419= mysqli_fetch_array($result419);
												echo "<tr><td>" . $i .  "</td>";
												echo "<td>" . $row219['branchName'] . "</td>";
												echo "<td>" . $row419['name'] . "</td>";
												echo "<td>" . $row419['contact'] . "</td>";
												echo "<td><b>" . $row2['gw'] . "</b></td>";
												// echo "<td>" . $row3['contact'] . "</td>";
												// echo "<td><a class='btn btn-success' href='viewexpensebranch.php?branch=" . $row2['branchId'] . "'><i class='fa fa-edit'></i></a></td></tr>";
											}
										}

									?>
									</tbody>
								</table>
							</div>
						</div>
						
						
						<!--Top Expense Branches-->
						
						<div id="tab-2" class="tab-pane">
							<div class="panel-body" style="box-shadow:10px 15px 15px #999;">
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr class="theadRow">
											<th><i class="fa fa-sort-numeric-asc"></i></th>
											<th>Branch Name</th>
											<th>BM Name</th>
											<th>BM Contact</th>
											<th>Amount</th>
											<th>View</th>

										</tr>
									</thead>
									<tbody>
									<?php
										$query1 = mysqli_query($con, "select round(sum(amount),2) as expense,branchCode from expense where date = '$date' AND status='Approved' group by branchCode order by expense DESC;");

										$count = mysqli_num_rows($query1);
										for ($i = 1; $i <= $count; $i++) {
											if ($count > 0) {
												$row2 = mysqli_fetch_array($query1);
												$row2['employeeId'];
												
												$result219 = mysqli_query($con,"SELECT branchName from branch where branchId = '".$row2['branchCode']."'");
												$row219= mysqli_fetch_array($result219);
												$result319 = mysqli_query($con,"SELECT employeeId from users where username = '".$row2['branchCode']."'");
												$row319= mysqli_fetch_array($result319);
												$result419 = mysqli_query($con,"SELECT name,contact from employee where empId = '".$row319['employeeId']."'");
												$row419= mysqli_fetch_array($result419);
												
												echo "<tr><td>" . $i .  "</td>";
												echo "<td>" . $row219['branchName'] . "</td>";
												echo "<td>" . $row419['name'] . "</td>";
												echo "<td>" . $row419['contact'] . "</td>";
												echo "<td><b>" . $row2['expense'] . "</b></td>";
												// echo "<td>" . $row3['contact'] . "</td>";
												echo "<td><a class='btn btn-success' href='viewexpensebranch.php?branch=" . $row2['branchCode'] . "'><i class='fa fa-edit'></i></a></td></tr>";
											}
										}
									 ?>
									</tbody>
								</table>
							</div>
						</div>				
						
						
						<!--Top Gold Unchecked-->
						<div id="tab-3" class="tab-pane">
							<div class="panel-body">
								<table id="example1" class="table table-striped table-bordered">
									<thead>
										<tr class="theadRow">
											<th><i class="fa fa-sort-numeric-asc"></i></th>
											<th>Branch Name</th> 
											<th>City</th> 
											<th>State</th> 
											<th>BM Name</th>
											<th>BM Contact</th>
											<th>Net W</th>
											<th>View</th>

										</tr>
									</thead>
									<tbody>
										<?php

										$query2 = mysqli_query($con, "SELECT round(SUM(netW),2) AS netW,branchId FROM trans WHERE sta='' AND staDate='' AND status='Approved' AND metal='Gold' group by branchId order by netW DESC;");

										$count1 = mysqli_num_rows($query2);
										for ($i = 1; $i <= $count1; $i++) {
											if ($count1 > 0) {
												$row3 = mysqli_fetch_array($query2);
												$row3['employeeId'];
												$result220 = mysqli_query($con,"SELECT branchName,city,state from branch where branchId = '".$row3['branchId']."'");
												$row220= mysqli_fetch_array($result220);
												$result320 = mysqli_query($con,"SELECT employeeId from users where username = '".$row3['branchId']."'");
												$row320= mysqli_fetch_array($result320);
												$result420 = mysqli_query($con,"SELECT name,contact from employee where empId = '".$row320['employeeId']."'");
												$row420= mysqli_fetch_array($result420);

												echo "<tr><td>" . $i .  "</td>";
												echo "<td>" . $row220['branchName'] . "</td>";
												echo "<td>" . $row220['city'] . "</td>";
												echo "<td>" . $row220['state'] . "</td>";
												echo "<td>" . $row420['name'] . "</td>";
												echo "<td>" . $row420['contact'] . "</td>";
												echo "<td><b>" . $row3['netW'] . "</b></td>";
												echo "<td><a class='btn btn-success' href='viewstockgold.php?branch=" . $row3['branchId'] . "'><i class='fa fa-edit'></i></a></td></tr>";
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
		</div>
	</div>		
<div style="clear:both"></div>
	<?php
	include("footer.php");
	?>										