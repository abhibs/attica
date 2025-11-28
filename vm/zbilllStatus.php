<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];	
	if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	} 
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
	
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT branch FROM vmagent WHERE agentId='$empId'"));
	$branches = explode(",", $vmBranchList['branch']);
	
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading text-success">
					<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Assigned Branch Transaction Details</h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">									
									<th>Branch</th>
									<th>Name</th>
									<th>Type</th>
									<th>Net Weight</th>
									<th>Gross Weight</th>
									<th>Net Amount</th>
									<th>Gross Amount</th>
									<th>Amount Paid</th>
									<th>Margin Amount</th>
									<th>Time</th>
									<th>Payment Type</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
	<?php
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['trans_id'])) {
			$transId = mysqli_real_escape_string($con, $_POST['trans_id']);
			$action = $_POST['action'];

			// Get payment type
			$paymentTypeResult = mysqli_fetch_assoc(mysqli_query($con, "SELECT paymentType FROM trans WHERE id='$transId'"));
			$paymentType = $paymentTypeResult['paymentType'];

			if ($action == 'verify') {
				if (strtolower($paymentType) == 'cash') {
					$newStatus = 'Approved';
				} else {
					$newStatus = 'Verified';
				}
			} elseif ($action == 'reject') {
				$newStatus = 'Rejected';
			}

			// Update status
			mysqli_query($con, "UPDATE trans SET status='$newStatus' WHERE id='$transId'");
		}

		// Query again after any update
		$query = mysqli_query($con, "
    SELECT 
        b.branchName,
        t.id,
        t.name,
        t.grossW,
        t.netW,
        t.phone,
        t.grossA,
        t.netA,
        t.amountPaid,
        t.margin,
        t.time,
        t.type,
        t.paymentType,
        t.status,
        t.cashA,
        t.impsA
    FROM trans t
    INNER JOIN branch b ON t.branchId = b.branchId
    WHERE 
        t.date = '$date' AND 
        t.branchId IN ('$branches[0]', '$branches[1]', '$branches[2]', '$branches[3]', '$branches[4]') AND 
        t.branchId != '' AND 
        t.status = 'ZONAL-VERIFIED'
");

while($row = mysqli_fetch_assoc($query)) {
    echo "<tr>";
    echo "<td>".$row['branchName']."</td>";
    echo "<td>".$row['name']."</td>";
    echo "<td>".$row['type']."</td>";
    echo "<td>".$row['netW']."</td>";
    echo "<td>".$row['grossW']."</td>";
    echo "<td>".$row['netA']."</td>";
    echo "<td>".$row['grossA']."</td>";
    echo "<td>".$row['amountPaid']."</td>";
    echo "<td>".$row['margin']."</td>";
    echo "<td>".$row['time']."</td>";

    // Payment Type Column with subtext
    echo "<td>";
        $paymentType = ($row['paymentType'] == 'NEFT/RTGS') ? 'IMPS' : $row['paymentType'];
        echo $paymentType;

        if ($row['paymentType'] == 'Cash') {
            echo "<br><small>Cash: " . $row['cashA'] . "</small>";
        } else {
            echo "<br><small>Cash: " . $row['cashA'] . "</small>";
            echo "<br><small>IMPS: " . $row['impsA'] . "</small>";
        }
    echo "</td>";

    echo "<td>".$row['status']."</td>";

    echo "<td>"; // You can continue this column for any additional logic


			// if ($row['status'] == "ZONAL-VERIFIED") { 
			// 	echo "<a class='btn btn-success' target='_blank' href='../Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i> View</a>";
			// }
			// else if($row['status'] == "Begin"){
			// 	echo "Uploading Docs";
			// }
			// else {
				// Add verify and reject buttons
				echo " ";
				echo "<a class='btn btn-primary btn-sm' target='_blank' href='../Invoice.php?id=".base64_encode($row['id'])."'> Bill</a></b>";
				echo " ";
				echo "<a class='btn btn-primary btn-sm' href='vmCustomerDetails.php?id=".$row['phone']."&ids=".$row['id']."'> Action</a></b>";
				echo " ";
				//echo "<form method='POST' style='display:inline-block; margin-right: 5px;'>";
			//	echo "<input type='hidden' name='trans_id' value='".$row['id']."'>";
			//	echo "<input type='hidden' name='action' value='verify'>";
			//	echo "<button type='submit' class='btn btn-primary btn-sm'>Verify</button>";
			//	echo "</form>";
			//	echo " ";
			//	echo "<form method='POST' style='display:inline-block;'>";
			//	echo "<input type='hidden' name='trans_id' value='".$row['id']."'>";
			//	echo "<input type='hidden' name='action' value='reject'>";
			//	echo "<button type='submit' class='btn btn-danger btn-sm'>Reject</button>";
			//	echo "</form>";
			}
			echo "</td>";

			echo "</tr>";										
		// }
	?>
</tbody>

						</table>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
	</div>
	<?php include("footerNew.php"); ?>
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
