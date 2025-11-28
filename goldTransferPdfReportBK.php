<?php
/* goldTransferPdfReport.php — inline ornament rows with selective borders */
ob_start();
session_start();

ini_set('display_errors', '1');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Branch') {
    include("logout.php");
    exit();
}

include("dbConnection.php"); // defines $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

$branchId = $_SESSION['branchCode'] ?? '';
$today    = date('Y-m-d');

/* helpers */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function num0($v){ return number_format((float)$v, 0); }
function num2($v){ return number_format((float)$v, 2); }
function enc($s){
    if ($s === null) return '';
    $out = @iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',(string)$s);
    return $out !== false ? $out : utf8_decode((string)$s);
}
function ensure_space($pdf, $needed = 20) {
    if ($pdf->GetY() + $needed > 285) $pdf->AddPage();
}

/* POST validate */
if (!isset($_POST['mul']) || !is_array($_POST['mul'])) {
    echo "<script>alert('No rows selected. Please select at least one row before printing the report.'); window.close();</script>";
    exit();
}
$selectedIds = array_values(array_filter(array_map('intval', $_POST['mul']), fn($v)=>$v>0));
if (empty($selectedIds)) {
    echo "<script>alert('No valid selections.'); window.close();</script>";
    exit();
}

$tareWeight = isset($_POST['tareweight']) ? (float)$_POST['tareweight'] : 0.0;
$fwdInput   = isset($_POST['fwd']) && is_array($_POST['fwd']) ? $_POST['fwd'] : [];
$fwdFirst   = isset($_POST['fwd_first']) ? (string)$_POST['fwd_first'] : '';

/* branch map */
$branchMap = [];
try {
    $rs = mysqli_query($con, "SELECT branchId, branchName FROM branch");
    while ($r = mysqli_fetch_assoc($rs)) $branchMap[$r['branchId']] = $r['branchName'];
} catch (Throwable $e) {}
$branchMap['HO'] = $branchMap['HO'] ?? 'Head Office';

/* resolve "Forwarding To" */
$forwardCode = '';
if (!empty($selectedIds)) {
    $firstId = (int)$selectedIds[0];
    if (isset($fwdInput[$firstId]) && trim((string)$fwdInput[$firstId]) !== '') $forwardCode = (string)$fwdInput[$firstId];
}
if ($forwardCode === '' && $fwdFirst !== '') $forwardCode = $fwdFirst;
$forwardCode = strtoupper(preg_replace('/\s+/', '', (string)$forwardCode));

$forwardName = '';
if ($forwardCode !== '') {
    if (isset($branchMap[$forwardCode]) && trim($forwardCode) !== '') {
        $forwardName = $branchMap[$forwardCode];
    } else {
        $escCode = mysqli_real_escape_string($con, $forwardCode);
        $r2 = mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$escCode' LIMIT 1");
        if ($r2 && mysqli_num_rows($r2)>0) $forwardName = mysqli_fetch_assoc($r2)['branchName'];
    }
    if ($forwardCode === 'HO' && $forwardName === '') $forwardName = 'Head Office';
}
$forwardBranch = ($forwardCode !== '')
    ? (($forwardName !== '') ? ($forwardName.' ('.$forwardCode.')') : $forwardCode)
    : '';

/* BM details */
$branchData = ['branchName'=>'', 'empId'=>'', 'name'=>''];
try {
    $stmt = mysqli_prepare($con, "
        SELECT b.branchName, e.empId, e.name
          FROM branch b
     LEFT JOIN users u ON u.branch = b.branchId
     LEFT JOIN employee e ON e.empId = u.employeeId
         WHERE b.branchId = ?
         LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "s", $branchId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) $branchData = $row;
    mysqli_stmt_close($stmt);
} catch (Throwable $e) {}

/* selected trans rows */
$idIn = implode(',', $selectedIds);
$rows = [];
$billIds = [];
$q = mysqli_query($con, "
    SELECT id,billId,date,grossW,netW,grossA,netA,purity,rate,COALESCE(margin,comm,0) AS margin,type,branchId,CurrentBranch
      FROM trans
     WHERE id IN ($idIn)
       AND status='Approved'
       AND metal='Gold'
     ORDER BY date, billId
");
while ($r = mysqli_fetch_assoc($q)) {
    $rows[] = $r;
    if (!in_array($r['billId'], $billIds, true)) $billIds[] = $r['billId'];
}
if (empty($rows)) {
    echo "<script>alert('Unable to load selected rows.'); window.close();</script>";
    exit();
}

/* ornaments (pieces > 0) — need weight, nine, reading, purity, gross, type (ornament) */
$ornamentsByBill = [];
if (!empty($billIds)) {
    $safe = array_map(fn($b)=>"'".mysqli_real_escape_string($con,(string)$b)."'", $billIds);
    $in   = implode(',', $safe);
    $oq = mysqli_query($con, "
        SELECT billId, date, pieces, reading, purity, gross, weight, nine, type
          FROM ornament
         WHERE billId IN ($in)
           AND CAST(pieces AS SIGNED) > 0
         ORDER BY billId, date
    ");
    while ($o = mysqli_fetch_assoc($oq)) {
        $k = (string)$o['billId'];
        if (!isset($ornamentsByBill[$k])) $ornamentsByBill[$k] = [];
        $ornamentsByBill[$k][] = $o;
    }
}

/* PDF */
require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Times','',9);
        $this->Cell(0, 8, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->SetLeftMargin(10);
$pdf->SetRightMargin(10);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetDrawColor(30,30,30);
$pdf->SetTextColor(0);
$pdf->SetFillColor(235,239,255);

/* header */
$pdf->SetXY(10, 10);
$pdf->SetFont('Times','B',16);
$pdf->Cell(120, 8, enc("Gold Send Report (Selected)"), 0, 1, "L");
$pdf->Ln(1);

$pdf->SetFont('Times','',11);
$pdf->Cell(30, 6, enc("Branch"), 0, 0);
$pdf->Cell(4, 6, enc(":"), 0, 0, "C");
$pdf->Cell(0, 6, enc(($branchData['branchName'] ?? '').' ('.$branchId.')'), 0, 1);

$pdf->Cell(30, 6, enc("BM"), 0, 0);
$pdf->Cell(4, 6, enc(":"), 0, 0, "C");
$pdf->Cell(0, 6, enc(($branchData['name'] ?? '')." / ".($branchData['empId'] ?? '')), 0, 1);

$pdf->Cell(30, 6, enc("Date"), 0, 0);
$pdf->Cell(4, 6, enc(":"), 0, 0, "C");
$pdf->Cell(0, 6, enc(date("d-m-Y", strtotime($today))), 0, 1);
$pdf->Ln(2);

$pdf->Cell(30, 6, enc("Forwarding To"), 0, 0);
$pdf->Cell(4, 6, enc(":"), 0, 0, "C");
$pdf->Cell(0, 6, enc($forwardBranch), 0, 1);
$pdf->Ln(2);

/* column widths */
$w = [
    'sno'   => 8,
    'bill'  => 20,
    'date'  => 26,
    'gw'    => 20,
    'nw'    => 20,
    'ga'    => 28,
    'na'    => 28,
    'pur'   => 14,
    'rate'  => 14,
    'margin'=> 16
];

/* head row */
$pdf->SetFont('Times','B',10);
$pdf->SetFillColor(18,60,105);
$pdf->SetTextColor(255,255,255);
$pdf->Cell($w['sno'],   7, enc("#"),            1, 0, "C", true);
$pdf->Cell($w['bill'],  7, enc("Bill ID"),      1, 0, "C", true);
$pdf->Cell($w['date'],  7, enc("Date"),         1, 0, "C", true);
$pdf->Cell($w['gw'],    7, enc("Gross Wt"),     1, 0, "C", true);
$pdf->Cell($w['nw'],    7, enc("Net Wt"),       1, 0, "C", true);
$pdf->Cell($w['ga'],    7, enc("Gross Amount"), 1, 0, "C", true);
$pdf->Cell($w['na'],    7, enc("Net Amount"),   1, 0, "C", true);
$pdf->Cell($w['pur'],   7, enc("Purity"),       1, 0, "C", true);
$pdf->Cell($w['rate'],  7, enc("Rate"),         1, 0, "C", true);
$pdf->Cell($w['margin'],7, enc("Margin"),       1, 1, "C", true);

/* body with inline ornament rows (empty cells -> no borders) */
$pdf->SetFont('Times','',10);
$pdf->SetTextColor(0,0,0);

$sn=1; $t_gw=0; $t_nw=0; $t_ga=0; $t_na=0; $t_margin=0;
$countPhysical=0; $countRelease=0;
$sum_purity=0.0; $row_count=0;

foreach ($rows as $r) {
    $gw = (float)$r['grossW'];
    $nw = (float)$r['netW'];
    $ga = (float)$r['grossA'];
    $na = (float)$r['netA'];
    $pur = $r['purity'];
    $rate = $r['rate'];
    $margin = (float)$r['margin'];

    ensure_space($pdf, 14);

    /* === CHANGE #1: Make this whole transaction row bold + light blue fill === */
    $pdf->SetFont('Times','B',10);
    $pdf->SetFillColor(235,245,255); // light blue
    $pdf->Cell($w['sno'],   7, $sn,                 1, 0, "C", true);
    $pdf->Cell($w['bill'],  7, enc($r['billId']),   1, 0, "C", true);
    $pdf->Cell($w['date'],  7, enc($r['date']),     1, 0, "C", true);
    $pdf->Cell($w['gw'],    7, num2($gw),           1, 0, "R", true);
    $pdf->Cell($w['nw'],    7, num2($nw),           1, 0, "R", true);
    $pdf->Cell($w['ga'],    7, num0($ga),           1, 0, "R", true);
    $pdf->Cell($w['na'],    7, num0($na),           1, 0, "R", true);
    $pdf->Cell($w['pur'],   7, is_numeric($pur)?num2((float)$pur):enc($pur), 1, 0, "R", true);
    $pdf->Cell($w['rate'],  7, is_numeric($rate)?num0((float)$rate):enc($rate), 1, 0, "R", true);
    $pdf->Cell($w['margin'],7, num2($margin),       1, 1, "R", true);
    /* === End change block; reset font for subsequent ornament lines === */
    $pdf->SetFont('Times','',10);
    $pdf->SetFillColor(235,239,255); // restore previous light fill used elsewhere (not applied unless true)

    $sn++;
    $t_gw += $gw; $t_nw += $nw; $t_ga += $ga; $t_na += $na; $t_margin += $margin;
    if (is_numeric($pur)) $sum_purity += (float)$pur;
    $row_count++;
    if (($r['type'] ?? '') === 'Physical Gold') $countPhysical++;
    if (($r['type'] ?? '') === 'Release Gold')  $countRelease++;

    // INLINE ornament rows
    $bill = (string)$r['billId'];
    $oList = $ornamentsByBill[$bill] ?? [];

    // Only ornaments with SAME DATE as trans row and pieces > 0 (second gate)
    $oList = array_values(array_filter($oList, function($o) use ($r){
        if (!isset($o['date'])) return false; // adjust if column differs
        if ((int)$o['pieces'] <= 0) return false;
        return (trim((string)$o['date']) === trim((string)$r['date']));
    }));

    if (!empty($oList)) {
        foreach ($oList as $o) {
            ensure_space($pdf, 8);

            $orn_billCol = enc((string)$o['nine']);                           // under Bill ID
            $orn_dateCol = enc((string)($o['type'] ?? ''));                   // under Date (type from ornament)
            $orn_gw      = is_numeric($o['weight'])  ? num2($o['weight']) : enc($o['weight']);   // Gross Wt
            $orn_nw      = is_numeric($o['reading']) ? num2($o['reading']) : enc($o['reading']); // Net Wt
            $orn_gamt    = is_numeric($o['gross'])   ? num0($o['gross'])   : enc($o['gross']);   // Gross Amount
            $orn_pur     = is_numeric($o['purity'])  ? num2($o['purity'])  : enc($o['purity']);  // Purity

            // For empty cells: border=0 (no border). Filled cells: border=1. (no fill)
            $pdf->Cell($w['sno'],    6, '',             0, 0, "C");         // empty, no border
            $pdf->Cell($w['bill'],   6, $orn_billCol,   1, 0, "C");         // nine
            $pdf->Cell($w['date'],   6, $orn_dateCol,   1, 0, "C");         // ornament type
            $pdf->Cell($w['gw'],     6, $orn_gw,        1, 0, "R");         // Gross Wt = weight
            $pdf->Cell($w['nw'],     6, $orn_nw,        1, 0, "R");         // Net Wt = reading
            $pdf->Cell($w['ga'],     6, $orn_gamt,      1, 0, "R");         // Gross Amount = gross
            $pdf->Cell($w['na'],     6, '',             0, 0, "R");         // Net Amount empty, no border
            $pdf->Cell($w['pur'],    6, $orn_pur,       1, 0, "R");         // Purity
            $pdf->Cell($w['rate'],   6, '',             0, 0, "R");         // Rate empty, no border
            $pdf->Cell($w['margin'], 6, '',             0, 1, "R");         // Margin empty, no border
        }
    }
}

/* totals/averages */
$pdf->SetFont('Times','B',10);
$pdf->SetFillColor(235,239,255);
$packets   = $sn - 1;
$avgPurity = ($row_count > 0) ? round($sum_purity / $row_count, 2) : 0;
$avgRate   = " ";
$avgMargin = ($row_count > 0) ? round($t_margin / $row_count, 2) : 0;

$pdf->Cell($w['sno'] + $w['bill'] + $w['date'], 7, enc("Packets : ").$packets, 1, 0, "C", true);
$pdf->Cell($w['gw'],    7, num2($t_gw),    1, 0, "R", true);
$pdf->Cell($w['nw'],    7, num2($t_nw),    1, 0, "R", true);
$pdf->Cell($w['ga'],    7, num0($t_ga),    1, 0, "R", true);
$pdf->Cell($w['na'],    7, num0($t_na),    1, 0, "R", true);
$pdf->Cell($w['pur'],   7, num2($avgPurity),1, 0, "R", true);
$pdf->Cell($w['rate'],  7, enc($avgRate),  1, 0, "R", true);
$pdf->Cell($w['margin'],7, num2($avgMargin),1, 1, "R", true);

/* summary */
$pdf->Ln(6);
$pdf->SetFont('Times','B',11);
$pdf->Cell(0, 7, enc('Summary'), 0, 1, 'L');

$pdf->SetFont('Times','B',10);
$pdf->SetFillColor(18,60,105);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(40, 7, enc('Type'),  1, 0, 'C', true);
$pdf->Cell(25, 7, enc('Count'), 1, 1, 'C', true);

$pdf->SetFont('Times','',10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(40, 7, enc('Physical Gold'), 1, 0, 'L');
$pdf->Cell(25, 7, (string)$countPhysical, 1, 1, 'C');
$pdf->Cell(40, 7, enc('Release Gold'),  1, 0, 'L');
$pdf->Cell(25, 7, (string)$countRelease, 1, 1, 'C');

/* footer blocks */
$placeStr = ($branchData['branchName'] ?? '') . ' (' . $branchId . ')';
$timeStr  = ''; // === CHANGE #2: leave time value empty ===

$labelW = 22; $colonW = 4; $valW = 60; $gapW = 10;

$pdf->Ln(6);
$pdf->Cell($labelW, 8, enc('Tare Weight'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),           0, 0, 'C');
$pdf->Cell(40,     8, enc(num2($tareWeight) . ' g'), 0, 0, 'L');
$pdf->Cell($gapW,  8, '', 0, 0);
$pdf->Cell($labelW,8, enc('Place'), 0, 0, 'L');
$pdf->Cell($colonW,8, enc(':'),    0, 0, 'C');
$pdf->Cell($valW,  8, enc($placeStr), 0, 1, 'L');

$pdf->Cell($labelW, 8, enc('BM'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),  0, 0, 'C');
$pdf->Cell(40,     8, enc(''),    0, 0, 'L');
$pdf->Cell($gapW,  8, '',         0, 0);
$pdf->Cell($labelW,8, enc('Time'),0, 0, 'L');
$pdf->Cell($colonW,8, enc(':'),   0, 0, 'C');
$pdf->Cell($valW,  8, enc($timeStr), 0, 1, 'L'); // empty value

$pdf->Cell($labelW, 8, enc('ABM'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),   0, 0, 'C');
$pdf->Cell(40,     8, enc(''),     0, 0, 'L');
$pdf->Cell($gapW,  8, '',          0, 0);
$pdf->Cell($labelW,8, enc('Carrier'), 0, 0, 'L');
$pdf->Cell($colonW,8, enc(':'),       0, 0, 'C');
$pdf->Cell($valW,  8, enc(''),        0, 1, 'L');

/* output */
$filename = "GoldSendReport_Selected_".$branchId."_".date('Ymd_His').".pdf";
$pdf->Output($filename, 'I');
?>

