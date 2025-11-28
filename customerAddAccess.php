<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master'){
	    require("header.php");
		include("menumaster.php");
	}
	else if($type=='Software'){
	    include("header.php");
		include("menuSoftware.php");
	}
	else{
        include("logout.php");
	}
	
	include("dbConnection.php");
	$presentAccessDay = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM misc WHERE purpose='Add Customer'"));
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow:10px 15px 15px #999;
	border: 1px solid #edf2f9;
	border-radius:7px;
	background-color: #f5f5f5;
	padding: 20px 20px 35px 20px;
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
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success no-margins" style="padding-top:23px"><span style="color:#990000" class="fa fa-user-plus"></span><b> Branch-side Customer Adding Access</b></h3>
				</div>
				<div class="panel-body">
					<div class="panel-heading">
						<h3 class="text-success no-margins">Present Access Day Is : 
							<?php 
								echo "<span style='color:#990000'>".$presentAccessDay['day']."</span> ( From ".$presentAccessDay['date']." / ".$presentAccessDay['time']." )";
							?>
						<hr></h3>
					</div>
					<form action="edit.php" method="POST">
						<input type="hidden" name="access" value="Yes">
						<div class="col-sm-2">
							<button type="submit" name="accessDaySubmit" class="btn btn-success btn-block" style="margin-top:23px;"> Give Access</button>
						</div>
					</form>
					<form action="edit.php" method="POST">
						<input type="hidden" name="access" value="No">
						<div class="col-sm-2">
							<button type="submit" name="accessDaySubmit" class="btn btn-danger btn-block" style="margin-top:23px;"> Remove Access</button>
						</div>
					</form>
				</div>
			</div>
			
		</div>
	</div>
<?php include("footer.php");?>