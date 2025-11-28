<?php
// website_leads_details.php
error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json; charset=utf-8');
session_start();
date_default_timezone_set('Asia/Kolkata');

require_once('dbConnection.php'); // $con
@mysqli_set_charset($con, 'utf8mb4');

/* ---------- Helpers ---------- */
function out_json($x){ echo json_encode($x, JSON_UNESCAPED_UNICODE); exit; }
function err($m){ http_response_code(400); out_json(['error'=>$m]); }
function ok($rows){ out_json(['rows'=>$rows]); }
function phone_norm_sql($col){
  return "TRIM(LEADING '0' FROM
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM($col),' ',''),'-',''),'+',''),'(',''),')',''),'.',''),CHAR(13),''),CHAR(10),'')
          )";
}
function col_exists($con, $db, $tbl, $col){
  $q = mysqli_query($con, "SHOW COLUMNS FROM `$db`.`$tbl` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
}
function pick_col($con, $db, $tbl, $cands){
  foreach ($cands as $c) if (col_exists($con,$db,$tbl,$c)) return $c;
  return null;
}

/* ---------- Inputs ---------- */
$from = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : date('Y-m-d');
$to   = (isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))   ? $_GET['to']   : $from;
if (strtotime($from) > strtotime($to)) { $t=$from; $from=$to; $to=$t; }
$FROM0    = $from . ' 00:00:00';
$TO_NEXT0 = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00';

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$q      = isset($_GET['q']) ? trim($_GET['q']) : '';

/* ---------- Table + column detection ---------- */
$enqDb='atticaaws'; $enqTbl='enquiry';
$trDb ='atticaaws'; $trTbl ='trans';
$wkDb ='atticaaws'; $wkTbl ='walkin';

/* Enquiry columns (to show in UI) */
$wantCols = ['id','name','mobile','type','state','date','time','status','remarks','comments','updateDate','followup','device'];
$has = [];
foreach ($wantCols as $c){ $has[$c] = col_exists($con,$enqDb,$enqTbl,$c); }

/* Choose date col for enquiry filter */
$enqDateCol = $has['date'] ? 'date' : pick_col($con,$enqDb,$enqTbl, ['created_at','created_datetime','enquiry_date','datetime','createdon','created_dt','updateDate']);
if (!$enqDateCol) err('No date column on enquiry');

/* Phone columns for joins */
$enqPhoneCol = pick_col($con,$enqDb,$enqTbl, ['contact','mobile','phone','customer_contact','cust_phone']);
$trPhoneCol  = pick_col($con,$trDb,$trTbl,   ['contact','mobile','phone','customer_contact','cust_phone']);
$wkPhoneCol  = pick_col($con,$wkDb,$wkTbl,   ['contact','mobile','phone','customer_contact','cust_phone']);
if (!$enqPhoneCol) err('No phone column on enquiry');

/* Remarks + status columns */
$enqStatusCol  = $has['status']  ? 'status'  : pick_col($con,$enqDb,$enqTbl, ['status','enquiry_status']);
$enqRemarksCol = $has['remarks'] ? 'remarks' : pick_col($con,$enqDb,$enqTbl, ['remarks','remark','comments','comment','notes','note']);

/* Walkin/trans dates */
$trDateCol = pick_col($con,$trDb,$trTbl, ['date','created_at','created_datetime','datetime','approved_date']);
$wkDateCol = pick_col($con,$wkDb,$wkTbl, ['date','created_at','created_datetime','datetime','walkin_date']);

/* ---------- WHERE for enquiry ---------- */
$where = ["e.`$enqDateCol` >= '$FROM0' AND e.`$enqDateCol` < '$TO_NEXT0'"];
if ($status !== '' && $enqStatusCol) {
  $esc = mysqli_real_escape_string($con, $status);
  $where[] = "LOWER(e.`$enqStatusCol`) = LOWER('$esc')";
}
if ($q !== '') {
  $qesc = mysqli_real_escape_string($con, $q);
  $ors = [];
  if ($has['name'])     $ors[] = "e.`name` LIKE '%$qesc%'";
  if ($has['mobile'])   $ors[] = "e.`mobile` LIKE '%$qesc%'";
  if ($has['remarks'])  $ors[] = "e.`remarks` LIKE '%$qesc%'";
  if ($has['comments']) $ors[] = "e.`comments` LIKE '%$qesc%'";
  if (!empty($ors)) $where[] = '(' . implode(' OR ', $ors) . ')';
}
$where_sql = implode(' AND ', $where);

/* ---------- Build SELECT for visible columns ---------- */
$selCols = [];
foreach ($wantCols as $c) {
  $selCols[] = $has[$c] ? "e.`$c`" : "CAST(NULL AS CHAR) AS `$c`";
}
$sel = implode(", ", $selCols);

/* ---------- Temp tables ---------- */
mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_wl_enq");
mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_wl_bill");
mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_wl_walk");
mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_wl_plan");

$pn_enq = phone_norm_sql("e.`$enqPhoneCol`");

/* 1) Base list of enquiries to return */
$sqlBase = "
  CREATE TEMPORARY TABLE tmp_wl_enq AS
  SELECT DISTINCT $pn_enq AS phone, $sel
  FROM `$enqDb`.`$enqTbl` e
  WHERE e.`$enqPhoneCol` IS NOT NULL AND e.`$enqPhoneCol` <> ''
    AND $where_sql
";
if (!mysqli_query($con, $sqlBase)) err('Failed to build base list');

/* 2) Billed via TRANS — grams from trans.grossW */
if ($trPhoneCol && $trDateCol) {
  $pn_tr = phone_norm_sql("t.`$trPhoneCol`");
  $sqlBill = "
    CREATE TEMPORARY TABLE tmp_wl_bill AS
    SELECT $pn_tr AS phone,
           SUM(CAST(REPLACE(t.grossW, ',', '') AS DECIMAL(12,3))) AS grossw
    FROM `$trDb`.`$trTbl` t
    JOIN tmp_wl_enq e ON e.phone = $pn_tr
    WHERE t.`$trDateCol` >= '$FROM0' AND t.`$trDateCol` < '$TO_NEXT0'
      AND t.status='Approved'
      AND LOWER(t.metal)='gold'
      AND t.`$trPhoneCol` IS NOT NULL AND t.`$trPhoneCol` <> ''
      AND COALESCE(t.grossW,'') <> '' AND REPLACE(t.grossW, ',', '') <> '0'
    GROUP BY $pn_tr
  ";
  mysqli_query($con, $sqlBill);
} else {
  mysqli_query($con, "CREATE TEMPORARY TABLE tmp_wl_bill AS SELECT '' AS phone, 0.0 AS grossw LIMIT 0");
}

/* 3) Enquiry via WALKIN — grams from walkin.gwt */
if ($wkPhoneCol && $wkDateCol) {
  $wkGwtCol = col_exists($con,$wkDb,$wkTbl,'gwt') ? 'gwt' : 'gms'; // fallback if schema differs
  $pn_wk = phone_norm_sql("w.`$wkPhoneCol`");
  $sqlWalk = "
    CREATE TEMPORARY TABLE tmp_wl_walk AS
    SELECT
      $pn_wk AS phone,
      SUM(CAST(REPLACE(w.`$wkGwtCol`, ',', '') AS DECIMAL(12,3))) AS enq_gw
    FROM `$wkDb`.`$wkTbl` w
    JOIN tmp_wl_enq e ON e.phone = $pn_wk
    WHERE w.`$wkDateCol` >= '$FROM0' AND w.`$wkDateCol` < '$TO_NEXT0'
      AND w.`$wkPhoneCol` IS NOT NULL AND w.`$wkPhoneCol` <> ''
      AND COALESCE(w.`$wkGwtCol`,'') <> '' AND REPLACE(w.`$wkGwtCol`, ',', '') <> '0'
      AND COALESCE(w.issue,'') <> 'Rejected'
    GROUP BY $pn_wk
  ";
  mysqli_query($con, $sqlWalk);
} else {
  mysqli_query($con, "CREATE TEMPORARY TABLE tmp_wl_walk AS SELECT '' AS phone, 0.0 AS enq_gw LIMIT 0");
}

/* 4) Planning To Visit from enquiry remarks */
if ($enqRemarksCol) {
  mysqli_query($con, "
    CREATE TEMPORARY TABLE tmp_wl_plan AS
    SELECT DISTINCT $pn_enq AS phone
    FROM `$enqDb`.`$enqTbl` e
    JOIN tmp_wl_enq base ON base.phone = $pn_enq
    WHERE e.`$enqDateCol` >= '$FROM0' AND e.`$enqDateCol` < '$TO_NEXT0'
      AND LOWER(e.`$enqRemarksCol`) LIKE '%planning to visit%'
  ");
} else {
  mysqli_query($con, "CREATE TEMPORARY TABLE tmp_wl_plan AS SELECT '' AS phone LIMIT 0");
}

/* ---------- Final detail with classification + correct grams ---------- */
$sqlFinal = "
  SELECT
    b.phone,
    base.*,
    CASE
      WHEN b.phone IS NOT NULL THEN 'Billed'
      WHEN w.phone IS NOT NULL THEN 'Enquiry'
      WHEN p.phone IS NOT NULL THEN 'Pending Visit'
      ELSE 'No Show'
    END AS matched,
    ROUND(
      CASE
        WHEN b.phone IS NOT NULL THEN COALESCE(b.grossw, 0)
        WHEN w.phone IS NOT NULL THEN COALESCE(w.enq_gw, 0)
        ELSE 0
      END, 2
    ) AS grossw
  FROM tmp_wl_enq base
  LEFT JOIN tmp_wl_bill b  ON b.phone = base.phone   -- TRANS grams
  LEFT JOIN tmp_wl_walk w  ON w.phone = base.phone   -- WALKIN grams
  LEFT JOIN tmp_wl_plan p  ON p.phone = base.phone
  ORDER BY
    CASE
      WHEN b.phone IS NOT NULL THEN 1
      WHEN w.phone IS NOT NULL THEN 2
      WHEN p.phone IS NOT NULL THEN 3
      ELSE 4
    END, base.phone
  LIMIT 1000
";
$res = mysqli_query($con, $sqlFinal);
if (!$res) err('Failed to fetch details');

/* ---------- Emit JSON ---------- */
$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
  $rows[] = [
    'id'         => $r['id'] ?? '',
    'name'       => $r['name'] ?? '',
    'mobile'     => $r['mobile'] ?? '',
    'type'       => $r['type'] ?? '',
    'state'      => $r['state'] ?? '',
    'date'       => $r['date'] ?? '',
    'time'       => $r['time'] ?? '',
    'status'     => $r['status'] ?? '',
    'remarks'    => $r['remarks'] ?? '',
    'comments'   => $r['comments'] ?? '',
    'updateDate' => $r['updateDate'] ?? '',
    'followup'   => $r['followup'] ?? '',
    'device'     => $r['device'] ?? '',
    'matched'    => $r['matched'] ?? 'No Show',
    'grossw'     => (string) number_format((float)($r['grossw'] ?? 0), 2, '.', ''), // string for UI
  ];
}
ok($rows);
