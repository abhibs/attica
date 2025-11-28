<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$branchId = $_SESSION['branchCode'];
	$date = date('Y-m-d');
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}
	#wrapper h2{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	}
	#wrapper .panel-body{
	box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
	border-radius:10px;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	}
	.fa_Icon{
	color:#ffa500;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	font-size:10px;
	}
	thead th{
	text-align: center;
	vertical-align: middle;
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
	tbody tr{
    text-align: center;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h2><i class="fa_Icon fa fa-edit"></i> Transaction Details</h2>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>bill ID</th>
								<th>Name</th>
								<th>Type</th>
								<th>Gross<br>Weight</th>
								<th>Net<br>Weight</th>
								<th>Gross Amount</th>
								<th>Net Amount</th>
								<th>Amount Paid</th>
								<th>Margin</th>
								<th>Time</th>
								<th>Payment Type</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$sql = mysqli_query($con,"SELECT id,customerId,billId,name,phone,releases,grossW,netW,grossA,netA,amountPaid,time,type,margin,paymentType,status,cashA,impsA FROM trans WHERE date='$date' AND branchId='$branchId'");
								while($row = mysqli_fetch_assoc($sql)){
									echo "<tr>";
									echo "<td>".$row['billId']."</td>";
									echo "<td>".$row['name']."</td>";
									echo "<td>".$row['type']."</td>";
									echo "<td>".ROUND($row['grossW'],2)."</td>";
									echo "<td>".ROUND($row['netW'],2)."</td>";
									echo "<td>".$row['grossA']."</td>";
									echo "<td>".$row['netA']."</td>";
									echo "<td>".$row['amountPaid']."</td>";
									echo "<td>".$row['margin']."</td>";
									echo "<td>".$row['time']."</td>";
									
									if($row['paymentType']=="Cash"){
										echo "<td><b>CASH</b><br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>";
									}
									else if ($row['paymentType']=="NEFT/RTGS"){
										echo "<td><b>IMPS</b><br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>";
									}
									else{
										echo "<td><b>CASH/IMPS</b><br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>";
									}
									
									if($row['status']=="Begin"){
										if($row['type'] == 'Physical Gold'){
											echo "<td style='text-align:center'><a class='btn btn-success btn-md' href='xuploadDocs.php?cid=".$row['customerId']."&mob=".$row['phone']."&bid=".$row['billId']."'><i style='color:#ffcf40' class='fa fa-upload'></i> Upload</a></td>";
										}
										else if($row['type'] == 'Release Gold'){
											echo "<td style='text-align:center'><a class='btn btn-success btn-md' href='xuploadDocsAfterRel.php?cid=".$row['customerId']."&mob=".$row['phone']."&bid=".$row['billId']."'><i style='color:#ffcf40' class='fa fa-upload'></i> Upload</a></td>";
										}
									}
									else if($row['status']=="Approved"){
										$pdf = "Invoice.php?id=".base64_encode($row['id']);
										echo "<td style='text-align:center'><input type='button' id='bt' class='btn btn-success' onclick='print(\"".$pdf."\")' value='View Bill' /></td>";
									}
									else{
										echo "<td style='text-align:center'>" . $row['status'] . "</td>";
									}
									
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>	
	<?php include("footer.php"); ?>
	<script>
		let print = (doc) => {
			let objFra = document.createElement('iframe');
			objFra.style.visibility = 'hidden';
			objFra.src = doc;
			document.body.appendChild(objFra);
			objFra.contentWindow.focus();
			objFra.contentWindow.print();
		}
	</script>