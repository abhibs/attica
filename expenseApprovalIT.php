<?php
	session_start();
	$type=$_SESSION['usertype'];	
	if($type=='ITMaster'){
		include("header.php");
	    include("menuItMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
?>
<style>
	.tab{
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
	padding: 0;
	margin: 0;
	border: none;
	}   
	.tab .nav-tabs li a{
	color: #123C69;
	background: #f8f8ff;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover{
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius:3px;
	}
	.tab .nav-tabs li a:before{
	content: "";
	background: #f8f8ff;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
	color: #0C1115;
	background: #f8f8ff;
	font-size: 12px;
	/* outline: 2px solid rgba(19,99,126,0.2); */
	position: relative;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
	color: #123C69;
	font-weight:500;
	}	
	.tab-pane{
	background: #f8f8ff;
	padding:10px;
	}	
	@media only screen and (max-width: 479px){
	.tab .nav-tabs{
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li{
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a{ margin: 0 0 5px; }
	}
	#wrapper{
	background: #f5f5f5;
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
	color:#ffcf40;
	}
	.fa_icon{
	color:#990000;
	}
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	.btn-danger{
	background-color: #990000;		
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_icon fa fa-money"></i> Expense Details</h3> 
				</div>
				<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b></button>
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa_Icon fa fa-refresh"></i> Pending Expense</a></li>
						<li class=""><a data-toggle="tab" href="#tab-2"><i class="fa_Icon fa fa-check"></i> Approved Expense</a></li>
						<li class=""><a data-toggle="tab" href="#tab-3"><i class="fa_Icon fa fa-times"></i> Rejected Expense</a></li>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body">
								<table id="example5" class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
											<th>BranchName</th>
											<th>Approve</th>
											<th>Reject</th>
											<th>Particular</th>
											<th>Type</th>
											<th>Amount</th>
											<th>Status</th>
											<th>Remarks</th>
											<th>Date/Time</th>
											<th>Bill</th>
											<th>Second Bill</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;										
											$sqlA = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.branchCode=branch.branchId AND expense.status IN ('Pending','Verified') AND expense.date='$date' AND expense.type='IT Expense'");
											while($rowA = mysqli_fetch_assoc($sqlA)){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $rowA['branchName'] . "</td>";
												echo "<td><a class='btn btn-primary' href='approveRejectExpenseIT.php?appId=".$rowA['id']."' onClick=\"javascript: return confirm('Please confirm To Approve');\"><i style='color:#ffcf40' class='fa fa-check'></i> Approve</a></td>";
												echo "<td><a class='btn btn-danger' href='approveRejectExpenseIT.php?rejId=".$rowA['id']."' onClick=\"javascript: return confirm('Please confirm To Reject');\"><i style='color:#ffcf40' class='fa fa-times'></i>  Reject</a></td>";
												echo "<td>" . $rowA['particular'] . "</td>";
												echo "<td>" . $rowA['type'] . "</td>";
												echo "<td>" . $rowA['amount'] . "</td>";
												echo "<td>" . $rowA['status'] . "</td>";
												echo "<td>" . $rowA['remarks'] . "</td>";
												echo "<td>" . $rowA['date'] . "<br>" . $rowA['time'] . "</td>";
												echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowA['file']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill1 </a></td>";
												if (($rowA['file1']) != "") {
													echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowA['file1']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill2 </a></td>"; 
												}
												else{
													echo "<td>N/A</td>";
												}
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="tab-2" class="tab-pane">
							<div class="panel-body"> 
								<table id="example6" class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
											<th>BranchName</th>
											<th>Particular</th>
											<th>Type</th>
											<th>Requested Amount</th>
											<th>Date/Time</th>
											<th>Bill</th>
											<th>Second Bill</th>											
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											$sqlB = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.branchCode=branch.branchId AND expense.status='Approved' AND expense.date='$date' AND expense.type='IT Expense'");
											while($rowB = mysqli_fetch_assoc($sqlB)){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $rowB['branchName'] . "</td>";											
												echo "<td>" . $rowB['particular'] . "</td>";
												echo "<td>" . $rowB['type'] . "</td>";
												echo "<td>" . $rowB['amount'] . "</td>";
												echo "<td>" . $rowB['date'] . "<br>" . $rowB['time'] . "</td>";
												echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowB['file']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill1 </a></td>";
												if (($rowB['file1']) != "") {
													echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowB['file1']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill2 </a></td>"; 
												}
												else{
													echo "<td>N/A</td>";
												}
												echo "</tr>";
												$i++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="tab-3" class="tab-pane">
							<div class="panel-body"> 
								<table id="example7" class="table table-striped table-bordered table-hover">
									<thead>
										<tr class="theadRow">
											<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
											<th>BranchName</th>
											<th>Particular</th>
											<th>Type</th>
											<th>Requested Amount</th>
											<th>Date/Time</th>
											<th>Bill</th>
											<th>Second Bill</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$i = 1;
											$sqlC = mysqli_query($con,"SELECT expense.*,branch.branchName FROM expense,branch WHERE expense.branchCode=branch.branchId AND expense.status='Rejected' AND expense.date='$date' AND expense.type='IT Expense'");
											while($rowC = mysqli_fetch_assoc($sqlC)){
												echo "<tr>";
												echo "<td>" . $i . "</td>";
												echo "<td>" . $rowC['branchName'] . "</td>";
												echo "<td>" . $rowC['particular'] . "</td>";
												echo "<td>" . $rowC['type'] . "</td>";
												echo "<td>" . $rowC['amount'] . "</td>";
												echo "<td>" . $rowC['date'] . "<br>" . $rowC['time'] . "</td>";
												echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowC['file']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill1 </a></td>";
												if (($rowC['file1']) != "") {
													echo "<td><a class='btn btn-success' target='_blank' href='ExpenseDocuments/".$rowC['file1']."'><i style='color:#ffcf40' class='fa fa-eye'></i> Bill2 </a></td>"; 
												}
												else{
													echo "<td>N/A</td>";
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
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php");?>