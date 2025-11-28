<?php
include("dbConnection.php");

/**
 * Build the WHERE fragment for a branch/region filter using a table alias.
 * Produces a clause like:
 *   AND t.branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')
 */
function buildStateClauseFor($branchId, $alias) {
  switch($branchId){
    case "All Branches": return '';
    case "Bangalore"   : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')";
    case "Karnataka"   : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')";
    case "Chennai"     : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE city='Chennai')";
    case "Tamilnadu"   : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')";
    case "Hyderabad"   : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')";
    case "AP-TS"       : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')";
    case "Pondicherry" : return " AND {$alias}.branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')";
    default            : return " AND {$alias}.branchId='".mysqli_real_escape_string($GLOBALS['con'], $branchId)."'";
  }
}

if ($_POST['type'] == 'Monthly') {
  $months   = max(1, (int)$_POST['months']);
  $to       = date('Y-m-d');
  $from     = date("Y-m-01", strtotime(date('Y-m-01')." -$months months")); // same logic you had
  $branchId = $_POST['branchId'];

  $stateT = buildStateClauseFor($branchId, 't');
  $stateW = buildStateClauseFor($branchId, 'w');

  // TRANS (GrossW)
  $sqlT = "
    SELECT DATE_FORMAT(t.date, '%Y-%m') AS ym,
           ROUND(SUM(t.grossW)) AS grossW
    FROM trans t
    WHERE t.date BETWEEN '$from' AND '$to'
      AND t.status='Approved' AND t.metal='Gold'
      $stateT
    GROUP BY DATE_FORMAT(t.date, '%Y-%m')
  ";
  $resT = mysqli_query($con, $sqlT);
  $map = []; // ym => ['grossW'=>..., 'enquiry'=>...]

  if ($resT) {
    while ($r = mysqli_fetch_assoc($resT)) {
      $ym = $r['ym'];
      $map[$ym]['grossW']  = (float)$r['grossW'];
      if (!isset($map[$ym]['enquiry'])) $map[$ym]['enquiry'] = 0.0;
    }
    mysqli_free_result($resT);
  }

  // WALKIN (Enquiry GrossW = sum(gwt), metal=Gold, issue!=Rejected)
  $sqlW = "
    SELECT DATE_FORMAT(w.date, '%Y-%m') AS ym,
           ROUND(SUM(w.gwt)) AS enquiry
    FROM walkin w
    WHERE w.date BETWEEN '$from' AND '$to'
      AND w.metal='Gold' AND COALESCE(w.issue,'') <> 'Rejected'
      $stateW
    GROUP BY DATE_FORMAT(w.date, '%Y-%m')
  ";
  $resW = mysqli_query($con, $sqlW);
  if ($resW) {
    while ($r = mysqli_fetch_assoc($resW)) {
      $ym = $r['ym'];
      $map[$ym]['enquiry'] = (float)$r['enquiry'];
      if (!isset($map[$ym]['grossW'])) $map[$ym]['grossW'] = 0.0;
    }
    mysqli_free_result($resW);
  }

  // Build output in chronological order
  ksort($map);
  $out = [];
  foreach ($map as $ym => $vals) {
    $ts     = strtotime($ym.'-01');
    // Label style similar to your original (Month on first line, Year on second)
    $label  = date('M', $ts)."\n".date('Y', $ts);
    $grossW = isset($vals['grossW']) ? (float)$vals['grossW'] : 0.0;
    $enq    = isset($vals['enquiry']) ? (float)$vals['enquiry'] : 0.0;
    $out[]  = [$label, $grossW, $enq];
  }

  echo json_encode($out);
  exit;
}

if ($_POST['type'] == 'Daily') {
  $days     = max(1, (int)$_POST['days']);
  $to       = date('Y-m-d');
  $from     = date("Y-m-d", strtotime("-$days days")); // use last N days window
  $branchId = $_POST['branchId'];

  $stateT = buildStateClauseFor($branchId, 't');
  $stateW = buildStateClauseFor($branchId, 'w');

  // TRANS (GrossW + Rate)
  $sqlT = "
    SELECT t.date AS d,
           ROUND(SUM(t.grossW)) AS grossW,
           MAX(t.rate) AS rate
    FROM trans t
    WHERE t.date BETWEEN '$from' AND '$to'
      AND t.status='Approved' AND t.metal='Gold'
      $stateT
    GROUP BY t.date
  ";
  $resT = mysqli_query($con, $sqlT);
  $map = []; // d => ['grossW'=>..., 'rate'=>..., 'enquiry'=>...]

  if ($resT) {
    while ($r = mysqli_fetch_assoc($resT)) {
      $d = $r['d'];
      $map[$d]['grossW']  = (float)$r['grossW'];
      $map[$d]['rate']    = isset($r['rate']) ? (float)$r['rate'] : 0.0;
      if (!isset($map[$d]['enquiry'])) $map[$d]['enquiry'] = 0.0;
    }
    mysqli_free_result($resT);
  }

  // WALKIN (Enquiry GrossW = sum(gwt))
  $sqlW = "
    SELECT w.date AS d,
           ROUND(SUM(w.gwt)) AS enquiry
    FROM walkin w
    WHERE w.date BETWEEN '$from' AND '$to'
      AND w.metal='Gold' AND COALESCE(w.issue,'') <> 'Rejected'
      $stateW
    GROUP BY w.date
  ";
  $resW = mysqli_query($con, $sqlW);
  if ($resW) {
    while ($r = mysqli_fetch_assoc($resW)) {
      $d = $r['d'];
      $map[$d]['enquiry'] = (float)$r['enquiry'];
      if (!isset($map[$d]['grossW'])) $map[$d]['grossW'] = 0.0;
      if (!isset($map[$d]['rate']))   $map[$d]['rate']   = 0.0;
    }
    mysqli_free_result($resW);
  }

  // Build output sorted by date
  ksort($map);
  $out = [];
  foreach ($map as $d => $vals) {
    $label = date('d-m-Y', strtotime($d));
    $grossW = isset($vals['grossW']) ? (float)$vals['grossW'] : 0.0;
    $rate   = isset($vals['rate'])   ? (float)$vals['rate']   : 0.0;
    $enq    = isset($vals['enquiry'])? (float)$vals['enquiry']: 0.0;
    $out[]  = [$label, $grossW, $rate, $enq];
  }

  echo json_encode($out);
  exit;
}

// unknown request
echo json_encode([]);

