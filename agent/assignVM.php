<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='VM-REG'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	include("../dbConnection.php");
	$date = date('Y-m-d');
	
	$language = [
	"English"=>[],
	"Kannada"=>[],
	"Tamil"=>[],
	"Telugu"=>[],
	"Hindi"=>[]
	];
	
	$agentSQL  = mysqli_query($con, "SELECT a.agentId, e.name, a.language, a.grade
	FROM vmagent a 
	LEFT JOIN employee e ON a.agentId=e.empId
	WHERE grade!='' AND language != ''");
	while($row = mysqli_fetch_assoc($agentSQL)){
		$langs = explode(",", $row['language']);
		foreach($langs as $l){
			$language[$l][$row['grade']][] = [$row['agentId'], $row['name']];
		}
	}
	// print_r($language);
	
?>
<style>
	.sp-input{
	border-radius:0px;
	margin: 0px;
	}
	.no-border-radius{
	border-radius:0px;
	}
	.td-style{
	background-color: #b3cce6;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div id="accordion">
					<div class="card">
						<div class="card-header" id="headingOne">
							<h3 class="font-light m-b-xs text-success" >
								<i style="color:#990000; margin-left: 5px;" class="fa fa-mail-forward"></i><b> Assign VM</b>
							</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<table id="example5" class="table table-bordered table-striped">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>BranchID</th>
								<th>Branch_Name</th>
								<th>Customer Name</th>
								<th>Gross_weight</th>
								<th>Language</th>
								<th>Time</th>
								<th width='15%'>VMs</th>
								<th class="text-center">Assign</th>
							</tr>
						</thead>
						<tbody>
							<?php 	
								$sql = mysqli_query($con, "SELECT e.Id, e.branch, e.customer, e.extra, e.time, b.branchName
								FROM everycustomer e
								JOIN branch b ON e.branch = b.branchId
								WHERE e.date='$date' AND e.status='Begin' AND e.agent = ''
								ORDER BY e.Id");
								
								$i = 1;
								while($row = mysqli_fetch_assoc($sql)){
									$data = json_decode($row['extra'], true);
									echo "<tr style='margin-top: 30px'>";
									echo "<td>".$i."</td>";
									echo "<td>".$row['branch']."</td>";
									echo "<td>".$row['branchName']."</td>";
									echo "<td>".$row['customer']."</td>";
									echo "<td>".$data['GrossW']."</td>";
									echo "<td>".$data['Language']."</td>";
									echo "<td>".$row['time']."</td>";
									
									if($data['GrossW'] >= 70 ){
										$agent = $language[$data['Language']]["A"];
									}
									else if($data['GrossW'] >= 30 &&  $data['GrossW'] < 70){
										$agent = $language[$data['Language']]["B"];
									}
									else if($data['GrossW'] < 30 ){
										$agent = $language[$data['Language']]["C"];
									}
									
									echo "<form method='POST' action='submit.php'>";
									echo "<input type='hidden' name='everycustomerid' value='".$row['Id']."' >";
									echo "<td class='td-style'>";
									echo "<select class='form-control m-b' name='agentId' required style='margin-bottom: 0; width: 100%'>";
									echo "<option value='' selected disabled>Select VM</option>";
									foreach($agent as $a){
										echo "<option value='".$a[0]."'>".$a[1]."</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td class='td-style text-center'>";
									echo "<button class='btn btn-primary btn-sm' name='assignVM' type='submit'>Assign</button>";
									echo "</td>";
									echo "</form>";
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
<?php include("footer.php"); ?>	