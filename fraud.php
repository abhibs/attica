<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	
	if ($type == 'Legal') {
		include("header.php");
		include("menulegal.php");
	} 
	elseif ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	elseif ($type == 'Software') {
		include("header.php");
		include("menuSoftware.php");
	}
	elseif ($type == 'ApprovalTeam') {
		include("header.php");
		include("menuapproval.php");
	}
	elseif ($type=='Zonal'){
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
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	
	if (isset($_POST['submitNC'])) {
		$date = date('Y-m-d');
		$customerQuery=mysqli_query($con,"SELECT * FROM fraud where phone = '$_POST[cusmob]'");
		$count=mysqli_num_rows($customerQuery);
		if($count>0){
			echo "<script type='text/javascript'>alert('FRAUD CUSTOMER NUMBER ALREADY PRESENT IN THE LIST')</script>";
			}else{
			$inscon = "INSERT INTO fraud(name, phone, type, idnumber, date, branchId, reason) VALUE ('$_POST[cusname]', '$_POST[cusmob]', '$_POST[type]', '$_POST[idnumber]', '$date', '$_POST[branchId]', '$_POST[reason]')";
			mysqli_query($con, $inscon);
		}
	}
	
	if(isset($_GET['fraud_id'])){
		$id = $_GET['fraud_id'];
		$query = "DELETE FROM fraud WHERE id='$id'";
		if(mysqli_query($con,$query)){
			header("location:fraud.php");
		}
		else{
			echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'fraud.php'\",150);</script>";
		}
	}
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
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
</style>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header">
                <h3 class="text-success"><b>Add Fraud</b></h3>
			</div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group row" style="padding-left: 60px">
                        <div class="col-sm-5">
                            <label class="text-success">Name</label>
							<input type="text" name="cusname" placeholder="Name" required id="cusname" class="form-control" autocomplete="off">
						</div>
                        <div class="col-sm-5">
                            <label class="text-success">Contact Number</label>
							<input type="text" name="cusmob" style="padding:0px 5px" id="cusmob" pattern="[0-9]{10}" required placeholder="Contact Number" maxlength="10" required class="form-control" autocomplete="off">
						</div>
						<label class="col-sm-12"><br></label>
                        <div class="col-sm-5">
                            <label class="text-success">Type</label>
							<select name="type" class="form-control" id="exampleFormControlSelect1">
								<option selected="true" disabled="disabled">Proof Type</option>
								<option value="Aadhar Card">Aadhar Card</option>
								<option value="Pan Card">Pan Card</option>
								<option value="Driving Licence">Driving Licence</option>
								<option value="Voter ID">Voter ID</option>
							</select>
						</div>
						
                        <div class="col-sm-5">
                            <label class="text-success">ID Number</label>
							<input type="text" name="idnumber" style="padding:0px 5px" placeholder="ID Number" class="form-control" autocomplete="off">
						</div>
						<label class="col-sm-12"><br></label>
						<div class="col-sm-5">
							<label class="text-success">BRANCH</label>
							<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
								<input list="bId" class="form-control" name="branchId" id="branchId" placeholder="Branch" autocomplete="off"/>  
							</div>
						</div>
						
						<datalist id="bId">
							<?php
								$sqlb="select * from branch";
								$resb = mysqli_query($con, $sqlb);					
								while($row = mysqli_fetch_array($resb)){
								?>
								<option value="<?php echo $row['branchName']; ?>"><?php echo $row['branchName']; ?></option>
							<?php } ?>
						</datalist>		
                        <div class="col-sm-5">
                            <label class="text-success">Reason</label>
							<input type="text" name="reason" style="padding:0px 5px" placeholder="reason" class="form-control" autocomplete="off">
						</div>						
						<label class="col-sm-12"><br></label>
                        <div class="col-sm-5"><br>
                            <button class="btn btn-success btn-block" name="submitNC" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Submit</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
    <div style="clear:both"></div>
</div>

<div id="wrapper">
    <div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-10">
							<h3 style="padding-left: 10px;"> <i class="trans_Icon fa fa-users" style="color: #990000"></i> FRAUD LIST</h3>
						</div>
						<div class="col-sm-2">
							<div data-toggle="modal" data-target="#myModal" class='btn btn-success btn-block' style="margin-top: 5px">
								<b><i style="color:#F5F5F5" class="fa fa-plus"></i> Add Fraud</b>
							</div>
						</div>
					</div>
				</div>
				
				<div class="panel-body">
					<table id="example1" class="table table-striped table-bordered table-hover">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>Name</th>
								<th>Mobile</th>
								<th>Type</th>
								<th>ID Number</th>
								<th>Branch</th>
								<th>Reason</th>
								<th>Date</th>
								<?php
									if ($type == 'Software') {
									?>
									<th>Action</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 1;
								$query = mysqli_query($con, "SELECT * FROM fraud ORDER BY id DESC");
								while ($row = mysqli_fetch_assoc($query)) {
									echo "<tr>";
									echo "<td>" . $i . "</td>";
									echo "<td>" . $row['name'] . "</td>";
									echo "<td>" . $row['phone'] . "</td>";
									echo "<td>" . $row['type'] . "</td>";
									echo "<td>" . $row['idnumber'] . "</td>";
									echo "<td>" . $row['branchId'] . "</td>";
									echo "<td>" . $row['reason'] . "</td>";
									echo "<td>" . $row['date'] . "</td>";
									if ($type == 'Software') {
										echo "<td style='text-align:center'><a onClick=\"javascript: return confirm('DELETE THE RECORD ???');\" href='fraud.php?fraud_id=".$row['id']."' class='btn' type='button'><i class='fa fa-trash' style='font-size:18px;color:red'></i></button></td>";
									}
									echo "</tr>";
									$i++;
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>
