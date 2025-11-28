<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} 
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else if($type=='Zonal'){
		include("header.php");
                include("menuZonal.php");
	}
        else if($type=='SubZonal'){
		include("header.php");
                include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date("Y-m-d");
	
	$extra = "";
	if($type=='SubZonal'){
		$branch = $_SESSION['branchCode'];
		if($branch=="KA"){
			$extra = "AND b.state = 'Karnataka'";
		}
		elseif($branch=="TN"){
			$extra = "AND b.state IN ('Tamilnadu', 'Pondicherry')";
		}
		elseif($branch=="AP-TS"){
			$extra = "AND b.state IN ('Andhra Pradesh', 'Telangana')";
		}
	}
	
	$sql = mysqli_query($con, "SELECT branchId, branchName, (CASE WHEN state='Andhra Pradesh' THEN 'Andhra_Pradesh' ELSE state END) As state,
	(SELECT SUM(t.cashA) FROM trans t WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS cashA,
	(SELECT SUM(t.impsA) FROM trans t WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS impsA,
	(SELECT SUM(t.relCash) FROM releasedata t WHERE t.date='$date' AND t.BranchId=b.branchId AND t.status IN ('Approved','Billed') GROUP BY t.BranchId) AS cashRelA,
	(SELECT SUM(t.relIMPS) FROM releasedata t WHERE t.date='$date' AND t.BranchId=b.branchId AND t.status IN ('Approved','Billed') GROUP BY t.BranchId) AS impsRelA,
	(SELECT SUM(request) FROM fund t WHERE t.date='$date' AND t.branch=b.branchId AND t.status='Approved' GROUP BY t.branch) AS fund,
	(SELECT SUM(transferAmount) FROM trare t WHERE t.date='$date' AND t.branchId=b.branchId AND t.status='Approved' GROUP BY t.branchId) AS transfer,
	(SELECT SUM(transferAmount) FROM trare t WHERE t.date='$date' AND t.branchTo=b.branchName AND t.status='Approved' GROUP BY t.branchTo) AS received,
	(SELECT SUM(amount) FROM expense t WHERE t.date='$date' AND t.branchCode=b.branchId AND t.status='Approved' GROUP BY t.branchCode) AS expense
	FROM branch b
	WHERE b.status = 1 AND b.branchId != 'AGPL000' ".$extra);
	
	$open = [];
	$openingData = mysqli_query($con,"SELECT a.branchId,(CASE when a.forward='Forward to HO' THEN 0 ELSE a.balance END) as open
	FROM closing a
	INNER JOIN
	(SELECT c.branchId,MAX(c.date) AS date
	FROM closing c,branch b
	WHERE c.branchId!='' AND b.status=1 AND c.branchId=b.branchId
	GROUP BY c.branchId) b
	ON a.branchId = b.branchId AND a.date = b.date");
	while($row = mysqli_fetch_assoc($openingData)){
		$open[$row['branchId']] = $row['open'];		
	}
	
?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:700;
	font-size: 18px;
	color:#123C69;
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
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
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
	color: #990000;
	}
	.modal-title {
	font-size: 20px;
	font-weight: 600;
	color:#708090;
	text-transform:uppercase;
	}
	.modal-header{
	background: #123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	.td-align-right{
	text-align : right;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="col-lg-8">
				<h3><span></span>BRANCH AVAILABLE AMOUNT</h3>
			</div>
			<?php if($type != 'Zonal'){ ?>
				<div class="col-lg-4">
					<select class="form-control m-b" id="state">
						<option selected="true" value="ALL">ALL</option>
						<option value="APT">ANDHRA & TELANGANA</option>
						<option value="KAR">KARNATAKA</option>
						<option value="TN">TAMILNADU</option>
					</select>
				</div>
				<?php }else{ ?>
				<div class='col-lg-4'>
					<select class='form-control m-b' id='state'>
						<?php 
							if($branch=="KA"){
								echo "<option selected='true' value='KAR'>KARNATAKA</option>";
							}
							elseif($branch=="TN"){
								echo "<option selected='true' value='TN'>TAMILNADU</option>";
							}
							elseif($branch=="AP-TS"){
								echo "<option selected='true' value='APT'>ANDHRA & TELANGANA</option>";
							}
						?>
					</select>
				</div>
			<?php } ?>
		</div>
		
		<div class="col-md-3">
			<div class="hpanel">
				<div class="panel-body">
					<div class="text-center">
						<h3>OPEN BALANCE</h3>
						<p id="totalOpenBalance"></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2">
			<div class="hpanel">
				<div class="panel-body">
					<div class="text-center">
						<h3>FUNDS</h3>
						<p id="totalFunds"></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2">
			<div class="hpanel">
				<div class="panel-body">
					<div class="text-center">
						<h3>NET AMOUNT</h3>
						<p id="totalNetA"></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2">
			<div class="hpanel">
				<div class="panel-body">
					<div class="text-center">
						<h3>EXPENSE</h3>
						<p id="totalExpense"></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-3">
			<div class="hpanel">
				<div class="panel-body">
					<div class="text-center">
						<h3>CURRENT BALANCE</h3>
						<p id="totalAvail"></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive">
						<table id="exampleBalance" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th>BRANCH ID</th>
									<th>BRANCH NAME</th>
									<th>OPENING</th>
									<th>FUND</th>
									<th>RECEIVED</th>
									<th>TRANSFERRED</th>
									<th>NET A</th>
									<th>EXPENSE</th>
									<th>BALANCE</th>
								</tr>
							</thead>
							<tbody id="tableBody">
								<?php	
									$i = 1;
									$openBalance = 0;
									$transCashAmount = 0;
									$transIMPSAmount = 0;
									$relCashAmount = 0;
									$relIMPSAmount = 0;
									$fundAmount = 0;
									$tranferAmount = 0;
									$recAmount = 0;
									$expenseAmount = 0;
									
									while($row = mysqli_fetch_assoc($sql)){	
										
										$openBalance = array_key_exists($row['branchId'],$open) ? $open[$row['branchId']] : 0;
										$transCashAmount = ($row['cashA'] != null) ? $row['cashA'] : 0;
										$transIMPSAmount = ($row['impsA'] != null) ? $row['impsA'] : 0;
										$relCashAmount = ($row['cashRelA'] != null) ? $row['cashRelA'] : 0;
										$relIMPSAmount = ($row['impsRelA'] != null) ? $row['impsRelA'] : 0;
										$fundAmount = ($row['fund'] != null) ? $row['fund'] : 0;
										$tranferAmount = ($row['transfer'] != null) ? $row['transfer'] : 0;
										$recAmount = ($row['received'] != null) ? $row['received'] : 0;
										$expenseAmount = ($row['expense'] != null) ? $row['expense'] : 0;
										
										echo "<tr class='".$row['state']."'>";
										echo "<td>".$row['branchId']."</td>";
										echo "<td>".$row['branchName']."</td>";
										echo "<td class='td-align-right'>".$openBalance."</td>";
										echo "<td class='td-align-right'>".($transIMPSAmount + $relIMPSAmount + $fundAmount)."</td>";
										echo "<td class='td-align-right'>".$recAmount."</td>";
										echo "<td class='td-align-right'>".$tranferAmount."</td>";
										echo "<td class='td-align-right'>".($transCashAmount + $transIMPSAmount + $relCashAmount + $relIMPSAmount)."</td>";
										echo "<td class='td-align-right'>".$expenseAmount."</td>";
										echo "<td class='td-align-right'>".(($openBalance + $fundAmount + $recAmount) - ($transCashAmount + $relCashAmount + $tranferAmount + $expenseAmount))."</td>";
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
	<script>
		
		$(document).ready(()=>{
			
			$('#exampleBalance').DataTable( {
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
			
			let tableRows = document.querySelectorAll('tr'),
			tdAP = document.querySelectorAll('.Andhra_Pradesh , .Telangana'),
			tdKAR = document.querySelectorAll('.Karnataka'),
			tdTN = document.querySelectorAll('.Tamilnadu , .Pondicherry');
			tdIndex = [2, 3, 6, 7, 8];
			
			let stateSelect = document.getElementById('state');
			stateSelect.addEventListener('change',(state)=>{
				let selected = stateSelect.value;
				switch(selected){
					
					case 'APT': getStateData('APT');
					tdAP.forEach((e)=>{ e.removeAttribute("hidden"); });
					tdKAR.forEach((e)=>{ e.setAttribute("hidden","hidden"); });
					tdTN.forEach((e)=>{ e.setAttribute("hidden","hidden"); });
					break;
					
					case 'KAR': getStateData('KAR');
					tdAP.forEach((e)=>{ e.setAttribute("hidden","hidden"); });
					tdKAR.forEach((e)=>{ e.removeAttribute("hidden"); });
					tdTN.forEach((e)=>{ e.setAttribute("hidden","hidden");});
					break;
					
					case 'TN' : getStateData('TN');
					tdAP.forEach((e)=>{ e.setAttribute("hidden","hidden"); });
					tdKAR.forEach((e)=>{ e.setAttribute("hidden","hidden"); });
					tdTN.forEach((e)=>{ e.removeAttribute("hidden"); });
					break;
					
					case 'ALL' : getStateData('ALL');
					tableRows.forEach((e)=>{ e.removeAttribute("hidden"); });
					break;
					
				}
			});
			
			function getStateData(state){
				let stateRows,
				open = 0,
				fund = 0,
				net = 0,
				expense = 0,
				balance = 0;
				
				if(state == 'ALL'){
					stateRows = tableRows;
				}
				else if(state == 'APT'){
					stateRows = tdAP;
				}
				else if(state == 'KAR'){
					stateRows = tdKAR;
				}
				else if(state == 'TN'){
					stateRows = tdTN;
				}
				
				// TOTAL DATA
				for(let index of tdIndex){
					stateRows.forEach((e)=>{
						if(e.className != "theadRow"){
							switch(index){
								case 2 : open += parseInt(e.children[index].innerHTML); break;
								case 3 : fund += parseInt(e.children[index].innerHTML); break;
								case 6 : net += parseInt(e.children[index].innerHTML); break;
								case 7 : expense += parseInt(e.children[index].innerHTML); break;
								case 8 : balance += parseInt(e.children[index].innerHTML); break;
							}
						}
					});
				}
				document.getElementById('totalOpenBalance').innerHTML = open.toLocaleString('en-IN');
				document.getElementById('totalFunds').innerHTML = fund.toLocaleString('en-IN');
				document.getElementById('totalNetA').innerHTML = net.toLocaleString('en-IN');
				document.getElementById('totalExpense').innerHTML = expense.toLocaleString('en-IN');
				document.getElementById('totalAvail').innerHTML = balance.toLocaleString('en-IN');
				
			}
			getStateData(stateSelect.value);
			
		});
		
	</script>
<?php include("footer.php"); ?>
