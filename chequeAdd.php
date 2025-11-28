<?php
/* chequeTransfer.php — single EmpId, branch from session; PRG; status=Pending on insert */
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/dbConnection.php';

$type      = $_SESSION['usertype']   ?? '';
$branchId  = $_SESSION['branchCode'] ?? '';
$empIdSes  = $_SESSION['empId']      ?? '';
$date      = date('Y-m-d');

/* flash via session (PRG) */
$flashMsg   = $_SESSION['flash_msg']   ?? '';
$flashClass = $_SESSION['flash_class'] ?? 'info';
unset($_SESSION['flash_msg'], $_SESSION['flash_class']);

/* helpers */
$sval = fn($k) => trim((string)($_POST[$k] ?? ''));
$ival = fn($k) => max(0, (int)($_POST[$k] ?? 0));

/* 1) SUBMIT (DB insert) — PRG */
if (isset($_POST['submitCheque'])) {
    $cheque_number = $sval('cheque_number');
    $cheque_date   = $sval('cheque_date'); // may be ''
    $bank_name     = $sval('bank_name');   // may be ''
    $person_name   = $sval('person_name');
    $amount        = $ival('amount');
    $empId         = $empIdSes ?: $sval('empId');

    if ($type !== 'Branch' || $branchId==='') {
        $_SESSION['flash_msg']='Branch not found in session.';
        $_SESSION['flash_class']='danger';
    } elseif ($cheque_number==='' || $person_name==='' || $amount<=0 || $empId==='') {
        $_SESSION['flash_msg']='Cheque No, Person, Amount and EmpId are required.';
        $_SESSION['flash_class']='danger';
    } else {
        // Set DB variables (must be variables for bind_param; no expressions)
        $cheque_date_db = ($cheque_date !== '') ? $cheque_date : NULL;
        $bank_name_db   = ($bank_name !== '')   ? $bank_name   : NULL;
        $status         = 'Pending';

        $sql = "INSERT INTO `chequetransfer`
                (`branchId`,`cheque_number`,`cheque_date`,`bank_name`,`person_name`,`amount`,`status`,`empId`,`approvalEmpId`)
                VALUES (?,?,?,?,?,?,?,?,NULL)";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            $_SESSION['flash_msg']="Prepare failed: ".mysqli_error($con);
            $_SESSION['flash_class']='danger';
        } else {
            // types: s s s s s i s s  (approvalEmpId is NULL in SQL)
            mysqli_stmt_bind_param(
                $stmt,
                "sssssiss",
                $branchId,
                $cheque_number,
                $cheque_date_db,  // variable, may be NULL
                $bank_name_db,    // variable, may be NULL
                $person_name,
                $amount,
                $status,          // explicitly 'Pending'
                $empId
            );
            if (!mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_msg']="Insert failed: ".mysqli_error($con);
                $_SESSION['flash_class']='danger';
            } else {
                $_SESSION['flash_msg']="Cheque transfer saved with Pending status for ₹".number_format($amount);
                $_SESSION['flash_class']='success';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // PRG redirect to avoid resubmission on refresh
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

/* 2) NORMAL PAGE */
if ($type == 'Branch') {
    include("header.php");
    include("menu.php");
} else {
    include("logout.php");
    exit();
}
?>
<style>
	#results img{ width:100px; }
	#wrapper{ background:#f5f5f5; }
	#wrapper h1,#wrapper h3{ text-transform:uppercase;font-weight:600;font-size:20px;color:#123C69; }
	#wrapper h4{ text-transform:uppercase;font-weight:600;font-size:16px;color:#123C69; }
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
	.quotation h3{ color:#123C69;font-size:18px!important; }
	.text-success{ color:#123C69;text-transform:uppercase;font-weight:bold;font-size:11px; }
	.btn-primary{ background-color:#123C69; }
	.btn-info{ background-color:#123C69;border-color:#123C69;font-size:12px; }
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{ background-color:#123C69;border-color:#123C69; }
	.fa_Icon{ color:#ffa500; }
	thead{ text-transform:uppercase;background-color:#123C69; }
	thead tr{ color:#f2f2f2;font-size:12px; }
	.dataTables_empty{ text-align:center;font-weight:600;font-size:12px;text-transform:uppercase; }
	.btn-success{
		display:inline-block;padding:0.7em 1.4em;margin:0 0.3em 0.3em 0;border-radius:0.15em;box-sizing:border-box;
		text-decoration:none;font-size:12px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;
		background-color:#123C69;box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);text-align:center;position:relative;
	}
	.modaldesign{ float:right;cursor:pointer;padding:5px;background:none;color:#f0f8ff;border-radius:5px;margin:15px;font-size:20px; }
	#available{ text-transform:uppercase; }
	.panel-heading{ margin-bottom:15px; }
	.panel-box{
		margin-top:20px;border:4px solid #fff;border-radius:10px;padding:10px;overflow:hidden;
		background-image:-moz-linear-gradient(top,#f5f5f5,#f6f2ec);
		background-image:-webkit-gradient(linear,left top,left bottom,color-stop(0,#f5f5f5),color-stop(1,#f6f2ec));
		filter:progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec');
		-ms-filter:"progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec')";
		-moz-box-shadow:0 0 2px rgba(0,0,0,0.35), 0 85px 180px 0 #fff, 0 12px 8px -5px rgba(0,0,0,0.85);
		-webkit-box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 810px -68px #fff, 0 12px 8px -5px rgb(0 0 0 / 65%);
		box-shadow:0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);
	}
	input[data-readonly]{ pointer-events:none;background-color:#fffafa; }
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
            <?php if (!empty($flashMsg)): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashClass); ?>" style="margin-top:10px;font-weight:600;">
                    <?php echo htmlspecialchars($flashMsg); ?>
                </div>
            <?php endif; ?>

            <div class="hpanel panel-box">
                <div class="panel-heading">
                    <h3 class="text-success no-margins">
                        <span style="color:#900" class="fa fa-file-text"></span> <b>CHEQUE TRANSFER</b>
                        <button style="float:right" onclick="window.location.reload();" class="btn btn-success">
                            <b><i style="color:#ffcf40" class="fa fa-spinner"></i> RELOAD</b>
                        </button>
                    </h3>
                </div>

                <form method="post" action="">
                    <label class="col-sm-12"><br></label>
                    <h3>&nbsp; <span style="color:#900" class="fa fa-bank"></span> ADD CHEQUE DETAILS</h3>

                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr class="text-success" align="center">
                            <td><b>Cheque Number</b></td>
                            <td><b>Cheque Date</b></td>
                            <td><b>Bank Name</b></td>
                            <td><b>Person Name</b></td>
                            <td><b>Amount (₹)</b></td>
                            <td><b>EmpId</b></td>
                        </tr>
                        <tr>
                            <td><input type="text"   name="cheque_number"  id="cheque_number" class="form-control" required></td>
                            <td><input type="date"   name="cheque_date"    id="cheque_date"   class="form-control"></td>
                            <td><input type="text"   name="bank_name"      id="bank_name"     class="form-control"></td>
                            <td><input type="text"   name="person_name"    id="person_name"   class="form-control" required></td>
                            <td><input type="number" name="amount"         id="amount"        class="form-control" min="1" step="1" required></td>
                            <td>
                                <input type="text" name="empId" id="empId" class="form-control"
                                       value="<?php echo htmlspecialchars($empIdSes); ?>"
                                       <?php echo $empIdSes ? 'readonly' : ''; ?> required>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:center"><br>
                                <button class="btn btn-success" name="submitCheque" id="submitCheque" type="submit">
                                    <span style="color:#ffcf40" class="fa fa-save"></span> Submit Cheque Transfer
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php");?>
</div>
