<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'] ?? '';

/* ------ Role-based chrome (kept as-is) ------ */
if ($type == 'Master') { include("header.php"); include("menumaster.php"); }
else if ($type == 'SundayUser') { include("header.php"); include("menuSundayUser.php"); }
else if ($type == 'Zonal') { include("header.php"); include("menuZonal.php"); }
else if ($type == 'SubZonal') { include("header.php"); include("menuSubZonal.php"); }
else if ($type == 'ZonalMaster') { include("header.php"); include("menuzonalMaster.php"); }
else if ($type == 'Software') { include("header.php"); include("menuSoftware.php"); }
else { include("logout.php"); exit; }

include("dbConnection.php");
@mysqli_set_charset($con, 'utf8mb4');

/* ---------- Date Logic ---------- */
$today    = date('Y-m-d');
$MIN_FROM = '2025-11-05';  // fixed minimum From date

$from = isset($_GET['from']) ? $_GET['from'] : $today;
$to   = isset($_GET['to'])   ? $_GET['to']   : $today;

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = $today;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = $today;

if (strtotime($from) < strtotime($MIN_FROM)) $from = $MIN_FROM;
if (strtotime($from) > strtotime($to)) $to = $from;

/* ---------- JSON GrossW extractor expression ---------- */
$GROSSW = "CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(EC.extra,'$.GrossW')),''),'0') AS DECIMAL(12,3))";
?>
<style>
  #wrapper h3{
    text-transform:uppercase;
    font-weight:600;
    font-size:20px;
    color:#123C69;
  }
  thead { text-transform:uppercase; background-color:#123C69; color:#f2f2f2; }
  .text-success{ font-weight:600; color:#123C69; }
  .hpanel .panel-body{ padding:5px 15px; }
  .filters{ margin:10px 0 15px; }
  .filters .form-inline .form-control{ height:30px; padding:3px 8px; }
  .mono{ font-family:Consolas,Monaco,'Courier New',monospace; }

  /* ---------- Layout fixes (no horizontal scroll, thinner # column) ---------- */
  .table-responsive { overflow-x: visible; }
  #example5 { width: 100%; table-layout: fixed; }
  #example5 th, #example5 td { white-space: normal; word-break: break-word; }

  /* Keep numeric/short columns on one line for neatness */
  #example5 th:nth-child(2),  #example5 td:nth-child(2),
  #example5 th:nth-child(4),  #example5 td:nth-child(4),
  #example5 th:nth-child(6),  #example5 td:nth-child(6),
  #example5 th:nth-child(7),  #example5 td:nth-child(7),
  #example5 th:nth-child(8),  #example5 td:nth-child(8),
  #example5 th:nth-child(9),  #example5 td:nth-child(9),
  #example5 th:nth-child(10), #example5 td:nth-child(10) {
    white-space: nowrap;
  }

  /* Narrow serial column */
  #example5 th.serial, #example5 td.serial {
    width: 50px;
    max-width: 60px;
    text-align: right;
    white-space: nowrap;
  }

  /* Allow Assigned Branches (col 5) to wrap nicely */
  #example5 th.assigned-branches, #example5 td.assigned-branches {
    white-space: normal;
    word-break: break-word;
    max-width: 420px;
  }

  /* DataTables polish */
  .dataTables_filter { float: right; }
  .dataTables_filter input { max-width: 180px; }
  .dataTables_info { padding-top: 6px; }
  .dataTables_paginate { margin-top: 2px; }
</style>

<div id="wrapper">
  <div class="row content">
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="col-xs-12">
          <h3 class="text-success">Branch Manager COG</h3>
        </div>
        <div style="clear:both"></div>

        <div class="panel-body">
          <!-- Date Filters -->
          <form method="get" class="filters form-inline">
            <label class="text-success">From:&nbsp;</label>
            <input
              type="date"
              name="from"
              class="form-control"
              value="<?php echo htmlspecialchars($from); ?>"
              min="<?php echo htmlspecialchars($MIN_FROM); ?>"
              max="<?php echo htmlspecialchars($today); ?>"
            />
            &nbsp;&nbsp;
            <label class="text-success">To:&nbsp;</label>
            <input
              type="date"
              name="to"
              class="form-control"
              value="<?php echo htmlspecialchars($to); ?>"
              min="<?php echo htmlspecialchars($from); ?>"
              max="<?php echo htmlspecialchars($today); ?>"
            />
            &nbsp;&nbsp;
            <button type="submit" class="btn btn-sm btn-default">Apply</button>
            <?php if (!($from === $today && $to === $today)): ?>
              &nbsp;<a href="?from=<?php echo $today; ?>&to=<?php echo $today; ?>" class="btn btn-sm btn-link">Today</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table id="example5" class="table table-bordered">
              <thead>
                <tr>
                  <th class="serial">#</th>
                  <th>BM EMPID</th>
                  <th>BM NAME</th>
                  <th>BM CONTACT</th>
                  <th class="assigned-branches">BRANCHES</th>
                  <th>BILLED</th>
                  <th>WALKIN</th>
                  <th>COG %</th>
                  <th>BILLED GWT SUM</th>
                  <th>ENQUIRY GWT SUM</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;

                /* 
                  Join branch table to fetch names for EC.branch (branchId).
                  Display as "AGPL003 (BENGALURU MG ROAD)" etc.
                */
                $sql = "
                  SELECT
                    EC.BMId AS bmEmpId,
                    E.name AS bmName,
                    E.contact AS bmContact,
                    GROUP_CONCAT(
                      DISTINCT CONCAT(
                        COALESCE(B.branchName, EC.branch), '(', EC.branch, ')'
                      )
                      ORDER BY EC.branch
                      SEPARATOR ', '
                    ) AS assigned_branches,
                    SUM(CASE WHEN EC.status IN ('release','billed') THEN 1 ELSE 0 END) AS rb_count,
                    SUM(CASE WHEN EC.status IN ('release','billed','enquiry') THEN 1 ELSE 0 END) AS total_count,
                    ROUND(
                      CASE WHEN SUM(CASE WHEN EC.status IN ('release','billed','enquiry') THEN 1 ELSE 0 END)=0
                        THEN 0
                      ELSE SUM(CASE WHEN EC.status IN ('release','billed') THEN 1 ELSE 0 END)*100.0/
                           SUM(CASE WHEN EC.status IN ('release','billed','enquiry') THEN 1 ELSE 0 END)
                      END, 2
                    ) AS cog_percent,
                    ROUND(SUM(CASE WHEN EC.status IN ('release','billed') THEN {$GROSSW} ELSE 0 END), 3) AS rb_grosswt_sum,
                    ROUND(SUM(CASE WHEN EC.status = 'enquiry'           THEN {$GROSSW} ELSE 0 END), 3) AS enq_grosswt_sum
                  FROM everycustomer EC
                  LEFT JOIN employee E ON E.empId = CAST(EC.BMId AS CHAR)
                  LEFT JOIN branch   B ON B.branchId = EC.branch
                  WHERE EC.BMId IS NOT NULL
                    AND EC.BMId <> 0
                    AND EC.branch IS NOT NULL
                    AND EC.branch <> ''
                    AND EC.status IN ('release','billed','enquiry')
                    AND EC.date BETWEEN '$from' AND '$to'
                  GROUP BY EC.BMId, E.name, E.contact
                  HAVING total_count > 0
                  ORDER BY cog_percent DESC, rb_count DESC, bmEmpId ASC
                ";

                $res = mysqli_query($con, $sql);
                if ($res) {
                  while ($r = mysqli_fetch_assoc($res)) {
                    $bmEmpId   = $r['bmEmpId'] ?: 'N/A';
                    $bmName    = $r['bmName'] ?: 'N/A';
                    $bmContact = $r['bmContact'] ?: 'N/A';
                    $branches  = $r['assigned_branches'] ?: '—';
                    $rb        = (int)$r['rb_count'];
                    $tot       = (int)$r['total_count'];
                    $cog       = number_format((float)$r['cog_percent'], 2);
                    $rbGW      = number_format((float)$r['rb_grosswt_sum'], 3);
                    $enqGW     = number_format((float)$r['enq_grosswt_sum'], 3);

                    echo "<tr>
                      <td class='serial'>{$i}</td>
                      <td class='mono'>".htmlspecialchars($bmEmpId)."</td>
                      <td>".htmlspecialchars($bmName)."</td>
                      <td class='mono'>".htmlspecialchars($bmContact)."</td>
                      <td class='assigned-branches mono'>".htmlspecialchars($branches)."</td>
                      <td>{$rb}</td>
                      <td>{$tot}</td>
                      <td>{$cog}%</td>
                      <td class='mono'>{$rbGW}</td>
                      <td class='mono'>{$enqGW}</td>
                    </tr>";
                    $i++;
                  }
                } else {
                  echo "<tr><td colspan='10'>Query Error: ".htmlspecialchars(mysqli_error($con))."</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
  
<?php include("footer.php"); ?>

</div>

