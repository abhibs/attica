<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Kolkata');

$type = $_SESSION['usertype'] ?? '';

if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type == 'Accounts') {
    include("header.php");
    include("menuacc.php");
} else if ($type == 'Accounts IMPS') {
    include("header.php");
    include("menuimpsAcc.php");
} else if ($type == 'Expense Team') {
    include("header.php");
    include("menuexpense.php");
} else if ($type == 'AccHead') {
    include("header.php");
    include("menuaccHeadPage.php");
} else {
    include("logout.php");
    exit();
}

include("dbConnection.php");

$date     = date('Y-m-d');
$timeNow  = date('H:i:s');
$branchIdFromSession = $_SESSION['branchCode'] ?? '';
// We will insert 'ExpenseTeam' instead of any session employee id:
$employeeIdToInsert = 'ExpenseTeam';

/* ---------------------------
   Helpers
---------------------------- */
function next_id(mysqli $con, string $table, string $col = 'id'): int {
    $res = mysqli_query($con, "SELECT COALESCE(MAX($col),0)+1 AS nid FROM $table");
    $row = $res ? mysqli_fetch_assoc($res) : ['nid' => 1];
    return (int)($row['nid'] ?? 1);
}
function safe_name($s) {
    return preg_replace('/[^A-Za-z0-9._-]/', '_', $s);
}

/* ---------------------------
   Load active branches for dropdown
---------------------------- */
$branches = [];
$bq = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status = 1 ORDER BY branchName ASC");
if ($bq) {
    while ($r = mysqli_fetch_assoc($bq)) {
        $branches[] = $r;
    }
}

/* ---------------------------
   Determine selected branch (for insert & listing)
---------------------------- */
$selectedBranchId = $_POST['branchId'] ?? $_GET['branchId'] ?? $branchIdFromSession;
$selectedBranchId = trim($selectedBranchId);

/* ---------------------------
   Handle Submit -> Insert into expense with status 'Acc-Approved'
---------------------------- */
$flash = ['type' => '', 'msg' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitExpenses'])) {
    // Basic validations
    $typeInput = trim($_POST['expense'] ?? '');
    $particular = trim($_POST['particular'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $branchForInsert = trim($_POST['branchId'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $remarks = mb_substr(strip_tags($remarks), 0, 255);

    if ($typeInput === '' || $particular === '' || $amount === '' || $branchForInsert === '') {
        $flash = ['type' => 'danger', 'msg' => 'Missing required fields (branch, type, particulars, amount).'];
    } else {
        // File handling
        $uploadDir = __DIR__ . '/ExpenseDocuments';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $allowedExt = ['jpg','jpeg','png','pdf','JPG','JPEG','PNG','PDF'];
        $maxBytes = 5 * 1024 * 1024; // 5 MB

        $fileRel = '';
        $file1Rel = '';

        // file (required)
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            if (!in_array($ext, $allowedExt, true)) {
                $flash = ['type' => 'danger', 'msg' => 'Invalid file type for File 1.'];
            } elseif ($_FILES['file']['size'] > $maxBytes) {
                $flash = ['type' => 'danger', 'msg' => 'File 1 size exceeds 5MB.'];
            } elseif (empty($flash['msg'])) {
                $newName = 'EXP_' . safe_name($branchForInsert . '_' . time() . '_' . $_FILES['file']['name']);
                $dest = $uploadDir . '/' . $newName;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                    $fileRel = 'ExpenseDocuments/' . $newName;
                } else {
                    $flash = ['type' => 'danger', 'msg' => 'Failed to upload File 1.'];
                }
            }
        }

        // file1 (optional)
        if (!empty($_FILES['file1']['name']) && empty($flash['msg'])) {
            $ext = pathinfo($_FILES['file1']['name'], PATHINFO_EXTENSION);
            if (!in_array($ext, $allowedExt, true)) {
                $flash = ['type' => 'danger', 'msg' => 'Invalid file type for File 2.'];
            } elseif ($_FILES['file1']['size'] > $maxBytes) {
                $flash = ['type' => 'danger', 'msg' => 'File 2 size exceeds 5MB.'];
            } else {
                $newName = 'EXP2_' . safe_name($branchForInsert . '_' . time() . '_' . $_FILES['file1']['name']);
                $dest = $uploadDir . '/' . $newName;
                if (move_uploaded_file($_FILES['file1']['tmp_name'], $dest)) {
                    $file1Rel = 'ExpenseDocuments/' . $newName;
                } else {
                    $flash = ['type' => 'danger', 'msg' => 'Failed to upload File 2.'];
                }
            }
        }

        // Insert into expense if no file errors
        if (empty($flash['msg'])) {
            $id = next_id($con, 'expense', 'id');

            $sql = "INSERT INTO expense
                    (id, branchCode, employeeId, particular, type, file, file1, amount, status, date, time, remarks)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Acc-Approved', ?, ?, ?)";

            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "issssssssss", // 1 int + 10 strings
                    $id,
                    $branchForInsert,
                    $employeeIdToInsert, // <- store as literal "ExpenseTeam"
                    $particular,
                    $typeInput,
                    $fileRel,
                    $file1Rel,
                    $amount,
                    $date,
                    $timeNow,
                    $remarks
                );
                if (mysqli_stmt_execute($stmt)) {
                    $flash = ['type' => 'success', 'msg' => 'Expense added with status Acc-Approved.'];
                    // keep the selected branch in view
                    $selectedBranchId = $branchForInsert;
                } else {
                    $flash = ['type' => 'danger', 'msg' => 'Insert failed: ' . mysqli_error($con)];
                }
                mysqli_stmt_close($stmt);
            } else {
                $flash = ['type' => 'danger', 'msg' => 'Prepare failed: ' . mysqli_error($con)];
            }
        }
    }
}
?>
<style>
	#wrapper{ background:#f5f5f5; }
	#wrapper h3{ text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
	.text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
	.fa_Icon{ color:#ffa500; }
	thead { text-transform:uppercase; background-color:#123C69; }
	thead tr{ color:#f2f2f2; font-size:12px; }
	.dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
	.btn-success{
		display:inline-block;padding:0.7em 1.4em;margin:0 0.3em 0.3em 0;border-radius:0.15em;box-sizing:border-box;text-decoration:none;
		font-size:12px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;background-color:#123C69;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);text-align:center;position:relative;
	}
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3><span style="color:#900" class="fa fa-money"></span> Branch Daily Expenses</h3>
				</div>
				<div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius:10px;">

					<?php if (!empty($flash['msg'])): ?>
						<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" role="alert" style="margin-bottom:15px;">
							<?php echo htmlspecialchars($flash['msg']); ?>
						</div>
					<?php endif; ?>

					<form method="POST" class="form-horizontal" action="" enctype="multipart/form-data">
                        <!-- Branch selector -->
                        <div class="col-sm-4">
							<label class="text-success">Branch</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-building"></span></span>
								<select class="form-control" name="branchId" id="branchId" required>
									<option value="" disabled <?php echo $selectedBranchId===''?'selected':''; ?>>SELECT BRANCH</option>
									<?php foreach ($branches as $b): ?>
										<option value="<?php echo htmlspecialchars($b['branchId']); ?>"
                                            <?php echo ($selectedBranchId === $b['branchId']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b['branchName']); ?>
                                        </option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="col-sm-4">
							<label class="text-success">Expense Type</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-rupee"></span></span>
								<select class="form-control" name="expense" id="expense" required>
									<option selected disabled value="">SELECT TYPE</option>
									<option value="Office Rent">Office Rent</option>
									<option value="Electricity Bill">Electricity Bill</option>
									<option value="Internet Bill">Internet Bill</option>
									<option value="Water Bill">Water Bill</option>
								</select>
							</div>
						</div>

						<div class="col-sm-4">
							<label class="text-success">Particulars</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-edit"></span></span>
								<input type="text" name="particular" id="particular" class="form-control" required placeholder="Particulars" autocomplete="off">
							</div>
						</div>

						<div class="col-sm-4">
							<label class="text-success">Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-money"></span></span>
								<input type="text" placeholder="Expenses" pattern="[0-9]{1,7}" maxlength="7" title="enter only Numbers" class="form-control" required name="amount" id="amount" autocomplete="off">
							</div>
						</div>

                        <div class="col-sm-8">
							<label class="text-success">Remarks (optional)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-comment"></span></span>
								<input type="text" name="remarks" id="remarks" class="form-control" placeholder="Enter remarks (optional)" maxlength="255" autocomplete="off">
							</div>
						</div>

						<label class="col-sm-12 control-label"><br></label>

						<div class="col-sm-4">
							<label class="text-success">Upload Bill</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-file"></span></span>
								<input type="file" style="background:#ffcf40" class="form-control" name="file" id="file">
							</div>
						</div>

						<br><br>

						<div class="col-sm-4">
							<label class="text-success">Upload Second Bill</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#900" class="fa fa-file"></span></span>
								<input type="file" style="background:#ffcf40" class="form-control" name="file1" id="file1">
							</div>
						</div>

						<div class="col-sm-4" style="text-align:center">
							<button class="btn btn-success" name="submitExpenses" id="submitExpenses" type="submit" style="margin-top:23px;">
								<span style="color:#ffcf40" class="fa fa-check"></span> Submit
							</button>
						</div>
					</form>
				</div>
			</div>

			<div class="hpanel">
				<div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">

                    <!-- Small filter to view today's expenses by branch -->
                    <form method="GET" class="form-inline" style="margin-bottom:10px;">
                        <div class="form-group">
                            <label class="text-success" style="margin-right:6px;">Branch</label>
                            <select class="form-control" name="branchId" onchange="this.form.submit()">
                                <option value="">SELECT BRANCH</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?php echo htmlspecialchars($b['branchId']); ?>"
                                        <?php echo ($selectedBranchId === $b['branchId']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($b['branchName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th><i class="fa fa-sort-numeric-asc"></i></th>
								<th>Particular</th>
								<th>Type</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Remarks</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
                            $branchForList = mysqli_real_escape_string($con, $selectedBranchId);
							$q = mysqli_query(
                                $con,
                                "SELECT particular,type,amount,status,remarks
                                 FROM expense
                                 WHERE date = '$date' AND branchCode = '$branchForList'"
                            );
							while($row = mysqli_fetch_assoc($q)){
								echo "<tr>";
								echo "<td>" . $i . "</td>";
								echo "<td>" . htmlspecialchars($row['particular']) . "</td>";
								echo "<td>" . htmlspecialchars($row['type']) . "</td>";
								echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
								echo "<td>" . htmlspecialchars($row['status']) . "</td>";
								echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
								echo "</tr>";
								$i++;
							}
							?>
						</tbody>
					</table>
				</div>
			</div>

		</div>
		<div style="clear:both"></div>
	</div>

	<script>
	// Allowed extensions pattern for your file checks
	var allowedFileExtensions = /(\.jpg|\.jpeg|\.png|\.pdf)$/i;

	$(document).ready(function(){
		$("#file").change(function(){
			var fileInput = document.getElementById('file');
			var filePath = fileInput.value;
			if (!allowedFileExtensions.exec(filePath)) {
				alert('Invalid file type');
				fileInput.value = '';
				return false;
			}
			// 5 MB client-side limit
			if (fileInput.files.length && (fileInput.files[0].size / 1024 / 1024) > 5){
				alert('File size exceeds 5MB');
				fileInput.value = '';
			}
		});
        $("#file1").change(function(){
			var fileInput = document.getElementById('file1');
			var filePath = fileInput.value;
			if (filePath && !allowedFileExtensions.exec(filePath)) {
				alert('Invalid file type');
				fileInput.value = '';
				return false;
			}
			// 5 MB client-side limit
			if (fileInput.files.length && (fileInput.files[0].size / 1024 / 1024) > 5){
				alert('File size exceeds 5MB');
				fileInput.value = '';
			}
		});
	});
	</script>

<?php include("footer.php"); ?>

