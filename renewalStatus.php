<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Asia/Calcutta');
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$branchCode = $_SESSION['branchCode'];
	$date = date("Y-m-d");
	
	$branchData = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName, renewal_date FROM branch WHERE branchId='$branchCode'"));
	$internetRenewalData = mysqli_fetch_assoc(mysqli_query($con, "SELECT internet, ISP_provider, shop_license FROM renewal WHERE branchID='$branchCode'"));
	
?>
<style>
	#wrapper{
	background:#E3E3E3;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 14px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:800;
	font-size: 12px;
	margin: 0px 0px 0px;
	}
	.btn-primary{
	background-color:#123C69;
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
	#wrapper .panel-body{
	box-shadow:10px 15px 15px #999;
	border: 1px solid #edf2f9;
	border-radius: 0px 0px 7px 7px;
	background-color: #f5f5f5;
	padding: 20px;
	}
	#wrapper .panel-heading{
	border-radius: 7px 7px 0px 0px ;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-4">
			<div class="hpanel">
				<div class="panel-heading hbuilt">
					<h3><i style="color:#990000" class="fa fa-balance-scale"></i> Weighing Scale</h3>
				</div>
				<?php 
					if($branchData["renewal_date"]!="" && $branchData["renewal_date"]!="0000-00-00" && $branchData["renewal_date"]!="00-00-0000" && $branchData["renewal_date"]!=null && $branchData["renewal_date"]!=0){
						$current_date = strtotime($date);
						$renewal_date = strtotime($branchData["renewal_date"]);
						$difference = ($renewal_date - $current_date)/60/60/24;
						if($difference<=5 && $difference>0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRY IS APPROACHING IN '.$difference.' DAYS
							</div>';
						}
						elseif($difference==0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRING TODAY
							</div>';
						}
						elseif($difference<0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE ALREADY EXPIRED ON '.$branchData["renewal_date"].'
							</div>';
						}
						else{
							echo '<div class="alert alert-success font-bold">
							<i class="fa fa-bolt"></i> RENEWAL DONE
							</div>';
						}
					}
				?>
				<div class="panel-body no-padding">
					<ul class="list-group">
						<li class="list-group-item">
							<span class="badge badge-primary"><?php echo $branchData["renewal_date"]; ?></span>
							Renewal Date
						</li>
						<li class="list-group-item font-trans">
							Please Contact The IT Department For Renewals & other Query.
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="col-lg-4">
			<div class="hpanel">
				<div class="panel-heading hbuilt">
					<h3><i style="color:#990000" class="fa fa-wifi"></i> Internet</h3>
				</div>
				<?php
					$diff = ($internetRenewalData['internet'] != '') ? round((strtotime($internetRenewalData['internet']) - strtotime($date))/86400) : '';
					if($diff !== ''){
						if($diff < 0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE ALREADY EXPIRED ON '.$internetRenewalData['internet'].'
							</div>';
						}
						else if($diff == 0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRING TODAY
							</div>';
						}
						else if($diff > 0 && $diff <= 5){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRY IS APPROACHING IN '.$diff.' DAYS
							</div>';
						}
						else{
							echo '<div class="alert alert-success font-bold">
							<i class="fa fa-bolt"></i> RENEWAL DONE
							</div>';
						}
					}
					else{
						echo '<div class="alert alert-warning font-bold">
						<i class="fa fa-bolt"></i> CONTACT IT DEPARTMENT
						</div>';
					}
				?>
				<div class="panel-body no-padding">
					<ul class="list-group">
						<li class="list-group-item">
							<span class="badge badge-primary"><?php echo $internetRenewalData["internet"]; ?></span>
							Renewal Date
						</li>
						<li class="list-group-item">
							<span class="badge badge-primary"><?php echo $internetRenewalData["ISP_provider"]; ?></span>
							ISP Provider
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="col-lg-4">
			<div class="hpanel">
				<div class="panel-heading hbuilt">
					<h3><i style="color:#990000" class="fa fa-bank"></i> Shop License</h3>
				</div>
				<?php
					$diff = ($internetRenewalData['shop_license'] != '') ? round((strtotime($internetRenewalData['shop_license']) - strtotime($date))/86400) : '';				
					if($diff !== ''){
						if($diff < 0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE ALREADY EXPIRED ON '.$internetRenewalData['shop_license'].'
							</div>';
						}
						else if($diff == 0){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRING TODAY
							</div>';
						}
						else if($diff > 0 && $diff <= 30){
							echo '<div class="alert alert-danger font-bold">
							<i class="fa fa-exclamation-triangle"></i> RENEWAL DATE EXPIRY IS APPROACHING IN '.$diff.' DAYS
							</div>';
						}
						else{
							echo '<div class="alert alert-success font-bold">
							<i class="fa fa-bolt"></i> NEXT RENEWAL ON '.$internetRenewalData['shop_license'].'
							</div>';
						}
					}
					else{
						echo '<div class="alert alert-warning font-bold">
						<i class="fa fa-bolt"></i> CONTACT CONCERNED DEPARTMENT
						</div>';
					}
				?>
				<div class="panel-body no-padding">
					<ul class="list-group">
						<li class="list-group-item">
							<span class="badge badge-primary"><?php echo $internetRenewalData["shop_license"]; ?></span>
							License Renewal Date
						</li>
						<li class="list-group-item font-trans">
							Please Contact Head Office.
						</li>
					</ul>
				</div>
			</div>
		</div>
		
	</div>
	
<?php include("footer.php"); ?>