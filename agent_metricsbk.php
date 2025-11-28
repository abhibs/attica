<?php
/**
 * agent_metrics.php
 * Returns JSON with per-agent metrics for the given date range.
 * Expects: GET agent (string), from (YYYY-MM-DD), to (YYYY-MM-DD)
 */

error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/dbConnection.php';
@mysqli_set_charset($con, 'utf8mb4');

function json_error($msg, $code = 400){
  http_response_code($code);
  echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}
function valid_date($d){ return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }

// must match dashboard
function phone_norm_sql($col){
  return "TRIM(LEADING '0' FROM
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM($col),' ',''),'-',''),'+',''),'(',''),')',''),'.',''),CHAR(13),''),CHAR(10),'')
          )";
}

/* ---------- Inputs ---------- */
$agent = isset($_GET['agent']) ? trim($_GET['agent']) : '';
$from  = isset($_GET['from'])  ? trim($_GET['from'])  : '';
$to    = isset($_GET['to'])    ? trim($_GET['to'])    : '';
if ($agent === '') json_error('Missing agent');
if (!valid_date($from) || !valid_date($to)) json_error('Invalid or missing date(s)');

$fromDate = $from;
$toDate   = $to;
if (strtotime($fromDate) > strtotime($toDate)) { $t=$fromDate; $fromDate=$toDate; $toDate=$t; }

$FROM0    = $con->real_escape_string($fromDate . ' 00:00:00');
$TO_NEXT0 = $con->real_escape_string(date('Y-m-d', strtotime($toDate . ' +1 day')) . ' 00:00:00');
$escAgent = $con->real_escape_string($agent);

/* ---------- Detect EC phone column (same as dashboard) ---------- */
$ecCol = 'contact';
$hasCol = function($c) use ($con){
  $q = mysqli_query($con, "SHOW COLUMNS FROM `atticaaws`.`everycustomer` LIKE '$c'");
  return $q && mysqli_num_rows($q) > 0;
};
if     (!$hasCol('contact') &&  $hasCol('mobile')) $ecCol = 'mobile';
elseif (!$hasCol('contact') && !$hasCol('mobile') && $hasCol('phone'))  $ecCol = 'phone';
$pn_ec = phone_norm_sql("ec.`$ecCol`");

/* ---------- Build temp phone set for this agent (UNIVERSE) ---------- */
/* CHANGE HERE: use TRIM(vl.user) to match dashboard agent counting */
$con->query("DROP TEMPORARY TABLE IF EXISTS tmp_agent_phones");
$sql_tmp = "
  CREATE TEMPORARY TABLE tmp_agent_phones AS
  SELECT DISTINCT ".phone_norm_sql('vl.phone_number')." AS phone
  FROM `asterisk`.`vicidial_log` vl
  WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
    AND vl.user REGEXP '^[0-9]+$'
    AND TRIM(vl.user) = '$escAgent'
    AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
";
if (!$con->query($sql_tmp)) {
  json_error('Failed to build temp phones: '.$con->error, 500);
}
@$con->query("CREATE INDEX IF NOT EXISTS idx_tmp_agent_phones_phone ON tmp_agent_phones (phone)");

/* ---------- Calls received (unique) ---------- */
$calls_received = 0;
if ($q = $con->query("SELECT COUNT(*) AS c FROM tmp_agent_phones")) {
  $calls_received = (int)($q->fetch_assoc()['c'] ?? 0);
}

/* ---------- Shown Interest (HOT/WARM in cust_info within range) ---------- */
$interested = 0;
$sqlInterested = "
  SELECT COUNT(DISTINCT p.phone) AS c
  FROM tmp_agent_phones p
  JOIN `alpha_attica`.`cust_info` ci
    ON ".phone_norm_sql('ci.mobile')." = p.phone
  WHERE ci.created_datetime >= '$FROM0' AND ci.created_datetime < '$TO_NEXT0'
    AND LOWER(ci.lead) IN ('hot','warm')
";
if ($r = $con->query($sqlInterested)) { $interested = (int)$r->fetch_assoc()['c']; }

/* ---------- Billed/Release (EC open-ended from fromDate) ---------- */
$billed_release = 0;
$sqlBR = "
  SELECT COUNT(DISTINCT p.phone) AS c
  FROM tmp_agent_phones p
  JOIN `atticaaws`.`everycustomer` ec
    ON $pn_ec = p.phone
  WHERE ec.`date` >= '".$con->real_escape_string($fromDate)."'
    AND LOWER(ec.status) IN ('billed','release')
";
if ($r = $con->query($sqlBR)) { $billed_release = (int)$r->fetch_assoc()['c']; }

/* ---------- Enquiry (EC open-ended from fromDate) ---------- */
$enquiry = 0;
$sqlENQ = "
  SELECT COUNT(DISTINCT p.phone) AS c
  FROM tmp_agent_phones p
  JOIN `atticaaws`.`everycustomer` ec
    ON $pn_ec = p.phone
  WHERE ec.`date` >= '".$con->real_escape_string($fromDate)."'
    AND LOWER(ec.status) = 'enquiry'
";
if ($r = $con->query($sqlENQ)) { $enquiry = (int)$r->fetch_assoc()['c']; }

/* ---------- Pending Visit (remarks within range) ---------- */
$pending_visit = 0;
$sqlPending = "
  SELECT COUNT(DISTINCT p.phone) AS c
  FROM tmp_agent_phones p
  JOIN `alpha_attica`.`cust_info` ci
    ON ".phone_norm_sql('ci.mobile')." = p.phone
  JOIN `alpha_attica`.`remarks_info` r
    ON r.customer_id = ci.customer_id
  WHERE r.remarks IS NOT NULL AND r.remarks <> ''
    AND r.remarks_date_time >= '$FROM0' AND r.remarks_date_time < '$TO_NEXT0'
    AND (LOWER(r.remarks) REGEXP 'coming[[:space:][:punct:]]*to[[:space:][:punct:]]*office'
         OR LOWER(r.remarks) REGEXP '\\bcoming\\b')
    AND LOWER(r.remarks) NOT REGEXP 'not[[:space:][:punct:]]*coming'
";
if ($r = $con->query($sqlPending)) { $pending_visit = (int)$r->fetch_assoc()['c']; }

/* ---------- Not Visited (remarks within range) ---------- */
$not_visited = 0;
$sqlNV = "
  SELECT COUNT(DISTINCT p.phone) AS c
  FROM tmp_agent_phones p
  JOIN `alpha_attica`.`cust_info` ci
    ON ".phone_norm_sql('ci.mobile')." = p.phone
  JOIN `alpha_attica`.`remarks_info` r
    ON r.customer_id = ci.customer_id
  WHERE r.remarks IS NOT NULL AND r.remarks <> ''
    AND r.remarks_date_time >= '$FROM0' AND r.remarks_date_time < '$TO_NEXT0'
    AND (
      LOWER(r.remarks) REGEXP 'not[[:space:][:punct:]]*visited'
      OR LOWER(r.remarks) REGEXP 'didn[[:punct:][:space:]]*t[[:space:]]*come'
      OR LOWER(r.remarks) REGEXP 'did[[:space:]]*not[[:space:]]*come'
      OR LOWER(r.remarks) REGEXP 'no[[:space:]]*show'
    )
";
if ($r = $con->query($sqlNV)) { $not_visited = (int)$r->fetch_assoc()['c']; }

/* ---------- Output ---------- */
echo json_encode([
  'agent'          => $agent,
  'from'           => $fromDate,
  'to'             => $toDate,
  'calls_received' => $calls_received,
  'interested'     => $interested,
  'billed_release' => $billed_release,
  'enquiry'        => $enquiry,
  'pending_visit'  => $pending_visit,
  'not_visited'    => $not_visited,
], JSON_UNESCAPED_UNICODE);

