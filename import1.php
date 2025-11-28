<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Kolkata');

/* -----------------------------
   Auth / Menus
------------------------------*/
$type = $_SESSION['usertype'] ?? '';
if ($type == 'Master') {
	include("header.php"); include("menumaster.php");
} else if ($type == 'SocialMedia') {
	include("header.php"); include("menuSocialMedia.php");
} else if ($type == 'IssueHead') {
	include("header.php"); include("menuIssueHead.php");
} else if ($type == 'Call Centre') {
	include("header.php"); include("menuCall.php");
} else if ($type == 'Issuecall') {
	include("header.php"); include("menuissues.php");
} else if ($type == 'Leads') {
	include("header.php"); include("menuLeads.php");
}else if($type == 'BD') {
	include("header.php");include("menubd.php");
}else {
	include("logout.php"); exit();
}

include("dbConnection.php");
@mysqli_set_charset($con, 'utf8mb4');

$today = date('Y-m-d');

/* -----------------------------
   Helpers
------------------------------*/
function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function val_date($d){
	$d = trim((string)$d); if ($d === '') return '';
	$ts = strtotime($d);  return $ts ? date('Y-m-d', $ts) : '';
}

/* Phone utilities */
function normalize_mobile($m){
	$m = preg_replace('/\D+/', '', (string)$m);
	if (strlen($m) > 10) { $m = substr($m, -10); }
	return $m;
}
function is_sequential_like($m){
	$asc = '01234567890'; $desc = '09876543210';
	return (strpos($asc, $m) !== false) || (strpos($desc, $m) !== false);
}
function is_all_same_digit($m){ return preg_match('/^(\d)\1+$/', $m) === 1; }
function is_known_bad($m){
	$bad = ['111111','123456','987654321','9999999','000000','222222','333333','444444','555555','666666','777777','888888','999999','123123'];
	foreach ($bad as $b) { if (strpos($m, $b) !== false) return true; }
	return false;
}
function is_valid_mobile_for_display($m){
	$m = normalize_mobile($m);
	if ($m === '' || strlen($m) < 10) return false;
	if (is_all_same_digit($m) || is_sequential_like($m) || is_known_bad($m)) return false;
	return true;
}

/* -----------------------------
   Read filters (apply to ALL tabs)
------------------------------*/
$fromDate = isset($_GET['from']) ? val_date($_GET['from']) : $today;
$toDate   = isset($_GET['to'])   ? val_date($_GET['to'])   : $today;
if ($fromDate === '') $fromDate = $today;
if ($toDate === '')   $toDate   = $today;

/* -----------------------------
   POST: Append comments + update
------------------------------*/
if (isset($_POST['submit'])) {
	$id       = (int)($_POST['id'] ?? 0);
	$remarks  = trim($_POST['remarks'] ?? '');
	$newNote  = trim($_POST['comments'] ?? '');
	$followup = trim($_POST['followup'] ?? ''); // planned visit date (optional)
	$status   = 'Done';
	$stampDay = date('Y-m-d');

	if ($id <= 0 || $remarks === '' || $newNote === '') {
		echo "<script>alert('Missing required fields');</script>";
	} else {
		// current comments
		$oldComments = '';
		if ($stmt0 = $con->prepare("SELECT comments FROM enquiry WHERE id=?")) {
			$stmt0->bind_param('i', $id);
			$stmt0->execute();
			$stmt0->bind_result($oldComments);
			$stmt0->fetch();
			$stmt0->close();
		}
		$entry   = "Remarks: {$remarks}\n" . $newNote;
		$divider = "\n------------------------------\n";
		$combinedComments = ($oldComments && trim($oldComments) !== '')
			? $oldComments . $divider . $entry
			: $entry;

		if ($followup !== '') {
			$fd = val_date($followup);
			if ($fd === '') { echo "<script>alert('Invalid follow-up date');</script>"; exit; }
			// planned date -> updateDate, today's date -> followup
			$stmt = $con->prepare("
				UPDATE enquiry
				   SET remarks=?, comments=?, updateDate=?, followup=?, status=?
				 WHERE id=?
			");
			$stmt->bind_param('sssssi', $remarks, $combinedComments, $fd, $stampDay, $status, $id);
		} else {
			$stmt = $con->prepare("
				UPDATE enquiry
				   SET remarks=?, comments=?, followup=?, status=?
				 WHERE id=?
			");
			$stmt->bind_param('ssssi', $remarks, $combinedComments, $stampDay, $status, $id);
		}
		$ok = $stmt->execute();
		if ($ok) {
			echo "<script>alert('Remarks & comments updated');</script>";
			// keep current filter on return
			$qs = '?from='.urlencode($fromDate).'&to='.urlencode($toDate);
			echo "<script>setTimeout(function(){ location.href='import1.php$qs'; }, 150);</script>";
			exit;
		} else {
			echo "<script>alert('Update failed');</script>";
		}
	}
}

/* -----------------------------
   POST: Delete (SocialMedia only)
------------------------------*/
if (isset($_POST['rejectBtn']) && $type === 'SocialMedia') {
	$issueId = (int)($_POST['issueId'] ?? 0);
	if ($issueId > 0) {
		$stmt = $con->prepare("DELETE FROM enquiry WHERE id=?");
		$stmt->bind_param('i', $issueId);
		if ($stmt->execute()) {
			echo '<script>alert("Customer Data Deleted");</script>';
			$qs = '?from='.urlencode($fromDate).'&to='.urlencode($toDate);
			echo "<script>setTimeout(function(){ location.href='import1.php$qs'; }, 150);</script>";
			exit;
		} else {
			echo '<script>alert("Error deleting row");</script>';
		}
	}
}

/* -----------------------------
   KPI counters (respect date filter)
------------------------------*/
$cnt = ['total'=>0,'pending'=>0,'follow'=>0,'visited'=>0,'today'=>0];
$qkpi = "
  SELECT
    COUNT(*) AS total_count,
    SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN remarks IN ('Coming Tomorrow','Comming Tomorrow','Planning To Visit') THEN 1 ELSE 0 END) AS followup_count,
    SUM(CASE WHEN remarks='Visited' THEN 1 ELSE 0 END) AS visited_count,
    SUM(CASE WHEN `date` BETWEEN ? AND ? THEN 1 ELSE 0 END) AS range_count
  FROM enquiry
  WHERE `date` BETWEEN ? AND ?
";
if ($stmt = $con->prepare($qkpi)) {
	$stmt->bind_param('ssss', $fromDate, $toDate, $fromDate, $toDate);
	$stmt->execute();
	$r = $stmt->get_result();
	if ($row = $r->fetch_assoc()) {
		$cnt['total']   = (int)$row['total_count'];
		$cnt['pending'] = (int)$row['pending_count'];
		$cnt['follow']  = (int)$row['followup_count'];
		$cnt['visited'] = (int)$row['visited_count'];
		$cnt['today']   = (int)$row['range_count']; // shows count in selected range
	}
	$stmt->close();
}

/* -----------------------------
   Fetch datasets (ALL date filtered)
------------------------------*/
$pendingRows = [];
$stmt = $con->prepare("
  SELECT id,name,mobile,`type`,state,`date`,`time`
    FROM enquiry
   WHERE status='Pending'
     AND `date` BETWEEN ? AND ?
ORDER BY id DESC
   LIMIT 500
");
$stmt->bind_param('ss', $fromDate, $toDate);
$stmt->execute(); $res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $pendingRows[] = $row;
$stmt->close();

$followRows = [];
$stmt = $con->prepare("
  SELECT id,name,mobile,`type`,state,`date`,followup,remarks,comments
    FROM enquiry
   WHERE status='Done'
     AND `date` BETWEEN ? AND ?
ORDER BY id DESC
   LIMIT 500
");
$stmt->bind_param('ss', $fromDate, $toDate);
$stmt->execute(); $res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $followRows[] = $row;
$stmt->close();

$comingRows = [];
$stmt = $con->prepare("
  SELECT id,name,mobile,`type`,state,`date`,remarks,comments,followup
    FROM enquiry
   WHERE status!='Pending'
     AND remarks IN ('Coming Tomorrow','Comming Tomorrow')
     AND `date` BETWEEN ? AND ?
ORDER BY id DESC
   LIMIT 500
");
$stmt->bind_param('ss', $fromDate, $toDate);
$stmt->execute(); $res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $comingRows[] = $row;
$stmt->close();

$planRows = [];
$stmt = $con->prepare("
  SELECT id,name,mobile,`type`,state,`date`,followup,remarks,updateDate,comments
    FROM enquiry
   WHERE status!='Pending'
     AND remarks='Planning To Visit'
     AND `date` BETWEEN ? AND ?
ORDER BY id DESC
   LIMIT 500
");
$stmt->bind_param('ss', $fromDate, $toDate);
$stmt->execute(); $res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $planRows[] = $row;
$stmt->close();

$visitedRows = [];
$stmt = $con->prepare("
  SELECT id,name,mobile,`type`,state,`date`,followup,remarks,updateDate,comments
    FROM enquiry
   WHERE status!='Pending'
     AND remarks='Visited'
     AND `date` BETWEEN ? AND ?
ORDER BY id DESC
   LIMIT 500
");
$stmt->bind_param('ss', $fromDate, $toDate);
$stmt->execute(); $res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $visitedRows[] = $row;
$stmt->close();
?>
<style>
	.tab .nav-tabs { padding:0; margin:0; border:none; }
	.tab .nav-tabs li a{
		color:#123C69;background:#E3E3E3;font-size:11px;font-weight:600;text-align:center;
		letter-spacing:1px;text-transform:uppercase;padding:6px 8px 5px;margin:5px 5px 0 0;border:none;
		border-bottom:3px solid #123C69;border-radius:0;position:relative;z-index:1;transition:all .3s ease .1s;
	}
	.tab .nav-tabs li.active a, .tab .nav-tabs li a:hover, .tab .nav-tabs li.active a:hover{
		color:#f2f2f2;background:#123C69;border:none;border-bottom:3px solid #ffa500;font-weight:600;border-radius:3px;
	}
	.tab .nav-tabs li a:before{content:"";background:#E3E3E3;height:100%;width:100%;position:absolute;bottom:0;left:0;z-index:-1;transition:clip-path .3s ease 0s, height .3s ease .2s;clip-path:polygon(0 0, 100% 0, 100% 100%, 0% 100%);}
	.tab .nav-tabs li.active a:before, .tab .nav-tabs li a:hover:before{height:0;clip-path:polygon(0 0, 0% 0, 100% 100%, 0% 100%);}
	#wrapper{background-color:#E3E3E3;}
	#wrapper h3{text-transform:uppercase;font-weight:600;font-size:20px;color:#123C69;}
	thead{text-transform:uppercase;background-color:#123C69;font-size:10px;}
	thead tr{color:#f2f2f2;}
	.btn-primary{
		display:inline-block;padding:.45em .9em;margin:0 .3em .3em 0;border-radius:.15em;box-sizing:border-box;text-decoration:none;
		font-size:12px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;background-color:#123C69;box-shadow:none;text-align:center;position:relative;
	}
	.text-success{font-weight:600;color:#123C69;}
	.hpanel .panel-body{box-shadow:10px 15px 15px #999;border-radius:3px;padding:15px;background-color:#f5f5f5;}
	.table-responsive .row{margin:0;}
	.table { table-layout: fixed; }
	.table > thead > tr > th, .table > tbody > tr > td { padding:6px 8px; line-height:1.15; vertical-align:middle; }
	.table td{ white-space:pre-wrap; word-wrap:break-word; overflow-wrap:break-word; max-width:250px; min-height:0; }
	.dlt-button{background-color:transparent;color:red;border:none;padding:0;cursor:pointer;}
	.block-button{background-color:transparent;border:0;cursor:pointer;}
	.block-button i, .dlt-button i { font-size:14px !important; line-height:1; }
	.kpi .btn{ padding:10px 12px; font-size:11px; line-height:1.5; box-shadow:none; }
</style>

<div id="wrapper">
	<div class="row content">
		<form action="" method="GET">
			<div class="col-sm-5">
				<h3><i class="trans_Icon fa fa-edit"></i> Website Leads
					<span style='color:#990000'> - <?php echo esc_html($today); ?></span>
				</h3>
			</div>
			<div class="col-sm-3">
				<div class="input-group">
					<span class="input-group-addon text-success">From</span>
					<input type="date" class="form-control" name="from" value="<?php echo esc_html($fromDate); ?>" required>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="input-group">
					<span class="input-group-addon text-success">To</span>
					<input type="date" class="form-control" name="to" value="<?php echo esc_html($toDate); ?>" required>
				</div>
			</div>
			<div class="col-sm-1">
				<button class="btn btn-success btn-block" name="expenseDetailsSubmit">
					<span style="color:#ffcf40" class="fa fa-search"></span> Search
				</button>
			</div>
		</form>

		<div class="col-lg-12"><br><br>
			<div class="hpanel">
				<div class="tab" role="tabpanel">
					<!-- KPIs (filtered to range) -->
					<div class="row kpi" style="margin:0 0 10px 0;">
						<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">Total Leads (in range) - <?php echo (int)$cnt['today']; ?></button></div>
						<div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">Pending - <?php echo (int)$cnt['pending']; ?></button></div>
						<div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">Follow-up - <?php echo (int)$cnt['follow']; ?></button></div>
						<div class="col-lg-2 text-center"><button class="btn btn-default btn-block text-success" type="button">Visited - <?php echo (int)$cnt['visited']; ?></button></div>
						<div class="col-lg-3 text-center"><button class="btn btn-default btn-block text-success" type="button">All-time Total - <?php echo (int)$cnt['total']; ?></button></div>
					</div>

					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a href="#Pending" aria-controls="Pending" role="tab" data-toggle="tab"> Pending </a></li>
						<li><a href="#FollowUp" aria-controls="FollowUp" role="tab" data-toggle="tab"> FollowUp</a></li>
						<li><a href="#ComingTommrow" aria-controls="ComingTommrow" role="tab" data-toggle="tab"> Coming Tomorrow</a></li>
						<li><a href="#Planing_To_Visit" aria-controls="Planing_To_Visit" role="tab" data-toggle="tab"> Planning To Visit </a></li>
						<li><a href="#Visited" aria-controls="Visited" role="tab" data-toggle="tab"> Visited</a></li>
					</ul>

					<div class="tab-content tabs">
						<!-- Pending -->
						<div role="tabpanel" class="tab-pane fade in active" id="Pending">
							<div class="panel-body">
								<label class="col-sm-12" style="margin-top:10px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example1" class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40" class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Customer Name</th>
												<th>Contact number</th>
												<th>Type</th>
												<th>State</th>
												<th>Date</th>
												<th>Time</th>
												<?php if ($type != 'Master') { ?><th class="text-center"><i class="fa fa-edit"></i></th><?php } ?>
												<?php if ($type == 'SocialMedia') { ?><th class="text-center"><i class="fa fa-trash"></i></th><?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1; $seen = [];
											foreach ($pendingRows as $row):
												$mob = normalize_mobile($row['mobile']);
												if (!is_valid_mobile_for_display($mob)) continue;
												if (isset($seen[$mob])) continue; $seen[$mob] = true;
											?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo esc_html($row['name']); ?></td>
													<td><?php echo esc_html($mob); ?></td>
													<td><?php echo esc_html($row['type']); ?></td>
													<td><?php echo esc_html($row['state']); ?></td>
													<td><?php echo esc_html($row['date']); ?></td>
													<td><?php echo esc_html($row['time']); ?></td>
													<?php if ($type != 'Master'): ?>
														<td class="text-center">
															<button class="block-button btn-sm openModal" data-toggle="modal" data-target="#editModal"
																data-id="<?php echo (int)$row['id']; ?>"
																data-name="<?php echo esc_html($row['name']); ?>"
																data-mobile="<?php echo esc_html($mob); ?>"
																data-type="<?php echo esc_html($row['type']); ?>"
																data-state="<?php echo esc_html($row['state']); ?>">
																<i class="fa fa-edit"></i>
															</button>
														</td>
													<?php endif; ?>
													<?php if ($type == 'SocialMedia'): ?>
														<td class="text-center">
															<form method="POST" action="" onsubmit="return confirm('Delete this lead?')">
																<input type="hidden" name="issueId" value="<?php echo (int)$row['id']; ?>">
																<button class="dlt-button" type="submit" name="rejectBtn"><i class="fa fa-trash" style="color:red;"></i></button>
															</form>
														</td>
													<?php endif; ?>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- FollowUp -->
						<div role="tabpanel" class="tab-pane fade" id="FollowUp">
							<div class="panel-body">
								<label class="col-sm-12" style="margin-top:20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example2" class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40" class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Customer Name</th>
												<th>Contact number</th>
												<th>Type</th>
												<th>State</th>
												<th>Date</th>
												<th>Followup Date</th>
												<th>Remarks</th>
												<th>Comments</th>
												<?php if ($type != 'Master') { ?><th class="text-center"><i class="fa fa-edit"></i></th><?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1; $seen = [];
											foreach ($followRows as $row):
												$mob = normalize_mobile($row['mobile']);
												if (!is_valid_mobile_for_display($mob)) continue;
												if (isset($seen[$mob])) continue; $seen[$mob] = true;
											?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo esc_html($row['name']); ?></td>
													<td><?php echo esc_html($mob); ?></td>
													<td><?php echo esc_html($row['type']); ?></td>
													<td><?php echo esc_html($row['state']); ?></td>
													<td><?php echo esc_html($row['date']); ?></td>
													<td><?php echo esc_html($row['followup']); ?></td>
													<td><?php echo esc_html($row['remarks']); ?></td>
													<td style="white-space:pre-line;"><?php echo esc_html($row['comments']); ?></td>
													<?php if ($type != 'Master'): ?>
														<td class="text-center">
															<button class="block-button btn-sm openModal" data-toggle="modal" data-target="#editModal"
																data-id="<?php echo (int)$row['id']; ?>"
																data-name="<?php echo esc_html($row['name']); ?>"
																data-mobile="<?php echo esc_html($mob); ?>"
																data-type="<?php echo esc_html($row['type']); ?>"
																data-state="<?php echo esc_html($row['state']); ?>">
																<i class="fa fa-edit"></i>
															</button>
														</td>
													<?php endif; ?>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Coming Tomorrow -->
						<div role="tabpanel" class="tab-pane fade" id="ComingTommrow">
							<div class="panel-body">
								<label class="col-sm-12" style="margin-top:20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example3" class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40" class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Customer Name</th>
												<th>Contact number</th>
												<th>Type</th>
												<th>State</th>
												<th>Date</th>
												<th>Remarks</th>
												<th>Comments</th>
												<th>Followup Date</th>
												<?php if ($type != 'Master') { ?><th class="text-center"><i class="fa fa-edit"></i></th><?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1; $seen = [];
											foreach ($comingRows as $row):
												$mob = normalize_mobile($row['mobile']);
												if (!is_valid_mobile_for_display($mob)) continue;
												if (isset($seen[$mob])) continue; $seen[$mob] = true;
											?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo esc_html($row['name']); ?></td>
													<td><?php echo esc_html($mob); ?></td>
													<td><?php echo esc_html($row['type']); ?></td>
													<td><?php echo esc_html($row['state']); ?></td>
													<td><?php echo esc_html($row['date']); ?></td>
													<td><?php echo esc_html($row['remarks']); ?></td>
													<td style="white-space:pre-line;"><?php echo esc_html($row['comments']); ?></td>
													<td><?php echo esc_html($row['followup']); ?></td>
													<?php if ($type != 'Master'): ?>
														<td class="text-center">
															<button class="block-button btn-sm openModal" data-toggle="modal" data-target="#editModal"
																data-id="<?php echo (int)$row['id']; ?>"
																data-name="<?php echo esc_html($row['name']); ?>"
																data-mobile="<?php echo esc_html($mob); ?>"
																data-type="<?php echo esc_html($row['type']); ?>"
																data-state="<?php echo esc_html($row['state']); ?>">
																<i class="fa fa-edit"></i>
															</button>
														</td>
													<?php endif; ?>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Planning To Visit -->
						<div role="tabpanel" class="tab-pane fade" id="Planing_To_Visit">
							<div class="panel-body">
								<label class="col-sm-12" style="margin-top:20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="example4" class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40" class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Customer Name</th>
												<th>Contact number</th>
												<th>Type</th>
												<th>State</th>
												<th>Date</th>
												<th>Followup Date</th>
												<th>Remarks</th>
												<th>Visit Date</th>
												<th>Comments</th>
												<?php if ($type != 'Master') { ?><th class="text-center"><i class="fa fa-edit"></i></th><?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1; $seen = [];
											foreach ($planRows as $row):
												$mob = normalize_mobile($row['mobile']);
												if (!is_valid_mobile_for_display($mob)) continue;
												if (isset($seen[$mob])) continue; $seen[$mob] = true;
											?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo esc_html($row['name']); ?></td>
													<td><?php echo esc_html($mob); ?></td>
													<td><?php echo esc_html($row['type']); ?></td>
													<td><?php echo esc_html($row['state']); ?></td>
													<td><?php echo esc_html($row['date']); ?></td>
													<td><?php echo esc_html($row['followup']); ?></td>
													<td><?php echo esc_html($row['remarks']); ?></td>
													<td><?php echo esc_html($row['updateDate']); ?></td>
													<td style="white-space:pre-line;"><?php echo esc_html($row['comments']); ?></td>
													<?php if ($type != 'Master'): ?>
														<td class="text-center">
															<button class="block-button btn-sm openModal" data-toggle="modal" data-target="#editModal"
																data-id="<?php echo (int)$row['id']; ?>"
																data-name="<?php echo esc_html($row['name']); ?>"
																data-mobile="<?php echo esc_html($mob); ?>"
																data-type="<?php echo esc_html($row['type']); ?>"
																data-state="<?php echo esc_html($row['state']); ?>">
																<i class="fa fa-edit"></i>
															</button>
														</td>
													<?php endif; ?>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Visited -->
						<div role="tabpanel" class="tab-pane fade" id="Visited">
							<div class="panel-body">
								<label class="col-sm-12" style="margin-top:20px;"></label>
								<div class="col-sm-12 table-responsive">
									<table id="call1" class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th class="text-center"><i style="color:#ffcf40" class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Customer Name</th>
												<th>Contact number</th>
												<th>Type</th>
												<th>State</th>
												<th>Date</th>
												<th>Followup Date</th>
												<th>Remarks</th>
												<th>Visit Date</th>
												<th>Comments</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 1; $seen = [];
											foreach ($visitedRows as $row):
												$mob = normalize_mobile($row['mobile']);
												if (!is_valid_mobile_for_display($mob)) continue;
												if (isset($seen[$mob])) continue; $seen[$mob] = true;
											?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo esc_html($row['name']); ?></td>
													<td><?php echo esc_html($mob); ?></td>
													<td><?php echo esc_html($row['type']); ?></td>
													<td><?php echo esc_html($row['state']); ?></td>
													<td><?php echo esc_html($row['date']); ?></td>
													<td><?php echo esc_html($row['followup']); ?></td>
													<td><?php echo esc_html($row['remarks']); ?></td>
													<td><?php echo esc_html($row['updateDate']); ?></td>
													<td style="white-space:pre-line;"><?php echo esc_html($row['comments']); ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

					</div><!-- /.tab-content -->
				</div>
			</div>
		</div>
	</div>

	<!-- Single reusable modal -->
	<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLbl" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<form method="POST" action="" class="modal-content">
				<div class="modal-header" style="background-color:#123C69;color:#fff;padding:12px;">
					<h2 id="editModalLbl" style="color:white;font-size:14px;margin-bottom:0;">Update Remarks</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity:1;color:#fff;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" name="id" id="m_id" value="">
					<div class="form-group row">
						<div class="col-sm-6">
							<label class="col-form-label">Customer Name:</label>
							<input type="text" class="form-control" id="m_name" name="name" readonly>
						</div>
						<div class="col-sm-6">
							<label class="col-form-label">Contact Number:</label>
							<input type="text" class="form-control" id="m_mobile" name="mobile" readonly>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-6">
							<label class="col-form-label">Type:</label>
							<input type="text" class="form-control" id="m_type" name="type" readonly>
						</div>
						<div class="col-sm-6">
							<label class="col-form-label">State:</label>
							<input type="text" class="form-control" id="m_state" name="state" readonly>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-6">
							<label class="col-form-label">Select Remarks:</label>
							<select name="remarks" id="m_remarks" class="form-control" required>
								<option selected disabled value="">Select Remarks</option>
								<option value="Coming Tomorrow">Coming Tomorrow</option>
								<option value="Planning To Visit">Planning To Visit</option>
								<option value="Not Interested">Not Interested</option>
								<option value="Not Reachable">Not Reachable</option>
								<option value="Duplicate Number">Duplicate Number</option>
								<option value="Wrong Number">Wrong Number</option>
								<option value="RNR">RNR</option>
								<option value="Busy">Busy</option>
								<option value="Pending">Pending</option>
								<option value="Enquiry">Enquiry</option>
								<option value="Visited">Visited</option>
							</select>
						</div>
						<div class="col-sm-6">
							<label class="col-form-label">Follow-up / Planned Visit Date</label>
							<input type="date" class="form-control" id="m_followup" name="followup">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<label class="col-form-label">Add Comment (will be appended):</label>
							<textarea class="form-control" id="m_comments" name="comments" rows="3" required></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer" style="display:flex;justify-content:flex-end;">
					<button class="btn btn-primary" type="submit" name="submit" style="padding:8px 16px;">Submit</button>
				</div>
			</form>

		</div>
	</div>
	<?php include("footer.php"); ?>
</div>

<script>
	// fill modal from button data
	$('#editModal').on('show.bs.modal', function(e) {
		var btn = $(e.relatedTarget);
		$('#m_id').val(btn.data('id'));
		$('#m_name').val(btn.data('name'));
		$('#m_mobile').val(btn.data('mobile'));
		$('#m_type').val(btn.data('type'));
		$('#m_state').val(btn.data('state'));
		$('#m_comments').val('');
		$('#m_remarks').val('');
		$('#m_followup').val('');
	});

	// DataTables init guard (avoid re-init)
	var dtCommon = {
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		lengthMenu: [[10,25,50,100,250,-1],[10,25,50,100,250,'All']],
		buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv',  title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf',  title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
		],
		retrieve: true
	};
	function safeInitDT(sel){ if ($(sel).length && !$.fn.DataTable.isDataTable(sel)) $(sel).DataTable(dtCommon); }
	$(function() {
		safeInitDT('#example1');
		safeInitDT('#example2');
		safeInitDT('#example3');
		safeInitDT('#example4');
		safeInitDT('#call1');
		$('a[data-toggle="tab"]').on('shown.bs.tab', function() {
			$.fn.dataTable.tables({visible:true, api:true}).columns.adjust();
		});
	});
</script>

