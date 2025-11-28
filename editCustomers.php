<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
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
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else{
		include("logout.php");
	}
?>
<style>
	th{
	text-align:center
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	border: none;
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
</style>
<div id="wrapper">
	<div class=" row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<h3 class="text-success"><span class="fa fa-user" style="color:#990000"></span><b> CUSTOMER DETAILS</b></h3>
				</div>
				<div class="col-xs-4">
					<form action="" method="GET">
						<div class="input-group">
							<input type="text" class="form-control" name="mobile" placeholder="CUSTOMER MOBILE" maxlength="10" required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block btn-sm" id="mobileSearch" type="submit"><i class="fa fa-search" ></i></button>
							</span>
						</div>
					</form>
				</div>
				<div style="clear:both"><br></div>
				<div class="panel-body">
					<table class="table table-bordered">
						<thead>
							<tr class="theadRow">
								<th>PHONE</th>
								<th>CUSTOMERID</th>
								<th>NAME</th>
								<th>GENDER</th>
								<th>DOB (DD-MM-YYYY)</th>
								<th>ID NUMBER</th>
								<th>ADDRESS NUMBER</th>
								<th style="text-align:center">UPDATE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								if(isset($_GET['mobile'])){
									include("dbConnection.php");
									$mobile = $_GET['mobile'];
									$custSQL = mysqli_query($con,"SELECT customerId,name,gender,dob,mobile,idNumber,addNumber FROM customer WHERE mobile='$mobile'");
									while($row = mysqli_fetch_assoc($custSQL)){
										echo "<tr>
										<form method='POST' action='edit.php'>
										<input type='hidden' name='mobileNumber' class='form-control' autocomplete='off' value='".$row['mobile']."' readonly>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['customerId']."</td>";
										echo "<td><input type='text' name='name' class='form-control' autocomplete='off' value='".$row['name']."'></td>";
										echo "<td><input type='text' name='gender' class='form-control' autocomplete='off' value='".$row['gender']."'></td>";
										echo "<td><input type='text' name='dob' class='form-control' autocomplete='off' value='".$row['dob']."'></td>";
										echo "<td><input type='text' name='idNumber' class='form-control' autocomplete='off' value='".$row['idNumber']."'></td>";
										echo "<td><input type='text' name='addNumber' class='form-control' autocomplete='off' value='".$row['addNumber']."'></td>";
										echo "<td style='text-align:center'><button onClick=\"javascript: return confirm('Please confirm Again');\" class='btn btn-lg' name='editCustomerData' type='submit' style='background-color:transparent'><i class='fa fa-pencil-square-o text-success' style='font-size:16px'></i></button></td>";
										echo "</form></tr>";
									}
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>
