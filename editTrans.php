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
	$id = $_GET['id'];
	$row = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM trans WHERE id='$id'"));		
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
	.fa-disabled{
		color: #990000;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><span class="fa_Icon fa fa-pencil-square"></span><b> EDIT BILL DETAILS</b></h3>
				</div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<input type="hidden" id="transId" value="<?php echo $row['id']; ?>">
					<table id="user" class="table table-bordered" style="clear: both">
						<tbody>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">CUSTOMER ID</th>
								<td width="65%">
									<input type="text" name="customerId" class="form-control" value="<?php echo $row['customerId']; ?>" readonly>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">BILL ID</th>
								<td width="65%">
									<input type="text" name="billId" class="form-control" value="<?php echo $row['billId']; ?>" readonly>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">NAME</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">PHONE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" autocomplete="off" readonly>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)" disabled><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">GROSS AMOUNT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="grossA" class="form-control" value="<?php echo $row['grossA']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)" disabled><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">NET AMOUNT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="netA" class="form-control" value="<?php echo $row['netA']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">RELEASE AMOUNT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="releases" class="form-control" value="<?php echo $row['releases']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">AMOUNT PAID</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="amountPaid" class="form-control" value="<?php echo $row['amountPaid']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)" disabled><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">MARGIN</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="margin" class="form-control" value="<?php echo $row['margin']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">COMM %</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="comm" class="form-control" value="<?php echo $row['comm']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">PAYMENT TYPE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="paymentType" class="form-control" value="<?php echo $row['paymentType']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">CASH</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="cashA" class="form-control" value="<?php echo $row['cashA']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">IMPS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="impsA" class="form-control" value="<?php echo $row['impsA']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">STATUS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="status" class="form-control" value="<?php echo $row['status']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updateTransData(this)"><i class="fa fa-paint-brush"></i></button>
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
		let transId = document.getElementById('transId').value;
		function updateTransData(button){
			if(transId !== ''){
				let colValue = button.parentNode.previousElementSibling.value,
				colName = button.parentNode.previousElementSibling.name;
				$.ajax({
					url:"editAjax.php",
					type:"POST",
					data:{editTrans:'editTrans',transId:transId,colName:colName,colValue:colValue},
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
		}
	</script>
<?php include("footer.php");?>