<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
date_default_timezone_set('Asia/Kolkata');

$type = $_SESSION['usertype'] ?? '';
if ($type === 'Goldsmith') {
  include("header.php");
  include("menugold.php");
} elseif ($type === 'Master') {
  include("header.php");
  include("menumaster.php");
} else if ($type == 'AccHead') {
  include("header.php");
  include("menuaccHeadPage.php");
} else {
  include("logout.php"); exit;
}

include('dbConnection.php');
@mysqli_set_charset($con, 'utf8mb4');

/* ---------- Date range ---------- */
$today        = date('Y-m-d');
$default_from = $today;
$from = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : $default_from;
$to   = (isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))   ? $_GET['to']   : $today;
if (strtotime($from) > strtotime($to)) { $tmp=$from; $from=$to; $to=$tmp; }

$FROM_DT = $from . ' 00:00:00';
$TO_DT   = $to   . ' 23:59:59';

/* ---------- Fetch from smelter only (with branch snapshot columns) ---------- */
$rows = [];
$sql = "SELECT
          s.id, s.packet_no,
          s.branch_name,           -- snapshot of branch name
          s.from_branch,           -- branchId saved with the record
          s.branch_gross,          -- snapshot of gross from trans
          s.branch_net,            -- snapshot of net from trans
          s.branch_purity,         -- snapshot of purity from trans
          s.before_purity, s.before_wt, s.before_netwt,
          s.after_purity,  s.after_wt,  s.after_netwt,
          s.before_img, s.after_img,
          s.created_by, s.created_at, s.mismatch
        FROM smelter s
        WHERE s.created_at BETWEEN ? AND ?
        ORDER BY s.created_at DESC";
if ($stmt = mysqli_prepare($con, $sql)) {
  mysqli_stmt_bind_param($stmt, "ss", $FROM_DT, $TO_DT);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
  mysqli_stmt_close($stmt);
}
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<style>
:root{ --header-h:70px; --footer-h:40px; }
.hpanel.stretch{ height:calc(100vh - var(--header-h) - var(--footer-h) - 14px); margin:5px; }
.hpanel.stretch .panel-body{ height:100%; display:flex; flex-direction:column; background:#fff; border-radius:10px; }
.filterbar{ display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:12px; margin:10px 0 14px; }
.filterbar .field{ display:flex; align-items:center; gap:6px; margin:0; }
.filterbar input[type="date"], .filterbar .btn{ height:34px; padding:.375rem .6rem; line-height:1.2; }
.filterbar label{ margin:0; font-weight:600; }
.table-holder{ flex:1 1 auto; min-height:0; }
.table-holder .dataTables_wrapper{ height:100%; }
.table-holder .dataTables_scroll{ height:100%; }
.table img.thumb{ max-height:48px; border:1px solid #eee; border-radius:6px; padding:2px; background:#fafafa; }
.btn-primary{ background:#990000; border-color:#990000; }
.btn-primary:hover{ background:#660000; border-color:#660000; }
.mismatch-row{ background:#fdd !important; }
@media(max-width:991px){
  #menu{position:static;width:100%;height:auto;border-right:none;}
  #wrapper{padding-left:0;}
  .hpanel.stretch{height:auto;}
}
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-lg-12" style="padding:0">
        <div class="hpanel stretch">
          <div class="panel-body">
            <h3 class="font-bold text-center" style="margin:0;">Smelter Records</h3>

            <!-- Filters -->
            <form method="get" class="filterbar">
              <div class="field">
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="<?php echo htmlspecialchars($from); ?>">
              </div>
              <div class="field">
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="<?php echo htmlspecialchars($to); ?>">
              </div>
              <button type="submit" class="btn btn-danger btn-sm">Apply</button>
              <a class="btn btn-default btn-sm" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Reset</a>
              <span class="text-muted">Showing: <?php echo htmlspecialchars($from); ?> → <?php echo htmlspecialchars($to); ?></span>
            </form>

            <!-- Table -->
            <div class="table-holder">
              <div class="table-responsive">
                <table id="smelterTable" class="table table-bordered table-striped table-hover display nowrap" style="width:100%;">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Packet No</th>

                      <!-- Branch snapshot FIRST -->
                      <th>Branch Name</th>
                      <th>From Branch (ID)</th>
                      <th>Branch Gross Wt</th>
                      <th>Branch Net Wt</th>
                      <th>Branch Purity</th>

                      <!-- BEFORE -->
                      <th>Before Purity</th>
                      <th>Before Wt</th>
                      <th>Before Net Wt</th>

                      <!-- AFTER -->
                      <th>After Purity</th>
                      <th>After Wt</th>
                      <th>After Net Wt</th>

                      <th class="no-export">Before Image</th>
                      <th class="no-export">After Image</th>
                      <th>Created By</th>
                      <th>Created At</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($rows)): foreach ($rows as $r):
                      $beforeUrl = trim($r['before_img'] ?? '');
                      $afterUrl  = trim($r['after_img']  ?? '');
                      $beforeIsImg = preg_match('/\.(jpe?g|png|gif|webp)$/i', $beforeUrl);
                      $afterIsImg  = preg_match('/\.(jpe?g|png|gif|webp)$/i', $afterUrl);
                      $rowClass = ($r['mismatch'] === 'yes') ? ' class="mismatch-row"' : '';
                    ?>
                      <tr<?php echo $rowClass; ?>>
                        <td><?php echo (int)$r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['packet_no']); ?></td>

                        <!-- Branch snapshot -->
                        <td><?php echo htmlspecialchars($r['branch_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['from_branch']); ?></td>
                        <td><?php echo htmlspecialchars($r['branch_gross']); ?></td>
                        <td><?php echo htmlspecialchars($r['branch_net']); ?></td>
                        <td><?php echo htmlspecialchars($r['branch_purity']); ?></td>

                        <!-- BEFORE -->
                        <td><?php echo htmlspecialchars($r['before_purity']); ?></td>
                        <td><?php echo htmlspecialchars($r['before_wt']); ?></td>
                        <td><?php echo htmlspecialchars($r['before_netwt']); ?></td>

                        <!-- AFTER -->
                        <td><?php echo htmlspecialchars($r['after_purity']); ?></td>
                        <td><?php echo htmlspecialchars($r['after_wt']); ?></td>
                        <td><?php echo htmlspecialchars($r['after_netwt']); ?></td>

                        <td>
                          <?php if ($beforeUrl): if ($beforeIsImg): ?>
                            <a href="<?php echo htmlspecialchars($beforeUrl); ?>" target="_blank">
                              <img class="thumb" src="<?php echo htmlspecialchars($beforeUrl); ?>" onerror="this.src='images/noimage.png';" />
                            </a>
                          <?php else: ?><a href="<?php echo htmlspecialchars($beforeUrl); ?>" target="_blank">Open file</a><?php endif; else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td>
                          <?php if ($afterUrl): if ($afterIsImg): ?>
                            <a href="<?php echo htmlspecialchars($afterUrl); ?>" target="_blank">
                              <img class="thumb" src="<?php echo htmlspecialchars($afterUrl); ?>" onerror="this.src='images/noimage.png';" />
                            </a>
                          <?php else: ?><a href="<?php echo htmlspecialchars($afterUrl); ?>" target="_blank">Open file</a><?php endif; else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['created_by']); ?></td>
                        <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
          <div class="panel-footer"><b>Attica Gold Call Center</b></div>
        </div>
      </div>
    </div>
  </div>
  <?php include('footer.php'); ?>
</div>

<!-- DataTables & Buttons JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(function(){
  var filenameBase = 'Smelter_Records_<?php
    echo preg_replace("/[^0-9-]/","",$from) . "_to_" . preg_replace("/[^0-9-]/","",$to);
  ?>';

  var table = $('#smelterTable').DataTable({
    responsive: false,
    scrollX: true,
    scrollY: 'calc(100vh - 300px)',
    scrollCollapse: true,
    pageLength: 25,
    order: [[16, 'desc']], // Created At (index of the last column)
    columnDefs: [
      { targets: [13,14], orderable: false, searchable: false } // image columns
    ],
    language: { emptyTable: "No records for this range." },
    dom: 'Bfrtip',
    buttons: [
      { extend: 'csvHtml5', title: filenameBase, filename: filenameBase, exportOptions: { columns: ':visible:not(.no-export)' }},
      { extend: 'pdfHtml5', title: 'Smelter Records (<?php echo htmlspecialchars($from); ?> → <?php echo htmlspecialchars($to); ?>)',
        filename: filenameBase, orientation: 'landscape', pageSize: 'A4',
        exportOptions: { columns: ':visible:not(.no-export)' },
        customize: function (doc) { doc.styles.tableHeader.alignment = 'left'; if (doc.content && doc.content[1]) { doc.content[1].margin = [0,10,0,0]; } }
      },
      { extend: 'print', title: 'Smelter Records' }
    ]
  });

  setTimeout(function(){ table.columns.adjust(); }, 0);
  $(window).on('resize', function(){ table.columns.adjust(); });
});
</script>
