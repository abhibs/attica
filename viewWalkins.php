<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	include("header.php");
	if($type=='Master')
	{
		include("menumaster.php");
	}
	else if($type=='Admin')
	{
		include("menuadmin.php");
	}
	else if($type=='Accounts')
	{
		include("menuacc.php");
	}
	else if($type=='Zonal')
    {
        include("menuZonal.php");
    }
	include("dbConnection.php");
	$date=date('Y-m-d');
	$search="";
	$search1="";
	$search2="";
	if(isset($_GET['aaa']))
	{
		$search=$_REQUEST['dat2'];
		$search1=$_REQUEST['dat3'];
		$search2=$_REQUEST['bran'];
		$_SESSION['se']=$search2;
		$_SESSION['one']=$search1;
		$_SESSION['two']=$search;
	}
?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
			    <form action="" method="GET">
							<div class="col-sm-3">
								<label class="text-success">Branch Id</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
									<input list="cusId"  class="form-control" name="bran" id="bran" placeholder="Branch Id" />  
								</div>
							</div>
							<datalist id="cusId">
								<option value="All Branches">All Branches</option>
								<?php 
									$sql="select * from branch";
									$res = mysqli_query($con, $sql);
									while($row = mysqli_fetch_array($res)) { ?>
									<option value="<?php echo $row['branchId']; ?>">
									<?php echo $row['branchName']; ?></option>
								<?php } ?>
							</datalist>
							<div class="col-sm-3">
								<label class="text-success">From Date:</label> 
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
									<input type="date"  class="form-control" id="dat3" name="dat3" />
								</div>
							</div>
							<div class="col-sm-3"> 
								<label class="text-success">To Date:</label>
								<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
									<input type="date" class="form-control"  id="dat2" name="dat2" />
								</div>
							</div>
							<div class="col-sm-1"> 
								<label class="text-success">_________________</label><br>
								<button class="btn btn-success" name="aaa" id="aaa"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
						    </div>
						</form>
						<!--div class="col-sm-2"> 
							<label class="text-success">______________________</label><br>
							<form action="export.php" enctype="multipart/form-data" method="post">
							    <button type="submit" name="exports" class="btn btn-primary" value="Export Excel" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button>
							</form>
						</div-->
						<div style="clear:both"></div>
				<div class="hpanel">
					<div class="panel-heading">
						<h3 class="text-success"><b><i class="fa fa-edit"></i> View Walkins</b></h3>
					</div>
					<div class="panel-body" style="box-shadow:10px 15px 15px #999;">
						<form id="frm1" action="" method="GET" onSubmit="return validate();"> 
							<table id="example3" class="table table-striped table-bordered">
								<thead>
									<tr class="text-success">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>Branch Name</th>
										<th>Customer</th>
										<th>Gross Weight</th>
										<th>Net Weight</th>
										<th>Gross Amount</th>
										<th>Expected Amount</th>
										<th>Offer amount</th>
										<th>Date</th>
										<th>Time</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if($search=="" && $search1==""  && $search2=="")
										{
											$query=mysqli_query($con,"SELECT * FROM walkin where date = '$date'".$extra_query." order by id DESC;");
										}
										else if($search!=="" && $search1!==""  && $search2!="All Branches")
										{
											$query=mysqli_query($con,"SELECT * FROM walkin where date BETWEEN '$search1' AND '$search' AND branchId='$search2' ".$extra_query." order by id DESC;");
										}
										else if($search!=="" && $search1!==""  && $search2="All Branches")
										{
											$query=mysqli_query($con,"SELECT * FROM walkin where date BETWEEN '$search1' AND '$search' ".$extra_query." order by id DESC;");
										}
										$count=mysqli_num_rows($query);
										for($i=1;$i<=$count;$i++)
										{
											if($count>0)
											{
												$row=mysqli_fetch_array($query);
												$branch=$row['branchId'];
												$sql="select branchName from branch where branchId='$branch'";
												$res=mysqli_query($con,$sql);
												$row2=mysqli_fetch_array($res);
												echo "<tr><td>" . $i .  "</td>";
												echo "<td>" . $row2['branchName'] . "</td>";
												echo "<td>" . $row['name'] . "</td>";
												echo "<td>" . round($row['gwt'],2). "</td>";
												echo "<td>" . round($row['nwt'],2). "</td>";
												echo "<td>" . round($row['gamt'],0). "</td>";
												echo "<td>" . round($row['eamt'],0). "</td>";
												echo "<td>" . round($row['oamt'],0). "</td>";
												echo "<td>" . $row['date']. "</td>";
												echo "<td>" . $row['time']. "</td>";
												$status=$row['status'];
												if($status==0)
												{
													$status='Pending';
												}
												else
												{
													$status='Enquired';
												}
												echo "<td>" . $status . "</td></tr>"; "</td>";
											}
										}
									?>
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</div>		
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
</div>