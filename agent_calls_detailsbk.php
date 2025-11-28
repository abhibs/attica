<?php
/**
 * agent_calls_details.php
 * Returns JSON rows of call/customer details for a specific agent and date range.
 * Expects: GET agent (string), from (YYYY-MM-DD), to (YYYY-MM-DD)
 * Output: { rows: [ {customer_name, mobile, location, district, language, business_type, gms, assigned_to_agpl_branch, follow_up_yes_date, lead} ... ] }
 */

error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json; charset=utf-8');

// Use the same connection as the dashboard page
require_once __DIR__ . '/dbConnection.php';
@mysqli_set_charset($con, 'utf8mb4');

function json_error($msg, $code = 400){
  http_response_code($code);
  echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}
function valid_date($d){ return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }
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

/* ---------- Build temp phone set for agent ---------- */
$con->query("DROP TEMPORARY TABLE IF EXISTS tmp_agent_phones");
$sql_tmp = "
  CREATE TEMPORARY TABLE tmp_agent_phones AS
  SELECT DISTINCT ".phone_norm_sql('vl.phone_number')." AS phone
  FROM `asterisk`.`vicidial_log` vl
  WHERE vl.call_date >= '$FROM0' AND vl.call_date < '$TO_NEXT0'
    AND vl.user REGEXP '^[0-9]+$'
    AND vl.user = '$escAgent'
    AND vl.phone_number IS NOT NULL AND vl.phone_number <> ''
";
if (!$con->query($sql_tmp)) {
  json_error('Failed to build temp phones: '.$con->error, 500);
}
$con->query("CREATE INDEX IF NOT EXISTS idx_tmp_agent_phones_phone ON tmp_agent_phones (phone)");

/* ---------- Pull latest cust_info row per phone within range ---------- */
/* Order: rows with a non-empty customer_name first, then others; within each group, newest first */
$sql_rows = "
  SELECT
    ci.customer_name,
    ci.mobile,
    ci.location,
    ci.district,
    ci.language,
    ci.business_type,
    ci.gms,
    ci.assigned_to_agpl_branch,
    ci.follow_up_yes_date,
    ci.lead,
    latest.maxdt AS row_datetime
  FROM `alpha_attica`.`cust_info` ci
  JOIN tmp_agent_phones p
    ON ".phone_norm_sql('ci.mobile')." = p.phone
  JOIN (
    SELECT ".phone_norm_sql('ci2.mobile')." AS phone, MAX(ci2.created_datetime) AS maxdt
    FROM `alpha_attica`.`cust_info` ci2
    JOIN tmp_agent_phones p2
      ON ".phone_norm_sql('ci2.mobile')." = p2.phone
    WHERE ci2.created_datetime >= '$FROM0' AND ci2.created_datetime < '$TO_NEXT0'
    GROUP BY ".phone_norm_sql('ci2.mobile')."
  ) latest
    ON latest.phone = ".phone_norm_sql('ci.mobile')."
   AND latest.maxdt = ci.created_datetime
  WHERE ci.created_datetime >= '$FROM0' AND ci.created_datetime < '$TO_NEXT0'
  ORDER BY
    CASE WHEN ci.customer_name IS NULL OR TRIM(ci.customer_name) = '' THEN 1 ELSE 0 END ASC,
    latest.maxdt DESC
  LIMIT 1000
";

$rows = [];
if ($res = $con->query($sql_rows)) {
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'customer_name'           => (string)($r['customer_name'] ?? ''),
      'mobile'                  => (string)($r['mobile'] ?? ''),
      'location'                => (string)($r['location'] ?? ''),
      'district'                => (string)($r['district'] ?? ''),
      'language'                => (string)($r['language'] ?? ''),
      'business_type'           => (string)($r['business_type'] ?? ''),
      'gms'                     => (string)($r['gms'] ?? ''),
      'assigned_to_agpl_branch' => (string)($r['assigned_to_agpl_branch'] ?? ''),
      'follow_up_yes_date'      => (string)($r['follow_up_yes_date'] ?? ''),
      'lead'                    => (string)($r['lead'] ?? ''),
      // 'row_datetime' is not returned in JSON (used only for ORDER BY)
    ];
  }
} else {
  json_error('Query failed: '.$con->error, 500);
}

/* ---------- Output ---------- */
echo json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE);

