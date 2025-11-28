<?php
// IMPORTANT: keep this file free of BOM/leading spaces. Headers must be sent first.
error_reporting(E_ERROR | E_PARSE);
session_start();
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/dbConnection.php'; // defines $con (mysqli)
@mysqli_set_charset($con, 'utf8mb4');

/* ---------- Config: database names ---------- */
$DB_CALLCENTER = 'alpha_attica';
$DB_BILLING    = 'atticaaws';

/* ---------- Common: default date = yesterday ---------- */
$prevDate = date('Y-m-d', strtotime('-1 day'));

/* ---------- Normalise From / To dates ---------- */
$rawFrom = isset($_GET['from']) ? trim($_GET['from']) : $prevDate;
$rawTo   = isset($_GET['to'])   ? trim($_GET['to'])   : $prevDate;

$fromDate = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFrom)) ? $rawFrom : $prevDate;
$toDate   = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTo))   ? $rawTo   : $prevDate;

// Ensure fromDate <= toDate
if ($fromDate > $toDate) {
    $tmp      = $fromDate;
    $fromDate = $toDate;
    $toDate   = $tmp;
}

/* ---------- Export path (runs BEFORE any HTML) ---------- */
if (isset($_GET['export'])) {
    $which = $_GET['export'];

    // Range suffix for filename
    $rangeSuffix = ($fromDate === $toDate)
        ? $fromDate
        : ($fromDate . '_to_' . $toDate);

    if ($which === 'callcenter') {
        $sql      = "
            SELECT *
            FROM `{$DB_CALLCENTER}`.`cust_info`
            WHERE DATE(`created_datetime`) BETWEEN '$fromDate' AND '$toDate'
        ";
        $filename = "callcenter_{$rangeSuffix}.csv";
    } elseif ($which === 'billing') {
        $sql      = "
            SELECT *
            FROM `{$DB_BILLING}`.`trans`
            WHERE `date` BETWEEN '$fromDate' AND '$toDate'
        ";
        $filename = "billing_{$rangeSuffix}.csv";
    } else {
        http_response_code(400);
        echo "Invalid export type.";
        exit;
    }

    $rs = mysqli_query($con, $sql);

    // Clean buffers / compression to avoid corrupt headers
    while (ob_get_level()) { ob_end_clean(); }
    if (function_exists('apache_setenv')) @apache_setenv('no-gzip', '1');
    @ini_set('zlib.output_compression', 'Off');
    @header_remove('Content-Encoding');

    // Strong download headers (forces Save As on client PC)
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');

    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    $firstRow = $rs ? mysqli_fetch_assoc($rs) : null;

    if ($firstRow) {
        fputcsv($out, array_keys($firstRow));      // header
        fputcsv($out, array_values($firstRow));    // first data row
        while ($row = mysqli_fetch_assoc($rs)) {
            fputcsv($out, array_values($row));
        }
    } else {
        fputcsv($out, ['No data found from '.$fromDate.' to '.$toDate]);
    }

    fclose($out);
    exit; // prevent any further output
}

/* ---------- If we're here, render the page with form + buttons ---------- */
$type = isset($_SESSION['usertype']) ? $_SESSION['usertype'] : '';
if ($type == 'Zonal' || $type == 'Master' || $type == 'BD') {
    include('header.php');
    if ($type == 'Zonal') include('menuZonal.php');
    elseif ($type == 'BD') include('menubd.php');
    else include('menumaster.php');
} elseif ($type == 'SocialMedia') {
    include('header.php');
    include('menuSocialMedia.php');
} elseif ($type == 'MIS-Team') {
    include('header.php');
    include('menumis.php');
} else {
    include('logout.php');
    exit;
}
?>
<style>
.panel-body { text-align: center; }

/* Wrapper card */
.export-card {
  display: inline-block;
  padding: 15px 20px;
  border-radius: 8px;
  background: #f5f7fa;
  border: 1px solid #d9e2ef;
}

/* Date range layout */
.date-range-wrap {
  margin-bottom: 15px;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
}
.date-range-wrap label {
  margin: 0;
  font-weight: 600;
  color: #333;
}
.date-range-wrap input[type="date"] {
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #ccc;
  font-size: 13px;
  min-width: 145px;
}

/* Buttons */
.btn-group button {
  display: inline-block;
  margin-right: 8px;
  padding: 10px 18px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  color: #fff !important;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}
.btn-primary { background-color: #003366; border: 1px solid #00264d; }
.btn-primary:hover { background-color: #004080; }
.btn-success { background-color: #0b6623; border: 1px solid #064016; }
.btn-success:hover { background-color: #0f7a2b; }

/* Small helper to reduce form top/bottom spacing */
.export-form-inline {
  margin: 0;
}
</style>

<div id="wrapper">
  <div class="content">
    <div class="row" style="margin-bottom:10px;">
      <div class="col-lg-12" style="padding: 5px">
        <div class="hpanel">
          <div class="panel-body h-50">
            <?php
              $labelFrom = htmlspecialchars($fromDate, ENT_QUOTES, 'utf-8');
              $labelTo   = htmlspecialchars($toDate,   ENT_QUOTES, 'utf-8');
            ?>
            <div class="export-card">
              <form method="get" class="form-inline export-form-inline">
                <div class="date-range-wrap">
                  <label for="from">From:</label>
                  <input type="date" name="from" id="from" value="<?php echo $labelFrom; ?>">

                  <label for="to">To:</label>
                  <input type="date" name="to" id="to" value="<?php echo $labelTo; ?>">
                </div>

                <div class="btn-group" role="group" aria-label="Exports">
                  <button type="submit"
                          name="export"
                          value="callcenter"
                          id="btnCc"
                          class="btn-primary"
                          data-base-label="Download CC Customer CSV"
                          title="Download Callcenter CSV from <?php echo $labelFrom; ?> to <?php echo $labelTo; ?>">
                    Download CC Customer CSV (<?php echo $labelFrom . ' to ' . $labelTo; ?>)
                  </button>

                  <button type="submit"
                          name="export"
                          value="billing"
                          id="btnBilling"
                          class="btn-success"
                          data-base-label="Download Billing CSV"
                          title="Download Billing CSV from <?php echo $labelFrom; ?> to <?php echo $labelTo; ?>">
                    Download Billing CSV (<?php echo $labelFrom . ' to ' . $labelTo; ?>)
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Update button labels & titles when dates change
(function() {
  function getRangeText(from, to) {
    if (!from || !to) return '';
    return (from === to) ? from : (from + ' to ' + to);
  }

  function updateButtons() {
    var from = document.getElementById('from').value;
    var to   = document.getElementById('to').value;
    var range = getRangeText(from, to);

    var btnCc      = document.getElementById('btnCc');
    var btnBilling = document.getElementById('btnBilling');

    if (!btnCc || !btnBilling || !range) return;

    var baseCc      = btnCc.getAttribute('data-base-label')      || 'Download CC Customer CSV';
    var baseBilling = btnBilling.getAttribute('data-base-label') || 'Download Billing CSV';

    btnCc.textContent      = baseCc + ' (' + range + ')';
    btnBilling.textContent = baseBilling + ' (' + range + ')';

    btnCc.title      = baseCc + ' from ' + range;
    btnBilling.title = baseBilling + ' from ' + range;
  }

  document.addEventListener('DOMContentLoaded', function() {
    var fromEl = document.getElementById('from');
    var toEl   = document.getElementById('to');

    if (fromEl) fromEl.addEventListener('change', updateButtons);
    if (toEl)   toEl.addEventListener('change', updateButtons);

    // Ensure labels are in sync on initial load
    updateButtons();
  });
})();
</script>

