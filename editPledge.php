<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$current_date=date('Y-m-d');

	$invoiceId=$_GET['invoiceId'];
	$row = mysqli_fetch_assoc(mysqli_query($con,"SELECT  id,billId,invoiceId,name,contact,grossW,stoneW,amount,rate,rateAmount,status FROM pledge_bill WHERE invoiceId='$invoiceId'"));	
	
	

	$invoiceDate = $_GET['date'];
	$invoice_date = date("d-m-Y", strtotime($invoiceDate));
	
	$ornamentQuery = mysqli_query($con, "SELECT * FROM pledge_ornament WHERE invoiceId='$invoiceId' ");
?>

<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 16px;
	color:#123C69;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
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
	tbody{
	font-weight: 600;
	}
	.trInput:focus-within{
	outline: 3px solid #990000;
	}
	.fa{
	color:#34495e;
	font-size:16px;
	}
	.btn{
	background-color:transparent;
	}
	.fa-disabled{
		color: #990000;
	}
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
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><span style="color:#123C69;" class="fa_Icon fa fa-pencil-square"></span><b> PLEDGE BILL DETAILS</b>  &nbsp;&nbsp; &nbsp; BILL ID:<a target='_blank' class='btn btn-success btn-md' title='View Bill' href='InvoicePledge.php?id=<?php echo base64_encode($row['id']);?>'> <?php echo $row["billId"]?></a></h3>
					
				</div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					
					<table id="user" class="table table-bordered" style="clear: both">
						<tbody>
						<input type="hidden" name="id" id="pledgeId"  value="<?php echo $row['id']; ?>">
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">BILL ID</th>
								<td width="65%">
									<input type="text" name="billId" class="form-control" value="<?php echo $row['billId']; ?>" readonly>
								</td>
							</tr>
							<tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">INVOICE ID</th>
								<td width="65%">
									<input type="text" name="invoiceId" id="pledgeId" class="form-control" value="<?php echo $row['invoiceId']; ?>" readonly>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">NAME</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr></tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">MOBILE</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="contact" class="form-control" value="<?php echo $row['contact']; ?>" autocomplete="off" readonly>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)" disabled><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>							
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">GROSS WT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="grossW" class="form-control" value="<?php echo $row['grossW']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)" disabled><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">NET WT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="" class="form-control" value="<?php echo ($row['grossW']-$row['stoneW']); ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">AMOUNT PAID</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="amount" class="form-control" value="<?php echo $row['amount']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">INTEREST</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="rate" class="form-control" value="<?php echo $row['rate']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">INTEREST AMOUNT</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="rateAmount" class="form-control" value="<?php echo $row['rateAmount']; ?>" autocomplete="off"required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush fa-disabled"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">STATUS</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="status" class="form-control" value="<?php echo $row['status']; ?>" autocomplete="off" required>
										<span class="input-group-btn"> 
											<button type="submit" class="btn" onclick="updatePledgeData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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
										<option Value="22 carot Biscuit (91.6">22 carot Biscuit (91.6)</option>
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
										<option Value="Others">Others</option>
									</select>
								</div>	
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="container1">
                            <div class="col-lg-12">
                                <h3 class="text-success"><i class="fa_Icon fa fa-eye"></i> VIEW ORNAMENT LIST OF INVOICE ID : <span class="branch_name"><?php echo $invoiceId ; ?></span></h3>
                                <table id="ornament-datatable" class="table table-striped table-bordered">
                                    <thead class="theadRow">
                                        <tr>
                                            <th>Sl.No.</th>
											<th>INVOICE NUMBER</th>
                                            <th>ORNAMENT TYPE</th>
                                            <th>NO OF PIECES</th>
                                            <th>GROSS WEIGHT</th>
                                            <th>NET WEIGHT</th>
                                            <th>PURITY</th>
                                            <th>AMOUNT</th>
                                            <th class="text-center">ACTION</th>
											<th class="text-center">DELETE</th>

                                        </tr>
                                    </thead>
                                    <tbody id="ornamentDetail-Response">
                                    <?php
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($ornamentQuery)) {
                                        ?>
                                        <tr id="row_<?php echo $i; ?>">
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $row['invoiceId'];?> </td>
                                            <td><input type="text" class="form-control" id="ornamentType_<?php echo $row['id']; ?>" name="ornamentType" value="<?php echo $row["ornamentType"]; ?>"></td>
                                            <td><input type="text" class="form-control" id="count_<?php echo $row['id']; ?>" name="pieces" value="<?php echo $row["count"]; ?>"></td>
                                            <td><input type="text" class="form-control" id="grossW_<?php echo $row['id']; ?>" name="grossW" value="<?php echo $row["grossW"]; ?>"></td>
                                            <td><input type="text" class="form-control" id="stoneW_<?php echo $row['id']; ?>" name="stoneW" value="<?php echo $row["stoneW"]; ?>"></td>
                                            <td><input type="text" class="form-control" id="purity_<?php echo $row['id']; ?>" name="purity" value="<?php echo $row["purity"]; ?>"></td>
                                            <td><input type="text" class="form-control" id="amount_<?php echo $row['id']; ?>" name="amount" value="<?php echo $row["amount"]; ?>"></td>
                                            <td style='text-align:center'><button class='btn btn-success' title='Update Ornament Data' onclick='update_pledge_ornament(<?php echo $row['id']; ?>, "<?php echo $row['invoiceId']; ?>")'><i class='fa fa-edit' style="color:white;" aria-hidden='true'></i></button></td>
											<td style='text-align:center'>
												<button type="button" class='btn btn-danger' style="background-color:red;" title='Delete Ornament Data' onclick='delete_pledge_ornament(<?php echo $row['id']; ?>, "<?php echo $row['invoiceId']; ?>")'>
													<i class='fa fa-trash' style="color:white;" aria-hidden='true'></i>
												</button>
											</td>
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
	<div style="clear:both"></div>
	 <script>	
		let pledgeId = document.getElementById('pledgeId').value;
		function updatePledgeData(button){
			if(pledgeId !== ''){
				let colValue = button.parentNode.previousElementSibling.value,
				colName = button.parentNode.previousElementSibling.name;
				$.ajax({
					url:"editAjax.php",
					type:"POST",
					data:{editPledge:'editPledge',pledgeId:pledgeId,colName:colName,colValue:colValue},
					success: function(e){
						if(e == '1'){
							alert('Successfully Updated');
						}
						else{
							alert('Oops!!! Something went wrong');
						}
					}
				});
			}		
		}

</script>
<script type="text/javascript">
    document.getElementById('ornamentList').addEventListener('change', (e) => {
        let optionSelected = document.getElementById('ornamentList').value;
        let inp = document.createElement('input');
        document.body.appendChild(inp);
        inp.value = optionSelected;
        inp.select();
        document.execCommand('copy', false);
        inp.remove();
    });

    $(document).ready(function() {
        $('#ornament-datatable').DataTable({
            responsive: true
        });
    });

    // UPDATE QUERY
    function update_pledge_ornament(id, invoiceId) {
        var ornamentType = $("#ornamentType_" + id).val();
        var count = $("#count_" + id).val();
        var grossW = $("#grossW_" + id).val();
        var stoneW = $("#stoneW_" + id).val();
        var purity = $("#purity_" + id).val();
        var amount = $("#amount_" + id).val();

        $.ajax({
            url: "searchBillAjax.php",
            type: "post",
            data: {
                update_pledge_ornament: "update_pledge_ornament",
                id: id,
                invoiceId: invoiceId,
                ornamentType: ornamentType,
                count: count,
                grossW: grossW,
                stoneW: stoneW,
                purity: purity,
                amount: amount
            },
            success: function(response) {
                if (response === "SUCCESS") {
                    alert("The ornament data has been updated successfully");
                    location.reload();
                } else {
                    alert("Oops!! Error in updating data");
                }
            },
            error: function() {
                alert("AJAX request failed");
            }
        });
    }

	// DELETE QUERY
	function delete_pledge_ornament(id, invoiceId) {
        if (confirm("Are you sure you want to delete this ornament?")) {
            $.ajax({
                url: "searchBillAjax.php",
                type: "post",
                data: {
                    deletePledgeOrnament: "deletePledgeOrnament",
                    id: id,
                    invoiceId: invoiceId
                },
                success: function(response) {
                    if (response === "SUCCESS") {
                        alert("The ornament data has been deleted successfully");
                        location.reload();
                    } else {
                        alert("Oops!! Error in deleting data");
                    }
                },
                error: function() {
                    alert("AJAX request failed");
                }
            });
        }
    }
</script>
<?php include("footer.php");?>





