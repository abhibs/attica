<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Branch') {
		include("header.php");
		include("menu.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$branchId = $_SESSION['branchCode'];
    $_SESSION['invoiceNumber'] = $invoiceNumber;
	$date = date('Y-m-d');
?>
<style>
	
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.fa_Icon{
	color:#990000;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:600;
	font-size: 12px;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;	
	}
	thead tr{
	color: #f2f2f2;
	font-size:12px;
	}
	.dataTables_empty{
	
	font-weight:600;
	font-size:12px;
	}

    #wrapper h2{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
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
	#wrapper .panel-body{
	border: 5px solid #fff;
	padding: 15px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
	background-color: #f5f5f5;
	border-radius: 3px;
	}

    .submit-button {
		/* background-color: #123C69; */
		color: #123C69;
		border: none;
		padding: 2px 6px;
		cursor: pointer;

	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h2 class="text-success"><i class="fa_Icon fa fa-edit"></i> Pledge Details</h2>
				</div>
				
				<div class="panel-body" style="padding-top:20px">

					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th class="text-center"><i class="fa fa-sort-numeric-asc"></i></th>
                                <th>InvoiceNo</th>
								<th>Customer</th>
								<th class="text-center">Phone</th>
                                <th class="text-center">GrossW</th>
                                <th class="text-center">NetW</th>
								<th class="text-center">Amount</th>
								<th class="text-center">Intrest</th>
								<th class="text-center">Rate Amount</th>
                                <th class="text-center">Date</th>
								<!--<th>Status</th>-->
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$queryA = mysqli_query($con, "SELECT * FROM pledge_bill  WHERE date='$date' AND branchId='$branchId' AND status!='Rejected'");
								$i = 1;
								while ($rowA = mysqli_fetch_assoc($queryA)) {
									echo "<tr>";
									echo "<td class='text-center'>" . $i . "</td>";
									echo "<td>" . $rowA['billId'] . "</td>";
									echo "<td>" . $rowA['name'] . "</td>";
									echo "<td class='text-center'>" . $rowA['contact'] . "</td>";
									echo "<td class='text-center'>" . $rowA['grossW'] . "</td>";
                                    $netW=$rowA['grossW']-$rowA['stoneW'];
									echo "<td class='text-center'>" . $netW. "</td>";
									echo "<td class='text-center'>" . $rowA['amount'] . "</td>";
									echo "<td class='text-center'>" . $rowA['rate'] . "</td>";
									echo "<td class='text-center'>" . $rowA['rateAmount'] . "</td>";
                                    echo "<td class='text-center'>" . $rowA['date'] . "</td>";
                                    // echo "<td>" . $rowA['status'] . "</td>";									
									//echo "<td class='text-center'><a target='_blank' class='submit-button' href='InvoicePledge.php?id=" . base64_encode($rowA['id']) . "'><i class='fa fa-file-pdf-o' style='color:red;font-size:16px;font-weight:bold;'></i> </a></td>";
									if($rowA['status']=="Billed"){
										echo "<td class='text-center'><a target='_blank' class='submit-button' href='InvoicePledge.php?id=" . base64_encode($rowA['id']) . "'><i class='fa fa-file-pdf-o' style='color:#900;font-size:16px;font-weight:bold;'></i> </a></td>";									
									
									}
									else{
										echo "<td style='text-align:center'>" . $rowA['status'] . "</td>";
									}
									
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
	<div style="clear:both"></div>
<?php include("footer.php"); ?>