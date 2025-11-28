<?php
/**
 * missed_calls_details.php
 * Returns JSON rows of all missed calls in a given date range.
 * Expects: GET from (YYYY-MM-DD), to (YYYY-MM-DD)
 * Output: { rows: [ {dt, phone_number, status, dial_status, campaign_id, user} ... ] }
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

/* ---------- Inputs ---------- */
$from  = isset($_GET['from']) ? trim($_GET['from']) : '';
$to    = isset($_GET['to'])   ? trim($_GET['to'])   : '';

if (!valid_date($from) || !valid_date($to)) json_error('Invalid or missing date(s)');
$fromDate = $from; $toDate = $to;
if (strtotime($fromDate) > strtotime($toDate)) { $t=$fromDate; $fromDate=$toDate; $toDate=$t; }

$FROM0    = $con->real_escape_string($fromDate . ' 00:00:00');
$TO_NEXT0 = $con->real_escape_string(date('Y-m-d', strtotime($toDate . ' +1 day')) . ' 00:00:00');

/* ---------- Robust date parser ---------- */
$dtExpr = "
  COALESCE(
    STR_TO_DATE(call_date, '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(call_date, '%Y-%m-%d'),
    STR_TO_DATE(call_date, '%d-%m-%Y %H:%i:%s'),
    STR_TO_DATE(call_date, '%d-%m-%Y'),
    STR_TO_DATE(call_date, '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(call_date, '%d/%m/%Y')
  )
";

/* ---------- Query ---------- */
$sql = "
  SELECT
    id,
    $dtExpr AS dt,
    call_date,
    phone_number,
    status,
    dial_status,
    campaign_id,
    user
  FROM alpha_attica.missed_calls
  WHERE $dtExpr >= '$FROM0' AND $dtExpr < '$TO_NEXT0'
  ORDER BY dt DESC, id DESC
  LIMIT 2000
";

$rows = [];
if ($res = $con->query($sql)) {
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id'          => (int)($r['id'] ?? 0),
      'dt'          => (string)($r['dt'] ?? ''),
      'phone'       => (string)($r['phone_number'] ?? ''),
      'status'      => (string)($r['status'] ?? ''),
      'dial_status' => (string)($r['dial_status'] ?? ''),
      'campaign_id' => (string)($r['campaign_id'] ?? ''),
      'user'        => (string)($r['user'] ?? ''),
    ];
  }
  echo json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE);
}
