<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='VM-HO'){
        include("headervc.php");
		include("menuvc.php");
	}
	else{
	    include("logout.php");
	}
	include("dbConnection.php");
?>
<div id="wrapper">
	<div class="content row">
		<form action="" method="GET">
			<div class="col-sm-5"> 
				<label class="text-success"></label><br>
				<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Search Customer </h3>
			</div>
			<div class="col-sm-3" style="margin-left:270px;">
				<label class="text-success">Customer Phone Number</label> 
				<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
					<input type="text" class="form-control" name="custph" pattern="[0-9]{10}" maxlength="10" required />
				</div>
			</div>
			<div class="col-sm-1">
				<button class="btn btn-success" style="margin-top:22px"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
			</div>
		</form>
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
					</div>
					<div class="panel-body">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th><span class="fa fa-sort-numeric-asc"></span></th>
									<th>Name</th>
									<th>Contact</th>
									<th>Branch</th>
									<th>Type</th>
									<th>GrossW</th>
									<th>ReleaseA</th>
									<th>Branch Remarks</th>
									<th>Comments</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if (isset($_GET['custph'])){
										$i=1;
										$sql = mysqli_query($con,"SELECT W.*,B.branchName FROM walkin W,branch B WHERE W.branchId=B.branchId AND W.mobile='$_GET[custph]'");
										while($row = mysqli_fetch_assoc($sql)){
											echo "<tr>";
											echo "<td>".$i."</td>";
											echo "<td>".$row['name']."</td>";
											echo "<td>".$row['mobile']."</td>";
											echo "<td>".$row['branchName']."</td>";
											echo "<td>".$row['gold']."</td>";
											echo "<td>".$row['gwt']."</td>";
											echo "<td>".$row['namt']."</td>";
											echo "<td>".$row['remarks']."</td>";
											echo "<td>".$row['comment']."</td>";
											echo "<td>".$row['date']."</td>";
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
<?php include("footerNew.php"); ?>