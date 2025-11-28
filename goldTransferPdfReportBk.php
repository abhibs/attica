<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
error_reporting(E_ERROR | E_PARSE);

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Branch') {
    include("logout.php");
    exit();
}

include("dbConnection.php");

$branchId = $_SESSION['branchCode'] ?? '';
$today    = date('Y-m-d');

// Debug: Check if mul[] is received
if (!isset($_POST['mul']) || empty($_POST['mul'])) {
    echo "<script>alert('No rows selected. Please select at least one row before printing the report.'); window.close();</script>";
    exit();
}
// Read tare from POST (printed in footer)
$tareWeight = isset($_POST['tareweight']) ? (float)$_POST['tareweight'] : 0.0;

// Resolve "Forwarding To" from either fwd[selectedId] or the hidden fwd_first helper
$forwardCode = '';
if (!empty($selectedIds)) {
    $firstId = (int)$selectedIds[0];
    if (isset($fwdInput[$firstId]) && $fwdInput[$firstId] !== '') {
        $forwardCode = trim((string)$fwdInput[$firstId]);
    }
}
if ($forwardCode === '' && !empty($_POST['fwd_first'])) {
    $forwardCode = trim((string)$_POST['fwd_first']);
}
$forwardBranch = '';
if ($forwardCode !== '') {
    $forwardBranch = isset($branchMap[$forwardCode])
        ? $branchMap[$forwardCode] . ' (' . $forwardCode . ')'
        : $forwardCode; // fallback to code if name not found
}

$selectedIds = isset($_POST['mul']) && is_array($_POST['mul']) ? array_filter($_POST['mul']) : [];
$fwdInput    = isset($_POST['fwd']) && is_array($_POST['fwd']) ? $_POST['fwd'] : [];

if (empty($selectedIds)) {
    echo "<script>alert('Please select at least one row before printing the report.'); window.close();</script>";
    exit();
}

/* ---------- Branch header info ---------- */
$branchSQL = "
SELECT b.branchName, e.empId, e.name
FROM branch b
LEFT JOIN users u    ON u.branch = b.branchId
LEFT JOIN employee e ON e.empId  = u.employeeId
WHERE b.branchId = '".mysqli_real_escape_string($con, $branchId)."'
LIMIT 1";
$branchData = mysqli_fetch_assoc(mysqli_query($con, $branchSQL)) ?: ['branchName'=>'', 'empId'=>'', 'name'=>''];

/* ---------- Branch map for names ---------- */
$branchMap = [];
$brRes = mysqli_query($con, "SELECT branchId, branchName FROM branch");
while ($r = mysqli_fetch_assoc($brRes)) $branchMap[$r['branchId']] = $r['branchName'];
$branchMap['HO'] = $branchMap['HO'] ?? 'Head Office (HO)';

/* ---------- IN() list ---------- */
$idList = [];
foreach ($selectedIds as $i) { $i = (int)$i; if ($i>0) $idList[] = $i; }
if (!$idList) { echo "<script>alert('No valid selections.'); window.close();</script>"; exit(); }
$inClause = implode(',', $idList);

/* ---------- Fetch data + ornament splits ---------- */
$query = "
SELECT 
  t.id, t.billId, t.date, t.grossW, t.netW, t.grossA, t.netA,
  t.purity, t.rate, COALESCE(t.margin, t.comm, 0) AS margin, t.type,
  t.branchId, t.CurrentBranch,
  ROUND(SUM(CASE WHEN o.nine='24Karat' THEN o.weight ELSE 0 END), 3) AS pure_weight,
  ROUND(SUM(CASE WHEN o.nine='24Karat' THEN o.reading ELSE 0 END), 3) AS pure_net,
  SUM(CASE WHEN o.nine='24Karat' THEN o.gross ELSE 0 END) AS pure_gross
FROM trans t
LEFT JOIN ornament o ON (t.date = o.date AND t.billId = o.billId)
WHERE t.id IN ($inClause) AND t.status='Approved' AND t.metal='Gold'
GROUP BY t.id, t.billId, t.date, t.grossW, t.netW, t.grossA, t.netA,
  t.purity, t.rate, margin, t.type, t.branchId, t.CurrentBranch
ORDER BY t.date, t.billId";
$res = mysqli_query($con, $query);
if (!$res || mysqli_num_rows($res) === 0) {
    echo "<script>alert('Unable to load selected rows.'); window.close();</script>";
    exit();
}

// ---------- Resolve "Forwarding To" (name + code) ----------
$forwardCode = '';
if (!empty($selectedIds)) {
    $firstId = (int)$selectedIds[0];
    if (isset($fwdInput[$firstId]) && $fwdInput[$firstId] !== '') {
        $forwardCode = (string)$fwdInput[$firstId];
    }
}
if ($forwardCode === '' && !empty($_POST['fwd_first'])) {
    $forwardCode = (string)$_POST['fwd_first'];
}

// normalize code (remove spaces, uppercase)
$forwardCode = strtoupper(preg_replace('/\s+/', '', $forwardCode));

$forwardName = '';
if ($forwardCode !== '') {
    // Try map first
    if (isset($branchMap[$forwardCode]) && trim($branchMap[$forwardCode]) !== '') {
        $forwardName = $branchMap[$forwardCode];
    } else {
        // Fallback: direct DB lookup (handles cases where map missed it)
        $fcEsc = mysqli_real_escape_string($con, $forwardCode);
        $rsF = mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$fcEsc' LIMIT 1");
        if ($rsF && mysqli_num_rows($rsF) > 0) {
            $forwardName = mysqli_fetch_assoc($rsF)['branchName'];
        }
    }

    // Special friendly label for HO
    if ($forwardCode === 'HO') {
        $forwardName = $forwardName ?: 'Head Office';
    }
}

$forwardBranch = '';
if ($forwardCode !== '') {
    $forwardBranch = ($forwardName !== '')
        ? ($forwardName.' ('.$forwardCode.')')
        : $forwardCode; // last-resort
}

/* ---------- PDF ---------- */
require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Times','',9);
        $this->Cell(0, 8, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('P','mm','A4');  // Portrait Mode
$pdf->SetLeftMargin(10);        // set margins explicitly
$pdf->SetRightMargin(10);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetDrawColor(30,30,30);
$pdf->SetTextColor(0);
$pdf->SetFillColor(235,239,255);

/* ---------- Helpers ---------- */
function num0($v){ return number_format((float)$v, 0); }
function num2($v){ return number_format((float)$v, 2); }

/* UTF-8 → Windows-1252 for FPDF */
function enc($s) {
    if ($s === null) return '';
    $out = @iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',(string)$s);
    return $out !== false ? $out : utf8_decode((string)$s);
}

// --- Header block ---
$pdf->SetXY(10, 10);
$pdf->SetFont('Times','B',16);
$pdf->Cell(120, 8, enc("Gold Send Report (Selected)"), 0, 1, "L");

$pdf->Ln(1);
$pdf->SetFont('Times','',11);

// Branch
$pdf->Cell(30, 6, enc("Branch"), 0, 0);
$pdf->Cell(4,  6, enc(":"), 0, 0, "C");
$pdf->Cell(0,  6, enc($branchData['branchName'].' ('.$branchId.')'), 0, 1);

// BM
$pdf->Cell(30, 6, enc("BM"), 0, 0);
$pdf->Cell(4,  6, enc(":"), 0, 0, "C");
$pdf->Cell(0,  6, enc($branchData['name']." / ".$branchData['empId']), 0, 1);

// Date
$pdf->Cell(30, 6, enc("Date"), 0, 0);
$pdf->Cell(4,  6, enc(":"), 0, 0, "C");
$pdf->Cell(0,  6, enc(date("d-m-Y", strtotime($today))), 0, 1);

$pdf->Ln(2);

// Forwarding To
$pdf->Cell(30, 6, enc("Forwarding To"), 0, 0);
$pdf->Cell(4,  6, enc(":"), 0, 0, "C");
$pdf->Cell(0,  6, enc($forwardBranch), 0, 1);

$pdf->Ln(2);


/* ---------- Table widths ---------- */
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

/* ---------- Table head ---------- */
$pdf->SetFont('Times','B',10);
$pdf->SetFillColor(18,60,105);
$pdf->SetTextColor(255,255,255);
$pdf->Cell($w['sno'],    7, enc("#"),               1, 0, "C", true);
$pdf->Cell($w['bill'],   7, enc("Bill ID"),         1, 0, "C", true);
$pdf->Cell($w['date'],   7, enc("Date"),            1, 0, "C", true);
$pdf->Cell($w['gw'],     7, enc("Gross Wt"),        1, 0, "C", true);
$pdf->Cell($w['nw'],     7, enc("Net Wt"),          1, 0, "C", true);
$pdf->Cell($w['ga'],     7, enc("Gross Amount"),    1, 0, "C", true);
$pdf->Cell($w['na'],     7, enc("Net Amount"),      1, 0, "C", true);
$pdf->Cell($w['pur'],    7, enc("Purity"),          1, 0, "C", true);
$pdf->Cell($w['rate'],   7, enc("Rate"),            1, 0, "C", true);
$pdf->Cell($w['margin'], 7, enc("Margin"),          1, 1, "C", true);

/* ---------- Body rows ---------- */
$pdf->SetFont('Times','',10);
$pdf->SetTextColor(0,0,0);

$sn=1; $physical=0; $release=0;
$t_gw=0; $t_nw=0; $t_ga=0; $t_na=0; $t_margin=0; $last_rate=1;

while ($row = mysqli_fetch_assoc($res)) {
    $id = (int)$row['id'];

    $pdf->Cell($w['sno'],    7, $sn, 1, 0, "C");
    $pdf->Cell($w['bill'],   7, enc($row['billId']), 1, 0, "C");
    $pdf->Cell($w['date'],   7, enc($row['date']), 1, 0, "C");
    $pdf->Cell($w['gw'],     7, num2($row['grossW']), 1, 0, "R");
    $pdf->Cell($w['nw'],     7, num2($row['netW']), 1, 0, "R");
    $pdf->Cell($w['ga'],     7, num0($row['grossA']), 1, 0, "R");
    $pdf->Cell($w['na'],     7, num0($row['netA']), 1, 0, "R");
    $pdf->Cell($w['pur'],    7, num2($row['purity']), 1, 0, "R");
    $pdf->Cell($w['rate'],   7, num0($row['rate']), 1, 0, "R");
    $pdf->Cell($w['margin'], 7, num2($row['margin']), 1, 1, "R");

    $sn++;
    $t_gw     += (float)$row['grossW'];
    $t_nw     += (float)$row['netW'];
    $t_ga     += (float)$row['grossA'];
    $t_na     += (float)$row['netA'];
    $t_margin += (float)$row['margin'];
}

// ---------- Totals row (only Packets, Gross Wt, Net Wt) ----------
$pdf->SetFont('Times','B',10);
$pdf->SetFillColor(235,239,255);

$packets = $sn - 1; // rows printed

// First merged cell shows packets
$pdf->Cell($w['sno'] + $w['bill'] + $w['date'], 7, enc("Packets : ").$packets, 1, 0, "C", true);

// Then show totals for GW and NW
$pdf->Cell($w['gw'], 7, num2($t_gw), 1, 0, "R", true);
$pdf->Cell($w['nw'], 7, num2($t_nw), 1, 0, "R", true);

// Fill the rest of the row with a single empty cell spanning remaining columns
$remainingWidth = $w['ga'] + $w['na'] + $w['pur'] + $w['rate'] + $w['margin'];
$pdf->Cell($remainingWidth, 7, '', 1, 1, "R", true);

// ---- Build Place & Time ----
$placeStr = ($branchData['branchName'] ?? '') . ' (' . $branchId . ')';
// 24h: 'H:i'  | 12h: 'h:i A'
$timeStr  = date('H:i');

// ---- Layout helpers for footer ----
$labelW = 22;   // width for labels like "Tare Weight", "Place", etc.
$colonW = 4;    // width for the colon cell
$valW   = 60;   // width for short values on the right side
$gapW   = 10;   // small spacer between left and right groups

$pdf->Ln(8);

// Row 1: Tare Weight  |  Place
$pdf->Cell($labelW, 8, enc('Tare Weight'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),           0, 0, 'C');
$pdf->Cell(40,      8, enc(num2($tareWeight) . ' g'), 0, 0, 'L');

$pdf->Cell($gapW,   8, '', 0, 0); // spacer

$pdf->Cell($labelW, 8, enc('Place'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),     0, 0, 'C');
$pdf->Cell($valW,   8, enc($placeStr), 0, 1, 'L');

// Row 2: BM           |  Time
$pdf->Cell($labelW, 8, enc('BM'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),  0, 0, 'C');
$pdf->Cell(40,      8, enc(''),   0, 0, 'L');  // fill if you have BM name

$pdf->Cell($gapW,   8, '', 0, 0); // spacer

$pdf->Cell($labelW, 8, enc('Time'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),    0, 0, 'C');
$pdf->Cell($valW,   8, enc($timeStr), 0, 1, 'L');

// Row 3: ABM          |  Carrier
$pdf->Cell($labelW, 8, enc('ABM'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),   0, 0, 'C');
$pdf->Cell(40,      8, enc(''),    0, 0, 'L');  // fill if you have ABM

$pdf->Cell($gapW,   8, '', 0, 0); // spacer

$pdf->Cell($labelW, 8, enc('Carrier'), 0, 0, 'L');
$pdf->Cell($colonW, 8, enc(':'),       0, 0, 'C');
$pdf->Cell($valW,   8, enc(''),        0, 1, 'L');

$filename = "GoldSendReport_Selected_".$branchId."_".date('Ymd_His').".pdf";
$pdf->Output($filename, 'I');
?>
