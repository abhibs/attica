<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
date_default_timezone_set('Asia/Kolkata');

/* ---------- Page chrome ---------- */
$type = isset($_SESSION['usertype']) ? $_SESSION['usertype'] : '';
if ($type == 'Zonal' || $type == 'Master' || $type == 'BD') {
  include('header.php');
  if ($type == 'Zonal') include('menuZonal.php');
  elseif ($type == 'BD') include('menubd.php');
  else include('menumaster.php');
} else {
  include('logout.php'); exit;
}

include('dbConnection.php'); // $con
@mysqli_set_charset($con, 'utf8mb4');

/* ---------- Date range (default = yesterday) ---------- */
$yesterday = date('Y-m-d', strtotime('-1 day'));
$fromDate = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : $yesterday;
$toDate   = (isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))   ? $_GET['to']   : $yesterday;
if (strtotime($fromDate) > strtotime($toDate)) { $tmp=$fromDate; $fromDate=$toDate; $toDate=$tmp; }
$FROM0    = $fromDate . ' 00:00:00';
$TO_NEXT0 = date('Y-m-d', strtotime($toDate . ' +1 day')) . ' 00:00:00';

/* ---------- Preserve filters (recordings now have their own dates) ---------- */
$rec_user  = isset($_GET['rec_user'])  ? trim($_GET['rec_user'])  : '';
$rec_phone = isset($_GET['rec_phone']) ? trim($_GET['rec_phone']) : '';
$esc_user  = mysqli_real_escape_string($con, $rec_user);
$esc_phone = mysqli_real_escape_string($con, $rec_phone);

$rec_from  = (isset($_GET['rec_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['rec_from'])) ? $_GET['rec_from'] : '';
$rec_to    = (isset($_GET['rec_to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['rec_to']))   ? $_GET['rec_to']   : '';
if ($rec_from && $rec_to && strtotime($rec_from) > strtotime($rec_to)) { $tmp=$rec_from; $rec_from=$rec_to; $rec_to=$tmp; }

/* ---------- Helpers ---------- */
define('REC_URL_BASE', '/recordings');
define('REC_FS_BASE',  '/var/www/html/recordings');

// Normalize phone (remove spaces/()+-., CR/LF, and leading zeros)
function phone_norm_sql($col){
  return "TRIM(LEADING '0' FROM
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM($col),' ',''),'-',''),'+',''),'(',''),')',''),'.',''),CHAR(13),''),CHAR(10),'')
          )";
}

function ensure_wav($name) {
  $n = trim((string)$name);
  if ($n === '') return '';
  if (preg_match('/-all\.wav$/i', $n)) return $n;
  $n = preg_replace('/\.wav$/i', '', $n);
  return $n . '-all.wav';
}

function parse_grossw($extra){
  $extra = trim((string)$extra);
  if ($extra === '') return 0.0;
  $gw = 0.0;
  $arr = json_decode($extra, true);
  if (is_array($arr) && isset($arr['GrossW'])) {
    $gw = (float)preg_replace('/[^\d.]+/', '', (string)$arr['GrossW']);
  } else {
    if (preg_match('/"GrossW"\s*:\s*"?([\d.]+)/i', $extra, $m)) $gw = (float)$m[1];
  }
  return $gw;
}


/* which EC column is phone */
$ecCol = 'contact';
$hasCol = function($c) use ($con) {
  $q = mysqli_query($con, "SHOW COLUMNS FROM `atticaaws`.`everycustomer` LIKE '$c'");
  return $q && mysqli_num_rows($q) > 0;
};
if     (!$hasCol('contact') &&  $hasCol('mobile')) $ecCol = 'mobile';
elseif (!$hasCol('contact') && !$hasCol('mobile') && $hasCol('phone'))  $ecCol = 'phone';

/* ---------- Language counts (unique mobiles in range) ---------- */
/* ---------- Language counts (unique mobiles in range) ---------- */
$langNone = 0; $langKannada = $langTelugu = $langTamil = $langEnglish = $langHindi = 0;
$qLang = mysqli_query($con, "
  SELECT
    COUNT(DISTINCT CASE WHEN (language IS NULL OR TRIM(language)='') THEN ".phone_norm_sql('mobile')." END) AS no_lang,
    COUNT(DISTINCT CASE WHEN LOWER(language)='kannada' THEN ".phone_norm_sql('mobile')." END) AS kannada,
    COUNT(DISTINCT CASE WHEN LOWER(language)='telugu'  THEN ".phone_norm_sql('mobile')." END) AS telugu,
    COUNT(DISTINCT CASE WHEN LOWER(language)='tamil'   THEN ".phone_norm_sql('mobile')." END) AS tamil,
    COUNT(DISTINCT CASE WHEN LOWER(language)='hindi'   THEN ".phone_norm_sql('mobile')." END) AS hindi,
    COUNT(DISTINCT CASE WHEN LOWER(language)='english' THEN ".phone_norm_sql('mobile')." END) AS english
  FROM `alpha_attica`.`cust_info`
  WHERE mobile IS NOT NULL AND mobile <> ''
    AND created_datetime >= '$FROM0'
    AND created_datetime <  '$TO_NEXT0'
");
if ($qLang) {
  $rowL = mysqli_fetch_assoc($qLang);
  $langNone    = (int)($rowL['no_lang']  ?? 0);
  $langKannada = (int)($rowL['kannada']  ?? 0);
  $langTelugu  = (int)($rowL['telugu']   ?? 0);
  $langTamil   = (int)($rowL['tamil']    ?? 0);
  $langHindi   = (int)($rowL['hindi']    ?? 0);
  $langEnglish = (int)($rowL['english']  ?? 0);
}


function recording_fs_fullpath($fname) {
  if (!$fname || strlen($fname) < 8) return '';
  $fname = ensure_wav($fname);
  $y = substr($fname,0,4); $m = substr($fname,4,2); $d = substr($fname,6,2);
  if (!ctype_digit($y.$m.$d)) return '';
  $dir = "$y-$m-$d";
  return REC_FS_BASE . "/$dir/$fname";
}
function recording_http_href($fname) {
  if (!$fname || strlen($fname) < 8) return '';
  $fname = ensure_wav($fname);
  $y = substr($fname,0,4); $m = substr($fname,4,2); $d = substr($fname,6,2);
  if (!ctype_digit($y.$m.$d)) return '';
  $dir = "$y-$m-$d";
  return REC_URL_BASE . "/$dir/" . rawurlencode($fname);
}
function fmt_hms($secs) {
  $s = max(0, (int)$secs);
  $h = floor($s/3600);
  $m = floor(($s%3600)/60);
  $s = $s%60;
  return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

/* ---------- KPIs (range) ---------- */

// Total calls (NOT unique) from cust_info (kept for legacy tiles)
$totIncoming = 0;
if ($r = mysqli_query(
  $con,
  "SELECT COUNT(*) AS c
   FROM `alpha_attica`.`cust_info`
   WHERE mobile IS NOT NULL AND mobile <> '' AND customer_name != ''
     AND created_datetime >= '$FROM0'
     AND created_datetime <  '$TO_NEXT0'"
)) { $row = mysqli_fetch_assoc($r); $totIncoming = (int)$row['c']; }

// Missed calls (robust parsing; no DATE() wrapper)
$totMissed = 0;
$sqlMissed = "
  SELECT COUNT(*) AS c
  FROM (
    SELECT COALESCE(
      STR_TO_DATE(call_date, '%Y-%m-%d %H:%i:%s'),
      STR_TO_DATE(call_date, '%Y-%m-%d'),
      STR_TO_DATE(call_date, '%d-%m-%Y %H:%i:%s'),
      STR_TO_DATE(call_date, '%d-%m-%Y'),
      STR_TO_DATE(call_date, '%d/%m/%Y %H:%i:%s'),
      STR_TO_DATE(call_date, '%d/%m/%Y')
    ) AS dt
    FROM `alpha_attica`.`missed_calls`
    WHERE COALESCE(dial_status,'') <> 'Pending'
  ) t
  WHERE t.dt >= '$FROM0' AND t.dt < '$TO_NEXT0'
";
if ($r = mysqli_query($con, $sqlMissed)) {
  $totMissed = (int)mysqli_fetch_assoc($r)['c'];
}

// TOTAL CALLS from asterisk.recording_log (index friendly; NO DATE())
$total_records = 0;
$rlHas = function($col) use ($con) {
  $q = mysqli_query($con, "SHOW COLUMNS FROM `asterisk`.`recording_log` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
};
$rlDateCol = null;
if     ($rlHas('start_time')) $rlDateCol = 'start_time';
elseif ($rlHas('start'))      $rlDateCol = 'start';
elseif ($rlHas('calldate'))   $rlDateCol = 'calldate';
elseif ($rlHas('created_at')) $rlDateCol = 'created_at';
if ($rlDateCol) {
  $sqlTR = "
    SELECT COUNT(*) AS total_records
    FROM `asterisk`.`recording_log`
    WHERE `$rlDateCol` >= '$FROM0' AND `$rlDateCol` < '$TO_NEXT0'
  ";
  if ($qr = mysqli_query($con, $sqlTR)) {
    $total_records = (int)mysqli_fetch_assoc($qr)['total_records'];
  }
}

// TOTAL UNIQUE CALLS (unique normalized mobiles) from cust_info
$totalCallsAll = 0;
if ($q = mysqli_query(
  $con,
  "SELECT COUNT(DISTINCT " . phone_norm_sql('mobile') . ") AS c
   FROM `alpha_attica`.`cust_info`
   WHERE mobile IS NOT NULL AND mobile <> ''
     AND created_datetime >= '$FROM0'
     AND created_datetime <  '$TO_NEXT0'"
)) { $totalCallsAll = (int)mysqli_fetch_assoc($q)['c']; }

/* ---------- Agent list using VICIDIAL_LOG (agents numeric-only) ---------- */
$pn_vl_phone   = phone_norm_sql('vl.phone_number');
$pn_ci_mobile  = phone_norm_sql('ci.mobile');
$pn_ec_col     = phone_norm_sql("ec.`$ecCol`");

$agentsRange = mysqli_query(
  $con,
  "
WITH v AS (
  /* calls per agent-phone within date range from vicidial_log; numeric agents only */
  SELECT TRIM(vl.user) AS agent,
         $pn_vl_phone  AS phone,
         SUM(COALESCE(vl.called_count,0)) AS calls_to_phone
  FROM `asterisk`.`vicidial_log` vl
  WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
    AND vl.user REGEXP '^[0-9]+$'
    AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
  GROUP BY TRIM(vl.user), $pn_vl_phone
),
u_agent AS (
  SELECT agent,
         COUNT(DISTINCT phone) AS unique_phones,
         SUM(calls_to_phone)   AS calls_made
  FROM v
  GROUP BY agent
),
/* EC statuses/grams only for ec.date >= fromDate (as requested) */
ecb AS (
  SELECT
    $pn_ec_col AS phone,
    MAX(LOWER(ec.status) IN ('billed','release')) AS is_billed,
    MAX(LOWER(ec.status) = 'enquiry')             AS is_enquiry,
    SUM(
      CASE WHEN LOWER(ec.status) IN ('billed','release')
           THEN IFNULL(CAST(JSON_UNQUOTE(JSON_EXTRACT(ec.extra,'$.GrossW')) AS DECIMAL(12,3)), 0)
           ELSE 0
      END
    ) AS gw_sum
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
  GROUP BY $pn_ec_col
),
/* CI enquiry flag in range */
ci_enq AS (
  SELECT
    $pn_ci_mobile AS phone,
    MAX(LOWER(ci.follow_up_no_status)='enquery') AS ci_enquiry
  FROM `alpha_attica`.`cust_info` ci
  WHERE ci.created_datetime >= '$FROM0'
    AND ci.created_datetime <  '$TO_NEXT0'
  GROUP BY $pn_ci_mobile
)
SELECT
  ua.agent AS agent_id,
  ua.unique_phones,
  ua.calls_made,
  SUM(CASE WHEN COALESCE(ecb.is_billed,0)=1 THEN 1 ELSE 0 END) AS billed_cnt,
  SUM(CASE WHEN COALESCE(ecb.is_enquiry,0)=1 OR COALESCE(ci_enq.ci_enquiry,0)=1 THEN 1 ELSE 0 END) AS enquiry_cnt,
  ROUND(SUM(COALESCE(ecb.gw_sum,0)), 3) AS billed_gw
FROM v
JOIN u_agent ua ON ua.agent = v.agent
LEFT JOIN ecb    ON ecb.phone    = v.phone
LEFT JOIN ci_enq ON ci_enq.phone = v.phone
GROUP BY ua.agent, ua.unique_phones, ua.calls_made
ORDER BY ua.agent
"
);

/* ---------- Phones universe (for range-based unique customer metrics) ---------- */
$phonesRangeUnion = "
  SELECT ".phone_norm_sql('vl.phone_number')." AS phone
  FROM `asterisk`.`vicidial_log` vl
  WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
    AND vl.user REGEXP '^[0-9]+$'
    AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
  GROUP BY ".phone_norm_sql('vl.phone_number')."
";

/* ---------- Unique billed/enquiry customers + grams (EC date >= fromDate) ---------- */
$prevBRCount   = 0;
$prevBRGrossW  = 0.0;
$seenBR        = [];

$qBRPhones = mysqli_query($con, "
  SELECT ".phone_norm_sql("ec.`$ecCol`")." AS phone, ec.extra
  FROM `atticaaws`.`everycustomer` ec
  JOIN ( $phonesRangeUnion ) u
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
");
if ($qBRPhones){
  while ($r = mysqli_fetch_assoc($qBRPhones)) {
    $p = $r['phone'];
    if ($p === '' || isset($seenBR[$p])) continue;
    $seenBR[$p] = true;
    $prevBRCount++;
    $prevBRGrossW += parse_grossw($r['extra'] ?? '');
  }
}

$prevEnqCount  = 0;
$prevEnqGrossW = 0.0;
$seenENQ       = [];

$qENQPhones = mysqli_query($con, "
  SELECT ".phone_norm_sql("ec.`$ecCol`")." AS phone, ec.extra
  FROM `atticaaws`.`everycustomer` ec
  JOIN ( $phonesRangeUnion ) u
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'enquiry'
");
if ($qENQPhones){
  while ($r = mysqli_fetch_assoc($qENQPhones)) {
    $p = $r['phone'];
    if ($p === '' || isset($seenENQ[$p])) continue;
    $seenENQ[$p] = true;
    $prevEnqCount++;
    $prevEnqGrossW += parse_grossw($r['extra'] ?? '');
  }
}

/* ---------- Dashboard COGs (denominator = total unique calls) ---------- */
$den = max(0, (int)$totalCallsAll);
$conversionCogPct = ($den > 0) ? (($prevBRCount + $prevEnqCount) * 100.0 / $den) : 0.0;
$billedCogPct     = ($den > 0) ? ( $prevBRCount               * 100.0 / $den) : 0.0;
$denUnique = (int)$totalCallsAll;
/* ---------- Unique billed/enquiry counts using EC >= fromDate ---------- */
$totBilledRelease = 0;
if ($qBR = mysqli_query($con, "
  SELECT COUNT(DISTINCT u.phone) AS c
  FROM ( $phonesRangeUnion ) u
  JOIN `atticaaws`.`everycustomer` ec
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
")) { $totBilledRelease = (int)mysqli_fetch_assoc($qBR)['c']; }

$totEnquiryPhones = 0;
if ($qENQ = mysqli_query($con, "
  SELECT COUNT(DISTINCT u.phone) AS c
  FROM ( $phonesRangeUnion ) u
  JOIN `atticaaws`.`everycustomer` ec
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'enquiry'
")) { $totEnquiryPhones = (int)mysqli_fetch_assoc($qENQ)['c']; }

/* ---------- EC row counts and grams (>= fromDate) ---------- */
$totBilledReleaseCount = 0;
$sumGrossWBR = 0.0;

if ($q = mysqli_query($con, "
  SELECT COUNT(*) AS c
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
")) { $totBilledReleaseCount = (int)mysqli_fetch_assoc($q)['c']; }

if ($q = mysqli_query($con, "
  SELECT ec.extra
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
")) {
  while ($r = mysqli_fetch_assoc($q)) {
    $gw = 0.0;
    $extra = trim((string)($r['extra'] ?? ''));
    if ($extra !== '') {
      $arr = json_decode($extra, true);
      if (is_array($arr) && isset($arr['GrossW'])) {
        $gw = (float)preg_replace('/[^\d.]+/', '', (string)$arr['GrossW']);
      } elseif (preg_match('/"GrossW"\s*:\s*"?([\d.]+)/i', $extra, $m)) {
        $gw = (float)$m[1];
      }
    }
    $sumGrossWBR += $gw;
  }
}

/* ---------- Interested (hot/warm) ---------- */
$interestSum = 0;
if ($q = mysqli_query($con, "
  SELECT COUNT(*) AS c
  FROM `alpha_attica`.`cust_info` ci
  WHERE ci.created_datetime >= '$FROM0'
    AND ci.created_datetime <  '$TO_NEXT0'
    AND LOWER(ci.lead) IN ('hot','warm')
")) { $interestSum = (int)mysqli_fetch_assoc($q)['c']; }


/* ---------- Dropped Calls ---------- */
$droppedCount = 0;
if ($qDrop = mysqli_query($con, "
  SELECT COUNT(*) AS c
  FROM (
    SELECT DISTINCT ".phone_norm_sql('ci.mobile')." AS phone
    FROM `alpha_attica`.`cust_info` ci
    WHERE ci.mobile IS NOT NULL AND ci.mobile <> ''
      AND ci.created_datetime >= '$FROM0' AND ci.created_datetime < '$TO_NEXT0'
  ) m
  LEFT JOIN `alpha_attica`.`cust_info_submit` s
    ON ".phone_norm_sql('s.phone')." = m.phone
   AND s.created_datetime >= '$FROM0' AND s.created_datetime < '$TO_NEXT0'
  WHERE s.phone IS NULL
")) { $droppedCount = (int)mysqli_fetch_assoc($qDrop)['c']; }

/* ---------- Recordings (Asterisk) ---------- */
$ASTERISK_DB = 'asterisk';
$REC_TABLE   = 'recording_log';
$recHas = function($col) use ($con, $ASTERISK_DB, $REC_TABLE) {
  $q = mysqli_query($con, "SHOW COLUMNS FROM `{$ASTERISK_DB}`.`{$REC_TABLE}` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
};
$dateCol = null;
if     ($recHas('calldate'))   $dateCol = 'calldate';
elseif ($recHas('start'))      $dateCol = 'start';
elseif ($recHas('created_at')) $dateCol = 'created_at';
$userCol = null;
if     ($recHas('user'))        $userCol = 'user';
elseif ($recHas('agent'))       $userCol = 'agent';
elseif ($recHas('accountcode')) $userCol = 'accountcode';
$srcCol  = $recHas('src') ? 'src' : ($recHas('callerid') ? 'callerid' : null);
$dstCol  = $recHas('dst') ? 'dst' : null;
$fileCol = $recHas('filename') ? 'filename' : ($recHas('recordingfile') ? 'recordingfile' : 'filename');
$lenCol  = null;
if     ($recHas('length_in_sec')) $lenCol = 'length_in_sec';
elseif ($recHas('duration'))      $lenCol = 'duration';

$recResults = [];
$didRunRecordings = false;

if ($rec_user !== '' || $rec_phone !== '' || ($rec_from && $rec_to)) {
  $didRunRecordings = true;
  $recWhere = "1=1";
  if ($rec_from && $rec_to) {
    if ($dateCol) {
      $recWhere .= " AND DATE(`$dateCol`) BETWEEN '$rec_from' AND '$rec_to'";
    } else {
      $yyyymmdd_from = str_replace('-', '', $rec_from);
      $yyyymmdd_to   = str_replace('-', '', $rec_to);
      $recWhere .= " AND LEFT(`$fileCol`,8) BETWEEN '$yyyymmdd_from' AND '$yyyymmdd_to'";
    }
  }
  if ($userCol && $rec_user !== '') $recWhere .= " AND `$userCol` LIKE '%$esc_user%'";
  if ($rec_phone !== '') {
    $ors = [];
    if ($srcCol) $ors[] = "`$srcCol` LIKE '%$esc_phone%'";
    if ($dstCol) $ors[] = "`$dstCol` LIKE '%$esc_phone%'";
    $ors[] = "`$fileCol` LIKE '%$esc_phone%'";
    $recWhere .= " AND (" . implode(" OR ", $ors) . ")";
  }
  $selParts = [];
  if ($dateCol) {
    $selParts[] = "`$dateCol` AS rec_date";
  } else {
    $selParts[] = "CASE WHEN LENGTH(`$fileCol`)>=15
                      THEN CONCAT(SUBSTRING(`$fileCol`,1,4),'-',SUBSTRING(`$fileCol`,5,2),'-',SUBSTRING(`$fileCol`,7,2),' ',SUBSTRING(`$fileCol`,10,2),':',SUBSTRING(`$fileCol`,12,2),':',SUBSTRING(`$fileCol`,14,2))
                      ELSE NULL END AS rec_date";
  }
  if ($userCol) $selParts[] = "`$userCol` AS rec_user";
  if ($lenCol)  $selParts[] = "`$lenCol`  AS len_sec";
  $selParts[] = "`$fileCol` AS fname";
  $sel = implode(", ", $selParts);
  $orderCol = $dateCol ? "`$dateCol`" : "`$fileCol`";

  $qRec = mysqli_query($con, "
    SELECT $sel
    FROM `{$ASTERISK_DB}`.`{$REC_TABLE}`
    WHERE $recWhere
    ORDER BY $orderCol DESC
    LIMIT 300
  ");
  if ($qRec) while ($r = mysqli_fetch_assoc($qRec)) { $recResults[] = $r; }
}

/* ---------- Enquiry count (EC >= fromDate) ---------- */
$totEnquiryCountEC = 0;
if ($q = mysqli_query($con, "
  SELECT COUNT(*) AS c
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'enquiry'
")) { $totEnquiryCountEC = (int)mysqli_fetch_assoc($q)['c']; }

/* ---------- COGs based on cust_info totals ---------- */
$numerConv = (int)$totBilledRelease + (int)$totEnquiryPhones;
$numerBill = (int)$totBilledRelease;

$conversionCogPct = $denUnique > 0 ? ($numerConv * 100.0 / $denUnique) : 0.0;
$billedCogPct     = $denUnique > 0 ? ($numerBill * 100.0 / $denUnique) : 0.0;

/* ---------- Build extremes from agentsRange ---------- */
$convHi = $convLo = $billedHi = $billedLo = null;

/* Highest/Lowest Calls (sum of called_count) + their unique-phone counts */
$hiCallsMade = $loCallsMade = null;
$hiCallsAgent = $loCallsAgent = '-';
$hiUniquePhones = $loUniquePhones = null;


if ($agentsRange && mysqli_num_rows($agentsRange) > 0) {
  // We'll also reuse the result set for the table, so buffer rows
  $agentRows = [];
  while ($a = mysqli_fetch_assoc($agentsRange)) {
    $aid       = trim($a['agent_id'] ?? '');
    if ($aid === '') continue;
    $uniq      = (int)($a['unique_phones'] ?? 0);
    $callsMade = (int)($a['calls_made'] ?? 0);
    $billed    = (int)($a['billed_cnt'] ?? 0);
    $enquiry   = (int)($a['enquiry_cnt'] ?? 0);
    $billedGW  = (float)($a['billed_gw'] ?? 0);

    $convPct   = $uniq > 0 ? (($billed + $enquiry) / $uniq) * 100 : 0.0;
    $billedPct = $uniq > 0 ? ($billed / $uniq) * 100 : 0.0;

    $row = [
      'agent_id'      => $aid,
      'unique_phones' => $uniq,
      'calls_made'    => $callsMade,
      'billed_cnt'    => $billed,
      'enquiry_cnt'   => $enquiry,
      'billed_gw'     => $billedGW,
      'conv_pct'      => $convPct,
      'billed_pct'    => $billedPct,
    ];
    $agentRows[] = $row;

    if ($convHi === null || $row['conv_pct']   > $convHi['conv_pct'])   $convHi   = $row;
    if ($convLo === null || $row['conv_pct']   < $convLo['conv_pct'])   $convLo   = $row;
    if ($billedHi=== null || $row['billed_pct']> $billedHi['billed_pct'])$billedHi = $row;
    if ($billedLo=== null || $row['billed_pct']< $billedLo['billed_pct'])$billedLo = $row;

    if ($hiCallsMade === null || $callsMade > $hiCallsMade) {
      $hiCallsMade   = $callsMade;
      $hiCallsAgent  = $aid;
      $hiUniquePhones = $uniq;   // track unique phones for the top caller
    }
    if ($loCallsMade === null || $callsMade < $loCallsMade) {
      $loCallsMade   = $callsMade;
      $loCallsAgent  = $aid;
      $loUniquePhones = $uniq;   // track unique phones for the bottom caller
    }
    
  }
  // Rebuild a memory result set for the table
  $agentsRangeMem = $agentRows;
} else {
  $agentsRangeMem = [];
}


/* ---------- Pending Visit (derived) ---------- */
/* ---------- Pending Visit from remarks_info (date filter = remarks_date_time) ---------- */
$pendingVisitAll = 0;

$pendingSql = "
  SELECT COUNT(*) AS c
  FROM (
    SELECT DISTINCT " . phone_norm_sql('ci.mobile') . " AS phone
    FROM `alpha_attica`.`remarks_info` r
    JOIN `alpha_attica`.`cust_info`    ci ON ci.customer_id = r.customer_id
    WHERE r.remarks IS NOT NULL AND r.remarks <> ''
      AND r.remarks_date_time >= '$FROM0'
      AND r.remarks_date_time <  '$TO_NEXT0'
      /* match 'coming to office' with flexible spaces/punct or just 'coming...' */
      AND (
            LOWER(r.remarks) REGEXP 'coming[[:space:][:punct:]]*to[[:space:][:punct:]]*office'
            OR  LOWER(r.remarks) REGEXP '\\bcoming\\b'
          )
      /* exclude negatives like 'not coming' */
      AND LOWER(r.remarks) NOT REGEXP 'not[[:space:][:punct:]]*coming'
      AND ci.mobile IS NOT NULL AND ci.mobile <> ''
  ) AS u
  WHERE u.phone <> ''
";
if ($qr = mysqli_query($con, $pendingSql)) {
  $pendingVisitAll = (int)mysqli_fetch_assoc($qr)['c'];
}

/* ---------- Unique Billed vs Release (EC >= fromDate; universe = phonesRangeUnion) ---------- */
$uniqBilled  = 0;
$uniqRelease = 0;

if ($qb = mysqli_query($con, "
  SELECT COUNT(DISTINCT u.phone) AS c
  FROM ( $phonesRangeUnion ) u
  JOIN `atticaaws`.`everycustomer` ec
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'billed'
")) { $uniqBilled = (int)mysqli_fetch_assoc($qb)['c']; }

if ($qr = mysqli_query($con, "
  SELECT COUNT(DISTINCT u.phone) AS c
  FROM ( $phonesRangeUnion ) u
  JOIN `atticaaws`.`everycustomer` ec
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'release'
")) { $uniqRelease = (int)mysqli_fetch_assoc($qr)['c']; }

?>
<style type="text/css">
  .hpanel{margin-bottom:5px;border-radius:10px;box-shadow:5px 5px 5px #999;}
  #wrapper .panel-body{background:#f5f5f5;border-radius:10px 10px 0 0;padding:20px;}
  .text-success{color:#123C69;text-transform:uppercase;font-size:20px;}
  .stats-label{text-transform:uppercase;font-size:10px;}
  .panel-footer{border-radius:0 0 10px 10px;text-align:center;}
  .panel-footer>b{color:#990000;}
  .fa{color:#990000;}
  .stats-icon>.fa{margin-right:10px;}
  .table>thead>tr>th{white-space:nowrap;}
  .muted{color:#777;font-size:11px;}
  #agentDetailsModal .modal-dialog{ width: 96vw; max-width: 1600px; }
  #agentDetailsModal .table{ table-layout: fixed; }
  #agentDetailsModal th, #agentDetailsModal td{ white-space: normal; word-break: break-word; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row" style="margin-bottom:10px;">
      <div class="col-lg-12" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-50">
            <form method="get" class="form-inline">
              <div class="form-group" style="margin-right:10px;">
                <label>From</label>
                <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($fromDate); ?>">
              </div>
              <div class="form-group" style="margin-right:10px;">
                <label>To</label>
                <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($toDate); ?>">
              </div>

              <input type="hidden" name="rec_user"  value="<?php echo htmlspecialchars($rec_user); ?>">
              <input type="hidden" name="rec_phone" value="<?php echo htmlspecialchars($rec_phone); ?>">

              <button type="submit" class="btn btn-primary">Apply</button>
              <a class="btn btn-default" href="<?php echo htmlspecialchars(strtok($_SERVER['REQUEST_URI'],'?')); ?>">Reset (Yesterday)</a>
              <span class="muted" style="margin-left:10px;">
                Showing: <?php echo htmlspecialchars($fromDate); ?> → <?php echo htmlspecialchars($toDate); ?>
              </span>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">

      <!-- Total Calls -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Total Calls</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-call fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($total_records); ?></h3>
              <span class="font-bold no-margins">Total calls (recording_log)</span>
              <h3 class="font-bold text-success"><?php echo number_format($totalCallsAll); ?></h3>
              <span class="font-bold no-margins">Total unique calls (cust_info)</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Missed Calls -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Missed Calls</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-close fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($totMissed); ?></h3>
              <span class="font-bold no-margins">Overall MissedCalls</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Billed / Release -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Billed / Release</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-cash fa-4x"></i></div>
            <div class="m-t-xl">
  <h3 class="font-bold text-success"><?php echo number_format($prevBRCount); ?></h3>

  <div style="margin-top:8px; line-height:1.4;">
    <span class="font-bold no-margins" style="display:block; font-size:13px;">
      Physical : <b><?php echo number_format($uniqBilled); ?></b>
    </span>
    <span class="font-bold no-margins" style="display:block; font-size:13px;">
      Release : <b><?php echo number_format($uniqRelease); ?></b>
    </span>
  </div>

  <div style="margin-top:6px;">
    <span class="font-bold no-margins">GrossW: <b><?php echo number_format($prevBRGrossW, 2); ?></b></span>
  </div>
</div>

          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Enquiry -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Enquiry</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-help1 fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($prevEnqCount); ?></h3>
              <span class="font-bold no-margins">Customers (unique)</span><br>
              <span class="font-bold no-margins">GrossW: <b><?php echo number_format($prevEnqGrossW, 2); ?></b></span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Shown Interest -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Shown Interest</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-like fa-4x"></i></div>
            <div class="m-t-xl">
  <h3 class="font-bold text-success"><?php echo number_format($interestSum); ?></h3>
  <span class="font-bold no-margins">Hot + Warm leads (all agents)</span>
  <br>
  <h3 class="font-bold text-success"><?php echo number_format($pendingVisitAll); ?></h3>
  <span class="font-bold no-margins">Pending Visit</span>
</div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Conversion COG -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Conversion COG</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-graph2 fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($conversionCogPct, 2); ?>%</h3>
              <span class="font-bold no-margins">(Billed + Enquiry) ÷ Total Unique Calls</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Billed COG -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Billed COG</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-graph1 fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($billedCogPct, 2); ?>%</h3>
              <span class="font-bold no-margins">Billed ÷ Total Unique Calls</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Highest/Lowest COG tiles -->
      <div class="col-lg-3" style="padding:5px">
        <div class="hpanel stats"><div class="panel-body h-200">
          <div class="stats-title pull-left"><h4 style="font-weight:900">Top Conversion COG</h4></div>
          <div class="stats-icon pull-right"><i class="pe-7s-graph2 fa-4x"></i></div>
          <div class="m-t-xl">
            <h3 class="font-bold text-success"><?php echo $convHi ? number_format($convHi['conv_pct'],2).'%' : '-'; ?></h3>
            <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($convHi['agent_id'] ?? '-'); ?></span><br>
            <span class="muted">Unique: <?php echo number_format($convHi['unique_phones'] ?? 0); ?> | Calls: <?php echo number_format($convHi['calls_made'] ?? 0); ?> | Billed: <?php echo number_format($convHi['billed_cnt'] ?? 0); ?> | Enquiry: <?php echo number_format($convHi['enquiry_cnt'] ?? 0); ?></span>
          </div>
        </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
      </div>

      <div class="col-lg-3" style="padding:5px">
        <div class="hpanel stats"><div class="panel-body h-200">
          <div class="stats-title pull-left"><h4 style="font-weight:900">Lowest Conversion COG</h4></div>
          <div class="stats-icon pull-right"><i class="pe-7s-graph1 fa-4x"></i></div>
          <div class="m-t-xl">
            <h3 class="font-bold text-success"><?php echo $convLo ? number_format($convLo['conv_pct'],2).'%' : '-'; ?></h3>
            <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($convLo['agent_id'] ?? '-'); ?></span><br>
            <span class="muted">Unique: <?php echo number_format($convLo['unique_phones'] ?? 0); ?> | Calls: <?php echo number_format($convLo['calls_made'] ?? 0); ?> | Billed: <?php echo number_format($convLo['billed_cnt'] ?? 0); ?> | Enquiry: <?php echo number_format($convLo['enquiry_cnt'] ?? 0); ?></span>
          </div>
        </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
      </div>

      <div class="col-lg-3" style="padding:5px">
        <div class="hpanel stats"><div class="panel-body h-200">
          <div class="stats-title pull-left"><h4 style="font-weight:900">Top Billed COG</h4></div>
          <div class="stats-icon pull-right"><i class="pe-7s-graph2 fa-4x"></i></div>
          <div class="m-t-xl">
            <h3 class="font-bold text-success"><?php echo $billedHi ? number_format($billedHi['billed_pct'],2).'%' : '-'; ?></h3>
            <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($billedHi['agent_id'] ?? '-'); ?></span><br>
            <span class="muted">Unique: <?php echo number_format($billedHi['unique_phones'] ?? 0); ?> | Calls: <?php echo number_format($billedHi['calls_made'] ?? 0); ?> | Billed: <?php echo number_format($billedHi['billed_cnt'] ?? 0); ?></span>
          </div>
        </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
      </div>

      <div class="col-lg-3" style="padding:5px">
        <div class="hpanel stats"><div class="panel-body h-200">
          <div class="stats-title pull-left"><h4 style="font-weight:900">Lowest Billed COG</h4></div>
          <div class="stats-icon pull-right"><i class="pe-7s-graph1 fa-4x"></i></div>
          <div class="m-t-xl">
            <h3 class="font-bold text-success"><?php echo $billedLo ? number_format($billedLo['billed_pct'],2).'%' : '-'; ?></h3>
            <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($billedLo['agent_id'] ?? '-'); ?></span><br>
            <span class="muted">Unique: <?php echo number_format($billedLo['unique_phones'] ?? 0); ?> | Calls: <?php echo number_format($billedLo['calls_made'] ?? 0); ?> | Billed: <?php echo number_format($billedLo['billed_cnt'] ?? 0); ?></span>
          </div>
        </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
      </div>
      <!-- Highest/Lowest Calls (from vicidial_log called_count sums) -->
      <div class="col-lg-3" style="padding: 5px">
  <div class="hpanel stats"><div class="panel-body h-200">
    <div class="stats-title pull-left"><h4 style="font-weight:900">Highest Calls (Made)</h4></div>
    <div class="stats-icon pull-right"><i class="pe-7s-call fa-4x"></i></div>
    <div class="m-t-xl">
      <h3 class="font-bold text-success"><?php echo ($hiCallsMade===null ? '-' : number_format($hiCallsMade)); ?></h3>
      <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($hiCallsAgent); ?></span><br>
      <span class="muted">Unique phones: <?php echo ($hiUniquePhones===null ? '-' : number_format($hiUniquePhones)); ?></span>
    </div>
  </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
</div>

<div class="col-lg-3" style="padding: 5px">
  <div class="hpanel stats"><div class="panel-body h-200">
    <div class="stats-title pull-left"><h4 style="font-weight:900">Lowest Calls (Made)</h4></div>
    <div class="stats-icon pull-right"><i class="pe-7s-call fa-4x"></i></div>
    <div class="m-t-xl">
      <h3 class="font-bold text-success"><?php echo ($loCallsMade===null ? '-' : number_format($loCallsMade)); ?></h3>
      <span class="font-bold no-margins">Agent: <?php echo htmlspecialchars($loCallsAgent); ?></span><br>
      <span class="muted">Unique phones: <?php echo ($loUniquePhones===null ? '-' : number_format($loUniquePhones)); ?></span>
    </div>
  </div><div class="panel-footer"><b>Attica Call Center</b></div></div>
</div>
      <!-- Dropped Calls -->
      <div class="col-lg-3" style="padding: 5px">
        <div class="hpanel stats">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Dropped Calls</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-attention fa-4x"></i></div>
            <div class="m-t-xl">
              <h3 class="font-bold text-success"><?php echo number_format($droppedCount); ?></h3>
              <span class="font-bold no-margins">Total dropped calls not unique</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Language Split -->
      <div class="col-lg-6" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Language Mix (Unique Mobiles)</h4></div>
            <div class="clearfix"></div>
            <ul class="nav nav-tabs" style="margin-top:10px;">
  <li class="active"><a data-toggle="tab" href="#lang_none">No Language</a></li>
  <li><a data-toggle="tab" href="#lang_english">English</a></li>
  <li><a data-toggle="tab" href="#lang_hindi">Hindi</a></li>
  <li><a data-toggle="tab" href="#lang_kannada">Kannada</a></li>
  <li><a data-toggle="tab" href="#lang_telugu">Telugu</a></li>
  <li><a data-toggle="tab" href="#lang_tamil">Tamil</a></li>
</ul>
<div class="tab-content" style="padding-top:15px;">
  <div id="lang_none" class="tab-pane active">
    <h3 class="font-bold text-success"><?php echo number_format($langNone); ?></h3>
    <span class="font-bold no-margins">Total with no language</span>
  </div>
  <div id="lang_english" class="tab-pane">
    <h3 class="font-bold text-success"><?php echo number_format($langEnglish); ?></h3>
    <span class="font-bold no-margins">Total English Calls</span>
  </div>
  <div id="lang_hindi" class="tab-pane">
    <h3 class="font-bold text-success"><?php echo number_format($langHindi); ?></h3>
    <span class="font-bold no-margins">Total Hindi Calls</span>
  </div>
  <div id="lang_kannada" class="tab-pane">
    <h3 class="font-bold text-success"><?php echo number_format($langKannada); ?></h3>
    <span class="font-bold no-margins">Total Kannada Calls</span>
  </div>
  <div id="lang_telugu" class="tab-pane">
    <h3 class="font-bold text-success"><?php echo number_format($langTelugu); ?></h3>
    <span class="font-bold no-margins">Total Telugu Calls</span>
  </div>
  <div id="lang_tamil" class="tab-pane">
    <h3 class="font-bold text-success"><?php echo number_format($langTamil); ?></h3>
    <span class="font-bold no-margins">Total Tamil Calls</span>
  </div>
</div>

          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Agent Info -->
      <div class="col-lg-12" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-300">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Agent Info</h4></div>
            <div class="stats-icon pull-right"><i class="pe-7s-users fa-4x"></i></div>
            <br><br>
            <div class="m-t-md" style="max-height:240px;overflow:auto;">
              <table class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th style="text-align:center; width:60px;">#</th>
                    <th>Agent ID</th>
                    <th style="text-align:center; width:90px;">Conv COG</th>
                    <th style="text-align:center; width:90px;">Billed COG</th>
                    <th style="text-align:center; width:110px;">Unique Phones</th>
                    <th style="text-align:center; width:110px;">Calls (made)</th>
                    <th style="text-align:center; width:100px;">Billed</th>
                    <th style="text-align:center; width:110px;">Enquiry</th>
                    <th style="text-align:center; width:140px;">Billed GW (g)</th>
                    <th style="text-align:center; width:150px;">Details</th>
                    <th style="text-align:center; width:120px;">View</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                $i=1;
                if (!empty($agentsRangeMem)) {
                  foreach ($agentsRangeMem as $a) {
                    $aid      = trim($a['agent_id'] ?? '');
                    if ($aid==='') continue;
                    $callsU   = (int)($a['unique_phones'] ?? 0);
                    $callsM   = (int)($a['calls_made'] ?? 0);
                    $billed   = (int)($a['billed_cnt'] ?? 0);
                    $enquiry  = (int)($a['enquiry_cnt'] ?? 0);
                    $billedGW = (float)($a['billed_gw'] ?? 0.0);
                    $convPct   = $callsU > 0 ? (($billed + $enquiry) / $callsU) * 100 : 0;
                    $billedPct = $callsU > 0 ? ($billed / $callsU) * 100 : 0;
                    $aidDisp = htmlspecialchars($aid);
                    $aidAttr = htmlspecialchars($aid, ENT_QUOTES);

                    echo "<tr>";
                    echo "<td style='text-align:center;'>".($i++)."</td>";
                    echo "<td><a href=\"#\" class=\"agent-link\" data-agent=\"{$aidAttr}\">$aidDisp</a></td>";
                    echo "<td style='text-align:center;'>".number_format($convPct, 2)."&#37;</td>";
                    echo "<td style='text-align:center;'>".number_format($billedPct, 2)."&#37;</td>";
                    echo "<td style='text-align:center;'>".number_format($callsU)."</td>";
                    echo "<td style='text-align:center;'>".number_format($callsM)."</td>";
                    echo "<td style='text-align:center;'>".number_format($billed)."</td>";
                    echo "<td style='text-align:center;'>".number_format($enquiry)."</td>";
                    echo "<td style='text-align:center;'>".number_format($billedGW, 3)."</td>";
                    echo "<td style='text-align:center; white-space:nowrap;'>
                            <button class='btn btn-xs btn-info agent-details' data-agent='{$aidAttr}'>Agent Call details</button>
                          </td>";
                    echo "<td style='text-align:center; white-space:nowrap;'>
                            <button class='btn btn-xs btn-primary agent-link' data-agent='{$aidAttr}'>Agent Metrics</button>
                          </td>";
                    echo "</tr>";
                  }
                } else {
                  echo "<tr><td colspan='11' class='text-center'>No agents in range</td></tr>";
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="panel-footer"><b>Click an Agent ID to view metrics for the selected range</b></div>
        </div>
      </div>

      <!-- Agent Calls Details Modal -->
      <div class="modal fade" id="agentDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
          <div class="color-line"></div>
          <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
          <div class="modal-header">
            <h4 class="modal-title" id="agentDetailsTitle">Agent Calls — Details</h4>
            <div id="agentDetailsRange" class="muted"></div>
          </div>
          <div class="modal-body" style="padding:16px 20px;">
            <div class="table-responsive" style="max-height:70vh; overflow-y:auto; overflow-x:hidden;">
              <table class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Location</th>
                    <th>District</th>
                    <th>Language</th>
                    <th>Business Type</th>
                    <th>GMS</th>
                    <th>Assigned Branch</th>
                    <th>Follow Up Yes Date</th>
                    <th>Lead</th>
                  </tr>
                </thead>
                <tbody id="agentDetailsBody">
                  <tr><td colspan="11" class="text-center">Loading…</td></tr>
                </tbody>
              </table>
            </div>
            <div id="agentDetailsError" class="text-danger" style="display:none;"></div>
          </div>
        </div></div>
      </div>

      <!-- Callcenter Recordings -->
      <div class="col-lg-12" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Callcenter Recordings</h4></div>
            <br><br>
            <form method="get" class="form-inline" style="margin:10px 0 15px 0;">
              <div class="form-group" style="margin-right:10px;">
                <label>From</label>
                <input type="date" name="rec_from" class="form-control" value="<?php echo htmlspecialchars($rec_from); ?>">
              </div>
              <div class="form-group" style="margin-right:10px;">
                <label>To</label>
                <input type="date" name="rec_to" class="form-control" value="<?php echo htmlspecialchars($rec_to); ?>">
              </div>
              <div class="form-group" style="margin-right:10px;">
                <label>User</label>
                <input type="text" name="rec_user" class="form-control" value="<?php echo htmlspecialchars($rec_user); ?>" placeholder="agent ID">
              </div>
              <div class="form-group" style="margin-right:10px;">
                <label>Phone</label>
                <input type="text" name="rec_phone" class="form-control" value="<?php echo htmlspecialchars($rec_phone); ?>" placeholder="Phone Number">
              </div>
              <button type="submit" class="btn btn-primary">Search</button>
              <a href="<?php echo htmlspecialchars(strtok($_SERVER['REQUEST_URI'],'?')); ?>" class="btn btn-default">Reset</a>
            </form>

            <div class="m-t-md" style="max-height:360px; overflow:auto;">
              <table class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Length</th>
                    <th>File</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                if (!$didRunRecordings) {
                  echo "<tr><td colspan='5' class='text-center'>Select a recordings date range and/or enter User or Phone, then click <b>Search</b>.</td></tr>";
                } elseif (!empty($recResults)) {
                  $i = 1;
                  foreach ($recResults as $r) {
                    $dt   = isset($r['rec_date']) ? htmlspecialchars($r['rec_date']) : '';
                    $usr  = isset($r['rec_user']) ? htmlspecialchars($r['rec_user']) : '';
                    $lenS = isset($r['len_sec'])  ? (int)$r['len_sec'] : null;
                    $len  = $lenS !== null ? fmt_hms($lenS) : '-';

                    $fnRaw = isset($r['fname']) ? $r['fname'] : '';
                    $fnTxt = htmlspecialchars(ensure_wav($fnRaw));
                    $fs    = recording_fs_fullpath($fnRaw);
                    $href  = recording_http_href($fnRaw);
                    $fileExists = $fs ? is_file($fs) : false;

                    $link = ($fileExists)
                            ? "<a href=\"".htmlspecialchars($href)."\" target=\"_blank\">{$fnTxt}</a>"
                            : "<span class=\"text-muted\">No file found</span>";

                    echo "<tr>
                            <td>".($i++)."</td>
                            <td>$dt</td>
                            <td>$usr</td>
                            <td>$len</td>
                            <td>$link</td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='5' class='text-center'>No recordings found for the selected filters</td></tr>";
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>

      <!-- Agent Metrics Modal (unchanged UI) -->
      <div class="modal fade" id="agentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" style="width:520px;">
          <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header">
              <h4 class="modal-title" id="agentTitle">Agent Metrics (Selected Range)</h4>
            </div>
            <div class="modal-body" style="padding:20px 24px;">
              <div id="agentRange" class="muted" style="margin-bottom:8px;"></div>
              <table class="table table-bordered table-striped">
              <tbody>
                <tr><th>Calls Received (unique)</th><td id="m_calls">-</td></tr>
                <tr><th>Shown Interest (Hot/Warm Lead)</th><td id="m_interest">-</td></tr>
                <tr><th>Billed / Release</th><td id="m_billed">-</td></tr>
                <tr><th>Enquiry</th><td id="m_enquiry">-</td></tr>
                <tr><th>Pending Visit</th><td id="m_pending">-</td></tr>
                <tr><th>Not Visited</th><td id="m_notvisited">-</td></tr>
                <tr><th>Others</th><td id="m_others">-</td></tr> <!-- NEW -->
              </tbody>
              </table>
              <div id="agentError" class="text-danger" style="display:none;"></div>
            </div>
          </div>
        </div>
      </div>

      <?php include('footer.php'); ?>
    </div><!-- /.row -->
  </div><!-- /.content -->
</div><!-- /#wrapper -->

<script>
(function(){
  const FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  function openAgent(agent){
    document.getElementById('agentTitle').textContent = 'Agent Metrics — ' + agent;
    document.getElementById('agentRange').textContent = 'Range: ' + FROM + ' → ' + TO;

    ['m_calls','m_interest','m_billed','m_enquiry','m_pending','m_notvisited','m_others']  // + m_others
      .forEach(id => document.getElementById(id).textContent = 'Loading…');

    document.getElementById('agentError').style.display='none';
    $('#agentModal').modal('show');

    const url = 'agent_metrics.php?agent=' + encodeURIComponent(agent)
              + '&from=' + encodeURIComponent(FROM)
              + '&to='   + encodeURIComponent(TO);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error){
          const e = document.getElementById('agentError');
          e.textContent = data.error; e.style.display = 'block';
          ['m_calls','m_interest','m_billed','m_enquiry','m_pending','m_notvisited','m_others']
            .forEach(id => document.getElementById(id).textContent = '-');
          return;
        }

        const calls       = Number(data.calls_received ?? 0);
        const interest    = Number(data.interested ?? 0);
        const billed      = Number(data.billed_release ?? 0);
        const enquiry     = Number(data.enquiry ?? 0);
        const pending     = Number(data.pending_visit ?? 0);
        const notVisited  = Number(data.not_visited ?? 0);
        const others      = Math.max(0, calls - interest - billed - enquiry - pending - notVisited);

        document.getElementById('m_calls').textContent       = calls.toLocaleString();
        document.getElementById('m_interest').textContent    = interest.toLocaleString();
        document.getElementById('m_billed').textContent      = billed.toLocaleString();
        document.getElementById('m_enquiry').textContent     = enquiry.toLocaleString();
        document.getElementById('m_pending').textContent     = pending.toLocaleString();
        document.getElementById('m_notvisited').textContent  = notVisited.toLocaleString();
        document.getElementById('m_others').textContent      = others.toLocaleString();
      })
      .catch(() => {
        const e = document.getElementById('agentError');
        e.textContent = 'Failed to load metrics.'; e.style.display = 'block';
      });
  }

  document.querySelectorAll('.agent-link').forEach(el=>{
    el.addEventListener('click', function(e){
      e.preventDefault();
      const agent = this.getAttribute('data-agent');
      if (agent) openAgent(agent);
    });
  });
})();

(function(){
  const FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  function escHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  function openAgentDetails(agent){
    document.getElementById('agentDetailsTitle').textContent = 'Agent Calls — ' + agent;
    document.getElementById('agentDetailsRange').textContent = 'Range: ' + FROM + ' → ' + TO;
    const body = document.getElementById('agentDetailsBody');
    const err  = document.getElementById('agentDetailsError');
    body.innerHTML = '<tr><td colspan="11" class="text-center">Loading…</td></tr>';
    err.style.display = 'none'; err.textContent = '';

    $('#agentDetailsModal').modal('show');

    const url = 'agent_calls_details.php?agent=' + encodeURIComponent(agent)
              + '&from=' + encodeURIComponent(FROM)
              + '&to='   + encodeURIComponent(TO);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error){
          err.textContent = data.error; err.style.display = 'block';
          body.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (rows.length === 0){
          body.innerHTML = '<tr><td colspan="11" class="text-center">No records in this range</td></tr>';
          return;
        }
        let i = 1, html = '';
        rows.forEach(r => {
          html += '<tr>'
            + '<td>'+ (i++) +'</td>'
            + '<td>'+ escHtml(r.customer_name) +'</td>'
            + '<td>'+ escHtml(r.mobile) +'</td>'
            + '<td>'+ escHtml(r.location) +'</td>'
            + '<td>'+ escHtml(r.district) +'</td>'
            + '<td>'+ escHtml(r.language) +'</td>'
            + '<td>'+ escHtml(r.business_type) +'</td>'
            + '<td>'+ escHtml(r.gms) +'</td>'
            + '<td>'+ escHtml(r.assigned_to_agpl_branch) +'</td>'
            + '<td>'+ escHtml(r.follow_up_yes_date) +'</td>'
            + '<td>'+ escHtml(r.lead) +'</td>'
          + '</tr>';
        });
        body.innerHTML = html;
      })
      .catch(() => {
        err.textContent = 'Network error while fetching details.'; err.style.display = 'block';
        body.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Failed to load</td></tr>';
      });
  }

  document.querySelectorAll('.agent-details').forEach(el=>{
    el.addEventListener('click', function(e){
      e.preventDefault();
      const agent = this.getAttribute('data-agent');
      if (agent) openAgentDetails(agent);
    });
  });
})();
</script>
