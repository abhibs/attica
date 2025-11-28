<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'];
if ($type == 'Doorstep') {
    include("header.php");
    include("menuDoorStep.php");
} else {
    include("logout.php");
    exit;
}
include("dbConnection.php");

/* -------------------- FLASH MESSAGE (AFTER REDIRECT) -------------------- */
$msg = '';
if (!empty($_SESSION['release_msg'])) {
    $msg = $_SESSION['release_msg'];
    unset($_SESSION['release_msg']); // show only once
}

/* -------------------- POST HANDLER -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitRequest'])) {
    $name          = trim($_POST['custName'] ?? '');
    $contact       = trim($_POST['contactNumber'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $metalType     = trim($_POST['metalType'] ?? '');
    $grams         = trim($_POST['grams'] ?? '');
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

    // Helper to build filename: originalbase-DOORSTEP-YYYYMMDDHHMMSS.ext
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
    if (
        $name         !== '' &&
        $contact      !== '' &&
        $location     !== '' &&
        $metalType    !== '' &&
        $grams        !== '' &&
        $businessType !== '' &&
        (!$isReleaseGold || $releasePlace !== '')
    ) {
        /*
           Make sure release_requests table has:
           release_doc, gold_image, customer_id_doc
        */
        $sql = "INSERT INTO release_requests
                (name, contact_number, location, metal_type, grams, branch, business_type, release_place, status,
                 release_doc, gold_image, customer_id_doc)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssss",
                $name,
                $contact,
                $location,
                $metalType,
                $grams,
                $branch,
                $businessType,
                $releasePlace,
                $status,
                $releaseDocPath,
                $goldImagePath,
                $customerIdPath
            );
            if (mysqli_stmt_execute($stmt)) {
                // Store success message in session and redirect (PRG pattern)
                $_SESSION['release_msg'] = 'Request submitted successfully';
            } else {
                // For debugging, you can keep the detailed error. Remove in production if you want.
                $_SESSION['release_msg'] = 'Insert failed: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['release_msg'] = 'DB error: ' . mysqli_error($con);
        }
    } else {
        $_SESSION['release_msg'] = 'All fields are required. (Release Place is required for Release Gold)';
    }

    // Redirect to THIS script to avoid resubmission on refresh
    $redirectUrl = basename(__FILE__);  // e.g. doorstepReleaseRequest.php
    header("Location: " . $redirectUrl);
    exit;
}
?>

<style>
    #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 16px; color: #123C69; }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color: #fffafa; }
    .text-success { color: #123C69; text-transform: uppercase; font-weight: 600; }
    .btn-primary { background-color: #123C69; }
    .btn-success{
        display:inline-block;padding:0.7em 1.4em;margin:0 0.3em 0.3em 0;border-radius:0.15em;box-sizing:border-box;
        text-decoration:none;font-size:12px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;
        background-color:#123C69;box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);text-align:center;position:relative;
    }
    .fa_Icon { color:#990000; }
    #wrapper .panel-body { box-shadow:10px 15px 15px #999; background-color:#f5f5f5; border-radius:3px; padding:15px; }
</style>

<div id="wrapper">
    <div class="row content">

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> RELEASE REQUEST FORM</b>
                        </h3>
                    </div>
                    <div class="card-body container-fluid" style="margin-top:24px;padding:0px;">
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

                                        <div class="col-sm-6" id="releasePlaceWrapper">
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

    </div><!-- /.row -->
    <?php include("footer.php"); ?>
</div>

<script>
  // Show/hide Release Place + Release Doc based on Business Type
  function toggleReleasePlace() {
    var val  = document.getElementById('businessType').value;
    var wrap = document.getElementById('releasePlaceWrapper');
    var input = document.getElementById('releasePlace');

    var releaseDocWrap = document.getElementById('releaseDocWrapper');

    if (val === 'Release Gold') {
        wrap.style.display = 'block';
        input.required = true;

        if (releaseDocWrap) {
            releaseDocWrap.style.display = 'block';  // show Release Doc input
        }
    } else {
        wrap.style.display = 'none';
        input.required = false;
        input.value = '';

        if (releaseDocWrap) {
            releaseDocWrap.style.display = 'none';   // hide Release Doc input
        }
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    var bt = document.getElementById('businessType');
    if (bt) {
      bt.addEventListener('change', toggleReleasePlace);
      toggleReleasePlace(); // initial state
    }
  });
</script>

