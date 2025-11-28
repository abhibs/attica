<?php
    session_start();
	error_reporting(E_ERROR | E_PARSE);
    $type=$_SESSION['usertype'];
    if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='AccHead'){
	    include("header.php");
	    include("menuaccHeadPage.php");
	}
	else if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type=='SubZonal'){
       	 	include("header.php");
                include("menuSubZonal.php");
	}
   	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type=='Accounts IMPS'){
		include("header.php");
		include("menuimpsAcc.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.hpanel .panel-body {
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
	font-weight:bold;
	font-size: 12px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}
	.btn-success {
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
	.fa_Icon {
	color:#990000;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	.panel-title{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	}
</style>
<div id="wrapper">
	<div class="row content">
	    <?php if($type != 'ApprovalTeam' && $type != 'Accounts IMPS'){ ?>
	       	
			<div class="col-lg-6">
				<div class="hpanel">
					<div class="panel-heading hbuilt">
						<i class="fa_Icon fa fa-money"></i> <span style="color:#123C69;"> TODAY'S GOLD PRICE</span>
					</div>
					<div class="panel-body">
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Gold">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange=javascript:cal0(this.form); autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers" autocomplete="off" required placeholder="IMPS">
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-lg-12">
								<p class="text-success"><b>STATES</b></p>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" id="checkAllGold">
										<label class="text-success"><b> Select All </b></label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Bangalore" class="CheckGold">
										<label> Bangalore </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Karnataka" class="CheckGold">
										<label> Karnataka </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Tamilnadu" class="CheckGold">
										<label> Tamilnadu </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Andhra Pradesh" class="CheckGold">
										<label> Andhra Pradesh </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Telangana" class="CheckGold">
										<label> Telangana </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Chennai" class="CheckGold">
										<label> Chennai </label>
									</div>
								</div>
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="col-lg-6">
				<div class="hpanel">
					<div class="panel-heading hbuilt">
						<i class="fa_Icon fa fa-money"></i> <span style="color:#123C69;"> TODAY'S SILVER PRICE</span>
					</div>
					<div class="panel-body">
						<form method="POST" class="form-horizontal" action="add.php">
							<input type="hidden" name="metal" value="Silver">
							<div class="col-lg-6">
								<p class="text-success"><b>CASH RATE</b></p>
								<input type="text" name="cash" class="form-control" onchange=javascript:cal0(this.form); autocomplete="off" required placeholder="Cash">
							</div>
							<div class="col-lg-6">
								<p class="text-success"><b>TRANSFER RATE</b></p>
								<input type="text" class="form-control" name="transfers"  autocomplete="off" required placeholder="IMPS">
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-lg-12">
								<p class="text-success"><b>STATES</b></p>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" id="checkAllSilver">
										<label class="text-success"><b> Select All </b></label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Bangalore" class="CheckSilver">
										<label> Bangalore </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Karnataka" class="CheckSilver">
										<label> Karnataka </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Tamilnadu" class="CheckSilver">
										<label> Tamilnadu </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Andhra Pradesh" class="CheckSilver">
										<label> Andhra Pradesh </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Telangana" class="CheckSilver">
										<label> Telangana </label>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="checkbox checkbox-success checkbox-inline">
										<input type="checkbox" name="place[]" value="Chennai" class="CheckSilver">
										<label> Chennai </label>
									</div>
								</div>
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-sm-12" style="text-align:right">
								<button class="btn btn-success" type="submit" name="submitGold">SUBMIT</button>
							</div>	
						</form>
					</div>
				</div>
			</div>
		<?php } ?>	
		
		<div class="col-lg-6">
			<div class="hpanel" >
				<div class="panel-heading">
					<b class="panel-title">TODAY'S GOLD RATE DETAILS</b>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>Time</th>
								<th>City</th>
								<th>Cash Rate</th>
								<th>Transfer Rate</th>
								
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								$sql1 = mysqli_query($con,"SELECT cash,transferRate,city,time FROM gold WHERE date='$date' AND type='Gold' ORDER BY id DESC");
								while($row1 = mysqli_fetch_assoc($sql1)){
									echo "<tr>";
									echo "<td>" . $row1['time'] . "</td>";
									echo "<td>" . $row1['city'] . "</td>";
									echo "<td>" . $row1['cash'] . "</td>";
									echo "<td>" . $row1['transferRate'] . "</td>";
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading">
					<b class="panel-title">TODAY'S SILVER RATE DETAILS</b>
				</div>
				<div class="panel-body">
					<table id="example6" class="table table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>Time</th>
								<th>City</th>
								<th>Cash Rate</th>
								<th>Transfer Rate</th>
								
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								$sql2 = mysqli_query($con,"SELECT cash,transferRate,city,time FROM gold WHERE date='$date' AND type='Silver' ORDER BY id DESC");
								while($row2 = mysqli_fetch_assoc($sql2)){
									echo "<tr>";
									echo "<td>" . $row2['time'] . "</td>";
									echo "<td>" . $row2['city'] . "</td>";
									echo "<td>" . $row2['cash'] . "</td>";
									echo "<td>" . $row2['transferRate'] . "</td>";
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
	</div>
	<script>
		$(document).ready(function(){
			
			$('#checkAllGold').change(function(){
				if(this.checked){
					$('.CheckGold').prop('checked',this.checked);
				}
				else{
					$('.CheckGold').prop('checked',false);
				}
			});
			
			$('.CheckGold').click(function() {
				if ($('.CheckGold:checked').length == $('.CheckGold').length) {
					$('#checkAllGold').prop('checked', true);
					} else {
					$('#checkAllGold').prop('checked', false);
				}
			});
			
			$('#checkAllSilver').change(function(){
				if(this.checked){
					$('.CheckSilver').prop('checked',this.checked);
				}
				else{
					$('.CheckSilver').prop('checked',false);
				}
			});
			
			$('.CheckSilver').click(function() {
				if ($('.CheckSilver:checked').length == $('.CheckSilver').length) {
					$('#checkAllSilver').prop('checked', true);
					} else {
					$('#checkAllSilver').prop('checked', false);
				}
			});
			
		});
	</script>
<?php include("footer.php");?>
