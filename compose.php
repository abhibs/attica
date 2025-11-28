<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type=='AccHead'){
	    include("header.php");
	    include("menuaccHeadPage.php");
	}
	else if($type=='Accounts IMPS'){
	    include("header.php");
	    include("menuimpsAcc.php");
	}	
	else if($type=='Expense Team'){
	    include("header.php");
	    include("menuexpense.php");
	}
	else if($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
	}
	else if($type=='Zonal'){
		include("header.php");
	    include("menuZonal.php");
	}
	else if($type=='HR'){
		include("header.php");
	    include("menuhr.php");
	}

	else if($type=='ApprovalTeam'){
		include("header.php");
	    include("menuapproval.php");
	}
	else if($type=='Assets'){
		include("header.php");
        include("menuassets.php");
	}
	else if($type=='Call Centre'){
		include("header.php");
	    include("menuCall.php");
	}
	else if($type=='Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$today = date('Y-m-d');
	$pastDay = date('Y-m-d',strtotime("-7 days"));
?>
<style>
	#wrapper .side-bar-compose .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	#wrapper .hbuilt{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	.note-editor.note-frame{
	border:none;
	}
	.note-editor.note-frame .note-editing-area .note-editable {
	min-height:230px;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	thead{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	color: #f2f2f2;
	font-size:11px;
	}	
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
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
	ul.mailbox-list li.active a, ul.h-list li.active a {
	background: #f5f5f5;
	border: 1px solid #c0c0c0;
	border-radius: 5px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
</style>
<!-- Main Wrapper -->
<div id="wrapper">
    <div class="row content">
		<div class="col-md-2">
			<div class="hpanel side-bar-compose">
				<div class="panel-body">
					<a href="compose.php" class="btn btn-success btn-block m-b-md"><i style="color:#ffcf40" class="fa fa-edit"></i> COMPOSE</a><hr>
					<ul class="mailbox-list">
						<li class="active"><a href="inbox.php">
							<?php
								if($type=='Master' || $type=='HR' || $type=='ApprovalTeam' || $type=='Zonal' || $type=='Assets'){
									$name = $type;
								}
								
								
								else if($type=='Accounts' || $type=='Accounts IMPS' || $type=='Expense Team' || $type=='AccHead'){
									$name = 'Accounts';
								}
								else if($type=='Branch'){
									$branchId = $_SESSION['branchCode'];
									$namesql = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branchId'"));
									$name = $namesql['branchName'];
								}
								$count = mysqli_num_rows(mysqli_query($con,"SELECT id FROM mails WHERE flag!=1 AND toBranch='$name' AND date BETWEEN '$pastDay' AND '$today'"));
							?>
							<span class="pull-right"><?php echo $count; ?> </span>
						<i style="color:#900" class="fa fa-envelope"></i> INBOX</a></li>
						<li><a href="sentItems.php"><i style="color:#900" class="fa fa-paper-plane"></i> SENT</a></li>
						<!--<li><a href=""><i style="color:#900" class="fa fa-trash"></i> TRASH</a></li>-->
					</ul>
					<hr>
				</div>
			</div>
		</div>
		<div class="col-md-10">
			<div class="hpanel email-compose">
				<div class="panel-heading hbuilt">
					<div class="p-xs">
						<form method="POST" class="form-horizontal" action="add.php" enctype="multipart/form-data">
							<input type="hidden" name="fromUser" value="<?php if($type=='Branch'){ echo $name; }else{ echo $type; } ?>">
							<div class="form-group">
								<div class="col-sm-1" align="right" style="line-height:30px"><label class="text-success">To:</label></div>
								<div class="col-sm-5">									
									<select class="form-control" id="to" name="to" required>
										<option disabled="disabled" selected="true" value=""></option>
										<option>HR</option>
										<option>Accounts</option>
										<option>Zonal</option>
										<option>Assets</option>
										<option>ApprovalTeam</option>
										<option disabled="disabled"></option>
										<?php
											if($type != 'Branch'){
												$branchs = mysqli_query($con,"SELECT branchName FROM branch WHERE status=1");
												while($row = mysqli_fetch_assoc($branchs)){
													echo "<option>".$row['branchName']."</option>";
												}
											}
										?>
									</select>									
								</div>
								<div class="col-sm-1" align="right" style="line-height:30px"><label class="text-success">Cc:</label></div>
								<div class="col-sm-5">									
									<input type="text" readonly placeholder="Server" class="form-control" id="cc" name="cc">									
								</div>
							</div>
							<div style="clear:both"></div>
							<div class="form-group">
								<div class="col-sm-1" align="right" style="line-height:30px"><label class="text-success">Subject:</label></div>
								<div class="col-sm-11">								
									<input type="text" class="form-control" placeholder="Enter Email subject" id="sub" name="sub" autocomplete="off">							
								</div>
							</div>
							<div style="clear:both"></div>
							<div class="form-group">
								<div class="col-sm-1" align="right" style="line-height:30px"><label class="text-success">Content:</label></div>
								<div class="col-sm-11"></div>
								<div class="col-sm-12">
									<textarea class="summernote" placeholder="Enter Content" id="content" name="content"></textarea>
								</div>
								<label class="col-sm-2 control-label text-success" style="text-align:left">Attach File:</label>
								<div class="col-sm-4">
									<div class="input-group"><span style="background:#ffcf40" class="input-group-addon"><span style="color:#990000" class="fa fa-paperclip"></span></span>
									<input style="background:#ffcf40" type="file" id="file" multiple class="form-control" name="file"></div>
								</div>
								<div class="col-sm-6" align="right">
									<button class="btn btn-success" id="sendEmail" name="sendEmail"><span style="color:#ffcf40" class="fa fa-paper-plane"></span> Send email</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php  include("footer.php");  ?>
	<script>
		
		/* ---------------- FILE UPLOAD SIZE / EXTENSION RESTRICTION --------------- */
		$(document).ready(function() {
			
			$("#file").change(function() {
				var fileInput = document.getElementById('file');              
				var filePath = fileInput.value;			  
				if (!allowedFileExtensions.exec(filePath)) {
					alert('Invalid file type');
					fileInput.value = '';
					return false;
				}				
				var FileSize = file.files[0].size / 1024 / 1024; // in MB
				if (FileSize > 1) {
					alert('File size exceeds 1 MB');
					$("#file").val('');
				}				
			});
			
		});	
	</script>