<?php
    session_start();
    $type=$_SESSION['usertype'];
    if ($type == "Call Centre"){
        include("header.php");
	    include("menuCall.php");
	}
    elseif ($type == "Agent") {
        include("header.php");
	    include("menuAgent.php");
	}
    elseif ($type == "Master") {
        include("header.php");
	    include("menumaster.php");
	}
    else{
        include("logout.php");
	}
	include("dbConnection.php");
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
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
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
	font-size: 10px;
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
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	fieldset {
	margin-top: 1.5rem;
	margin-bottom: 1.5rem;
	border: none;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}
	legend{
	margin-left:8px;
	width:350px;
	background-color: #123C69;
	padding: 5px 15px;
	line-height:30px;
	font-size: 18px;
	color: white;
	text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
	transform: translateX(-1.1rem);
	box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
	margin-bottom:0px;
	letter-spacing: 2px;
	text-transform:uppercase;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	legend:after {
	content: "";
	height: 0;
	width:0;
	background-color: transparent;
	border-top: 0.0rem solid transparent;
	border-right:  0.35rem solid black;
	border-bottom: 0.45rem solid transparent;
	border-left: 0.0rem solid transparent;
	position:absolute;
	left:-0.075rem;
	bottom: -0.45rem;
	}
</style>
<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<?php
		$branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-primary">
						<h3 class="text-primary"><i class="fa_Icon fa fa-sitemap"></i> Send Branch Address Link </h3>
					</h3>
				</div>
				<div class="panel-body">
					<form id="location-sms" method="POST" class="form-horizontal" action="sms1.php" onsubmit="vmSubmitNC.disabled = true; return true;">
						<div align="right" class="col-sm-3">
							<label class="text-success">Phone Number</label>
						</div>
						<div class="col-sm-8">
							<input type="text" name="pho" id="pho" class="form-control" placeholder="Enter the phone number" autocomplete="off" required>
						</div>
						<div style="clear:both"><br></div>
						<div align="right" class="col-sm-3">
							<label class="text-success">Branch</label>
						</div>
						<div class="col-sm-8">
							<input list="branchList" class="form-control" name="brana" id="brana" placeholder="Branch Id" required />
						</div>
						<div style="clear:both"><br></div>
						<div align="right" class="col-sm-3">
							<label class="text-success">Branch Link</label>
						</div>
						<div class="col-sm-8">
							<input name="add" id="add" placeholder="SMS Content" class="form-control" readonly>							
						</div>
						<div class="col-sm-11" align="right">
							<button class="btn btn-success" name="send-sms" type="submit" style="margin-top:30px;padding-left:50px;padding-right:50px"><span style="color:#ffcf40" class="fa fa-paper-plane-o"></span> Send SMS </button>
						</div>
					</form>
				</div>				
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php"); ?>