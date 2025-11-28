<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);

	date_default_timezone_set('Asia/Calcutta');
	$type = $_SESSION['usertype'];
	include("dbConnection.php");
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
		include('libs/phpqrcode/qrlib.php');
		$tempDir = 'temp/';
		
	}
	else{
		include("logout.php");
	}
	
	$date = date('Y-m-d');
	$branchCode = $_SESSION['branchCode'];	

?>

<style>
	.list-cust{
		list-style-type: square;
		margin: 0;
		padding-left: 15px;
	}
	.li-cust{
		padding-bottom: 15px;
	}
	.navbar-form-custom{
		margin-left:20px;
	}	

 	.center {
		display: block;
		margin-left: auto;
		margin-right: auto;
		width: 50%;
	}
	body{
		background:#E3E3E3;
	}
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-4">
		</div>
		<div class="col-lg-4">
			<div class="hpanel">		
				
				<div class="panel-heading hbuilt" style="margin-bottom:10px;background:#990000;">
					<img src="images/atticalogo.png" class="center">								
				</div>
				
				<div class="panel-body" style="padding : 5px 30px 15px 30px;height:500px;">
					<div class="panel-heading text-center">
						<h3 class="text-success text-center"><b>SCAN QR CODE</b></span></h3>
					</div>
					<div class="col-sm-12 col-md-12 text-center">

					</div>
					<div id="qr_code_display" class="col-sm-12 col-md-12 text-center">

					</div>	
					<div class="col-sm-12 col-md-12 text-center"></div>
				</div>


			</div>
		</div>		
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>	
	<script>

		fetch_qr_code();
		function fetch_qr_code() {
			
			var branchID = '<?php echo $branchCode;?>';
			//alert(branchID)
			var req = 	$.ajax({
							url: "add.php",
							type: "POST",
							data: {branch:branchID,fetch_qr_code:"branch_QR"},
						});
						req.done(function(response) {
							$("#qr_code_display").html(response);
						});
		}	
/* 		jQuery(document).bind("contextmenu",function(e){
			return false;
		});	 */
		
	</script>

