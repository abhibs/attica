<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    	include("header.php");
    	include("menuSundayUser.php");
	}
	else if($type == 'Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else if ($type == 'ZonalMaster') {
		include("header.php");
		include("menuzonalMaster.php");
	}
	else if ($type == 'ApprovalTeam') {
		include("header.php");
		include("menuapproval.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	$date = date("Y-m-d");
	$emp_id = $_SESSION['employeeId'];
?>
<style>
	#wrapper .panel-body{
		box-shadow: 10px 15px 15px #999;
		background-color: #f5f5f5;
		border-radius:3px;
		padding: 20px;
		border: none;
	}
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 18px;
		color:#123C69;
	}
	thead {
		text-transform: uppercase;
		font-size: 10px;
		background-color:#123C69;
	}
	thead tr{
		color: #f2f2f2;
	}
	.fa_Icon{
		color: #990000;
	}
	.text-success{
		font-weight:600;
		color: #123C69;
	}
	button {
		transform: none;
		box-shadow: none;
	}
	button:hover {
		background-color: gray;
		cursor: pointer;
	}
	.table-responsive .row{
		margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-12">
					<h3 class="text-success"> Bills - Zonal Verification </span></h3>
				</div>				
				<div style="clear:both"></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Branch</th>
									<th>Customer</th>
									<th>Type</th>
									<th>GrossW</th>
									<th>NetW</th>
									<th>GrossA</th>
									<th>NetA</th>
									<th>AmtPaid</th>
									<th>Margin</th>							
									<th>PaymentType</th>
									<th>Rate</th>
									<th>time</th>
									<th class="text-center">BIll</th>									
									<th class="text-center">Verify</th>
									<th class="text-center">Reject</th>
								</tr>
							</thead>
							<tbody>							
								<?php
								if($type == 'Zonal'){
									if(date('D') == 'Sun'){
										$sql = "SELECT b.branchName, t.id, t.name, t.phone, t.releases, t.grossW, t.netW, t.grossA, t.netA, t.amountPaid, t.time, t.type, t.comm, t.paymentType, t.cashA, t.impsA, t.metal,t.branchId,t.rate
										FROM trans t
										JOIN branch b ON t.branchId=b.branchId
										WHERE t.date='$date' AND t.status='Pending'";
									}
									else{
										// $state = "";
										// if($_SESSION['branchCode'] == "TN"){
										// 	$state = " AND b.state IN ('Tamilnadu', 'Pondicherry')";
										// }
										// elseif($_SESSION['branchCode'] == "KA"){
										// 	$state = " AND b.state IN ('Karnataka')";
										// }
										// elseif($_SESSION['branchCode'] == "AP-TS"){
										// 	$state = " AND b.state IN ('Andhra Pradesh', 'Telangana')";
										// }

										$sql = "SELECT b.branchName, t.id, t.name, t.phone, t.releases, t.grossW, t.netW, t.grossA, t.netA, t.amountPaid, t.time, t.type, t.comm, t.paymentType, 
               t.cashA, t.impsA, t.metal,t.branchId,t.rate
        FROM trans t
        JOIN branch b ON t.branchId = b.branchId
        WHERE t.date = '$date' 
          AND t.status = 'Pending' 
          AND b.ezviz_vc = '$emp_id'";
									}
									
								}
								else{
									$sql = "SELECT b.branchName, t.id, t.name, t.phone, t.releases, t.grossW, t.netW, t.grossA, t.netA, t.amountPaid, t.time, t.type, t.comm, t.paymentType, t.cashA, t.impsA, t.metal,t.branchId,t.rate
									FROM trans t
									JOIN branch b ON t.branchId=b.branchId
									WHERE t.date='$date' AND t.status='Pending'";
								}
								$query = mysqli_query($con, $sql);
								$i = 1;
								while($row = mysqli_fetch_assoc($query)){
									echo "<tr>";
									echo "<td>".$i."</td>";
									echo "<td>".$row['branchName']."</td>";
									echo "<td>".$row['name']."<br>".$row['phone']."</td>";								
									echo "<td>".$row['type']."</td>";
									echo "<td>".$row['grossW']."</td>";
									echo "<td>".$row['netW']."</td>";
									echo "<td>".$row['grossA']."</td>";
									echo "<td>".$row['netA']."</td>";
									echo "<td>".$row['amountPaid']."</td>";
									echo "<td>".$row['comm']."</td>";
									echo "<td>".$row['paymentType']."</td>";
									echo "<td>".$row['rate']."</td>";
									echo "<td>".$row['time']."</td>";
									echo "<td class='text-center'><a class='btn btn-primary btn-sm' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'> Bill</a></b></td>";
									//echo "<td class='text-center'><button class='btn btn-sm' style='background-color: #238636; color: #ffffff' data-transid='".$row['id']."' data-branchid='".$row['branchId']."' onclick='verifyBill(this)'>Verify</button></td>";
									$netA    = (float)$row['netA'];
									$amtPaid = (float)$row['amountPaid'];
									$diff    = abs($netA - $amtPaid);
									$isPhysicalGold = (strcasecmp(trim((string)$row['type']), 'Physical Gold') === 0);
									$shouldDisable  = $isPhysicalGold && ($diff > 0.01);
									$disabledAttr = $shouldDisable ? 'disabled' : '';
									$btnStyle = $shouldDisable ? "background-color:#a0a0a0;color:#ffffff;cursor:not-allowed;" : "background-color:#238636;color:#ffffff;";
									$title = $shouldDisable ? "NetA and AmtPaid must match to verify (Physical Gold)" : "";
									echo "<td class='text-center'> <button class='btn btn-sm' style='{$btnStyle}' data-transid='{$row['id']}' data-branchid='{$row['branchId']}'  title='{$title}' onclick='verifyBill(this)' {$disabledAttr}>Verify</button> </td>";

									echo "<td class='text-center'><button class='btn btn-sm' style='background-color: #cb1212; color: #ffffff' data-transid='".$row['id']."' data-branchid='".$row['branchId']."' onclick='rejectBill(this)'>Reject</button></td>";
									echo "</tr>";
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
	<?php include("footer.php"); ?>
	<script>	
		async function verifyBill(btn){ 
			const id = btn.dataset.transid;
			const branchId = btn.dataset.branchid;
			const parentRow = btn.parentElement.parentElement;

			btn.disabled = true;
			btn.textContent = "Verifying...";

			const form = new FormData();
			form.append("id", id);
			form.append("branchId", branchId);
			form.append("zonalBillVerification", true);

			const response = await fetch("editAjax.php", {
				method: "POST",
				body: form
			});
			const result = await response.json();
			if(result.message == "success"){
				parentRow.style.backgroundColor = "#b3c1e4";
				setTimeout(()=>{
					parentRow.remove();
				}, 2000);
			}
			else{
				console.log(result);
				btn.removeAttribute("disabled");
				alert("Something went wrong, Please try again later");
				return;
			}
		}

		async function rejectBill(btn){ 
			const confirmAgain = confirm("Are you sure you want to REJECT the bill?");
			if(confirmAgain){
				const id = btn.dataset.transid;
				const branchId = btn.dataset.branchid;
				const parentRow = btn.parentElement.parentElement;

				btn.disabled = true;
				btn.textContent = "Rejecting...";

				const form = new FormData();
				form.append("id", id);
				form.append("branchId", branchId);
				form.append("zonalBillReject", true);

				const response = await fetch("editAjax.php", {
					method: "POST",
					body: form
				});
				const result = await response.json();
				if(result.message == "success"){
					parentRow.style.backgroundColor = "#fac8c8";
					setTimeout(()=>{
						parentRow.remove();
					}, 2000);
				}
				else{
					console.log(result);
					btn.removeAttribute("disabled");
					alert("Something went wrong, Please try again later");
					return;
				}
			}		
		}
		
	</script>

