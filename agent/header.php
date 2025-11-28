<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	if (!isset($_SESSION['login_username']) && !empty($_SESSION['login_username'])) {
		$_SESSION = array();
		session_destroy();
		header("location:index.php");
	}
?>
<!DOCTYPE html>
<html>	
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="theme-color" content="#08347d">
		<!-- Page title -->
		<title>Attica Gold</title>
		
		<!-- Vendor styles -->
		<link rel="shortcut icon" type="image/png" href="../images/favicon.png" />
		
		<!-- Vendor styles -->
		<link rel="stylesheet" href="../vendor/fontawesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../vendor/metisMenu/dist/metisMenu.css" />
		<link rel="stylesheet" href="../vendor/animate.css/animate.css" />
		<link rel="stylesheet" href="../vendor/bootstrap/dist/css/bootstrap.css" />
		<link rel="stylesheet" href="../vendor/xeditable/bootstrap3-editable/css/bootstrap-editable.css" />
		<link rel="stylesheet" href="../vendor/select2-3.5.2/select2.css" />
		<link rel="stylesheet" href="../vendor/select2-bootstrap/select2-bootstrap.css" />
		<link rel="stylesheet" href="../vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css" />
		<link rel="stylesheet" href="../vendor/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />
		<link rel="stylesheet" href="../vendor/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" />
		
		<!-- App styles -->
		<link rel="stylesheet" href="../fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
		<link rel="stylesheet" href="../fonts/pe-icon-7-stroke/css/helper.css" />
		<link rel="stylesheet" href="../styles/style.css">
		<link rel="stylesheet" href="../styles/static_custom.css">
		<script src="../scripts/attic.js" type="text/javascript"></script>
		<script src="../vendor/bootstrap/dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../scripts/demo.js"></script>
		<script src="../scripts/jquery.js"></script>
		<link rel="stylesheet" href="styles/hobm_style.css"/>
		<style>
			.dropdown-menu {
			max-height: 500px;
			overflow: scroll;
			}
		</style>
	</head>
	
	<body class="fixed-navbar sidebar-scroll">
		<!-- Header -->
		<div id="header">
			<div class="color-line">
				<marquee style="font-weight:bold;font-size:18px;color:#900;line-height:30px;letter-spacing:1px">
					<!--  TYPE THE SCROLLING MESSAGE HERE  -->
				</marquee>
			</div>
			<a href="https://atticagoldcompany.com" target="_blank">
				<div id="logo" class="light-version">
					<b><span>Attica Gold Pvt Ltd</span></b>
				</div>
			</a>
			<nav role="navigation">
				<div class="header-link hide-menu"><i style="color:#990000" class="fa fa-bars"></i></div>
				<a href="https://atticagoldcompany.com" target="_blank">
					<div class="small-logo">
						<span class="text-primary">Attica Gold</span>
					</div>
				</a>
				<form role="search" class="navbar-form-custom" method="post" action="searchbills.php">
					<div class="form-group">
						<input type="text" placeholder="<?php date_default_timezone_set("Asia/Kolkata");
						echo date("l / d-M-Y / h:i A"); ?>" class="form-control" name="search" readonly>
					</div>
				</form>
				<!--<img src="../images/wish2022.gif" style="height: 30px;margin-bottom:-40px;margin-left:85px" alt="">-->
			<div class="mobile-menu">
				<button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
					<i class="fa fa-chevron-down"></i>
				</button>
			</div>
				<!--<div class="navbar-right" style="margin-top: -20px;">-->
				<div class="navbar-right" >
					<ul class="nav navbar-nav no-borders">
						<li class="dropdown">
							<a class="dropdown-toggle label-menu-corner" href="#" data-toggle="dropdown">
								<i style="color:#900" class="fa fa-phone"></i>
								<span class="label label-success" style="color:white;"><i class="fa fa-exclamation"></i></span>
							</a>
							<ul class="dropdown-menu hdropdown animated flipInX">
								<div class="title text-success" style="background:#ffcf40;"><b><i class="fa fa-phone"></i> Emergency Contacts</b></div>
								<li><a style="color:#900">Billing Approval / Customer OTP : 8925537846 </a></li>
								<li><a style="color:#900">Release Approval (Cash) / Closing Reopen : 8925537846 </a></li>
								<li><a style="color:#900">Software Issues : 8925537891 </a></li>
								<li><a style="color:#900">Social Media : 8925537892 </a></li>
								<li><a style="color:#900">Call Center : 8925536999 </a></li>
								<li><a style="color:#900">Weighing Scale License Renewal : 9035015936 </a></li>
								<li><a style="color:#900">Karat Meter : 9035015936 </a></li>
								<!--<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> Asset / UPS / Power Issue</b></li>-->
								<!--<li><a style="color:#900"></a></li>-->
								<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i>  IT / Webmail </b></li>
								<li><a style="color:#900">KA : 8925537881</a> </li>
								<li><a style="color:#900">TN : 8925537882</a></li>
								<li><a style="color:#900">AP : 8925537883</a></li>
								<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i>  Release Approval (IMPS) / IMPS </b></li>
								<li><a style="color:#900">KA : 8884414103</a></li>
								<li><a style="color:#900">AP/T : 8884414300</a></li>
								<li><a style="color:#900">TN : 8884410300</a></li>
								<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i>  Zonals </b></li>
								<li><a style="color:#900">BLR : 8925537861 </a></li>
								<li><a style="color:#900">KA : 8925537866 </a></li>
								<li><a style="color:#900">TN1 : 8925537870 , TN2 : 8925537008</a></li>
								<li><a style="color:#900">TN3 : 8925537871</a></li>
								<li><a style="color:#900;">AP/TS : 8925537875 / 8925537876 </a></li>
								<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i>  Legal Department </b></li>
								<li><a style="color:#900">Sandhya Ramakrishna : 8925537822 </a></li>
								<li><a style="color:#900">Pasha : 8880080900 </a></li>
								<li class="summary"><b><i style="color:#900" class="fa fa-phone"></i>  HR Department </b></li>
								<li><a style="color:#900">KA : 8925537831 </a></li>
								<li><a style="color:#900">AP/T : 8925537832 </a></li>
								<li><a style="color:#900">TN : 8925537833 </a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="logout.php" title="Go Back">
								<b><i style="color:#900" class="pe-7s-upload pe-rotate-90"></i></b>
							</a>
						</li>
					</ul>
				</div>
			</nav>
		</div>