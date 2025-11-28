<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

$type = $_SESSION['usertype'] ?? '';
if ($type === 'Master'){
  include("header.php");
  include("menumaster.php");
} elseif ($type === 'Software') {
  include("header.php");
  include("menuSoftware.php");
} else {
  include("logout.php");
  exit;
}
include("dbConnection.php");

/* -------------------------
   Helpers & Filters (GET)
-------------------------- */
function safe_date($d) { return (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$d)) ? $d : null; }

$today = date('Y-m-d');
$first_of_month = date('Y-m-01');
$from = safe_date($_GET['from'] ?? $first_of_month) ?: $first_of_month;
$to   = safe_date($_GET['to']   ?? $today)          ?: $today;
// exclusive upper bound (UI inclusive)
$to_exclusive = date('Y-m-d', strtotime($to . ' +1 day'));

// Single branch (string) or 'ALL'
$branch_id = '';
if (isset($_GET['branch'])) {
  $branch_id = trim((string)$_GET['branch']);
}

$range_label = date('M j, Y', strtotime($from)) . ' – ' . date('M j, Y', strtotime($to));

/* -------------------------
   Branch list (status = 1)
-------------------------- */
$branches = [];
$bres = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE status = 1 ORDER BY branchName");
if ($bres) {
  while ($b = mysqli_fetch_assoc($bres)) { $branches[] = $b; }
  mysqli_free_result($bres);
}

/* -------------------------
   Query Filters
-------------------------- */
$branch_filter_sql = '';
if ($branch_id !== '' && strtolower($branch_id) !== 'all') {
  $branch_filter_sql = " AND b.branchId = '" . mysqli_real_escape_string($con, $branch_id) . "' ";
}

/* -------------------------
   SUMMARY PIVOT with FILE LISTS
   (Status filter updated for Acc-Approved)
-------------------------- */
$sql = "
  SELECT
    b.branchId,
    b.branchName,

    SUM(CASE WHEN c.norm_type='Electricity' THEN c.amt END) AS Electricity,
    GROUP_CONCAT(CASE WHEN c.norm_type='Electricity' AND c.file_name<>'' THEN c.file_name END) AS ElectricityFiles,

    SUM(CASE WHEN c.norm_type='Rent'        THEN c.amt END) AS Rent,
    GROUP_CONCAT(CASE WHEN c.norm_type='Rent' AND c.file_name<>'' THEN c.file_name END) AS RentFiles,

    SUM(CASE WHEN c.norm_type='Internet'    THEN c.amt END) AS Internet,
    GROUP_CONCAT(CASE WHEN c.norm_type='Internet' AND c.file_name<>'' THEN c.file_name END) AS InternetFiles,

    SUM(CASE WHEN c.norm_type='Water'       THEN c.amt END) AS Water,
    GROUP_CONCAT(CASE WHEN c.norm_type='Water' AND c.file_name<>'' THEN c.file_name END) AS WaterFiles

  FROM branch b
  LEFT JOIN (
    SELECT
      e.branchCode,
      COALESCE(
        STR_TO_DATE(e.date, '%Y-%m-%d'),
        STR_TO_DATE(e.date, '%d-%m-%Y'),
        STR_TO_DATE(e.date, '%d/%m/%Y')
      ) AS dt,
      CASE
        WHEN e.amount REGEXP '^[0-9., ]+$'
          THEN CAST(REPLACE(REPLACE(e.amount, ',', ''), ' ', '') AS DECIMAL(12,2))
        ELSE NULL
      END AS amt,
      CASE
        WHEN TRIM(LOWER(REPLACE(e.type, '>', ''))) IN
          ('electricity bill','electricity bills','electricity expense','electricity expenses',
           'electric bill','electric bills','electricity') THEN 'Electricity'
        WHEN TRIM(LOWER(REPLACE(e.type, '>', ''))) IN
          ('office rent','rent','rent expense','shop rent','branch rent','office rental') THEN 'Rent'
        WHEN TRIM(LOWER(REPLACE(e.type, '>', ''))) IN
          ('internet bill','internet bills','internet expense','internet expenses',
           'internet','broadband','broadband bill') THEN 'Internet'
        WHEN TRIM(LOWER(REPLACE(e.type, '>', ''))) IN
          ('water bill','water bills','water expense','water expenses','water') THEN 'Water'
        ELSE NULL
      END AS norm_type,
      UPPER(TRIM(COALESCE(e.status, ''))) AS status_up,
      UPPER(REPLACE(TRIM(COALESCE(e.status, '')), '-', ' ')) AS status_up_nh,
      TRIM(COALESCE(e.file, '')) AS file_name
    FROM expense e
  ) c
    ON c.branchCode = b.branchId
   AND c.dt IS NOT NULL
   AND c.norm_type IS NOT NULL
   AND (c.status_up = 'ACC-APPROVED' OR c.status_up_nh = 'ACC APPROVED' OR c.status_up_nh = 'APPROVED')
   AND c.dt >= '".mysqli_real_escape_string($con, $from)."'
   AND c.dt <  '".mysqli_real_escape_string($con, $to_exclusive)."'
  WHERE b.status = 1
  $branch_filter_sql
  GROUP BY b.branchId, b.branchName
  ORDER BY b.branchName
";

$res  = mysqli_query($con, $sql);
$rows = [];
if ($res) {
  while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
  mysqli_free_result($res);
} else {
  $err = mysqli_error($con);
}

/* Default UI selection = All */
$select_all_default = ($branch_id === '' || strtolower($branch_id) === 'all');
?>
<style>
  :root{
    --brand:#123C69;
    --bg:#f5f5f5;
  }
  #wrapper{background:var(--bg);}
  h3.text-success{color:var(--brand);text-transform:uppercase;font-weight:600;font-size:18px;}
  .theadRow{background:var(--brand)!important;color:#f2f2f2;text-transform:uppercase;font-size:11px;}
  .panel-body{
    border:5px solid #fff;border-radius:12px;padding:16px;background:var(--bg);
    box-shadow:rgba(50,50,93,.25) 0 30px 60px -20px, rgba(0,0,0,.3) 0 18px 36px -18px, rgba(10,37,64,.35) 0 -2px 6px 0 inset;
  }

  .filter-card{background:#fff;border-radius:12px;padding:12px;margin-bottom:12px;border:1px solid #e8e8e8;}
  .filter-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:10px;align-items:end;}
  .f-branch{grid-column:span 4;}
  .f-from{grid-column:span 3;}
  .f-to{grid-column:span 3;}
  .f-apply{grid-column:span 2;}
  label{font-weight:600;margin-bottom:4px;}
  .filters .form-control{height:36px;}
  .filters .btn{height:36px;}

  .table>tbody>tr>td{text-align:right;font-weight:600; vertical-align:top;}
  .table>tbody>tr>td:first-child,
  .table>tbody>tr>td:nth-child(2){text-align:left;}
  .no-expense{ font-weight:700; color:#a33; }
  #expensesPivot thead th{position:sticky;top:0;z-index:1;}
  .amount-cell a{ display:inline-block; margin-top:4px; font-weight:600; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-lg-12">
        <div class="hpanel">
          <div class="panel-heading">
            <h3 class="text-success">
              <i class="fa-Icon fa fa-eye"></i>
              Branch-wise Approved Expenses (<?php echo htmlspecialchars($range_label); ?>)
            </h3>
          </div>

          <div class="panel-body">
            <!-- Filter Card -->
            <div class="filter-card">
              <form method="get" class="filters">
                <div class="filter-grid">
                  <div class="f-branch">
                    <label>Branch</label>
                    <select id="branchSelect" name="branch" class="form-control">
                      <option value="all" <?php echo $select_all_default ? 'selected' : ''; ?>>All Branches</option>
                      <?php foreach ($branches as $b):
                        $bid = (string)$b['branchId'];
                        $selected = (!$select_all_default && $branch_id === $bid) ? 'selected' : '';
                      ?>
                        <option value="<?php echo htmlspecialchars($bid); ?>" <?php echo $selected; ?>>
                          <?php echo htmlspecialchars($b['branchName']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="f-from">
                    <label>From</label>
                    <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($from); ?>" required>
                  </div>
                  <div class="f-to">
                    <label>To</label>
                    <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($to); ?>" required>
                  </div>
                  <div class="f-apply">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply</button>
                  </div>
                </div>
              </form>
            </div>

            <?php if (!empty($err)): ?>
              <div class="alert alert-danger">Query failed: <?php echo htmlspecialchars($err); ?></div>
            <?php endif; ?>

            <!-- SUMMARY PIVOT -->
            <div class="table-responsive">
              <table id="expensesPivot" class="table table-striped table-bordered" style="width:100%;">
                <thead class="theadRow">
                  <tr>
                    <th>#</th>
                    <th>BRANCH NAME</th>
                    <th class="text-center">ELECTRICITY</th>
                    <th class="text-center">RENT</th>
                    <th class="text-center">INTERNET</th>
                    <th class="text-center">WATER</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($rows)): $i=1; foreach ($rows as $r):
                    // Numeric amounts
                    $elec = array_key_exists('Electricity',$r) ? (is_null($r['Electricity'])? null : (float)$r['Electricity']) : null;
                    $rent = array_key_exists('Rent',$r)        ? (is_null($r['Rent'])? null : (float)$r['Rent']) : null;
                    $net  = array_key_exists('Internet',$r)    ? (is_null($r['Internet'])? null : (float)$r['Internet']) : null;
                    $wat  = array_key_exists('Water',$r)       ? (is_null($r['Water'])? null : (float)$r['Water']) : null;

                    // File lists (may be comma-separated)
                    $elecFiles = isset($r['ElectricityFiles']) ? (string)$r['ElectricityFiles'] : '';
                    $rentFiles = isset($r['RentFiles'])        ? (string)$r['RentFiles']        : '';
                    $netFiles  = isset($r['InternetFiles'])    ? (string)$r['InternetFiles']    : '';
                    $watFiles  = isset($r['WaterFiles'])       ? (string)$r['WaterFiles']       : '';

                    // Render helper: amount + first file link (if any)
                    $renderCell = function($amt, $filesCsv) {
                      if (is_null($amt) || $amt <= 0) {
                        return '<span class="no-expense">entry not added</span>';
                      }
                      $html = number_format((float)$amt, 2);
                      $filesCsv = trim((string)$filesCsv);
                      if ($filesCsv !== '') {
                        // pick the first non-empty filename
                        $parts = array_values(array_filter(array_map('trim', explode(',', $filesCsv)), function($x){ return $x !== ''; }));
                        if (!empty($parts)) {
                          $first = basename($parts[0]); // sanitize
                          $href  = 'ExpenseDocuments/' . rawurlencode($first);
                          $html .= '<br><a class="amount-file" href="'.$href.'" target="_blank" rel="noopener">View file</a>';
                        }
                      }
                      return $html;
                    };
                  ?>
                    <tr>
                      <td><?php echo $i++; ?></td>
                      <td><?php echo htmlspecialchars($r['branchName']); ?></td>
                      <td class="amount-cell"><?php echo $renderCell($elec, $elecFiles); ?></td>
                      <td class="amount-cell"><?php echo $renderCell($rent, $rentFiles); ?></td>
                      <td class="amount-cell"><?php echo $renderCell($net,  $netFiles);  ?></td>
                      <td class="amount-cell"><?php echo $renderCell($wat,  $watFiles);  ?></td>
                    </tr>
                  <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center" style="font-weight:600;">No branches found with the current filters</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div><!-- /.panel-body -->
        </div><!-- /.hpanel -->
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</div>

<!-- DataTables + Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
  (function(){
    if ($('#expensesPivot').length && $.fn.DataTable) {
      var title = 'Branch-wise Approved Expenses (<?php echo addslashes($range_label); ?>)';
      $('#expensesPivot').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
          { extend: 'csvHtml5', title: title, exportOptions: { columns: [0,1,2,3,4,5] } },
          { extend: 'print',   title: title, exportOptions: { columns: [0,1,2,3,4,5] } },
          { extend: 'pdfHtml5', title: title, orientation: 'landscape', pageSize: 'A4',
            exportOptions: { columns: [0,1,2,3,4,5] },
            customize: function (doc) {
              var objLayout = {};
              objLayout['hLineWidth'] = function(i) { return .5; };
              objLayout['vLineWidth'] = function(i) { return .5; };
              objLayout['hLineColor'] = function(i) { return '#aaa'; };
              objLayout['vLineColor'] = function(i) { return '#aaa'; };
              objLayout['paddingLeft'] = function(i) { return 6; };
              objLayout['paddingRight'] = function(i) { return 6; };
              objLayout['paddingTop'] = function(i) { return 4; };
              objLayout['paddingBottom'] = function(i) { return 4; };
              if (doc.content && doc.content[1]) doc.content[1].layout = objLayout;
            }
          }
        ]
      });
    }
  })();
</script>

