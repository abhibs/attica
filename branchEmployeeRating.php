<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Zonal'){
		include("header.php");
		include("menuZonal.php");
	}
	else if($type=='SubZonal') {
		include("header.php");
        	include("menuSubZonal.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if($type=='Master') {
        	include("header.php");
        	include("menumaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	$extra = "";
	$state = "";
	$displayState = "All States";
	if($_SESSION['branchCode']=="TN"){
		$extra= " AND u.username IN (select branchId from branch where state IN ('Tamilnadu','Pondicherry'))";	
		$state = " AND state IN ('Tamilnadu','Pondicherry')";
		$displayState = "Tamilnadu & Pondicherry";
	}
	elseif($_SESSION['branchCode']=="KA"){
		$extra= " AND u.username IN (select branchId from branch where state IN ('Karnataka'))";
		$state = " AND state IN ('Karnataka')";
		$displayState = "Karnataka";
	}
	elseif($_SESSION['branchCode']=="AP-TS"){
		$extra= " AND u.username IN (select branchId from branch where state IN ('Andhra Pradesh','Telangana'))";	
		$state = " AND state IN ('Andhra Pradesh','Telangana')";
		$displayState = "Andhra Pradesh & Telangana";
	}
	
?>
<link rel="stylesheet" href="vendor/toastr/build/toastr.min.css" />
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	background-color: #f5f5f5;
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
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
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
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
	.fa_Icon {
	color:#990000;
	}
	.table-responsive .row{
	margin: 0px;
	}
	.rating-star-full{
	display: inline-block;
	background-image: url("images/icons8-star-16.png");
	width: 16px;
	height: 16px;
	background-repeat: no-repeat;
	}
	.rating-star-half{
	display: inline-block;
	background-image: url("images/icons8-star-16.png");
	width: 8px;
	height: 16px;
	background-repeat: no-repeat;
	}
	.toast-message{
	color: black;
	}
	.editing-row{
	background-color: #C2EDB1;
	}
	.newRating{
	color: #F10003;
	font-weight: 600;
	background-color: #EEEAEA;
	}
	.empUpdateBtn, .branchUpdateBtn{
	border-radius: 3px;
	color: #123C69;
	padding-left: 10px;
	padding-right: 10px;
	border: 1px solid #123C69;
	}
	.empUpdateBtn:hover, .branchUpdateBtn:hover{
	color: #ffffff;
	background-color:#123C69; 
	}
</style>
<div id="wrapper">
	<div class="row content">	
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3>
					<i class="fa_Icon fa fa-star-half-full" ></i> Branch And Employee Rating - <?php echo $displayState; ?></h3>
				</div>
				
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa_Icon fa fa-user"></i> Employee</a></li>
						<li class=""><a data-toggle="tab" href="#tab-2"><i class="fa_Icon fa fa-bank"></i> Branch</a></li>
					</ul>
					<div class="tab-content">
						
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body" style="padding: 0px; padding-top: 25px;">
								<div class="table-responsive">
									<table id="employeeRatingTable" class="table">
										<thead>
											<tr class="theadRow">
												<th class="text-center">#</th>
												<th>Employee_Id</th>
												<th>Employee_Name</th>
												<th>Contact</th>
												<th>Current_Rating</th>
												<th width="20%">Stars</th>
												<th>New_Rating</th>
												<th class="text-center">Action</th>
											</tr>
										</thead>
										<tbody class="tbodyRow">
											<?php 
												$bmList = mysqli_query($con, "SELECT e.id, e.empId, e.name, e.contact, e.rating
												FROM users u
												JOIN employee e ON e.empId = u.employeeId
												WHERE u.type='Branch' AND u.employeeId!='' AND u.username !='AGPL000' ".$extra."
												ORDER BY e.empId ASC");
												$i = 1;
												while($row = mysqli_fetch_assoc($bmList)){
													$rating = $row['rating']/2;
													$ratingDiv = "";
													while($rating > 0.5){
														$ratingDiv .= "<div class='rating-star-full'></div>";
														$rating--;
													}
													if($rating == 0.5){
														$ratingDiv .= "<div class='rating-star-half'></div>";
													}
													echo "<tr className>";
													echo "<td class='text-center'>".$i."</td>";
													echo "<td>".$row['empId']."</td>";
													echo "<td>".$row['name']."</td>";
													echo "<td>".$row['contact']."</td>";
													echo "<td class='curRating'>".$row['rating']."</td>";
													echo "<td class='starDisplay'>".$ratingDiv."</td>";
													echo "<td contenteditable class='newRating'>".$row['rating']."</td>";
													echo "<td class='text-center'><button class='empUpdateBtn' data-empid='".$row['empId']."' data-crating='".$row['rating']."'>Update</button></td>";
													echo "</tr>";
													$i++;
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						
						<div id="tab-2" class="tab-pane">
							<div class="panel-body" style="padding: 0px; padding-top: 25px;">
								<div class="table-responsive">
									<table id="branchRatingTable" class="table">
										<thead>
											<tr class="theadRow">
												<th class="text-center">#</th>
												<th>Branch_Id</th>
												<th>Branch_Name</th>
												<th>City</th>
												<th>State</th>
												<th>Current_Rating</th>
												<th width="100px">Stars</th>
												<th>New_Rating</th>
												<th class="text-center">Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												$branchList = mysqli_query($con, "SELECT branchId, branchName, city, state, rating
												FROM branch
												WHERE status=1 AND branchId != 'AGPL000' ".$state."
												ORDER BY branchId");
												$i = 1;
												while($row = mysqli_fetch_assoc($branchList)){
													$rating = $row['rating']/2;
													$ratingDiv = "";
													while($rating > 0.5){
														$ratingDiv .= "<div class='rating-star-full'></div>";
														$rating--;
													}
													if($rating == 0.5){
														$ratingDiv .= "<div class='rating-star-half'></div>";
													}
													echo "<tr>";
													echo "<td class='text-center'>".$i."</td>";
													echo "<td>".$row['branchId']."</td>";
													echo "<td>".$row['branchName']."</td>";
													echo "<td>".$row['city']."</td>";
													echo "<td>".$row['state']."</td>";
													echo "<td class='curRating'>".$row['rating']."</td>";
													echo "<td class='starDisplay'>".$ratingDiv."</td>";
													echo "<td contenteditable class='newRating'>".$row['rating']."</td>";
													echo "<td class='text-center'><button class='branchUpdateBtn' data-branchid='".$row['branchId']."' data-crating='".$row['rating']."'>Update</button></td>";
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
			</div>
		</div>
	</div>
	<?php include("footer.php"); ?>
	<script src="vendor/toastr/build/toastr.min.js"></script>
	<script>
		$(document).ready(function() {
			
			$('#employeeRatingTable').DataTable( {
				paging: false,
				"ajax": '',
				dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
				"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
				buttons: [
				{ extend: 'copy', className: 'btn-sm' },
				{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
				{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
				{ extend: 'print', className: 'btn-sm' }
				]
			});
			
			$('#branchRatingTable').DataTable( {
				paging: false,
				"ajax": '',
				dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
				"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
				buttons: [
				{ extend: 'copy', className: 'btn-sm' },
				{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
				{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
				{ extend: 'print', className: 'btn-sm' }
				]
			});
			
			toastr.options = {
				"debug": false,
				"newestOnTop": false,
				"positionClass": "toast-top-center",
				"closeButton": true,
				"toastClass": "animated fadeInDown",
			};
			
			const ratingInput = document.querySelectorAll(".newRating");
			ratingInput.forEach((input)=>{
				input.addEventListener("input", ()=>{ colorRow(input) });
				input.addEventListener("focus", ()=>{ colorRow(input) });
				input.addEventListener("blur", ()=>{ uncolorRow(input) });
			})
			function colorRow(input){
				const parent = input.parentElement;
				parent.classList.add("editing-row");
			}
			function uncolorRow(input){
				const parent = input.parentElement;
				parent.classList.remove("editing-row");
			}
			
			
			// EMPLOYEE RATING UPDATE
			const empUpdateBtn = document.querySelectorAll(".empUpdateBtn");
			empUpdateBtn.forEach((btn)=>{
				btn.addEventListener("click", async (e)=>{
					const parent = btn.parentElement.parentElement;
					const empId = btn.dataset.empid;
					const ratingInput = parent.querySelector(".newRating");
					
					let newRating = ratingInput.textContent;
					newRating = newRating.replace(/[\n\r\s\t]+/g, '');
					newRating = newRating.replace(/[^0-9.]/g, '');
					newRating = Math.floor(+newRating);
					
					if(newRating >=0 && newRating <= 10){
						const requestData = {
							employeeRatingUpdate: true,
							employeeid: empId,
							rating: newRating
						};
						const response = await fetch("ratingUpdate.php", {
							method: "post",
							headers: {
								"Content-Type": "application/json"
							},
							body: JSON.stringify(requestData)
						})
						const result = await response.json();
						if(result.error){
							ratingInput.textContent = btn.dataset.crating;
							toastr.error('Error - Server could not process your request');
							return;
						}
						
						toastr.success('Success - Succesfully Updated the ranking');
						
						ratingInput.textContent = result.rating;
						
						const preRating = parent.querySelector(".curRating");
						preRating.textContent = result.rating;
						
						const displayStar = parent.querySelector(".starDisplay");
						let starDiv = "";
						let star = result.rating / 2;
						while(star > 0.5){
							starDiv += "<div class='rating-star-full'></div>"
							star--;
						}
						if(star == 0.5){
							starDiv += "<div class='rating-star-half'></div>"
						}
						displayStar.innerHTML = starDiv;
						
					}
					else{	
						ratingInput.textContent = btn.dataset.crating;
						toastr.warning('Warning - Give proper rating, should be between 1-10');
					}
					
				})
			})
			
			
			// BRANCH RATING UPDATE
			const branchUpdateBtn = document.querySelectorAll(".branchUpdateBtn");
			branchUpdateBtn.forEach((btn)=>{
				btn.addEventListener("click", async (e)=>{
					const parent = btn.parentElement.parentElement;
					const branchid = btn.dataset.branchid;
					const ratingInput = parent.querySelector(".newRating");
					
					let newRating = ratingInput.textContent;
					newRating = newRating.replace(/[\n\r\s\t]+/g, '');
					newRating = newRating.replace(/[^0-9.]/g, '');
					newRating = Math.floor(+newRating);
					
					if(newRating >=0 && newRating <= 10){
						const requestData = {
							branchRatingUpdate: true,
							branchid: branchid,
							rating: newRating
						};
						const response = await fetch("ratingUpdate.php", {
							method: "post",
							headers: {
								"Content-Type": "application/json"
							},
							body: JSON.stringify(requestData)
						})
						const result = await response.json();
						if(result.error){
							ratingInput.textContent = btn.dataset.crating;
							toastr.error('Error - Server could not process your request');
							return;
						}
						
						toastr.success('Success - Succesfully Updated the ranking');
						
						ratingInput.textContent = result.rating;
						
						const preRating = parent.querySelector(".curRating");
						preRating.textContent = result.rating;
						
						const displayStar = parent.querySelector(".starDisplay");
						let starDiv = "";
						let star = result.rating / 2;
						while(star > 0.5){
							starDiv += "<div class='rating-star-full'></div>"
							star--;
						}
						if(star == 0.5){
							starDiv += "<div class='rating-star-half'></div>"
						}
						displayStar.innerHTML = starDiv;
						
					}
					else{	
						ratingInput.textContent = btn.dataset.crating;
						toastr.warning('Warning - Give proper rating, should be between 1-10');
					}
					
				})
			})
			
		})
	</script>					
