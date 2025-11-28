<?php
/**
 * all_calls_details.php
 * Returns JSON rows of all customer calls in a given date range (names first).
 * Expects: GET from (YYYY-MM-DD), to (YYYY-MM-DD)
 * Output: { rows: [ {customer_name, mobile, location, district, language,
 *                   business_type, gms, assigned_to_agpl_branch,
 *                   follow_up_yes_date, lead, rec_count, agent_id} ... ] }
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
function phone_norm_sql($col){
  return "TRIM(LEADING '0' FROM
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM($col),' ',''),'-',''),'+',''),'(',''),')',''),'.',''),CHAR(13),''),CHAR(10),'')
          )";
}

/* ---------- Inputs ---------- */
$from  = isset($_GET['from']) ? trim($_GET['from']) : '';
$to    = isset($_GET['to'])   ? trim($_GET['to'])   : '';

if (!valid_date($from) || !valid_date($to)) json_error('Invalid or missing date(s)');

$fromDate = $from;
$toDate   = $to;
if (strtotime($fromDate) > strtotime($toDate)) { $t=$fromDate; $fromDate=$toDate; $toDate=$t; }
$FROM0    = $con->real_escape_string($fromDate . ' 00:00:00');
$TO_NEXT0 = $con->real_escape_string(date('Y-m-d', strtotime($toDate . ' +1 day')) . ' 00:00:00');

/* ---------- recording_log columns ---------- */
$rlDateCol = 'start_time';   // ✅ confirmed in schema
$rlPhoneCol = 'filename';    // ✅ phone is in filename (last _ segment)

/* ---------- Query ---------- */
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
    ci.created_datetime,
    COUNT(rl.recording_id) AS rec_count,
    -- Extract agent_id from filename (second last underscore)
    SUBSTRING_INDEX(SUBSTRING_INDEX(rl.filename, '_', -2), '_', 1) AS agent_id
  FROM alpha_attica.cust_info ci
  LEFT JOIN asterisk.recording_log rl
    ON ".phone_norm_sql('ci.mobile')." = ".phone_norm_sql("SUBSTRING_INDEX(rl.filename, '_', -1)")."
    AND rl.$rlDateCol >= '$FROM0' AND rl.$rlDateCol < '$TO_NEXT0'
  WHERE ci.created_datetime >= '$FROM0' AND ci.created_datetime < '$TO_NEXT0'
    AND ci.mobile IS NOT NULL AND ci.mobile <> ''
  GROUP BY ci.customer_name, ci.mobile, ci.location, ci.district,
           ci.language, ci.business_type, ci.gms, ci.assigned_to_agpl_branch,
           ci.follow_up_yes_date, ci.lead, ci.created_datetime, agent_id
  ORDER BY 
    CASE WHEN ci.customer_name IS NULL OR TRIM(ci.customer_name) = '' THEN 1 ELSE 0 END ASC,
    ci.created_datetime DESC
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
      'rec_count'               => (int)($r['rec_count'] ?? 0),
      'agent_id'                => (string)($r['agent_id'] ?? ''),
    ];
  }
} else {
  json_error('Query failed: '.$con->error, 500);
}

/* ---------- Output ---------- */
echo json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE);

