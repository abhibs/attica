<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Branch'){ 
		include("header.php");
		include("menu.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date=date("Y-m-d");
	
	if (isset($_SESSION['customerID']) && isset($_SESSION['mobile'])){
		$cusDetail = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,mobile FROM customer WHERE mobile='$_SESSION[mobile]' LIMIT 1"));
		
		/* GOLD PRICE */
		$goldPrice =  mysqli_fetch_assoc(mysqli_query($con, "SELECT cash 
		FROM gold 
		WHERE date='$date' AND type='Gold' AND city = (
		SELECT 
		(CASE
		WHEN priceId=1 THEN 'Bangalore'
		WHEN priceId=2 THEN 'Karnataka'
		WHEN priceId=3 THEN 'Andhra Pradesh'
		WHEN priceId=4 THEN 'Telangana'
		WHEN priceId=5 THEN 'Chennai'
		WHEN priceId=6 THEN 'Tamilnadu'
		END) AS city
		FROM branch
		WHERE branchId='$_SESSION[branchCode]'
		)
		ORDER BY id DESC
		LIMIT 1"));
	}
	else{
		include("logout.php");
	}
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color: #123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	border: none;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	}
	.fa_Icon{
	color:#990000;
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
	button {
	transform: none;
	box-shadow: none;
	}
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	.trans_Icon{
	color:#ffa500;
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
</style>


<!--   PLEDGE PLACE NAME ( NBFC / BANK )   -->
<div id="nbfc_name_clone" style="display: none">
	<label class="text-success">NBFC Name </label>	
	<select class="form-control m-b" name="pledgeName" required>
		<option selected="true" disabled="disabled" value="">NBFC Name</option>
		<option value="Manappuram">Manappuram Finance Limited</option>
		<option value="MuthoottuMini">Muthoottu Mini </option>
		<option value="MuthootRed">Muthoot Red </option>
		<option value="MuthootBlue">Muthoot Blue </option>
		<option value="FedBank">Fed Bank</option>
		<option value="IIFL">IIFL</option>
		<option value="PawnBroker">Pawn Broker</option>
		<option value="others">Others</option>
	</select>	
</div>
<div id="bank_name_clone" style="display: none">
	<label class="text-success"> Bank</label>
	<input type="text" name="pledgeName" placeholder="Bank Name" class="form-control" autocomplete="off" required>
</div>


<!--   IMPS DETAILS / PLEDGE SLIPS COUNT   -->
<div class="hpanel" style="display: none" id="imps_details_clone">
	<div class="panel-heading">
		<i style="color:#990000" class="fa fa-asterisk"></i> IMPS DETAILS
	</div>
	<div class="panel-body">
		<div class="form-group row">
			<div class="col-sm-3">
				<label class="text-success">Bank Name </label>
				<input type="text" name="bankname" placeholder="Bank Name" class="form-control" autocomplete="off" required>
			</div>
			<div class="col-sm-3">
				<label class="text-success">Bank Branch </label>
				<input type="text" name="branchname" placeholder="Branch Name"  class="form-control" autocomplete="off" required>
			</div>
			<div class="col-sm-3">
				<label class="text-success">Account Holder Name </label>
				<input type="text" name="accountHolder" placeholder="Account Holder Name" class="form-control" autocomplete="off" required>
			</div>
			<div class="col-sm-3">
				<label class="text-success">Relationship </label>
				<select class="form-control m-b" name="relationship" required>
					<option selected="true" disabled="disabled" value="">Relationship</option>
					<option value="myself">Myself</option>
					<option value="Father">Father</option>
					<option value="Mother">Mother</option>
					<option value="Husband">Husband</option>
					<option value="Wife">Wife</option>
					<option value="Son">Son</option>
					<option value="Daughter">Daughter</option>
					<option value="others">Others</option>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-3">
				<label class="text-success">Loan Account Number </label>
				<input type="text" name="loan_account_number" placeholder="Loan Account Number" class="form-control" autocomplete="off" required>
			</div>
			<div class="col-sm-3">
				<label class="text-success">Account Number </label>
				<input type="text" name="accountnumber" placeholder="Account Number" class="form-control" autocomplete="off" required>
			</div>
			<div class="col-sm-3">
				<label class="text-success">IFSC Number </label>
				<input type="text" name="ifsc" placeholder="IFSC Number" class="form-control" autocomplete="off" required>
			</div>
		</div>
	</div>
</div>
<div class="hpanel" style="display: none" id="pledge_details_clone">
	<div class="panel-heading">
		<i style="color:#990000" class="fa fa-asterisk"></i> PLEDGE SLIP COUNT
	</div>
	<div class="panel-body">
		<div class="form-group row">
			<div class="col-sm-3">
				<label class="text-success">No of Pledge Slips</label>
				<input type="text" name="pledge_number" placeholder="No of Pledge Slips" class="form-control" autocomplete="off" required> 
			</div>
		</div>
	</div>
</div>


<!--   CASH & CASH/IMPS AMOUNT    -->
<div id="both_cash_amount_clone" style="display: none">
	<div class="col-sm-3">
		<label class="text-success"> Cash Amount</label>
		<input type="text" name="cash" placeholder="Cash Amount" class="form-control" autocomplete="off" required>
	</div>
</div>

<div id="bank_imps_amount_clone" style="display: none">
	<div class="col-sm-3">
		<label class="text-success"> Cash Amount</label>
		<input type="text" name="cash" placeholder="Cash Amount" class="form-control" autocomplete="off" required>
	</div>
	<div class="col-sm-3">
		<label class="text-success"> IMPS Amount</label>
		<input type="text" name="imps" placeholder="IMPS Amount" class="form-control" autocomplete="off" required>
	</div>
</div>

<div id="nbfc_imps_amount_clone" style="display: none">
	<div class="col-sm-3">
		<label class="text-success"> Cash Amount</label>
		<input type="text" name="cash" placeholder="Cash Amount" class="form-control" autocomplete="off" required>
	</div>
	<div class="col-sm-3">
		<label class="text-success"> IMPS Amount</label>
		<input type="text" name="imps" placeholder="IMPS Amount" class="form-control" autocomplete="off" required>
	</div>
	<div class="col-sm-3">
		<label class="text-success">IMPS transfer to </label>
		<select class="form-control m-b" name="releasewith" required>
			<option selected="true" disabled="disabled" value=''>Type of Release With</option>
			<option value="NBFC">To NBFC</option>
			<option value="BANK">To Customer's bank</option>
		</select>
	</div>
</div>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<input type="hidden" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>" >
					<input type="hidden" id='branch_goldPrice' value="<?php echo $goldPrice['cash']; ?>" >
					<div class="pull-right">
						<a class="btn btn-default a-extra">Available Amount | <span class="fa fa-rupee fa_Icon"></span><i id="available"></i></a>
					</div>
					<h3><i style="color:#990000" class="fa fa-shield"></i> FILL GOLD RELEASE INFO</h3>
				</div>
			</div>
		</div>		
		
		<form method="POST" class="form-horizontal" action="xaddReleaseInfo.php">
			
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<i style="color:#990000" class="fa fa-asterisk"></i> RELEASE INFORMATION
					</div>
					<div class="panel-body">
						<div class="form-group row">
							<div class="col-sm-3">
								<label class="text-success">Customer Name</label>
								<input type="text" name="name" readonly class="form-control" value="<?php echo $cusDetail['name']; ?>">
							</div>
							<div class="col-sm-3">
								<label class="text-success">Customer Contact</label>
								<input type="text" name="contact" readonly class="form-control" value="<?php echo $cusDetail['mobile']; ?>">
							</div>
							<div class="col-sm-3">
								<label class="text-success">Pledge Place</label>
								<select class="form-control m-b" name="pPlace" id="pPlace" required>
									<option selected="true" disabled="disabled" value=""> Select Place</option>
									<option value="NBFC"> NBFC</option>
									<option value="BANK"> BANK</option>
								</select>
							</div>
							<div class="col-sm-3" id="pledge_place_name_div"></div>
						</div>
						<div class="form-group row">
							<div class="col-sm-3">
								<label class="text-success">Type of Release Collection</label>
								<select required class="form-control m-b" name="releasetype" id="releasetype" required>
									<option selected="true" disabled="disabled" value="">Select Type of Release</option>
									<option value="Cash">Cash</option>
									<option value="CASH/IMPS">Cash And IMPS</option>
								</select>
							</div>
							<div id="release_type_div"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-lg-12" id="imps_details_div"></div>
			
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<i style="color:#990000" class="fa fa-asterisk"></i> OTHER DETAILS
					</div>
					<div class="panel-body no-padding">
						<div class="list-group ">
							<a class="list-group-item">
								<h5 class="list-group-item-heading">Calculate Purity</h5>
								<div class="form-group row" style="padding: 10px;">
									<div class="col-sm-3">
										<label class="text-success">Release Gross Weight </label>
										<input type="text" name="relGrossW" placeholder="Gross Weight" required id="rgw" class="form-control" autocomplete="off">
									</div>
									<div class="col-sm-3">
										<label class="text-success">Release Net Weight </label>
										<input type="text" name="relNetW" placeholder="Net Weight" required id="rnw" class="form-control" autocomplete="off">
									</div>
									<div class="col-sm-1">
										<button class="btn btn-primary btn-block btn-sm" type="button" id="crp" style="margin-top: 25px;">
											<span style="color:#ffcf40" class="fa fa-check"></span> Purity
										</button>
									</div>
									<div class="col-sm-2">
										<label class="text-success">Release Purity (%) </label>
										<input type="text" name="relPurity" placeholder="Purity(%)" required id="rpp" class="form-control" readonly style="text-align:center;">
									</div>
									<div class="col-sm-2" style="margin-top: 25px;">
										<div class="checkbox checkbox-primary" id="zonalCheck">
											<input type="checkbox">
											<label for="checkbox2" style="color: #990000; font-weight: 600">
												Spoken to Zonal
											</label>
										</div>
									</div>
								</div>
							</a>
							<a class="list-group-item">
								<h5 class="list-group-item-heading">TE Information</h5>
								<div class="form-group row" style="padding: 10px;">
									<div class="col-sm-3">
										<select required class="form-control m-b" name="TEId"  required>
											<option selected="true" disabled="disabled" value="">Select Employee ID</option>
											<?php 
												$teSQL = mysqli_query($con, "SELECT DISTINCT empId FROM employee WHERE designation NOT IN ('VM', 'ZONAL') ORDER BY empId");
												while($row = mysqli_fetch_assoc($teSQL)){
													echo "<option value=".$row['empId'].">".$row['empId']."</option>";
												}
											?>
										</select>
									</div>
								</div>
							</a>
							<a class="list-group-item text-right">
								<button class="btn btn-primary" name="submitRel" type="submit" id="submitRel">
									<span style="color:#ffcf40" class="fa fa-save"></span> SUBMIT RELEASE DATA
								</button>
							</a>
						</div>
					</div>
				</div>
			</div>
			
		</form>
		
	</div>	
	<?php include("footer.php"); ?>
	<script>
		$(document).ready(function() {
			
			// CLONES
			const nbfc_name_clone = document.getElementById("nbfc_name_clone");
			const bank_name_clone = document.getElementById("bank_name_clone");
			
			const both_cash_amount_clone = document.getElementById("both_cash_amount_clone"); 
			const bank_imps_amount_clone = document.getElementById("bank_imps_amount_clone"); 
			const nbfc_imps_amount_clone = document.getElementById("nbfc_imps_amount_clone"); 
			
			const imps_details_clone = document.getElementById("imps_details_clone");
			const pledge_details_clone = document.getElementById("pledge_details_clone");
			
			
			// PLEDGE PLACE TYPE 
			const pledge_place = document.getElementById("pPlace");
			const pledge_place_attach_parent = document.getElementById("pledge_place_name_div");
			
			
			// CASH TYPE
			const release_type = document.getElementById("releasetype");
			const release_type_attach_parent = document.getElementById("release_type_div");
			
			
			// IMPS TYPE 
			const imps_detail_attach_parent = document.getElementById("imps_details_div");
			
			// -------------------------------------------------------------------------------------------------------------- //
			
			// PLEDGE PLACE TYPE CHANGE
			pledge_place.addEventListener("change", (event)=>{
				let type = pledge_place.value;
				let twin;
				if(type == "NBFC"){
					twin = nbfc_name_clone.cloneNode(true);
				}
				else if(type == "BANK"){
					twin = bank_name_clone.cloneNode(true);
				}
				twin.removeAttribute("style");
				pledge_place_attach_parent.innerHTML = "";
				pledge_place_attach_parent.appendChild(twin);
				
				release_type_attach_parent.innerHTML = "";
				release_type.value = "";
				
				imps_detail_attach_parent.innerHTML = "";
			});
			
			// CASH TYPE CHANGE
			release_type.addEventListener("change", (event)=>{
				imps_detail_attach_parent.innerHTML = "";
				
				let type = pledge_place.value;
				let transfer_type = release_type.value;
				let twin;
				if(type == ""){
					release_type.value = "";
					alert("Please Select the Pledged Place !!!");
				}
				else{
					if(transfer_type == "Cash"){
						twin = both_cash_amount_clone.cloneNode(true);
					}
					else{
						if(type == "NBFC"){
							twin = nbfc_imps_amount_clone.cloneNode(true);
							
							const selector = twin.querySelector("select");
							selector.addEventListener("change", (event)=>{
								imps_detail_attach_parent.innerHTML = "";
								let val = selector.value;
								let val_twin;
								if(val == "NBFC"){
									val_twin = pledge_details_clone.cloneNode(true);
								}
								else{
									val_twin = imps_details_clone.cloneNode(true);
								}
								val_twin.removeAttribute("style");
								imps_detail_attach_parent.appendChild(val_twin);
							});
						}
						else if(type == "BANK"){
							twin = bank_imps_amount_clone.cloneNode(true);
							
							let imps_twin = imps_details_clone.cloneNode(true);
							imps_twin.removeAttribute("style");
							imps_detail_attach_parent.appendChild(imps_twin);
						}
					}
					twin.removeAttribute("style");
					release_type_attach_parent.innerHTML = "";
					release_type_attach_parent.appendChild(twin);
				}
			});		
		});
	</script>
	<script>
		$(document).ready(function(){
			
			const submit_button = document.getElementById("submitRel");
			submit_button.disabled = true;
			
			const price = document.getElementById('branch_goldPrice').value;
			const netWeight = document.getElementById("rnw");
			const purity_button = document.getElementById("crp");
			const purity_input = document.getElementById("rpp");
			const zonalCheck = document.getElementById("zonalCheck");
			const release_type_select = document.getElementById("releasetype");
			
			zonalCheck.style.display = "none";
			
			purity_button.addEventListener("click", (event)=>{
				let nw = netWeight.value;
				
				if(nw != "" && release_type_select.value != ""){
					let cash = "";
					let imps = "";
					
					if(release_type_select.value == "CASH/IMPS"){
						cash = document.querySelector("#release_type_div").querySelector("input[name=cash]").value;
						imps = document.querySelector("#release_type_div").querySelector("input[name=imps]").value;
						
						if(cash == "" || imps == ""){
							alert("Please fill Both Cash & IMPS Amount");
						}
						else{
							let purity = ( ( (+cash + +imps) / +nw ) / price ) * 100;
							purity_input.value = purity.toFixed(2);
							setColor(purity);
						}
					}
					else if(release_type_select.value == "Cash"){
						cash = document.querySelector("#release_type_div").querySelector("input[name=cash]").value;
						
						if(cash == ""){
							alert("Please fill Both Cash & IMPS Amount");
						}
						else{
							let purity = ( ( (cash) / nw ) / price ) * 100;
							purity_input.value = purity.toFixed(2);
							setColor(purity);
						}
					}
				}
				else{
					alert("Please fill Amount, Gross Weight & Net Weight");
				}
				
			});
			
			function setColor(purity){
				if (purity <= 70) {
					purity_input.style.backgroundColor = "#62cb31";
					submit_button.removeAttribute("disabled");
					zonalCheck.style.display = "none";
				} 
				else if (purity > 70 && purity <= 80) {
					purity_input.style.backgroundColor = "#ffb606";
					submit_button.removeAttribute("disabled");
					zonalCheck.style.display = "none";
				} 
				else if (purity > 80 && purity <= 85) {
					purity_input.style.backgroundColor = "#e67e22";
					submit_button.removeAttribute("disabled");
					zonalCheck.style.display = "none";
				} 
				else {
					purity_input.style.backgroundColor = "#c0392b";
					zonalCheck.style.display = "inline";
				}
			}
			
			zonalCheck.querySelector('input').addEventListener("change", (event)=>{
				if (event.currentTarget.checked) {
					submit_button.removeAttribute("disabled");
				} 
				else {
					submit_button.disabled = true;
				}
			});
			
		});
	</script>
	<script>
		$(document).ready(function(){
			var branch  = $("#session_branchID").val();
			var req = $.ajax({
				url:"xbalance.php",
				type:"POST",
				data:{branchId:branch},
				dataType:'JSON'
			});
			req.done(function(e){
				var available = e.balance;
				$("#available").text(available);
			});
		});
	</script>								
