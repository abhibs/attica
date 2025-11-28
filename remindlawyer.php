<?php
/**
 * Cron: Upcoming Case SMS Reminders (hardened)
 * - Window: today .. today+3 days (inclusive)
 * - Only status='Open'
 * - No redirects/headers; prints JSON and logs to file
 */

error_reporting(E_ALL);
ini_set('display_errors', '0'); // keep output clean for cron; errors go to file below
date_default_timezone_set('Asia/Kolkata');

// ---------- Paths (case-sensitive on Linux!) ----------
$DB_PATH  = __DIR__ . '/dbConnection.php';
$SMS_PATH = __DIR__ . '/Config/SMS/sms_generator.php'; // verify Casing on server

// ---------- Logging ----------
$LOG_DIR  = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/case_reminders.log';
$PHP_ERR  = $LOG_DIR . '/php_errors.log';

if (!is_dir($LOG_DIR)) {
    @mkdir($LOG_DIR, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $PHP_ERR);

function fail_json($msg, $extra = [], $http = 500) {
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    $out = array_merge(['ok' => false, 'error' => $msg], $extra);
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function log_line($msg) {
    global $LOG_FILE;
    $stamp = date('Y-m-d H:i:s');
    @file_put_contents($LOG_FILE, "[$stamp] $msg\n", FILE_APPEND);
}

/* ---------- Sanity checks on required files ---------- */
if (!file_exists($DB_PATH)) {
    fail_json("Missing dbConnection.php", ['path' => $DB_PATH]);
}
if (!file_exists($SMS_PATH)) {
    fail_json("Missing sms_generator.php (check path/case)", ['path' => $SMS_PATH]);
}

require_once $DB_PATH;   // must define $con (mysqli)
require_once $SMS_PATH;  // must define class SMS

/* ---------- Validate DB connection ---------- */
if (!isset($con) || !($con instanceof mysqli)) {
    fail_json("DB connection \$con not initialized (mysqli)", [
        'mysqli_connect_errno' => function_exists('mysqli_connect_errno') ? mysqli_connect_errno() : null,
        'mysqli_connect_error' => function_exists('mysqli_connect_error') ? mysqli_connect_error() : null
    ]);
}

/* ---------- Verify table/columns exist (quick check) ---------- */
$checkSql = "SHOW COLUMNS FROM `cases` LIKE 'status'";
if (!mysqli_query($con, $checkSql)) {
    fail_json("Table/column check failed for `cases`.`status`", ['mysqli_error' => mysqli_error($con)]);
}
$checkSql = "SHOW COLUMNS FROM `cases` LIKE 'date'";
if (!mysqli_query($con, $checkSql)) {
    fail_json("Table/column check failed for `cases`.`date`", ['mysqli_error' => mysqli_error($con)]);
}
$checkSql = "SHOW COLUMNS FROM `cases` LIKE 'name'";
if (!mysqli_query($con, $checkSql)) {
    fail_json("Table/column check failed for `cases`.`name`", ['mysqli_error' => mysqli_error($con)]);
}

/* ---------- Config ---------- */
$RECIPIENT_MOBILES = ['7676892615', '9148759512', '8925537800', '8880080900','7090532778','9980770162','8951949270','8925537891'];
$DLT_NAME          = 'Lawyer';      // {#var#1}
$DLT_DATE_FORMAT   = 'Y-m-d';       // {#var#3} format expected by DLT

/* ---------- Window ---------- */
$today   = new DateTime('today');
$max     = (clone $today)->modify('+3 days');
$todayStr = $today->format('Y-m-d');
$maxStr   = $max->format('Y-m-d');

/* ---------- Fetch cases ---------- */
$sql = "
    SELECT id, name, date
    FROM cases
    WHERE status = 'Open'
      AND date BETWEEN ? AND ?
    ORDER BY date ASC, id ASC
";

$cases = [];
$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    fail_json("DB prepare failed", ['mysqli_error' => mysqli_error($con)]);
}
mysqli_stmt_bind_param($stmt, 'ss', $todayStr, $maxStr);
if (!mysqli_stmt_execute($stmt)) {
    fail_json("DB execute failed", ['mysqli_error' => mysqli_error($con)]);
}
$res = mysqli_stmt_get_result($stmt);
if ($res === false) {
    fail_json("DB get_result failed", ['mysqli_error' => mysqli_error($con)]);
}
while ($row = mysqli_fetch_assoc($res)) {
    $cases[] = $row;
}
mysqli_stmt_close($stmt);

if (!$cases) {
    log_line("No open cases in window {$todayStr}..{$maxStr}");
    echo json_encode([
        'ok' => true,
        'window' => [$todayStr, $maxStr],
        'cases_found' => 0,
        'sent' => 0,
        'failed' => [],
        'detail' => []
    ]);
    exit;
}

/* ---------- Send SMS ---------- */
$totalSent = 0;
$failed    = [];
$detail    = [];

foreach ($cases as $c) {
    $caseName = (string)$c['name'];         // {#var#2}
    $dbDate   = (string)$c['date'];
    $dateForDLT = (new DateTime($dbDate))->format($DLT_DATE_FORMAT);

    $recList = [];
    foreach ($RECIPIENT_MOBILES as $mobile) {
        $okFlag = false;
        try {
            $sms = new SMS($mobile);
            // Must return true/false
            $okFlag = $sms->case_notification($DLT_NAME, $caseName, $dateForDLT) ? true : false;
        } catch (Throwable $e) { // If PHP < 7, change to Exception
            log_line("EXCEPTION to {$mobile} | case={$caseName} date={$dateForDLT} | ".$e->getMessage());
            $okFlag = false;
        }

        if ($okFlag) {
            $totalSent++;
            log_line("SMS OK to {$mobile} | case={$caseName} date={$dateForDLT}");
        } else {
            $failed[] = $mobile;
            log_line("SMS FAIL to {$mobile} | case={$caseName} date={$dateForDLT}");
        }
        $recList[] = ['mobile' => $mobile, 'ok' => $okFlag];
    }

    $detail[] = [
        'case_id'   => (int)$c['id'],
        'case_name' => $caseName,
        'date'      => $dbDate,
        'recipients'=> $recList,
    ];
}

/* ---------- Output ---------- */
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok'          => true,
    'window'      => [$todayStr, $maxStr],
    'cases_found' => count($cases),
    'sent'        => $totalSent,
    'failed'      => array_values(array_unique($failed)),
    'detail'      => $detail
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

