
<!DOCTYPE html>
<html>
	<head>
		
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<meta name="theme-color" content="#760107">
		<!-- Page title -->
		<title>Attica Gold</title>
		
		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
		
		<link rel="shortcut icon" type="image/png" href="../images/favicon.png" />
		
		<!-- Vendor styles -->
		<link rel="stylesheet" href="../vendor/fontawesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../vendor/metisMenu/dist/metisMenu.css" />
		<link rel="stylesheet" href="../vendor/animate.css/animate.css" />
		<link rel="stylesheet" href="../vendor/bootstrap/dist/css/bootstrap.css" />
		
		<!-- App styles -->
		<link rel="stylesheet" href="../fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
		<link rel="stylesheet" href="../fonts/pe-icon-7-stroke/css/helper.css" />
		<link rel="stylesheet" href="../styles/style.css">
	</head>
	<body style="background:#760107">		
		<div class="login-container">
			<div class="row">
				<div class="col-md-12" align="center">
					<img src="../images/group-of-attica-gold-companies.jpg" style="width:80%;padding:40px">
					<div class="hpanel">
						<div class="panel-body" style="background:none;box-shadow:0px 0px 35px #f8e75c;border:none;border-radius:5px;padding:10px">
							<form action="checkLogin.php" method="POST" id="loginForm" role="form" autocomplete="off">
								<div class="err" id="add_err" style="font-size:12px;color:#ff0000;text-align:center;"></div>
								<div class="col-sm-5">
									<div class="input-group">
										<span style="background:#f8e75c;color:#900;border:none" class="input-group-addon"><span class="fa fa-users"></span></span>
										<input type="text" placeholder="Username" title="Please enter you username" required="" value="" name="username" id="username" class="form-control">
									</div>
								</div>
								<div class="col-sm-4">
									<div class="input-group">
										<span style="background:#f8e75c;color:#900;border:none" class="input-group-addon"><span class="fa fa-key"></span></span>
										<input type="password" id="password" name="password" title="Please enter your password" placeholder="Password" required="" value="" name="password" id="password" class="form-control">
									</div>
								</div>
								
								<div class="col-sm-3">
									<button type="submit" id="submit" style="background:#f8e75c;color:#900;font-weight:bold;border:none" name="submit" class="btn btn-success btn-block"><span style="color:#900" class="fa fa-sign-in"></span> Login</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div style="color:#f8e75c;font-weight:bold" class="col-md-12 text-center">
					<strong>Copyright &copy; 2017.  Powered by Attica Gold Pvt Ltd</strong>
				</div>
			</div>
		</div>
		<script src="../vendor/jquery/dist/jquery.min.js"></script>
		<script src="../vendor/jquery-ui/jquery-ui.min.js"></script>
		<script src="../vendor/slimScroll/jquery.slimscroll.min.js"></script>
		<script src="../vendor/bootstrap/dist/js/bootstrap.min.js"></script>
		<script src="../vendor/metisMenu/dist/metisMenu.min.js"></script>
		<script src="../vendor/iCheck/icheck.min.js"></script>
		<script src="../vendor/sparkline/index.js"></script>
		
		<!-- App scripts -->
		<script src="../scripts/homer.js"></script>
		
	</body>
</html>