<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$time=date("l / d-M-Y / h:i A");
	$date=date('Y-m-d');
?>
<style>
	#wrapper{
		/*background-color:#f5f5f5;*/
		background-color:#e6e6fa;
	}
	.box{
		padding:10px;
		transition:.2s all; 
	}
	.box-wrap:hover .box{
		transform: scale(.98);
		box-shadow:none;
	}
	.box-wrap:hover .box:hover{
	    filter:blur(0px);
		transform: scale(1.05);
		box-shadow:0 8px 20px 0px #b8860b;
	}
	.hpanel{
		margin-bottom:5px;
	}
	.row{
	    margin-left:0px;
	    margin-right:0px;
	}
</style>
<div id="wrapper">
	<div class="content animate-panel"><b><h4 align="right" class="font-extra-bold no-margins text-success"><i style="color:#990000" class="fa fa-clock-o"></i> <?php echo $time;?></b></h4>
		<div class="row m-t-md box-wrap">
			<!--   BANGALORE GOLD   -->
			<?php
			$bangloreGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT ROUND(SUM(grossW),2) AS gw,ROUND(SUM(netW),2) AS nw FROM trans WHERE date='$date' AND status='Approved' AND metal='Gold' AND branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru' AND status=1)"));
			?>
			<div class="col-lg-4 box">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">BANGALORE GOLD</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">GROSS WEIGHT:</small>
									<h4><i style="color:#990000" class="fa fa-balance-scale"></i> <?php echo $bangloreGold['gw']; ?></h4>
								</div>
								<div class="col-xs-6">				
									<small class="stats-label">NET WEIGHT:</small>
									<h4><i style="color:#990000" class="fa fa-balance-scale"></i> <?php echo $bangloreGold['nw']; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="transactionReportsb.php">VIEW DETAILS</a>
					</div>
				</div>
			</div>

			<!--   TOTAL SILVER   -->
			<?php
			$totalSilver = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(netW) AS netW, SUM(grossW) AS grossW FROM trans WHERE metal='Silver' AND date='$date' AND status='Approved'"));
			?>
			<div class="col-lg-4 box">
				<div class="hpanel stats " style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">TOTAL SILVER</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-balance-scale fa-2x"></i>
							</div>
						</div>
						
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">GROSS WEIGHT:</small>
									<h4><i style="color:#990000" class="fa fa-balance-scale"></i> <?php if(isset($totalSilver['grossW'])){echo round($totalSilver['grossW'],2);}?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">NET WEIGHT:</small>
									<h4><i style="color:#990000" class="fa fa-balance-scale"></i> <?php if(isset($totalSilver['netW'])){echo round($totalSilver['netW'],2);}?></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="silverReports.php">VIEW DETAILS</a>
					</div>
				</div>
			</div>

			<!--   BANGALORE RATE   -->
			<?php
			$goldRate = mysqli_fetch_assoc(mysqli_query($con,"SELECT cash,transferRate FROM gold WHERE date='$date' AND type='Gold' AND city='AGPL000' ORDER BY id DESC"));
			$silverRate = mysqli_fetch_assoc(mysqli_query($con,"SELECT cash FROM gold WHERE date='$date' AND type='Silver' AND city='AGPL000' ORDER BY id DESC"));
			?>
			<div class="col-lg-4 box">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">TODAY'S RATE</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-rupee fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<small class="stats-label">GOLD RATE:</small>
									<h4><i style="color:#990000" class="fa fa-rupee"></i> <?php echo round($goldRate['cash'],0);?>/<?php echo round($goldRate['transferRate'],0);?></h4>
								</div>
								<div class="col-xs-6">
									<small class="stats-label">SILVER RATE:</small>
									<h4><i style="color:#990000" class="fa fa-rupee"></i> <?php echo round($silverRate['cash'],0);?></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="#">VIEW DETAILS</a>
					</div>
				</div>
			</div>
		</div>
	<?php include("footer.php"); ?>
	</div>

