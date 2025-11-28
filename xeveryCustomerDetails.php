<?php
	session_start();
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
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
	font-size: 18px;
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
	color:#800000;
	}
	.fa_icon {
	color:#ffd700;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_Icon fa fa-users"></i> Customer History </h3>
				</div>
				
				<div class="panel-body">					
					<table class="table table-bordered">
						<caption>Bills</caption>
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>Branch</th>
								<th>Customer</th>
								<th>Type</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['id']) && $_GET['id']!=''){
									$contact = $_GET['id'];
									$i = 1;
									$query2 = mysqli_query($con,"SELECT t.name, t.type, t.date, b.branchName 
									FROM trans t LEFT JOIN branch b ON t.branchId = b.branchId
									WHERE t.phone='$contact'");
									while($row = mysqli_fetch_assoc($query2)){
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['date']."</td>";
										echo "</tr>";
										$i++;
									}
								}
							?>
						</tbody>
					</table>
				</div>
				
				<div class="panel-body">				
					<table class="table table-bordered">
						<caption>Enquiry</caption>
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>Branch</th>
								<th>Customer</th>	
								<th>Type</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['id']) && $_GET['id']!=''){
									$contact = $_GET['id'];
									$i = 1;
									$query1 = mysqli_query($con, "SELECT w.name, w.date, w.gold, b.branchName 
									FROM walkin w LEFT JOIN branch b ON w.branchId = b.branchId
									WHERE w.mobile='$contact'");
									while($row = mysqli_fetch_assoc($query1)){
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['gold']."</td>";
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
<?php include("footer.php"); ?>