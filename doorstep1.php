<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Calcutta');

$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} elseif ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} elseif ($type == 'Software') {
    include("header.php");
    include("menuSoftware.php");
} else if($type == 'BD') {
    include("header.php");
    include("menubd.php");
}
else {
    include("logout.php");
    exit;
}

include("dbConnection.php");

/* ----------------- APPROVE / REJECT HANDLER ----------------- */
$msg = '';

// put this after session_start() and dbConnection include
function showAlertAndReload($text)
{
    echo "<script>
        alert(" . json_encode($text) . ");
        window.location.href = '" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // These posts come from the Action buttons in the table
    if (isset($_POST['approveRequest']) || isset($_POST['rejectRequest'])) {
        $id     = isset($_POST['requestId']) ? (int)$_POST['requestId'] : 0;
        $branch = trim($_POST['branch'] ?? '');

        if ($id > 0) {

            // APPROVE
            if (isset($_POST['approveRequest'])) {
                if ($branch === '') {
                    showAlertAndReload('Please select a branch before approving.');
                } else {
                    $stmt = mysqli_prepare(
                        $con,
                        "UPDATE release_requests
                         SET status = 'Approved', branch = ?
                         WHERE id = ?"
                    );

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "si", $branch, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);

                        // >>> NEW: PUSH TO everycustomer on APPROVE (aligned to table structure) <<<
                        $reqRes = mysqli_query(
                            $con,
                            "SELECT * FROM release_requests WHERE id = " . (int)$id . " LIMIT 1");

                        if ($reqRes && mysqli_num_rows($reqRes) === 1) {
                            $req = mysqli_fetch_assoc($reqRes);

                            // Basic fields from release_requests
                            $ecCustomer  = $req['name'] ?? '';
                            $ecContact   = $req['contact_number'] ?? '';
                            $ecType      = $req['business_type'] ?? '';   // store business_type here
                            $ecBranch    = $branch;                       // assigned branch

                            // Quotation: store exactly what is in release_requests.quotation (JSON or plain string)
                            $ecQuotation = '';
                            if (!empty($req['quotation'])) {
                                $ecQuotation = $req['quotation'];   // e.g. {"image":"...png","status":1,"rate":"10600"}
                            }

                            $ecDate   = date('Y-m-d');
                            $ecTime   = date('H:i:s');
                           // $agent    = '';

                            // Escape values for safety
                            $ecCustomerEsc   = mysqli_real_escape_string($con, $ecCustomer);
                            $ecContactEsc    = mysqli_real_escape_string($con, $ecContact);
                            $ecTypeEsc       = mysqli_real_escape_string($con, $ecType);
                            $ecBranchEsc     = mysqli_real_escape_string($con, $ecBranch);
                            $ecQuotationEsc  = mysqli_real_escape_string($con, $ecQuotation);
                            $ecDateEsc       = mysqli_real_escape_string($con, $ecDate);
                            $ecTimeEsc       = mysqli_real_escape_string($con, $ecTime);
                            //$agentEsc        = mysqli_real_escape_string($con, $agent);

                            // EXACT pattern you asked:
                            // INSERT INTO everycustomer(
                            //   customer, contact, type, idnumber, branch, image, quotation,
                            //   date, time, status, status_remark, remark, block_counter,
                            //   extra, reg_type, agent
                            // ) VALUES
                            //   ('$ecCustomer', '$ecContact', '$ecType', '', '$ecBranch', '', '$ecQuotation',
                            //    '$ecDate', '$ecTime', 'Begin', '', '', '0', '', 'Doorstep', '$agent');

                            $everyCustomerQuery = "INSERT INTO everycustomer(customer, contact, type, idnumber, branch, image, quotation,date, time, status, status_remark, issue_remark, remark, remark1,ornament_docs,block_counter, extra, vmtime, reg_type, agent, agent_time, BMId, walkinType) VALUES ('$ecCustomerEsc','$ecContactEsc','$ecTypeEsc','','$ecBranchEsc','','$ecQuotationEsc','$ecDateEsc','$ecTimeEsc','Begin','','','','','','0','','','Doorstep','','','','')";
                            if (mysqli_query($con, $everyCustomerQuery)) {
                                showAlertAndReload('Request approved and pushed to everycustomer.');
                            } else {
                                showAlertAndReload('Request approved, but failed to insert into everycustomer.');
                            }
                        } else {
                            showAlertAndReload('Request approved, but data not found to push into everycustomer.');
                        }
                    } else {
                        showAlertAndReload('Database error while approving.');
                    }
                }
            }

            // REJECT
            if (isset($_POST['rejectRequest'])) {
                $stmt = mysqli_prepare(
                    $con,
                    "UPDATE release_requests
                     SET status = 'Rejected'
                     WHERE id = ?"
                );
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    showAlertAndReload('Request rejected.');
                } else {
                    showAlertAndReload('Database error while rejecting.');
                }
            }
        } else {
            showAlertAndReload('Invalid request id.');
        }
    }
}




/* ------------------------------------------------------------ */

$date = date('Y-m-d');

/* ---------- FORCE HO / AGPL000 ---------- */
$branchCode = 'AGPL000';   // branchId
$branchData = [
    'branchId'   => $branchCode,
    'branchName' => 'HO'
];

/* GOLD RATE */
$goldRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash,transferRate
    FROM gold
    WHERE type='Gold' AND date='$date' AND city='$branchCode'
    ORDER BY id DESC
    LIMIT 1"));

/* SILVER RATE */
$silverRate = mysqli_fetch_assoc(mysqli_query($con, "SELECT cash
    FROM gold
    WHERE type='Silver' AND date='$date' AND city='$branchCode'
    ORDER BY id DESC
    LIMIT 1"));

/* ---- BRANCH OPTIONS FOR ASSIGN BRANCH DROPDOWN ---- */
$branchOptions = [];
$branchRes = mysqli_query(
    $con,
    "SELECT branchId, branchName 
     FROM branch 
     WHERE status = 1
     ORDER BY branchName ASC"
);
if ($branchRes) {
    while ($b = mysqli_fetch_assoc($branchRes)) {
        $branchOptions[$b['branchId']] = $b['branchName'];
    }
}

$miscData = mysqli_fetch_assoc(mysqli_query($con, "SELECT
    (SELECT COUNT(DISTINCT(contact)) AS totalWalkin FROM everycustomer WHERE date='$date' AND branch='$branchCode' AND status NOT IN ('Double Entry','Wrong Entry')) AS totalWalkin,
    (SELECT COUNT(id) AS totalSold FROM trans WHERE date='$date' AND branchId='$branchCode' AND status='Approved') AS totalSold,
    (SELECT date FROM closing WHERE branchId='$branchCode' ORDER BY date DESC LIMIT 1) AS ClosingDate,
    (SELECT day FROM misc WHERE purpose='Add Customer') AS addDate
    "));
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

    .action-btn {
        width: 40px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    :root {
        --accent: #990000;
        --accent-2: #6b0000;
        --muted: #6b7280;
    }

    .tp-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        z-index: 9999;
        padding: 16px
    }

    .tp-backdrop.show {
        display: grid;
        place-items: center
    }

    .tp-modal {
        width: min(1060px, 96vw);
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .35);
        position: relative;
        animation: tpPop .18s ease-out
    }

    @keyframes tpPop {
        from {
            transform: scale(.985);
            opacity: .85
        }

        to {
            transform: scale(1);
            opacity: 1
        }
    }

    .tp-close {
        position: absolute;
        right: 10px;
        top: 10px;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        border: none;
        background: #fff;
        color: var(--accent);
        font-size: 20px;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
        line-height: 0;
        z-index: 3;
    }

    .tp-close:hover {
        transform: scale(1.05)
    }

    .tp-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        color: #fff;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        padding-right: 64px;
    }

    .tp-header--strip {
        border-top: 1px solid rgba(255, 255, 255, .15)
    }

    .tp-title {
        font-weight: 800;
        letter-spacing: .3px;
        text-transform: uppercase
    }

    .tp-date-pill {
        margin-left: auto;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .28);
        backdrop-filter: saturate(120%) blur(2px);
    }

    .tp-window {
        margin-left: auto;
        font-size: 12px;
        opacity: .95
    }

    .tp-content {
        padding: 18px;
        background: #fff
    }

    .tp-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(4, 1fr)
    }

    @media (max-width:1100px) {
        .tp-grid {
            grid-template-columns: repeat(3, 1fr)
        }
    }

    @media (max-width:700px) {
        .tp-grid {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    .tp-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
        display: flex;
        flex-direction: column;
    }

    .tp-photo {
        width: 100%;
        height: 280px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .tp-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #e5e7eb;
    }

    .tp-meta {
        padding: 12px 12px 14px;
        text-align: center
    }

    .tp-rank {
        font-size: 22px;
        font-weight: 900;
        color: #1f2937;
        line-height: 1
    }

    .tp-name {
        margin-top: 6px;
        font-weight: 700;
        color: #111827
    }

    .tp-sub {
        margin-top: 4px;
        font-size: 12px;
        color: var(--muted);
        font-weight: 600
    }

    .tp-losers {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px
    }

    .tp-loser-line {
        font-size: 20px;
        font-weight: 800;
        color: #111;
        text-align: center
    }

    .tp-empty {
        color: var(--muted);
        text-align: center;
        padding: 12px
    }

    .add-customer-cta {
        position: relative;
        z-index: 10001;
    }

    .modal-open #tpBackdrop {
        z-index: 1040 !important;
        pointer-events: none;
    }
</style>

<?php if ($msg !== ''): ?>
    <div class="alert alert-info" style="margin:10px;">
        <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<!--   EVERY CUSTOMER MODAL   -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="addCustomerTitle" style="z-index:1060;">
    <div class="modal-dialog modal-sm" role="document" style="width:600px;">
        <div class="modal-content">
            <div class="color-line"></div>

            <span class="fa fa-close modaldesign" data-dismiss="modal" aria-label="Close"></span>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position:absolute; right:10px; top:10px; z-index:2;">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="modal-header" style="background-color:#123C69;color:#f0f8ff;">
                <h3 id="addCustomerTitle">ADD CUSTOMER</h3>
            </div>

            <div class="modal-body" style="padding-right:40px;">
                <?php
                $empId = $_SESSION['employeeId'] ?? '';
                ?>
                <form method="POST" class="form-horizontal" action="xsubmit.php" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="branchid" class="form-control" value="<?php echo htmlspecialchars($branchCode, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <label class="col-sm-2 control-label text-success">Name</label>
                        <div class="col-sm-10">
                            <input type="text" name="cusname" placeholder="Customer Name" required class="form-control" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Mobile</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="cusmob" placeholder="Contact Number" required maxlength="10" pattern="[0-9]{10}" inputmode="numeric" oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label text-success">Location</label>
                        <div class="col-sm-10">
                            <input type="text" name="location" placeholder="Location" required class="form-control" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Metal Type</label>
                        <div class="col-sm-10">
                            <select name="metalType" class="form-control" aria-label="Default select example" required>
                                <option selected disabled value="">Type</option>
                                <option value="Gold">Gold</option>
                                <option value="Silver">Silver</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Grams</label>
                        <div class="col-sm-10">
                            <input type="number" step="0.01" name="grams" class="form-control" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Business Type</label>
                        <div class="col-sm-10">
                            <select name="customerType" class="form-control" aria-label="Default select example" required>
                                <option selected disabled value="">Type</option>
                                <option value="physical">Physical</option>
                                <option value="release">Release</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">KMs</label>
                        <div class="col-sm-10">
                            <input type="number" step="0.01" name="kms" class="form-control" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label text-success">Reason1</label>
                        <div class="col-sm-10">
                            <input type="text" name="reason1" placeholder="Reason1" required class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label text-success">Reason2</label>
                        <div class="col-sm-10">
                            <input type="text" name="reason2" placeholder="Reason2" required class="form-control" autocomplete="off">
                        </div>
                    </div>

                    <!-- Release Amount wrapper (hidden by default, only for Release Gold) -->
                    <div class="form-group">
                        <div id="releaseAmountWrapper" style="display:none;">
                            <label class="col-sm-2 control-label text-success">Release Amount</label>
                            <div class="col-sm-10">
                                <input type="number" step="0.01" name="releaseAmount" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div id="releasePlaceWrapper" style="display:none;">
                            <label class="col-sm-2 control-label text-success">Release Place</label>
                            <div class="col-sm-10">
                                <input type="text" name="releasePlace" id="releasePlace" class="form-control" autocomplete="off" placeholder="Bank / Financier / etc">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Gold Image (optional)</label>
                        <div class="col-sm-10">
                            <input type="file" style="background:#ffcf40" class="form-control" name="goldImage" id="file" accept=".jpg,.jpeg,.png,.pdf,image/*">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-2 control-label text-success">Release Doc (optional)</label>
                        <div class="col-sm-10">
                            <input type="file" style="background:#ffcf40" class="form-control" name="releaseDoc" id="file" accept=".jpg,.jpeg,.png,.pdf,image/*">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:30px">
                        <div class="col-sm-4 col-sm-offset-8">
                            <button class="btn btn-success btn-block" name="submitDoorstep" type="submit">
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
    (function() {
        if (!window.jQuery) return;
        jQuery(function($) {
            $('#addCustomerModal').on('show.bs.modal', function() {
                var bd = document.getElementById('tpBackdrop');
                if (bd) {
                    bd.classList.remove('show');
                    bd.setAttribute('aria-hidden', 'true');
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
                <input type="hidden" name="source" value="doorstep">
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

                        <!-- RELEASE QUOTATION -->
                        <div class="panel panel-default">
                            <div id="collapse2" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="col-xs-12">
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
        <div class="col-lg-2">
            <div class="hpanel">
                <?php if ($miscData['addDate'] == 'Yes') { ?>
                    <div class="panel-body" style="height:50px; padding: 5px; background-color: #BEE890;">
                        <ul class="mailbox-list">
                            <li>
                                <a title="Click To Add New Customer" data-toggle="modal" data-target="#addCustomerModal" class="add-customer-cta">
                                    <span class="pull-right"><i style="color:#990000; font-size: 18px; font-weight: 600;"></i> </span>
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
            </div>
        </div>

        <?php if ($miscData['ClosingDate'] == $date) { ?>
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body">
                        <h3 class='text-center' style='margin-bottom: 10px; color:#990000'>BRANCH IS CLOSED, TO REOPEN & BILL - CALL APPROVAL TEAM : 8925537846</h3>
                    </div>
                </div>
            </div>
        <?php } ?>

        <!-- PENDING TABLE -->
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive project-list">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Mobile</th>
                                    <th>Location</th>
                                    <th>Metal</th>
                                    <th>Grams</th>
                                    <th>KMs</th>
                                    <th>Reason1</th>
                                    <th>Reason2</th>
                                    <th>Release Amount</th>
                                    <th>Assign Branch</th>
                                    <th>Business Type</th>
                                    <th>Release Place</th>
                                    <th>Document</th>
                                    <th>Gold Image</th>
                                    <!-- <th>Customer Id</th> -->
                                    <th>Quotation</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $i = 1;
                                $sql = mysqli_query(
                                    $con,
                                    "SELECT id, name, contact_number, location, metal_type, grams, kms,
            reason1, reason2, release_amount, branch, business_type,
            release_place, release_doc, gold_image, customer_id_doc, status, quotation
     FROM release_requests
     WHERE status = 'Pending'
     ORDER BY id ASC"
                                );

                                while ($row = mysqli_fetch_assoc($sql)) {

                                    $id             = $row['id'];
                                    $name           = $row['name'];
                                    $contact        = $row['contact_number'];
                                    $location       = $row['location'];
                                    $metal          = $row['metal_type'];
                                    $grams          = $row['grams'];
                                    $kms            = $row['kms'];
                                    $reason1        = $row['reason1'];
                                    $reason2        = $row['reason2'];   // NEW
                                    $releaseAmount  = $row['release_amount'];
                                    $branchAssign   = $row['branch'];
                                    $businessType   = $row['business_type'];
                                    $releasePlace   = $row['release_place'];
                                    $releaseDoc     = $row['release_doc'];
                                    $goldImage      = $row['gold_image'];
                                    $customerIdDoc  = $row['customer_id_doc'];
                                    $status         = $row['status'];
                                    $quotationRaw   = $row['quotation'];

                                    // --- Quotation: handle JSON or plain filename ---
                                    $quotationFile = '';
                                    if (!empty($quotationRaw)) {
                                        $decoded = json_decode($quotationRaw, true);
                                        if (is_array($decoded) && !empty($decoded['image'])) {
                                            $quotationFile = $decoded['image'];
                                        } else {
                                            $quotationFile = $quotationRaw;
                                        }
                                    }

                                    $statusClass = 'label-default';
                                    if ($status === 'Pending')  $statusClass = 'label-warning';
                                    if ($status === 'Approved') $statusClass = 'label-success';
                                    if ($status === 'Rejected') $statusClass = 'label-danger';

                                    echo "<tr>";
                                    echo "<td>" . $i++ . "</td>";
                                    echo "<td>" . htmlspecialchars($name) . "</td>";
                                    echo "<td>" . htmlspecialchars($contact) . "</td>";
                                    echo "<td>" . htmlspecialchars($location) . "</td>";
                                    echo "<td>" . htmlspecialchars($metal) . "</td>";
                                    echo "<td>" . htmlspecialchars($grams) . "</td>";
                                    echo "<td>" . htmlspecialchars($kms) . "</td>";
                                    echo "<td>" . htmlspecialchars($reason1) . "</td>";
                                    echo "<td>" . htmlspecialchars($reason2) . "</td>";
                                    echo "<td>" . htmlspecialchars($releaseAmount) . "</td>";
                                    // ASSIGN BRANCH DROPDOWN
                                    echo "<td>";
                                    $selectedBranch = !empty($branchAssign) ? $branchAssign : 'AGPL000';

                                    echo "<select class='form-control input-sm assign-branch selectpicker'
                                        data-live-search='true'
                                        data-id='" . (int)$id . "'
                                        name='assign_branch_" . (int)$id . "'>";

                                    foreach ($branchOptions as $bId => $bName) {
                                        $sel   = ($selectedBranch === $bId) ? "selected" : "";
                                        $label = htmlspecialchars($bName . ' - ' . $bId, ENT_QUOTES, 'UTF-8');
                                        echo "<option value='" . htmlspecialchars($bId, ENT_QUOTES, 'UTF-8') . "' $sel>$label</option>";
                                    }

                                    echo "</select>";
                                    echo "</td>";


                                    // // ASSIGN BRANCH DROPDOWN
                                    // echo "<td>";
                                    // // if no branch stored yet, default to HO-AGPL000
                                    // $selectedBranch = !empty($branchAssign) ? $branchAssign : 'AGPL000';

                                    // echo "<select class='form-control input-sm assign-branch' data-id='" . (int)$id . "' name='assign_branch_" . (int)$id . "'>";
                                    // foreach ($branchOptions as $bId => $bName) {
                                    //     $sel = ($selectedBranch === $bId) ? "selected" : "";
                                    //     // label style: "HO - AGPL000"
                                    //     $label = htmlspecialchars($bName . ' - ' . $bId, ENT_QUOTES, 'UTF-8');
                                    //     echo "<option value='" . htmlspecialchars($bId, ENT_QUOTES, 'UTF-8') . "' $sel>$label</option>";
                                    // }
                                    // echo "</select>";
                                    echo "</td>";

                                    echo "<td>" . htmlspecialchars($businessType) . "</td>";
                                    echo "<td>" . htmlspecialchars($releasePlace) . "</td>";

                                    // Release Doc link
                                    if (!empty($releaseDoc)) {
                                        echo "<td class='text-center'>
                <a href='release_docs/" . htmlspecialchars($releaseDoc, ENT_QUOTES, 'UTF-8') . "' target='_blank' class='btn btn-xs btn-info'>
                    <i class='fa fa-file'></i> View
                </a>
              </td>";
                                    } else {
                                        echo "<td class='text-center'>-</td>";
                                    }

                                    // Gold Image link
                                    if (!empty($goldImage)) {
                                        echo "<td class='text-center'>
                <a href='gold_images/" . htmlspecialchars($goldImage, ENT_QUOTES, 'UTF-8') . "' target='_blank' class='btn btn-xs btn-info'>
                    <i class='fa fa-image'></i>
                </a>
              </td>";
                                    } else {
                                        echo "<td class='text-center'>-</td>";
                                    }

                                    // Quotation column – button + optional image
                                    echo "<td class='text-center'>";
                                    echo "<a class='btn btn-success btn-user'
                                            data-toggle='modal'
                                            data-target='#quotationModal'
                                            data-rowid='" . (int)$id . "'
                                            data-rowname='" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "'>
                                            <i class='fa fa-quora' style='font-size:15px;color:#ffa500'></i>
                                        </a>";

                                    if (!empty($quotationFile)) {
                                        echo " <a target='_BLANK' href='QuotationImage/" . htmlspecialchars($quotationFile, ENT_QUOTES, 'UTF-8') . "'>
                                                    <button class='btn btn-circle' type='button'>
                                                        <i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69'></i>
                                                    </button>
                                                </a>";
                                    }

                                    echo "</td>";

                                    echo "<td class='text-center'>
                                            <span class='label " . $statusClass . "'>" . htmlspecialchars($status) . "</span>
                                        </td>";

                                    // ACTION: forms posting back to this same page
                                    echo "<td style='text-align:center; white-space:nowrap;'>";

                                    // APPROVE form
                                    echo "<form method='post' style='display:inline-block;'>
                                            <input type='hidden' name='requestId' value='" . (int)$id . "'>
                                            <input type='hidden' name='branch' value='" . htmlspecialchars($selectedBranch, ENT_QUOTES, 'UTF-8') . "'>
                                            <button type='submit' name='approveRequest' class='btn btn-xs btn-success action-btn'
                                                onclick='return handleApprove(this," . (int)$id . ");'>
                                                <i class=\"fa fa-check\"></i>
                                            </button>
                                        </form>";

                                    // REJECT form
                                    echo "<form method='post' style='display:inline-block; margin-left:4px;'>
                                            <input type='hidden' name='requestId' value='" . (int)$id . "'>
                                            <button type='submit' name='rejectRequest' class='btn btn-xs btn-danger action-btn'
                                                onclick=\"return confirm('Reject this request?');\">
                                                <i class=\"fa fa-times\"></i>
                                            </button>
                                        </form>";
                                    echo "</td>";
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


    <?php include("footer.php"); ?>
    <script type="text/javascript" src="scripts/html2canvas.min.js"></script>
    <script src="scripts/quotation-v3.js"></script>
    <script src="vendor/sweetalert/lib/sweet-alert.min.js"></script>
    <script>
        document.addEventListener('click', function(e) {
            var a = e.target.closest('[data-toggle="modal"][data-target="#addCustomerModal"]');
            if (!a) return;
            var bd = document.getElementById('tpBackdrop');
            if (bd) {
                bd.classList.remove('show');
                bd.setAttribute('aria-hidden', 'true');
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
            jQuery(function($) {
                $(document).on('show.bs.modal', '#addCustomerModal, #quotationModal', function() {
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
        (function() {
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
                    swal({
                        title: "Today's Rate",
                        text: text
                    });
                } else {
                    alert("Today's Rate\n\n" + text);
                }
            };

            if (window.jQuery && typeof jQuery.fn.on === 'function') {
                jQuery(function($) {
                    // Namespace the handler to avoid duplicates
                    $('.getRate').off('click.__rate').on('click.__rate', function() {
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
        })();
    </script>

    <!-- APPROVE helper: copy selected branch into hidden field -->
    <script>
        function handleApprove(btn, id) {
            if (!confirm('Approve this request?')) {
                return false;
            }

            var select = document.querySelector('select.assign-branch[data-id="' + id + '"]');
            if (!select || !select.value) {
                alert('Please select a branch before approving.');
                return false;
            }

            var form = btn.closest('form');
            if (!form) {
                return false;
            }

            var hiddenBranch = form.querySelector('input[name="branch"]');
            if (hiddenBranch) {
                hiddenBranch.value = select.value;
            }
            return true; // allow submit
        }
    </script>

    <script>
        (function() {
            // Wait for DOM ready (in case jQuery didn't load, use vanilla fallback)
            function initReleaseToggle() {
                var businessSelect = document.querySelector('select[name="customerType"]');
                var releaseAmountWrapper = document.getElementById('releaseAmountWrapper');
                var releasePlaceWrapper = document.getElementById('releasePlaceWrapper');
                var releaseFieldsRow = document.getElementById('releaseFieldsRow'); // optional wrapper

                if (!businessSelect || !releaseAmountWrapper || !releasePlaceWrapper) return;

                function toggleReleaseFields() {
                    var val = (businessSelect.value || '').toLowerCase();

                    var show = (val === 'release');

                    if (releaseFieldsRow) {
                        // If you used the wrapper row
                        releaseFieldsRow.style.display = show ? 'block' : 'none';
                    } else {
                        // If you kept the original two divs without wrapper
                        releaseAmountWrapper.style.display = show ? 'block' : 'none';
                        releasePlaceWrapper.style.display = show ? 'block' : 'none';
                    }

                    // Optional: clear values when hiding
                    if (!show) {
                        var amtInput = document.querySelector('input[name="releaseAmount"]');
                        var placeInput = document.getElementById('releasePlace');
                        if (amtInput) amtInput.value = '';
                        if (placeInput) placeInput.value = '';
                    }
                }

                // On change
                businessSelect.addEventListener('change', toggleReleaseFields);

                // On page load (in case of validation errors, back-navigation, etc.)
                toggleReleaseFields();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initReleaseToggle);
            } else {
                initReleaseToggle();
            }
        })();
    </script>

    
