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
	
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$sqlRead = mysqli_query($con,"UPDATE mails SET flag=1 WHERE id='$id'");
		$row = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM mails WHERE id='$id'"));
	}
?>
<style>
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
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
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
	.hpanel .hbuilt.panel-heading{
	padding:20px;
	}	
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	#wrapper .email-content-display{
	margin-bottom: 20px;
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
								if($type=='Master' ||  $type=='HR' || $type=='ApprovalTeam' || $type=='Zonal' || $type=='Assets'){
									$name = $type;
								}
							
							if($type=='Master' || $type=='ApprovalTeam' || $type=='Zonal' || $type=='Assets'){
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
						<!--<li><a href=""><i style="color:#900" class="fa fa-trash"></i> Trash</a></li>-->
					</ul>
					<hr>
				</div>
			</div>
		</div>	
		<div class="col-md-10">
			<div class="hpanel email-compose">
				<div class="panel-body" style="margin-bottom: 10px;">
					<div class="p-xs h4">
                        <small class="pull-right">
                            <b><?php echo $row['date']." ( ".$row['time']." )";  ?></b>
						</small>
						<b class="text-success">From : <?php echo $row['fromBranch']; ?></b>
					</div>
					<div class="border-top border-left border-right bg-light">
						<div class="p-m" style="font-size:14px">
							<div>
								<span class="font-extra-bold">To : </span>
								<?php echo $row['toBranch']; ?>
							</div>
							<br>
							<div>
								<span class="font-extra-bold">Subject : </span>
								<?php echo $row['subject']; ?>
							</div>
						</div>
					</div>
					<hr>
					<div class="email-content-display" style="padding-left: 20px;">
						<?php echo $row['content']; ?>
					</div>
					<?php if($row['file']!=""){ ?>
						<hr>
						<div class="col-sm-3">
							<a class="btn btn-default" style="background-color: #FFCF40; border: 1px solid #eee;" target="popup" href="MailDocuments/<?php echo $row['file'];?>"><i class="fa fa-paperclip"></i> Attached File</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>