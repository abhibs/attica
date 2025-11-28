<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software')
	{
	    include("header.php");
		include("menuSoftware.php");
		$current_date=date('Y-m-d');
	}else
    {
        include("logout.php");
    }
	include("dbConnection.php");
	
	$contactNumber=$_GET['search'];
	if($contactNumber==""){
		//$contactNumber= "Bengaluru";
	}
?>
<style> 
	input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		background-image: url('images/searchicon.png');
		background-position: 220px 12px; 
		background-repeat: no-repeat;
		padding: 12px 50px 12px 15px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	#state_performance,#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	#wrapper{
		background: #f5f5f5;
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
		color: #ffcf40;
	}
	tbody{
	    font-weight: 600;
	}
	.modal-title {
		font-size: 20px;
		font-weight: 600;
		color:#708090;
		text-transform:uppercase;
	}	
	.modal-header{
		background: #123C69;
	}	
	#wrapper .panel-body{
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
		background-color: #f5f5f5;
		min-height: 300px;
	}	
	.preload{ 
		width:100px;
		height: 100px;
		position: fixed;
		top: 40%;
		left: 70%;
	}	
	.ajaxload{ 
		width:100px;
		height: 300px;
		position: fixed;
		top: 20%;
		left: 20%;
	}
	.buttons-csv,.btn-info{
	    font-size: 10px;
	}
	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
		border-radius:5px;
	}
	legend{
		margin-left:8px;
		width:400px;
		background-color: #123C69;
		padding: 5px 15px;
		line-height:30px;
		font-size: 14px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
		margin-bottom:0px;
		letter-spacing: 2px;
	}
	.card {
		position: relative;
		display: flex;
		flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border: 0 solid rgba(0,0,0,.125);
		border-radius: .25rem;
		box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
	}
	.card-body {
		flex: 1 1 auto;
		min-height: 1px;
		padding: 1rem;
	}
	h4, h6{
		font-weight: bold;
	}
</style>
<div id="wrapper">
	<div class="content animate-panel">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">	
						<div class="row">
							<div class="col-lg-12">
								<div class="col-lg-2">
										<input type="date" name="search_date" id="search_date" placeholder="Select the date">
								</div>
								
								<div class="col-lg-3">
									<select id="state_performance" class="form-control" aria-label=".form-select-lg example">
									    <option selected="true" disabled="disabled">Select A State</option>  
										<option value="Karnataka">Karnataka</option>
										<option value="Andhra Pradesh & Telangana">Andhra Pradesh & Telangana</option>
										<option value="Tamilnadu & Pondicherry">Tamilnadu & Pondicherry</option>
									</select>
								</div>

								<div class="col-lg-3 pull-right">
									<form action="<?php echo $_SERVER['REQUEST_URI'];?>">
										<input type="text" name="search" id="searchTransaction" placeholder="SEARCH BY CONTACT NUMBER">							
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="container1" style="display:none;">
							<?php
							if(isset($_GET['search'])){
							?>
							<div class="col-lg-5">
								<fieldset class="ornament-detail-box">
									<legend><i style="padding-top:15px" class="fa_Icon fa fa-eye"></i> VIEW TRANSACTIONS OF <span class="branch_name"><?php echo $contactNumber; ?></span></legend>
									<div class="panel-body">
									
										<?php
										$contactNumber=$_GET['search'];					
										$searchQuery=mysqli_query($con,"SELECT count(*) as bill_count, SUM(grossW) as gross_weight FROM trans where phone = '$contactNumber' and status='Approved'");
										$customerQuery=mysqli_query($con,"SELECT name FROM customer where mobile = '$contactNumber'");
										$cRow = mysqli_fetch_assoc($customerQuery);
										$count=mysqli_num_rows($searchQuery);
										if($count>0){											
										$row = mysqli_fetch_assoc($searchQuery);
										if($row['bill_count']>0){?>
											<ul class="list-group list-group-flush">

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
											<div class="row">
											<div class="col-sm-4">
											<h6 class="mb-0">CUSTOMER</h6>
											</div>				  
											<div class="col-sm-1"> : </div>
											<div class="col-sm-7">
											<h6 class="mb-0"><?php echo $cRow['name']; ?></h6>
											</div>
											</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
											<div class="row">
											<div class="col-sm-4">
											<h6 class="mb-0">CONTACT</h6>
											</div>				  
											<div class="col-sm-1"> : </div>
											<div class="col-sm-7">
											<h6 class="mb-0"><?php echo $contactNumber; ?></h6>
											</div>
											</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">NO. OF BILLS</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><?php echo $row['bill_count']; ?></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">TOTAL GROSS WEIGHT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><?php echo round($row['gross_weight'],2); ?></h6>
												</div>
												</div>
											</li>											
											</ul>
										<?php
										}
										}
										?>			


									</div>
								</fieldset>
							</div>
							
							<div class="col-lg-7">
							<fieldset class="ornament-detail-box">
							<legend><i style="padding-top:15px" class="fa_Icon fa fa-list"></i> BILL DETAILS LIST</legend>
								<div class="panel-body">
									<table id="transactionBill" class="table table-bordered">
										<thead class="theadRow">
											<tr>
												<th class='text-center'><i class="fa fa-sort-numeric-asc"></i> SL. No.</th>
												<th class='text-center'>Bill Date</th>
												<th class='text-center'>Gross Weight</th>
												<th class='text-center'>Bill</th>
												<th class='text-center'>Details</th>
												
											</tr>
										</thead>
										<tbody>
											<?php
												$searchQuery=mysqli_query($con,"SELECT id as invoice_id, date as invoice_date, name as customer_name, grossW, time FROM `trans` where phone=$contactNumber and status='Approved' order by invoice_date desc");
												$searchCount=mysqli_num_rows($searchQuery);
												if($searchCount>0){
													$i = 1;
													while($sRow = mysqli_fetch_assoc($searchQuery)){
														echo "<tr class='text-center'>";
														echo "<td class='text-center'>" . $i . "</td>";
														echo "<td class='text-center'>" .date('d-m-Y', strtotime($sRow['invoice_date'])). "<br>".$sRow['time']."</td>";
														echo "<td class='text-center'>" .round($sRow['grossW'],2). "</td>";
														echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='Invoice.php?id=".base64_encode($sRow['invoice_id'])."'><i class='fa_Icon fa fa-eye'></i> View Bill</a></b></td>";
														echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='xviewCustomerDetails.php?id=$contactNumber&ids=".$sRow['invoice_id']."'><i class='fa_Icon fa fa-eye'></i> View Details</a></b></td>";
														echo "</tr>";
														$i++;
													}
												}
											?>
										</tbody>
									</table>						
								</div>
							</fieldset>
							</div>
							<?php				
							 } 
							?>
						</div>

						<div class="container2" style="display:block">
							<div class="col-lg-6">
								<h3 class="text-success"><i class="fa_Icon fa fa-eye"></i> View Transactions of <span class="branch_name"><?php echo $contactNumber; ?></span></h3>
								<table id="currentTransactionResult" class="table table-striped table-bordered">
									<thead class="theadRow">
										<tr>
											<th>Sl. No.</th>
											<th>Branch Name</th>
											<th>Branch ID</th>
											<th>No. Of Bills</th>
											<th class="text-center">Action</th>									
										</tr>
									</thead>
									<tbody id="currentTransactionResponse">								
									<?php									
										/*$current_date = date('Y-m-d');
										$invQuery=mysqli_query($con,"SELECT count(*) as bill_count, b.branchName, t.branchId FROM `trans` t inner join branch b on b.branchId=t.branchId where t.date='$current_date' and t.status='Approved' and b.city='Bengaluru' group by t.branchId order by bill_count desc");
										$invCount=mysqli_num_rows($invQuery);
										if($invCount>0){					
											$i = 1;
											while($invRow = mysqli_fetch_assoc($invQuery)){
												echo "<tr>";
												echo "<td class='text-center'>".$i."</td>";
												echo "<td class='text-center'>".$invRow['branchName']."</td>";
												echo "<td class='text-center'>".$invRow['branchId']."</td>";
												echo "<td class='text-center'>".$invRow['bill_count']."</td>";
												echo "<td class='text-center'><a class='btn btn-info' style='margin-right:10px;' onClick='transactionBillResult(\"".$invRow['branchId']."\",\"".$current_date."\")'><i style='color:#ffa500' class='fa fa-eye'></i> View </a></td>";
												echo "</tr>";										
												$i++;
											}
											
										}	*/								
									?>
									</tbody>
								</table>
							</div>
							<div class="col-lg-1"></div>
							<div class="col-lg-5">
								<h3 class="text-success">TRANSACTION DETAILS OF <span id="branch_name"></span></h3>
								<table id="transactionDatatable" class="table table-striped table-bordered">
									<thead class="theadRow">
									<tr>
									<th><i class="fa fa-sort-numeric-asc"></i> SL. No. </th>
									<th>Customer Name</th>
									<th>Contact Number</th>
									</tr>
									</thead>
									<tbody id="transactionBillResult">

									</tbody>
								</table>
							</div>
						</div>		
					</div>
				</div>
			</div>
		</div>		
	</div>
	<div style="clear:both"></div>	
	<?php include("footer.php"); ?>
</div>

<script type="text/javascript">
	var uri = window.location.toString();
	if (uri.indexOf("?") > 0) {		
		$(".container1").css('display','block');
		$(".container2").css('display','none');		
	}else{		
		$(".container1").css('display','none');
		$(".container2").css('display','block');
	}	

	$('#open-alert-modal').click(function(e){
		$('#alert-modal').modal('show');
	});


	$('#transactionBill').DataTable({
		responsive: true
	});	
	$('#transactionResult').DataTable({
		responsive: true
	}); 
	
	$('#currentTransactionResult').DataTable({
		responsive: true
	}); 
	
	$('#transactionDatatable').DataTable({
		responsive: true
	}); 

	$("#state_performance").change(function(){
		
		if (uri.indexOf("?") > 0) {
			var clean_uri = uri.substring(0, uri.indexOf("?"));
			window.history.replaceState({}, document.title, clean_uri);	
			$(".container1").css('display','none');			
			$(".container2").css('display','block');			
		}
		$('#branch_name').html("");
		$('#transactionDatatable').DataTable().clear().destroy();
		$('#transactionDatatable').DataTable({
			responsive: true
		}); 
		$('#currentTransactionResponse').html('<div class="ajaxload"><img src="loadingImage.gif"></div>');
		var search_date=$('#search_date').val();
 		if(search_date=="" || search_date==null){
			search_date="";
			$('.branch_name').html(this.value);
		}else{		
			$('.branch_name').html(this.value+" "+convertDate(search_date));
		}

		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: {stateCode: this.value,search_date:search_date},
			success: function(response){
				//alert(response)
				$(".ajaxload").fadeOut(2000, function() {
					$('#currentTransactionResult').DataTable().clear().destroy();
					$('#currentTransactionResponse').empty().append(response);// Add new data
					$('#currentTransactionResult').DataTable({
						responsive: true
					});
  
				});
			
			}
		});
		
	});
	
	
	function transactionBillResult(id,date){	
		$('#transactionBillResult').html('<div class="preload"><img src="images/data-loading.gif"></div>');
		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: { branch_id: id,bill_date:date},
			dataType: 'json',
			success: function(response){
				$(".preload").fadeOut(2000, function() {
					$('#branch_name').html(response.branch_name);
					$('#transactionDatatable').DataTable().clear().destroy();
					$('#transactionBillResult').empty().append(response.resultList); // Add new data
					$('#transactionDatatable').DataTable({
						responsive: true
					});	
					
				});				
			}
		}); 
	}
	
	
		$("#search_date").change(function(){
		
		if (uri.indexOf("?") > 0) {
			var clean_uri = uri.substring(0, uri.indexOf("?"));
			window.history.replaceState({}, document.title, clean_uri);	
			$(".container1").css('display','none');			
			$(".container2").css('display','block');			
		}
		$('#branch_name').html("");
		$('#transactionDatatable').DataTable().clear().destroy();
		$('#transactionDatatable').DataTable({
			responsive: true
		}); 
		$('#currentTransactionResponse').html('<div class="ajaxload"><img src="loadingImage.gif"></div>');	
		var search_state=$('#state_performance').val();
		
 		if(search_state=="" || search_state==null){
			search_state="";
			$('.branch_name').html(convertDate(this.value));
		}else{
			$('.branch_name').html(search_state+" "+convertDate(this.value));
		}
		
		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: {billDate: this.value,search_state:search_state},
			success: function(response){
				//alert(response)
				$(".ajaxload").fadeOut(2000, function() {
					$('#currentTransactionResult').DataTable().clear().destroy();
					$('#currentTransactionResponse').empty().append(response);// Add new data
					$('#currentTransactionResult').DataTable({
						responsive: true
					});
  
				});
			
			}
		});
		
	});
	
	function convertDate(dateString){
		var p = dateString.split(/\D/g)
		return [p[2],p[1],p[0] ].join("-")
	}
</script>