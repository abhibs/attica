<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");
	$type=$_SESSION['usertype'];
	date_default_timezone_set("Asia/Calcutta"); 
	$current_date=date('Y-m-d');
	
	
	if($type=='Software'){
	    include("header.php");
		include("menuSoftware.php");
		$current_date=date('Y-m-d');
	}elseif($type == 'Legal') {
		include("header.php");
		include("menulegal.php");
	} else{
        include("logout.php");
    }
	

	if(trim($_GET['search'])!=""){
		
		$contactNumber= trim($_GET['search']);
		
	}elseif(trim($_GET['contact_no'])!=""){
		
		$contactNumber= trim($_GET['contact_no']);

	}else{

		$contactNumber= "";
		
	}
	

?>
<style> 
	input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		/*background-image: url('images/searchicon.png');
		background-position: 220px 12px; */
		background-repeat: no-repeat;
		padding: 12px 12px 12px 12px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	#search_type,#search_date{
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
	
	.position_center{
		position: absolute;
		float: left;
		top: 40%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">	
						<div class="row">
							<div class="col-lg-12">	
								<div class="col-lg-8"></div>
								<form action="<?php echo $_SERVER['REQUEST_URI'];?>">	
								<div class="col-lg-3" id="type_number">
									<div class="input-group">							
										<input type="text" name="search" id="search" placeholder="ENTER THE NUMBER" autocomplete="off" style="width: 313px;">
										<span class="input-group-btn">
											<button class="btn btn-primary btn-block" style="height: 49px;" id="search_trans" type="submit"><i class="fa fa-search"></i></button>
										</span>										
									</div>	
								</div>	
								</form>	
								<div class="col-lg-1"></div>								
							</div>
						</div>
					</div>
					<div class="panel-body">
					
						
							<?php
							if(isset($_GET['contact_no'])){
							?>
							<div class="container1">
							<div class="col-lg-5">
								<fieldset class="ornament-detail-box">
									<legend><i style="padding-top:15px" class="fa_Icon fa fa-eye"></i> VIEW TRANSACTIONS OF <span class="branch_name"><?php echo $contactNumber; ?></span></legend>
									<div class="panel-body">
									
										<?php
										$contact_no=trim($_GET['contact_no']);	
										$customerQuery=mysqli_query($con,"SELECT name FROM customer where mobile = '$contact_no'");										
										$searchQuery=mysqli_query($con,"SELECT count(*) as bill_count, SUM(grossW) as gross_weight FROM trans where phone = '$contact_no' and status='Approved'");
										
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
											<h6 class="mb-0"><?php echo $contact_no; ?></h6>
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
												<!--<th class='text-center'>Details</th>-->
												
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
														//echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='xviewCustomerDetails.php?id=$contactNumber&ids=".$sRow['invoice_id']."'><i class='fa_Icon fa fa-eye'></i> View Details</a></b></td>";
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
							</div>
							<?php				
							 }							 
							?>		
							<?php
							if(isset($_GET['search'])){
							?>
								<div class="container2">
									<div class="col-lg-6">
										<fieldset class="ornament-detail-box">
											<legend><i style="padding-top:15px" class="fa_Icon fa fa-eye"></i> VIEW TRANSACTIONS OF <span class="branch_name"><?php echo $contactNumber; ?></span></legend>
												<div class="panel-body">									
													<?php
													
														$search_number=$_GET['search'];	
														$customerQuery=mysqli_query($con,"SELECT name,mobile,idNumber,addNumber,idProof,addProof FROM customer where mobile = '$search_number' or idNumber='$search_number' or addNumber='$search_number'");
														$count=mysqli_num_rows($customerQuery);
														
														if($count>0){
													?>
															<table class="table table-striped table-bordered table-hover">
																<thead>
																	<tr class="theadRow">
																		<th>#</th>
																		<th>NAME</th>
																		<th>MOBILE</th>
																		<th>ID PROOF</th>
																		<th>ADD PROOF</th>
																	</tr>
																</thead>
																<tbody>
																	<?php
																	$i = 1;

																	while ($row = mysqli_fetch_assoc($customerQuery)) {
																	echo "<tr>";
																	echo "<td>" . $i . "</td>";
																	echo "<td>" . $row['name'] . "</td>";
																	echo "<td><a href='searchbillsdata.php?contact_no=$row[mobile]' target='_blank'>" . $row['mobile'] . "</a></td>";
																	echo "<td>" .$row['idProof'] ."<br>". $row['idNumber'] . "</td>";
																	echo "<td>" .$row['addProof'] ."<br>". $row['addNumber'] . "</td>";
																	echo "</tr>";
																	$i++;
																	}
																	?>
																</tbody>
															</table>
												
													<?php
														}else{
															echo '<h4 class="position_center">DATA NOT AVAILABLE</h4>';
															
														}
													?>			


												</div>
													
										</fieldset>
									</div>
								</div>
									<?php
										}
									?>	
						
					</div>
				</div>
			</div>
		</div>		
	</div>
	<div style="clear:both"></div>	
	<?php include("footer.php"); ?>
</div>

<script type="text/javascript">	

	$("#search_trans").click(function(){
		var search_number=$("#search").val();
		search_number=search_number.replace(/\s/g, "");
		$("#search").val(search_number);
	});	

	
	function convertDate(dateString){
		var p = dateString.split(/\D/g)
		return [p[2],p[1],p[0] ].join("-")
	}
	
</script>