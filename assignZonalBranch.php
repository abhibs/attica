<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type=$_SESSION['usertype'];
if($type=='Master'){
	include("header.php");
	include("menumaster.php");
}
else if($type=='Software'){
	include("header.php");
	include("menuSoftware.php");
}
else{
	include("logout.php");
}
include("dbConnection.php");

if(isset($_GET['state'])){

	if($_GET['state'] == 'karnataka'){
		$zonal_state = "KA";
		$state_str = "('Karnataka')";
	}
	else if($_GET['state'] == 'tamilnadu'){
		$zonal_state = "TN";
		$state_str = "('Tamilnadu', 'Pondicherry')";
	}
	else if($_GET['state'] == 'apt'){
		$zonal_state = "AP-TS";
		$state_str = "('Andhra Pradesh', 'Telangana')";
	}

	$total_data = [];
	$zonal_data = [];
	$branch_zonal_data = [];
	$zonalOptions = "<option value='' selected disabled>Zonal</option>";

	// ZONAL DATA
	$zonal_query = mysqli_query($con, "SELECT u.employeeId, e.name 
		FROM users u JOIN employee e ON u.employeeId=e.empId
		WHERE u.type='Zonal' AND u.branch='$zonal_state'");
	while($row = mysqli_fetch_assoc($zonal_query)){
		$zonal_data[$row['employeeId']] = $row['name'];
		$branch_zonal_data[$row['employeeId']] = [];
		$zonalOptions .= "<option value=".$row['employeeId'].">".$row['name']."</option>";
	}

	// BRANCH DATA
	$branch_query = mysqli_query($con, "SELECT branchId, branchName, city, ezviz_vc 
		FROM branch
		WHERE state IN $state_str AND status=1 AND branchId != 'AGPL000'
		ORDER BY branchName ASC");
	while($row = mysqli_fetch_assoc($branch_query)){
		$total_data[$row['branchId']] = $row;
		if($row['ezviz_vc'] != '' && array_key_exists($row['ezviz_vc'], $branch_zonal_data)){
			$branch_zonal_data[$row['ezviz_vc']][] = $row['branchName'];
		}
	}

	// print_r($zonal_data);
	// print_r($branch_zonal_data);
	// print_r($total_data);

}
?>
<style>
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 18px;
		color:#123C69;
	}
	.hpanel .panel-body {
		box-shadow: 10px 15px 15px #999;
		border: 1px solid #edf2f9;
		background-color: #f5f5f5;
		border-radius:3px;
		padding: 20px;
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
		color:#990000;
	}
	.table-responsive .row{
		margin: 0px;
	}
	.selected-text{
		color: #990000 !important;
	}
	.text-white{
		color: #ffffff !important;
		font-weight: 700 !important;
	}
</style>
<div id="wrapper">
	<div class="row content">

		<div class="col-lg-12" style="margin-bottom: 25px;">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-lg-6">
						<h3>Zonal Branch - (Assign / Edit)</h3>
					</div>
					<div class="col-lg-2">
						<a href="assignZonalBranch.php?state=karnataka">
							<h3 <?php echo ($_GET['state'] == 'karnataka') ? "class='selected-text'" : ""; ?>>Karnataka</h3>
						</a>
					</div>
					<div class="col-lg-2">
						<a href="assignZonalBranch.php?state=tamilnadu">
							<h3 <?php echo ($_GET['state'] == 'tamilnadu') ? "class='selected-text'" : ""; ?>>Tamilnadu</h3>
						</a>
					</div>
					<div class="col-lg-2">
						<a href="assignZonalBranch.php?state=apt">
							<h3 <?php echo ($_GET['state'] == 'apt') ? "class='selected-text'" : ""; ?>>Andhra & Telangana</h3>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead class="theadRow">
								<tr>
									<th>#</th>							
									<th>Branch_Name</th>
									<th>Zonal</th>
									<th>Select</th>
									<th width="20px">Change</th>
								</tr>
							</thead>
							<tbody  >
								<?php
								$i = 1;
								foreach($total_data as $key=>$value){
									echo "<tr id='".$value['branchId']."'>";
									echo "<td>".$i."</td>";								
									echo "<td>".$value['branchName']."</td>";
									echo "<td class='zonal-name' data-zonalid='".$value['ezviz_vc']."' >".$zonal_data[$value['ezviz_vc']]."</td>";
									echo "<td><select class='form-control m-b zonal-select'>";
									echo $zonalOptions;
									echo "</select></td>";
									echo "<td><button data-branchid='".$value['branchId']."' data-branchname='".$value['branchName']."' class='btn btn-outline btn-primary text-white' onClick='updateZonal(this)' >ok</button></td>";
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

		<div class="col-lg-4">
			<?php foreach($branch_zonal_data as $key=>$value){ ?>
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-body">
							<div class="panel-heading">
								<?php echo $zonal_data[$key]; ?>	
								<span class="zonal-count-<?php echo $key ?>" style="float: right; background-color: purple; padding: 5px 10px; font-weight: 900; color: #ffffff;" >
									<?php echo count($branch_zonal_data[$key]); ?>
								</span>			
							</div>
							<table class="table table-bordered">							
								<tbody class="zonal-table-<?php echo $key ?>">
									<?php									
									foreach($value as $key2){ 
										echo "<tr>";										
										echo "<td>".$key2."</td>";									
										echo "</tr>";										
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

	</div>
	<?php include("footer.php"); ?>
	<script type="text/javascript">
		const zonalName = <?php echo json_encode($zonal_data); ?>;
		async function updateZonal(btn){
			const branchId = btn.dataset.branchid;
			const branchName = btn.dataset.branchname; console.log(branchName);

			const row = document.querySelector("#"+branchId);
			const zonalTd = row.querySelector(".zonal-name");
			const selectInput = row.querySelector(".zonal-select");

			const selectedZonalEmpId = selectInput.value;
			if(selectedZonalEmpId == ""){
				alert("Please Select A Zonal");
				return;
			}

			const form = new FormData();
			form.append("updateZonalBranch", true);
			form.append("branchId", branchId);
			form.append("zonalEmpId", selectedZonalEmpId);

			const response = await fetch("editAjax.php", {
				method: "POST",
				body: form
			});
			const result = await response.json();
			console.log(result);

			if(result.message == "success"){
				if(zonalTd.dataset.zonalid in zonalName){
					const prevZonalTable = document.querySelector(".zonal-table-"+zonalTd.dataset.zonalid);
					prevZonalTable.querySelectorAll("td").forEach((td, i)=>{
						if(td.textContent == branchName){
							td.parentElement.remove();
						}
					});

					const prevZonalCount = document.querySelector(".zonal-count-"+zonalTd.dataset.zonalid);
					prevZonalCount.textContent = +prevZonalCount.textContent - 1;
				}				

				const newZonalTable = document.querySelector(".zonal-table-"+selectedZonalEmpId);
				const tr = document.createElement("tr");
				const td = document.createElement("td");
				td.textContent = branchName;
				tr.append(td);
				newZonalTable.appendChild(tr);
				const newZonalCount = document.querySelector(".zonal-count-"+selectedZonalEmpId);
				newZonalCount.textContent = +newZonalCount.textContent + 1;

				zonalTd.textContent = zonalName[selectedZonalEmpId];
				zonalTd.dataset.zonalid = selectedZonalEmpId;			
			}
			else{
				alert("Something went wrong, Please try again later");
				return;
			}
		}
	</script>
