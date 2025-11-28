<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
?>

<style>
	#wrapper{
		background: #f5f5f5;
	}
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
	}
	#wrapper .panel-body{
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
		background-color: #f5f5f5;
	}
</style>
<div id="wrapper">
    <div class="row content">
		<div class="col-sm-12" align="center">
			<div id="my_camera"></div>
		</div>
	</div>
	<div style="clear:both"></div>	
	<?php include("footer.php"); ?>
	<script src="scripts/webcam.min.js"></script>
	<script language="JavaScript">
		Webcam.set({
			width: '900',
			height: '560'
		});
		Webcam.attach( '#my_camera' );
	</script>