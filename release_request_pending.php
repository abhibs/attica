<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
}
elseif ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
}
elseif($type=='Software'){
    include("header.php");
    include("menuSoftware.php");
}
else {
    include("logout.php");
    exit;
}
include("dbConnection.php");

$username = $_SESSION['username'] ?? '';

/* ---------- Helper: filename builder for uploads ---------- */
function makeDoorstepFilename($originalName, $timestamp) {
    $base = pathinfo($originalName, PATHINFO_FILENAME);
    $ext  = pathinfo($originalName, PATHINFO_EXTENSION);

    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$base);
    $safeExt  = preg_replace('/[^a-zA-Z0-9]/', '', (string)$ext);

    if ($safeBase === '') {
        $safeBase = 'file';
    }

    $name = $safeBase . '-DOORSTEP-' . $timestamp;
    if ($safeExt !== '') {
        $name .= '.' . $safeExt;
    }
    return $name;
}

/* -------------------- BRANCH LIST (all active) -------------------- */
$branchList = [];
$branchQuery = mysqli_query(
    $con,
    "SELECT branchId, branchName 
     FROM branch 
     WHERE status = 1 
     ORDER BY branchName"
);
while ($b = mysqli_fetch_assoc($branchQuery)) {
    $branchList[] = $b;
}

/* -------------------- HANDLERS -------------------- */
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* --------- 1) ADD NEW RELEASE REQUEST (from top form) --------- */
    if (isset($_POST['submitRequest'])) {
        $name          = trim($_POST['custName'] ?? '');
        $contact       = trim($_POST['contactNumber'] ?? '');
        $location      = trim($_POST['location'] ?? '');
        $metalType     = trim($_POST['metalType'] ?? '');
        $grams         = trim($_POST['grams'] ?? '');
        $releaseAmount = trim($_POST['releaseAmount'] ?? '');   // release amount
        $businessType  = trim($_POST['businessType'] ?? '');
        $releasePlace  = trim($_POST['releasePlace'] ?? '');
        $status        = 'Pending'; // default
        $branch        = '';        // branch will be assigned in Pending page

        $isReleaseGold = ($businessType === 'Release Gold');

        /* ---------- FILE UPLOAD HANDLING ---------- */
        $uploadTime = date('YmdHis'); // used in filenames

        // Base folders (make sure these folders exist and are writable)
        $releaseDocDir   = "ReleaseDocuments/";
        $goldImageDir    = "OrnamentDocs/";
        $customerIdDir   = "CustomerDocuments/";

        if (!is_dir($releaseDocDir))   { @mkdir($releaseDocDir, 0777, true); }
        if (!is_dir($goldImageDir))    { @mkdir($goldImageDir, 0777, true); }
        if (!is_dir($customerIdDir))   { @mkdir($customerIdDir, 0777, true); }

        $releaseDocPath  = '';
        $goldImagePath   = '';
        $customerIdPath  = '';

        // 1) Release Doc (only if file uploaded; NOT required)
        if (!empty($_FILES['releaseDoc']['name']) && is_uploaded_file($_FILES['releaseDoc']['tmp_name'])) {
            $newName = makeDoorstepFilename($_FILES['releaseDoc']['name'], $uploadTime);
            $target  = $releaseDocDir . $newName;

            if (move_uploaded_file($_FILES['releaseDoc']['tmp_name'], $target)) {
                $releaseDocPath = $target;
            }
        }

        // 2) Gold Image
        if (!empty($_FILES['goldImage']['name']) && is_uploaded_file($_FILES['goldImage']['tmp_name'])) {
            $newName = makeDoorstepFilename($_FILES['goldImage']['name'], $uploadTime);
            $target  = $goldImageDir . $newName;

            if (move_uploaded_file($_FILES['goldImage']['tmp_name'], $target)) {
                $goldImagePath = $target;
            }
        }

        // 3) Customer Id
        if (!empty($_FILES['customerIdDoc']['name']) && is_uploaded_file($_FILES['customerIdDoc']['tmp_name'])) {
            $newName = makeDoorstepFilename($_FILES['customerIdDoc']['name'], $uploadTime);
            $target  = $customerIdDir . $newName;

            if (move_uploaded_file($_FILES['customerIdDoc']['tmp_name'], $target)) {
                $customerIdPath = $target;
            }
        }

        // Validation (branch not included here)
        // For Release Gold -> both Release Place & Release Amount required
        if (
            $name         !== '' &&
            $contact      !== '' &&
            $location     !== '' &&
            $metalType    !== '' &&
            $grams        !== '' &&
            $businessType !== '' &&
            (
                !$isReleaseGold ||
                ($releasePlace !== '' && $releaseAmount !== '')
            )
        ) {
            /*
               Make sure release_requests table has:
               release_amount, release_doc, gold_image, customer_id_doc
            */
            $sql = "INSERT INTO release_requests
                    (name, contact_number, location, metal_type, grams, release_amount, branch,
                     business_type, release_place, status,
                     release_doc, gold_image, customer_id_doc)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssssssssss",   // 13 params, all as strings
                    $name,
                    $contact,
                    $location,
                    $metalType,
                    $grams,
                    $releaseAmount,
                    $branch,
                    $businessType,
                    $releasePlace,
                    $status,
                    $releaseDocPath,
                    $goldImagePath,
                    $customerIdPath
                );
                if (mysqli_stmt_execute($stmt)) {
                    $msg = 'Request submitted successfully';
                } else {
                    $msg = 'Insert failed: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $msg = 'DB error: ' . mysqli_error($con);
            }
        } else {
            $msg = 'All fields are required. (For Release Gold: both Release Place and Release Amount are required)';
        }

    }
    /* --------- 2) APPROVE / REJECT HANDLER (existing logic) --------- */
    elseif (isset($_POST['approveRequest']) || isset($_POST['rejectRequest'])) {
        $id             = (int)($_POST['requestId'] ?? 0);
        $selectedBranch = trim($_POST['branch'] ?? '');

        // which button was clicked?
        $action    = isset($_POST['approveRequest']) ? 'approve' : 'reject';
        $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';

        if ($id <= 0) {
            $msg = 'Invalid request';
        } elseif ($action === 'approve' && $selectedBranch === '') {
            // only approve needs branch
            $msg = 'Please select a branch before approval';
        } else {
            if ($action === 'approve') {
                // APPROVE: also set branch
                $sql = "UPDATE release_requests
                        SET status = ?,
                            approved_at = NOW(),
                            approved_by = ?,
                            branch = ?
                        WHERE id = ?";
                if ($stmt = mysqli_prepare($con, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssi", $newStatus, $username, $selectedBranch, $id);
                } else {
                    $stmt = false;
                    $msg  = 'DB error';
                }
            } else {
                // REJECT: no need for branch, leave it as is
                $sql = "UPDATE release_requests
                        SET status = ?,
                            approved_at = NOW(),
                            approved_by = ?
                        WHERE id = ?";
                if ($stmt = mysqli_prepare($con, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssi", $newStatus, $username, $id);
                } else {
                    $stmt = false;
                    $msg  = 'DB error';
                }
            }

            if ($stmt) {
                if (mysqli_stmt_execute($stmt)) {
                    $msg = ($action === 'approve') ? 'Request approved' : 'Request rejected';
                } else {
                    $msg = 'Action failed';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<style>
    #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 16px; color: #123C69; }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color: #fffafa; }
    .text-success { color: #123C69; text-transform: uppercase; font-weight: 600; }
    .btn-primary { background-color: #123C69; }
    .btn-success{
        display:inline-block;padding:0.5em 1em;margin:0 0.2em 0.2em 0;border-radius:0.15em;box-sizing:border-box;
        text-decoration:none;font-size:11px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;
        background-color:#123C69;box-shadow:inset 0 -0.4em 0 -0.35em rgba(0,0,0,0.17);text-align:center;position:relative;
    }
    .btn-danger{
        display:inline-block;padding:0.5em 1em;margin:0 0.2em 0.2em 0;border-radius:0.15em;box-sizing:border-box;
        text-decoration:none;font-size:11px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;
        background-color:#b30000;box-shadow:inset 0 -0.4em 0 -0.35em rgba(0,0,0,0.25);text-align:center;position:relative;
    }
    .fa_Icon { color:#990000; }
    #wrapper .panel-body { box-shadow:10px 15px 15px #999; background-color:#f5f5f5; border-radius:3px; padding:15px; }
    .theadRow { text-transform: uppercase; background-color: #123C69 !important; color: #f2f2f2; font-size: 11px; }
    .thumb-img { max-width: 60px; max-height: 60px; border-radius: 3px; display:block; margin:0 auto 3px; }
</style>

<!-- Select2 for searchable branch dropdown -->
<link
  href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
  rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div id="wrapper">
    <div class="row content">

        <!-- ADD NEW RELEASE REQUEST FORM -->
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> ADD RELEASE REQUEST</b>
                        </h3>
                    </div>
                    <div class="card-body container-fluid" style="margin-top:10px;padding:0px;">
                        <?php if ($msg !== ''): ?>
                            <div class="col-lg-12">
                                <div class="alert alert-info" style="margin-top:10px;">
                                    <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-lg-12">
                            <div class="panel-body">
                                <!-- IMPORTANT: enctype for file uploads -->
                                <form method="POST" class="form-horizontal" action="" enctype="multipart/form-data">
                                    <div class="row content">

                                        <div class="col-sm-4">
                                            <label class="text-success">Name</label>
                                            <input type="text" name="custName" class="form-control" autocomplete="off" required>
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Contact Number</label>
                                            <input type="text" name="contactNumber" class="form-control" autocomplete="off" required>
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Location</label>
                                            <input type="text" name="location" class="form-control" autocomplete="off" required>
                                        </div>

                                        <label class="col-sm-12 control-label"><br></label>

                                        <div class="col-sm-4">
                                            <label class="text-success">Metal Type</label>
                                            <select name="metalType" class="form-control" required>
                                                <option value="">-- Select Metal Type --</option>
                                                <option value="Gold">Gold</option>
                                                <option value="Silver">Silver</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Grams</label>
                                            <input type="number" step="0.01" name="grams" class="form-control" autocomplete="off" required>
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Business Type</label>
                                            <select name="businessType" id="businessType" class="form-control" required>
                                                <option value="">-- Select Business Type --</option>
                                                <option value="Physical Gold">Physical Gold</option>
                                                <option value="Release Gold">Release Gold</option>
                                            </select>
                                        </div>

                                        <label class="col-sm-12 control-label"><br></label>
                                         <!-- Release Amount wrapper (hidden by default, only for Release Gold) -->
                                        <div class="col-sm-4" id="releaseAmountWrapper" style="display:none;">
                                            <label class="text-success">Release Amount</label>
                                            <input type="number" step="0.01" name="releaseAmount" class="form-control" autocomplete="off">
                                        </div>

                                        <div class="col-sm-4" id="releasePlaceWrapper" style="display:none;">
                                            <label class="text-success">Release Place</label>
                                            <input type="text" name="releasePlace" id="releasePlace" class="form-control" autocomplete="off" placeholder="Bank / Financier / etc">
                                        </div>

                                        <label class="col-sm-12 control-label"><br></label>

                                        <!-- FILE INPUTS (ALL OPTIONAL) -->
                                        <div class="col-sm-4" id="releaseDocWrapper">
                                            <label class="text-success">Release Doc (optional)</label>
                                            <input type="file" name="releaseDoc" class="form-control"
                                                   accept=".pdf,image/*">
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Gold Image (optional)</label>
                                            <input type="file" name="goldImage" class="form-control"
                                                   accept="image/*">
                                        </div>

                                        <div class="col-sm-4">
                                            <label class="text-success">Customer Id (optional)</label>
                                            <input type="file" name="customerIdDoc" class="form-control"
                                                   accept=".pdf,image/*">
                                        </div>

                                        <!-- Status is always Pending, no need to show -->
                                        <input type="hidden" name="status" value="Pending">

                                        <div class="col-sm-12" align="right" style="padding-top:22px;">
                                            <button class="btn btn-success" name="submitRequest" type="submit">
                                                <span style="color:#ffcf40" class="fa fa-save"></span> SUBMIT REQUEST
                                            </button>
                                        </div>

                                    </div><!-- /.row -->
                                </form>
                            </div><!-- /.panel-body -->
                        </div><!-- /.col-lg-12 -->

                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            </div><!-- /.hpanel -->
        </div><!-- /.col-lg-12 -->

        <!-- PENDING TABLE -->
        <div class="col-lg-12" style="margin-top:10px;">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> PENDING RELEASE REQUESTS</b>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="example1" class="table table-hover table-bordered">
                                <thead>
                                    <tr class="theadRow">
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Metal</th>
                                        <th>Grams</th>
                                        <th>Release Amount</th>
                                        <th>Assign Branch</th>
                                        <th>Business Type</th>
                                        <th>Release Place</th>
                                        <th>Release Doc</th>
                                        <th>Gold Image</th>
                                        <th>Customer Id</th>
                                        <th>Status</th>
                                        <th style="text-align:center;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
$i = 1;
$sql = mysqli_query(
    $con,
    "SELECT id, name, contact_number, location, metal_type, grams, release_amount, branch, business_type,
            release_place, release_doc, gold_image, customer_id_doc, status
     FROM release_requests
     WHERE status = 'Pending'
     ORDER BY created_at DESC"
);

// helper to detect image by extension
$isImage = function($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
};

while ($row = mysqli_fetch_assoc($sql)) {
    $id            = (int)$row['id'];
    $name          = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $contact       = htmlspecialchars($row['contact_number'] ?? '', ENT_QUOTES, 'UTF-8');
    $location      = htmlspecialchars($row['location'] ?? '', ENT_QUOTES, 'UTF-8');
    $metalType     = htmlspecialchars($row['metal_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $grams         = htmlspecialchars($row['grams'] ?? '', ENT_QUOTES, 'UTF-8');
    $releaseAmount = htmlspecialchars($row['release_amount'] ?? '', ENT_QUOTES, 'UTF-8');
    $currentBranch = $row['branch'] ?? '';
    $businessType  = htmlspecialchars($row['business_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $releasePlace  = htmlspecialchars($row['release_place'] ?? '', ENT_QUOTES, 'UTF-8');
    $releaseDoc    = $row['release_doc'] ?? '';
    $goldImage     = $row['gold_image'] ?? '';
    $customerIdDoc = $row['customer_id_doc'] ?? '';
    $status        = htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8');

    echo "<tr>";
    echo "<td>{$i}</td>";
    echo "<td>{$name}</td>";
    echo "<td>{$contact}</td>";
    echo "<td>{$location}</td>";
    echo "<td>{$metalType}</td>";
    echo "<td>{$grams}</td>";
    echo "<td>{$releaseAmount}</td>";

    // Branch dropdown + form
    echo "<td>";
    echo "<form method='POST' action='' style='display:inline-block; margin:0;'>";
    echo "  <input type='hidden' name='requestId' value='{$id}'>";

    echo "  <select name='branch' class='form-control branch-select'>";
    echo "      <option value=''>-- Select Branch --</option>";
    foreach ($branchList as $b) {
        $bid  = htmlspecialchars($b['branchId'], ENT_QUOTES, 'UTF-8');
        $bnam = htmlspecialchars($b['branchName'], ENT_QUOTES, 'UTF-8');
        $selected = ($currentBranch == $bid) ? "selected='selected'" : "";
        echo "  <option value='{$bid}' {$selected}>{$bnam} - {$bid}</option>";
    }
    echo "  </select>";
    echo "</td>";

    echo "<td>{$businessType}</td>";
    echo "<td>{$releasePlace}</td>";

    // Release Doc column
    echo "<td style='text-align:center;'>";
    if (!empty($releaseDoc)) {
        $safePath = htmlspecialchars($releaseDoc, ENT_QUOTES, 'UTF-8');
        if ($isImage($releaseDoc)) {
            echo "<a href='{$safePath}' target='_blank' title='View Release Doc'>";
            echo "<img src='{$safePath}' alt='Release Doc' class='thumb-img'>";
            echo "</a>";
        } else {
            echo "<a href='{$safePath}' target='_blank' class='btn btn-primary btn-xs'>View</a>";
        }
    } else {
        echo "-";
    }
    echo "</td>";

    // Gold Image column
    echo "<td style='text-align:center;'>";
    if (!empty($goldImage)) {
        $safePath = htmlspecialchars($goldImage, ENT_QUOTES, 'UTF-8');
        echo "<a href='{$safePath}' target='_blank' title='View Gold Image'>";
        echo "<img src='{$safePath}' alt='Gold Image' class='thumb-img'>";
        echo "</a>";
    } else {
        echo "-";
    }
    echo "</td>";

    // Customer Id column
    echo "<td style='text-align:center;'>";
    if (!empty($customerIdDoc)) {
        $safePath = htmlspecialchars($customerIdDoc, ENT_QUOTES, 'UTF-8');
        if ($isImage($customerIdDoc)) {
            echo "<a href='{$safePath}' target='_blank' title='View Customer Id'>";
            echo "<img src='{$safePath}' alt='Customer Id' class='thumb-img'>";
            echo "</a>";
        } else {
            echo "<a href='{$safePath}' target='_blank' class='btn btn-primary btn-xs'>View</a>";
        }
    } else {
        echo "-";
    }
    echo "</td>";

    echo "<td>{$status}</td>";

    // ACTION buttons
    echo "<td style='text-align:center'>";
    echo "      <button class='btn btn-success' name='approveRequest' type='submit' ";
    echo "              onclick=\"return confirm('Approve this request?');\">";
    echo "          <i class='fa fa-check'></i> APPROVE";
    echo "      </button>";
    echo "      <button class='btn btn-danger' name='rejectRequest' type='submit' ";
    echo "              onclick=\"return confirm('Reject this request?');\">";
    echo "          <i class='fa fa-times'></i> REJECT";
    echo "      </button>";
    echo "  </form>";
    echo "</td>";

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
        </div><!-- /.col-lg-12 -->

    </div><!-- /.row -->
    <?php include("footer.php"); ?>
</div>

<script>
  // Show/hide Release Place + Release Amount based on Business Type
  function toggleReleasePlace() {
    var bt   = document.getElementById('businessType');
    if (!bt) return;

    var val            = bt.value;
    var placeWrap      = document.getElementById('releasePlaceWrapper');
    var placeInput     = document.getElementById('releasePlace');
    var amountWrap     = document.getElementById('releaseAmountWrapper');
    var amountInput    = document.getElementsByName('releaseAmount')[0];
    var releaseDocWrap = document.getElementById('releaseDocWrapper');

    if (val === 'Release Gold') {
        if (placeWrap)  placeWrap.style.display  = 'block';
        if (placeInput) placeInput.required      = true;

        if (amountWrap) amountWrap.style.display = 'block';
        if (amountInput) amountInput.required    = true;

        if (releaseDocWrap) releaseDocWrap.style.display = 'block';
    } else {
        if (placeWrap)  placeWrap.style.display  = 'none';
        if (placeInput) {
            placeInput.required = false;
            placeInput.value    = '';
        }

        if (amountWrap) amountWrap.style.display = 'none';
        if (amountInput) {
            amountInput.required = false;
            amountInput.value    = '';
        }

        if (releaseDocWrap) releaseDocWrap.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    var bt = document.getElementById('businessType');
    if (bt) {
      bt.addEventListener('change', toggleReleasePlace);
      toggleReleasePlace(); // initial state
    }

    $('.branch-select').select2({
      placeholder: 'Select Branch',
      width: '100%'
    });
  });
</script>

