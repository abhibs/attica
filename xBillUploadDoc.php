<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
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
	$date = date('Y-m-d');
	if($_GET["search_date"]==""){
		$date = date('Y-m-d');
	}else{
		$date = $_GET["search_date"];
	}
	
	$phone=$_GET["phone"];
	$sql = mysqli_query($con,"SELECT name, customerId,idProof,addProof, idNumber,addNumber from customer where mobile=$phone");
	$row = mysqli_fetch_assoc($sql);
	$misc_sql = mysqli_query($con,"SELECT * from customer_misc where phone=$phone");
	$mrow = mysqli_fetch_assoc($misc_sql);
?>
<style>
	.tab{
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
	padding: 0;
	margin: 0;
	border: none;
	}   
	.tab .nav-tabs li a{
	color: #123C69;
	background: #f8f8ff;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover{
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius:3px;
	}
	.tab .nav-tabs li a:before{
	content: "";
	background: #f8f8ff;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
	color: #0C1115;
	background: #f8f8ff;
	font-size: 12px;
	/* outline: 2px solid rgba(19,99,126,0.2); */
	position: relative;
	border: 3px solid #fff;
	border-radius: 10px;
	padding: 2px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
	color: #123C69;
	font-weight:500;
	}	
	.tab-pane{
	background: #f8f8ff;
	padding: 10px 5px 50px 5px;
	min-height:350px;
	}	
	@media only screen and (max-width: 479px){
	.tab .nav-tabs{
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li{
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a{ margin: 0 0 5px; }
	}	
	#wrapper{
	background-color: #f8f8ff;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	}	
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
	}
	.btn-primary{
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
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 14px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}	
	.fa_Icon{
	color: #ffa500;
	}
	.trans_Icon{
	color: #ffe4c4;
	}
	.text-success{
	font-weight:600;
	color: #123C69;
	}
	.panel-heading h3{
	text-transform:uppercase;
	}
	.hpanel .panel-body{
	padding: 5px 15px 5px;
	}	
	.dataTables_wrapper .row{
	margin-right: 0px;
	margin-left: 0px;
	}
	
	.btn-default{
		margin-bottom: 10px;
		background: #b8860b;
		color: aliceblue;
	}
	#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	
	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		/*box-shadow: 0px 0px 2px rgba(0,0,0,1);*/
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
		background-color: #fffafa;
		border-radius:10px;
	}

	legend, button {
		margin-left:8px;
		width:300px;
		background-color: #123C69;
		padding: 5px 15px;
		line-height:30px;
		font-size: 14px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
		margin-bottom:0px;
		letter-spacing: 2px;
	}
</style>

<div id="wrapper">

	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-lg-10">
						<h3 class="text-success"> &nbsp; <b><span style="color:#900" class="fa fa-cloud-upload "></span> Upload Documents </b></h3>
					</div>
					<div class="col-lg-2">
						<a class="btn btn-success pull-right" href="xBillTest.php"><i class="trans_Icon fa fa-backward" aria-hidden="true"></i> Back</a>
					</div>
				</div>
				<div style="clear:both"></div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<div class="col-sm-3">
					<fieldset class="photo-upload">
									
						<legend><i style="padding-top:15px" class="trans_Icon fa fa-user-o"></i> CUSTOMER DETAILS </legend>

						<div style="background-color:#fffafa;border-radius:5px;" class="panel-body">
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> Customer Name</label></div>  <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success">  <?php echo $row['name']; ?> </label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success">Customer ID</label></div>  <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"> <?php echo $row['customerId']; ?> </label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> Mobile</label></div> <div class="col-md-1">:</div>  <div class="col-md-6"><label class="text-success"><?php echo $phone; ?> </label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> <?php echo $row['idProof']; ?></label></div><div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php echo $row['idNumber']; ?> </label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> <?php echo $row['addProof']; ?></label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php echo $row['addNumber']; ?> </label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> PHOTO 1</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['photo_1']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['photo_1'];?>"><i class='pe-7s-photo fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> PHOTO 2</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['photo_2']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><<a href="CustomerMisc/<?php echo $mrow['photo_2'];?>"><i class='pe-7s-photo fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> DOCUMENT 1</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['doc_1']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['doc_1'];?>"><i class='pe-7s-paperclip fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> DOCUMENT 2</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['doc_2']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['doc_2'];?>"><i class='pe-7s-paperclip fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> VIDEO 1</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['video_1']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['video_1'];?>"><i class='pe-7s-video fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> VIDEO 2</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['video_2']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['video_2'];?>"><i class='pe-7s-video fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> AUDIO 1</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['audio_1']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['audio_1'];?>"><i class='pe-7s-musiclist fa-2x'></i></a><?php } ?></label></div>
							<div class="col-md-12"></div>
							<div class="col-md-5"><label class="text-success"> AUDIO 2</label></div> <div class="col-md-1">:</div> <div class="col-md-6"><label class="text-success"><?php if($mrow['audio_2']==""){?><i class='pe-7s-close fa-2x'></i><?php }else{?><a href="CustomerMisc/<?php echo $mrow['audio_2'];?>"><i class='pe-7s-musiclist fa-2x'></i></a><?php } ?></label></div>
						</div>
						</fieldset>
					</div>				

					
					<div class="col-sm-9" id="uploadCustomerDocuments">
						<div style="background:#f5f5f5;border-radius:5px" class="panel-body">
							<form method="POST" action="xBillTest.php" enctype="multipart/form-data">

								<input type="hidden" name='phone' value="<?php echo $phone; ?>">
								<input type="hidden" name='name' value="<?php echo $row['name']; ?>">
								<input type="hidden" name='custId' value="<?php echo $row['customerId']; ?>">
								<input type="hidden" name='photo-1' value="<?php echo $mrow['photo_1']; ?>">
								<input type="hidden" name='photo-2' value="<?php echo $mrow['photo_2']; ?>">
								<input type="hidden" name='doc-1' value="<?php echo $mrow['doc_1']; ?>">
								<input type="hidden" name='doc-2' value="<?php echo $mrow['doc_2']; ?>">
								<input type="hidden" name='video-1' value="<?php echo $mrow['video_1']; ?>">
								<input type="hidden" name='video-2' value="<?php echo $mrow['video_2']; ?>">
								<input type="hidden" name='audio-1' value="<?php echo $mrow['video_2']; ?>">
								<input type="hidden" name='audio-2' value="<?php echo $mrow['audio_2']; ?>">
								
								

								<div class="col-md-6">
									<fieldset class="photo-upload">									
										<legend><i style="padding-top:15px" class="trans_Icon fa fa-picture-o"></i> PHOTO UPLOAD </legend>
										
										<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">										
											<div class="col-md-6" align="center">
												<label class="text-success">PHOTO 1</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="photo_1" id="photo_1" accept="image/*" >
												</div>
											</div>
											<div class="col-md-6" align="center">
												<label class="text-success">PHOTO 2</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="photo_2" id="photo_2" accept="image/*">
												</div>
											</div>								
										</div>

									</fieldset>
								</div>
								
								
								<div class="col-md-6">
								<fieldset class="document-upload">
									
										<legend><i style="padding-top:15px" class="trans_Icon fa fa-file-text-o"></i> DOCUMENT UPLOAD </legend>
										
										<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
										
											<div class="col-md-6" align="center">
												<label class="text-success">DOCUMENT 1</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="doc_1" id="doc_1" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf">
												</div>
											</div>
											<div class="col-md-6" align="center">
												<label class="text-success">DOCUMENT 2</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="doc_2" id="doc_2" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" >
												</div>
											</div>
											</div>

									</fieldset>
								</div>							
								
								
								<div class="col-md-6">
								<fieldset class="video-upload">
									
									<legend><i style="padding-top:15px" class="trans_Icon fa fa-video-camera"></i> VIDEO UPLOAD </legend>

									<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
									<div class="col-md-6" align="center">
									<label class="text-success">VIDEO 1</label>
									<div class="input-group">
									<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
									<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="video_1" id="video_1" accept="video/*">
									</div>
									</div>
									<div class="col-md-6" align="center">
									<label class="text-success">VIDEO 2</label>
									<div class="input-group">
									<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
									<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="video_2" id="video_2" accept="video/*">
									</div>
									</div>								
								
								</fieldset>
								</div>
								
								
								
								<div class="col-md-6">
									<fieldset class="video-upload">
										
										<legend><i style="padding-top:15px" class="trans_Icon fa fa-headphones"></i> AUDIO UPLOAD </legend>

										<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
											<div class="col-md-6" align="center">
												<label class="text-success">AUDIO 1</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="audio_1" id="audio_1" accept="audio/*">
												</div>
											</div>
											<div class="col-md-6" align="center">
												<label class="text-success">AUDIO 2</label>
												<div class="input-group">
													<span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-file-text-o"></span></span>
													<input type="file" style="background:#ffcf40" class="form-control" style="padding:5px 10px" name="audio_2" id="audio_2" accept="audio/*">
												</div>
											</div>								
										</div>								
									
									</fieldset>
								</div>
								<div class="col-sm-12">
									<hr>
								</div>
								<div class="col-sm-12" align='right'>
									<button class="btn btn-primary" name="uploadCustomerDocs" type="submit">
										<span style="color:#ffcf40" class="fa fa-upload"></span> Upload Docs
									</button>
								</div>
							</form>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
