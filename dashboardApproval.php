<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'ApprovalTeam') {
		include("header.php");
		include("menuapproval.php");
	} 
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	$query = mysqli_fetch_assoc(mysqli_query($con, "SELECT 
	COUNT(CASE WHEN (B.state='Karnataka' AND A.metal='Gold') THEN 1 END) AS karGold,
	COUNT(CASE WHEN ((B.state='Andhra Pradesh' OR B.state='Telangana') AND A.metal='Gold') THEN 1 END) AS APTGold,
	COUNT(CASE WHEN ((B.state='Tamilnadu' OR B.state='Pondicherry') AND A.metal='Gold') THEN 1 END) AS TnGold,
	COUNT(CASE WHEN (A.metal='Silver') THEN 1 END) AS Silver
	FROM
	(SELECT metal,branchId FROM trans WHERE status='Approved' AND date='$date') A
	INNER JOIN 
	(SELECT branchId,state FROM branch WHERE status=1) B
	ON A.branchId=B.branchId"));
?>
<style>	
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	.text-success{
	color:#344767;
	text-transform:uppercase;
	font-size:12px;
	font-weight:700;
	}
	.custom-panel-heading{
	background-color: #123C69;
	color: #ffffff;
	text-transform: uppercase;
	}
</style>
<div id="wrapper">
	<div class="content row">
		<div class="hpanel">
			
			<div class="col-lg-4">
				<div class="panel-body no-padding" style="margin-bottom: 20px;">
					<div class="panel-heading custom-panel-heading">Gold Bills</div>
					<ul class="list-group">
						<li class="list-group-item">
							<span class="badge text-success" style="background-color: transparent;"><?php echo $query['karGold']; ?></span>
							Karnataka
						</li>
						<li class="list-group-item ">
							<span class="badge text-success" style="background-color: transparent;"><?php echo $query['APTGold']; ?></span>
							Andhra & Telangana
						</li>
						<li class="list-group-item">
							<span class="badge text-success" style="background-color: transparent;"><?php echo $query['TnGold']; ?></span>
							Tamilnadu
						</li>
					</ul>
				</div>
			</div>
			
			<div class="col-lg-4">
				<div class="panel-body no-padding">
					<div class="panel-heading custom-panel-heading">Silver Bills</div>
					<ul class="list-group">
						<li class="list-group-item">
							<span class="badge text-success" style="background-color: transparent;"><?php echo $query['Silver']; ?></span>
							All States
						</li>
					</ul>
				</div>
			</div>
			
		</div>
	</div>
<?php include("footer.php"); ?>