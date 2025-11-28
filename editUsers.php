<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date("Y-m-d");
	/* ------------------------------- Insert new user data  --------------------------------- */
	if(isset($_POST["add_new_user_btn"])){	
		
		$type=$_POST["type"];
		$username=$_POST["username"];
		$password=$_POST["password"];
		$branch=$_POST["branch"];
		$employeeId=$_POST["employeeId"];
		$agent=$_POST["agent"];
		$language=$_POST["language"];
	
		$insertUserQuery = "INSERT INTO users(type,username,password,employeeId,branch,agent,date,ip,language) VALUES ('$type','$username','$password','$employeeId','$branch','$agent','$date','','$language')";	
		if(mysqli_query($con,$insertUserQuery)){
			$response = "<script> alert('User data inserted successfully !') </script>";
		}else{
			$response = "<script> alert('Oops!! Error in storing the data !') </script>";
		} 
		echo $response;
	
	}
?>
<style>
	th{
	text-align:center
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
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
	color: #b8860b;
	}
	tbody{
	font-weight: 600;
	}
</style>
<datalist id="branchList">
    <?php 
        $userType = mysqli_query($con,"SELECT DISTINCT type FROM users WHERE type!='Master'");
        while($userTypeList = mysqli_fetch_array($userType)){
		?>
		<option value="<?php echo $userTypeList['type']; ?>"></option>
	<?php } ?>
</datalist>
<datalist id="user_type">
    <?php 
        $userType = mysqli_query($con,"SELECT DISTINCT type FROM users WHERE type!='Master'");
        while($userTypeList = mysqli_fetch_array($userType)){
		?>
		<option value="<?php echo $userTypeList['type']; ?>"></option>
	<?php } ?>
</datalist>
<datalist id="branch_list"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>
<!--   ADDING NEW USER MODAL   -->
<div class="modal fade" id="add_new_user" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
				<h3>NEW USER</h3>
			</div>
			<div class="modal-body">
				<form method="POST" class="form-horizontal" action="" enctype="multipart/form-data">
					<div class="form-group row">
						<div class="col-xs-12">
							<div class="col-sm-6">
								<label class="text-success">TYPE</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-certificate"></span></span>
									<input list="user_type" class="form-control" name="type" class="form-control" placeholder="SELECT USER TYPE" required>
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">EMPLOYEE ID</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-id-badge"></span></span>
									<input type="text" name="employeeId" id="employeeId" placeholder="Enter Employee ID" maxlength="10"  class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">USERNAME</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="username" id="username" required placeholder="Enter Username" maxlength="10" required class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">BRANCH ID</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-id-badge"></span></span>
									<input list="branch_list" class="form-control" name="branch" class="form-control" placeholder="SELECT BRANCH" required>
								</div>
							</div>							
							<div class="col-sm-6">
								<label class="text-success">PASSWORD</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-lock"></span></span>
									<input type="text" name="password" id="password" required placeholder="Enter Password" maxlength="10" required class="form-control" autocomplete="off">
								</div>
							</div>							
							<div class="col-sm-6">
								<label class="text-success">AGENT ID</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-id-badge"></span></span>
									<input type="text" name="agent" id="agent" placeholder="Enter Agent ID" maxlength="10"  class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">LANGUAGE</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-language"></span></span>
									<input type="text" name="language" id="language" placeholder="Enter Language" maxlength="10"  class="form-control" autocomplete="off">
								</div>
							</div>							
							<div class="col-sm-4"><br>
								<button class="btn btn-success btn-block" name="add_new_user_btn" id="add_new_user_btn" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Submit </button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>
<!-- ************************************   ADDING NEW USER MODAL    ***************************** -->
<div id="wrapper">
	<div class=" row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-7">
					<h3 class="text-success"><span class="fa fa-lock" style="color:#990000"></span><b> USER DETAILS</b></h3>
				</div>
				<div class="col-xs-2" style="margin-top:6.5px">
					<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#add_new_user"><i class="fa fa-user-plus"></i> ADD NEW USER</button>
				</div>				
				<div class="col-xs-3" style="margin-top:6.5px">
					<form action="" method="GET">
						<div class="input-group">
							<input list="branchList" class="form-control" name="userType" placeholder="SELECT TYPE" required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block" id="branchIDsearch" type="submit"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>TYPE</th>
								<th>USERNAME</th>
								<th>PASSWORD</th>
								<th>EMPLOYEE ID</th>
								<th>BRANCH</th>
								<th>AGENT</th>
								<th>DATE</th>
								<th>LANGUAGE</th>
								<th>EDIT</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['userType'])){
									$type = $_GET['userType'];
									$sql = mysqli_query($con,"SELECT * FROM users WHERE type='$type'");
									while($row = mysqli_fetch_assoc($sql)){
										echo "<tr>
										<form method='POST' action='edit.php'>
										<input type='hidden' name='id' value=".$row['id'].">
										<input type='hidden' name='type' value=".$row['type'].">";
										echo "<td>".$row['type']."</td>";
										echo "<td class='text-success'>".$row['username']."</td>";
										echo "<td class='text-success'>**********</td>";
										echo "<td><input type='text' name='employeeId' id='employeeId_".$row['id']."' class='form-control' value='".$row['employeeId']."'></td>";
										echo "<td><input type='text' name='branch' id='branch_".$row['id']."' class='form-control' value='".$row['branch']."'></td>";
										echo "<td><input type='text' name='agent' id='agent_".$row['id']."' class='form-control' value='".$row['agent']."'></td>";
										echo "<td>".$row['date']."</td>";
										echo "<td><input type='text' name='language' id='language_".$row['id']."' class='form-control' value='".$row['language']."'></td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('DO YOU WISH TO PROCEED?');\" class='btn btn-lg' type='submit' name='editUsers'><i class='fa fa-edit text-success' style='font-size:16px'></i></button></td>";
										echo "</form>";
										echo "</tr>";
									}
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php");?>
<script>
$( "#add_new_user_btn" ).one( "click", function( event ) {
	document.getElementById("add-new-customer").submit();
	$('#add_new_user_btn').attr("disabled", true);				
	
}); 
</script>