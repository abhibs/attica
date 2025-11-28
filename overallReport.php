<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}	
	else if ($type == 'Software') {
		include("header.php");
		include("menuSoftware.php");
	}
	else if ($type == 'Zonal') {
		include("header.php");
		include("menuZonal.php");
	}
	else if($type=='SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else if ($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if($type == 'BD') {
		include("header.php");
		include("menubd.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	
	if(isset($_GET['getReport'])){
		$branch = $_GET['branch'];
		$from = $_GET['from'];
		$to = $_GET['to'];
		
		$state = "";
		switch($branch){
		    case 'All_Karnataka': $state = "b.state IN ('Karnataka') AND b.status=1 AND b.branchId!='AGPL000'"; break;
			case 'All_APT': $state = "b.state IN ('Telangana', 'Andhra Pradesh') AND b.status=1"; break;
			case 'All_Tamilnadu': $state = "b.state IN ('Tamilnadu', 'Pondicherry') AND b.status=1"; break;
		    
			case "Bengaluru": $state = " b.city = 'Bengaluru' AND b.status=1 AND b.branchId!='AGPL000'"; break;
			case "Karnataka": $state = "b.city != 'Bengaluru' AND state IN ('Karnataka') AND b.status=1"; break;
			
			case "Chennai": $state = " b.city = 'Chennai' AND b.status=1"; break;
			case "Tamilnadu": $state = "b.city != 'Chennai' AND state IN ('Tamilnadu', 'Pondicherry') AND b.status=1"; break;
			
			case "Hyderabad": $state = " b.city = 'Hyderabad' AND b.status=1"; break;
			case "APT": $state = "b.city != 'Hyderabad' AND state IN ('Telangana', 'Andhra Pradesh') AND b.status=1"; break;
                        
                        case 'All_Branches':
			$state = "b.status=1";
			break;
		}
		
		$query = "SELECT b.branchName,
		(COALESCE(billed, 0) + COALESCE(enquiry, 0)) as walkin,
		
		COALESCE(billed, 0) AS billed,
		ROUND((COALESCE(billed, 0)/(DATEDIFF('".$from."','".$to."') + 1)),2) AS per_day_billed,
		COALESCE(ROUND(((COALESCE(billed, 0)/(COALESCE(billed, 0) + COALESCE(enquiry, 0))) * 100),2), 0) as billed_cog,
		ROUND(gold_netW, 2) AS gold_netW,
		ROUND(silver_netW, 2) AS silver_netW,
		
		COALESCE(enquiry, 0) AS enquiry,
		ROUND((COALESCE(enquiry, 0)/(DATEDIFF('".$from."','".$to."') + 1)),2) AS per_day_enquiry,
		COALESCE(enquiry_physical, 0) AS enquiry_physical,
		COALESCE(enquiry_release, 0) AS enquiry_release,
		ROUND(COALESCE(enquiry_grossW, 0), 2) AS enquiry_grossW,
		ROUND(((COALESCE(enquiry, 0)/(COALESCE(billed, 0) + COALESCE(enquiry, 0))) * 100),2) as enquiry_cog
		
		FROM
		
		(SELECT branchId, COUNT(*) AS billed,
		SUM(CASE WHEN metal='Gold' THEN netW ELSE 0 END) as gold_netW,
		SUM(CASE WHEN metal='Silver' THEN netW ELSE 0 END) as silver_netW
		FROM trans t
		WHERE date BETWEEN '".$from."' AND '".$to."' AND status='Approved'
		GROUP BY branchId) t
		
		RIGHT JOIN
		
		branch b
		
		ON t.branchId = b.branchId
		
		LEFT JOIN
		
		(SELECT branchId, COUNT(*) AS enquiry,
		SUM(CASE WHEN gold='physical' THEN 1 ELSE 0 END) AS enquiry_physical,
		SUM(CASE WHEN gold='release' THEN 1 ELSE 0 END) AS enquiry_release,
		SUM(gwt) as enquiry_grossW
		FROM walkin
		WHERE date BETWEEN '".$from."' AND '".$to."' AND status!='Rejected' AND issue!='Rejected' AND metal='Gold'
		GROUP BY branchId) w
		
		ON b.branchId = w.branchId
		
		WHERE ".$state."
		ORDER BY billed_cog DESC";
		
		$sql = mysqli_query($con, $query);
	}
	
?>
<style>
	#wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 18px;
	color: #123C69;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.text-success {
	color: #123C69;
	text-transform: uppercase;
	font-weight: bold;
	font-size: 12px;
	}
	.btn-primary {
	background-color: #123C69;
	}
	.theadRow {
	text-transform: uppercase;
	background-color: #123C69 !important;
	color: #f2f2f2;
	font-size: 10px;
	}
	.btn-success {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 11px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
	}
	.fa_Icon {
	color: #990000;
	}
	.modal-title {
	font-size: 13px;
	font-weight: 600;
	color: #123C69;
	text-transform: uppercase;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>

<div id="wrapper">
	<div class="row content">
		
		<div class="col-sm-4">
			<h3 class="text-success" style="margin-left: 10px;"> <i class="fa_Icon fa fa-money"></i> Overall Monthly Report</h3>
		</div>
		<form action="" method="GET">
			<div class="col-sm-3">
				<input list="branchList" class="form-control" name="branch" placeholder="City or State" required>
				<datalist id="branchList">
					<?php if($type == "Master" || $type == "Software" || $type == "ZonalMaster"){ ?>
						<option value="All_Karnataka" label="All_Karnataka"></option>
						<option value="All_APT" label="All_APT"></option>
						<option value="All_Tamilnadu" label="All_Tamilnadu"></option>
						<option value="Bengaluru" label="Bengaluru"></option>
						<option value="Karnataka" label="Karnataka"></option>
						<option value="Chennai" label="Chennai"></option>
						<option value="Tamilnadu" label="Tamilnadu"></option>
						<option value="Hyderabad" label="Hyderabad"></option>
						<option value="APT" label="APT"></option>
                                                <option value="All_Branches" label="All_Branches"></option>
						<?php 
						}
						else if($type == "Zonal"){ 
							if($_SESSION['branchCode'] == 'AP-TS'){
								echo '<option value="All_APT" label="All_APT"></option>';
								echo '<option value="Hyderabad" label="Hyderabad"></option>';
								echo '<option value="APT" label="APT"></option>';
							}
							else if($_SESSION['branchCode'] == 'KA'){
								echo '<option value="All_Karnataka" label="All_Karnataka"></option>';
								echo '<option value="Bengaluru" label="Bengaluru"></option>';
								echo '<option value="Karnataka" label="Karnataka"></option>';
							}
							else if($_SESSION['branchCode'] == 'TN'){
								echo '<option value="All_Tamilnadu" label="All_Tamilnadu"></option>';
								echo '<option value="Chennai" label="Chennai"></option>';
								echo '<option value="Tamilnadu" label="Tamilnadu"></option>';
							}
						}
						else if($type == "SubZonal"){
							if($_SESSION['branchCode'] == 'AP-TS'){
								echo '<option value="All_APT" label="All_APT"></option>';
								echo '<option value="Hyderabad" label="Hyderabad"></option>';
								echo '<option value="APT" label="APT"></option>';
							}
							else if($_SESSION['branchCode'] == 'KA'){
								echo '<option value="All_Karnataka" label="All_Karnataka"></option>';
								echo '<option value="Bengaluru" label="Bengaluru"></option>';
								echo '<option value="Karnataka" label="Karnataka"></option>';
							}
							else if($_SESSION['branchCode'] == 'TN'){
								echo '<option value="All_Tamilnadu" label="All_Tamilnadu"></option>';
								echo '<option value="Chennai" label="Chennai"></option>';
								echo '<option value="Tamilnadu" label="Tamilnadu"></option>';
							}
						}
					?>				
				</datalist>
			</div>
			<div class="col-sm-4">
				<div class="input-group">
					<input type="date" class="form-control" name="from" required>
					<span class="input-group-addon">to</span>
					<input type="date" class="form-control" name="to" required>
				</div>
			</div>
			<div class="col-sm-1">
				<button class="btn btn-success btn-block" name="getReport"><span style="color:#ffcf40" class="fa fa-table"></span> Get</button>
			</div>
		</form>		
		
		<div class="col-lg-12">
			<div class="hpanel">
				
				<div class="panel-body">
					<div class="col-sm-12 table-responsive">
						<table id="reportTable" class="table table-bordered">
							<caption><b><?php if(isset($_GET['getReport'])){ echo $branch.", ".$from." - ".$to;  } ?></b></caption>
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Walkin</th>
									<th>Billed</th>
									<th>Billed/Day</th>
									<th>Gram (%)</th>
									<th>Billed_COG</th>
									<th>Gold_NetW</th>
									<th>Silver_NetW</th>
									<th>Enquiry</th>
									<th>Enquiry/Day</th>
									<th>Physical</th>
									<th>Release</th>
									<th>grossW</th>
									<th>Enquiry(%)</th>
									<th>Enq_COG</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if(isset($_GET['getReport'])){
										$walkin = 0;
										$billed = 0;
										$billed_day = 0;
										$gold_netw = 0;
										$silver_netw = 0;
										
										$enquiry = 0;
										$enq_day = 0;
										$physical = 0;
										$release = 0;
										$enq_grossw = 0;
										
										$i = 1;
										while ($row = mysqli_fetch_assoc($sql)) {
											echo "<tr>";
											echo "<td>" . $i . "</td>";
											echo "<td>" . $row['branchName'] . "</td>";
											echo "<td>" . $row['walkin'] . "</td>";
											echo "<td>" . $row['billed'] . "</td>";
											echo "<td>" . $row['per_day_billed'] . "</td>";
											$grossW = ($row['enquiry_grossW'] != '') ? $row['enquiry_grossW'] : 0;
											$goldNetW = ($row['gold_netW'] != '') ? $row['gold_netW'] : 0;
											$billed_percent = ($goldNetW + $grossW) > 0 ? round(($goldNetW / ($goldNetW + $grossW)) * 100, 2): 0;
										
											echo "<td style='background-color: #cef5f5;'>" . $billed_percent . "</td>";
											echo "<td style='background-color: #bfe8bd;' >" . $row['billed_cog'] . "</td>";
											echo "<td>" . $row['gold_netW'] . "</td>";
											echo "<td>" . $row['silver_netW'] . "</td>";
											echo "<td>" . $row['enquiry'] . "</td>";
											echo "<td>" . $row['per_day_enquiry'] .  "</td>";
											echo "<td>" . $row['enquiry_physical'] . "</td>";
											echo "<td>" . $row['enquiry_release'] . "</td>";
											echo "<td>" . $row['enquiry_grossW'] . "</td>";
											$enquiry_percent = ($row['enquiry_grossW'] > 0 || $row['gold_netW'] > 0) ? round(($row['enquiry_grossW'] / ($row['gold_netW'] + $row['enquiry_grossW'])) * 100, 2): 0;

											echo "<td style='background-color: #cef5f5;'>" . $enquiry_percent . "</td>";
											echo "<td>" . $row['enquiry_cog'] .  "</td>";
											echo "</tr>";
											$i++;
											
											$walkin += ($row['walkin'] != "") ? $row['walkin'] : 0;
											$billed += ($row['billed'] != "") ? $row['billed'] : 0;
											$billed_day += ($row['per_day_billed'] != "") ? $row['per_day_billed'] : 0;
											$gold_netw += ($row['gold_netW'] != "") ? $row['gold_netW'] : 0;
											$total_billed_percent = ($gold_netw + $enq_grossw) > 0
											? round(($gold_netw / ($gold_netw + $enq_grossw)) * 100, 2): 0;
										
											$silver_netw += ($row['silver_netW'] != "") ? $row['silver_netW'] : 0;
											
											$enquiry += ($row['enquiry'] != "") ? $row['enquiry'] : 0;
											$enq_day += ($row['per_day_enquiry'] != "") ? $row['per_day_enquiry'] : 0;
											$physical += ($row['enquiry_physical'] != "") ? $row['enquiry_physical'] : 0;
											$release += ($row['enquiry_release'] != "") ? $row['enquiry_release'] : 0;
											$enq_grossw += ($row['enquiry_grossW'] != "") ? $row['enquiry_grossW'] : 0;
											$total_enquiry_percent = ($gold_netw + $enq_grossw) > 0 ? round(($enq_grossw / ($gold_netw + $enq_grossw)) * 100, 2): 0;
										}							
										
										echo "<tr class='theadRow'>";
										echo "<td style='color: #123C69'>" . $i . "</td>";
										echo "<td class='text-center'>Total</td>";
										echo "<td>" . $walkin . "</td>";
										echo "<td>" . $billed . "</td>";
										echo "<td>" . $billed_day . "</td>";
										echo "<td>" . $total_billed_percent . "</td>";
										if($walkin != 0 ) echo "<td>".round(($billed/$walkin)*100, 2)."</td>";  else  echo "<td>0</td>";
										echo "<td>" . $gold_netw . "</td>";
										echo "<td>" . $silver_netw . "</td>";
										echo "<td>" . $enquiry . "</td>";
										echo "<td>" . $enq_day . "</td>";
										echo "<td>" . $physical .  "</td>";
										echo "<td>" . $release . "</td>";
										echo "<td>" . $enq_grossw . "</td>";
										echo "<td>" . $total_enquiry_percent . "</td>";
										if($walkin != 0 ) echo "<td>".round(($enquiry/$walkin)*100, 2)."</td>";  else  echo "<td>0</td>";
										echo "</tr>";
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
		$('#reportTable').DataTable( {
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
		} );
	</script>		

