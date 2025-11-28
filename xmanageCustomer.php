<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='Zonal'){
	    require("header.php");
        include("menuZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
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
	font-size: 18px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	font-size: 10px;
	}
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
	}
	.btn-primary{
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
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
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
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}
	.fa_Icon{
	color: #ffa500;
	}
	.trans_Icon{
	color: #990000;
	}
	.text-success{
	font-weight:600;
	color: #123C69;
	}
	.dataTables_wrapper .row{
	margin-right: 0px;
	margin-left: 0px;
	}
	.preload{ 
	width:100px;
	height: 100px;
	position: fixed;
	top: 30%;
	left: 25%;
	}
	.ajaxload img{ 
	width:100px;
	height: 100px;
	position: fixed;
	top: 40%;
	left: 50%;
	}
	.bg-success{
	color:#006400;
	font-weight:bold;
	}
	.font-weight-bold{
	font-weight:bold;
	}
	.text-unblocked{
	color:#006400;
	}
</style>

<div class="modal fade" id="fraudInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header">
				<p class="text-success"><b>ADD FRAUD DETAILS</b></p>
			</div>
			<div class="modal-body">
				<div class="form-group row" style="padding:0px 20px 0px 20px;">
					<div class="col-sm-6">
						<label class="text-success">CUSTOMER NAME</label>
						<input type="text" name="customer_name" placeholder="Name" required id="customer_name" class="form-control" autocomplete="off">						
					</div>
					<div class="col-sm-6">
						<label class="text-success">CONTACT NUMBER</label>
						<input type="text" name="customer_no" style="padding:0px 5px" id="customer_no" pattern="[0-9]{10}" required placeholder="Contact Number" maxlength="10" required class="form-control" autocomplete="off">
					</div>
					<label class="col-sm-12"></label>
					<div class="col-sm-6">
						<label class="text-success">TYPE</label>
						<select name="id_type" class="form-control" id="id_type">
							<option selected="true" disabled="disabled">PROOF TYPE</option>
							<option value="Aadhar Card">Aadhar Card</option>
							<option value="Pan Card">Pan Card</option>
							<option value="Driving Licence">Driving Licence</option>
							<option value="Voter ID">Voter ID</option>
						</select>
					</div>
					<div class="col-sm-6">
						<label class="text-success">ID NUMBER</label>
						<input type="text" name="id_number" id="id_number" style="padding:0px 5px" placeholder="ID Number" class="form-control" autocomplete="off">
					</div>
					<div class="col-sm-3"><br>
						<button class="btn btn-success btn-block" name="submit_fraud" id="submit_fraud" type="button" onclick="addFraudCustomer();">
							<span style="color:#ffcf40" class="fa fa-save"></span> Submit
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

<div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="box-shadow: rgb(0 0 0 / 30%) 0px 19px 38px, rgb(0 0 0 / 22%) 0px 15px 12px;">
			<div class="modal-header">
				<div class="col-lg-12">
					<div class="col-lg-10">
						<p class="text-success" id="exampleModalLabel1"><b>TRANSACTION DETAILS OF <span id="custId"></span></b></p>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<table id="transactionDatatable" class="table table-striped table-bordered">
					<thead class="theadRow">
						<tr>
							<th>#</th>
							<th>Branch</th>
							<th>Customer</th>
							<th>Type</th>
							<th>Metal</th>
							<th>GrossW</th>
							<th>Amount</th>
							<th>Bill_Date_Time</th>
						</tr>
					</thead>
					<tbody id="transactionBillResult"></tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> <i class="fa fa-times" aria-hidden="true"></i> Close</button>
			</div>
		</div>
	</div> 
</div>	

<div class="modal fade" id="customer_info" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="box-shadow: rgb(0 0 0 / 30%) 0px 19px 38px, rgb(0 0 0 / 22%) 0px 15px 12px;">
			<div class="modal-header">
				<div class="col-lg-12">
					<div class="col-lg-10">
						<p class="text-success" id="exampleModalLabel1"><b>DETAILS OF <span id="cust_Id"></span></b></p>
					</div>	
					<div class="col-lg-2">
						<div class="close" data-dismiss="modal"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div id="customerInfo"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> <i class="fa fa-times" aria-hidden="true"></i> Close</button>
			</div>
		</div>
	</div> 
</div>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-7">
						<h3><i class="trans_Icon fa fa-edit"></i> Manage Customers</h3>
					</div>
					<div class="col-sm-2">
						<!--<a href="xrecentlyblockedList.php" class="btn btn-primary">
							<i class="fa_Icon fa fa-ban"></i> RECENTLY BLOCKED
						</a>-->
					</div>
					<div class="col-sm-2">
						<button data-toggle='modal' data-target='#fraudInfoModal' class='btn btn-primary btn-block'>
							<i class='fa_Icon fa fa-user-secret' aria-hidden='true'></i> ADD FRAUD INFO
						</button>
					</div>
					<div class="col-sm-1">
						<button onclick="window.location.reload();" class="btn btn-primary btn-block">
							<i class="fa_Icon fa fa-spinner"></i> RELOAD
						</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<h3 id="recently_blocked" class='text-center'><i class='trans_Icon fa fa-ban'></i> RECENTLY BLOCKED CUSTOMERS </h3>
					<div class="table-responsive">
						<table id="customer-datatable" class="table table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Customer Name</th>
									<th>Mobile</th>
									<th>Branch</th>
									<th>Branch Manager</th>
									<th style='text-align:center'>Check Bill</th>
									<th style='text-align:center'>Customer Details</th>
									<th style='text-align:center'>Action</th>
								</tr>
							</thead>
							<tbody id="customer-datatable-result">
								<?php
									$i = 1;
									$query = mysqli_query($con,"SELECT Id,customer,contact,status,branch,remark,block_counter 
									FROM everycustomer 
									WHERE date='$date' and status='Blocked'");
									
									while($row = mysqli_fetch_assoc($query)){
										
										$customerQuery = mysqli_query($con,"SELECT idProof,idFile,idNumber,addProof,addFile,addNumber FROM customer where mobile=".$row['contact']);
										$cRow = mysqli_fetch_assoc($customerQuery);
										
										$empQuery = mysqli_query($con,"SELECT branchName,branch,employeeId,name,contact FROM users u join branch b on b.branchId=u.branch join employee e on e.empId=u.employeeId where u.branch='".$row['branch']."'");
										$eRow = mysqli_fetch_assoc($empQuery);
										
										$billQuery = mysqli_fetch_assoc(mysqli_query($con,"SELECT count(*) AS billCount FROM trans where phone=".$row['contact']." and status='Approved'"));
										
										if($billQuery['billCount']>0){
											$view_bill="<button class='btn btn-success btn-sm' onClick='customerBillCheck(\"".$row['contact']."\")'><i style='color:#ffa500' class='fa fa-eye'></i> VIEW </button>";
											}else{
											$view_bill="<button class='btn btn-warning btn-sm font-weight-bold' disabled> NO BILLS </button>";
										}
										
										// Filter the Numbers from String
										if($row['remark']==""){
											$view_bill_altNo="<button class='btn btn-dark btn-sm' disabled> NO BILLS </button>";
											}else{
											$arr1=explode('(', $row['remark']);
											if(empty($arr1[1])){
												$view_bill_altNo="";
												}else{
												$altNo = explode(')', ($arr1[1]))[0];
												if(strlen($altNo)>=10){
													$customerQuery2 = mysqli_query($con,"SELECT mobile FROM customer where idNumber='".$altNo."' OR addNumber='".$altNo."' OR mobile='".$altNo."'");
													$cRow2 = mysqli_fetch_assoc($customerQuery2);
													$cRow2Count = mysqli_num_rows($customerQuery2);
													if($cRow2Count>0){
														if(isset($cRow2['mobile'])){
															$altNo2 = $cRow2['mobile'];
															$view_bill_altNo="<button class='btn btn-success btn-sm' onClick='customerBillCheck(\"".$altNo2."\")'><i style='color:#ffa500' class='fa fa-eye'></i> VIEW </button>";
															}else{
															$view_bill_altNo="";
														}
														}else{
														$customerQuery3 = mysqli_query($con,"SELECT mobile FROM customer where mobile='".$altNo."'");
														$cRow3 = mysqli_fetch_assoc($customerQuery3);
														if(isset($cRow3['mobile'])){
															$altNo2 = $cRow3['mobile'];
															$view_bill_altNo="<button class='btn btn-success btn-sm' onClick='customerBillCheck(\"".$altNo2."\")'><i style='color:#ffa500' class='fa fa-eye'></i> VIEW </button>";
															}else{
															$view_bill_altNo="";
														}
													}
													}else{
													$view_bill_altNo="<button class='btn btn-success btn-sm' onClick='customerBillCheck(\"".$altNo."\")'><i style='color:#ffa500' class='fa fa-eye'></i> VIEW </button>";
												}
											}
										}
										
										if(isset($cRow['idFile'])){
											if(file_exists("CustomerDocuments/".$cRow['idFile']) && $cRow['idFile']!=""){
												$id_file="<a target='_blank' href='CustomerDocuments/".$cRow['idFile']."'><i class='pe-7s-paperclip fa-2x'></i></a>";
												}else{
												$id_file=" <a target='_blank' href='attachments/".$cRow['idFile']."'><i class='pe-7s-paperclip fa-2x'></i></a>";
											}
											
											}else{
											$id_file="<i class='pe-7s-close-circle fa-2x'></i>";
										}
										if(isset($cRow['addFile'])){
											if(file_exists("CustomerDocuments/".$cRow['addFile']) && $cRow['addFile']!=""){
												$add_file="<a target='_blank' href='CustomerDocuments/".$cRow['addFile']."'><i class='pe-7s-paperclip fa-2x'></i></a>";
												}else{
												$add_file=" <a target='_blank' href='attachments/".$cRow['addFile']."'><i class='pe-7s-paperclip fa-2x'></i></a>";
											}
											
											}else{
											$add_file="<i class='pe-7s-close-circle fa-2x'></i>";
										}
										
										$idProof = (isset($cRow['idProof'])) ? $cRow['idProof'] : "";
										$idNumber = (isset($cRow['idNumber'])) ? $cRow['idNumber'] : "Not Available";
										$addProof = (isset($cRow['addProof'])) ? $cRow['addProof'] : "";
										$addNumber = (isset($cRow['addNumber'])) ? $cRow['addNumber'] : "Not Available";
										
										echo "<tr id='row_$i' class='bg-danger'>";
										echo "<td>".$i."</td>";
										echo "<td class='text-uppercase'>".$row['customer']."<p>(<span class='text-danger'> <i class='fa fa-ban' aria-hidden='true'></i> BLOCKED </span>)</p></td>";
										echo "<td>".$row['contact']."</td>";
										echo "<td>".$eRow['branchName']."</td>";
										echo "<td>".$eRow['name']."<p>".$eRow['contact']."</p></td>";
										echo "<td style='text-align:center'>".$view_bill."</td>";
										echo "<td><span class='text-info'>Id Proof :</span> ".$idProof."<span> (".$idNumber.")</span> ".$id_file."<p><span class='text-info'>Address Proof : </span><span>".$addProof." (".$addNumber.")</span> ".$add_file."</p><p><span class='text-info'>Remark : </span><span>".$row['remark']." ".$view_bill_altNo."</span></p></td>";
										echo "<td style='text-align:center'><button class='btn btn-success btn-sm' id='statusUpdate_".$i."' onClick='customer_updateStatus(\"".$i."\",\"".$row['Id']."\",\"".$row['contact']."\",\"".$row['status']."\",".$row['block_counter'].")' style='background:#008000;border:1px solid #008000;'> <i style='color:#ffa500' class='fa fa-unlock' aria-hidden='true'></i> UNBLOCK </button></td>";
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
	</div>
	
	<?php include("footer.php"); ?>
	<script>
		$('#recently_blocked').css("display","none");
		$('#customer-datatable').DataTable({
			responsive: true
		});
		$('#transactionDatatable').DataTable({
			responsive: true
		}); 
		
		function customer_updateStatus(arg1,arg2,arg3,arg4,arg5){
			$.ajax({
				url: "xTransactionAjax.php",
				type: "post",
				data: { action:"customer_status",cust_arg2: arg2,cust_arg3: arg3,cust_arg4: arg4,cust_arg5: arg5},
				success: function(response){
					$('#statusUpdate_'+arg1).html(response);
					if(response=="Blocked"){
						$('#row_'+arg1).html("<td colspan='8' class='text-danger text-center bg-danger'>The customer is blocked</td>");
					}
					if(response=="Unblocked"){
						$('#row_'+arg1).html("<td colspan='8' class='text-success text-center bg-success'>The customer is unblocked</td>");
					}
					setTimeout(function(){
						document.getElementById('row_'+arg1).remove();
					}, 2000);
				}
			});
		}
		
		function customerBillCheck(id){
			$('#viewDetails').modal();
			$('#custId').html(id);
			$('#transactionBillResult').html('<tr><td colspan="9"><div class="preload"><img src="images/data-loading.gif"></div></td></tr>');
			$.ajax({
				url: "xTransactionAjax.php",
				type: "post",
				data: {custId: id},
				success: function(response){
					$(".preload").fadeOut(2000, function() {
						$('#transactionDatatable').DataTable().clear().destroy();
						$('#transactionBillResult').empty().append(response);
						$('#transactionDatatable').DataTable({
							responsive: true,
							dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
							"lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
							buttons: [
							{extend: 'csv',title: 'Transaction-Bill-Report', className: 'btn-sm btn-success',text: 'EXPORT TO EXCEL'}
							]
						});	
						
					});
				}
			});
		}
		
		function getCustomerInfo(cid){
			$('#customer_info').modal();
			$('#customerInfo').html('<tr><td colspan="9"><div class="preload"><img src="images/data-loading.gif"></div></td></tr>');
			$.ajax({
				url: "xTransactionAjax.php",
				type: "post",
				data: {customerId: cid},
				dataType: 'json',
				success: function(response){
					
					$(".preload").fadeOut(2000, function() {
						$('#customerInfo').html(response.branch_name);
					});
				}
			}); 
		}	
		
		function addFraudCustomer(){
			var customer_name = $('#customer_name').val();
			var customer_no = $('#customer_no').val();
			var id_type = $('#id_type').val();
			var id_number = $('#id_number').val();
			$.ajax({
				url: "xTransactionAjax.php",
				type: "post",
				data: {action: 'fraud',cusname:customer_name,cusmob:customer_no,type:id_type,idnumber:id_number},
				success: function(response){
					if(response=="success"){
						alert("The data has been added succcessfully");
						$('#fraudInfoModal').modal('hide');
						}else{
						alert("Oops!! Error while adding the data.Please try again!!");
					}
				}
			});
		}
	</script>
