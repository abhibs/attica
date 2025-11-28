<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Kolkata');

include("dbConnection.php");

/* --------- Guard (no output yet) --------- */
$type = $_SESSION['usertype'] ?? '';
if ($type !== 'Accounts') {
    include("logout.php");
    exit();
}

/* --------- Flash (session) --------- */
$flash = $_SESSION['flash_msg'] ?? '';
$flashClass = $_SESSION['flash_class'] ?? 'info';
unset($_SESSION['flash_msg'], $_SESSION['flash_class']);

/* --------- Handle per-row status update for chequetransfer (APPROVAL) --------- */
/* IMPORTANT: This is BEFORE any HTML output or header/menu includes */
if (isset($_POST['update_chequetransfer'])) {
    $id = (int)($_POST['cq_id'] ?? 0);
    $new = $_POST['new_status'] ?? '';
    $approvalEmpId = trim((string)($_POST['approvalEmpId'] ?? ''));
    $allowed = ['Approved','Rejected']; // approval must be one of these

    if (!$id) {
        $_SESSION['flash_msg'] = 'Invalid cheque row selected.';
        $_SESSION['flash_class'] = 'danger';
    } elseif (!in_array($new, $allowed, true)) {
        $_SESSION['flash_msg'] = 'Invalid cheque status.';
        $_SESSION['flash_class'] = 'danger';
    } elseif ($approvalEmpId === '') {
        $_SESSION['flash_msg'] = 'Approval EmpId is required to approve/reject cheque.';
        $_SESSION['flash_class'] = 'danger';
    } else {
        // Approve/Reject across branches; only transition from Pending
        $sql = "UPDATE chequetransfer
                   SET status=?, approvalEmpId=?
                 WHERE id=? AND status='Pending'";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt && mysqli_stmt_bind_param($stmt, "ssi", $new, $approvalEmpId, $id) && mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['flash_msg'] = "Cheque #$id updated to $new by $approvalEmpId.";
                $_SESSION['flash_class'] = 'success';
            } else {
                $_SESSION['flash_msg'] = "No change made. Cheque might not be Pending or id not found.";
                $_SESSION['flash_class'] = 'warning';
            }
        } else {
            $_SESSION['flash_msg'] = "Cheque update failed: ".mysqli_error($con);
            $_SESSION['flash_class'] = 'danger';
        }
        if ($stmt) mysqli_stmt_close($stmt);
    }
    // PRG redirect BEFORE any output → prevents blank page/resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* ================== SAFE TO OUTPUT NOW ================== */
include("header.php");
include("menuacc.php");
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
    .cq-select{ min-width:110px; }
    .cq-update{ padding:6px 10px; font-size:11px; }
    .cq-emp{ max-width:140px; }
</style>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashClass); ?>" style="margin-top:10px;font-weight:600;">
                    <?php echo htmlspecialchars($flash); ?>
                </div>
            <?php endif; ?>

            <!-- ==================== Panel: Cheque Transfer Entries (Pending across all branches) ==================== -->
            <div class="hpanel" style="margin-top:22px;">
                <div class="panel-heading">
                    <h2><i class="fa_Icon fa fa-bank"></i> Cheque Transfer Entries</h2>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Cheque No</th>
                                <th>Cheque Date</th>
                                <th>Bank</th>
                                <th>Person</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Approval EmpId</th>
                                <th>New Status</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            // Show ALL pending cheques (do not filter by branch for Accounts)
                            $cqSql = "
                                SELECT id, branchId, cheque_number, cheque_date, bank_name, person_name,
                                       amount, status, approvalEmpId, created_at
                                FROM chequetransfer
                                WHERE status = 'Pending'
                                ORDER BY id DESC";
                            $cqRes = mysqli_query($con, $cqSql);
                            if ($cqRes && mysqli_num_rows($cqRes) > 0) {
                                while ($r = mysqli_fetch_assoc($cqRes)) {
                                    $id = (int)$r['id'];
                                    echo "<tr>";
                                    echo "<td>".$id."</td>";
                                    echo "<td>".htmlspecialchars($r['branchId'])."</td>";
                                    echo "<td>".htmlspecialchars($r['cheque_number'])."</td>";
                                    echo "<td>".($r['cheque_date'] ? htmlspecialchars(date('d-m-Y', strtotime($r['cheque_date']))) : "-")."</td>";
                                    echo "<td>".htmlspecialchars($r['bank_name'] ?: '-')."</td>";
                                    echo "<td>".htmlspecialchars($r['person_name'])."</td>";
                                    echo "<td><b>".number_format((int)$r['amount'])."</b></td>";
                                    echo "<td>".htmlspecialchars($r['status'])."</td>";
                                    echo "<td>".htmlspecialchars($r['created_at'])."</td>";

                                    // Per-row form for approval
                                    echo "<td>";
                                    echo "<form method='post' action='' style='margin:0; display:inline-block;'>";
                                    echo "<input type='hidden' name='cq_id' value='".$id."'>";
                                    echo "<input type='text' name='approvalEmpId' class='form-control cq-emp' placeholder='Enter EmpId' required>";
                                    echo "</td>";

                                    echo "<td>";
                                    echo "<select name='new_status' class='form-control cq-select'>";
                                    $opts = ['Approved','Rejected']; // no Pending here
                                    foreach ($opts as $opt) {
                                        echo "<option value='".htmlspecialchars($opt)."'>".htmlspecialchars($opt)."</option>";
                                    }
                                    echo "</select>";
                                    echo "</td>";

                                    echo "<td>";
                                    echo "<button type='submit' name='update_chequetransfer' class='btn btn-success cq-update'>";
                                    echo "<i style='color:#ffcf40' class='fa fa-save'></i> Update";
                                    echo "</button>";
                                    echo "</form>";
                                    echo "</td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' style='text-align:center;font-weight:600;'>No pending cheque transfers</td></tr>";
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
    let printBill = (doc) => {
        let objFra = document.createElement('iframe');
        objFra.style.visibility = 'hidden';
        objFra.src = doc;
        document.body.appendChild(objFra);
        objFra.contentWindow.focus();
        objFra.contentWindow.print();
    }
</script>
