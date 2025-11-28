<?php
/* cashSending.php — PDF + signatures + PRG + defaults(0) */
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/dbConnection.php';

$type     = $_SESSION['usertype']  ?? '';
$branchId = $_SESSION['branchCode'] ?? '';
$date     = date('Y-m-d');

/* flash via session (PRG) */
$flashMsg   = $_SESSION['flash_msg']   ?? '';
$flashClass = $_SESSION['flash_class'] ?? 'info';
unset($_SESSION['flash_msg'], $_SESSION['flash_class']);

/* helpers */
$ival  = fn($k) => max(0, (int)($_POST[$k] ?? 0));
$total = function($c2000,$c500,$c200,$c100,$c50,$c20,$c10,$c5,$c2,$c1){
  return 2000*$c2000 + 500*$c500 + 200*$c200 + 100*$c100 + 50*$c50
       +   20*$c20   +  10*$c10 +   5*$c5   +   2*$c2   +  1*$c1;
};

/* ────────────────────────────────────────────────────────────────────────────
   1) PDF PRINT — MUST run before any HTML output
   ──────────────────────────────────────────────────────────────────────────── */
if (isset($_POST['action']) && $_POST['action'] === 'pdf') {
    if (ob_get_length()) { ob_end_clean(); }

    $c2000=$ival('aa'); $c500=$ival('cc'); $c200=$ival('bb'); $c100=$ival('dd'); $c50=$ival('ee');
    $c20=$ival('jj');   $c10=$ival('ff');  $c5=$ival('gg');  $c2=$ival('hh');  $c1=$ival('ii');
    $sendingEmp   = trim((string)($_POST['SendingEmpId'] ?? ''));
    $receivingEmp = trim((string)($_POST['ReceivingEmpId'] ?? ''));
    $totalServer  = $total($c2000,$c500,$c200,$c100,$c50,$c20,$c10,$c5,$c2,$c1);

    $fpdfPath = __DIR__.'/fpdf/fpdf.php';
    if (file_exists($fpdfPath)) {
        require_once $fpdfPath;
        class PDF extends FPDF { function Footer(){ $this->SetY(-12); $this->SetFont('Times','',9); $this->Cell(0,8,'Page '.$this->PageNo().'/{nb}',0,0,'C'); } }
        $pdf = new PDF('P','mm','A4'); $pdf->AliasNbPages(); $pdf->AddPage(); $pdf->SetMargins(10,10,10);

        $pdf->SetFont('Times','B',16); $pdf->Cell(0,8,'Cash Sending - Denominations',0,1,'C'); $pdf->Ln(1);
        $pdf->SetFont('Times','',12);
        $pdf->Cell(50,6,'Date:',0,0);            $pdf->Cell(0,6,date('d-m-Y',strtotime($date)),0,1);
        $pdf->Cell(50,6,'Branch ID:',0,0);       $pdf->Cell(0,6,$branchId,0,1);
        $pdf->Cell(50,6,'Sending EmpId:',0,0);   $pdf->Cell(0,6,$sendingEmp,0,1);
        $pdf->Cell(50,6,'Receiving EmpId:',0,0); $pdf->Cell(0,6,$receivingEmp,0,1);

        $pdf->Ln(4);
        $pdf->SetFont('Times','B',12); $pdf->SetFillColor(18,60,105); $pdf->SetTextColor(255,255,255);
        $pdf->Cell(60,8,'Denomination',1,0,'C',true);
        $pdf->Cell(60,8,'Count',       1,0,'C',true);
        $pdf->Cell(60,8,'Amount',      1,1,'C',true);

        $pdf->SetTextColor(0,0,0); $pdf->SetFont('Times','',12);
        $row=function($lbl,$cnt,$amt)use($pdf){$pdf->Cell(60,8,$lbl,1,0,'C');$pdf->Cell(60,8,number_format($cnt,0),1,0,'R');$pdf->Cell(60,8,number_format($amt,0),1,1,'R');};
        $row('2000',$c2000,2000*$c2000); $row('500',$c500,500*$c500); $row('200',$c200,200*$c200);
        $row('100',$c100,100*$c100); $row('50',$c50,50*$c50); $row('20',$c20,20*$c20);
        $row('10',$c10,10*$c10); $row('5',$c5,5*$c5); $row('2',$c2,2*$c2); $row('1',$c1,1*$c1);

        $pdf->SetFont('Times','B',12); $pdf->Cell(120,8,'TOTAL',1,0,'R'); $pdf->Cell(60,8,number_format($totalServer,0),1,1,'R');

        // Signatures
        $pdf->Ln(14); $pdf->SetFont('Times','',11);
        $pdf->Cell(60,6,'Sending Employee',0,0,'L');
        $pdf->Cell(60,6,'Receiving Employee',0,0,'L');
        $pdf->Cell(60,6,'Verified By',0,1,'L');
        $y=$pdf->GetY()+10; $leftX=10; $midX=80; $rightX=150; $lineW=60;
        $pdf->Line($leftX,$y,$leftX+$lineW,$y);
        $pdf->Line($midX,$y,$midX+$lineW,$y);
        $pdf->Line($rightX,$y,$rightX+$lineW,$y);
        $pdf->SetY($y+2); $pdf->SetFont('Times','',9);
        $pdf->Cell(60,5,'Sign & Date (ID: '.$sendingEmp.')',0,0,'L');
        $pdf->Cell(60,5,'Sign & Date (ID: '.$receivingEmp.')',0,0,'L');
        $pdf->Cell(60,5,'Sign & Date',0,1,'L');

        header('Content-Type: application/pdf');
        $pdf->Output('I','CashSending_'.$branchId.'_'.$date.'.pdf');
        exit;
    } else {
        // HTML print fallback with signatures
        if (ob_get_length()) { ob_end_clean(); }
        header('Content-Type: text/html; charset=utf-8'); ?>
        <!doctype html><html><head><meta charset="utf-8"><title>Cash Sending - Denominations</title>
        <style>
          body{font-family:Arial,Helvetica,sans-serif;font-size:14px}
          h2{margin:8px 0} table{border-collapse:collapse;width:100%}
          th,td{border:1px solid #444;padding:8px;text-align:center}
          thead th{background:#123C69;color:#fff} .meta td{border:none;text-align:left;padding:3px 0}
          .row{display:flex;margin-top:24px} .sig{flex:1;margin:0 8px} .line{border-top:1px solid #000;height:1px;margin-top:28px}
          @media print{.noprint{display:none}}
        </style></head><body>
        <h2 style="text-align:center;">Cash Sending - Denominations</h2>
        <table class="meta" style="width:100%;margin-bottom:8px;">
          <tr><td><b>Date:</b> <?=htmlspecialchars(date('d-m-Y',strtotime($date)))?></td></tr>
          <tr><td><b>Branch ID:</b> <?=htmlspecialchars($branchId)?></td></tr>
          <tr><td><b>Sending EmpId:</b> <?=htmlspecialchars($sendingEmp)?></td></tr>
          <tr><td><b>Receiving EmpId:</b> <?=htmlspecialchars($receivingEmp)?></td></tr>
        </table>
        <table><thead><tr><th>Denomination</th><th>Count</th><th>Amount</th></tr></thead><tbody>
          <?php
            $rows=[2000=>$c2000,500=>$c500,200=>$c200,100=>$c100,50=>$c50,20=>$c20,10=>$c10,5=>$c5,2=>$c2,1=>$c1];
            foreach($rows as $d=>$cnt){ echo "<tr><td>$d</td><td>$cnt</td><td>".number_format($d*$cnt,0)."</td></tr>"; }
          ?>
          <tr><th colspan="2" style="text-align:right;">TOTAL</th><th><?=number_format($totalServer,0)?></th></tr>
        </tbody></table>
        <div class="row">
          <div class="sig"><b>Sending Employee (ID: <?=htmlspecialchars($sendingEmp)?>)</b><div class="line"></div><div>Sign & Date</div></div>
          <div class="sig"><b>Receiving Employee (ID: <?=htmlspecialchars($receivingEmp)?>)</b><div class="line"></div><div>Sign & Date</div></div>
          <div class="sig"><b>Verified By Cashier(HO)</b><div class="line"></div><div>Sign & Date</div></div>
        </div>
        <div class="noprint" style="text-align:center;margin-top:12px;"><button onclick="window.print()">Print</button></div>
        </body></html><?php
        exit;
    }
}

/* ────────────────────────────────────────────────────────────────────────────
   2) SUBMIT (DB insert) — must happen BEFORE any HTML output for PRG redirect
   ──────────────────────────────────────────────────────────────────────────── */
if (isset($_POST['submitClosing'])) {
    $c2000=$ival('aa'); $c500=$ival('cc'); $c200=$ival('bb'); $c100=$ival('dd'); $c50=$ival('ee');
    $c20=$ival('jj');   $c10=$ival('ff');  $c5=$ival('gg');  $c2=$ival('hh');  $c1=$ival('ii');
    $sendingEmp   = trim((string)($_POST['SendingEmpId'] ?? ''));
    $receivingEmp = trim((string)($_POST['ReceivingEmpId'] ?? ''));
    $totalServer  = $total($c2000,$c500,$c200,$c100,$c50,$c20,$c10,$c5,$c2,$c1);

    if ($type !== 'Branch' || $branchId==='') {
        $_SESSION['flash_msg']='Branch not found in session.'; $_SESSION['flash_class']='danger';
    } elseif ($sendingEmp==='' || $receivingEmp==='') {
        $_SESSION['flash_msg']='Sending and Receiving EmpId are required.'; $_SESSION['flash_class']='danger';
    } else {
        $sql = "INSERT INTO `cashtransfer`
                (`branchId`,`2000`,`500`,`200`,`100`,`50`,`20`,`10`,`5`,`2`,`1`,`total`,`status`,`sendingEmpId`,`receivingEmpId`)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = mysqli_prepare($GLOBALS['con'], $sql);
        if (!$stmt) {
            $_SESSION['flash_msg']="Prepare failed: ".mysqli_error($GLOBALS['con']); $_SESSION['flash_class']='danger';
        } else {
            $status = 'Pending';
            $types  = "s" . str_repeat("i", 11) . "sss";  // 1s + 11i + 3s  => total 15 params
            $ok = mysqli_stmt_bind_param(
                $stmt, $types,
                $branchId, $c2000,$c500,$c200,$c100,$c50,$c20,$c10,$c5,$c2,$c1,
                $totalServer, $status, $sendingEmp, $receivingEmp
            );
            if (!$ok) {
                $_SESSION['flash_msg']="Bind failed: ".mysqli_error($GLOBALS['con']); $_SESSION['flash_class']='danger';
            } elseif (!mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_msg']="Insert failed: ".mysqli_error($GLOBALS['con']); $_SESSION['flash_class']='danger';
            } else {
                $_SESSION['flash_msg']="Cash sending saved. Total ₹".number_format($totalServer); $_SESSION['flash_class']='success';
            }
            mysqli_stmt_close($stmt);
        }
    }
    // PRG redirect BEFORE any output -> avoids resubmission and blank screen
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

/* ────────────────────────────────────────────────────────────────────────────
   3) NORMAL PAGE (safe to output now)
   ──────────────────────────────────────────────────────────────────────────── */
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
                        <span style="color:#900" class="fa fa-file-text"></span> <b>CASH SENDING</b>
                        <button style="float:right" onclick="window.location.reload();" class="btn btn-success">
                            <b><i style="color:#ffcf40" class="fa fa-spinner"></i> RELOAD</b>
                        </button>
                    </h3>
                </div>

                <form method="post" action="">
                    <label class="col-sm-12"><br></label>
                    <h3>&nbsp; <span style="color:#900" class="fa fa-money"></span> ADD SENDING DENOMINATIONS</h3>

                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr class="text-success" align="center">
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 2000 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 500 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 200 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 100 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 50 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 20 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 10 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 5 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 2 X</b></td>
                            <td><b><span style="color:#990000" class="fa fa-money"></span> 1 X</b></td>
                        </tr>
                        <tr>
                            <td><input type="number" min="0" name="aa" id="aa" class="form-control" value="0" onchange="calls1(this.form)"></td>
                            <td><input type="number" min="0" name="cc" id="cc" class="form-control" value="0" onchange="calls3(this.form)"></td>
                            <td><input type="number" min="0" name="bb" id="bb" class="form-control" value="0" onchange="calls2(this.form)"></td>
                            <td><input type="number" min="0" name="dd" id="dd" class="form-control" value="0" onchange="calls4(this.form)"></td>
                            <td><input type="number" min="0" name="ee" id="ee" class="form-control" value="0" onchange="calls5(this.form)"></td>
                            <td><input type="number" min="0" name="jj" id="jj" class="form-control" value="0" onchange="calls10(this.form)"></td>
                            <td><input type="number" min="0" name="ff" id="ff" class="form-control" value="0" onchange="calls6(this.form)"></td>
                            <td><input type="number" min="0" name="gg" id="gg" class="form-control" value="0" onchange="calls7(this.form)"></td>
                            <td><input type="number" min="0" name="hh" id="hh" class="form-control" value="0" onchange="calls8(this.form)"></td>
                            <td><input type="number" min="0" name="ii" id="ii" class="form-control" value="0" onchange="calls9(this.form)"></td>
                        </tr>
                        <tr>
                            <td><input type="text" id="aaa" name="aaa" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="ccc" name="ccc" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="bbb" name="bbb" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="ddd" name="ddd" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="eee" name="eee" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="jjj" name="jjj" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="fff" name="fff" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="ggg" name="ggg" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="hhh" name="hhh" readonly class="form-control" value="0"></td>
                            <td><input type="text" id="iii" name="iii" readonly class="form-control" value="0"></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <b class="text-success">DENOMINATION TOTAL</b>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa fa-rupee" style="color:#990000"></span></span>
                                    <input type="text" data-readonly class="form-control" name="total" id="total" value="0" required>
                                </div>
                            </td>
                            <td colspan="2">
                                <b class="text-success">Sending EmpId</b>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa fa-user"></span></span>
                                    <input type="text" class="form-control" name="SendingEmpId" id="SendingEmpId" required>
                                </div>
                            </td>
                            <td colspan="2">
                                <b class="text-success">Receiving EmpId</b>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa fa-user"></span></span>
                                    <input type="text" class="form-control" name="ReceivingEmpId" id="ReceivingEmpId" required>
                                </div>
                            </td>
                            <td colspan="2" style="text-align:center"><br>
                                <button class="btn btn-info" type="submit" name="action" value="pdf" formtarget="_blank" id="btnPrint">
                                    <span class="fa fa-print"></span> Print PDF
                                </button>
                            </td>
                            <td colspan="2" style="text-align:center"><br>
                                <button class="btn btn-success" name="submitClosing" id="submitClosing" type="submit">
                                    <span style="color:#ffcf40" class="fa fa-save"></span> Submit Cash Sending
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

<script>
function calls1(f){ var n=+f.aa.value||0; f.aaa.value=n*2000; sumAll(f); }
function calls2(f){ var n=+f.bb.value||0; f.bbb.value=n*200;  sumAll(f); }
function calls3(f){ var n=+f.cc.value||0; f.ccc.value=n*500;  sumAll(f); }
function calls4(f){ var n=+f.dd.value||0; f.ddd.value=n*100;  sumAll(f); }
function calls5(f){ var n=+f.ee.value||0; f.eee.value=n*50;   sumAll(f); }
function calls6(f){ var n=+f.ff.value||0; f.fff.value=n*10;   sumAll(f); }
function calls7(f){ var n=+f.gg.value||0; f.ggg.value=n*5;    sumAll(f); }
function calls8(f){ var n=+f.hh.value||0; f.hhh.value=n*2;    sumAll(f); }
function calls9(f){ var n=+f.ii.value||0; f.iii.value=n*1;    sumAll(f); }
function calls10(f){var n=+f.jj.value||0; f.jjj.value=n*20;   sumAll(f); }
function sumAll(f){
  var t=(+f.aaa.value||0)+(+f.ccc.value||0)+(+f.bbb.value||0)+(+f.ddd.value||0)+(+f.eee.value||0)+(+f.jjj.value||0)+(+f.fff.value||0)+(+f.ggg.value||0)+(+f.hhh.value||0)+(+f.iii.value||0);
  f.total.value=t;
}
(function initZero(){ var f=document.forms[0]; if(!f) return; sumAll(f); })();
</script>

