<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
	    include("header.php");
		include("menuSoftware.php");
		$current_date=date('Y-m-d');
	}
	else{
        include("logout.php");
    }
	include("dbConnection.php");
	
	$contactNumber=$_GET['search'];

?>
<style> 
	.panel-heading input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		/* background-image: url('images/searchicon.png'); */
		background-position: 220px 12px; 
		background-repeat: no-repeat;
		padding: 12px 50px 12px 15px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	#search_branchId,#search_date{
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
		font-size: 18px;
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
	.fa-Icon {
		color: #990000;
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
		width:450px;
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
	.form-control{
		height:25px;
	}
	@media only screen and (max-width: 600px) {
		
		legend{

			width:295px;
			font-size: 10px;

		}	
	
	}
</style>
<datalist id="branchList"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">	
			<div class="modal-body">
			    <div class="row">
			        <div class="col-lg-6">
			            <table class="table table-bordered">
        					<caption>Status</caption>
        					<tbody>
        						<tr><td>Begin</td></tr>
        						<tr><td>Pending</td></tr>
        						<tr><td>ZONAL-VERIFIED</td></tr>
							<tr><td>Verified</td></tr>
        						<tr><td>Approved</td></tr>
        						<tr><td>Rejected</td></tr>
        					</tbody>
        				</table>
			        </div>
			        <div class="col-lg-6">
			            <table class="table table-bordered">
        					<caption>Payment Type</caption>
        					<tbody>
        					    <tr><td>Cash</td></tr>
        						<tr><td>Cash/IMPS</td></tr>
        						<tr><td>NEFT/RTGS</td></tr>
        					</tbody>
        				</table>
			        </div>
			    </div>   
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">	
						<div class="row">
							<div class="col-lg-12">
								<div class="col-lg-7">	
									<h3 class="text-success"> 
										<i class="fa-Icon fa fa-exchange" aria-hidden="true"></i> Transaction Details 
										<button class="btn btn-default" data-toggle="modal" data-target="#statusModal">+</button>
									</h3>
								</div>	

								<div class="col-lg-2">
									<input type="date" name="search_date" id="search_date" placeholder="Select the date">
								</div>								
								<div class="col-lg-3">								
									<div class="input-group">
										<input list="branchList" class="form-control" id="search_branchId" name="search_branchId" placeholder="SELECT BRANCH" required>
										<span class="input-group-btn">
											<button class="btn btn-primary btn-block" style="height: 49px;" id="search_trans" type="button"><i class="fa fa-search"></i></button>
										</span>
									</div>								
								</div>


							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="container1" style="display:none;">
							<?php
							if(isset($_GET['search'])){
							$transId=$_GET['search'];					
							$searchQuery=mysqli_query($con,"SELECT b.branchName,t.* FROM trans t inner join branch b on b.branchId=t.branchId where t.id = '$transId'");
							$count=mysqli_num_rows($searchQuery);
							$row = mysqli_fetch_assoc($searchQuery);
							?>
							<form id="bill-transaction">
							<div class="col-lg-4">
								<fieldset class="transaction-detail-box">
									<legend><i style="padding-top:15px" class="fa_Icon fa fa-sitemap"></i> <?php echo $row["branchName"];?> - <span class="branch_name"><?php echo $row["branchId"];?></span></legend>
									<div class="panel-body">
											<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">CUSTOMER</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="customer_name" name="name" value="<?php echo $row["name"]?>"></h6>
												</div>
												</div>				  
											</li>

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">CUSTOMER ID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><a href="editCustomers.php?mobile=<?php echo $row["phone"]?>" target="_blank"><?php echo $row["customerId"];?></a><input type="hidden" class="form-control" name="customerId" id="customerId" value="<?php echo $row["customerId"];?>"></h6>
												</div>
												</div>
											</li>
											

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">TYPE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0 text-uppercase"><?php echo $row["type"];?><input type="hidden" class="form-control" name="type" id="type" value="<?php echo $row["type"];?>"></h6>
												</div>
												</div>				  
											</li>
											
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">METAL</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0 text-uppercase"><a href='searchOrnament.php?billId=<?php echo $row["billId"];?>&date=<?php echo $row["date"];?>'><?php echo $row["metal"];?></a></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">RATE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><?php echo $row["rate"];?></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">PURITY</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><?php echo $row["purity"];?></h6>
												</div>
												</div>				  
											</li>											
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">RELEASES</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="treleases" name="releases" value="<?php echo $row["releases"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">RELEASE ID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="releaseID" name="releaseID" value="<?php echo $row["releaseID"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">RELEASE DATE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="date" class="form-control" id="relDate" name="relDate" value="<?php echo $row["relDate"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="display: hidden; ">
											</li>

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="display: hidden; ">
											</li>
									
											</ul>
		


									</div>
								</fieldset>
							</div>
							
							<div class="col-lg-4">
							<fieldset class="transaction-detail-box">
							<legend><i style="padding-top:15px" class="fa_Icon fa fa-list"></i> BILL ID : <a target='_blank' class='btn btn-success btn-md' title='View Bill' href='Invoice.php?id=<?php echo base64_encode($row['id']);?>'> <?php echo $row["billId"]?></a></legend>
								<div class="panel-body">
											<ul class="list-group list-group-flush">

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">CONTACT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="phone" name="phone" value="<?php echo $row["phone"]?>" readonly></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">GROSS WEIGHT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="grossW" name="grossW" value="<?php echo $row["grossW"];?>"></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">NET WEIGHT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="netW" name="netW" value="<?php echo $row["netW"];?>"></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">GROSS AMOUNT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="grossA" name="grossA" value="<?php echo $row["grossA"];?>"></h6>
												</div>
												</div>
											</li>											
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">NET AMOUNT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="netA" name="netA" value="<?php echo $row["netA"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">AMOUNT PAID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);" type="text" class="form-control" id="amountPaid" name="amountPaid" value="<?php echo $row["amountPaid"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">MARGIN % </h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="marginP" name="comm" value="<?php echo $row["comm"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">MARGIN  </h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="margin" name="margin" value="<?php echo $row["margin"];?>"></h6>
												</div>
												</div>
											</li>	
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="display: hidden; ">
											</li>



											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="display: hidden; ">
											</li>
											</ul>
															
								</div>
							</fieldset>
							</div>
							<div class="col-lg-4">
							<fieldset class="transaction-detail-box">
								<legend><i style="padding-top:15px" class="fa_Icon fa fa-calendar"></i> BILL DATE TIME : <?php echo date("d-m-Y", strtotime($row["date"]));?> <?php echo $row["time"]?></legend>
								<div class="panel-body">
											<ul class="list-group list-group-flush">

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">PAYMENT TYPE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="paymentType" name="paymentType" value="<?php echo $row["paymentType"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">CASH AMOUNT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);" type="text" class="form-control" id="cashA" name="cashA" value="<?php echo $row["cashA"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">IMPS AMOUNT</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input style="box-shadow: 0 0 5px rgba(255, 0, 0, 1);" type="text" class="form-control" id="impsA" name="impsA" value="<?php echo $row["impsA"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">STA</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="sta" name="sta" value="<?php echo $row["sta"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">STA DATE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="date" class="form-control" id="staDate" name="staDate" value="<?php echo $row["staDate"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
													<div class="col-sm-4">
														<h6 class="mb-0">ZONAL</h6>
													</div>
													<div class="col-sm-1"> : </div>
													<div class="col-sm-7">
														<select class="form-control" style="padding:0px 5px" name="ezviz_vc" required>
															<option selected="true" value="">Zonal Name</option>
															<option value="1000211">Gayatri</option>
															<option value="1000036">Ramya</option>
															<option value="1000336">Murugesh</option>
															<option value="1000423">Honnegowda</option>
															<option value="1000043">Nagarathna</option>
															<option value="1002063">Naveen</option>
															<option value="1001038">Revathi</option>
															<option value="1001627">Ramesh</option>
															<option value="1000735">Selvam</option>
															<option value="1003665">Bhavesh</option>
															<option value="1001234">Salman</option>
															<option value="1005678">Arbin</option>
														</select>
													</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">STATUS</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="status" name="status" value="<?php echo $row["status"];?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">REMARKS</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="remarks" name="remarks" value="<?php echo $row["remarks"];?>"></h6>
												</div>
												</div>
											</li>	
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">PACKET NO.</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" class="form-control" id="packetNo" name="packetNo" value="<?php echo $row["packetNo"];?>"></h6>
												</div>
												</div>				  
											</li>											
											</ul>
															
								</div>
							</fieldset>
							</div>
							<input type="hidden" class="form-control" id="trans_id" name="trans_id" value="<?php echo $row["id"];?>">
							<div class="col-lg-1 pull-right">
							<button class="btn btn-primary btn-block" id="search" type="button" onclick="update_transaction()"> <i class="fa fa-upload" aria-hidden="true"></i></i> UPDATE </button>
							</div>
							</form>
							<?php				
							 } 
							?>
						</div>

						<div class="container2" style="display:block">
							<div class="col-lg-12">
								<h3 class="text-success"><i class="fa-Icon fa fa-eye"></i> View Transactions of <span class="branch_name"><?php echo $contactNumber; ?></span></h3>
								<div class="table-responsive">
								<table id="currentTransactionResult" class="table table-striped table-bordered">
									<thead class="theadRow">
										<tr>
											<th>#</th>
											<th>BRANCH NAME</th>
											<th>CUSTOMER</th>
											<th class="text-center">BILL ID</th>
											<th class="text-center">METAL</th>
											<th class="text-center">TYPE</th>
											<th class="text-center">PAYMENT TYPE</th>
											<th>GROSSW</th>	
											<th>NETW</th>
											<th>GROSSA</th>
											<th>NETA</th>
											<th>AMOUNTPAID</th>	
											<th>STATUS</th>
											<th class="text-center">ACTION</th>									
										</tr>
									</thead>
									<tbody id="currentTransactionResponse">								
									</tbody>
								</table>
								</div>
							</div>
							<div class="col-lg-1"></div>

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
	
	function convertDate(dateString){
		var p = dateString.split(/\D/g)
		return [p[2],p[1],p[0] ].join("-")
	}
	
	$("#search_trans").click(function(){
		
		var trans_date=$('#search_date').val();
		var trans_branchId=$('#search_branchId').val();
		
		if (uri.indexOf("?") > 0) {
			var clean_uri = uri.substring(0, uri.indexOf("?"));
			window.history.replaceState({}, document.title, clean_uri);	
			$(".container1").css('display','none');			
			$(".container2").css('display','block');			
		}
		$('.branch_name').html("");
		$('#currentTransactionResult').DataTable().clear().destroy();
		$('#currentTransactionResult').DataTable().draw();

						
		if(trans_date=="" && trans_branchId==""){
		    
		    alert("Please select date or branch ID");
		
		}else{
		    $('#currentTransactionResponse').html('<div class="ajaxload"><img src="loadingImage.gif"></div>');	
    		$.ajax({
    			url: "searchBillAjax.php",
    			type: "post",
    			data: {transactionType:"physical_gold",trans_date: trans_date,trans_branchId:trans_branchId},
    			dataType: 'json',
    			success: function(response){
    				
    				$('.branch_name').html(response.branch_name+" "+convertDate(response.billDate));
    				$(".ajaxload").fadeOut(1000, function() {
    					$('#currentTransactionResult').DataTable().clear().destroy();
    					$('#currentTransactionResponse').empty().append(response.resultList);
    					$('#currentTransactionResult').DataTable({
    						responsive: true
    					});
      
    				});
    			
    			}
    		});
		}
	});

	

	
	
	$(document).ready(function(){
		
		$("#netA").keyup(function(){			
			var gross = parseFloat($("#grossA").val());
			var amountPaid = Math.round($("#amountPaid").val());
			var margin = Math.round(gross - amountPaid);
			var marginP = parseFloat((margin/gross)*100).toFixed(2);
			$("#margin").val(margin);
			$("#marginP").val(marginP);						
		});
		
		$("#amountPaid").keyup(function(){			
			var trans_type=$("#type").val();
			//alert(trans_type);
			var gross = parseFloat($("#grossA").val());
			var amountPaid = Math.round($("#amountPaid").val());
			if(trans_type=="Physical Gold"){
				$("#netA").val(this.value);	
				var margin = Math.round(gross - amountPaid);
				var marginP = parseFloat((margin/gross)*100).toFixed(2);
				$("#margin").val(margin);
				$("#marginP").val(marginP);					
			}
			if(trans_type=="Release Gold"){
				var releases=$("#treleases").val();	
				var netA= parseFloat(releases) + parseFloat(amountPaid);
				$("#netA").val(netA);
				var margin = Math.round(gross - netA);
				var marginP = parseFloat((margin/gross)*100).toFixed(2);
				$("#margin").val(margin);
				$("#marginP").val(marginP);				
			}
						
		});
	});
	
	// UPDATE BILL TRANSACTION
	function update_transaction(){	
		var billData=$("#bill-transaction").serialize();//alert(values);
		//alert(billData);
		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: {update_transaction: "update_transaction",billData:billData},
			success: function(response){
 				if(response=="SUCCESS"){
					alert("The bill data has been updated successfully");
					location.reload();
				}else{
					alert("Oops!! Error in updating data");
					location.reload();
				} 
			}
		});
		
	}
	// DELETE BILL TRANSACTION
	function delete_transaction(trans_id){
	    // alert(trans_id);
		var answer = window.confirm("Do you wish to delete this transaction?");
		if (answer){
			$.ajax({
				type: "POST",
				url: "searchBillAjax.php",
				data: {delete_transaction:"delete_transaction",trans_id:trans_id},
				success: function(response){
					if(response=="SUCCESS"){
						alert("The transaction data has been deleted successfully");
					}else{
						alert("Oops!! Error in deleting data");
					}
				}
			});
			location.reload();
		}
	
	}
</script>
