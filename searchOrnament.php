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
	
	$billId=$_GET['billId'];
	$billDate = $_GET['date'];
	$bill_date= date("d-m-Y", strtotime($billDate));
	$ornamentQuery = mysqli_query($con,"SELECT * FROM ornament where billId='$billId' and date='$billDate'");

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
										<i class="fa-Icon fa fa-exchange" aria-hidden="true"></i> ORNAMENT DETAILS 
									</h3>
								</div>	
                                <div class="col-lg-3">									
									<select class="form-control" id="ornamentList" style="height:40px">
										<option selected="true" disabled="disabled" value="">Ornament List</option>
										<!--<option Value="22 carot Biscuit (91.6">22 carot Biscuit (91.6)</option>
										<option Value="24 carot Biscuit(99.9)">24 carot Biscuit (99.9)</option>
										<option Value="22 carot Coin (91.6">22 carot Coin (91.6)</option>
										<option Value="24 carot Coin (99.9)">24 carot Coin (99.9)</option>
										<option Value="Anklets">Anklets</option>
										<option Value="Armlets">Armlets</option>
										<option Value="Baby Bangles">Baby Bangles</option>
										<option Value="Bangles">Bangles</option>
										<option Value="Bracelet">Bracelet</option>
										<option Value="Broad Bangles">Broad Bangles</option>
										<option Value="Chain">Chain</option>
										<option Value="Chain with Locket">Chain with Locket</option>
										<option Value="Chain with Black Beads">Chain with Black Beads</option>
										<option Value="Drops">Drops</option>
										<option Value="Ear Rings">Ear Rings</option>
										<option Value="Gold Bar">Gold Bar</option>
										
										<option Value="Head Locket">Head Locket</option>
										<option Value="Locket">Locket</option>
										<option Value="Matti">Matti</option>
										<option Value="Necklace">Necklace</option>
										<option Value="Ring">Ring</option>
										<option Value="Silver Bar">Silver Bar</option>
										<option Value="Silver Items">Silver Items</option>
										<option Value="Small Gold Piece">Small Gold Piece</option>
										<option Value="Studs">Studs</option>
										<option Value="Studs And Drops">Studs And Drops</option>
										<option Value="Thala/Mangalya Chain">Thali Chain</option>
										<option Value="Thala/Mangalya Chain with Black Beads">Thali Chain with Black Beads</option>
										<option Value="Waist Belt/Chain">Waist Belt/Chain</option>
										<option Value="Others">Others</option>-->
										<!-- Gold ornaments -->
										<option value="24K Coin/Bar">24K Coin/Bar</option>
										<option value="22K Coin/Bar">22K Coin/Bar</option>
										<option value="Anklet">Anklet</option>
										<option value="Drops/Studs">Drops/Studs</option>
										<option value="Ring">Ring</option>
										<option value="Baby Bangles">Baby Bangles</option>
										<option value="Bangles">Bangles</option>
										<option value="Chain">Chain</option>
										<option value="Chain with Locket">Chain with Locket</option>
										<option value="Bracelet">Bracelet</option>
										<option value="Thala/Mangalya Chain with Black Beads">Thali Chain with Black Beads</option>
										<option value="Necklace">Necklace</option>
										<option value="Matti">Matti</option>
										<option value="Waist Belt/Chain">Waist Belt/Chain</option>
										<option value="Locket">Locket</option>
								
										<!-- Silver ornaments -->
										<option value="Bar/Coin">Bar/Coin</option>
										<option value="Ornaments">Ornaments</option>
										<option value="Others">Others</option>
									</select>
								</div>							
								<div class="col-lg-1">						
									<a style="float:right;padding-right:10px" href="searchTrans.php" class="btn btn-primary">
										<i class="fa_Icon fa fa-backward"></i> BACK
									</a>								
								</div>


							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="container1">
							<div class="col-lg-12">
								<h3 class="text-success"><i class="fa_Icon fa fa-eye"></i> VIEW ORNAMENT LIST OF BILL ID : <span class="branch_name"><?php echo $billId." (".$bill_date.")"; ?></span></h3>
								<table id="ornament-datatable" class="table table-striped table-bordered">
									<thead class="theadRow">
										<tr>
											<th>Sl.No.</th>
											<th>METAL</th>
											<th>ORNAMENT</th>
											<th>NO OF PIECES</th>
											<th>WEIGHT</th>	
											<th>S WASTE</th>	
											<th>READING</th>	
											<th>PURITY</th>	
											<th>GROSS</th>	
											<th class="text-center">ACTION</th>									
										</tr>
									</thead>
									
									<tbody id="ornamentDetail-Response">	
									<?php
										$i = 1;
										while($row = mysqli_fetch_assoc($ornamentQuery)){
									?>									
										<tr id="row_<?php echo $i;?>">										
										<td><?php echo $i;?></td>
										<td><?php echo $row["metal"];?><p><?php echo $row["rate"];?></p></td>
										<td><input type="text" class="form-control" id="type_<?php echo $row['ornamentId'];?>" name="type" value="<?php echo $row["type"];?>"></td>
										<td><input type="text" class="form-control" id="pieces_<?php echo $row['ornamentId'];?>" name="pieces" value="<?php echo $row["pieces"];?>"></td>
										<td><input type="text" class="form-control" id="weight_<?php echo $row['ornamentId'];?>" name="weight" value="<?php echo $row["weight"];?>"></td>
										<td><input type="text" class="form-control" id="sWaste_<?php echo $row['ornamentId'];?>" name="sWaste" value="<?php echo $row["sWaste"];?>"></td>
										<td><input type="text" class="form-control" id="reading_<?php echo $row['ornamentId'];?>" name="reading" value="<?php echo $row["reading"];?>"></td>
										<td><input type="text" class="form-control" id="purity_<?php echo $row['ornamentId'];?>" name="purity" value="<?php echo $row["purity"];?>"></td>
										<td><input type="text" class="form-control" id="gross_<?php echo $row['ornamentId'];?>" name="gross" value="<?php echo $row["gross"];?>"></td>
										<td style='text-align:center'><button class='btn btn-success'  title='Update Ornament Data' onclick='update_ornament(<?php echo $row['ornamentId'];?>,<?php echo $row['billId'];?>)'><i class='fa fa-edit' aria-hidden='true'></i> </button></td>									
										</tr>
										
									<?php
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
		</div>		
	</div>
	<div style="clear:both"></div>	
	<?php include("footer.php"); ?>
</div>

<script type="text/javascript">

    document.getElementById('ornamentList').addEventListener('change',(e)=>{
		let optionSelected = document.getElementById('ornamentList').value;		
		let inp =document.createElement('input');
		document.body.appendChild(inp);
		inp.value = optionSelected;
		inp.select();
		document.execCommand('copy',false);
		inp.remove();
	});

	$('#ornament-datatable').DataTable({
		responsive: true
	});	
	
	// UPDATE BILL TRANSACTION
	function update_ornament(ornamentId,billId){
		
		var type=$("#type_"+ornamentId).val();
		var pieces=$("#pieces_"+ornamentId).val();
		var weight=$("#weight_"+ornamentId).val();
		var sWaste=$("#sWaste_"+ornamentId).val();
		var reading=$("#reading_"+ornamentId).val();
		var purity=$("#purity_"+ornamentId).val();
		var gross=$("#gross_"+ornamentId).val();
		
 		$.ajax({
			url: "searchBillAjax.php",
			type: "post",
			data: {update_ornament: "update_ornament",ornamentId:ornamentId,billId:billId,type:type,pieces:pieces,weight:weight,sWaste:sWaste,reading:reading,purity:purity,gross:gross},
			success: function(response){
 				if(response=="SUCCESS"){
					alert("The ornament data has been updated successfully");
					location.reload();
				}else{
					alert("Oops!! Error in updating data");
					location.reload();
				} 
			}
		});
		
	}

</script>
