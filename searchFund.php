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
	
	$branch_id=$_POST['branch_id'];
	$fund_date = $_POST['fund_date'];
	if($branch_id=="" && $branch_id==null){			
		$condition1 = "";
		$branchName = "";
	}else{			
		$condition1 = "and f.branch='$branch_id' ";
		$branchQuery=mysqli_query($con,"SELECT branchName from branch where branchId='$branch_id'");
		$bRow= mysqli_fetch_assoc($branchQuery);
		$branchName="BRANCH : ".$bRow["branchName"];
	}

	if($fund_date=="" && $fund_date==null){
		$fund_date=$current_date;
		$condition2 = "and f.date='$fund_date' ";
	}else{			
		$condition2 = "and f.date='$fund_date' ";
	}

	$fundDate= date("d-m-Y", strtotime($fund_date));

	$fundQuery = mysqli_query($con,"SELECT b.branchName, f.* FROM `fund` f inner join branch b on b.branchId=f.branch WHERE b.status=1 $condition1 $condition2 order by id desc");
	$fCount=mysqli_num_rows($fundQuery);	

?>

<style> 
	.panel-heading input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		background-position: 220px 12px; 
		background-repeat: no-repeat;
		padding: 12px 50px 12px 15px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	
	#ornament-datatable input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 12px;
		background-color: white;
		width:120px;
		padding: 5px;
	}
	
	#search_branchId,#search_date{
		padding: 15px;
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
		width:395px;
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
<div id="wrapper">
	<div class="content animate-panel">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">	
						<div class="row">
							<div class="col-lg-12">
								<div class="col-lg-8">	
									<h3 class="text-success"> 
										<i class="fa-Icon fa fa-exchange" aria-hidden="true"></i> FUND DETAILS 
									</h3>
								</div>	
								<form id="search_fundTransaction" method="POST" action="">
									<div class="col-lg-2">
										<input type="date" name="fund_date" id="search_date" placeholder="Select the date">
									</div>							
									<div class="col-lg-2">						
										<div class="input-group">
											<input list="branchList" class="form-control" id="search_branchId" name="branch_id" placeholder="SELECT BRANCH" >
											<span class="input-group-btn">
												<button class="btn btn-primary btn-block" style="height: 49px;" id="search_transaction" type="submit"><i class="fa fa-search"></i></button>
											</span>
										</div>							
									</div>
									
								</form>


							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="container1">
							<div class="col-lg-12">
								<h3 class="text-success"><i class="fa-Icon fa fa-eye"></i> VIEW FUND REQUEST LIST OF  <span class="branch_name"><?php echo $branchName." ".$fundDate." "; ?></span></h3>
								<table id="fund-datatable" class="table table-striped table-bordered">
									<thead class="theadRow">
										<tr>
											<th>Sl.No.</th>
											<th>BRANCH</th>
											<th>DATE TIME</th>
											<th>TYPE</th>											
											<th>AVAILABLE</th>																				
											<th>REQUEST</th>
											
											<th>CUSTOMER NAME</th>	
											<th>CUSTOMER MOBILE</th>	
											<th>STATUS</th>	
											<th class="text-center">ACTION</th>									
										</tr>
									</thead>									
									<tbody id="fundDetail-Response">
																			
									<?php
									if(mysqli_num_rows($fundQuery)!=0){ 
										$i = 1;
										while($row = mysqli_fetch_assoc($fundQuery)){
										$fund_date= date("d-m-Y", strtotime($row["date"]));
									?>								
											<tr id="row_<?php echo $i;?>">												
												<td><?php echo $i;?><input type="hidden" class="form-control" id="fund_id" name="fund_id" value="<?php echo $row['id'];?>"></td>
												<td><?php echo $row["branch"];?></td>
												<td><?php echo $fund_date;?> <?php echo $row["time"];?></td>
												<td><?php echo $row["type"];?></td>
												<td><?php echo $row["available"];?></td>
												<td><input type="text" class="form-control" id="request_<?php echo $row['id'];?>" name="request" value="<?php echo $row["request"];?>"></td>
												<td><input type="text" class="form-control" id="customerName_<?php echo $row['id'];?>" name="customerName" value="<?php echo $row["customerName"];?>"></td>
												<td><input type="text" class="form-control" id="customerMobile_<?php echo $row['id'];?>" name="customerMobile" value="<?php echo $row["customerMobile"];?>"></td>
												<td><input type="text" class="form-control" id="status_<?php echo $row['id'];?>" name="status" value="<?php echo $row["status"];?>"></td>
												<td style='text-align:center'>
												<button class='btn btn-success'  title='Update Fund Data' onclick='update_fund(<?php echo $row['id'];?>)'><i class='fa fa-edit' aria-hidden='true'></i> </button>											
												<button class='btn btn-danger' onclick='delete_fund_transaction(<?php echo $row['id'];?>)'><i class='fa fa-trash' aria-hidden='true' title='Delete this transaction'></i> </button>											
												</td>												
											</tr>									
									<?php
											$i++;										
										}

									}										
									?>
																								
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
	$("#search_branchId,#search_date").change(function(){
		
		var fund_date=$('#search_date').val();
		var branchId=$('#search_branchId').val();
						
		if(fund_date=="" && branchId==""){		    
		    alert("Please select date or branch ID");		
		}
		
	});
	
	$('#fund-datatable').DataTable({
		responsive: true
	});	
	
	// UPDATE BILL TRANSACTION
	function update_fund(fund_id){
		
		var request=$("#request_"+fund_id).val();
		var type=$("#type_"+fund_id).val();
		var customerName=$("#customerName_"+fund_id).val();
		var customerMobile=$("#customerMobile_"+fund_id).val();
		var status=$("#status_"+fund_id).val();
		
 		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: {update_fund: "update_fund",fund_id:fund_id,request:request,type:type,customerName:customerName,customerMobile:customerMobile,status:status},
			success: function(response){
				//alert(response)
 				if(response=="SUCCESS"){
					alert("The fund data has been updated successfully");
					location.reload();
				}else{
					alert("Oops!! Error in updating data");
					location.reload();
				}
			}
		});
		
	}
	function convertDate(dateString){
		var p = dateString.split(/\D/g)
		return [p[2],p[1],p[0] ].join("-")
	}
	
	// DELETE FUND TRANSACTION
	function delete_fund_transaction(fund_id){
		
		var answer = window.confirm("Do you wish to delete this transaction?");
		if (answer){
			$.ajax({
				type: "POST",
				url: "searchBillAjax.php",
				data: {action:"delete_fundTransaction",fund_id:fund_id},
				success: function(response){
					//alert(response);
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
