<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=="Master"){
        include("header.php");
    	include("menumaster.php");
	}
	else if($type=='ITMaster'){
		include("header.php");
		include("menuItMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
?>
<style>
	#wrapper{
	background-color: #E3E3E3;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:7px;	
	padding: 20px;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}
	.fa_Icon {
	color: #123C69;
	font-size: 20px;
	}
	.btn-danger{
	background-color: #990000;
	}
	button{
	border: none;
	background-color: transparent;
	}
	input[type="date"]{
	border-radius: 3px;
	padding: 7px;
	color: grey;
	border: 1px solid grey;
	}
	
	.datepicker-toggle{
	display: inline-block;
	position: relative;
	width: 18px;
	height: 19px;
	color: #123c69;
	float: right;
	}
	.datepicker-input {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	opacity: 0;
	cursor: pointer;
	box-sizing: border-box;
	}
	.datepicker-input::-webkit-calendar-picker-indicator {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	cursor: pointer;
	}
	.dropdown-menu {
    overflow: hidden;
	}
	
	.select-toggle-button{
	width: 100%;
	padding: 10px;
	border: none;
	background-color: transparent;
	}
	.table > tbody > tr > .table-td-padding-zero{
	padding: 0px;
	}
	
	.table-row-expired{
	background-color: #ffe5e4;
	}
	.table-row-warning{
	background-color: #ffffa6;
	}
	.table-row-processing{
	background-color: #ebebff;
	}
	.table-row-success{
	background-color: #d7ffd7;
	}
	.table-row-info{
	background-color: #daf3f2;
	}
	.table-row-dummy{
	background-color: transaparent;
	}
	
	.panel-heading button{
	box-shadow: 5px 5px 5px #999;
	border-radius:7px;
	margin-right: 10px;
	cursor: pointer;
	}
	
	.table-row-hide{
	display: none;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="row" style="padding-right: 10px; padding-left: 10px;">
						<div class="col-md-3">
							<h3 class="text-success"><i style="color:#990000" class="fa fa-clipboard"></i> Shop License Details</h3>
						</div>
						<div class="col-md-9 text-right">
							<button class="btn data-sort" data-value="All" type="button" style="background-color: #f5f5f5" title="Display All Data"><i class="fa fa-star"></i> <br/>All Data</button>
							<button class="btn data-sort" data-value="Active" type="button" style="background-color: #f5f5f5" title="Currently Active"><i class="fa fa-toggle-on"></i> <br/>Active</button>
							<button class="btn table-row-info data-sort" data-value="Unknown" type="button" title="Validity Is Required"><i class="fa fa-question"></i> <br/>Unknown</button>
							<button class="btn table-row-success data-sort" data-value="Life Time" type="button" title="Life Time Validity"><i class="fa fa-check"></i> <br/>Life Time</button>
							<button class="btn table-row-processing data-sort" data-value="Processing" type="button" title="Processing"><i class="fa fa-spinner" ></i> <br/>Processing</button>
							<button class="btn table-row-warning data-sort" data-value="Warning" type="button" title="Less Than 15 Days For Expiry Date"><i class="fa fa-hourglass-half"></i> <br/>Warning</button>
							<button class="btn table-row-expired data-sort" data-value="Expired" type="button" title="Expired"><i class="fa fa-warning"></i> <br/>Expired</button>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="customTable" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch Id</th>
									<th>Branch Name</th>
									<th>State</th>
									<th>Renewal Date</th>
									<th>Days Remaining</th>
									<th>Status</th>
									<th>Validity</th>
								</tr>
							</thead>
							<tbody>
								<?php
									function dateDiff ($d1, $d2) {
										return round((strtotime($d1) - strtotime($d2))/86400);
									}
									
									$i = 1;
									$date = date('Y-m-d');
									$diff = '';
									$status = '';
									$class = '';
									
									$query = mysqli_query($con, "SELECT r.id, r.branchId, r.shop_license, r.shop_license_validity, b.branchName, b.state
									FROM renewal r, branch b
									WHERE r.branchId = b.branchId AND b.status=1 AND b.branchId!='AGPL000'");
									
									while($row = mysqli_fetch_assoc($query)){
										
										$diff = ($row['shop_license'] == '' || $row['shop_license_validity'] == 'Life Time') ? "" : dateDiff($row['shop_license'], $date);	
										if($row['shop_license_validity'] == 'Processing'){
											$status = "Processing";
											$class = "table-row-processing";
										}
										else if($row['shop_license_validity'] == 'Life Time'){
											$status = "Life Time";
											$class = "table-row-success";
										}
										else if($row['shop_license_validity'] == 'One Time'){
											if($diff === ''){
												$status = 'Unknown';
												$class = 'table-row-info';
											}
											else{
												if($diff < 15 && $diff >= 0){
													$status = 'Warning';
													$class = 'table-row-warning';
												}
												else if($diff < 0){
													$status = 'Expired';
													$class = 'table-row-expired';
												}
												else{
													$status = 'Active';
													$class = '';
												}
											}
										}
										else{
											if($diff != ''){
												$status = 'Unknown';
												$class = 'table-row-info';
											}
											else{
												$status = "Pending";
												$class = '';
											}
										}
										
										echo "<tr id='table_row_".$row['id']."' class='".$class."'>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['branchId']."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['state']."</td>";
										echo "<td><span class='td-show-date'>".$row['shop_license']."</span>
										<span class='datepicker-toggle'>
										<span class='datepicker-toggle-button fa fa-calendar'></span>
										<input type='date' class='datepicker-input' data-id=".$row['id'].">
										</span>
										</td>";
										echo "<td class='text-right td-show-diff'>".$diff."</td>";
										echo "<td class='text-center td-show-status'>".$status."</td>";
										echo "<td class='text-left table-td-padding-zero'>
										<select class='select-toggle-button' data-id=".$row['id'].">
										<option value='' selected disabled>".$row['shop_license_validity']."</option>
										<option value='One Time'>One Time</option>
										<option value='Life Time'>Life Time</option>
										<option value='Processing'>Processing</option>
										</select>
										</td>";
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
	<div style="clear:both"></div>
	<script>
		
	</script>
	<?php include("footer.php");?>
	<script>	
		(function(){		
			
			/*    DATE UPDATE    */
			const dateInput = document.querySelectorAll('.datepicker-input');
			dateInput.forEach((item, i)=>{
				item.addEventListener('change',(event)=>{
					const selectedDate = event.target.value;
					
					const rowId = item.dataset.id;
					const row = document.querySelector('#table_row_'+rowId);
					
					const dateDisplay = row.querySelector('.td-show-date');
					const diffDisplay = row.querySelector('.td-show-diff');
					const statusDisplay = row.querySelector('.td-show-status');
					const validity = row.querySelector('.select-toggle-button');
					
					event.target.value = '';
					
					$.ajax({
						type: "POST",
						url: "renewalUpdate.php",
						data: {shopLicense_renewalUpdate:"true", update:"date", id:rowId, date:selectedDate, valid:validity.options[validity.selectedIndex].text},
						dataType:'JSON',
						success: function(response){
							if(response.result == "0"){
								dateDisplay.textContent = selectedDate;
								diffDisplay.textContent = (response.diff != '') ? parseInt(response.diff) : '';
								statusDisplay.textContent = response.status;
								row.removeAttribute("class");
								row.classList.add(response.color);
							}
							else{
								window.alert("Something Went Wrong...");
							}
						}
					});
					
				});
			});
			
			
			/*    VALIDITY UPDATE    */
			const selectValidity = document.querySelectorAll('.select-toggle-button'); console.log(selectValidity);
			selectValidity.forEach((item, i)=>{
				item.addEventListener('change', (event)=>{
					const selectedValidity = event.target.value;
					
					const rowId = item.dataset.id;
					const row = document.querySelector('#table_row_'+rowId);
					
					const dateDisplay = row.querySelector('.td-show-date');
					const diffDisplay = row.querySelector('.td-show-diff');
					const statusDisplay = row.querySelector('.td-show-status'); console.log(rowId, dateDisplay.textContent)
					
					$.ajax({
						type: "POST",
						url: "renewalUpdate.php",
						data: {shopLicense_renewalUpdate:"true", update:"validity", id:rowId, date:dateDisplay.textContent, valid:selectedValidity},
						dataType:'JSON',
						success: function(response){
							if(response.result == "0"){
								diffDisplay.textContent = (response.diff != '') ? parseInt(response.diff) : '';
								statusDisplay.textContent = response.status;
								row.removeAttribute("class");
								row.classList.add(response.color); 
								table.api().cell(statusDisplay).data(response.status);
							}
							else{
								window.alert("Something Went Wrong...");
							}
						}
					});
				});
			});
			
			/*   DATA SORT   */
			const table = $('#customTable').dataTable({
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
			
			const sortButtons = document.querySelectorAll('.data-sort');
			const allRows = document.querySelectorAll('table tbody tr');
			sortButtons.forEach((item, i)=>{
				item.addEventListener('click', (event)=>{
					const choice = item.dataset.value;
					if(choice == 'All'){
						table.api().columns().search('').draw();
					}
					else{
						table.api().column(6).search(choice).draw();
					}
				});
			});	
			
		})();
	</script>		