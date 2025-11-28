<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);

	/* --------- Guard --------- */
	$type = $_SESSION['usertype'] ?? '';
	if($type=='Accounts'){
		include("header.php");
		include("menuacc.php");
	} else {
		include("logout.php");
		exit();
	}

	include("dbConnection.php");

	$branchId = $_SESSION['branchCode'] ?? '';
	date_default_timezone_set('Asia/Kolkata');
	$date = date('Y-m-d');

	/* --------- Flash (session) --------- */
	$flash = $_SESSION['flash_msg'] ?? '';
	$flashClass = $_SESSION['flash_class'] ?? 'info';
	unset($_SESSION['flash_msg'], $_SESSION['flash_class']);

	/* --------- Handle per-row status update for cashtransfer --------- */
	if (isset($_POST['update_cashtransfer'])) {
		$id = (int)($_POST['ct_id'] ?? 0);
		$new = $_POST['new_status'] ?? '';
		$allowed = ['Pending','Approved','Rejected'];
		if (!$id) {
			$_SESSION['flash_msg'] = 'Invalid row selected.';
			$_SESSION['flash_class'] = 'danger';
		} elseif (!in_array($new, $allowed, true)) {
			$_SESSION['flash_msg'] = 'Invalid status.';
			$_SESSION['flash_class'] = 'danger';
		} else {
			$sql = "UPDATE cashtransfer SET status=? WHERE id=? AND branchId=?";
			$stmt = mysqli_prepare($con, $sql);
			if ($stmt && mysqli_stmt_bind_param($stmt, "sis", $new, $id, $branchId) && mysqli_stmt_execute($stmt)) {
				$_SESSION['flash_msg'] = "Updated #$id to $new.";
				$_SESSION['flash_class'] = 'success';
			} else {
				$_SESSION['flash_msg'] = "Update failed: ".mysqli_error($con);
				$_SESSION['flash_class'] = 'danger';
			}
			if ($stmt) mysqli_stmt_close($stmt);
		}
		/* PRG */
		header("Location: ". strtok($_SERVER["REQUEST_URI"], '?'));
		exit();
	}
?>
<style>
	#wrapper{ background:#f5f5f5; }
	#wrapper h2{ color:#123C69; text-transform:uppercase; font-weight:600; font-size:20px; }
	#wrapper .panel-body{ box-shadow:rgba(60,64,67,.3)0 1px 2px 0, rgba(60,64,67,.15)0 2px 6px 2px; border-radius:10px; }
	.text-success{ color:#123C69; text-transform:uppercase; font-weight:600; font-size:12px; }
	.fa_Icon{ color:#ffa500; }
	thead{ text-transform:uppercase; background-color:#123C69; }
	thead tr{ color:#f2f2f2; font-size:10px; }
	thead th{ text-align:center; vertical-align:middle; }
	.btn-success{
		display:inline-block; padding:.7em 1.4em; margin:0 .3em .3em 0; border-radius:.15em; box-sizing:border-box;
		text-decoration:none; font-size:12px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa;
		background-color:#123C69; box-shadow:inset 0 -.6em 0 -.35em rgba(0,0,0,.17); text-align:center; position:relative;
	}
	tbody tr{ text-align:center; }
	/* small controls in second table */
	.ct-select{ min-width:110px; }
	.ct-update{ padding:6px 10px; font-size:11px; }
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">

			<?php if (!empty($flash)): ?>
				<div class="alert alert-<?php echo htmlspecialchars($flashClass); ?>" style="margin-top:10px;font-weight:600;">
					<?php echo htmlspecialchars($flash); ?>
				</div>
			<?php endif; ?>


			<!-- ==================== Panel 2: Cash Transfer Entries (Today) ==================== -->
			<div class="hpanel" style="margin-top:22px;">
				<div class="panel-heading">
					<h2><i class="fa_Icon fa fa-money"></i> Cash Transfer Entries</h2>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Branch</th>
								<th>2000</th>
								<th>500</th>
								<th>200</th>
								<th>100</th>
								<th>50</th>
								<th>20</th>
								<th>10</th>
								<th>5</th>
								<th>2</th>
								<th>1</th>
								<th>Total</th>
								<th>Sending Emp</th>
								<th>Receiving Emp</th>
								<th>Status</th>
								<th>Created</th>
								<th>Update</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$ctSql = "
								SELECT id, branchId, `2000`,`500`,`200`,`100`,`50`,`20`,`10`,`5`,`2`,`1`,
									   total, status, sendingEmpId, receivingEmpId, created_at
								FROM cashtransfer
								WHERE branchId = '".mysqli_real_escape_string($con,$branchId)."'
								  AND status = 'Pending'
								ORDER BY id DESC";
							$ctRes = mysqli_query($con, $ctSql);
							if ($ctRes && mysqli_num_rows($ctRes) > 0) {
								while ($r = mysqli_fetch_assoc($ctRes)) {
									$id = (int)$r['id'];
									echo "<tr>";
									echo "<td>".$id."</td>";
									echo "<td>".htmlspecialchars($r['branchId'])."</td>";
									echo "<td>".(int)$r['2000']."</td>";
									echo "<td>".(int)$r['500']."</td>";
									echo "<td>".(int)$r['200']."</td>";
									echo "<td>".(int)$r['100']."</td>";
									echo "<td>".(int)$r['50']."</td>";
									echo "<td>".(int)$r['20']."</td>";
									echo "<td>".(int)$r['10']."</td>";
									echo "<td>".(int)$r['5']."</td>";
									echo "<td>".(int)$r['2']."</td>";
									echo "<td>".(int)$r['1']."</td>";
									echo "<td><b>".number_format((int)$r['total'])."</b></td>";
									echo "<td>".htmlspecialchars($r['sendingEmpId'])."</td>";
									echo "<td>".htmlspecialchars($r['receivingEmpId'])."</td>";

									/* Per-row update form */
									echo "<td>";
									echo "<form method='post' action='' style='margin:0; display:inline-block;'>";
									echo "<input type='hidden' name='ct_id' value='".$id."'>";
									echo "<select name='new_status' class='form-control ct-select'>";
									$opts = ['Pending','Approved','Rejected'];
									foreach ($opts as $opt) {
										$sel = ($r['status'] === $opt) ? "selected" : "";
										echo "<option value='".htmlspecialchars($opt)."' $sel>".htmlspecialchars($opt)."</option>";
									}
									echo "</select>";
									echo "</td>";

									echo "<td>".htmlspecialchars($r['created_at'])."</td>";

									echo "<td>";
									echo "<button type='submit' name='update_cashtransfer' class='btn btn-success ct-update'>";
									echo "<i style='color:#ffcf40' class='fa fa-save'></i> Update";
									echo "</button>";
									echo "</form>";
									echo "</td>";

									echo "</tr>";
								}
							} else {
								echo "<tr><td colspan='18' style='text-align:center;font-weight:600;'>No cash transfers today</td></tr>";
							}
						?>
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
	<?php include("footer.php"); ?>
</div>

<script>
	/* print helper kept as-is */
	let printBill = (doc) => {
		let objFra = document.createElement('iframe');
		objFra.style.visibility = 'hidden';
		objFra.src = doc;
		document.body.appendChild(objFra);
		objFra.contentWindow.focus();
		objFra.contentWindow.print();
	}
</script>

