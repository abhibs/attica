<?php
	error_reporting(E_ERROR | E_PARSE);
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


	if (isset($_POST["addStock"])) {
		$time = date('H:i:s');
		//$product_id="AGPL-".mt_rand(100000,999999);
		$inscon = "INSERT INTO inventory(product_name,brand,category,stock,inventory_received,inventory_shipped,date,time,supplier,purchase_date,remarks) VALUES 
		('$_POST[product_name]','$_POST[brand]','$_POST[category]','$_POST[quantity]','$_POST[quantity]','0','$_POST[date]','$time','$_POST[supplier]','$_POST[purchase_date]','$_POST[remarks]')";
		if ((mysqli_query($con, $inscon))) {
			echo "<script>alert('Stock Added')</script>";				
		} 
		else {				
			echo "<script>alert('Cannot Add Stock')</script>";				
		}		
	}
	
	if (isset($_POST["updateStock"])) {
		$time = date('H:i:s');
		if($_POST["quantity"]==""){
			$quantity=0;
			$inventory_received=$_POST["inventory_received"];
		}else{
			$quantity=$_POST["quantity"];
			$inventory_received=$_POST["inventory_received"]+$_POST["quantity"];
		}
		$stock=$_POST["stock"]+$quantity;		
		$id=$_POST["id"];
		$updateStock = "UPDATE inventory SET stock=$stock, inventory_received=$inventory_received,remarks='$_POST[remarks]' WHERE id=$id";
		
		if ((mysqli_query($con, $updateStock))){
			echo "<script>alert('Stock Updated')</script>";				
		}else{				
			echo "<script>alert('Cannot Update Stock')</script>";				
		}		
	}
	
	if (isset($_GET["edit_stock"])){
			$getQuery=mysqli_query($con,"SELECT * FROM inventory where id=".$_GET["edit_stock"]);
			$count=mysqli_num_rows($getQuery);
			if($count>0)
			{
				$res = mysqli_fetch_assoc($getQuery);	
				//print_r($res);			
			}
			$title="Update The Inventory Stock";
			$btn_text=" UPDATE STOCK";
			$btn_name="updateStock";
			$id=$_GET["edit_stock"];
			$readonly="readonly";
			$btn='<div class="col-xs-2"><a href="inventory_stock.php" style="float:right;" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-arrow-left"></i> Add New Inventory</b></a></div>';
			$col="col-xs-8";
			
	}else{
			$title="Add New Inventory Stock";
			$btn_text=" ADD STOCK ";
			$btn_name="addStock";
			$col="col-xs-10";
			
	}
	
	
	if (isset($_GET["delete_stock"])){
		$deleteQuery=mysqli_query($con,"DELETE FROM inventory WHERE id=".$_GET["delete_stock"]);
		if($deleteQuery==1){
			echo "<script>alert('Inventory Deleted')</script>";	
			header("Location: inventory_stock.php");
		}else{
			echo "<script>alert('Error!! Inventory Cannot Be Deleted')</script>";
			header("Location: inventory_stock.php");			
		}		
	}	
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
						<div class="<?php echo $col;?>">
							<h3 class="text-success"><b><i style="color:#990000" class="fa fa-hourglass-half"></i> <?php echo $title; ?> </b></h3>
						</div>
						<?php echo $btn;?>
						<div class="col-xs-2" >
							<a href="inventory_logs.php" style="float:right;" class="btn btn-success"><b> View Inventory Logs <i style="color:#ffcf40" class="fa fa-arrow-right"></i></b></a>
						</div>
					</div>
					
					<div class="panel-body" style="box-shadow:10px 15px 15px #999;margin-top:30px;">
						<form method="POST" class="form-horizontal" autocomplete="off" action="inventory_stock.php">
							<div class="form-group">							
								<div class="col-md-12">
									<div class="col-md-6">
										<div class="col-md-6">
											<label class="text-success">CATEGORY</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-alpha-asc"></span></span>   
												<?php if (isset($_GET["edit_stock"])){?>
													<input type="text" class="form-control" name="category" id="category" style="width: 100%;" value="<?php echo $res['category'];?>" <?php echo $readonly;?>/>											
												<?php }else{ ?>											
													<select class="form-control" id="category" name="category" <?php echo $readonly;?> required>												
														<option disabled selected>Select Category</option>												
														<option value="Light">Light</option>
														<option value="Bulb">Bulb</option>
														<option value="TubeLight">TubeLight</option>
														<option value="Computer">Computer</option>
														<option value="Computer Accessory">Computer Accessory</option>
														<option value="Weighing Scale">Weighing Scale</option>
														<option value="Karat Meter">Karat Meter</option>
														<option value="Web Camera">Web Camera</option>
														<option value="Light Accessory">Light Accessory</option>
														<option value="Wall Mount Fan">Wall Mount Fan</option>
														<option value="CCTV Camera">CCTV Camera</option>
														<option value="Chair">Chair</option>
														<option value="AC">AC</option>
													</select>
												<?php } ?>
											</div>	
										</div>
										<div class="col-md-6">
											<label class="text-success">BRAND</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>                
												<input type="text" required class="form-control" name="brand" id="brand" style="width: 100%;" value="<?php echo $res['brand'];?>" <?php echo $readonly;?>/>
											</div>
										</div>	
										
									<label class="col-sm-12"><br></label>
									
										<div class="col-md-6">
											<label class="text-success">SUPPLIER</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>                
												<input type="text" required class="form-control" name="supplier" id="supplier" style="width: 100%;" value="<?php echo $res['supplier'];?>" <?php echo $readonly;?>/>
											</div>
										</div>	


										<div class="col-md-6">
											<label class="text-success">PRODUCT NAME</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>                
												<input type="text" required class="form-control" name="product_name" id="product_name" style="width: 100%;" value="<?php echo $res['product_name'];?>" <?php echo $readonly;?>/>
											</div>
										</div>	
									<label class="col-sm-12"><br></label>
									<div class="col-md-6">
										<label class="text-success">PURCHASE DATE</label>
										<div class="input-group">
											<input type="date" required class="form-control" style="width: 100%;" name="purchase_date" id="purchase_date" value="<?php echo $res['purchase_date'];?>" <?php echo $readonly;?>>
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
										</div>
									</div>
									
									<div class="col-md-6">
										<label class="text-success">DELIVERY DATE</label>
										<div class="input-group">
											<input type="date" required class="form-control" style="width: 100%;" name="date" id="date" value="<?php echo $res['date'];?>" <?php echo $readonly;?>>
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
										</div>
									</div>						
									
									</div>
								
									<div class="col-md-6">
										<div class="col-md-12">
											<label class="text-success">REMARKS</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-edit"></span></span>
												<textarea rows="5" class="form-control" name="remarks" id="remarks"><?php echo $res['remarks'];?></textarea>
											</div>
										</div>					
									
										<label class="col-sm-12"><br></label>

										<?php if (isset($_GET["edit_stock"])){?>
										<div class="col-md-3">
											<label class="text-success">CURRENT STOCK</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-hourglass-half"></span></span>
												<input type="text" readonly class="form-control" style="width: 100%;" name="stock" id="stock" value="<?php echo $res['stock'];?>">
											</div>
										</div>
										<?php } ?>
										
										<div class="col-md-3">
											<label class="text-success">ADD QUANTITY</label>
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
												<input type="text" required class="form-control" style="width: 100%;" name="quantity" id="quantity">
											</div>
										</div>
										<div class="col-sm-3" align="left">
											<label style="height:40px"></label>
											<input type="hidden" class="form-control" name="id" style="width: 100%;" value="<?php echo $id;?>"/>
											<input type="hidden" class="form-control" name="inventory_received" style="width: 100%;" value="<?php echo $res['inventory_received'];?>"/>
											<button class="btn btn-success" name="<?php echo $btn_name; ?>" id="<?php echo $btn_name; ?>" type="submit" >
												<span style="color:#ffcf40" class="fa fa-check-circle"></span> &nbsp; <b><?php echo $btn_text; ?></b>
											</button>
										</div>								
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
									<th>Category</th>									
									<th>Brand</th>									
									<th>Supplier</th>									
									<th>Product Name</th>									
									<th>Qty Received</th>
									<th>Qty Shipped</th>
									<th>In Stock</th>
									<th>Remarks</th>
									<th>Purchase Date</th>									
									<th>Delivery Date</th>									
									<th>Last Updated</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$query=mysqli_query($con,"SELECT * FROM inventory order by id desc");
								$count=mysqli_num_rows($query);
								if($count>0)
								{
									$i = 1;
									while($row = mysqli_fetch_assoc($query)){
										
										$id=$row['id'];
									
										echo "<tr>";
										echo "<td>$i</td>";
										echo "<td>".$row['category']."</td>";									
										echo "<td>".$row['brand']."</td>";				
										echo "<td>".$row['supplier']."</td>";				
										echo "<td>".$row['product_name']."</td>";									
										echo "<td>".$row['inventory_received']."</td>";						
										echo "<td>".$row['inventory_shipped']."</td>";
										echo "<td>".$row['stock']."</td>";												
										echo "<td>".$row['remarks']."</td>";												
										echo "<td>".date("d-m-Y", strtotime($row['purchase_date']))."</td>";									
										echo "<td>".date("d-m-Y", strtotime($row['date']))."</td>";									
										echo "<td>".$row['last_updated']."</td>";									
										echo "<td class='text-center'><a class='btn btn-success' title='EDIT' href='inventory_stock.php?edit_stock=".$id."'> <i class='fa fa-edit'></i> </a> <br> <a onClick=\"javascript: return confirm('Do you confirm to delete this inventory?');\" class='btn btn-danger' title='DELETE' href='inventory_stock.php?delete_stock=".$id."'> <i class='fa fa-trash'></i> </a></td>";									
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
		</div>
	

<div class="col-lg-12"></div>
<div style="clear:both"></div>
<?php include("footer.php");?>
	<script>
		
		$('#inventory').DataTable({
			responsive: true,
			dom: 'Bfrtip',
			buttons: [
				{extend: 'csv',text: 'EXPORT TO EXCEL', title: 'Inventory Report', className: 'btn-md btn-info'},
			]
		});
		

	</script>	