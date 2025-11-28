<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else {
		include("logout.php");
	}
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];

	$id = $_GET['id'];	
	$customerData = mysqli_fetch_assoc(mysqli_query($con, "SELECT e.Id, e.customer, e.contact, e.type, e.ornament_docs, e.idnumber, e.branch, e.extra, e.status, b.branchName, e.time
	FROM everycustomer e
	LEFT JOIN branch b ON e.branch=b.branchId
	WHERE e.Id='$id'
	LIMIT 1"));
	$extra = json_decode($customerData['extra'], true);
	if (!isset($extra['startTime2']) || empty($extra['startTime2'])) {
		$startTime2 = date("H:i:s");
		$extra['startTime2'] = $startTime2;
		$extraJson = json_encode($extra);
		mysqli_query($con, "UPDATE everycustomer SET extra = '$extraJson' WHERE Id = '$id'");
	}
	// Always use the stored one
	$customerData['startTime2'] = $extra['startTime2'];
?>
<style>
	.list-cust{
	list-style-type: square;
	margin: 0;
	padding-left: 15px;
	}
	.li-cust{
	padding-bottom: 15px;
	}
	form h5{
	color: #123C69;
	text-transform: uppercase;
	font-size: 12px;
	}
	#timerDisplay{
	padding-top: 10px;
	padding-bottom: 10px;
	font-family: monospace;
	font-weight: bold;
	letter-spacing: 4px;
	font-size: 15px;
	}
	#timerDisplay1 {
	font-family: monospace;
	font-weight: bold;
	letter-spacing: 4px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-8">
			<div class="hpanel">
				<div id="accordion">
					<div class="card">
						<div class="card-header" id="headingOne">
							<h3 class="font-light m-b-xs text-success" >
								<b><i class="fa_Icon fa fa-users"></i> Registered Customers</b>
							</h3>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
	<div class="hpanel">
	<?php if ($customerData['status'] == 'Begin') { ?>
		<div class="panel-body text-center">
			<h4 class="m-xs" style="padding: 10px;">
				<span class="timerDisplay1" data-starttime2="<?php echo $customerData['startTime2']; ?>">00:00:00</span>
			</h4>
			<h3 class="font-extra-bold no-margins text-success">
				Billed Time : <?php echo $customerData['time']; ?>
			</h3>
		</div>
	<?php } ?>
		</div>
	</div>



		<div class="col-lg-3">
			<div class="hpanel hgreen">
				<div class="panel-body text-center">
					<h3><?php echo $customerData['branchName']; ?></h3>
					<hr>					
					<h3><?php echo $customerData['customer']; ?></h3>
					<?php if($customerData['status'] == 'Begin'){  ?>
						<h4 class="font-bold" id="customerContact" data-mobile="<?php echo $customerData['contact']; ?>" style='cursor: pointer; color: #9b0606;'><?php echo "XXXXXX".substr($customerData['contact'], 6); ?></h4>
						<small><i>(Click the Number to copy Mobile Number)</i></small>
					<?php }else{ ?>
						<h4 class="font-bold" style='cursor: pointer; color: #9b0606;'><?php echo "XXXXXX".substr($customerData['contact'], 6); ?></h4>
					<?php } ?>														
				</div>
				<?php if($customerData['status'] == 'Begin'){  ?>
					<div class="panel-body text-center">
						<p id="timerDisplay"></p>									
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="col-lg-9">
			<div class="hpanel">
				<div class="hpanel">				
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1" class="text-success">CUSTOMER DATA</a></li>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body">
								<?php if($customerData['status'] == 'Begin'){  ?>
									<form method="POST" class="form-horizontal" action="vmSubmit.php">
										<input type="hidden" name="id" value="<?php echo $customerData['Id']; ?>" >
										<input type="hidden" name="language" value="<?php echo $extra['Language']; ?>" >
										<input type="hidden" name="contact" value="<?php echo $customerData['contact']; ?>" >
										<input type="hidden" name="liveTimer" id="liveTimer">
										<input type="hidden" name="totalbills" value="<?php echo $extra['bills']; ?>" >
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">Bill Type</label>
											<div class="col-sm-8">
												<input type="text" id="billType" name="billType" class="form-control" value="<?php echo $customerData['type']; ?>" readonly>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success"></label>
											<div class="col-sm-8">
												<div id="typeData"></div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">Document</label>
											<div class="col-sm-8">
												<a href="../OrnamentDocs/<?php echo $customerData['ornament_docs']; ?>" target="_blank">
													<img src="../OrnamentDocs/<?php echo $customerData['ornament_docs']; ?>" height="75px" width="75px" alt="Customer Document">
												</a>
											</div>
										</div>
										<div class="hr-line-dashed"></div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">Gross Weight</label>
											<div class="col-sm-8">
												<input type="text" name="grossW" placeholder="Gross Weight" required class="form-control" value="<?php echo $extra['GrossW']; ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">No of Ornaments</label>
											<div class="col-sm-8">
												<input type="text" name="itemCount" placeholder="Count" required class="form-control" autocomplete="off">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">Hallmark</label>
											<div class="col-sm-8">
												<select class="form-control m-b" name="hallmark" required>
													<option selected="true" disabled="disabled" value="">Select</option>
													<option value="yes">Hallmark</option>
													<option value="no">Non Hallmark</option>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-2 control-label text-success">Remarks</label>
											<div class="col-sm-8">
												<textarea name="remarks" placeholder="Remarks" class="form-control" required autocomplete="off"></textarea>
											</div>
										</div>
										<div class="hr-line-dashed"></div>
										<div class="form-group">
											<div class="col-sm-8 col-sm-offset-2">
												<button class="btn btn-danger" type="button" data-id="<?php echo $customerData['Id']; ?>" id="wrongEntryCustomer">Wrong Entry</button>
												<button class="btn btn-success" type="submit" name="updateRegisteredCustomer">Save changes</button>
											</div>
										</div>
									</form>
									<?php }else{ ?>
									<table class="table table-bordered">
										<tbody>
											<tr>
												<th width="30%" class='text-success'>Bill Type</th>
												<td><?php echo $customerData['type']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>With Gold</th>
												<td><?php echo $extra['With']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Pledge</th>
												<td><?php echo $extra['Pledge']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Release Slips</th>
												<td><?php echo $extra['RelSlips']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Release Amount</th>
												<td><?php echo $extra['RelAmount']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Gross Weight</th>
												<td><?php echo $extra['GrossW']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>No of Ornaments</th>
												<td><?php echo $extra['itemCount']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Hallmark</th>
												<td><?php echo $extra['Hallmark']; ?></td>
											</tr>
											<tr>
												<th class='text-success'>Remarks</th>
												<td><?php echo $customerData['idnumber']; ?></td>
											</tr>
										</tbody>
									</table>
								<?php } ?>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		
	</div>
	
	<?php if($customerData['status'] == 'Begin'){  ?>
		<script>
			const type = document.getElementById("billType");
			const typeData = document.getElementById('typeData');
			
			const physicalData = "<div class='col-sm-8'><div class='radio radio-info radio-inline'><input type='radio' value='with' name='withMetal' checked=''><label> With Gold </label></div><div class='radio radio-info radio-inline'><input type='radio' value='without' name='withMetal'><label> Without Gold </label></div></div><div class='col-sm-8' style='margin-bottom: 20px; margin-top: 10px;'><div class='radio radio-info radio-inline'><input type='radio' value='no' name='pledge' checked=''><label> Billing </label></div></div>";
			
			const releaseData = "<div class='col-sm-4'><select class='form-control m-b' name='relSlips' required><option selected='true' disabled='disabled' value=''>Rel Slips</option><option value='yes'>With Slips</option><option value='no'>Without Slips</option></select></div><div class='col-sm-4'><input type='text' name='relAmount' placeholder='Rel Amount' class='form-control' autocomplete='off' required></div>";		
			
			let billType = type.value;
			if(billType == 'physical'){
				typeData.innerHTML = physicalData;
			}
			else if(billType == 'release'){
				typeData.innerHTML = releaseData;
			}
		</script>
		<!-- <script>
			(function(){
				
				const weButton = document.getElementById("wrongEntryCustomer");
				weButton.addEventListener("click", async (e)=>{
					const reconfirm = confirm("Are you sure this is a Wrong Entry ?");
					if(reconfirm){
						let id = weButton.dataset.id;
						
						let formData = new FormData();
						formData.append("id", weButton.dataset.id);
						formData.append("wrongEntryCustomer", true);
						
						let response = await fetch("zvmn.php", {
							method: "POST",
							body: formData
						});
						
						if(response.ok){
							let result = await response.text();
							if(result == "SUCCESS"){
								alert("This customer is updated as 'Wrong Entry'");
								window.location.href = 'zbmhoHome1.php';
							}
							else{
								alert("Error Occured, Please try again later");
							}
						}
						else{
							alert("Error Occured, Please try again later");
						}
					}
				});
				
			})();
		</script>-->


		<script>
			(function() {
				const weButton = document.getElementById("wrongEntryCustomer");

				weButton.addEventListener("click", async (e) => {
					// Get form field values
					const grossW = document.querySelector("input[name='grossW']").value.trim();
					const itemCount = document.querySelector("input[name='itemCount']").value.trim();
					const hallmark = document.querySelector("select[name='hallmark']").value;
					const remarks = document.querySelector("textarea[name='remarks']").value.trim();

					// Check if all required fields are filled
					if (!grossW || !itemCount || !hallmark || !remarks) {
						alert("Please fill in all required fields before marking as 'Wrong Entry'.");
						return;
					}

					const confirmWrong = confirm("Are you sure this is a Wrong Entry ?");
					if (!confirmWrong) return;

					let id = weButton.dataset.id;

					let formData = new FormData();
					formData.append("id", id);
					formData.append("wrongEntryCustomer", true);
					formData.append("remarks", remarks);

					let response = await fetch("zvmn.php", {
						method: "POST",
						body: formData
					});

					if (response.ok) {
						let result = await response.text();
						if (result === "SUCCESS") {
							alert("This customer is updated as 'Wrong Entry'");
							window.location.href = 'zbmhoHome1.php';
						} else {
							alert("Error occurred, please try again later.");
						}
					} else {
						alert("Error occurred, please try again later.");
					}
				});
			})();
		</script>

		<script>
			(function(){
				
				const timeDisplay = document.querySelector("#timerDisplay");	
				const startTime = '<?php echo $customerData['time']; ?>';

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
				function getDiffColor(start_time, current_date_time){
					const start_time_arr = start_time.split(":");

					const start_date_time = new Date(current_date_time.getFullYear(), +current_date_time.getMonth(), current_date_time.getDate(), start_time_arr[0], start_time_arr[1], start_time_arr[2]);
					const diff = current_date_time - start_date_time;

					const { hour, min, sec } = convertMS(diff);
					let timeText = (hour % 12 > 0) ? hour % 12 +":" : "";
					timeText += min +":"+ sec;

					let color = "";
					if(hour % 12 == 0){
						if(min < 5){
							color = "#c9df8a";
						}
						else if(min >= 5 && min < 10){
							color = "#ffebaa";
						}
						else if(min >= 10 && min < 15){
							color = "#ffb38a";
						}
						else{
							color = "#ff7b7b";
						}
					}
					else{
						color = "#b6b6b6";
					}

					return {
						timeText: timeText,
						color: color
					}

				}
				function timerDisplay(){				
					const current_date_time = new Date();				
					const { timeText, color } = getDiffColor(startTime, current_date_time);	

					timeDisplay.innerHTML = "&#x23F3; "+timeText;							
					timeDisplay.style.backgroundColor = color;			
				}
				timerDisplay();
				setInterval(timerDisplay, 1000);
				
			})();
		</script>
		<script>
const timerDisplay1 = document.querySelectorAll(".timerDisplay1");
const convertMS = (ms) => {
	let s = Math.floor(ms / 1000);
	let m = Math.floor(s / 60);
	s = s % 60;
	let h = Math.floor(m / 60);
	m = m % 60;
	return {
		hour: h,
		min: m,
		sec: s
	};
};


const showCountDown = () => {
	const now = new Date();
	timerDisplay1.forEach(td => {
		const starttime2 = td.dataset.starttime2;
		const [hh, mm, ss] = starttime2.split(":").map(Number);
		const start = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hh, mm, ss);
		let diff = now - start;
		if (diff < 0) diff = 0; // avoid negative
		const {
			hour,
			min,
			sec
		} = convertMS(diff);
	const timeText =
	String(hour).padStart(2, "0") + ":" +
	String(min).padStart(2, "0") + ":" +
	String(sec).padStart(2, "0");
	td.textContent = timeText;
	});
};
setInterval(showCountDown, 1000);
showCountDown();
</script>

	<?php } ?>	
	<script>
		(function(){
			
			const contact = document.querySelector("#customerContact");
			contact.addEventListener("click", (e)=>{
				var inp =document.createElement('input');
				document.body.appendChild(inp);
				inp.value = contact.dataset.mobile;
				inp.select();
				document.execCommand('copy',false);
				inp.remove();
			})
			
		})();
	</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
	const form = document.querySelector("form[action='vmSubmit.php']");
	const liveTimerInput = document.getElementById("liveTimer");
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

<?php include("footerNew.php"); ?>	
