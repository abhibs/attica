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
}
else if($type == 'SocialMedia'){
  include("header.php");
  include("menuSocialMedia.php");
}
else if($type == 'MIS-Team'){
  include("header.php");
  include("menumis.php");
}
else if ($type == 'AccHead') {
  include("header.php");
  include("menuaccHeadPage.php");
}
else { include('logout.php'); exit; }

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
$rec_user  = isset($_GET['rec_user']) ? trim($_GET['rec_user']) : '';
$rec_phone = isset($_GET['rec_phone']) ? trim($_GET['rec_phone']) : '';
$esc_user  = mysqli_real_escape_string($con, $rec_user);
$esc_phone = mysqli_real_escape_string($con, $rec_phone);

$rec_from  = (isset($_GET['rec_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['rec_from'])) ? $_GET['rec_from'] : '';
$rec_to    = (isset($_GET['rec_to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['rec_to']))   ? $_GET['rec_to']   : '';
if ($rec_from && $rec_to && strtotime($rec_from) > strtotime($rec_to)) { $tmp=$rec_from; $rec_from=$rec_to; $rec_to=$tmp; }

/* ---------- Helpers ---------- */
define('REC_URL_BASE', '/recordings');
define('REC_FS_BASE',  '/var/www/html/recordings');

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
  } else if (preg_match('/"GrossW"\s*:\s*"?([\d.]+)/i', $extra, $m)) {
    $gw = (float)$m[1];
  }
  return $gw;
}
//PT CAP
function pct_cap($v){
  $x = (float)$v;
  if ($x < 0) return 0.0;
  if ($x > 100) return 100.0;
  return $x;
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

// Total calls (NOT unique) from cust_info (legacy tile)
$totIncoming = 0;
if ($r = mysqli_query(
  $con,
  "SELECT COUNT(*) AS c
   FROM `alpha_attica`.`cust_info`
   WHERE mobile IS NOT NULL AND mobile <> '' AND customer_name != ''
     AND created_datetime >= '$FROM0'
     AND created_datetime <  '$TO_NEXT0'"
)) { $row = mysqli_fetch_assoc($r); $totIncoming = (int)$row['c']; }

// Missed calls (robust parsing)
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
if ($r = mysqli_query($con, $sqlMissed)) $totMissed = (int)mysqli_fetch_assoc($r)['c'];

// TOTAL CALLS from asterisk.recording_log
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
  if ($qr = mysqli_query($con, $sqlTR)) $total_records = (int)mysqli_fetch_assoc($qr)['total_records'];
}
// Which column in recording_log identifies the agent/user?
$rlUserCol = null;
foreach (['user','agent','accountcode'] as $c) {
  $q = mysqli_query($con, "SHOW COLUMNS FROM `asterisk`.`recording_log` LIKE '$c'");
  if ($q && mysqli_num_rows($q) > 0) { $rlUserCol = $c; break; }
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

/* ---------- Detect TRANS (for GW per agent) ---------- */
$trDb = 'atticaaws'; $trTbl = 'trans';
$colExistsTr = function($db,$tbl,$col) use ($con){
  $q = mysqli_query($con, "SHOW COLUMNS FROM `$db`.`$tbl` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
};
$pickFirstTr = function($db,$tbl,$cands) use ($colExistsTr){
  foreach ($cands as $c) if ($colExistsTr($db,$tbl,$c)) return $c;
  return null;
};
$trPhoneCol = $pickFirstTr($trDb,$trTbl, ['contact','mobile','phone','customer_contact','cust_phone']);
$trDateCol  = $pickFirstTr($trDb,$trTbl, ['date','approved_date','created_at','created_datetime','datetime']);
$pn_tr_col  = $trPhoneCol ? phone_norm_sql("t.`$trPhoneCol`") : null;

/* ---------- Agent list using VICIDIAL_LOG (agents numeric-only) ---------- */
$pn_vl_phone  = phone_norm_sql('vl.phone_number');
$pn_ci_mobile = phone_norm_sql('ci.mobile');
$pn_ec_col    = phone_norm_sql("ec.`$ecCol`");

// Build the dynamic pieces for recording_log (calls_made)
$rlDateExpr = $rlDateCol ? "`$rlDateCol`" : null;
$rlUserExpr = $rlUserCol ? "`$rlUserCol`" : null;

// If we can't read calls from recording_log (no cols), we’ll safely fall back to vicidial counts.
$callsMadeCTE = $rlDateExpr && $rlUserExpr ? "
  calls_rec AS (
    SELECT TRIM($rlUserExpr) AS agent,
           COUNT(*)          AS calls_made
    FROM `asterisk`.`recording_log`
    WHERE $rlDateExpr >= '$FROM0' AND $rlDateExpr < '$TO_NEXT0'
    GROUP BY TRIM($rlUserExpr)
  )
" : "
  calls_rec AS (
    SELECT agent, SUM(calls_to_phone) AS calls_made
    FROM v_vl
    GROUP BY agent
  )
";
/* ---------- Use alpha_attica.cust_info_submit for agent phones & counts ---------- */
$cisDb  = 'alpha_attica';
$cisTbl = 'cust_info_submit';

$cisColExists = function($col) use ($con, $cisDb, $cisTbl){
  $q = mysqli_query($con, "SHOW COLUMNS FROM `$cisDb`.`$cisTbl` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
};
$cisPick = function($cands) use ($cisColExists){
  foreach ($cands as $c) if ($cisColExists($c)) return $c;
  return null;
};

/* Likely column names in cust_info_submit (auto-detect gracefully) */
$cisPhoneCol = $cisPick(['phone','mobile','contact','customer_contact','cust_phone']);
$cisAgentCol = $cisPick(['agent_id','agent','user','created_by','handled_by','assigned_to']);
$cisDateCol  = $cisPick(['created_datetime','created_at','date','datetime']);

if (!$cisPhoneCol || !$cisAgentCol || !$cisDateCol){
  die("cust_info_submit is missing required columns (phone/agent/date).");
}

$pn_cis_phone = phone_norm_sql("cis.`$cisPhoneCol`");

$agentsRange = mysqli_query(
  $con,
  "
WITH
/* Base rows for the selected range coming from cust_info_submit */
cis_base AS (
  SELECT
    TRIM(cis.`$cisAgentCol`) AS agent,
    $pn_cis_phone            AS phone
  FROM `$cisDb`.`$cisTbl` cis
  WHERE cis.`$cisDateCol` >= '$FROM0'
    AND cis.`$cisDateCol` <  '$TO_NEXT0'
    AND cis.`$cisPhoneCol` IS NOT NULL AND TRIM(cis.`$cisPhoneCol`) <> ''
    AND TRIM(cis.`$cisAgentCol`) REGEXP '^[0-9]+$'   -- keep your numeric agent convention
),

/* All agents present in the range (from cust_info_submit) */
agents AS (
  SELECT DISTINCT agent FROM cis_base
),

/* Unique phones per agent (this powers both the tiles and table) */
u_unique AS (
  SELECT agent, COUNT(DISTINCT phone) AS unique_phones
  FROM cis_base
  GROUP BY agent
),

/* Calls (made) per agent = total rows in cust_info_submit for that agent in range */
calls_rec AS (
  SELECT agent, COUNT(*) AS calls_made
  FROM cis_base
  GROUP BY agent
),

/* ===== The rest of your sets remain the same (they key off phone) ===== */
bor_set AS (
  SELECT $pn_ec_col AS phone
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
  GROUP BY $pn_ec_col
),
billed_only_set AS (
  SELECT $pn_ec_col AS phone
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'billed'
  GROUP BY $pn_ec_col
),
enq_set AS (
  SELECT $pn_ec_col AS phone
  FROM `atticaaws`.`everycustomer` ec
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) = 'enquiry'
  GROUP BY $pn_ec_col
),

/* Same-day grams from TRANS (Approved, gold) keyed by phone (unchanged) */
gw_day AS (
  SELECT
    " . ($trPhoneCol ? phone_norm_sql("t.`$trPhoneCol`") : "''") . " AS phone,
    SUM(CAST(REPLACE(t.grossW, ',', '') AS DECIMAL(12,3))) AS gw
  FROM `atticaaws`.`trans` t
  WHERE t.`" . ($trDateCol ?: 'date') . "` >= '$FROM0' AND t.`" . ($trDateCol ?: 'date') . "` < '$TO_NEXT0'
    AND t.status = 'Approved'
    AND LOWER(t.metal) = 'gold'
    " . ($trPhoneCol ? "AND t.`$trPhoneCol` IS NOT NULL AND t.`$trPhoneCol` <> ''" : "AND 1=0") . "
  GROUP BY " . ($trPhoneCol ? phone_norm_sql("t.`$trPhoneCol`") : "phone") . "
),

/* The agent+phone universe now also comes directly from cis_base */
agent_phone_universe AS (
  SELECT agent, phone
  FROM cis_base
  GROUP BY agent, phone
)

/* Final rollup exactly like before */
SELECT
  a.agent                            AS agent_id,
  COALESCE(u.unique_phones, 0)       AS unique_phones,
  COALESCE(cr.calls_made,   0)       AS calls_made,
  SUM(CASE WHEN bor.phone    IS NOT NULL THEN 1 ELSE 0 END) AS billed_cnt,
  SUM(CASE WHEN enq.phone    IS NOT NULL THEN 1 ELSE 0 END) AS enquiry_cnt,
  ROUND(SUM(CASE WHEN b_only.phone IS NOT NULL THEN COALESCE(gw.gw,0) ELSE 0 END), 3) AS billed_gw
FROM agents a
LEFT JOIN u_unique             u      ON u.agent       = a.agent
LEFT JOIN calls_rec            cr     ON cr.agent      = a.agent
LEFT JOIN agent_phone_universe apu    ON apu.agent     = a.agent
LEFT JOIN bor_set              bor    ON bor.phone     = apu.phone
LEFT JOIN enq_set              enq    ON enq.phone     = apu.phone
LEFT JOIN billed_only_set      b_only ON b_only.phone  = apu.phone
LEFT JOIN gw_day               gw     ON gw.phone      = apu.phone
GROUP BY a.agent, u.unique_phones, cr.calls_made
ORDER BY a.agent
"
);


// $agentsRange = mysqli_query(
//   $con,
//   "
// WITH
// /* Phones each agent actually dialed (from vicidial_log) in the range */
// v_vl AS (
//   SELECT
//     TRIM(vl.user) AS agent,
//     $pn_vl_phone  AS phone,
//     COUNT(*)      AS calls_to_phone
//   FROM `asterisk`.`vicidial_log` vl
//   WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
//     AND vl.user REGEXP '^[0-9]+$'
//     AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
//   GROUP BY TRIM(vl.user), $pn_vl_phone
// ),

// /* All agents present in the range (from v_vl) */
// agents AS (
//   SELECT DISTINCT agent FROM v_vl
// ),

// /* Phones that landed in cust_info during the range (this powers the tile) */
// ci_day AS (
//   SELECT DISTINCT $pn_ci_mobile AS phone
//   FROM `alpha_attica`.`cust_info` ci
//   WHERE ci.mobile IS NOT NULL AND ci.mobile <> ''
//     AND ci.created_datetime >= '$FROM0'
//     AND ci.created_datetime <  '$TO_NEXT0'
// ),

// /* Unique phones per agent = intersection of that agent's v_vl phones with ci_day phones */
// u_unique AS (
//   SELECT v.agent, COUNT(DISTINCT v.phone) AS unique_phones
//   FROM v_vl v
//   JOIN ci_day c ON c.phone = v.phone
//   GROUP BY v.agent
// ),

// /* Calls (made) per agent from recording_log (same source as the Total Calls tile) */
// $callsMadeCTE,

// /* EC sets (>= fromDate) used for billed/release/enquiry flags — unchanged for counts */
// bor_set AS (
//   SELECT $pn_ec_col AS phone
//   FROM `atticaaws`.`everycustomer` ec
//   WHERE ec.`date` >= '$fromDate'
//     AND LOWER(ec.status) IN ('billed','release')
//   GROUP BY $pn_ec_col
// ),
// billed_only_set AS (
//   SELECT $pn_ec_col AS phone
//   FROM `atticaaws`.`everycustomer` ec
//   WHERE ec.`date` >= '$fromDate'
//     AND LOWER(ec.status) = 'billed'
//   GROUP BY $pn_ec_col
// ),
// enq_set AS (
//   SELECT $pn_ec_col AS phone
//   FROM `atticaaws`.`everycustomer` ec
//   WHERE ec.`date` >= '$fromDate'
//     AND LOWER(ec.status) = 'enquiry'
//   GROUP BY $pn_ec_col
// ),

// /* Same-day grams from TRANS (Approved, gold) keyed by phone (for billed-only GW) */
// gw_day AS (
//   SELECT
//     " . ($trPhoneCol ? phone_norm_sql("t.`$trPhoneCol`") : "''") . " AS phone,
//     SUM(CAST(REPLACE(t.grossW, ',', '') AS DECIMAL(12,3))) AS gw
//   FROM `atticaaws`.`trans` t
//   WHERE t.`" . ($trDateCol ?: 'date') . "` >= '$FROM0' AND t.`" . ($trDateCol ?: 'date') . "` < '$TO_NEXT0'
//     AND t.status = 'Approved'
//     AND LOWER(t.metal) = 'gold'
//     " . ($trPhoneCol ? "AND t.`$trPhoneCol` IS NOT NULL AND t.`$trPhoneCol` <> ''" : "AND 1=0") . "
//   GROUP BY " . ($trPhoneCol ? phone_norm_sql("t.`$trPhoneCol`") : "phone") . "
// ),

// /* Bring each agent's phone list along to be able to flag billed/release/enquiry and attach grams */
// agent_phone_universe AS (
//   SELECT agent, phone
//   FROM v_vl
//   GROUP BY agent, phone
// )

// SELECT
//   a.agent                            AS agent_id,
//   COALESCE(u.unique_phones, 0)       AS unique_phones,         -- from cust_info (same as tile logic)
//   COALESCE(cr.calls_made,   0)       AS calls_made,            -- from recording_log (same as tile logic)
//   SUM(CASE WHEN bor.phone    IS NOT NULL THEN 1 ELSE 0 END) AS billed_cnt,   -- billed/release (unchanged)
//   SUM(CASE WHEN enq.phone    IS NOT NULL THEN 1 ELSE 0 END) AS enquiry_cnt,
//   ROUND(SUM(CASE WHEN b_only.phone IS NOT NULL THEN COALESCE(gw.gw,0) ELSE 0 END), 3) AS billed_gw  -- billed-only grams
// FROM agents a
// LEFT JOIN u_unique             u     ON u.agent       = a.agent
// LEFT JOIN calls_rec            cr    ON cr.agent      = a.agent
// LEFT JOIN agent_phone_universe apu   ON apu.agent     = a.agent
// LEFT JOIN bor_set              bor   ON bor.phone     = apu.phone
// LEFT JOIN enq_set              enq   ON enq.phone     = apu.phone
// LEFT JOIN billed_only_set      b_only ON b_only.phone = apu.phone
// LEFT JOIN gw_day               gw    ON gw.phone      = apu.phone
// GROUP BY a.agent, u.unique_phones, cr.calls_made
// ORDER BY a.agent
// "
// );


/* ---------- Phones universe (for range-based unique customer metrics) ---------- */
$phonesRangeUnion = "
  SELECT ".phone_norm_sql('vl.phone_number')." AS phone
  FROM `asterisk`.`vicidial_log` vl
  WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
    AND vl.user REGEXP '^[0-9]+$'
    AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
  GROUP BY ".phone_norm_sql('vl.phone_number')."
";

/* ---------- Unique billed/enquiry customers + grams (tile logic) ---------- */
$prevBRCount   = 0;
$prevBRGrossW  = 0.0; // grams for the day (TRANS)
$seenBR        = [];

$qBRPhones = mysqli_query($con, "
  SELECT ".phone_norm_sql("ec.`$ecCol`")." AS phone
  FROM `atticaaws`.`everycustomer` ec
  JOIN ( $phonesRangeUnion ) u
    ON ".phone_norm_sql("ec.`$ecCol`")." = u.phone
  WHERE ec.`date` >= '$fromDate'
    AND LOWER(ec.status) IN ('billed','release')
");
if ($qBRPhones){
  while ($r = mysqli_fetch_assoc($qBRPhones)) {
    $p = trim($r['phone'] ?? '');
    if ($p === '' || isset($seenBR[$p])) continue;
    $seenBR[$p] = true;
    $prevBRCount++;
  }
}

/* Sum billed GW only for the day from TRANS */
if ($trPhoneCol && $trDateCol) {
  $pn_tr = phone_norm_sql("t.`$trPhoneCol`");
  $qGwDay = mysqli_query($con, "
    SELECT SUM(CAST(REPLACE(t.grossW, ',', '') AS DECIMAL(12,3))) AS gw
    FROM `{$trDb}`.`{$trTbl}` t
    JOIN ( $phonesRangeUnion ) u ON $pn_tr = u.phone
    WHERE t.`$trDateCol` >= '$FROM0' AND t.`$trDateCol` < '$TO_NEXT0'
      AND t.status = 'Approved'
      AND LOWER(t.metal) = 'gold'
      AND COALESCE(t.grossW,'') <> '' AND REPLACE(t.grossW, ',', '') <> '0'
  ");
  if ($qGwDay) $prevBRGrossW = (float) (mysqli_fetch_assoc($qGwDay)['gw'] ?? 0.0);
}

/* Enquiry counts (unchanged) */
$prevEnqCount  = 0; $prevEnqGrossW = 0.0; $seenENQ = [];
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

/* ---------- Dashboard COGs ---------- */
$denUnique        = max(0, (int)$totalCallsAll);
$conversionCogPct = pct_cap( ($denUnique > 0) ? (($prevBRCount + $prevEnqCount) * 100.0 / $denUnique) : 0.0 );
$billedCogPct     = pct_cap( ($denUnique > 0) ? ( $prevBRCount               * 100.0 / $denUnique) : 0.0 );


/* ---------- Unique Billed vs Release ---------- */
$uniqBilled  = 0; $uniqRelease = 0;
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
      AND (
            LOWER(r.remarks) REGEXP 'coming[[:space:][:punct:]]*to[[:space:][:punct:]]*office'
            OR  LOWER(r.remarks) REGEXP '\\bcoming\\b'
          )
      AND LOWER(r.remarks) NOT REGEXP 'not[[:space:][:punct:]]*coming'
      AND ci.mobile IS NOT NULL AND ci.mobile <> ''
  ) AS u
  WHERE u.phone <> ''
";
if ($qr = mysqli_query($con, $pendingSql)) {
  $pendingVisitAll = (int)mysqli_fetch_assoc($qr)['c'];
}

/* ================== WEBSITE LEADS + MATCHING with PENDING VISIT ================== */
/* Helpers to detect columns */
$colExists = function($db, $table, $col) use ($con) {
  $q = mysqli_query($con, "SHOW COLUMNS FROM `$db`.`$table` LIKE '$col'");
  return $q && mysqli_num_rows($q) > 0;
};
$pickFirstCol = function($db, $table, $cands) use ($colExists) {
  foreach ($cands as $c) if ($colExists($db,$table,$c)) return $c;
  return null;
};

/* Table/column detection */
$enqDb='atticaaws'; $enqTbl='enquiry';
$trDb ='atticaaws'; $trTbl ='trans';
$wkDb ='atticaaws'; $wkTbl ='walkin';

/* Phone columns (auto-detect) */
$enqPhoneCol = $pickFirstCol($enqDb,$enqTbl, ['contact','mobile','phone','customer_contact','cust_phone']);
$trPhoneCol  = $pickFirstCol($trDb, $trTbl,  ['contact','mobile','phone','customer_contact','cust_phone']);
$wkPhoneCol  = $pickFirstCol($wkDb, $wkTbl,  ['contact','mobile','phone','customer_contact','cust_phone']);

/* Date columns (auto-detect) */
$enqDateCol  = $pickFirstCol($enqDb,$enqTbl, ['created_at','created_datetime','date','enquiry_date','datetime','createdon','created_dt']);
$trDateCol   = $pickFirstCol($trDb, $trTbl,  ['date','created_at','created_datetime','datetime','approved_date']);
$wkDateCol   = $pickFirstCol($wkDb, $wkTbl,  ['date','created_at','created_datetime','datetime','walkin_date']);

/* Remarks/status columns */
$enqStatusCol  = $pickFirstCol($enqDb,$enqTbl, ['status','enquiry_status']);
$enqRemarksCol = $pickFirstCol($enqDb,$enqTbl, ['remarks','remark','comments','comment','notes','note']);

/* Defaults */
$websiteLeadUnique = 0;
$websiteLeadPendingCallStatus = 0;   // legacy: status='pending' (if present)
$websiteLeadPendingVisit = 0;        // NEW: remarks contains 'Planning To Visit'
$websiteLeadMatchCounts = ['Billed'=>0,'Enquiry'=>0,'Pending Visit'=>0,'No Show'=>0];
$websiteLeadMatchRows   = [];

if ($enqPhoneCol && $enqDateCol) {
  $pn_enq = phone_norm_sql("e.`$enqPhoneCol`");

  /* Temp tables */
  mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_enq_web");
  mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_bill_web");
  mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_walk_web");
  mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmp_plan_web");

  /* Website enquiry phones for the day */
  $sqlTmpEnq = "
    CREATE TEMPORARY TABLE tmp_enq_web AS
    SELECT DISTINCT $pn_enq AS phone
    FROM `$enqDb`.`$enqTbl` e
    WHERE e.`$enqPhoneCol` IS NOT NULL AND e.`$enqPhoneCol` <> ''
      AND e.`$enqDateCol` >= '$FROM0'
      AND e.`$enqDateCol` <  '$TO_NEXT0'
  ";
  mysqli_query($con, $sqlTmpEnq);

  /* Count unique leads */
  if ($ru = mysqli_query($con, "SELECT COUNT(*) AS c FROM tmp_enq_web")) {
    $websiteLeadUnique = (int)mysqli_fetch_assoc($ru)['c'];
  }

  /* Pending CALLS by status (keep if your UI still shows it) */
  if ($enqStatusCol) {
    $sqlPendingStatus = "
      SELECT COUNT(DISTINCT $pn_enq) AS c
      FROM `$enqDb`.`$enqTbl` e
      WHERE e.`$enqPhoneCol` IS NOT NULL AND e.`$enqPhoneCol` <> ''
        AND e.`$enqDateCol` >= '$FROM0' AND e.`$enqDateCol` < '$TO_NEXT0'
        AND LOWER(e.`$enqStatusCol`) = 'pending'
    ";
    if ($rp = mysqli_query($con,$sqlPendingStatus)) {
      $websiteLeadPendingCallStatus = (int)mysqli_fetch_assoc($rp)['c'];
    }
  }

  /* Pending VISIT (remarks contains 'Planning To Visit') */
  if ($enqRemarksCol) {
    $sqlTmpPlan = "
      CREATE TEMPORARY TABLE tmp_plan_web AS
      SELECT DISTINCT $pn_enq AS phone
      FROM `$enqDb`.`$enqTbl` e
      WHERE e.`$enqPhoneCol` IS NOT NULL AND e.`$enqPhoneCol` <> ''
        AND e.`$enqDateCol` >= '$FROM0' AND e.`$enqDateCol` < '$TO_NEXT0'
        AND LOWER(e.`$enqRemarksCol`) LIKE '%planning to visit%'
    ";
    mysqli_query($con, $sqlTmpPlan);

    if ($rc = mysqli_query($con, "SELECT COUNT(*) AS c FROM tmp_plan_web")) {
      $websiteLeadPendingVisit = (int)mysqli_fetch_assoc($rc)['c'];
    }
  } else {
    mysqli_query($con, "CREATE TEMPORARY TABLE tmp_plan_web AS SELECT '' AS phone LIMIT 0");
  }

  /* Billed via TRANS (Approved, gold, grossW>0) */
  if ($trPhoneCol && $trDateCol) {
    $pn_tr = phone_norm_sql("t.`$trPhoneCol`");
    $sqlTmpBill = "
      CREATE TEMPORARY TABLE tmp_bill_web AS
      SELECT DISTINCT $pn_tr AS phone
      FROM `$trDb`.`$trTbl` t
      WHERE t.`$trDateCol` >= '$FROM0' AND t.`$trDateCol` < '$TO_NEXT0'
        AND t.status = 'Approved'
        AND LOWER(t.metal) = 'gold'
        AND t.`$trPhoneCol` IS NOT NULL AND t.`$trPhoneCol` <> ''
        AND COALESCE(t.grossW,'') <> '' AND REPLACE(t.grossW, ',', '') <> '0'
    ";
    mysqli_query($con, $sqlTmpBill);
  } else {
    mysqli_query($con, "CREATE TEMPORARY TABLE tmp_bill_web AS SELECT '' AS phone LIMIT 0");
  }

  /* Enquiry via WALKIN (not Rejected, gwt>0) */
  if ($wkPhoneCol && $wkDateCol) {
    $pn_wk = phone_norm_sql("w.`$wkPhoneCol`");
    $sqlTmpWalk = "
      CREATE TEMPORARY TABLE tmp_walk_web AS
      SELECT DISTINCT $pn_wk AS phone
      FROM `$wkDb`.`$wkTbl` w
      WHERE w.`$wkDateCol` >= '$FROM0' AND w.`$wkDateCol` < '$TO_NEXT0'
        AND w.`$wkPhoneCol` IS NOT NULL AND w.`$wkPhoneCol` <> ''
        AND w.issue <> 'Rejected'
        AND COALESCE(w.gwt,'') <> '' AND REPLACE(w.gwt, ',', '') <> '0'
    ";
    mysqli_query($con, $sqlTmpWalk);
  } else {
    mysqli_query($con, "CREATE TEMPORARY TABLE tmp_walk_web AS SELECT '' AS phone LIMIT 0");
  }

  /* Classify with priority: Billed > Enquiry (walkin) > Pending Visit (remarks) > No Show */
  $sqlDetail = "
    SELECT e.phone,
           CASE
             WHEN b.phone IS NOT NULL THEN 'Billed'
             WHEN w.phone IS NOT NULL THEN 'Enquiry'
             WHEN p.phone IS NOT NULL THEN 'Pending Visit'
             ELSE 'No Show'
           END AS status
    FROM tmp_enq_web e
    LEFT JOIN tmp_bill_web b ON b.phone = e.phone
    LEFT JOIN tmp_walk_web w ON w.phone = e.phone
    LEFT JOIN tmp_plan_web p ON p.phone = e.phone
    ORDER BY status, e.phone
  ";
  if ($rd = mysqli_query($con, $sqlDetail)) {
    while ($r = mysqli_fetch_assoc($rd)) {
      $ph = trim($r['phone'] ?? '');
      $st = trim($r['status'] ?? 'No Show');
      if ($ph !== '') {
        $websiteLeadMatchRows[] = ['phone'=>$ph, 'status'=>$st];
        if (!isset($websiteLeadMatchCounts[$st])) $websiteLeadMatchCounts[$st] = 0;
        $websiteLeadMatchCounts[$st]++;
      }
    }
  }
}
/* ================== END WEBSITE LEADS + MATCHING ================== */
/* Total billed GrossWt from TRANS for website leads (Approved, gold, >0) */
$websiteLeadBilledGrossW = 0.0;
if ($trPhoneCol && $trDateCol) {
  $pn_tr_col = phone_norm_sql("t.`$trPhoneCol`"); // <-- use detected phone column
  $qGW = mysqli_query($con, "
    SELECT SUM(CAST(REPLACE(t.grossW, ',', '') AS DECIMAL(12,3))) AS gw
    FROM `$trDb`.`$trTbl` t
    JOIN tmp_enq_web e ON $pn_tr_col = e.phone
    WHERE t.`$trDateCol` >= '$FROM0' AND t.`$trDateCol` < '$TO_NEXT0'
      AND t.status = 'Approved'
      AND LOWER(t.metal) = 'gold'
      AND COALESCE(t.grossW,'') <> '' AND REPLACE(t.grossW, ',', '') <> '0'
  ");
  if ($qGW) $websiteLeadBilledGrossW = (float) (mysqli_fetch_assoc($qGW)['gw'] ?? 0);
}

/* # Planning To Visit (from tmp_plan_web already built) */
$websiteLeadPlanningToVisit = 0;
if (mysqli_query($con, "SHOW TABLES LIKE 'tmp_plan_web'")) {
  $rpv = mysqli_query($con, "SELECT COUNT(*) AS c FROM tmp_plan_web");
  if ($rpv) $websiteLeadPlanningToVisit = (int) (mysqli_fetch_assoc($rpv)['c'] ?? 0);
}

/* ---------- MONTH RANGE (based on $fromDate's month) ---------- */
$MONTH_FROM_DATE   = date('Y-m-01', strtotime($fromDate));                 // 1st of month
$MONTH_TO_NEXT_0   = date('Y-m-01', strtotime($fromDate.' +1 month')) . ' 00:00:00';
$MONTH_FROM_0      = $MONTH_FROM_DATE . ' 00:00:00';

/* Monthly: TOTAL CALLS from recording_log (if date/user columns detected earlier) */
$month_total_calls = 0;
if (!empty($rlDateCol)) {
  $q = mysqli_query($con, "
    SELECT COUNT(*) AS c
    FROM `asterisk`.`recording_log`
    WHERE `$rlDateCol` >= '$MONTH_FROM_0' AND `$rlDateCol` < '$MONTH_TO_NEXT_0'
  ");
  if ($q) $month_total_calls = (int)mysqli_fetch_assoc($q)['c'];
}

/* Monthly: TOTAL UNIQUE CUSTOMERS from cust_info */
$month_total_unique = 0;
$q = mysqli_query($con, "
  SELECT COUNT(DISTINCT " . phone_norm_sql('mobile') . ") AS c
  FROM `alpha_attica`.`cust_info`
  WHERE mobile IS NOT NULL AND mobile <> ''
    AND created_datetime >= '$MONTH_FROM_0'
    AND created_datetime <  '$MONTH_TO_NEXT_0'
");
if ($q) $month_total_unique = (int)mysqli_fetch_assoc($q)['c'];

/* Monthly: BULK CUSTOMERS (unique mobiles with GMS > 99) */
$month_bulk_unique = 0;
$q = mysqli_query($con, "
  SELECT COUNT(*) AS c FROM (
    SELECT DISTINCT " . phone_norm_sql('mobile') . " AS phone
    FROM `alpha_attica`.`cust_info`
    WHERE mobile IS NOT NULL AND mobile <> ''
      AND created_datetime >= '$MONTH_FROM_0'
      AND created_datetime <  '$MONTH_TO_NEXT_0'
      AND CAST(REPLACE(COALESCE(gms,'0'), ',', '') AS DECIMAL(10,3)) > 99
  ) x
");
if ($q) $month_bulk_unique = (int)mysqli_fetch_assoc($q)['c'];


?>
<!-- The rest of your HTML + CSS + JS is unchanged. Keep your existing markup and scripts. -->

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
<style>
/* Website Leads tidy layout */
.kpi-tile .tile-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.kpi-tile .tile-title{font-weight:900;font-size:16px;margin:0}
.kpi-tile .tile-icon i{opacity:.9}
.kpi-tile .tile-body{display:grid;grid-template-columns:1fr auto;grid-gap:8px}

/* Numbers */
.kpi-tile .big-num{font-size:24px;font-weight:800;line-height:1}
.kpi-tile .muted-sm{font-size:12px;color:#666;margin-top:2px}

/* Right-side stacked lines */
.kpi-stack{display:flex;flex-direction:column;gap:4px;min-width:160px}
.kpi-row{display:flex;gap:8px;align-items:baseline}
.kpi-label{font-size:12px;font-weight:600;color:#444}
.kpi-value{font-weight:800}

/* Convert “chips” to plain text rows (no button look) */
.kpi-lines{margin-top:6px}
.kpi-line{font-size:12px}
.kpi-line b{font-weight:800}

/* Convert the “View Details” button to a link-style text */
.kpi-actions{margin-top:6px}
.kpi-actions .as-link{background:none;border:0;padding:0;cursor:pointer;
  color:#23527c;text-decoration:underline;font-size:12px;font-weight:600}
.kpi-actions .as-link:hover{opacity:.8}
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

<!-- Total Calls Tile -->
<div class="col-lg-3" style="padding: 5px">
  <div class="hpanel stats" data-toggle="modal" data-target="#allCallsModal" style="cursor:pointer;">
    <div class="panel-body h-200">
      <div class="stats-title pull-left"><h4 style="font-weight:900">Total Calls</h4></div>
      <div class="stats-icon pull-right"><i class="pe-7s-call fa-4x"></i></div>
      <div class="m-t-xl">
        <h3 class="font-bold text-success"><?php echo number_format($total_records); ?></h3>
        <span class="font-bold no-margins">Total calls</span>
        <h3 class="font-bold text-success"><?php echo number_format($totalCallsAll); ?></h3>
        <span class="font-bold no-margins">Total unique customers</span>
      </div>
    </div>
    <div class="panel-footer"><b>Click for more Info >> Attica Call Center</b></div>
  </div>
</div>

<!-- Monthly Data -->
<div class="col-lg-3" style="padding:5px">
  <div class="hpanel stats" id="tileMonthlyData" style="cursor:pointer;">
    <div class="panel-body h-200">
      <div class="stats-title pull-left"><h4 style="font-weight:900">Monthly Data</h4></div>
      <div class="stats-icon pull-right"><i class="pe-7s-date fa-4x"></i></div>
      <div class="m-t-xl">
        <h3 class="font-bold text-success"><?php echo number_format($month_total_calls); ?></h3>
        <span class="font-bold no-margins">Total Calls (Month)</span>
        <br>
        <h3 class="font-bold text-success"><?php echo number_format($month_total_unique); ?></h3>
        <span class="font-bold no-margins">Total Unique Customers (Month)</span>
      </div>
    </div>
    <div class="panel-footer"><b>Click to view Full Month Customers</b></div>
  </div>
</div>

<!-- Bulk Customers (GMS > 99) -->
<div class="col-lg-3" style="padding:5px">
  <div class="hpanel stats" id="tileBulkCustomers" style="cursor:pointer;">
    <div class="panel-body h-200">
      <div class="stats-title pull-left"><h4 style="font-weight:900">Bulk Customers</h4></div>
      <div class="stats-icon pull-right"><i class="pe-7s-diamond fa-4x"></i></div>
      <div class="m-t-xl">
        <h3 class="font-bold text-success"><?php echo number_format($month_bulk_unique); ?></h3>
        <span class="font-bold no-margins">Unique Customers (GMS &gt; 99, Month)</span>
      </div>
    </div>
    <div class="panel-footer"><b>Click to view Bulk Calls (Month)</b></div>
  </div>
</div>


<!-- Missed Calls -->
<div class="col-lg-3" style="padding: 5px">
  <div class="hpanel stats" id="missedCallsTile" style="cursor:pointer;">
    <div class="panel-body h-200">
      <div class="stats-title pull-left"><h4 style="font-weight:900">Missed Calls</h4></div>
      <div class="stats-icon pull-right"><i class="pe-7s-close fa-4x"></i></div>
      <div class="m-t-xl">
        <h3 class="font-bold text-success"><?php echo number_format($totMissed); ?></h3>
        <span class="font-bold no-margins">Overall MissedCalls</span>
      </div>
    </div>
    <div class="panel-footer"><b>Click for more Info >> Attica Call Center</b></div>
  </div>
</div>

<!-- Monthly Customers Modal -->
<div class="modal fade" id="monthlyCustomersModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:96vw; max-width:1600px;">
    <div class="modal-content">
      <div class="color-line"></div>
      <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
      <div class="modal-header">
        <h4 class="modal-title">Monthly Customers — Details</h4>
        <div class="muted" id="monthlyRange"></div>
      </div>
      <div class="modal-body" style="padding:16px 20px;">
        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
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
                <th>Recording Count</th>
                <th>Agent ID</th>
              </tr>
            </thead>
            <tbody id="monthlyCustomersBody">
              <tr><td colspan="13" class="text-center">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div id="monthlyCustomersError" class="text-danger" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Customers (GMS > 99) Modal -->
<div class="modal fade" id="bulkCustomersModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:96vw; max-width:1600px;">
    <div class="modal-content">
      <div class="color-line"></div>
      <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
      <div class="modal-header">
        <h4 class="modal-title">Bulk Customers — GMS &gt; 99 (Monthly)</h4>
        <div class="muted" id="bulkRange"></div>
      </div>
      <div class="modal-body" style="padding:16px 20px;">
        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
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
                <th>Recording Count</th>
                <th>Agent ID</th>
              </tr>
            </thead>
            <tbody id="bulkCustomersBody">
              <tr><td colspan="13" class="text-center">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div id="bulkCustomersError" class="text-danger" style="display:none;"></div>
      </div>
    </div>
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
              <span class="font-bold no-margins">Hot + Warm leads</span>
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

      <!-- Highest/Lowest COG tiles (built in agent endpoints UI) -->
      <?php
      // build extremes from agentsRange
      $convHi = $convLo = $billedHi = $billedLo = null;
      $hiCallsMade = $loCallsMade = null;
      $hiCallsAgent = $loCallsAgent = '-';
      $hiUniquePhones = $loUniquePhones = null;

      $agentsRangeMem = [];
      if ($agentsRange && mysqli_num_rows($agentsRange) > 0) {
        while ($a = mysqli_fetch_assoc($agentsRange)) {
          $aid       = trim($a['agent_id'] ?? '');
          if ($aid==='') continue;
          $uniq      = (int)($a['unique_phones'] ?? 0);
          $callsMade = (int)($a['calls_made'] ?? 0);
          $billed    = (int)($a['billed_cnt'] ?? 0);
          $enquiry   = (int)($a['enquiry_cnt'] ?? 0);
          $billedGW  = (float)($a['billed_gw'] ?? 0.0);
          $convPct   = pct_cap($uniq > 0 ? (($billed + $enquiry) / $uniq) * 100 : 0.0);
          $billedPct = pct_cap($uniq > 0 ? ($billed / $uniq) * 100 : 0.0);


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
          $agentsRangeMem[] = $row;

          if ($convHi === null || $row['conv_pct']   > $convHi['conv_pct'])   $convHi   = $row;
          if ($convLo === null || $row['conv_pct']   < $convLo['conv_pct'])   $convLo   = $row;
          if ($billedHi=== null || $row['billed_pct']> $billedHi['billed_pct'])$billedHi = $row;
          if ($billedLo=== null || $row['billed_pct']< $billedLo['billed_pct'])$billedLo = $row;

          if ($hiCallsMade === null || $callsMade > $hiCallsMade) {
            $hiCallsMade   = $callsMade;
            $hiCallsAgent  = $aid;
            $hiUniquePhones = $uniq;
          }
          if ($loCallsMade === null || $callsMade < $loCallsMade) {
            $loCallsMade   = $callsMade;
            $loCallsAgent  = $aid;
            $loUniquePhones = $uniq;
          }
        }
      }
      ?>

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
              <span class="font-bold no-margins">Total dropped calls</span>
            </div>
          </div>
          <div class="panel-footer"><b>Attica Call Center</b></div>
        </div>
      </div>
<!-- Website Leads (clean) -->
<div class="col-lg-3" style="padding:5px">
  <div class="hpanel stats kpi-tile">
    <div class="panel-body h-200">
      <div class="tile-header">
        <h4 class="tile-title">Website Leads</h4>
        <div class="tile-icon"><i class="pe-7s-global fa-4x"></i></div>
      </div>

      <div class="tile-body">
        <!-- Left -->
        <div>
          <div class="big-num text-success"><?php echo number_format($websiteLeadUnique); ?></div>
          <br>
          <div class="kpi-lines">
            <div class="kpi-line">Billed: <b><?php echo number_format($websiteLeadMatchCounts['Billed'] ?? 0); ?></b></div>
            <div class="kpi-line">Enquiry: <b><?php echo number_format($websiteLeadMatchCounts['Enquiry'] ?? 0); ?></b></div>
            <div class="kpi-line">No Show: <b><?php echo number_format($websiteLeadMatchCounts['No Show'] ?? 0); ?></b></div>
          </div>
        </div>

        <!-- Right (stacked text under the icon) -->
        <div class="kpi-stack">
          <div class="kpi-row">
            <span class="kpi-label">Pending Calls</span>
            <span class="kpi-value"><?php echo number_format($websiteLeadPendingCallStatus); ?></span>
          </div>
          <div class="kpi-row">
            <span class="kpi-label">Billed GW</span>
            <span class="kpi-value"><?php echo number_format($websiteLeadBilledGrossW ?? 0, 2); ?></span>
          </div>
          <div class="kpi-row">
            <span class="kpi-label">Planning To Visit</span>
            <span class="kpi-value"><?php echo number_format($websiteLeadPlanningToVisit ?? 0); ?></span>
          </div>
          <div class="kpi-actions">
            <button id="btnWebsiteLeadDetails">View details</button>
        </div>
        </div>
      </div>
    </div>
    <div class="panel-footer"> <b>Attica Call Center</b></div>
  </div>
</div>
      <!-- Language Split -->
      <div class="col-lg-6" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-200">
            <div class="stats-title pull-left"><h4 style="font-weight:900">Language Mix </h4></div>
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
                    $convPct   = pct_cap($callsU > 0 ? (($billed + $enquiry) / $callsU) * 100 : 0);
                    $billedPct = pct_cap($callsU > 0 ? ($billed / $callsU) * 100 : 0);

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

<!-- Website Lead Details Modal -->
<div class="modal fade" id="websiteLeadsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:96vw; max-width:1600px;">
    <div class="modal-content">
      <div class="color-line"></div>
      <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
      <div class="modal-header">
        <h4 class="modal-title">Website Leads — Details</h4>
        <div class="muted" id="wlRange"></div>
      </div>
      <div class="modal-body" style="padding:16px 20px;">
        <form id="wlSearchForm" class="form-inline" style="margin-bottom:12px;">
          <div class="form-group" style="margin-right:10px;">
            <label>From</label>
            <input type="date" class="form-control" id="wl_from" value="<?php echo htmlspecialchars($fromDate); ?>">
          </div>
          <div class="form-group" style="margin-right:10px;">
            <label>To</label>
            <input type="date" class="form-control" id="wl_to" value="<?php echo htmlspecialchars($toDate); ?>">
          </div>
          <div class="form-group" style="margin-right:10px;">
            <label>Status</label>
            <input type="text" class="form-control" id="wl_status" placeholder="eg. Pending / Done">
          </div>
          <div class="form-group" style="margin-right:10px;">
            <label>Search</label>
            <input type="text" class="form-control" id="wl_q" placeholder="name / mobile / remarks">
          </div>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th style="white-space:nowrap;">#</th>
                <th>id</th>
                <th>name</th>
                <th>mobile</th>
                <th>type</th>
                <th>state</th>
                <th>date</th>
                <th>time</th>
                <th>status</th>
                <th>remarks</th>
                <th>comments</th>
                <th>updateDate</th>
                <th>followup</th>
                <th>device</th>
                <th>Matched</th>
                <th>GrossWt (g)</th>
              </tr>
            </thead>
            <tbody id="wlBody">
              <tr><td colspan="14" class="text-center">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div id="wlError" class="text-danger" style="display:none;"></div>
      </div>
    </div>
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

      <!-- Agent Metrics Modal -->
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
                <tr><th>Calls Received </th><td id="m_calls">-</td></tr>
                <tr><th>Shown Interest (Hot/Warm Lead)</th><td id="m_interest">-</td></tr>
                <tr><th>Billed / Release</th><td id="m_billed">-</td></tr>
                <tr><th>Enquiry</th><td id="m_enquiry">-</td></tr>
                <tr><th>Pending Visit</th><td id="m_pending">-</td></tr>
                <tr><th>Not Visited</th><td id="m_notvisited">-</td></tr>
                <tr><th>Others</th><td id="m_others">-</td></tr>
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


<!-- Missed Calls — Details Modal -->
<div class="modal fade" id="missedCallsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:96vw; max-width:1600px;">
    <div class="modal-content">
      <div class="color-line"></div>
      <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
      <div class="modal-header">
        <h4 class="modal-title">Missed Calls — Details</h4>
        <div class="muted" id="missedCallsRange"></div>
      </div>
      <div class="modal-body" style="padding:16px 20px;">
        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
          <table class="table table-bordered table-striped table-hover">
          <thead class="table-dark">
  <tr>
    <th>#</th>
    <th>Date/Time</th>
    <th>Phone</th>
    <th>User</th>
    <th>Dial Status</th>
    <th>Language</th> <!-- actually campaign_id -->
    <th>Status</th>
  </tr>
</thead>
<tbody id="missedCallsBody">
  <tr><td colspan="7" class="text-center">Loading…</td></tr>
</tbody>
          </table>
        </div>
        <div id="missedCallsError" class="text-danger" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>

<style>
  #missedCallsModal .table { table-layout: fixed; }
  #missedCallsModal th, 
  #missedCallsModal td { white-space: normal; word-break: break-word; }
</style>

<script>
(function(){
  const M_FROM = '<?php echo htmlspecialchars($MONTH_FROM_DATE, ENT_QUOTES); ?>';
  const M_TO   = '<?php echo htmlspecialchars(date('Y-m-d', strtotime($MONTH_FROM_DATE . ' +1 month -1 day')), ENT_QUOTES); ?>';

  function escHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  /* -------- Monthly Customers -------- */
  function openMonthlyCustomers(){
    const body  = document.getElementById('monthlyCustomersBody');
    const err   = document.getElementById('monthlyCustomersError');
    const range = document.getElementById('monthlyRange');

    body.innerHTML = '<tr><td colspan="13" class="text-center">Loading…</td></tr>';
    err.style.display='none'; err.textContent='';
    range.textContent = 'Month: ' + M_FROM + ' → ' + M_TO;

    $('#monthlyCustomersModal').modal('show');

    const url = 'all_calls_details.php?from=' + encodeURIComponent(M_FROM)
              + '&to=' + encodeURIComponent(M_TO);

    fetch(url)
      .then(r=>r.json())
      .then(data=>{
        if (data.error){
          err.textContent = data.error;
          err.style.display='block';
          body.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (!rows.length){
          body.innerHTML = '<tr><td colspan="13" class="text-center">No records found</td></tr>';
          return;
        }
        let i=1, html='';
        rows.forEach(r=>{
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
            + '<td>'+ (Number(r.rec_count)||0) +'</td>'
            + '<td>'+ escHtml(r.agent_id) +'</td>'
          + '</tr>';
        });
        body.innerHTML = html;
      })
      .catch(()=>{
        err.textContent = 'Network error while fetching monthly customers.';
        err.style.display='block';
        body.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Failed to load</td></tr>';
      });
  }

  /* -------- Bulk Customers (GMS > 99, unique mobiles) -------- */
  function openBulkCustomers(){
    const body  = document.getElementById('bulkCustomersBody');
    const err   = document.getElementById('bulkCustomersError');
    const range = document.getElementById('bulkRange');

    body.innerHTML = '<tr><td colspan="13" class="text-center">Loading…</td></tr>';
    err.style.display='none'; err.textContent='';
    range.textContent = 'Month: ' + M_FROM + ' → ' + M_TO + ' | GMS > 99';

    $('#bulkCustomersModal').modal('show');

    const url = 'all_calls_details.php?from=' + encodeURIComponent(M_FROM)
              + '&to='   + encodeURIComponent(M_TO);

    fetch(url)
      .then(r=>r.json())
      .then(data=>{
        if (data.error){
          err.textContent = data.error;
          err.style.display='block';
          body.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (!rows.length){
          body.innerHTML = '<tr><td colspan="13" class="text-center">No records</td></tr>';
          return;
        }

        // Filter GMS > 99 and de-duplicate by normalized mobile
        const seen = new Set();
        const out = [];
        rows.forEach(r=>{
          const g = parseFloat(String(r.gms||'0').replace(/,/g,''));
          const m = String(r.mobile||'').replace(/[\s()+\-\.]/g,'').replace(/^0+/,'');
          if (!m) return;
          if (isNaN(g) || g <= 99) return;
          if (seen.has(m)) return;
          seen.add(m);
          out.push(r);
        });

        if (!out.length){
          body.innerHTML = '<tr><td colspan="13" class="text-center">No bulk customers (GMS &gt; 99) this month</td></tr>';
          return;
        }

        // Optional: sort desc by GMS
        out.sort((a,b)=> (parseFloat((b.gms||'0').toString().replace(/,/g,''))||0) - (parseFloat((a.gms||'0').toString().replace(/,/g,''))||0));

        let i=1, html='';
        out.forEach(r=>{
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
            + '<td>'+ (Number(r.rec_count)||0) +'</td>'
            + '<td>'+ escHtml(r.agent_id) +'</td>'
          + '</tr>';
        });
        body.innerHTML = html;
      })
      .catch(()=>{
        err.textContent = 'Network error while fetching bulk customers.';
        err.style.display='block';
        body.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Failed to load</td></tr>';
      });
  }

  const t1 = document.getElementById('tileMonthlyData');
  const t2 = document.getElementById('tileBulkCustomers');
  if (t1) t1.addEventListener('click', openMonthlyCustomers);
  if (t2) t2.addEventListener('click', openBulkCustomers);
})();
</script>


<script>
(function(){
  const FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  function escHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  function openMissedCalls(){
    const body  = document.getElementById('missedCallsBody');
    const err   = document.getElementById('missedCallsError');
    const range = document.getElementById('missedCallsRange');

    body.innerHTML = '<tr><td colspan="5" class="text-center">Loading…</td></tr>';
    err.style.display = 'none'; err.textContent = '';
    range.textContent = 'Range: ' + FROM + ' → ' + TO;

    $('#missedCallsModal').modal('show');

    const url = 'missed_calls_details.php?from=' + encodeURIComponent(FROM)
              + '&to=' + encodeURIComponent(TO);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error){
          err.textContent = data.error;
          err.style.display = 'block';
          body.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (rows.length === 0){
          body.innerHTML = '<tr><td colspan="5" class="text-center">No missed calls found</td></tr>';
          return;
        }
        let i=1, html='';
        rows.forEach(r => {
  html += '<tr>'
    + '<td>'+ (i++) +'</td>'
    + '<td>'+ escHtml(r.dt) +'</td>'
    + '<td>'+ escHtml(r.phone) +'</td>'
    + '<td>'+ escHtml(r.user) +'</td>'
    + '<td>'+ escHtml(r.dial_status) +'</td>'
    + '<td>'+ escHtml(r.campaign_id) +'</td>'  // shown as Language
    + '<td>'+ escHtml(r.status) +'</td>'
  + '</tr>';
});
        body.innerHTML = html;
      })
      .catch(() => {
        err.textContent = 'Network error while fetching missed calls.';
        err.style.display = 'block';
        body.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>';
      });
  }

  const tile = document.getElementById('missedCallsTile');
  if (tile) tile.addEventListener('click', openMissedCalls);
})();
</script>


<!-- All Calls Details Modal -->
<style>
#allCallsModal thead input {
  width: 100%;
  min-width: 70px;
  font-size: 12px;
  padding: 2px 4px;
  box-sizing: border-box;
}
#allCallsModal .table { table-layout: fixed; }
#allCallsModal th, #allCallsModal td { white-space: normal; word-break: break-word; }
</style>
<!-- All Calls Details Modal -->
<div class="modal fade" id="allCallsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:96vw; max-width:1600px;">
    <div class="modal-content">
      <div class="color-line"></div>
      <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
      <div class="modal-header">
        <h4 class="modal-title">All Calls — Details</h4>
        <div class="muted" id="allCallsRange"></div>
      </div>
      <div class="modal-body" style="padding:16px 20px;">
        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
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
                <th>Recording Count</th>
                <th>Agent ID</th>
              </tr>
            </thead>
            <tbody id="allCallsBody">
              <tr><td colspan="13" class="text-center">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div id="allCallsError" class="text-danger" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>
<script>
function addAllCallsFilters() {
  const table = document.querySelector("#allCallsModal table");
  if (!table) return;

  const inputs = table.querySelectorAll("thead input");
  inputs.forEach((input, colIndex) => {
    input.addEventListener("keyup", function() {
      const filter = this.value.toLowerCase();
      const rows = table.querySelectorAll("tbody tr");
      rows.forEach(row => {
        const cell = row.cells[colIndex];
        if (!cell) return;
        const txt = cell.textContent.toLowerCase();
        row.style.display = txt.indexOf(filter) > -1 ? "" : "none";
      });
    });
  });
}

$('#allCallsModal').on('shown.bs.modal', function() {
  addAllCallsFilters();
});
</script>

<script>
(function(){
  const FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  function escHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  // When the modal opens
  $('#allCallsModal').on('show.bs.modal', function(){
    const body = document.getElementById('allCallsBody');
    const err  = document.getElementById('allCallsError');
    const range = document.getElementById('allCallsRange');

    body.innerHTML = '<tr><td colspan="12" class="text-center">Loading…</td></tr>';
    err.style.display = 'none'; err.textContent = '';
    range.textContent = 'Range: ' + FROM + ' → ' + TO;

    const url = 'all_calls_details.php?from=' + encodeURIComponent(FROM)
              + '&to='   + encodeURIComponent(TO);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error){
          err.textContent = data.error;
          err.style.display = 'block';
          body.innerHTML = '<tr><td colspan="12" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (rows.length === 0){
          body.innerHTML = '<tr><td colspan="12" class="text-center">No records found</td></tr>';
          return;
        }
        let i=1, html='';
        rows.forEach(r=>{
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
    + '<td>'+ (Number(r.rec_count)||0) +'</td>'
    + '<td>'+ escHtml(r.agent_id) +'</td>'   // ✅ Added Agent ID
  + '</tr>';
});

        body.innerHTML = html;
      })
      .catch(()=>{
        err.textContent = 'Network error while fetching calls.';
        err.style.display = 'block';
        body.innerHTML = '<tr><td colspan="12" class="text-center text-danger">Failed to load</td></tr>';
      });
  });
})();
</script>

<script>
(function(){
  const FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  function openAgent(agent){
    document.getElementById('agentTitle').textContent = 'Agent Metrics — ' + agent;
    document.getElementById('agentRange').textContent = 'Range: ' + FROM + ' → ' + TO;

    ['m_calls','m_interest','m_billed','m_enquiry','m_pending','m_notvisited','m_others']
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

(function(){
  const DEF_FROM = '<?php echo htmlspecialchars($fromDate, ENT_QUOTES); ?>';
  const DEF_TO   = '<?php echo htmlspecialchars($toDate,   ENT_QUOTES); ?>';

  const btn = document.getElementById('btnWebsiteLeadDetails');
  const body = document.getElementById('wlBody');
  const err  = document.getElementById('wlError');
  const range = document.getElementById('wlRange');
  const f = document.getElementById('wlSearchForm');

  function escHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  function loadWL(from, to, status, q){
    err.style.display='none'; err.textContent='';
    body.innerHTML = '<tr><td colspan="14" class="text-center">Loading…</td></tr>';
    range.textContent = 'Range: ' + from + ' → ' + to + (status? (' | Status: ' + status) : '') + (q? (' | Search: ' + q) : '');

    const url = 'website_leads_details.php'
      + '?from=' + encodeURIComponent(from)
      + '&to='   + encodeURIComponent(to)
      + (status ? '&status=' + encodeURIComponent(status) : '')
      + (q ? '&q=' + encodeURIComponent(q) : '');

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error){
          err.textContent = data.error;
          err.style.display = 'block';
          body.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Failed to load</td></tr>';
          return;
        }
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (rows.length === 0){
          body.innerHTML = '<tr><td colspan="14" class="text-center">No records found</td></tr>';
          return;
        }
        let i=1, html='';
        rows.forEach(r=>{
          html += '<tr>'
            + '<td>'+ (i++) +'</td>'
            + '<td>'+ escHtml(r.id) +'</td>'
            + '<td>'+ escHtml(r.name) +'</td>'
            + '<td>'+ escHtml(r.mobile) +'</td>'
            + '<td>'+ escHtml(r.type) +'</td>'
            + '<td>'+ escHtml(r.state) +'</td>'
            + '<td>'+ escHtml(r.date) +'</td>'
            + '<td>'+ escHtml(r.time) +'</td>'
            + '<td>'+ escHtml(r.status) +'</td>'
            + '<td>'+ escHtml(r.remarks) +'</td>'
            + '<td>'+ escHtml(r.comments) +'</td>'
            + '<td>'+ escHtml(r.updateDate) +'</td>'
            + '<td>'+ escHtml(r.followup) +'</td>'
            + '<td>'+ escHtml(r.device) +'</td>'
            + '<td>'+ escHtml(r.matched) +'</td>'
            + '<td>'+ (Number(r.grossw||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}) +'</td>'

          + '</tr>';
        });
        body.innerHTML = html;
      })
      .catch(()=>{
        err.textContent = 'Network error while fetching website leads.';
        err.style.display = 'block';
        body.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Failed to load</td></tr>';
      });
  }

  if (btn){
    btn.addEventListener('click', function(){
      document.getElementById('wl_from').value = DEF_FROM;
      document.getElementById('wl_to').value   = DEF_TO;
      document.getElementById('wl_status').value = '';
      document.getElementById('wl_q').value = '';
      $('#websiteLeadsModal').modal('show');
      loadWL(DEF_FROM, DEF_TO, '', '');
    });
  }

  if (f){
    f.addEventListener('submit', function(e){
      e.preventDefault();
      const from = document.getElementById('wl_from').value || DEF_FROM;
      const to   = document.getElementById('wl_to').value   || DEF_TO;
      const status = document.getElementById('wl_status').value.trim();
      const q      = document.getElementById('wl_q').value.trim();
      loadWL(from, to, status, q);
    });
  }
})();
</script>

