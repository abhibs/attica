<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master'){
	    include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type=='Accounts'){
	    include("header.php");
		include("menuacc.php");
	}
	else if($type=='Accounts IMPS'){
	    include("header.php");
	    include("menuimpsAcc.php");
	}
	else if($type=='AccHead'){
	    include("header.php");
	    include("menuaccHeadPage.php");
	}
	else if($type=='Expense Team'){
	    include("header.php");
	    include("menuexpense.php");
	}
	else if($type=='Call Centre'){
	    include("header.php");
		include("menuCall.php");
	}
	else if($type=='Zonal'){
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
	else if($type=='Assets'){
	    include("header.php");
		include("menuassets.php");
	}
	else if($type == "Agent") {
        include("header.php");
	    include("menuAgent.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
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
					<div class="panel-tools">
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<h3><b><i class="fa_Icon fa fa-institution"></i> Branch Details &nbsp;</b></h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example2" class="table table-striped table-bordered table-hover">
							<thead class="theadRow">
								<tr>
									<th>#</th>
									<th>Branch_ID</th>
									<th>Branch Name</th>
									<th>Branch Address</th>
									<th>Contact</th>					
									<th>Email</th>
									<th>GST</th>
									<th>Open Close Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									$sql = mysqli_query($con,"SELECT branchId,branchName,addr,officeContact,email,gst,openclosestatus FROM branch WHERE Status=1");
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>";
										echo "<td>" . $i .  "</td>";
										echo "<td>" . $row['branchId'] . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['addr'] . "</td>";
										echo "<td>" . $row['officeContact'] . "</td>";
										echo "<td>" . $row['email'] . "</td>";
										echo "<td>" . $row['gst'] . "</td>";
										echo "<td>" . ($row['openclosestatus'] == '1' ? 'Open' : 'Close') . "</td>";
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
