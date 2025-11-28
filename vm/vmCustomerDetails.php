<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type == 'Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type == 'SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
		include("header.php");
		include("menuzonalMaster.php");
	}
	else if($type == 'AccHead'){
		include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type == 'Accounts IMPS'){
		include("header.php");
		include("menuimpsAcc.php");
	}
	else if($type == 'Expense Team'){
		include("header.php");
		include("menuexpense.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$ornamentsJSON = file_get_contents('../'.dirname(__FILE__).'/Config/ornament.json');
	$ornamentType = json_decode($ornamentsJSON, true);
	
	if(isset($_GET['ids']) && $_GET['ids']!=''){
		$id = $_GET['ids'];
		$contact = $_GET['id'];	
		
		$customerData = mysqli_fetch_assoc(mysqli_query($con, "SELECT *
		FROM customer
		WHERE mobile='$contact'
		LIMIT 1"));
		
		$billData = mysqli_fetch_assoc(mysqli_query($con, "SELECT id, billId, date, phone, grossW, grossA, type, ple, releaseID, relDate, status, paymentType, amountPaid, branchId, impsA, time
		FROM trans
		WHERE id='$id' AND phone='$contact'"));
		
		$extraData = mysqli_query($con, "SELECT extra, idnumber
		FROM everycustomer
		WHERE date='$billData[date]' AND contact='$contact' AND extra!=''");
		
		$ornamentData = mysqli_query($con, "SELECT ornamentId,type,pieces,weight,gross
		FROM ornament
		WHERE date='$billData[date]' AND billId='$billData[billId]'");
		
		
		if($billData['type'] == "Release Gold"){
			$releaseData = mysqli_fetch_assoc(mysqli_query($con,"SELECT rid,relDoc1,relDoc2,date,amount 
			FROM releasedata
			WHERE phone ='$contact' AND releaseID='$billData[releaseID]' AND date='$billData[relDate]'"));
		}
		
		// AGE CALCULATION
		$age = "Unknown";
		$current_year = date('Y');
		if($customerData['dob'] != ""){
			$year = substr($customerData['dob'], -4);		
			$age = $current_year - $year;
		}
	}
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:700;
	font-size: 15px;
	color:#123C69;
	margin: 0px;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 10px;
	}
	.flex-container {
	display: flex;	
	flex-wrap: wrap
	}
	.flex-selected,.flex-data{
	margin:2px;
	}
	table h4{
	font-size:12px;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
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
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}
	.btn-danger{
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
	background-color:#e74c3c;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}	
	.text-success{
	font-weight:600;
	color: #123C69;
	text-transform: uppercase;
	font-size: 12px;
	}	
	.table .table-th{
	width: 20%;
	}
	#ornamentTable th, #ornamentTable td{
	text-align: center;
	}
	#ornamentTable th{
	text-transform:uppercase;
	background-color:#123C69;
	color: #f5f5f5;
	}
	.fa_Icon{
	color: #123C69;
	font-weight: 900;
	}
	.fa-no-file{
	color: #990000;
	font-weight: 900;
	}
	.timerDisplay{
	font-family: monospace;
	font-weight: bold;
	letter-spacing: 4px;
	}
	.timerDisplay1{
	font-family: monospace;
	font-weight: bold;
	letter-spacing: 4px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="col-lg-10" style="margin-bottom: 0px;">
				<h3>Customer Details</h3>
			</div>


			<div class="col-lg-4">
				<div class="hpanel">
					<?php if($billData['status'] == 'ZONAL-VERIFIED'){ ?>
    <div class="panel-body text-center">					
        <h4 class="m-xs" style="padding: 10px;">
            ⏳ <span class="timerDisplay1">00:00:00</span>
        </h4>		
				
        <h3 class="font-extra-bold no-margins text-success">
            Billed Time check : <?php echo $billData['time']; ?>
        </h3>					
    </div>
					<?php } ?>
				</div>
			</div>	
			<div class="col-lg-4">
				<div class="hpanel">
					<?php if($billData['status'] == 'ZONAL-VERIFIED'){ ?>
						<div class="panel-body text-center">					
							<h4 class="m-xs" style="padding: 10px;">
  	 <span class="timerDisplay" data-starttime="<?php echo $billData['time']; ?>">00:00</span></h4>					
							<h3 class="font-extra-bold no-margins text-success">
								Billed Time : <?php echo $billData['time']; ?>
							</h3>					
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">				
				<div class="panel-body">
					<div class="col-lg-3" align="center" style="padding-top: 20px;">
						<?php
						$imagePath = "../" . $customerData['customerImage'];
						if (!empty($customerData['customerImage']) && file_exists($imagePath)) {
					        ?>
						    <a target="_blank" href="<?php echo $imagePath; ?>">
        					    <img src="<?php echo $imagePath; ?>" style="border: 2px solid #ddd;">
    						    </a>
					        <?php
						} else {
    						?>
    						    <img src="<?php $customerData['customerImage']="user.png";echo $customerData['customerImage']; ?>">
						<?php
						}
						?>
					</div>
					<div class="col-lg-9" style="padding-top: 20px;"> 
						<table class="table table-bordered">
							<tr>
								<td class="text-success table-th">Name</td>
								<td><?php echo $customerData['name']; ?></td>
								<td class="text-success table-th">Date Of Birth</td>
								<td><?php echo $customerData['dob']; ?></td>
								<td class="text-success table-th" style="width: 10px;">Age</td>
								<td><?php echo $age; ?></td>
							</tr>
							<tr>
								<td class="text-success table-th">Contact</td>
								<td><?php echo $customerData['mobile']; ?></td>
								<td class="text-success table-th">Gender</td>
								<td colspan="3"><?php echo $customerData['gender']; ?></td>
							</tr>
							<tr>
								<td class="text-success table-th">Alternate Contact</td>
								<td><?php echo $customerData['rcontact']; ?></td>
								<td class="text-success table-th">Relation</td>
								<td colspan="3"><?php echo $customerData['relation']; ?></td>
							</tr>
							<!-- <tr>
								<td class="text-success table-th">Address</td>
								<td colspan="5">
									<!?php echo $customerData['paline'].", ".$customerData['plocality'].", ".$customerData['pland'].", ".$customerData['pcity'].", ".$customerData['pstate'].", ".$customerData['ppin']; ?>
								</td>
							</tr> -->
						</table>
						<table class="table table-bordered">
							<tr>
								<td class="text-success table-th">ID Proof</td>
								<td class="text-center"><?php echo $customerData['idProof']; ?></td>
								<td class="text-center"><?php echo $customerData['idNumber']; ?></td>
								<td class="text-center">
									<?php if($customerData['idFile']!=""){ ?>
										<i><a target="_blank" href="../CustomerDocuments/<?php echo $customerData['idFile'];?>">
											<i class="pe-7s-paperclip fa-2x fa_Icon"></i>
										</a></i>
										<?php }else{ ?>
										<i class="pe-7s-close-circle fa-2x fa-no-file"></i>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="text-success table-th">Address Proof</td>
								<td class="text-center"><?php echo $customerData['addProof']; ?></td>
								<td class="text-center"><?php echo $customerData['addNumber']; ?></td>
								<td class="text-center">
									<?php if($customerData['addFile']!=""){ ?>
										<a target="_blank" href="../CustomerDocuments/<?php echo $customerData['addFile'];?>">
											<i class="pe-7s-paperclip fa-2x fa_Icon"></i>
										</a>
										<?php } else{ ?>
										<i class="pe-7s-close-circle fa-2x fa-no-file"></i>
									<?php } ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-3">
			<div class="hpanel">
				<div class="panel-heading">
					<h3>VM Details</h3>
				</div>
				<div class="panel-body">
					<?php 
						while($row = mysqli_fetch_Assoc($extraData)){
							$x = json_decode($row['extra'],true);
							echo "<table class='table table-bordered'>";
							echo "<tr><td width='30%' class='text-success'>Gross W</td><td>".$x['GrossW']."</td></tr>";
							echo "<tr><th class='text-success'>Count</th><td>".$x['itemCount']."</td></tr>";
							echo "<tr><th class='text-success'>Hallmark</th><td>".$x['Hallmark']."</td></tr>";
							echo "<tr><th class='text-success'>RelAmount</th><td>".$x['RelAmount']."</td></tr>";
							echo "<tr><th class='text-success'>Remarks</th><td>".$row['idnumber']."</td></tr>";
							echo "</table>";
						}
					?>
				</div>
				<?php if($billData['status'] == 'ZONAL-VERIFIED' && ($type=='ApprovalTeam' || $type=='Master' || $type=='VM-HO')) { ?>
					<div class="panel-body" style="margin-top: 5px;">
						<h3 class="text-success" style="padding: 10px; margin-bottom: 15px;"><b> <i style="color:#990000" class="fa fa-upload"></i> Re-Upload Docs </b></h3>
						<?php if($billData['type'] == "Physical Gold") { ?>
							<button class="btn btn-sm btn-outline btn-danger2" type="button" data-transid="<?php echo $billData['id']; ?>" onclick="updateTransUpload(this)" >Customer Docs</button>
							<?php } else if($billData['type'] == "Release Gold") { ?>
							<button class="btn btn-sm btn-outline btn-danger2" type="button" data-relid="<?php echo $releaseData['rid']; ?>" onclick="updateReleaseUpload(this)" >Customer & Before Rel Docs</button>
							<button class="btn btn-sm btn-outline btn-danger2" type="button" data-transid="<?php echo $billData['id']; ?>" onclick="updateTransUpload(this)" >After Release Documents</button>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
		
		<div class="col-lg-9">

				<div class="panel-heading">
					<h3>Bill Details</h3>
				</div>
				<div class="panel-body">
					<?php if($billData['type'] == "Physical Gold"){ ?>
						<h3 class="text-success" style="padding: 10px; margin-bottom: 15px;"><b><i style="color:#990000" class="fa fa-circle-o"></i> PHYSICAL GOLD </b></h3>
						<?php } else{ ?>
						<div class="table-responsive col-md-12">
							<table class="table table-bordered">
								<tr>
									<td class="text-success" style="border-top: hidden; border-bottom: hidden; border-left: hidden;">
										<h3 class="text-success" style="padding: 10px;"><b><i style="color:#990000" class="fa fa-circle-o"></i> RELEASE GOLD </b></h3>
									</td>
									<td class="text-center"><small>DOC 1</small><br>
										<?php if($releaseData['relDoc1']!=""){ ?>
											<a target="_blank" href="../ReleaseDocuments/<?php echo $releaseData['relDoc1'];?>">
												<i class="pe-7s-ticket fa-2x fa_Icon"></i>
											</a>
											<?php }else{ ?>
											<i class="pe-7s-close fa-2x fa-no-file"></i>
										<?php } ?>
									</td>
									<td class="text-center"><small>DOC 2</small><br>
										<?php if($releaseData['relDoc2']!=""){ ?>
											<a target="_blank" href="../ReleaseDocuments/<?php echo $releaseData['relDoc2'];?>">
												<i class="pe-7s-ticket fa-2x fa_Icon"></i>
											</a>
											<?php }else{ ?>
											<i class="pe-7s-close fa-2x fa-no-file"></i>
										<?php } ?>
									</td>
									<td class="text-center"><small>DOC 3</small><br>
										<?php if($billData['ple']!=""){ ?>
											<a target="_blank" href="../ReleaseDocuments/<?php echo $billData['ple'];?>">
												<i class="pe-7s-ticket fa-2x fa_Icon"></i>
											</a>
											<?php }else{ ?>
											<i class="pe-7s-close fa-2x fa-no-file"></i>
										<?php } ?>
									</td>
								</tr>
							</table>
						</div>
					<?php } ?>
					<?php if($billData['status'] == 'ZONAL-VERIFIED' && ($type=='ApprovalTeam' || $type=='Master'|| $type=='VM-HO' )) { ?>
						<form action="xapprovalVM.php" method="POST">
							<input type="hidden" name="tid" value="<?php echo $billData['id']; ?>">
							<?php if($billData['type'] == "Release Gold"){ ?>
								<input type="hidden" name="rid" value="<?php echo $releaseData['rid']; ?>">
								<input type="hidden" name="relDate" value="<?php echo $releaseData['date']; ?>">
								<input type="hidden" name="releaseAmount" value="<?php echo $releaseData['amount']; ?>">
								<?php }else{?>
								<input type="hidden" name="releaseAmount" value="">
							<?php } ?>
							<input type="hidden" name="impsStatus" value="<?php echo $billData['impsA']; ?>">
							<input type="hidden" name="name" value="<?php echo $customerData['name']; ?>">
							<input type="hidden" name="mobile" value="<?php echo $contact; ?>">
							<input type="hidden" name="branchId" value="<?php echo $billData['branchId']; ?>">
							<input type="hidden" name="billId" value="<?php echo $billData['billId']; ?>">
							<input type="hidden" name="idNum" value="<?php echo $customerData['idNumber']; ?>">
							<input type="hidden" name="addNum" value="<?php echo $customerData['addNumber']; ?>">
							<input type="hidden" name="grossW" value="<?php echo $billData['grossW']; ?>">
							<input type="hidden" name="amountPaid" value="<?php echo $billData['amountPaid']; ?>">
							<input type="hidden" name="livetimer" id="livetimer">
							
							<div class="table-responsive col-md-12">
								<table id="ornamentTable" cellpadding="1" cellspacing="1" class="table table-bordered table-condensed">
									<tbody>
										<tr>
											<th class="text-success">ORNAMENT</th>
											<th class="text-success">PIECES</th>
											<th class="text-success">GROSS WEIGHT</th>
											<th width="30%" class="text-success">ORNAMENT PARTS</th>
											<th width="30%" class="text-success">CHOICE</th>
										</tr>
										<?php
											while($orn = mysqli_fetch_assoc($ornamentData)){
												echo "<div><tr><input type='hidden' name='typeInfo[]' class='ornamentInfo' required>";
												echo "<input type='hidden' name='ornID[]' value='".$orn['ornamentId']."'>";
												echo "<td>".$orn['type']."</td>";
												echo "<td>".$orn['pieces']."</td>";
												echo "<td>".$orn['weight']."</td>";
												echo "<td><div class='flex-container typeInfo'></div></td><td><div class='flex-container choiceInfo'>";
												$ornament = $orn['type'];
												foreach ($ornamentType[$ornament] as $key => $value){
													echo "<button class='flex-data btn btn-outline btn-primary btn-sm' type='button' value='".$value."'>".$value."</button>";
												}
												echo "</div></td>";
												echo "<tr></div>";
											}
										?>
										<tr>
											<th class="text-success">TOTAL</th>
											<th></th>
											<th class="text-success"><?php echo $billData['grossW'] ?></th>
											<th></th>
											<th></th>
										</tr>
									</tbody>
								</table>
							</div>
							<label class="col-sm-12 control-label"><br></label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="remarks" placeholder="Enter your remarks" style="margin-bottom: 20px;">
								<?php if($billData['paymentType'] == 'Cash') { ?>
									<button class="btn btn-success submitDesign" name="submitApproveCash" type="submit">
										<span style="color:#ffcf40" class="fa fa-check"></span> APPROVE
									</button> 
									<button class="btn btn-danger submitDesign" name="submitRejectCash" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> <b>REJECT</b>
									</button>
									<?php } else if($billData['paymentType'] == 'NEFT/RTGS') { ?>
									<button class="btn btn-success submitDesign"  name="submitVerifyIMPS"  type="submit">
										<span style="color:#ffcf40" class="fa fa-check"></span> VERIFY
									</button>
									<button class="btn btn-danger submitDesign" name="submitRejectIMPS" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> REJECT
									</button>
									<?php } else if($billData['paymentType'] == 'Cash/IMPS') { ?>
									<button class="btn btn-success submitDesign"  name="submitVerifyCashIMPS"  type="submit">
										<span style="color:#ffcf40" class="fa fa-check"></span> VERIFY
									</button>
									<button class="btn btn-danger submitDesign" name="submitRejectCashIMPS" type="submit">
										<span style="color:#ffcf40" class="fa fa-times"></span> REJECT
									</button>
								<?php } ?>
							</div>
						</form>
						<?php  } else { ?>
						<div class="table-responsive col-md-12">
							<table id="ornamentTable" cellpadding="1" cellspacing="1" class="table table-bordered table-condensed table-striped">
								<tbody>
									<tr>
										<th class="text-success">ORNAMENT</th>
										<th class="text-success">PIECES</th>
										<th class="text-success">GROSS WEIGHT</th>
										<th class="text-success">GROSS AMOUNT</th>
									</tr>
									<?php
										while($orn = mysqli_fetch_assoc($ornamentData)){
											echo "<tr>";
											echo "<td>".$orn['type']."</td>";
											echo "<td>".$orn['pieces']."</td>";
											echo "<td>".$orn['weight']."</td>";
											echo "<td>".$orn['gross']."</td>";
											echo "</tr>";
										}
									?>
									<tr>
										<th class="text-success">TOTAL</th>
										<th></th>
										<th class="text-success"><?php echo $billData['grossW'] ?></th>
										<th class="text-success"><?php echo $billData['grossA'] ?></th>
									</tr>
								</tbody>
							</table>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php if($billData['status'] == 'ZONAL-VERIFIED'){ ?>
 <script>
        const timerDisplay1 = document.querySelector(".timerDisplay1");
        const billId = <?php echo json_encode($billData['id']); ?>;
        const localStorageKey = `timerStartTime_${billId}`;

        const pad = (n) => n.toString().padStart(2, '0');

        let startTimestamp = localStorage.getItem(localStorageKey);

        if (!startTimestamp) {
            startTimestamp = Date.now();
            localStorage.setItem(localStorageKey, startTimestamp);
        } else {
            startTimestamp = parseInt(startTimestamp);
        }

        const updateTimer = () => {
            const now = Date.now();
            const elapsed = now - startTimestamp;

            let totalSeconds = Math.floor(elapsed / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            totalSeconds %= 3600;
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;

            timerDisplay1.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;

            const parentTr = timerDisplay1.parentElement;

            if (hours === 0) {
                if (minutes < 5) {
                    parentTr.style.backgroundColor = "#c9df8a";
                } else if (minutes < 10) {
                    parentTr.style.backgroundColor = "#ffebaa";
                } else if (minutes < 15) {
                    parentTr.style.backgroundColor = "#ffb38a";
                } else {
                    parentTr.style.backgroundColor = "#ff7b7b";
                }
            } else {
                parentTr.style.backgroundColor = "#b6b6b6";
            }
        };

        setInterval(updateTimer, 1000);
        updateTimer();
</script>
	<script>
		const timerDisplay = document.querySelectorAll(".timerDisplay");
		
		const convertMS = (ms)=>{
			var d, h, m, s;
			s = Math.floor(ms / 1000);
			m = Math.floor(s / 60);
			s = s % 60;
			h = Math.floor(m / 60);
			m = m % 60;
			d = Math.floor(h / 24);
			h = h % 24;
			h += d * 24;
			return {
				hour: h,
				min: m,
				sec: s
			}
		}	
		
		const showCountDown = ()=>{
			const current_date_time = new Date();		
			
			timerDisplay.forEach((td)=>{			
				const starttime = td.dataset.starttime;
				const start_time_arr = starttime.split(":");
				
				const tdTime = new Date(current_date_time.getFullYear(), +current_date_time.getMonth(), current_date_time.getDate(), start_time_arr[0], start_time_arr[1], start_time_arr[2]);
				const diff = current_date_time - tdTime;
				
				const { hour, min, sec } = convertMS(diff)	
				
				let timeText = (hour % 12 > 0 ) ? hour % 12 +":" : "";
				timeText += min +":"+ sec;
				
				td.textContent = timeText;
				
				const parentTr = td.parentElement;
				if(hour % 12 == 0){
					if(min < 5){
						parentTr.style.backgroundColor = "#c9df8a";
					}
					else if(min >= 5 && min < 10){
						parentTr.style.backgroundColor = "#ffebaa";
					}
					else if(min >= 10 && min < 15){
						parentTr.style.backgroundColor = "#ffb38a";
					}
					else{
						parentTr.style.backgroundColor = "#ff7b7b";
					}
				}
				else{
					parentTr.style.backgroundColor = "#b6b6b6";
				}
				
			})
		}
		
		const timer = setInterval(showCountDown, 1000);
		showCountDown();
	</script>
		<script>		
			async function updateTransUpload(btn){
				const conf = confirm("Are you sure ??");
				if(conf){
					const transid = btn.dataset.transid;
					
					const form = new FormData();
					form.append("transid", transid);
					form.append("updateTransUpload", true);
					
					const response =  await fetch("../editAjax.php", {
						method: "POST",
						body: form
					});
					const result = await response.json();
						
					if(result['message'] == "success"){
						alert("BM can Reupload the documents");
						window.location = "zbilllStatus.php";
					}
					else{
						console.log(result);
						alert("Something went wrong, please try again later");
						return;
					}				
				}
			}		
			async function updateReleaseUpload(btn){
				const conf = confirm("Are you sure ??");
				if(conf){
					const relid = btn.dataset.relid;
					
					const form = new FormData();
					form.append("relid", relid);
					form.append("updateReleaseUpload", true);
					
					const response =  await fetch("editAjax.php", {
						method: "POST",
						body: form
					});
					const result = await response.json();
						
					if(result['message'] == "success"){
						alert("BM can Reupload the documents");
						window.location = "zbilllStatus.php";					
					}
					else{
						console.log(result);
						alert("Something went wrong, please try again later");
						return;
					}					
				}
			}			
		</script>
	<?php } ?>
	<script>
		$(document).ready(function(){
			$('.choiceInfo').on('click','.flex-data', function(){
				let data = $(this).val(),
				ornamentInfo = '',
				datum = "<button class='flex-selected btn btn-outline btn-primary btn-sm' type='button' value='"+data+"'>"+data+"</button>",
				domValue = $(this).parent().parent('td').prev('td').find('.typeInfo');
				domValue.append(datum);
				domValue.children().each(function () {
					ornamentInfo +=$(this).html()+",";
				});
				domValue.parent('td').parent('tr').find('.ornamentInfo').val(ornamentInfo);
				$(this).remove();
			});
			$('.typeInfo').on('click','.flex-selected', function(){
				let data = $(this).val(),
				ornamentInfo = '',
				datum = "<button class='flex-data btn btn-outline btn-primary btn-sm' type='button' value='"+data+"'>"+data+"</button>",
				domElement = $(this).parent().parent('td').next('td').find('.choiceInfo'),
				domParent = $(this).parent();
				domElement.append(datum);
				$(this).remove();
				domParent.children().each(function () {
					ornamentInfo +=$(this).html()+",";
				});
				domElement.parent('td').parent('tr').find('.ornamentInfo').val(ornamentInfo);
			});
		});
	</script>
	<script>
document.addEventListener("DOMContentLoaded", function () {
	const form = document.querySelector("form[action='xapprovalVM.php']");
	const liveTimerInput = document.getElementById("livetimer");
	const timerSpan = document.querySelector(".timerDisplay1");

	if (form && liveTimerInput && timerSpan) {
		form.addEventListener("submit", function (e) {
			const timerValue = timerSpan.textContent.trim();

			// Debugging
			console.log("⏱️ Timer span shows:", timerValue);

			liveTimerInput.value = timerValue;

			// Confirm it's set
			console.log("✅ liveTimer hidden input set to:", liveTimerInput.value);
		});
	} else {
		console.warn("❌ Form, timer span, or hidden input not found.");
	}
});
</script>
	<?php include("footer.php"); ?>



