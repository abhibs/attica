<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$branchId = $_GET['id'];
	$row = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM branch WHERE branchId='$branchId'"));
?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
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
	tbody{
	font-weight: 600;
	}
	.trInput:focus-within{
	outline: 3px solid #990000;
	}
	.fa{
	color:#34495e;
	font-size:16px;
	}
	.btn{
	background-color:transparent;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><span class="fa fa-pencil-square" style="color:#990000"></span><b> EDIT BRANCH DETAILS</b></h3>
				</div>
				<div class="panel-body">
					<table id="user" class="table table-bordered table-striped" style="clear: both">
						<tbody>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">BRANCH ID</th>
								<td width="65%"><input type="text" name="branchId" id="branchId" class="form-control" value="<?php echo $row['branchId']; ?>" readonly></td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">BRANCH NAME</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="branchName" class="form-control" value="<?php echo $row['branchName']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">BRANCH AREA</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="branchArea" class="form-control" value="<?php echo $row['branchArea']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">ADDRESS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="addr" class="form-control" autocomplete="off" value="<?php echo $row['addr'];?>">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">CITY</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="city" class="form-control" value="<?php echo $row['city']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">STATE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="state" class="form-control" value="<?php echo $row['state']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">PINCODE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="pincode" class="form-control" value="<?php echo $row['pincode']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">OFFICE CONTACT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="officeContact" class="form-control" value="<?php echo $row['officeContact']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> BRANCH MANAGER</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="branchManager" class="form-control" value="<?php echo $row['branchManager']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th  class="text-success" width="35%" style="padding-top:17px"> PHONE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> EMAIL</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="email" class="form-control" value="<?php echo $row['email']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> GST</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="gst" class="form-control" value="<?php echo $row['gst']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> LATITUDE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="latitude" class="form-control" value="<?php echo $row['latitude']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> LONGITUDE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="longitude" class="form-control" value="<?php echo $row['longitude']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> URL</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="url" class="form-control" value="<?php echo $row['url']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> STATUS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="Status" class="form-control" value="<?php echo $row['Status']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> PRICE ID</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="priceId" class="form-control" value="<?php echo $row['priceId']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> GRADE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="grade" class="form-control" value="<?php echo $row['grade']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> OPEN DATE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="openDate" class="form-control" value="<?php echo $row['openDate']; ?>"autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> CLOSED DATE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="closeDate" class="form-control" value="<?php echo $row['closeDate']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> RENEWAL DATE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="renewal_date" class="form-control" value="<?php echo $row['renewal_date']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> RENEWAL STATUS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="renewal_status" class="form-control" value="<?php echo $row['renewal_status']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px"> WEIGHING SCALE ACCESS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="ws_access" class="form-control" value="<?php echo $row['ws_access']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">RATING LINK</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="ratinglink" class="form-control" value="<?php echo $row['ratinglink']; ?>" autocomplete="off">
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateBranchData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<script>	
		let branchId = document.getElementById('branchId').value;
		function updateBranchData(button){
			let colValue = button.parentNode.previousElementSibling.value,
			colName = button.parentNode.previousElementSibling.name;
			$.ajax({
				url:"editAjax.php",
				type:"POST",
				data:{editBranch:'editBranch',branchId:branchId,colName:colName,colValue:colValue},
				success: function(e){
					if(e == '1'){
						alert('Successfully Updated');
					}
					else{
						alert('Oops!!! Something went wrong');
					}
				}
			});
		}
	</script>
<?php include("footer.php"); ?>