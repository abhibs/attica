<?php
	session_start();
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if($type == 'Call Centre'){
	    include("header.php");
		include("menuCall.php");
	}
	else if($type=='Software'){
	    include("header.php");
		include("menuSoftware.php");
	}
	else {
		include("logout.php");
	}
    include("dbConnection.php");
	
	$data = [];
	$sql = mysqli_query($con, "SELECT * FROM misc WHERE purpose IN ('Gold Rate','Silver Rate','KA Closed','TN Closed','APT Closed','Special Rate')");
	while($row = mysqli_fetch_assoc($sql)){
		switch($row['purpose']){
			case "Gold Rate": 
			$data['goldRate'] = $row['day'];
			break;
			
			case "Silver Rate": 
			$data['silverRate'] = $row['day'];
			break;
			
			case "KA Closed": 
			$data['kaClosed'] = $row['day'];
			break;
			
			case "TN Closed": 
			$data['tnClosed'] = $row['day'];
			break;
			
			case "APT Closed": 
			$data['aptClosed'] = $row['day'];
			break;
			
			case "Special Rate":
			$data['special'] = $row['day'];
			break;
			
			default: break;
		}
	}
	
	$kaList = ($data['kaClosed'] != "") ? explode(",", $data['kaClosed']) : [];
	$tnList = ($data['tnClosed'] != "") ? explode(",", $data['tnClosed']) : [];
	$aptList = ($data['aptClosed'] != "") ? explode(",", $data['aptClosed']) : [];
	
	$kaBranchData = [];
	$tnBranchData = [];
	$aptBranchData = [];
	$branchList = mysqli_query($con, "SELECT branchId, branchName, state FROM branch WHERE status=1");
	while($row = mysqli_fetch_assoc($branchList)){
		if($row['state'] == 'Karnataka'){
			$kaBranchData[$row['branchId']] = $row['branchName'];
		}
		else if($row['state'] == 'Andhra Pradesh' || $row['state'] == 'Telangana'){
			$aptBranchData[$row['branchId']] = $row['branchName'];
		}
		else if($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry'){
			$tnBranchData[$row['branchId']] = $row['branchName'];
		}
	}
	
?>
<style>
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.btn-primary {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 12px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.text-success {
	font-weight: 600;
	color: #123C69;
	}
	.branchList{
	list-style-type: none;
	padding-left: 1px;
	}
	.branchList li{
	background-color: #d2cccc;
	padding: 10px 10px;
	margin-top: 10px;
	border-radius: 5px;
	}
	.branchList li button{
	float: right;
	background-color: #990000;
	color: #ffffff;
	border-radius: 5px;
	border: none;
	}
	.btn-warning{
	border: none;
	background-color: #123C69;
	}
	.btn-warning:hover{
	background-color: #06203d;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-bottom: 0">
				<div class="panel-heading">
					<h3>Update Gold / Silver Rate</h3>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="col-lg-3">
				<div class="hpanel plan-box active">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Gold Rate</h4>
					</div>
					<div class="panel-body text-center">
						<form id="goldRateForm">
							<input type="number" name="goldrate" value="<?php echo $data['goldRate']; ?>" placeholder="Gold Rate" class="form-control m-b" required>
							<button class="btn btn-warning btn-block">Update</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="hpanel plan-box active">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Silver Rate</h4>
					</div>
					<div class="panel-body text-center">
						<form id="silverRateForm">
							<input type="number" name="silverrate" value="<?php echo $data['silverRate']; ?>" placeholder="Silver Rate" class="form-control m-b" required>
							<button class="btn btn-warning btn-block">Update</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="hpanel plan-box active">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Special Rate</h4>
					</div>
					<div class="panel-body text-center">
						<form id="specialRateForm">
							<input type="number" name="specialrate" value="<?php echo $data['special']; ?>" placeholder="Special Rate" class="form-control m-b" required>
							<button class="btn btn-warning btn-block">Update</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel" style="margin-bottom: 0">
				<div class="panel-heading">
					<h3>Update Closed Branches</h3>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">	
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Karnataka</h4>
					</div>
					<div class="panel-body">
						<form id="kaClosedForm" >
							<div class="input-group">
								<select name="branchId" class="form-control" required>
									<option selected value="" disabled>Select Closed Branch</option>
									<?php 
										foreach($kaBranchData as $key=>$value){
											echo "<option value=".$key." data-branchname='".$value."' >".$value."</option>";
										}
									?>
								</select>
								<span class="input-group-btn"> <button class="btn btn-warning">Add</button></span>
							</div>
						</form>
						<ul class="branchList kaClosed">
							<?php
								$len = count($kaList);
								for($i=0; $i<$len; $i++){
									echo "<li data-branchid='".$kaList[$i]."'>".$kaBranchData[$kaList[$i]]."<button data-branchid='".$kaList[$i]."' data-state='ka' onclick='removeClosedBranch(this)'>Delete</button></li>";
								}
							?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Tamilnadu</h4>
					</div>
					<div class="panel-body">
						<form id="tnClosedForm" >
							<div class="input-group">
								<select name="branchId" class="form-control" required>
									<option selected value="" disabled>Select Closed Branch</option>
									<?php 
										foreach($tnBranchData as $key=>$value){
											echo "<option value=".$key." data-branchname='".$value."' >".$value."</option>";
										}
									?>
								</select>
								<span class="input-group-btn"> <button class="btn btn-warning">Add</button></span>
							</div>
						</form>
						<ul class="branchList tnClosed">
							<?php
								$len = count($tnList);
								for($i=0; $i<$len; $i++){
									echo "<li data-branchid='".$tnList[$i]."'>".$tnBranchData[$tnList[$i]]."<button data-branchid='".$tnList[$i]."' data-state='tn' onclick='removeClosedBranch(this)' >Delete</button></li>";
								}
							?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading hbuilt text-center">
						<h4 class="font-bold">Andhra & Telangana</h4>
					</div>
					<div class="panel-body">
						<form id="aptClosedForm" >
							<div class="input-group">
								<select name="branchId" class="form-control" required>
									<option selected value="" disabled>Select Closed Branch</option>
									<?php 
										foreach($aptBranchData as $key=>$value){
											echo "<option value=".$key." data-branchname='".$value."' >".$value."</option>";
										}
									?>
								</select>
								<span class="input-group-btn"> <button class="btn btn-warning">Add</button></span>
							</div>
						</form>
						<ul class="branchList aptClosed">
							<?php
								$len = count($aptList);
								for($i=0; $i<$len; $i++){
									echo "<li data-branchid='".$aptList[$i]."'>".$aptBranchData[$aptList[$i]]."<button data-branchid='".$aptList[$i]."' data-state='apt' onclick='removeClosedBranch(this)' >Delete</button></li>";
								}
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	<?php include("footer.php"); ?>
	<script>
		
		function getCurrentList(list){
			const liArr = list.querySelectorAll("li");
			const arr = [];
			liArr.forEach((li, i)=>{
				arr.push(li.dataset.branchid);
			});
			return arr;
		}
		
		function removeClosedBranch(btn){
			const branchId = btn.dataset.branchid;
			const state = btn.dataset.state;
			
			const parentList = btn.parentElement.parentElement;
			const arr = getCurrentList(parentList);
			const filtered  = arr.filter((data, i)=>{
				if(data != branchId){
					return data;
				}
			});
			filteredStr = filtered.toString();
			
			const form = new FormData();
			form.append("updateClosed", true);
			form.append("state", state);
			form.append("branches", filteredStr);
			
			const xhr = new XMLHttpRequest();
			xhr.open("POST", "utils/misc/updateDisplay.php");
			xhr.responseType = "json";
			xhr.send(form);
			xhr.onprogress = (e)=>{
				btn.setAttribute("disabled", true);
			}
			xhr.onload = function(){
				const response = xhr.response;
				btn.removeAttribute("disabled");
				if(response.message == "success"){
					btn.parentElement.remove();
				}
				else{
					alert("Something went wrong, please try again later");
				}
			}
		}
		
		function addClosedbranch(btn, input, list, formId, state){
			let arr = getCurrentList(list);
			if(arr.includes(input.value)){
				alert("Branch Already Selected");
				return;
			}
			
			arr.push(input.value);
			const branchStr = arr.toString();
			
			const form = new FormData();
			form.append(formId, true);
			form.append("branches", branchStr);
			
			const xhr = new XMLHttpRequest();
			xhr.open("POST", "utils/misc/updateDisplay.php");
			xhr.responseType = "json";
			xhr.send(form);
			xhr.onprogress = (e)=>{
				btn.setAttribute("disabled", true);
			}
			xhr.onload = function(){
				btn.removeAttribute("disabled");
				
				const newLi = document.createElement("li");
				newLi.setAttribute("data-branchid", input.value);
				newLi.textContent = input.querySelector("option[value="+input.value+"]").dataset.branchname;
				
				const delBtn = document.createElement("button");
				delBtn.setAttribute("data-branchid", input.value);
				delBtn.setAttribute("data-state", state);
				delBtn.textContent = "Delete";
				delBtn.addEventListener("click", ()=>{ removeClosedBranch(delBtn) })
				
				newLi.append(delBtn);
				list.append(newLi);
			}
		}
		
		(function(){
			
			// KARNATAKA
			const kaClosedForm = document.getElementById("kaClosedForm");
			const kaList = document.querySelector(".kaClosed");
			kaClosedForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = kaClosedForm.querySelector("button");
				const branchInput = kaClosedForm.querySelector("select");
				
				addClosedbranch(btn, branchInput, kaList, "closeKAbranch", "ka");	
			});
			
			// TAMILNADU
			const tnClosedForm = document.getElementById("tnClosedForm");
			const tnList = document.querySelector(".tnClosed");
			tnClosedForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = tnClosedForm.querySelector("button");
				const branchInput = tnClosedForm.querySelector("select");
				
				addClosedbranch(btn, branchInput, tnList, "closeTNbranch", "tn");									
			});
			
			// ANDHRA PRADESH AND TELANGANA
			const aptClosedForm = document.getElementById("aptClosedForm");
			const aptList = document.querySelector(".aptClosed");
			aptClosedForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = aptClosedForm.querySelector("button");
				const branchInput = aptClosedForm.querySelector("select");
				
				addClosedbranch(btn, branchInput, aptList, "closeAPTbranch", "apt");				
			});
			
			// GOLD RATE UPDATE
			const goldRateForm = document.getElementById("goldRateForm");
			goldRateForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = goldRateForm.querySelector("button");
				const rateInput = goldRateForm.querySelector("[name=goldrate]")
				
				const form = new FormData();
				form.append("updateGoldRate", true);
				form.append("rate", rateInput.value);
				
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "utils/misc/updateDisplay.php");
				xhr.responseType = 'json';
				xhr.send(form);
				xhr.onprogress = (e)=>{
					btn.disabled = true;
				}
				xhr.onload = function(){
					const response = xhr.response;
					btn.removeAttribute("disabled");
					if(response.message == "success"){
						alert("Gold Rate Updated, Refresh the display");
					}
					else{
						alert("Something went wrong, please try again later")
					}
				}
			});
			
			// SILVER RATE UPDATE
			const silverRateForm = document.getElementById("silverRateForm");
			silverRateForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = silverRateForm.querySelector("button");
				const rateInput = silverRateForm.querySelector("[name=silverrate]");
				
				const form = new FormData();
				form.append("updateSilverRate", true);
				form.append("rate", rateInput.value);
				
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "utils/misc/updateDisplay.php");
				xhr.responseType = "json";
				xhr.send(form);
				xhr.onprogress = (e)=>{
					btn.setAttribute("disabled", true);
				}
				xhr.onload = function(){
					btn.removeAttribute("disabled");
					const response = xhr.response;
					if(response.message == "success"){
						alert("Silver Rate Updated, Refresh the display");
					}
					else{
						alert("Something went wrong, please try again later")
					}
				}
			});
			
			// SPECIAL RATE UPDATE
			const specialRateForm = document.getElementById("specialRateForm");
			specialRateForm.addEventListener("submit", (e)=>{
				e.preventDefault();
				
				const btn = specialRateForm.querySelector("button");
				const rateInput = specialRateForm.querySelector("[name=specialrate]");
				
				const form = new FormData();
				form.append("updateSpecialRate", true);
				form.append("rate", rateInput.value);
				
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "utils/misc/updateDisplay.php");
				xhr.responseType = "json";
				xhr.send(form);
				xhr.onprogress = (e)=>{
					btn.setAttribute("disabled", true);
				}
				xhr.onload = function(){
					btn.removeAttribute("disabled");
					const response = xhr.response;
					if(response.message == "success"){
						alert("Special Rate Updated, Refresh the display");
					}
					else{
						alert("Something went wrong, please try again later")
					}
				}
			});
			
		})();
		
	</script>	
