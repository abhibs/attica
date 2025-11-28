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
	else if($type=='Expense Team'){
		include("header.php");
		include("menuexpense.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
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
	th, td{
	text-align: center;
	}
</style>
<div id="wrapper">
	<div class="row content">   
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i style="color:#990000" class="fa fa-wifi"></i> Internet Renewal</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example1" class="table table-hover table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch Id</th>
									<th>Branch Name</th>
									<th>ISP Provider</th>
									<th>Renewal-Date</th>
									<th>Days Remaining</th>
									<th>Status</th>
									<th>Next Renewal Date</th>
									<th>Update</th>
								</tr>
							</thead>
							<tbody>
								<?php
									function dateDiff ($d1, $d2) {
										return round((strtotime($d1) - strtotime($d2))/86400);
									}
									
									$i = 1;
									$date = date('Y-m-d');
									$query = "SELECT r.id, r.branchId, r.internet, r.ISP_provider, b.branchName, b.status
									FROM renewal r, branch b
									WHERE r.branchId = b.branchId AND b.status=1 AND b.branchId!='AGPL000'";
									$sql  = mysqli_query($con, $query);
									while($row = mysqli_fetch_assoc($sql)){
										$diff = ($row['internet'] != '') ? dateDiff($row['internet'], $date) : '';
										$str = '';
										if($diff !== ''){
											if($diff <= 0){
												$str = "<span class='badge badge-danger'>Expired</span>";	
											}
											else if($diff > 0 && $diff <= 3){
												$str = "<span class='badge badge-warning'>Less Than 3 Days</span>";
											}
										}
										echo "<tr>";
										echo "<td>".$i."</td>";
										echo "<td>".$row['branchId']."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td>".$row['ISP_provider']."</td>";
										echo "<td class='display-date'>".$row['internet']."</td>";
										echo "<td class='display-diff'>".$diff."</td>";
										echo "<td class='display-status'>".$str."</td>";
										echo "<td><input type='date' name='renewal-date'></td>";
										echo "<td><button onclick=updateRenewalDate(this) data-id=".$row['id']."><span class='fa_Icon fa fa-check-square-o'></span></button></td>";
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
		function updateRenewalDate(button){
			const row = button.parentNode.parentNode;
			const displayDate = row.querySelector('.display-date');
			const displayDiff = row.querySelector('.display-diff');
			const displayStatus = row.querySelector('.display-status');
			
			const id = button.dataset.id;
			const date = row.querySelector('input').value;
			
			if(id != '' && date != '') {
				$.ajax({
					type: "POST",
					url: "edit.php",
					data: {internetRenewal:"true", id:id, date:date},
					dataType:'JSON',
					success: function(response){
						if(response.status == "0"){
							let diff = parseInt(response.diff);
							displayDate.innerHTML = date;
							displayDiff.innerHTML = diff;
							if(diff <= 0){
								displayStatus.innerHTML = "<span class='badge badge-danger'>Expired</span>";
							}
							else if(diff > 0 && diff <= 3){
								displayStatus.innerHTML = "<span class='badge badge-warning'>Less Than 3 Days</span>";
							}
							else{
								displayStatus.innerHTML = "";
							}
						}
						else if(response.status == "1"){
							window.alert('Something Went Wrong with Updating');
						}
						else if(response.status == "2"){
							window.alert('Reload your page and try again')
						}
						else{
							console.log(response);
						}
					}
				});
			}
			else{
				window.alert('Please Select the Renewal Date');
			}
		}
	</script>
<?php include("footer.php");?>