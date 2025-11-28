<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Legal') {
    include("header.php");
    include("menulegal.php");
} else {
    include("logout.php");
}
include("dbConnection.php");

/* -------------------- POST HANDLERS (same file) -------------------- */
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD new case (status defaults to Open)
    if (isset($_POST['submitCase'])) {
        $name     = trim($_POST['caseName'] ?? '');
        $date     = trim($_POST['caseDate'] ?? '');
        $content  = trim($_POST['caseContent'] ?? '');
        $remarks  = trim($_POST['caseRemarks'] ?? '');
        $status   = 'Open'; // default

        if ($name !== '' && $date !== '' && $content !== '') { // remarks can be empty
            $sql = "INSERT INTO cases (name, date, content, remarks, status) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssss", $name, $date, $content, $remarks, $status);
                if (mysqli_stmt_execute($stmt)) $msg = 'Case added';
                else $msg = 'Insert failed';
                mysqli_stmt_close($stmt);
            } else {
                $msg = 'DB error';
            }
        } else {
            $msg = 'Invalid input';
        }
    }

    // UPDATE existing case
    if (isset($_POST['updateCase'])) {
        $id       = (int)($_POST['caseId'] ?? 0);
        $name     = trim($_POST['caseName'] ?? '');
        $date     = trim($_POST['caseDate'] ?? '');
        $content  = trim($_POST['caseContent'] ?? '');
        $remarks  = trim($_POST['caseRemarks'] ?? '');
        $status   = ($_POST['caseStatus'] ?? 'Open') === 'Closed' ? 'Closed' : 'Open';

        if ($id > 0 && $name !== '' && $date !== '' && $content !== '') {
            $sql = "UPDATE cases SET name = ?, date = ?, content = ?, remarks = ?, status = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssi", $name, $date, $content, $remarks, $status, $id);
                if (mysqli_stmt_execute($stmt)) $msg = 'Case updated';
                else $msg = 'Update failed';
                mysqli_stmt_close($stmt);
            } else {
                $msg = 'DB error';
            }
        } else {
            $msg = 'Invalid input';
        }
    }
}
?>
<style>
    #wrapper h3 { text-transform: uppercase; font-weight: 600; font-size: 16px; color: #123C69; }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color: #fffafa; }
    .text-success { color: #123C69; text-transform: uppercase; font-weight: 600; }
    .btn-primary { background-color: #123C69; }
    .theadRow { text-transform: uppercase; background-color: #123C69 !important; color: #f2f2f2; font-size: 11px; }
    .btn-success{
        display:inline-block;padding:0.7em 1.4em;margin:0 0.3em 0.3em 0;border-radius:0.15em;box-sizing:border-box;
        text-decoration:none;font-size:12px;font-family:'Roboto',sans-serif;text-transform:uppercase;color:#fffafa;
        background-color:#123C69;box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);text-align:center;position:relative;
    }
    .fa_Icon { color:#990000; }
    #wrapper .panel-body { box-shadow:10px 15px 15px #999; background-color:#f5f5f5; border-radius:3px; padding:15px; }
    .table-responsive .row { margin:0px; }

    /* Row status colors */
    .row-closed { background-color: #e7f5ec !important; }   /* green-ish */
    .row-open-past { background-color: #fdeaea !important; }/* red-ish */
    .row-open-soon { background-color: #fff8e1 !important; }/* yellow-ish */

    /* Status pill */
    .status-pill { display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; }
    .status-open  { background:#fff1f1; color:#b40000; border:1px solid #f0caca; }
    .status-closed{ background:#eaf7f0; color:#116d3b; border:1px solid #c7e9d4; }
</style>

<!--   ADD CASE MODAL   -->
<div class="modal fade" id="addCaseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:1050px;">
        <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
                <h3>ADD NEW CASE</h3>
            </div>
            <div class="modal-body" style="padding-right: 40px; background-color: #f5f5f5;">
                <form method="POST" class="form-horizontal" action="">
                    <div class="row content">
                        <div class="col-sm-6">
                            <label class="text-success">Case NAME</label>
                            <input type="text" name="caseName" class="form-control" autocomplete="off"  placeholder="Case name, Court City"  required>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-success">Case Date</label>
                            <input type="date" name="caseDate" class="form-control" autocomplete="off" required>
                        </div>
                        <label class="col-sm-12 control-label"><br></label>
                        <div class="col-sm-12">
                            <label class="text-success">Case Content</label>
                            <textarea name="caseContent" class="form-control" autocomplete="off"  placeholder="Case Details"  required></textarea>
                        </div>
                        <div class="col-sm-12">
                            <label class="text-success">Case Remarks</label>
                            <textarea name="caseRemarks" class="form-control"  placeholder="Case Updates on each hearing"  autocomplete="off"></textarea>
                        </div>
                        <!-- Status defaults to Open (hidden) -->
                        <input type="hidden" name="caseStatus" value="Open">
                        <div class="col-sm-9" align="right" style="padding-top:22px;">
                            <button class="btn btn-success" name="submitCase" type="submit">
                                <span style="color:#ffcf40" class="fa fa-plus"></span> ADD Case
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
</div>

<!--   EDIT CASE MODAL   -->
<div class="modal fade" id="editCaseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:1050px;">
        <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
                <h3>UPDATE CASE</h3>
            </div>
            <div class="modal-body" style="padding-right: 40px; background-color: #f5f5f5;">
                <form method="POST" class="form-horizontal" action="">
                    <input type="hidden" name="caseId" id="edit_caseId">
                    <div class="row content">
                        <div class="col-sm-6">
                            <label class="text-success">Case NAME</label>
                            <input type="text" name="caseName" id="edit_caseName" class="form-control" autocomplete="off" required placeholder="Case name, Court name" >
                        </div>
                        <div class="col-sm-6">
                            <label class="text-success">Case Date</label>
                            <input type="date" name="caseDate" id="edit_caseDate" class="form-control" autocomplete="off" required>
                        </div>
                        <label class="col-sm-12 control-label"><br></label>
                        <div class="col-sm-12">
                            <label class="text-success">Case Content</label>
                            <textarea name="caseContent" id="edit_caseContent" class="form-control" autocomplete="off" required placeholder="Case details" ></textarea>
                        </div>
                        <div class="col-sm-12">
                            <label class="text-success">Case Remarks</label>
                            <textarea name="caseRemarks" id="edit_caseRemarks" class="form-control" autocomplete="off" placeholder="Case Updates on each hearing"  ></textarea>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-success">Case Status</label>
                            <select name="caseStatus" id="edit_caseStatus" class="form-control" required>
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-sm-9" align="right" style="padding-top:22px;">
                            <button class="btn btn-success" name="updateCase" type="submit">
                                <span style="color:#ffcf40" class="fa fa-save"></span> UPDATE Case
                            </button>
                        </div>
                    </div><!-- /.row -->
                </form>
            </div><!-- /.modal-body -->
        </div>
    </div>
    <div style="clear:both"></div>
</div>

<div id="wrapper">
    <div class="row content">

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> Case DETAILS</b>
                        </h3>
                    </div>
                    <div class="card-body container-fluid" style="margin-top:24px;padding:0px;align:right">
                        <div class="col-lg-2">
                            <a data-toggle="modal" data-target="#addCaseModal">
                                <div class="panel-body text-center" style="margin-bottom:0px">
                                    <h3 class="m-xs" style="color: #990000;">
                                        <i class='fa fa-plus'></i>
                                    </h3>
                                    <h5 class="font-extra-bold no-margins text-success">
                                        ADD Case
                                    </h5>
                                </div>
                            </a>
                        </div>
                        <?php if ($msg !== ''): ?>
                        <div class="col-lg-10">
                            <div class="alert alert-info" style="margin-top:10px;"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-hover table-bordered">
                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
                                    <th>Case Name</th>
                                    <th>Case Date</th>
                                    <th>Content</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                    <th style="text-align:center;">EDIT</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
$i = 1;
$today = new DateTime('today');
$soon  = (clone $today)->modify('+7 days'); // coming week

$sql = mysqli_query($con, "SELECT id, name, date, content, remarks, status FROM cases ORDER BY `date` DESC");
while ($row = mysqli_fetch_assoc($sql)) {
    $id      = (int)$row['id'];
    $name    = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $dateStr = htmlspecialchars($row['date'] ?? '', ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($row['content'] ?? '', ENT_QUOTES, 'UTF-8');
    $remarks = htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES, 'UTF-8');
    $status  = ($row['status'] === 'Closed') ? 'Closed' : 'Open'; // normalize

    // previews
    $prevContent = (function($t){ return mb_strlen($t) > 80 ? mb_substr($t, 0, 80) . '…' : $t; })($content);
    $prevRemarks = (function($t){ return mb_strlen($t) > 80 ? mb_substr($t, 0, 80) . '…' : $t; })($remarks);

    // compute row class
    $rowClass = '';
    try {
        $caseDate = new DateTime($row['date']);
        if ($status === 'Closed') {
            $rowClass = 'row-closed';
        } else { // Open
            if ($caseDate < $today) {
                $rowClass = 'row-open-past';
            } elseif ($caseDate >= $today && $caseDate <= $soon) {
                $rowClass = 'row-open-soon';
            }
        }
    } catch (Exception $e) { /* ignore invalid date */ }

    $statusPill = $status === 'Closed'
        ? '<span class="status-pill status-closed">Closed</span>'
        : '<span class="status-pill status-open">Open</span>';

    echo "<tr class='{$rowClass}'>";
    echo "<td>{$i}</td>";
    echo "<td>{$name}</td>";
    echo "<td>{$dateStr}</td>";
    echo "<td title=\"{$content}\">{$prevContent}</td>";
    echo "<td title=\"{$remarks}\">{$prevRemarks}</td>";
    echo "<td>{$statusPill}</td>";
    echo "<td style='text-align:center'>
            <button 
              class='btn btn-link p-0 edit-case-btn'
              type='button'
              data-toggle='modal'
              data-target='#editCaseModal'
              data-id='{$id}'
              data-name='{$name}'
              data-date='{$dateStr}'
              data-content='".htmlspecialchars($row['content'] ?? '', ENT_QUOTES, 'UTF-8')."'
              data-remarks='".htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES, 'UTF-8')."'
              data-status='{$status}'
              title='Edit Case'
            >
              <i class='fa fa-pencil-square-o text-success' style='font-size:16px'></i>
            </button>
          </td>";
    echo "</tr>";
    $i++;
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

<!-- Fill Edit Modal -->
<script>
  // Requires jQuery + Bootstrap
  $('#editCaseModal').on('show.bs.modal', function (event) {
    var button  = $(event.relatedTarget);
    var id      = button.data('id') || '';
    var name    = button.data('name') || '';
    var date    = button.data('date') || '';
    var content = button.data('content') || '';
    var remarks = button.data('remarks') || '';
    var status  = button.data('status') || 'Open';

    var modal = $(this);
    modal.find('#edit_caseId').val(id);
    modal.find('#edit_caseName').val(name);
    modal.find('#edit_caseDate').val(date);
    modal.find('#edit_caseContent').val(content);
    modal.find('#edit_caseRemarks').val(remarks);
    modal.find('#edit_caseStatus').val(status);
  });
</script>

