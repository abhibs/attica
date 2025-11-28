<?php
	session_start();
	$type = $_SESSION['usertype'];
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
	else if($type == 'SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
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
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
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
	thead{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	color: #f2f2f2;
	font-size:11px;
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
			<div class="hpanel">
				<div class="panel-body">
					<a href="compose.php" class="btn btn-success btn-block m-b-md"><i style="color:#ffd700" class="fa fa-edit"></i> COMPOSE</a> <hr>
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
						<!--<li><a><i style="color:#900" class="fa fa-trash"></i> TRASH</a></li>-->
					</ul>
					<hr>
				</div>
			</div>
		</div>
		<div class="col-md-10">
			<div class="hpanel">
				<div class="panel-body">
					<form action="" method="GET">
						<table id="example5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th style="width:75px"><i style="color:#ffd700" class="fa fa-sort-numeric-asc"></i></th>
									<th style="width:200px"><i style="color:#ffd700" class='fa fa-address-book'></i> From</th>
									<th><i style="color:#ffd700" class='fa fa-book'></i> Subject</th>
									<th style="width:10px"><i style="color:#ffd700" class='fa fa-paperclip'></i></th>
									<th style="width:100px"><i style="color:#ffd700" class='fa fa-calendar'></i> Date</th>
									<th style="width:100px"><i style="color:#ffd700" class='fa fa-clock-o'></i> Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									$sql = mysqli_query($con,"SELECT id,fromBranch,subject,file,date,time,flag FROM mails WHERE toBranch='$name' AND date BETWEEN '$pastDay' AND '$today' ORDER BY id DESC");
									while($row = mysqli_fetch_assoc($sql)){
										if($row['flag'] == 0){
											echo "<tr style='font-weight:bold'>";
											echo "<td>" . $i . "</td>";
											echo "<td><a href='viewMail.php?id=".$row['id']."'>".$row['fromBranch']."</a></td>";
											echo "<td><a href='viewMail.php?id=".$row['id']."'>".$row['subject']."</a></td>";
											if($row['file']!=''){
												echo "<td><i class='fa fa-paperclip'></i></td>";
											}
											else{
												echo "<td></td>";
											}
											echo "<td class='mail-date'>" . $row['date'] . "</td>";
											echo "<td>" . $row['time'] . "</td>";
											echo "</tr>";
										}
										else if($row['flag'] == 1){
											echo "<tr>";
											echo "<td>" . $i . "</td>";
											echo "<td><a href='viewMail.php?id=".$row['id']."'>".$row['fromBranch']."</a></td>";
											echo "<td><a href='viewMail.php?id=".$row['id']."'>".$row['subject']."</a></td>";
											if($row['file']!=''){
												echo "<td><i class='fa fa-paperclip'></i></td>";
											}
											else{
												echo "<td></td>";
											}
											echo "<td>" . $row['date'] . "</td>";
											echo "<td>" . $row['time'] . "</td>";
											echo "</tr>";
										}
										$i++;
									}
								?>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php");?>
