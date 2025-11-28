<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type=="Master"){
        include("header.php");
    	include("menumaster.php");
    }else if($type=='Software'){
	    include("header.php");
	    include("menuSoftware.php");
	}else if ($_SESSION['usertype'] == "Assets") {
	    include("header.php");
	    include("menuassets.php");		
	}else if ($_SESSION['usertype'] == "IT") {
	    include("header.php");
	    include("menuItMaster.php");		
	}else if ($_SESSION['usertype'] == "ITMaster") {
	    include("header.php");
	    include("menuItMaster.php");		
	}else{
        include("logout.php");
    }
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');
?>
<style>
	#wrapper{ background: #f5f5f5; }	
	#wrapper h3{ text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
	.text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
	.btn-primary{ background-color:#123C69; }
	.theadRow{ text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }	
	.dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
	.btn-success{ display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box; text-decoration:none; font-size:11px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa; background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative; }
	.fa_Icon{ color:#b8860b; }
	.row{ margin-left:0; margin-right:0; }
	tfoot{ background-color:#efeff5; }
	.text-inactive{ color:#006400; }
	.font-weight-bold{ font-weight:bold; }
	.text-active{ color:#006400; }
</style>

<div id="wrapper">
	<div class="content animate-panel">
		<div class="col-lg-11">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success">
						<i class="fa_Icon fa fa-balance-scale"></i>
						WEIGHING SCALE LICENSE RENEWAL & STONE RENEWAL
					</h3>
				</div>
				<div class="panel-body" style="border:5px solid #fff; border-radius:10px; padding:20px; box-shadow:rgba(50,50,93,0.25) 0 50px 100px -20px,rgba(0,0,0,0.3) 0 30px 60px -30px,rgba(10,37,64,0.35) 0 -2px 6px inset; background:#F5F5F5;">
					<div class="table-responsive">
						<table id="renewal-datatable" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>BRANCH DETAILS</th>
									<th style="text-align:center;">RENEWAL DATE</th>
									<th style="text-align:center;">RENEWAL STATUS</th>
									<th style="text-align:center;">RENEWAL ACTION</th>
									<th style="text-align:center;">STONE RENEWAL DATE</th>
									<th style="text-align:center;">STONE RENEWAL STATUS</th>
									<th style="text-align:center;">STONE RENEWAL ACTION</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$query = mysqli_query($con,"SELECT * FROM branch WHERE Status=1 ORDER BY renewal_date");
									$i=1;
									while($row = mysqli_fetch_assoc($query)){
										// license renewal
										$licDate = ($row["renewal_date"] && $row["renewal_date"]!="0000-00-00")
											? date("d-m-Y", strtotime($row["renewal_date"]))
											: "Not Available";
										$licStatus = ($row['renewal_status']==1)
											? "<span><i class='fa fa-check text-active'></i> <small><b>ACTIVE</b></small></span>"
											: "<span><i class='fa fa-remove text-danger'></i> <small><b>EXPIRED</b></small></span>";
										// stone renewal
										$stoneDate = ($row["stoneR_date"] && $row["stoneR_date"]!="0000-00-00")
											? date("d-m-Y", strtotime($row["stoneR_date"]))
											: "Not Available";
										$stoneStatus = ($row['stoneR_status']==1)
											? "<span><i class='fa fa-check text-active'></i> <small><b>ACTIVE</b></small></span>"
											: "<span><i class='fa fa-remove text-danger'></i> <small><b>EXPIRED</b></small></span>";

										echo "<tr>";
										// counter
										echo "<td>{$i}</td>";
										// branch details
										echo "<td>
											<h5 style='text-transform:uppercase;color:#123C69;'>{$row['branchName']}</h5>
											BRANCH ID: {$row['branchId']}<br>
											BRANCH STATUS: ".($row['Status']==1
												? "<i class='fa fa-check text-active'></i> ACTIVE"
												: "<i class='fa fa-remove text-danger'></i> CLOSED")."
										</td>";
										// license renewal date
										echo "<td style='text-align:center;'>
											<h5>{$licDate}</h5>
											<p><i class='fa fa-calendar'>
												<input type='date' id='renewalDate_{$row['id']}' class='renewal-date' name='renewalDate_{$i}'>
											</i></p>
										</td>";
										// license status
										echo "<td style='text-align:center;'>{$licStatus}</td>";
										// license action
										echo "<td style='text-align:center;'>
											<button class='btn btn-success' title='Update Renewal Date'
												onclick='updateLicense({$row['id']})'>
												<i class='fa fa-edit'></i>
											</button>
										</td>";
										// stone renewal date
										echo "<td style='text-align:center;'>
											<h5>{$stoneDate}</h5>
											<p><i class='fa fa-calendar'>
												<input type='date' id='stoneRDate_{$row['id']}' class='stone-renewal-date' name='stoneRDate_{$i}'>
											</i></p>
										</td>";
										// stone status
										echo "<td style='text-align:center;'>{$stoneStatus}</td>";
										// stone action
										echo "<td style='text-align:center;'>
											<button class='btn btn-success' title='Update Stone Renewal Date'
												onclick='updateStone({$row['id']})'>
												<i class='fa fa-edit'></i>
											</button>
										</td>";
										echo "</tr>";
										$i++;
									}
								?>
								<input type="hidden" id="un" value="<?php echo $_SESSION['login_username']; ?>">
								<input type="hidden" id="ty" value="<?php echo $_SESSION['usertype']; ?>">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
	<script>
		$(".renewal-date, .stone-renewal-date").val("");
		$('#renewal-datatable').DataTable({ responsive: true });

		function updateLicense(id){
			var renewal_date = $("#renewalDate_"+id).val();
			var un = $("#un").val();
			var ty = $("#ty").val();
			$.ajax({
				type: "POST",
				url: "edit.php",
				data: {
					action: 'license-renewal',
					id: id,
					renewal_date: renewal_date,
					un: un,
					ty: ty
				},
				success: function(response){
					if(response === "SUCCESS"){
						alert("License renewal updated successfully");
						location.reload();
					} else {
						alert("Error updating license renewal");
						location.reload();
					}
				}
			});
		}

		function updateStone(id){
			var stone_date = $("#stoneRDate_"+id).val();
			var un = $("#un").val();
			var ty = $("#ty").val();
			$.ajax({
				type: "POST",
				url: "edit.php",
				data: {
					action: 'stone-renewal',
					id: id,
					stoneR_date: stone_date,
					un: un,
					ty: ty
				},
				success: function(response){
					if(response === "SUCCESS"){
						alert("Stone renewal updated successfully");
						location.reload();
					} else {
						alert("Error updating stone renewal");
						location.reload();
					}
				}
			});
		}
	</script>
</div>

