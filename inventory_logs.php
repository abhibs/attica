<?php
	session_start();
	$type=$_SESSION['usertype'];
	$branch=$_SESSION['branchCode'];

	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
	
	if($type == 'Assets'){
		include("header.php");
		include("menuassets.php");
    }
    else{
        include("logout.php");
    }
	
	
	if (isset($_POST["addStock"])){
		
		$time = date('H:i:s');
		
		if($_POST["from_branch"]=="Attica Inventory"){
			
			$stock=$_POST["stock"]-$_POST["quantity"];
			$inventory_shipped=$_POST["inventory_shipped"]+$_POST["quantity"];
			$id=$_POST["product_id"];
			$updateStock = "UPDATE inventory SET stock=$stock, inventory_shipped=$inventory_shipped WHERE id=$id";
			mysqli_query($con, $updateStock);
		}
		
		if($_POST["to_branch"]=="Attica Inventory"){
			
			$stock=$_POST["stock"]+$_POST["quantity"];
			$inventory_received=$_POST["inventory_received"]+$_POST["quantity"];
			$id=$_POST["product_id"];
			$updateStock = "UPDATE inventory SET stock=$stock, inventory_received=$inventory_received WHERE id=$id";
			mysqli_query($con, $updateStock);
		}	
		
		$inscon = "INSERT INTO inventory_logs(product_id,product_name,from_branch,to_branch,quantity,date,time,remarks,delivery_type) VALUES 
		('$id','$_POST[product_name]','$_POST[from_branch]','$_POST[to_branch]','$_POST[quantity]','$_POST[date]','$time','$_POST[remarks]','$_POST[delivery_type]')";
		if ((mysqli_query($con, $inscon))) {		
		
			echo "<script>alert('Stock Sent')</script>";				
		} 
		else {				
			echo "<script>alert('Cannot Send Stock')</script>";				
		}		
	}	

	$prodQuery=mysqli_query($con,"SELECT id,product_name FROM inventory order by id desc");
	$count=mysqli_num_rows($prodQuery);
	
?>
<style>
	#wrapper{
		background-color: #e6e6fa;
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
	.hpanel .panel-body{
		padding: 5px 15px 5px;
	}	
	.dataTables_wrapper .row{
		margin-right: 0px;
		margin-left: 0px;
	}
	
	.btn-default{
		margin-bottom: 10px;
		background: #b8860b;
		color: aliceblue;
	}
	#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	.row-danger{
	    background:#f08080;
        color: azure;
        font-size: 14px;
        font-weight: bold;
	}
	
	.row-warning{
        font-size: 14px;
        font-weight: bold;		    
	}
</style>

<div id="wrapper">
	<div class="content">
		<div class="row-content">
			<div class="col-lg-12">
				<div  class="hpanel">
					<div class="panel-heading" >
						<div class="col-xs-10">
							<h3 class="text-success"><b><i style="color:#990000" class="fa fa-hourglass-half"></i> Stock Transition </b></h3>
						</div>
						<div class="col-xs-2" >
						<a href="inventory_stock.php" style="float:right;" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-arrow-left"></i> View Inventory</b></a>
						</div>
					</div>
					<div class="panel-body" style="box-shadow:10px 15px 15px #999;margin-top:30px;">
						<form method="POST" class="form-horizontal" autocomplete="off" action="inventory_logs.php">
						<div class="form-group">
						
						<div class="col-md-6">
						
							<div class="col-xs-6">
								<label class="text-success">DELIVERY TYPE</label>
								<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book"></span></span>
								<select class="form-control" id="delivery_type" name="delivery_type">
									<option Readonly disabled selected>Select Type</option>
									<option value="Sent">Sent</option>
									<option value="Received">Received</option>
								</select>
								</div>
							</div>					
							<div class="col-md-6">
							<label class="text-success">DATE</label>
							<div class="input-group">
							<input type="date" class="form-control" style="width: 100%;" name="date" id="date">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
							</div>
							</div>	
						<label class="col-sm-12"><br></label>
						<div class="col-xs-6">
							<label class="text-success">FROM BRANCH</label>

							<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
							<input list="from_bId" class="form-control" name="from_branch" id="from_branch" placeholder="Branch Id" />  
							</div>
						</div>

						<div class="col-sm-6" id="tbr">
							<label class="text-success">TO BRANCH</label>
							<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
							<input list="to_bId" class="form-control" name="to_branch" id="to_branch" placeholder="Branch Id" />  
							</div>
						</div>
						
						<datalist id="from_bId">
							<option value="Attica Inventory" >ATTICA INVENTORY</option>
							<?php
							$sqlb="select * from branch order by branchName";
							$resb = mysqli_query($con, $sqlb);					
							while($row = mysqli_fetch_array($resb)){
							?>
							<option value="<?php echo $row['branchName']; ?>"><?php echo $row['branchName']; ?></option>
							<?php } ?>
						</datalist>		
						<datalist id="to_bId">
							<option value="Attica Inventory">ATTICA INVENTORY</option>
							<?php
							$sqlb="select * from branch order by branchName";
							$resb = mysqli_query($con, $sqlb);					
							while($row = mysqli_fetch_array($resb)){
							?>
							<option value="<?php echo $row['branchName']; ?>"><?php echo $row['branchName']; ?></option>
							<?php } ?>
						</datalist>	

						<label class="col-sm-12"><br></label>

						<div class="col-md-6">
						<label class="text-success">PRODUCT NAME</label>
						<div class="input-group">
						<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>               

						<select class="form-control" id="product_id" name="product_id">
							<option disabled selected>SELECT PRODUCT</option>
							<?php
							if($count>0)
							{					
								while($prod = mysqli_fetch_array($prodQuery))
								{
							?>
							<option value="<?php echo $prod['id']; ?>"><?php echo $prod['product_name']; ?></option>
							<?php
								}		
							} 
							?>
						</select>
						<input type="hidden" readonly class="form-control" style="width: 100%;" name="product_name" id="product_name">
						</div>
						</div>
						
			
						
						<div class="col-md-3">
							<label class="text-success">CURRENT STOCK</label>
							<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-hourglass-half"></span></span>
							<input type="text" readonly class="form-control" style="width: 100%;" name="stock" id="stock">
							<input type="hidden" readonly class="form-control" style="width: 100%;" name="inventory_received" id="inventory_received">
							<input type="hidden" readonly class="form-control" style="width: 100%;" name="inventory_shipped" id="inventory_shipped">
							</div>
						</div>
						
						<div class="col-md-3">
							<label class="text-success">QUANTITY</label>
							<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
							<input type="text" class="form-control" style="width: 100%;" name="quantity" id="quantity" onchange=javascript:cal(this.form);>
							</div>
						</div>

						
						</div>
						
						
						
						
						<div class="col-md-6">
							<div class="col-md-12">
								<label class="text-success">REMARKS</label>
								<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-edit"></span></span>
								<textarea rows="5" class="form-control" name="remarks" id="remarks"></textarea>
								</div>
							</div>	
						<label class="col-sm-12"><br></label>							

						<div class="col-sm-2" align="left">
							<label style="margin-top:40px;"></label>
							<button class="btn btn-success" name="addStock" id="addStock" type="submit" ><span style="color:#ffcf40" class="fa fa-check-circle"></span> &nbsp; <b>Submit</b></button>
						</div>						
						</div>						

						</div>
						</form>
					</div>
					
					
					<div class="col-lg-12"></div>
						<div class="panel-body" style="box-shadow:10px 15px 15px #999;">
							<table id="inventory" class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th>Product Name</th>									
										<th>From</th>									
										<th>To</th>
										<th>Quantity</th>									
										<th>Remarks</th>
										<th>Date</th>									
										<!--<th class="text-center">Action</th>-->
									</tr>
								</thead>
								<tbody>
								<?php
									$query=mysqli_query($con,"SELECT * FROM inventory_logs order by id desc");
									$count=mysqli_num_rows($query);
									if($count>0)
									{
										$i = 1;
										while($row = mysqli_fetch_assoc($query)){
											
											$id=$row['id'];										
											echo "<tr>";
											echo "<td>$i</td>";
											echo "<td>".$row['product_name']."</td>";									
											echo "<td>".$row['from_branch']."</td>";									
											echo "<td>".$row['to_branch']."</td>";									
											echo "<td>".$row['quantity']."</td>";									
											echo "<td>".$row['remarks']."</td>";									
											echo "<td>".date("d-m-Y", strtotime($row['date']))."</td>";									
											//echo "<td><a class='btn btn-success' title='EDIT' href='inventory_stock.php?edit_stock=".$id."'> <i class='fa fa-edit'></i> </a><button class='btn btn-danger' title='DELETE' style='margin-left:10px'> <i class='fa fa-trash'></i> </button></td>";									
											echo "</tr>";								
											$i++;
											
										}
									}
								?>
								</tbody>
							</table>
						</div>
				</div>
			</div>
	<div class="col-lg-12"></div>
	<div style="clear:both"></div>
		<?php include("footer.php");?>
	</div>
	<script>
	    $(document).ready(function(){
            $("#product_id").change(function(){				
                var product_id = $("#product_id").val();	
				//alert(product_id);
				$.ajax({
					url: "inventory_ajax.php",
					type: "post",
					data: {action: "get_current_stock",product_id:product_id},
					dataType: 'json',
					success: function(response){
						//alert(response);
						$("#stock").val(response.stock);			
						$("#product_name").val(response.product_name);			
						$("#inventory_received").val(response.inventory_received);			
						$("#inventory_shipped").val(response.inventory_shipped);			
						
					}
				});			
            });
            
  			$("#delivery_type").change(function(){
				var delivery_type = $("#delivery_type").val();	
				const option = new Option('Attica Inventory', 'Attica Inventory');
				if(delivery_type=="Sent"){					
					$("#from_bId").append(option);					
					$("#to_bId option[value='Attica Inventory']").remove();
				}else{
 					$("#to_bId").append(option);
					$("#from_bId option[value='Attica Inventory']").remove();
				}
			});          
            
        });	
		
		$('#inventory').DataTable({
			responsive: true,
			dom: 'Bfrtip',
			buttons: [
				{extend: 'csv',text: 'EXPORT TO EXCEL', title: 'Inventory Report', className: 'btn-md btn-info'},
			]
		});
	</script>