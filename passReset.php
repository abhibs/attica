<?php
	session_start();	
	$type=$_SESSION['usertype'];
	if($type=='HR'){
		include("header.php");
		include("menuhr.php");
	}

	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$branch = $_SESSION['login_username'];
?>
<style>
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
	.btn-danger{
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
		background-color:#e74c3c;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
		text-align:center;
		position:relative;
	}	
	.fa_Icon {
		color:#ffd700;
	}
	.fa_icon{
		color:#990000;
	}
	fieldset {
		box-shadow: 10px 15px 15px #999;
		border: 1px solid #edf2f9;
		background-color: #f5f5f5;
		border-radius:3px;	
		padding: 20px;
	}
	legend{
		margin-left:0px;
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
	@media only screen and (max-width: 500px) {
		legend{
			width:390px;
			font-size: 12px;

		}
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">			
				<fieldset>
					<legend> <i style="padding-top:15px" class="fa_Icon fa fa-key"></i>  RESET PASSWORD </legend>
					<div class="col-sm-12"><br></div>
					<form action="add.php" method="POST"  role="form">
						<div class="err" id="add_err" style="font-size:12px;color:#ff0000;text-align:center;"></div>
						<div class="col-sm-4">
							<label class="text-success">Branch ID</label>
							<div class="input-group">
								<span style="color:#900" class="input-group-addon"><span class="fa fa-bank"></span></span>
								<input type="text" placeholder="Branch ID" title="Please enter your Username" readonly value="<?php echo $branch; ?>" name="user" id="user" class="form-control">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">New Password</label>
							<div class="input-group">
								<span style="color:#900" class="input-group-addon"><span class="fa fa-users"></span></span>
								<input type="text" placeholder="New Password" title="Use only letters and numbers" pattern=".{6,20}" maxlength="20" required value="" name="passw" id="passw" class="form-control">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Confirm New Password</label>
							<div class="input-group">
								<span style="color:#900" class="input-group-addon"><span class="fa fa-key"></span></span>
								<input type="password" id="confirm" name="confirm" title="Use only letters and numbers" placeholder="Retype New Password" pattern=".{6,20}" maxlength="20" required value="" name="confirm" id="confirm" class="form-control">
							</div>
						</div>
						<div class="col-sm-2">
							<label><br></label>
							<input type="submit" id="submitPass"  name="submitPass" class="btn btn-success btn-block" value="Change Password">
						</div>
					</form>
					<div class="col-sm-12"><br></div>
				</fieldset>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?> 
