<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Calcutta');
$type = $_SESSION['usertype'];
if ($type == 'Branch') {
	include("header.php");
	include("menu.php");
} else {
	include("logout.php");
}

include("dbConnection.php");
$branchCode = $_SESSION['branchCode'];
$date = date('Y-m-d');


/* BRANCH DATA */
$branchData = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName, meet,
	(CASE
	WHEN priceId=1 THEN 'Bangalore'
	WHEN priceId=2 THEN 'Karnataka'
	WHEN priceId=3 THEN 'Andhra Pradesh'
	WHEN priceId=4 THEN 'Telangana'
	WHEN priceId=5 THEN 'Chennai'
	WHEN priceId=6 THEN 'Tamilnadu'
	END) AS city
	FROM branch
	WHERE branchId='$branchCode'"));

/* MISC DATA */
$miscData = mysqli_fetch_assoc(mysqli_query($con, "SELECT
	(SELECT COUNT(DISTINCT(contact)) AS totalWalkin FROM everycustomer WHERE date='$date' AND branch='$branchCode' AND status NOT IN ('Double Entry','Wrong Entry')) AS totalWalkin,
	(SELECT COUNT(id) AS totalSold FROM trans WHERE date='$date' AND branchId='$branchCode' AND status='Approved') AS totalSold,
	(SELECT date FROM closing WHERE branchId='$branchCode' ORDER BY date DESC LIMIT 1) AS ClosingDate,
	(SELECT day FROM misc WHERE purpose='Add Customer') AS addDate
	"));

/* GOLD RATE */
$goldRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash,transferRate
	FROM gold
	WHERE type='Gold' AND date='$date' AND city='$branchData[city]'
	ORDER BY id DESC
	LIMIT 1"));

/* SILVER RATE */
$silverRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash
	FROM gold
	WHERE type='Silver' AND date='$date' AND city='$branchData[city]'
	ORDER BY id DESC
	LIMIT 1"));

$contact = "Zonal contact not found.";

if (isset($_SESSION['branchCode'])) {
	$branchCode = mysqli_real_escape_string($con, $_SESSION['branchCode']);

	$query = "SELECT e.contact, e.name
        FROM branch b
        INNER JOIN employee e ON b.ezviz_vc = e.empId
        WHERE b.branchId = '$branchCode'
        LIMIT 1";

	$result = mysqli_query($con, $query);

	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$name = $row['name'];
		$contact = htmlspecialchars($row['contact']);
	}
}
?>

<link rel="stylesheet" href="vendor/sweetalert/lib/sweet-alert.css" />
<link rel="stylesheet" href="utils/feedback/survey-form/frontend/feedback-style.css" />
<style>
	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 14px;
	}

	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
		background-color: #fffafa;
	}

	.quotation h3 {
		color: #123C69;
		font-size: 18px !important;
	}

	.text-success {
		color: #123C69;
		text-transform: uppercase;
		font-weight: 800;
		font-size: 12px;
		margin: 0px 0px 0px;
	}

	.btn-primary {
		background-color: #123C69;
	}

	thead {
		text-transform: uppercase;
		background-color: #123C69;
	}

	thead tr {
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
		font-size: 12px;
		font-family: 'Roboto', sans-serif;
		text-transform: uppercase;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
		text-align: center;
		position: relative;
	}

	#wrapper .panel-body {
		box-shadow: 10px 15px 15px #999;
		border: none;
		background-color: #f5f5f5;
		border-radius: 3px;
		padding: 20px;
	}

	.tooltip .tooltiptext {
		visibility: hidden;
		width: 120px;
		background-color: #990000;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px 0;
		position: absolute;
		z-index: 1;
		top: -5px;
		right: 105%;
	}

	.tooltip:hover .tooltiptext {
		visibility: visible;
	}

	.fa-icon-color {
		color: #990000;
	}

	.getRate {
		border: 1px solid #123C69;
		color: #123C69;
		border-radius: 3px;
	}

	.getRate:hover {
		color: #ffffff;
		background-color: #123C69;
		border-radius: 0px;
	}

	.tooltipBilling:hover .tooltiptextBilling {
		visibility: visible;
	}

	.tooltipBilling .tooltiptextBilling {
		visibility: hidden;
		width: 120px;
		background-color: #990000;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px 0;
		position: absolute;
		z-index: 1;
		top: -5px;
		right: -135%;
	}

	:root {
		--accent: #990000;
		/* Attica deep red */
		--gold: #c9a227;
		--text: #1a1a1a;
		--muted: #666;
		--bg: #f7f7f8;
	}

	body {
		margin: 0;
		font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
		color: var(--text);
	}
  :root{ --accent:#990000; --accent-2:#6b0000; --muted:#6b7280; }

  .tp-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;z-index:9999;padding:16px}
  .tp-backdrop.show{display:grid;place-items:center}

  .tp-modal{
    width:min(1060px,96vw); background:#fff; border-radius:18px; overflow:hidden;
    box-shadow:0 28px 70px rgba(0,0,0,.35); position:relative; animation:tpPop .18s ease-out
  }
  @keyframes tpPop{from{transform:scale(.985);opacity:.85}to{transform:scale(1);opacity:1}}

  /* Close button: reserve space so it won't collide with pill */
  .tp-close{
    position:absolute; right:10px; top:10px; width:36px; height:36px; border-radius:999px;
    border:none; background:#fff; color:var(--accent); font-size:20px; font-weight:800; cursor:pointer;
    display:grid; place-items:center; box-shadow:0 2px 8px rgba(0,0,0,.18); line-height:0; z-index:3;
  }
  .tp-close:hover{transform:scale(1.05)}

  .tp-header{
    display:flex; align-items:center; gap:12px; padding:14px 18px; color:#fff;
    background:linear-gradient(135deg,var(--accent),var(--accent-2));
    padding-right:64px; /* <-- leaves room for the close button */
  }
  .tp-header--strip{border-top:1px solid rgba(255,255,255,.15)}
  .tp-title{font-weight:800; letter-spacing:.3px; text-transform:uppercase}
  .tp-date-pill{
    margin-left:auto; font-size:12px; padding:6px 12px; border-radius:999px;
    background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.28);
    backdrop-filter:saturate(120%) blur(2px);
  }
  .tp-window{margin-left:auto; font-size:12px; opacity:.95}

  .tp-content{padding:18px; background:#fff}

  /* Grid */
  .tp-grid{display:grid; gap:16px; grid-template-columns:repeat(4,1fr)}
  @media (max-width:1100px){ .tp-grid{grid-template-columns:repeat(3,1fr)} }
  @media (max-width:700px){ .tp-grid{grid-template-columns:repeat(2,1fr)} }

  /* Card */
  .tp-card{
    background:#fff; border:1px solid #eee; border-radius:14px; overflow:hidden;
    box-shadow:0 4px 16px rgba(0,0,0,.08); display:flex; flex-direction:column;
  }
  /* FIX: give the photo area a fixed height so the page never shows through */
  .tp-photo{
    width:100%;
    height:280px;                 /* <- fixed height (fallback if image missing) */
    background:#e5e7eb;           /* neutral placeholder */
    overflow:hidden;
  }
  .tp-photo img{
    width:100%; height:100%; object-fit:cover; display:block; background:#e5e7eb;
  }
  .tp-meta{padding:12px 12px 14px; text-align:center}
  .tp-rank{font-size:22px; font-weight:900; color:#1f2937; line-height:1}
  .tp-name{margin-top:6px; font-weight:700; color:#111827}
  .tp-sub{margin-top:4px; font-size:12px; color:var(--muted); font-weight:600}

  /* Losers list */
  .tp-losers{display:flex; flex-direction:column; align-items:center; gap:6px}
  .tp-loser-line{font-size:20px; font-weight:800; color:#111; text-align:center}
  .tp-empty{color:var(--muted); text-align:center; padding:12px}
  /* Keep the Add Customer CTA clickable even if overlay is on screen */
.add-customer-cta { position: relative; z-index: 10001; }

/* When any Bootstrap modal is open, push our overlay behind and disable its clicks */
.modal-open #tpBackdrop { z-index: 1040 !important; pointer-events: none; }
</style>

<!--   EVERY CUSTOMER MODAL   -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="addCustomerTitle" style="z-index:1060;">
  <div class="modal-dialog modal-sm" role="document" style="width:600px;">
    <div class="modal-content">
      <div class="color-line"></div>

      <!-- Keep your original close icon -->
      <span class="fa fa-close modaldesign" data-dismiss="modal" aria-label="Close"></span>

      <!-- Add a semantic close button (better accessibility) -->
      <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position:absolute; right:10px; top:10px; z-index:2;">
        <span aria-hidden="true">&times;</span>
      </button>

      <div class="modal-header" style="background-color:#123C69;color:#f0f8ff;">
        <h3 id="addCustomerTitle">ADD CUSTOMER</h3>
      </div>

      <div class="modal-body" style="padding-right:40px;">
        <form method="POST" class="form-horizontal" action="xsubmit.php" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" value="<?php echo $branchCode; ?>" class="form-control" name="branchid">

          <div class="form-group">
            <label class="col-sm-2 control-label text-success">Name</label>
            <div class="col-sm-10">
              <input type="text" name="cusname" placeholder="Customer Name" required class="form-control" autocomplete="off">
            </div>
          </div>

          <div class="form-group" style="margin-top:30px">
            <label class="col-sm-2 control-label text-success">Mobile</label>
            <div class="col-sm-10">
              <!-- keep your original behavior; add inputmode for better mobile keyboard -->
              <input
                type="number"
                class="form-control"
                name="cusmob"
                placeholder="Contact Number"
                required
                maxlength="10"
                pattern="[0-9]{10}"
                inputmode="numeric"
                oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                autocomplete="off">
            </div>
          </div>

          <div class="form-group" style="margin-top:30px">
            <label class="col-sm-2 control-label text-success">Type</label>
            <div class="col-sm-10">
              <select name="customerType" class="form-control" aria-label="Default select example" required>
                <option selected="true" disabled="disabled" value="">Type</option>
                <option value="physical">Physical</option>
                <option value="release">Release</option>
              </select>
            </div>
          </div>

          <div class="form-group" style="margin-top:30px">
            <label class="col-sm-2 control-label text-success">Ornament Doc / Image</label>
            <div class="col-sm-10">
              <input
                type="file"
                style="background:#ffcf40"
                class="form-control"
                name="customerDocument"
                id="file"
                accept=".jpg,.jpeg,.png,.pdf,image/*">
            </div>
          </div>

          <div class="form-group" style="margin-top:30px">
            <div class="col-sm-4 col-sm-offset-8">
              <button class="btn btn-success btn-block" name="submitNC" type="submit">
                <span style="color:#ffcf40" class="fa fa-save"></span> Submit
              </button>
            </div>
          </div>
        </form>
      </div> <!-- /.modal-body -->
    </div>
  </div>
</div>

<script>
// Ensure highlight overlay never blocks this modal when it opens
(function () {
  if (!window.jQuery) return;
  jQuery(function($){
    $('#addCustomerModal').on('show.bs.modal', function () {
      var bd = document.getElementById('tpBackdrop');
      if (bd) {
        bd.classList.remove('show');
        bd.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
      }
    });
  });
})();
</script>



<!--   QUOTATION  MODAL   -->
<div class="modal fade" id="quotationModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width:1050px;">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<form method="POST" action="quotationImageUpload.php" enctype="multipart/form-data" id="qForm">
				<input type="hidden" name="branchId" value="<?php echo $branchCode; ?>">
				<input type="hidden" id="everycustomerID" name="ecID">
				<input type="hidden" id="quotationImage" name="quotationImage">
				<input type="hidden" id="givenRate" name="givenRate">
				<div class="panel-body">
					<div class="panel-heading">
						<h3 class="panel-title">
							<a class="btn btn-success btn-user" style="color:#fff" data-toggle="collapse" data-parent="#accordion" href="#collapse1">Physical</a>
							<a class="btn btn-success btn-user" style="color:#fff" data-toggle="collapse" data-parent="#accordion" href="#collapse2">Release</a>
						</h3>
					</div>
					<div class="panel-group" id="accordion">
						<div class="panel panel-default">
							<div id="collapse1" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-xs-12">
										<!--  QUOTATION PHYSICAL GOLD  -->
										<div class="container-fluid" style="padding:0px;">
											<div class="col-xs-2" style="padding:10px;background-color:#ddd;" id="qPhysicalData">
												<select class="form-control">
													<option selected="true" disabled="disabled" value="">Gold / Silver</option>
													<option value="<?php echo $goldRate['cash']; ?>">Gold (Cash)</option>
													<option value="<?php echo $goldRate['transferRate']; ?>">Gold (IMPS)</option>
													<option value="<?php echo $silverRate['cash']; ?>">Silver</option>
												</select>
												<select class="form-control" style="margin-top:7px">
													<option selected="true" disabled="disabled" value="">Ornament</option>
													<?php
													$ornamentList = ['22 carot Biscuit (91.6)', '24 carot Biscuit (99.9)', '22 carot Coin (91.6)', '24 carot Coin (99.9)', 'Anklets', 'Armlets', 'Baby Bangles', 'Bangles', 'Bracelet', 'Broad Bangles', 'Chain', 'Chain with Locket', 'Chain with Black Beads', 'Drops', 'Ear Rings', 'Gold Bar', 'Head Locket', 'Locket', 'Matti', 'Necklace', 'Ring', 'Small Gold Piece', 'Studs', 'Studs And Drops', 'Thala/Mangalya Chain', 'Thala/Mangalya Chain with Black Beads', 'Waist Belt/Chain', 'Silver Bar', 'Silver Items', 'Others'];
													foreach ($ornamentList as $item) {
														echo "<option value='" . $item . "'>" . $item . "</option>";
													}
													?>
												</select>
												<input type="text" placeholder="Gross wgt" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="text" placeholder="Stone wgt" class="form-control" autocomplete="off" style="margin-top:7px">
												<input type="text" placeholder="Net wgt" class="form-control" autocomplete="off" readonly style="margin-top:7px">
												<select class="form-control" style="margin-top:7px">
													<option selected="true" disabled="disabled" value="">PURITY</option>
													<option value="1001">24K</option>
													<option value="1002">916 (Hallmark)</option>
													<option value="1003">916 (Non-Hallmark)</option>
													<option value="1004">(75% to 84%)</option>
													<option value="1005">(0% to 74%)</option>
													<option value="Silver">Silver</option>
												</select>
												<input type="text" placeholder="System Purity" class="form-control" autocomplete="off" style="margin-top:7px">
												<div class="system_purity_text font-bold text-info text-right" style="font-size: 11px;"> </div>
												<input type="text" placeholder="Given Purity" class="form-control" autocomplete="off" style="margin-top:7px">
												<div class="given_purity_text font-bold text-info text-right" style="font-size: 11px;"> </div>
												<input type="button" class="btn btn-primary btn-block" value="+" style="margin-top:7px">
											</div>
											<div class="col-xs-10" id="physicalGold">
												<div class="col-xs-12" style="padding:10px; background-color:#ddd; margin-bottom:5px;">
													<div class="col-xs-2">
														<label class="text-success">Physical</label>
													</div>
													<div class="col-xs-4">
														<label class="text-success"><span class="fa fa-sign-in fa-icon-color"></span> | <?php echo $_SESSION['employeeId']; ?></label>
													</div>
													<div class="col-xs-3">
														<label class="text-success"><span class="fa fa-bank fa-icon-color"></span> | <?php echo $branchData['branchName']; ?></label>
													</div>
													<div class="col-xs-3">
														<label class="text-success"><span class="fa fa-user-circle-o fa-icon-color"></span> | <span class="qCustomerName"></span></label>
													</div>
												</div>
												<div class="col-sm-12" style="padding:0px;">
													<table class="table table-bordered table-hover" id="qTablePhysical">
														<thead>
															<tr>
																<th>Ornament</th>
																<th>Gross W</th>
																<th>Stone</th>
																<th>Net W</th>
																<th>Code</th>
																<!-- <th>System Purity</th> -->
																<th>Purity</th>
																<th>Rate</th>
																<th>Gross Amt</th>
																<th>Delete</th>
															</tr>
														</thead>
														<tbody id="qPhysicalTableBody"></tbody>
														<tfoot id="qPhysicalTableFoot" style="background-color:#ddd;font-weight:600;">
															<tr>
																<td></td>
																<td></td>
																<td></td>
																<td></td>
																<td></td>
																<!-- <td></td> -->
																<td></td>
																<td></td>
																<td></td>
																<td></td>
															</tr>
														</tfoot>
													</table>
												</div>
												<div class="col-sm-12" style=" background-color:#ddd; margin-bottom:5px; padding-top:10px;">
													<div class="col-sm-4">
														<div class="input-group m-b">
															<span class="input-group-addon text-success">Gross Amt</span>
															<input type="text" id="qGrossAPhysical" readonly class="form-control">
														</div>
													</div>
													<div class="col-sm-4">
														<div class="input-group">
															<input type="text" id="qMarginPercPhysical" class="form-control">
															<span class="input-group-addon text-success">% |Margin</span>
															<input type="text" id="qMarginAPhysical" readonly class="form-control">
														</div>
													</div>
													<div class="col-sm-4">
														<div class="input-group m-b">
															<span class="input-group-addon text-success">Net Amt</span>
															<input type="text" id="qNetAPhysical" readonly class="form-control">
														</div>
													</div>
												</div>
												<div class="col-sm-12" style="padding-right:0px;padding-left:0px;">
													<textarea style="resize:none;height:100%;width:100%;margin:0px; border:1px solid #d9d9d9;" placeholder="Remarks"></textarea>
												</div>
											</div>
										</div>
										<div class="col-xs-12" align="right">
											<button type="button" class="btn btn-success" style="margin-top:10px;" id="sendPhysicalQuotButton"><i class="fa fa-camera" style="color:#ffcf40"></i> Save Quotation</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div id="collapse2" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-xs-12">
										<!--  QUOTATION RELEASE  -->
										<div class="container-fluid" id="releaseGold" style="padding:0px">
											<div class="col-xs-12" style="padding:10px; background-color:#ddd; margin-bottom:5px">
												<div class="col-xs-2">
													<label class="text-success">Release</label>
												</div>
												<div class="col-xs-4">
													<label class="text-success"><span class="fa fa-sign-in fa-icon-color"></span> | <?php echo $_SESSION['employeeId']; ?></label>
												</div>
												<div class="col-xs-3">
													<label class="text-success"><span class="fa fa-bank fa-icon-color"></span> | <?php echo $branchData['branchName']; ?></label>
												</div>
												<div class="col-xs-3">
													<label class="text-success"><span class="fa fa-user-circle-o fa-icon-color"></span> | <span class="qCustomerName"></span></label>
												</div>
											</div>
											<div class="col-xs-4" style="padding:10px; background-color:#ddd;">
												<div class="col-xs-6">
													<label class="text-success">Rate</label>
													<select class="form-control" id="qReleaseRate">
														<option selected="true" disabled="disabled" value="">Gold / Silver</option>
														<option value="<?php echo $goldRate['cash']; ?>">Gold (Cash)</option>
														<option value="<?php echo $goldRate['transferRate']; ?>">Gold (IMPS)</option>
														<option value="<?php echo $silverRate['cash']; ?>">Silver</option>
													</select>
												</div>
												<div class="col-xs-6">
													<label class="text-success">Release Amount</label>
													<input type="text" id="qReleaseA" class="form-control" placeholder="Release Amount">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Gross Weight</label>
													<input type="text" id="qReleaseGrossW" class="form-control" placeholder="Gross Weight">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Net Weight</label>
													<input type="text" id="qReleaseNetW" class="form-control" placeholder="Net Weight">
												</div>
												<div class="col-xs-6">
													<label class="text-success">Release Purity (%)</label>
													<input type="text" id="qReleasePurity" class="form-control" placeholder="Purity %" readonly>
												</div>
												<div class="col-xs-6" style="text-align:center;margin-top:22px">
													<button class="btn btn-success" type="button" id="quotation-button-release">
														<span style="color:#ffcf40" class="fa fa-calculator"></span>
													</button>
												</div>
											</div>
											<div class="col-xs-8">
												<table class="table table-bordered table-hover" id="tableRelease">
													<thead>
														<tr>
															<th style="text-align:center;">Purity</th>
															<th style="text-align:center;">Rate</th>
															<th style="text-align:center;">Gross Amount</th>
															<th style="text-align:center;">Margin Amount (3%)</th>
															<th style="text-align:center;">Net Amount</th>
															<th style="text-align:center;">Net Amount Payable</th>
														</tr>
													</thead>
													<tbody style="text-align:center;background-color:#F5F5F5">
														<tr id="qRelease91">
															<td> 91% </td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
														</tr>
													</tbody>
												</table>
												<div class="col-sm-12" style="background-color:#ddd; margin-bottom:5px; padding-top:10px;">
													<div class="row">
														<div class="col-sm-4">
															<div class="input-group mb-2" style="margin: 1vw;">
																<span class="input-group-text text-success" style="min-width: 120px;">Net Amount</span>
																<input type="text" id="qNetARelease" readonly class="form-control">
															</div>
														</div>
														<div class="col-sm-4">
															<div class="input-group mb-2" style="margin: 1vw;">
																<span class="input-group-text text-success" style="min-width: 120px;">Release Amount</span>
																<input type="text" id="qReleaseAmt" readonly class="form-control">
															</div>
														</div>
														<div class="col-sm-4">
															<div class="input-group mb-2" style="margin: 1vw;">
																<span class="input-group-text text-success" style="min-width: 120px;">Payable Amount</span>
																<input type="text" id="qNetAReleasePay" readonly class="form-control">
															</div>
														</div>
													</div>
												</div>

												<div class="col-xs-12" style="padding: 0px;">
													<textarea style="resize:none;height:100%;width:100%;margin:0px;border:1px solid #d9d9d9;" placeholder="Release Place"></textarea>
												</div>
											</div>
										</div>
										<div class="col-xs-12" align="right">
											<button type="button" class="btn btn-success" style="margin-top:15px;" id="sendReleaseQuotButton"><i class="fa fa-camera" style="color:#ffcf40"></i> Save Quotation</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div style="clear:both"></div>
</div>


<div id="wrapper">
	<div class="row content">

		<!--<div class="col-lg-2">
			<div class="hpanel">
			<div class="panel-body">
			<div class="text-center">
			<h3 class="text-success">New Customer</h3>
			
			<?php
			if ($miscData['addDate'] == 'Yes') {
			?>
				<div class="clearfix" style="height: 19px;"></div>
				<button title="Click To Add New Customer" data-toggle="modal" data-target="#addCustomerModal" class='btn' style="background-color:#123C69;padding:5px 8px 5px 8px;box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);">
				<i style="color:#ffa500" class="pe-7s-add-user fa-2x"></i>
				</button>
				<?php } else { ?>
				<div class="clearfix" style="height: 20px;"></div>
				<i style="color:#990000" class="pe-7s-add-user fa-3x"></i>
			<?php } ?>
			</div>
			</div>
			</div>
		</div>-->

		<div class="col-lg-2">
			<div class="hpanel">
				<?php if ($miscData['addDate'] == 'Yes') { ?>
					<div class="panel-body" style="height:50px; padding: 5px; background-color: #BEE890;">
						<ul class="mailbox-list">
							<li>
								<a title="Click To Add New Customer" data-toggle="modal" data-target="#addCustomerModal">
									<span class="pull-right"><i class="pe-7s-add-user fa" style="color:#990000; font-size: 18px; font-weight: 600;"></i> </span>
									Add Customer
								</a>
							</li>
						</ul>
					</div>
				<?php } else { ?>
					<div class="panel-body" style="height:50px; padding: 5px;">
						<ul class="mailbox-list">
							<li>
								<a>
									<span class="pull-right"><i class="pe-7s-add-user fa" style="color:#990000; font-size: 18px; font-weight: 600;"></i> </span>
									Add Customer
								</a>
							</li>
						</ul>
					</div>
				<?php } ?>
				<div class="panel-body" style="height:50px; margin-top: 20px; padding: 5px;">
					<ul class="mailbox-list">
						<li>
							<?php if ($branchData['meet'] == "") { ?>
								<a>
									<span class="pull-right"><i class="pe-7s-video fa" style="color:#990000; font-size: 18px; font-weight: 600;"></i> </span>
									Meet Link
								</a>
							<?php } else { ?>
								<a href="<?php echo $branchData['meet']; ?>" target="_BLANK">
									<span class="pull-right"><i class="pe-7s-video fa" style="color:#990000; font-size: 18px; font-weight: 600;"></i> </span>
									Meet Link
								</a>
							<?php } ?>

						</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="hpanel">
				<div class="panel-body" style="height:120px">
					<div class="stats-title pull-left">
						<h3 class="text-success"><?php echo $branchCode; ?></h3>
					</div>
					<div class="stats-icon pull-right">
						<i style="color:#990000" class="pe-7s-culture fa-3x"></i>
					</div>
					<div class="m-t-xl" style="margin-top:50px">
						<p class="font-bold no-margins">
							<?php echo $branchData['branchName']; ?>
						</p>
					</div>
				</div>
			</div>
		</div>

<div class="col-lg-3">
  <div class="hpanel">
    <div class="panel-body">
      <div class="m-t-xl">
        <p class="font-bold" id="branchTarget" style="color:#111827;font-size:1vw;text-align:center">
          target: —
        </p>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const branchId = "<?php echo addslashes($branchCode); ?>".toUpperCase();

  // Branch grade mapping
  const branchGrades = {
    A: ["AGPL009","AGPL235","AGPL087","AGPL086","AGPL010","AGPL028",
        "AGPL004","AGPL002","AGPL008","AGPL021","AGPL099","AGPL053",
        "AGPL195","AGPL003","AGPL011","AGPL057","AGPL230","AGPL155",
        "AGPL096","AGPL189","AGPL228","AGPL100","AGPL015","AGPL144",
        "AGPL060","AGPL072","AGPL061","AGPL137","AGPL071","AGPL150",
        "AGPL078","AGPL023","AGPL063","AGPL091","AGPL164","AGPL033",
        "AGPL032","AGPL039","AGPL104","AGPL048","AGPL049","AGPL051",
        "AGPL159","AGPL029","AGPL031","AGPL130","AGPL079","AGPL080",
        "AGPL056","AGPL176","AGPL082","AGPL054","AGPL055"],
    B: ["AGPL097","AGPL088","AGPL179","AGPL169","AGPL190","AGPL001",
        "AGPL083","AGPL016","AGPL058","AGPL027","AGPL093","AGPL069",
        "AGPL068","AGPL151","AGPL165","AGPL076","AGPL196","AGPL022",
        "AGPL220","AGPL217","AGPL226","AGPL145","AGPL094","AGPL026",
        "AGPL119","AGPL095","AGPL034","AGPL035","AGPL118","AGPL017",
        "AGPL036","AGPL038","AGPL124","AGPL106","AGPL215","AGPL041",
        "AGPL043","AGPL044","AGPL045","AGPL046","AGPL047","AGPL113",
        "AGPL209","AGPL120","AGPL102","AGPL128","AGPL081","AGPL126",
        "AGPL129","AGPL131","AGPL127","AGPL175","AGPL161"],
    C: ["AGPL237","AGPL238","AGPL065","AGPL221","AGPL234","AGPL138",
        "AGPL146","AGPL225","AGPL222","AGPL064","AGPL066","AGPL147",
        "AGPL067","AGPL148","AGPL089","AGPL143","AGPL199","AGPL123",
        "AGPL236","AGPL207","AGPL037","AGPL042","AGPL110","AGPL111"],
    D: ["AGPL216","AGPL030"]
  };

  // Weekly averages per grade
  const weeklyAvg = { A: 2000, B: 1500, C: 1000, D: 3000 };

  function getGrade(branchId) {
    for (const [grade, ids] of Object.entries(branchGrades)) {
      if (ids.includes(branchId)) return grade;
    }
    return 'A'; // fallback
  }

  function formatGrams(n) {
    try { return new Intl.NumberFormat().format(n) + ' g'; }
    catch { return n + ' g'; }
  }

  function computeTodayTarget(branchId) {
    const grade = getGrade(branchId);
    const weekly = weeklyAvg[grade] || weeklyAvg['A'];
    const dailyBase = weekly / 6; // Mon–Sat
    const dow = new Date().getDay(); // 0=Sun,1=Mon,...6=Sat

    if (dow === 0) return 0; // Sunday no target

    let adj = 0;
    if (dow === 2 || dow === 5) adj = -100; // Tue, Fri
    else if (dow === 1 || dow === 6) adj = +100; // Mon, Sat

    // Ensure minimum 100 g (except Sunday which is 0)
    return Math.max(100, dailyBase + adj);
  }

  document.getElementById('branchTarget').textContent =
    "Today's Target: " + formatGrams(computeTodayTarget(branchId));
})();
</script>

		<div class="col-lg-4">
			<div class="hpanel stats">
				<div class="panel-body" style="height:120px">
					<div class="stats-title pull-left">
						<h3 class="text-success">Business</h3>
					</div>
					<div class="stats-icon pull-right">
						<i style="color:#990000" class="pe-7s-users fa-3x"></i>
					</div>
					<div class="clearfix"></div>
					<div class="col-xs-2" style="padding:0px">
						<small class="stat-label font-extra-bold" style="color:#636464">WALKIN</small>
						<h4><?php echo $miscData['totalWalkin']; ?></h4>
					</div>
					<div class="col-xs-2" style="padding:0px">
						<small class="stat-label font-extra-bold" style="color:#636464">SOLD</small>
						<h4><?php echo $miscData['totalSold']; ?></h4>
					</div>
					<div class="col-xs-2" style="padding:0px">
						<small class="stat-label font-extra-bold" style="color:#636464">Branch</small>
						<div id="branchCOG" data-branch="<?php echo $_SESSION['branchCode']  ?>" style="padding-top: 5px; color: #990000; cursor: pointer; text-decoration: underline; font-style: italic;">
							COG
						</div>
					</div>
					<div class="col-xs-2" style="padding:0px">
						<small class="stat-label font-extra-bold" style="color:#636464">BM</small>
						<div id="bmCOG" data-bm="<?php echo $_SESSION['employeeId']  ?>" style="padding-top: 5px; color: #990000; cursor: pointer; text-decoration: underline; font-style: italic;">
							COG
						</div>
					</div>
					<div class="col-xs-2" style="padding:0px">
						<small class="stat-label font-extra-bold" style="color:#636464">Your Admin:</small>
						<button class="btn" style="margin-left: 18px;" onclick="window.open('https://wa.me/91<?php echo $contact; ?>', '_blank');">
							<?php echo $contact; ?>
						</button>

					</div>
				</div>
			</div>
		</div>

		<!-- ------- DAILY QUIZ RELATED ------- -->
		<?php if (isset($_SESSION['survey']) && $_SESSION['survey'] == "All") { ?>
			<div class="col-lg-3">
				<div class="hpanel">
					<div class="panel-body text-center" style="height:120px; background-image: linear-gradient(80deg, #fa709a, #fee140);">
						<h5 class="font-extra-bold" style="background: linear-gradient(135deg, #800793, #07CFBE); background-clip: text; color: transparent;">
							Click Here to take today's survey <br> (compulsory)
						</h5>
						<button id="feedbackModalBtn" type="button" class="btn btn-primary tilt-shaking-button" style="background-image: linear-gradient(135deg, #800793, #07CFBE); border: none;">Survey</button>
					</div>
				</div>
			</div>
			<div
				id="feedback"
				data-branchid="<?php echo $branchCode; ?>"
				data-empid="<?php echo $_SESSION['employeeId']; ?>">
			</div>
		<?php } ?>
		<!-- ------- END OF DAILY QUIZ RELATED -------- -->

		<?php if ($miscData['ClosingDate'] == $date) { ?>
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-body">
						<h3 class='text-center' style='margin-bottom: 10px; color:#990000'>BRANCH IS CLOSED, TO REOPEN & BILL - CALL APPROVAL TEAM : 8925537846</h3>
					</div>
				</div>
			</div>
		<?php } ?>

		<!--   ENQUIRY / PROCEED TO BUSINESS   -->
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive project-list">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th>Customer</th>
									<th>Mobile</th>
									<th style='text-align:center;' colspan="2">Quotation</th>
									<th style='text-align:center;'>Action</th>
									<th style='text-align:center;'>Info</th>
									<th class='text-center'></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$query = mysqli_query($con, "SELECT Id,customer,contact,quotation,status,time,extra,concat('XXXXXX', right(contact, 4)) as maskedContact
									FROM everycustomer
									WHERE date='$date' AND branch='$branchCode' AND status IN ('Begin', '0', 'Blocked')
									ORDER BY Id ASC");

								while ($row = mysqli_fetch_assoc($query)) {

									if ($row['extra'] != '') {
										$extra = json_decode($row['extra'], true);
										$bill_type = $extra['Pledge'];
										$bills = $extra['bills'];
									} else {
										$bill_type = "no";
										$bills = 0;
									}

									$contact = $row['contact'];
									$walkin_data = mysqli_fetch_Assoc(mysqli_query($con, "SELECT count(*) AS walkin FROM walkin WHERE mobile='$contact'"));

									//CHECK PHONE NUMBER
									$checkQ1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS countT FROM fraud WHERE phone='$contact'"));
									if ($checkQ1['countT'] > 0) {
										echo "<tr>";
										echo "<td class='text-danger'>" . $row['customer'] . "</td>";
										echo "<td class='text-danger'>" . $row['maskedContact'] . "</td>";
										echo "<td style='text-align:center' colspan='5'><button class='btn btn-danger'> CALL ZONAL ( THIEF / FRAUD )</button></td>";
										echo "</tr>";
									} else {
										/* ——— Minimal tooltip text visibility fix (no design change) ——— */
										if (empty($GLOBALS['__tooltip_text_fix'])) {
											echo '<style>
												.tooltip, .tooltipBilling { overflow: visible !important; }
												.tooltip .tooltiptext,
												.tooltipBilling .tooltiptextBilling {
													word-break: keep-all;     /* don’t split words */
													overflow: visible;        /* don’t clip the bubble */
													max-width: none;          /* remove any width cap */
													z-index: 2147483647;      /* sit above everything */
												}
												</style>';
											$GLOBALS['__tooltip_text_fix'] = true;
										}

										echo "<tr>";
										echo "<td>" . $row['customer'] . "</td>";
										echo "<td>" . $row['maskedContact'] . "</td>";

										// QUOTATION BUTTON
										echo "<td style='text-align:right'><a class='btn btn-success btn-user' data-toggle='modal' data-target='#quotationModal' data-rowid='" . $row['Id'] . "'  data-rowname='" . $row['customer'] . "'><i class='fa fa-quora' style='font-size:15px;color:#ffa500'></i></a></td>";

										// VIEW QUOTATION IMAGE
										if ($row['quotation'] == '') {
											echo "<td></td>";
										} else {
											$decoded = json_decode($row['quotation'], true);
											echo "<td><a target='_BLANK' href='QuotationImage/" . $decoded['image'] . "'><button class='btn btn-circle' type='button'><i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69'></i></button></a></td>";
										}

										// ENQUIRY
										if ($row['quotation'] == '') {
											echo "<td style='text-align:center;'>
												<button disabled class='btn btn-success btn-user tooltip' style='margin-right:25px;'>
												<i class='fa fa-comments' style='color:#ffa500'></i>
												<span class='tooltiptext'>No Quotation Given</span>
												<b> ENQUIRY</b>
												</button>";
										} else {
											echo "<td style='text-align:center;'><a href='xbranchEnquiry.php?id=" . $row['Id'] . "'class='btn btn-success btn-user' style='margin-right:25px'><i class='fa fa-comments' style='color:#ffa500'></i><b> ENQUIRY</b></a>";
										}

										// BILLING (requires BOTH: Quotation given AND Verified by VM)
										$hasQuotation = !empty($row['quotation']);
										$isVerifiedVM = ($row['status'] == '0'); // '0' == Verified by VM in your logic
										$btnLabel     = ($bill_type == "yes") ? " PLEDGE" : " BILLING";
										$btnStyle     = ($bill_type == "yes") ? "background-color: #c7254e; border-color: #c7254e; width:100%" : "";

										if ($row['status'] == "Blocked") {
											// BLOCK THE CUSTOMER FROM BILLING UPON BRANCH VISIT AGAIN WITH PREVIOUSLY BILLED IN LESS THAN 20DAYS
											echo "<button class='btn btn-danger'><span style='color:#ffcf40' class='fa fa-phone'></span>  CALL APPROVAL TEAM TO UNBLOCK </button></td>";
										} else {
											if ($miscData['ClosingDate'] == $date) {
												echo "<a class='btn btn-success btn-user' disabled><i class='fa fa-arrow-right' style='color:#ffa500'></i><b>BILLING</b></a></td>";
											} else {
												if (!$hasQuotation || !$isVerifiedVM) {
													// Show combined reasons in the tooltip (one or both)
													$reasons = [];
													if (!$hasQuotation) {
														$reasons[] = " No Quotation<br> Given ";
													}
													if (!$isVerifiedVM) {
														$reasons[] = " Not Verified<br>By VM ";
													}
													$reasonHtml = implode("&<br>", $reasons);

													echo "<button disabled class='btn btn-success btn-user tooltipBilling' style='{$btnStyle}'>
																<i class='fa fa-comments' style='color:#ffa500'></i>
																<span class='tooltiptextBilling'>" . $reasonHtml . "</span>
																<b>" . $btnLabel . "</b>
															  </button></td>";
												} else {
													// Both satisfied -> enable action
													if ($bill_type == "yes") {
														echo "<a href='addPledgeData.php?id=" . $row['Id'] . "' class='btn btn-success btn-user' style='background-color: #c7254e; border-color: #c7254e;'><i class='fa fa-arrow-right' style='color:#ffa500'></i><b> PLEDGE</b></a></td>";
													} else {
														echo "<a href='xcheckCustomer.php?contact=" . $row['contact'] . "&Id=" . $row['Id'] . "&encTime=" . $row['time'] . "' class='btn btn-success btn-user'><i class='fa fa-arrow-right' style='color:#ffa500'></i><b> BILLING</b></a></td>";
													}
												}
											}
										}

										echo "<td style='text-align:center'><a target='_BLANK' class='btn btn-default' href='xeveryCustomerDetails.php?id=" . $row['contact'] . "'> Bills : " . $bills . "<br> Enquiry : " . $walkin_data['walkin'] . " </a></td>";

										echo "<td class='text-center'><button class='getRate' data-cashrate='" . $goldRate['cash'] . "' data-impsrate='" . $goldRate['transferRate'] . "' data-silverrate='" . $silverRate['cash'] . "'>Rate</button></td>";

										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

<!-- ==== State-aware highlights modal (final, screenshot-style) ==== -->
<?php
$stateName = '';
$cityName  = '';
if ($stmt = $con->prepare("SELECT state, city FROM branch WHERE branchId = ? LIMIT 1")) {
  $stmt->bind_param("s", $branchCode);
  $stmt->execute();
  $stmt->bind_result($stateName, $cityName);
  $stmt->fetch();
  $stmt->close();
}

$today = date('Y-m-d');

$loserImageEnabled = false;
if ($chk = mysqli_query($con, "SHOW COLUMNS FROM state_highlight_losers LIKE 'image_path'")) {
  $loserImageEnabled = (mysqli_num_rows($chk) > 0);
  mysqli_free_result($chk);
}

$showHighlights = false;
$winners = [];
$losers  = [];
$losFrom = $losTo = null;

/* ---- region helpers ---- */
function shx_winner_keys($state, $city) {
  $S = strtoupper(trim((string)$state));
  $C = strtoupper(trim((string)$city));

  if ($S === 'KARNATAKA') {
    if ($C === 'BENGALURU' || $C === 'BANGALORE') return ['Karnataka','Bangalore'];
    return ['Karnataka','Outside Bangalore'];
  }

  if ($S === 'TAMILNADU' || $S === 'TAMIL NADU' || $S === 'PONDICHERRY' || $S === 'PUDUCHERRY') {
    if ($C === 'CHENNAI') return ['TamilNadu','Pondicherry','Chennai'];
    return ['TamilNadu','Pondicherry','Outside Chennai'];
  }

  if ($S === 'TELANGANA') {
    if ($C === 'HYDERABAD') return ['Telangana','Hyderabad'];
    return ['Telangana','Outside Hyderabad'];
  }

  if ($S === 'ANDHRAPRADESH' || $S === 'ANDHRA PRADESH') {
    return ['AndhraPradesh','Andhra Pradesh'];
  }

  return [$state ?: ''];
}

function shx_bind_params_array(mysqli_stmt $stmt, string $types, array $params){
  $refs = [];
  $refs[] = &$types;
  foreach ($params as $k => $v) $refs[] = &$params[$k];
  return call_user_func_array([$stmt,'bind_param'],$refs);
}

/* ---- build state keys ---- */
$keys = shx_winner_keys($stateName, $cityName);
$ph   = implode(',', array_fill(0, count($keys), '?'));

/* ---- winners: show BOTH parent + local winners active today ---- */
$sqlW = "
  SELECT rank_no, person_name, role_title, branch_name, image_path
  FROM state_highlight_winners
  WHERE state IN ($ph) AND is_active=1 AND ? BETWEEN display_start_date AND display_end_date
  ORDER BY rank_no ASC, person_name ASC";
if ($st = $con->prepare($sqlW)) {
  $types  = str_repeat('s', count($keys)) . 's';
  $params = array_merge($keys, [$today]);
  shx_bind_params_array($st, $types, $params);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $winners[] = $r;
  $st->close();
}

/* ---- losers: pick the most recent active window across the same keys ---- */
$sqlLW = "
  SELECT display_start_date, display_end_date
  FROM state_highlight_losers
  WHERE state IN ($ph) AND is_active=1 AND ? BETWEEN display_start_date AND display_end_date
  ORDER BY display_start_date DESC LIMIT 1";
if ($st = $con->prepare($sqlLW)) {
  $types  = str_repeat('s', count($keys)) . 's';
  $params = array_merge($keys, [$today]);
  shx_bind_params_array($st, $types, $params);
  $st->execute();
  $st->bind_result($losFrom, $losTo);
  $st->fetch();
  $st->close();
}

if ($losFrom && $losTo) {
  $loserFields = $loserImageEnabled
    ? "person_name, branch_name, image_path"
    : "person_name, branch_name";

  $sqlL = "
    SELECT $loserFields
    FROM state_highlight_losers
    WHERE state IN ($ph) AND is_active=1
      AND display_start_date=? AND display_end_date=?
    ORDER BY person_name ASC";
  if ($st = $con->prepare($sqlL)) {
    $types  = str_repeat('s', count($keys)) . 'ss';
    $params = array_merge($keys, [$losFrom, $losTo]);
    shx_bind_params_array($st, $types, $params);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) $losers[] = $r;
    $st->close();
  }
}

$showHighlights = (count($winners) > 0 || count($losers) > 0);
?>

<?php if ($showHighlights): ?>
<style>
  .tp-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.35);display:grid;place-items:center;z-index:9999}
  .tp-header{position:sticky;top:0;z-index:3;display:flex;justify-content:space-between;align-items:center; padding:12px 56px 12px 16px;background:linear-gradient(135deg,#990000,#6b0000);color:#fff}
  .tp-title{font-weight:800;letter-spacing:.3px}
  .tp-date-pill,.tp-window{font-size:12px;opacity:.95;background:rgba(255,255,255,.12);padding:6px 10px;border-radius:999px;}
  .tp-header--strip{border-top:1px solid #eee;background:#7b0000}
  .tp-scroll{padding:16px;overflow:auto}
  .tp-modal{position:relative;width:min(1100px,96vw);max-height:92vh;background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden}
  .tp-close{position:absolute;top:10px;right:12px;z-index:10;width:32px;height:32px;display:grid;place-items:center;border:1px solid #eee;border-radius:999px;background:#fff;color:#333;font-size:18px;line-height:0;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.08)}
  .tp-date-pill{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);margin:0;z-index:5}
  .tp-content{margin-bottom:12px}
  .tp-photo img{width:100%;height:220px;object-fit:cover;display:block}
  .tp-meta{padding:12px 14px}
  .tp-rank{font-size:24px;font-weight:800;margin-bottom:4px}
  .tp-name{font-weight:800}
  .tp-sub{font-size:12px;color:#6b7280}
  .tp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,260px));gap:16px;justify-content:center}
  .tp-card{width:260px;max-width:100%;background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);overflow:hidden;display:flex;flex-direction:column}
  .tp-losers-grid .tp-photo img{height:180px}
  .tp-empty{color:#6b7280;padding:6px 0}
</style>

<div class="tp-backdrop show" id="tpBackdrop" aria-hidden="false">
  <div class="tp-modal" role="dialog" aria-modal="true" aria-labelledby="tpTitle">
    <button class="tp-close" id="tpClose" aria-label="Close">&times;</button>
    <div class="tp-header">
      <div class="tp-title" id="tpTitle">TOP/BEST PERFORMERS — <?= htmlspecialchars(strtoupper($stateName)) ?></div>
      <div class="tp-date-pill"><?= date('l, d M Y', strtotime($today)) ?></div>
    </div>

    <div class="tp-scroll">
      <div class="tp-content">
        <?php if ($winners): ?>
          <div class="tp-grid">
            <?php foreach ($winners as $w): ?>
              <div class="tp-card">
                <div class="tp-photo">
                  <img src="<?= htmlspecialchars($w['image_path']) ?>" alt="<?= htmlspecialchars($w['person_name']) ?>" onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,<?= rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"400\" height=\"220\" viewBox=\"0 0 400 220\"><rect width=\"400\" height=\"220\" fill=\"#e5e7eb\"/><text x=\"50%\" y=\"50%\" dominant-baseline=\"middle\" text-anchor=\"middle\" font-family=\"Arial\" font-size=\"22\" fill=\"#9ca3af\">No Photo</text></svg>') ?>';">
                </div>
                <div class="tp-meta">
                  <div class="tp-rank"><?= (int)$w['rank_no'] ?></div>
                  <div class="tp-name"><?= htmlspecialchars($w['person_name']) ?></div>
                  <?php if (!empty($w['branch_name'])): ?><div class="tp-sub"><?= htmlspecialchars($w['branch_name']) ?></div><?php endif; ?>
                  <?php if (!empty($w['role_title'])): ?><div class="tp-sub"><?= htmlspecialchars($w['role_title']) ?></div><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="tp-empty">No winners for this window.</div>
        <?php endif; ?>
      </div>

      <div class="tp-header tp-header--strip" style="position:sticky;top:0;margin:12px -16px 0 -16px;padding-left:16px;padding-right:16px;">
        <div class="tp-title">LOWEST/WORST PERFORMERS</div>
        <div class="tp-window"><?= date('d M Y', strtotime($windowFrom)) ?> – <?= date('d M Y', strtotime($windowTo)) ?></div>
      </div>

      <div class="tp-content" style="margin-top:12px">
        <?php if ($losers): ?>
          <div class="tp-grid tp-losers-grid">
            <?php foreach ($losers as $L): ?>
              <div class="tp-card">
                <?php if ($loserImageEnabled && !empty($L['image_path'])): ?>
                  <div class="tp-photo"><img src="<?= htmlspecialchars($L['image_path']) ?>" alt="" onerror="this.parentElement.remove();"></div>
                <?php endif; ?>
                <div class="tp-meta">
                  <div class="tp-name"><?= htmlspecialchars($L['person_name']) ?></div>
                  <?php if (!empty($L['branch_name'])): ?><div class="tp-sub"><?= htmlspecialchars($L['branch_name']) ?></div><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="tp-empty">No losers for this window.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const backdrop = document.getElementById('tpBackdrop');
  const modal    = backdrop?.querySelector('.tp-modal');
  const btnClose = document.getElementById('tpClose');
  function closeModal(){ backdrop?.remove(); document.removeEventListener('keydown', onKey); }
  function onKey(e){ if (e.key === 'Escape' || e.key === 'Esc') closeModal(); }
  btnClose?.addEventListener('click', closeModal);
  document.addEventListener('keydown', onKey);
  backdrop?.addEventListener('mousedown', (e)=>{ if (e.target === backdrop) closeModal(); });
  modal?.addEventListener('mousedown', (e)=> e.stopPropagation());
})();
</script>
<?php endif; ?>								
								
<!--- Winners Display Modal End --->



<?php include("footer.php"); ?>
<script type="text/javascript" src="scripts/html2canvas.min.js"></script>
<script src="scripts/quotation-v3.js"></script>
<script src="vendor/sweetalert/lib/sweet-alert.min.js"></script>
<script>
document.addEventListener('click', function(e){
  var a = e.target.closest('[data-toggle="modal"][data-target="#addCustomerModal"]');
  if (!a) return;
  var bd = document.getElementById('tpBackdrop');
  if (bd) {
    bd.classList.remove('show');
    bd.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }
});
</script>
<script>
	// If the overlay is visible and user clicks the Add Customer CTA, hide overlay first
document.addEventListener('click', (e) => {
  const cta = e.target.closest('.add-customer-cta');
  if (!cta) return;
  const bd = document.getElementById('tpBackdrop');
  if (bd && bd.classList.contains('show')) {
    // Close the highlight overlay so the Bootstrap modal is clearly visible
    bd.classList.remove('show');
    bd.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
});

// When Bootstrap is about to show Add Customer / Quotation modal, ensure overlay can’t block it
if (window.jQuery) {
  jQuery(function ($) {
    $(document).on('show.bs.modal', '#addCustomerModal, #quotationModal', #, function () {
      const bd = document.getElementById('tpBackdrop');
      if (bd) {
        // Either hide it or simply let CSS (.modal-open #tpBackdrop) push it behind
        bd.classList.remove('show');
        bd.setAttribute('aria-hidden', 'true');
      }
      // Bootstrap will add .modal-open on body, which triggers our CSS rule
    });
  });
}
</script>
<script>
  (function () {
    const backdrop = document.getElementById('tpBackdrop');
    const closeBtn = document.getElementById('tpClose');

    function showModal() {
      if (!backdrop) return;
      if (!backdrop.classList.contains('show')) {
        backdrop.classList.add('show');
      }
      backdrop.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function hideModal() {
      if (!backdrop) return;
      backdrop.classList.remove('show');
      backdrop.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    // Show on window load (keeps your original behavior)
    window.addEventListener('load', showModal);

    // Close on button
    if (closeBtn) closeBtn.addEventListener('click', hideModal);

    // Close when clicking outside the modal
    if (backdrop) {
      backdrop.addEventListener('click', (e) => {
        const modal = e.currentTarget.querySelector('.tp-modal');
        if (!modal || !modal.contains(e.target)) {
          hideModal();
        }
      });
    }

    // ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') hideModal();
    });

    // ------ Today's Rate (with jQuery if available; vanilla fallback otherwise) ------
    const handleGetRate = (el) => {
      const cashRate = el.dataset.cashrate ?? '-';
      const impsRate = el.dataset.impsrate ?? '-';
      const silverRate = el.dataset.silverrate ?? '-';
      const text =
        "Cash Rate: " + cashRate +
        "\n IMPS Rate: " + impsRate +
        "\n Silver: " + silverRate;

      if (typeof swal === 'function') {
        swal({ title: "Today's Rate", text: text });
      } else {
        alert("Today's Rate\n\n" + text);
      }
    };

    if (window.jQuery && typeof jQuery.fn.on === 'function') {
      jQuery(function ($) {
        // Namespace the handler to avoid duplicates
        $('.getRate').off('click.__rate').on('click.__rate', function () {
          handleGetRate(this);
        });
      });
    } else {
      // Vanilla fallback
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.getRate');
        if (btn) handleGetRate(btn);
      });
    }

    // ------ COG buttons ------
    const branchCOG = document.getElementById('branchCOG');
    const bmCOG = document.getElementById('bmCOG');

    async function safeFetchText(url) {
      try {
        const resp = await fetch(url, { credentials: 'same-origin' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        return await resp.text();
      } catch (err) {
        console.error('Fetch failed:', err);
        return null;
      }
    }

    async function getBranchCOG() {
      if (!branchCOG) return;

      const originalLabel = branchCOG.textContent;
      branchCOG.textContent = 'Loading...';
      branchCOG.disabled = true;

      const branchId = branchCOG.dataset.branch || '';
      const url = 'utils/misc/getCOG.php?branchcog=true&branch=' + encodeURIComponent(branchId);
      const result = await safeFetchText(url);

      branchCOG.textContent = originalLabel;
      branchCOG.disabled = false;

      if (result) {
        const value = (String(result).match(/[\d.]+/) || [''])[0];
        const title = value ? value + '%' : String(result);
        const text = 'Branch Billing % (Last 30 days)';
        if (typeof swal === 'function') {
          swal({ title, text });
        } else {
          alert(title + '\n' + text);
        }
      } else {
        if (typeof swal === 'function') {
          swal('Oops', 'Unable to fetch COG. Please try again.', 'error');
        } else {
          alert('Unable to fetch COG.');
        }
      }
    }

    async function getBMCOG() {
      if (!bmCOG) return;

      const originalLabel = bmCOG.textContent;
      bmCOG.textContent = 'Loading...';
      bmCOG.disabled = true;

      const empId = bmCOG.dataset.bm || '';
      const url = 'utils/misc/getCOG.php?bmcog=true&empId=' + encodeURIComponent(empId);
      const result = await safeFetchText(url);

      bmCOG.textContent = originalLabel;
      bmCOG.disabled = false;

      if (result) {
        const value = (String(result).match(/[\d.]+/) || [''])[0];
        const title = value ? value + '%' : String(result);
        const text = 'Your Billing % (Last 30 Day)';
        if (typeof swal === 'function') {
          swal({ title, text });
        } else {
          alert(title + '\n' + text);
        }
      } else {
        if (typeof swal === 'function') {
          swal('Oops', 'Unable to fetch your COG. Please try again.', 'error');
        } else {
          alert('Unable to fetch your COG.');
        }
      }
    }

    // Attach (and de-duplicate) listeners
    if (branchCOG) {
      branchCOG.removeEventListener('click', getBranchCOG);
      branchCOG.addEventListener('click', getBranchCOG);
    }
    if (bmCOG) {
      bmCOG.removeEventListener('click', getBMCOG);
      bmCOG.addEventListener('click', getBMCOG);
    }
  })();
</script>
