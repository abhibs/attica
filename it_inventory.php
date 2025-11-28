<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type=$_SESSION['usertype'];
$branch=$_SESSION['branchCode'];

date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)

if($type=='ITMaster'){
	include("header.php");
	include("menuItMaster.php");
}
else{
	include("logout.php");
}
	
include("dbConnection.php");
if(isset($_POST['submit'])){
      
    
	$branchId=$_POST['branchId'];
	$userName=$_POST['userName'];
	$serialNumber=$_POST['serialNumber'];
	$keyboard=$_POST['keyboard'];
	$displaySize=$_POST['displaySize'];
	$processor=$_POST['processor'];
	$ram=$_POST['ram'];
	$mouse=$_POST['mouse'];
	$headphone=$_POST['headphone'];
	$systemName=$_POST['systemName'];
	$storage=$_POST['storage'];
	$sql = mysqli_query($con, "INSERT INTO  asset (branchId,userName,serialNumber,keyboard,displaySize,processor,ram,mouse,headphone,systemName,storage) VALUES('$branchId','$userName','$serialNumber','$keyboard','$displaySize','$processor','$ram','$mouse','$headphone','$systemName','$storage')");
   // $query=mysqli_query($con,$sql);
  
   if($sql){
	  echo "<script>alert('Inventory Added')</script>";
	  header("Location: it_inventory.php");
	}
	else{
	  echo "<script>alert('Error!! Inventory Cannot Be Added')</script>";
	  header("Location: it_inventory.php");
	}
  }

?>
<style>
	#results img{
		width:100px;
	}
	#wrapper{
		background: #f5f5f5;
	}
	
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
		background-color:#fffafa;
	}
	.quotation h3{
		color:#123C69;
		font-size: 18px!important;
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
	.btn-info{
		background-color:#123C69;
		border-color:#123C69;
		font-size:12px;
	}	
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
		background-color:#123C69;
		border-color:#123C69;
	}
	.fa_Icon{
		color:#ffa500;
	}
	thead {
		text-transform:uppercase;
		background-color:#123C69;

	}
	thead tr{
		color: #f2f2f2;
		font-size:12px;
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

	.modaldesign {
		float: right;
		cursor: pointer;
		padding: 5px;
		background:none;
		color: #f0f8ff;
		border-radius: 5px;
		margin: 15px;
		font-size: 20px;
	}
	#available{
		text-transform:uppercase;
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
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"> <i style="color:#990000" class="fa fa-desktop"></i> Systems Information </h3>
				</div>
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<form method="POST" class="form-horizontal" action="">
					 <h3 class="text-success"> &nbsp; <i style="color:#990000" class="fa fa-window-restore"></i><b> Add IT Inventory </b></h3>
                    <br>
					<div class="col-md-3">
						<label class="text-success">Select Branch</label>
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
							<input list="branchList" name="branchId" placeholder="SELECT BRANCH" required class="form-control" >
						</div>
					</div>
						<div class="col-md-3">
							<label class="text-success">Username</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-user-circle"></span></span>
								<input type="text" required class="form-control" name="userName" id="userName" style="width: 100%;" />
							</div>
						</div>
						<div class="col-md-3">
							<label class="text-success">Serial number</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>
								<input type="text" required class="form-control" name="serialNumber" id="serialNumber" style="width: 100%;" />
							</div>
						</div>
						<div class="col-md-3">
							<label class="text-success">Keyboard</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-keyboard-o"></span></span>
								<input type="text" required class="form-control" name="keyboard" id="keyboard" style="width: 100%;"/>
							</div>
						</div>
						<label class="col-sm-12"><br></label>
						<div class="col-md-3">
							<label class="text-success">Display size</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-desktop"></span></span>
								<input type="text" required class="form-control" name="displaySize" id="displaySize" style="width: 100%;"/>
							</div>
						</div>

						<div class="col-md-3">
							<label class="text-success">Processor</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-microchip"></span></span>
								<input type="text" required class="form-control" name="processor" id="processor" style="width: 100%;" />
							</div>
						</div>
						<div class="col-md-3">
							<label class="text-success">RAM</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-microchip"></span></span>
								<input type="text" required class="form-control" name="ram" id="ram" style="width: 100%;" />
							</div>
						</div>

						<div class="col-md-3">
							<label class="text-success">Mouse</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>
								<input type="text" required class="form-control" name="mouse" id="mouse" style="width: 100%;" />
							</div>
						</div>
						<label class="col-sm-12"><br></label>

						<div class="col-md-3">
							<label class="text-success">Headphone</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-sort-numeric-asc"></span></span>
								<input type="text" required class="form-control" name="headphone" id="headphone" style="width: 100%;" />
							</div>
						</div>

						

						<div class="col-md-3">
							<label class="text-success">System Name</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-desktop"></span></span>
								<input type="text" required class="form-control" name="systemName" id="systemName" style="width: 100%;"/>
							</div>
						</div>

						<div class="col-md-3">
							<label class="text-success">Storage</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-archive"></span></span>
								<input type="text" required class="form-control" name="storage" id="storage" style="width: 100%;" />
							</div>
						</div>
                            
                            <div class="col-sm-14" style="text-align:center">
                                <button class="btn btn-success" name="submit" id="submit" style="margin-top:21px;"> Submit</button>
								
                            </div>
					</form>
				</div>
			</div>
			
			<div class="hpanel">
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<table id="example5" class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>Branch Id</th>
							<th>user Name</th>
							<th>Serial Number</th>
							<th>Keyboard</th>
							<th>Display Size</th>
							<th>Processor</th>
							<th>RAM</th>
							<th>Mouse</th>
							<th>Headphone</th>
							<th>System Name</th>
							<th>Storage</th>
							<th>Edit</th>
							<th>Delete</th>
							
						</tr>
					</thead>
					<tbody>
						<?php
						
							$result=mysqli_query($con,"SELECT * FROM asset ORDER BY id DESC ");
							$numrow=mysqli_num_rows($result);
							if($numrow>0)
							{
									$i = 1;
								while($res = mysqli_fetch_array($result)){
									
										$id=$res['id'];
								
									echo "<tr>";
									echo "<td>$i</td>";
									echo "<td>".$res['branchId']."</td>";									
									echo "<td>".$res['userName']."</td>";				
									echo "<td>".$res['serialNumber']."</td>";				
									echo "<td>".$res['keyboard']."</td>";									
									echo "<td>".$res['displaySize']."</td>";						
									echo "<td>".$res['processor']."</td>";
									echo "<td>".$res['ram']."</td>";												
									echo "<td>".$res['mouse']."</td>";																					
									echo "<td>".$res['headphone']."</td>";	
									echo "<td>".$res['systemName']."</td>";	
									echo "<td>".$res['storage']."</td>";									
									echo "<td class='text-center'><a class='btn btn-success' title='EDIT' href='it_inventory_edit.php?id=".$res['id']."'><i class='fa fa-edit'></i> </a></td>";
									echo "<td class='text-center'><a class='btn btn-danger' title='DELETE' href='it_inventory.php?id=".$res['id']."'><i class='fa fa-trash'></i> </a></td>";									
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
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>
	

	<script>
		
		$('#inventory').DataTable({
			responsive: true,
			dom: 'Bfrtip',
			buttons: [
				{extend: 'csv',text: 'EXPORT TO EXCEL', title: 'Asset Report', className: 'btn-md btn-info'},
			]
		});
		

	</script>

<?php
include("db.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the employee record
    $sql = "DELETE FROM asset WHERE id=$id";

    if ($con->query($sql) === TRUE) {
		echo '<script>alert("Your Inventory has been deleted.");</script>';
        echo "<script>setTimeout(\"location.href = 'it_inventory.php';\", 150);</script>";
      
       
    } else {
        echo "<script>alert('Error!! Inventory Cannot Be Deleted')</script>";
		header("Location: it_inventory.php");	
    }
}
?>

