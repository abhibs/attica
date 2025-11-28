<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
	}
	else if($type=='ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'VM-WAIT') {
 		include("header.php");
    		include("menuvmWait.php");
	}
	else if($type=='SubZonal'){
                include("header.php");
                include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else if($type=='Legal'){
		include("header.php");
		include("menulegal.php");
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
	font-size: 16px;
	color:#123C69;
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
	color: #990000;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading" >
					<h3><i class="fa_Icon fa fa-edit"></i> View Transactions <span class="fa_Icon"><?php if(isset($_GET['phone'])){ echo "- ".$_GET['phone'];} ?></span></h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example1" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Name</th>
									<th>GrossW</th>
									<th>NetW</th>
									<th>grossA</th>
									<th>netA</th>
									<th>Type</th>
									<th>Metal</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if(isset($_GET['phone'])){
										$i=1;
										$phone = $_GET['phone'];
										$sql = mysqli_query($con,"SELECT t.name, t.grossW, t.netW, t.grossA, t.netA, t.date, t.type, t.metal, b.branchName 
										FROM trans t LEFT JOIN branch b ON t.branchId=b.branchId
										WHERE t.phone='$phone' AND t.status='Approved'
										ORDER BY t.date DESC");
										while($row = mysqli_fetch_assoc($sql)){
											echo "<tr>";
											echo "<td>". $i ."</td>";
											echo "<td>". $row['branchName'] ."</td>";
											echo "<td>". $row['name'] ."</td>";
											echo "<td>". $row['grossW'] ."</td>";
											echo "<td>". $row['netW'] ."</td>";	
											echo "<td>". $row['grossA'] ."</td>";
											echo "<td>". $row['netA'] ."</td>";
											echo "<td>". $row['type'] ."</td>";	
											echo "<td>". $row['metal'] ."</td>";
											echo "<td>". $row['date'] ."</td>";
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
<?php include("footer.php"); ?>	
