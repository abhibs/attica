<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}else if($type == 'ApprovalTeam' && $_SESSION["employeeId"]=='1000545'){
		include("header.php");
		include("menuapproval.php");
	}else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
?>
<style>
	.tab{
		font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
		padding: 0;
		margin: 0;
		border: none;
	}   
	.tab .nav-tabs li a{
		color: #123C69;
		background: #f8f8ff;
		font-size: 12px;
		font-weight: 600;
		text-align: center;
		letter-spacing: 1px;
		text-transform: uppercase;
		padding: 7px 10px 6px;
		margin: 5px 5px 0px 0px;
		border: none;
		border-bottom: 3px solid #123C69;
		border-radius: 0;
		position: relative;
		z-index: 1;
		transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover{
		color: #f2f2f2;
		background: #123C69;
		border: none;
		border-bottom: 3px solid #ffa500;
		font-weight: 600;
		border-radius:3px;
	}
	.tab .nav-tabs li a:before{
		content: "";
		background: #f8f8ff;
		height: 100%;
		width: 100%;
		position: absolute;
		bottom: 0;
		left: 0;
		z-index: -1;
		transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
		clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
		height: 0;
		clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
		color: #0C1115;
		background: #f8f8ff;
		font-size: 12px;
		position: relative;
		border: 3px solid #fff;
		border-radius: 10px;
		padding: 2px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
		color: #123C69;
		font-weight:500;
	}	
	.tab-pane{
		background: #f8f8ff;
		padding: 10px 5px 50px 5px;
		min-height:350px;
	}	
	@media only screen and (max-width: 479px){
		.tab .nav-tabs{
			padding: 0;
			margin: 0 0 15px;
		}
		.tab .nav-tabs li{
			width: 100%;
			text-align: center;
		}
		.tab .nav-tabs li a{ margin: 0 0 5px; }
	}	
	#wrapper{
		background-color: #f8f8ff;
	}
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
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
		font-size: 12px;
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
		color: #b8860b;
	}
	.text-success{
		font-weight:600;
		color: #123C69;
	}
	.panel-heading h3{
		text-transform:uppercase;
	}
	.dataTables_wrapper .row{
		margin-right: 0px;
		margin-left: 0px;
	}
	.row{
		margin-left:0px;
		margin-right:0px;
	}
	/* .fa-stack[data-count]:after{
		position:absolute;
		right:0%;
		top:1%;
		content: attr(data-count);
		font-size:30%;
		padding:.6em;
		border-radius:999px;
		line-height:.75em;
		color: white;
		background:green;
		text-align:center;
		min-width:2em;
		font-weight:bold;
	}
	.Blink { animation: blinker 0.5s cubic-bezier(.5, 0, 1, 1) infinite alternate; }
	@keyframes blinker { from { opacity: 1; } to { opacity: 0; } } */
	
	.preload{ 
		width:100px;
		height: 100px;
		position: fixed;
		top: 30%;
		left: 25%;
	}	
	.ajaxload img{ 
		width:200px;
		height: 200px;
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
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="row">
						<div class="col-lg-9">
							<h3><i class="trans_Icon fa fa-edit"></i> Recently Blocked Customer List </h3>
						</div>

						<div class="col-lg-2">
							<button data-toggle='modal' data-target='#fraudInfoModal' class='btn btn-primary'>
								<i class='fa_Icon fa fa-user-secret' aria-hidden='true'></i> ADD FRAUD INFO
							</button>
						</div>
						<div class="col-lg-1">
							<button style="float:right;padding-right:10px" onclick="window.location.reload();" class="btn btn-primary">
								<i class="fa_Icon fa fa-spinner"></i> RELOAD
							</button>	
						</div>
					</div>
				</div>
				 <div class="tab" role="tabpanel">	
					<div class="tab-content tabs">
						<div role="tabpanel" class="tab-pane fade in active">

							<div class="table-responsive">
							<table id="customer-datatable" class="table table-bordered">
								<thead>
									<tr>							
										<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
										<th>Customer Name</th>
										<th>Mobile</th>
										<th>Branch</th>
										<th>Branch Manager</th>
										<th style='text-align:center'>Check Bill</th>
										<th style='text-align:center'>Remarks</th>
										<th style='text-align:center'>Action</th>							
									</tr>
								</thead>
								<tbody id="customer-datatable-result">
								
								<?php	
                        		$resultArray = "";
                                $i = 1;
                        		$query = mysqli_query($con,"SELECT Id,customer,contact,status,branch,remark,block_counter,image FROM everycustomer WHERE date='$date' and status_remark='Recently Blocked' ORDER BY Id DESC");
                        		while($row = mysqli_fetch_array($query)){                        			
                        			
                        			$empQuery = mysqli_query($con,"SELECT branchName,branch,employeeId,name,contact FROM `users` u join branch b on b.branchId=u.branch join employee e on e.empId=u.employeeId where u.branch='".$row['branch']."'");
                        			$eRow = mysqli_fetch_assoc($empQuery);	
                        			
                        			$billQuery = mysqli_query($con,"SELECT * FROM trans where phone=".$row['contact']." and status='Approved'");
                        			$billCount = mysqli_num_rows($billQuery);
                        			
                        			if($billCount>0){
                        				$view_bill="<button class='btn btn-success btn-sm' onClick='customerBillCheck(\"".$row['contact']."\")'><i style='color:#ffa500' class='fa fa-eye'></i> VIEW </button>";
                        			}else{
                        				$view_bill="<button class='btn btn-dark btn-sm font-weight-bold' disabled> NO BILLS </button>";
                        			}

                        			
                        			if($row['status']=="Blocked"){
                        				$status ="BLOCKED";
                        				$fa_icon ="fa-ban";
                        				$text ="text-danger";
                        				$statusClass ="bg-danger";
                        			}else{
                        				$status ="UNBLOCKED";
                        				$fa_icon ="fa-check";
                        				$text ="text-unblocked";
                        				$statusClass ="bg-success";
                        			}
                        			
                        			$resultArray .= "<tr id='row_$i' class='$statusClass'>";
                        			$resultArray .= "<td>".$i."</td>";
                        			$resultArray .= "<td class='text-uppercase'>".$row['customer']."<p>(<span class='$text'> <i class='fa $fa_icon' aria-hidden='true'></i> ".$status." </span>)</p></td>";
                        			$resultArray .= "<td>".$row['contact']."</td>";								
                        			$resultArray .= "<td>".$eRow['branchName']."</td>";								
                        			$resultArray .= "<td>".$eRow['name']."<p class='font-weight-bold'>".$eRow['contact']."</p></td>";								
                        			$resultArray .= "<td style='text-align:center'>".$view_bill."</td>";
                        			$resultArray .= "<td class='font-weight-bold'>REMARK : </span><span>".$row['remark']."</span></p></td>";			
                        			if($row['status']=="Blocked"){
                        				$resultArray .= "<td style='text-align:center'><button class='btn btn-success btn-sm' id='statusUpdate_".$i."' onClick='customer_updateStatus(\"".$i."\",\"".$row['Id']."\",\"".$row['contact']."\",\"".$row['status']."\")' style='background:#008000;border:1px solid #008000;'> <i style='color:#ffa500' class='fa fa-unlock' aria-hidden='true'></i> UNBLOCK </button></td>";	
                        			}elseif($row['status']==0 && $row['block_counter']<2){
                        				$resultArray .= "<td style='text-align:center'><button class='btn btn-danger btn-sm' id='statusUpdate_".$i."' onClick='customer_updateStatus(\"".$i."\",\"".$row['Id']."\",\"".$row['contact']."\",\"".$row['status']."\")'> <i style='color:#ffa500' class='fa fa-lock' aria-hidden='true'></i> BLOCK </button></td>";
                        			}elseif($row['status']==0 && $row['block_counter']==2){
                        				$resultArray .= "<td class='font-weight-bold text-center'><button class='btn btn-warning btn-sm text-uppercase' disabled><i class='fa fa-check text-unblocked' aria-hidden='true'></i> APPROVED </button></td>";
                        			}else{
                        				$resultArray .= "<td class='font-weight-bold text-center'><button class='btn btn-warning btn-sm text-uppercase' disabled><i class='fa fa-check text-unblocked' aria-hidden='true'></i> ".$row['status']."</button></td>";
                        			}										
                        			$resultArray .= "</tr>";	
                        			$i++;	
                        		}			
                        		echo $resultArray;										
								?>							
								</tbody>
								<div style="clear:both"></div>
							</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="fraudInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="color-line"></div>
				<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
				<div class="modal-header">
					<h3 class="text-success"><b>ADD FRAUD DETAILS</b></h3>
				</div>
				<div class="modal-body">
						<div class="form-group row" style="padding-left:50px;">
							<div class="col-sm-5">
								<label class="text-success">CUSTOMER NAME</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="customer_name" placeholder="Name" required id="customer_name" class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-5">
								<label class="text-success">CONTACT NUMBER</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
									<input type="text" name="customer_no" style="padding:0px 5px" id="customer_no" pattern="[0-9]{10}" required placeholder="Contact Number" maxlength="10" required class="form-control" autocomplete="off">
								</div>
							</div>

							<div class="col-sm-5">
								<label class="text-success">TYPE</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<select name="id_type" class="form-control" id="id_type">
										<option selected="true" disabled="disabled">PROOF TYPE</option>
										<option value="Aadhar Card">Aadhar Card</option>
										<option value="Pan Card">Pan Card</option>
										<option value="Driving Licence">Driving Licence</option>
										<option value="Voter ID">Voter ID</option>
									</select>
								</div>
							</div>
							<div class="col-sm-5">
								<label class="text-success">ID NUMBER</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="id_number" id="id_number" style="padding:0px 5px" placeholder="ID Number" class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-8"><br>
								<button class="btn btn-success" name="submit_fraud" id="submit_fraud" type="button" onclick="addFraudCustomer();"><span style="color:#ffcf40" class="fa fa-save"></span> Submit </button>
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
				<div class="modal-header" style="background-color:#ffd700;color:#4682b4;">
					<div class="col-lg-12">
						<div class="col-lg-10">
							<h4 class="modal-title" id="exampleModalLabel1">TRANSACTION DETAILS OF <span id="custId"></span></h4>
						</div>	
						<div class="col-lg-2">				
							<div class="close" data-dismiss="modal"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>
						</div>
					</div>
				</div>
				<div class="modal-body">
					<table id="transactionDatatable" class="table table-striped table-bordered">
						<thead class="theadRow">
							<tr>
								<th><i class="fa fa-sort-numeric-asc"></i> SL. No. </th>
								<th>Customer Name</th>
								<th>Branch Name</th>
								<th>Bill Type</th>
								<th>Payment Type</th>
								<th>Metal</th>
								<th>GrossW</th>
								<th>Amount Paid</th>
								<th>Date</th>
								<th>Time</th>
								<!--<th>View</th>-->
							</tr>
						</thead>
						<tbody id="transactionBillResult">

						</tbody>
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
				<div class="modal-header" style="background-color:#ffd700;color:#4682b4;">
					<div class="col-lg-12">
						<div class="col-lg-10">
							<h4 class="modal-title" id="exampleModalLabel1">DETAILS OF <span id="cust_Id"></span></h4>
						</div>	
						<div class="col-lg-2">				
							<div class="close" data-dismiss="modal"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>
						</div>
					</div>
				</div>
				<div class="modal-body">

				<div id="customerInfo">
				

				</div>
					
				</div>
				<div class="modal-footer">                                       
					<button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> <i class="fa fa-times" aria-hidden="true"></i> Close</button>
				</div>									
			</div>
		</div> 
	</div>

	
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
	<script>
		$('#recently_blocked').css("display","block");

		$('#customer-datatable').DataTable({
			responsive: true			
		});			
		$('#transactionDatatable').DataTable({
			responsive: true
		}); 
		
		function customer_updateStatus(arg1,arg2,arg3,arg4,arg5){
			//$('#row_'+arg1).html("<td colspan='5' class='text-center bg-success'>The customer is unblocked</td>");
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
		//alert(id);
		
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