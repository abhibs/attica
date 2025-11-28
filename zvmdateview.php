<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type === 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type === 'VM-AD') {
    include("header.php");
    include("menuvmadd.php");
} else if ($type === 'Zonal') {
    include("header.php");
    include("menuZonal.php");
} else if ($type == 'ZonalMaster') {
	include("header.php");
	include("menuzonalMaster.php");
} 
else {
    include("logout.php");
}
include("dbConnection.php");

$sql       = null;
$branchName= '';
$onlyEmp   = false;

if (isset($_GET['getVMlogs'])) {
    // inputs
    $from     = trim($_GET['from']     ?? '');
    $to       = trim($_GET['to']       ?? '');
    $branchId = trim($_GET['branchId'] ?? '');
    $empId    = trim($_GET['empId']    ?? '');

    // build WHERE clauses
    $conds = [];

    if ($from && $to) {
        $f = mysqli_real_escape_string($con, $from);
        $t = mysqli_real_escape_string($con, $to);
        $conds[] = "v.date BETWEEN '$f' AND '$t'";
    }

    if ($branchId !== '') {
        $b = mysqli_real_escape_string($con, $branchId);
        $conds[] = "v.branchId LIKE '%$b%'";
        // get branchName for header
        $bn = mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT branchName FROM branch WHERE branchId='$b' LIMIT 1"
        ));
        $branchName = $bn['branchName'] ?? '';
    }

    if ($empId !== '') {
        $e = mysqli_real_escape_string($con, $empId);
        $conds[] = "v.empId = '$e'";
    }

    // detect only-employee-without-branch scenario
    if ($empId !== '' && $branchId === '') {
        $onlyEmp = true;
    }

    if ($onlyEmp) {
        // we will show each branch once, with earliest date/time
        $where = count($conds)
               ? 'WHERE ' . implode(' AND ', $conds)
               : '';
        $sql = mysqli_query($con,
            "SELECT 
               v.branchId,
               MIN(v.date) AS date_assigned,
               MIN(v.time) AS time_assigned
             FROM vm_log v
             $where
             GROUP BY v.branchId
             ORDER BY date_assigned ASC"
        );
    } else {
        // full log
        $where = count($conds)
               ? 'WHERE ' . implode(' AND ', $conds) . ' AND v.empId=e.empId'
               : 'WHERE v.empId=e.empId';
        $sql = mysqli_query($con,
            "SELECT 
               v.*, e.name
             FROM vm_log v
             JOIN employee e ON v.empId = e.empId
             $where
             ORDER BY v.date ASC"
        );
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <!-- ... your existing includes ... -->
  <style>
    .search-form .form-row { display:flex; flex-wrap:wrap; align-items:center; margin-bottom:1rem; }
    .search-form .form-group { margin-right:1rem; margin-bottom:0; }
    .search-form .form-control { max-width:180px; }
    .search-form .btn { margin-top:0.25rem; }
    .theadRow th {
      background:#123C69 !important;
      color:#f2f2f2 !important;
      font-size:11px; text-transform:uppercase;
    }
  </style>
</head>
<body>
<div id="wrapper">
  <div class="content row">
    <div class="col-lg-12">
      <div class="hpanel">
        <!-- Search -->
        <div class="panel-heading">
          <div class="row align-items-center">
            <div class="col-sm-2">
              <h3><i class="fa fa-clock-o text-success"></i> VM Logs</h3>
            </div>
            <div class="col-sm-10">
              <form class="search-form" method="get">
                <div class="form-row">
                  <div class="form-group">
                    <input list="branchList" name="branchId" class="form-control"
                           placeholder="Branch ID" value="<?= htmlentities($branchId) ?>">
                    <datalist id="branchList">
                      <?php
                      $bl = mysqli_query($con,"SELECT branchId,branchName FROM branch WHERE status=1");
                      while($r = mysqli_fetch_assoc($bl)){
                        echo "<option value=\"{$r['branchId']}\">{$r['branchName']}</option>";
                      }
                      ?>
                    </datalist>
                  </div>
                  <div class="form-group">
                    <input type="date" name="from" class="form-control" value="<?= htmlentities($from) ?>">
                  </div>
                  <span>to</span>
                  <div class="form-group">
                    <input type="date" name="to" class="form-control" value="<?= htmlentities($to) ?>">
                  </div>
                  <div class="form-group">
                    <input list="employeeList" name="empId" class="form-control"
                           placeholder="Employee ID" value="<?= htmlentities($empId) ?>">
                    <datalist id="employeeList">
                      <?php
                      $emps = mysqli_query($con,"SELECT empId,name FROM employee ORDER BY name");
                      while($e = mysqli_fetch_assoc($emps)){
                        echo "<option value=\"{$e['empId']}\">{$e['name']}</option>";
                      }
                      ?>
                    </datalist>
                  </div>
                  <div class="form-group">
                    <button type="submit" name="getVMlogs" class="btn btn-success">
                      <i class="fa fa-search"></i> Search
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Branch name heading if filtered -->
        <?php if ($branchName): ?>
        <div class="panel-heading">
          <h4 class="ml-3"><?= htmlentities($branchName) ?></h4>
        </div>
        <?php endif; ?>

        <!-- Results -->
        <div class="panel-body">
          <?php if ($onlyEmp): ?>
            <table id="example5" class="table table-striped table-bordered">
              <thead>
                <tr class="theadRow">
                  <th>#</th>
                  <th>Branch ID</th>
                  <th>Date Assigned</th>
                  <th>Time Assigned</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=1; while($r = mysqli_fetch_assoc($sql)): ?>
                  <tr>
                    <td><?= $i ?></td>
                    <td><?= htmlentities($r['branchId']) ?></td>
                    <td><?= $r['date_assigned'] ?></td>
                    <td><?= $r['time_assigned'] ?></td>
                  </tr>
                <?php $i++; endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <table id="example5" class="table table-striped table-bordered">
              <thead>
                <tr class="theadRow">
                  <th>#</th>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                  <th>Date</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($sql): $i=1; while($r = mysqli_fetch_assoc($sql)): ?>
                  <tr>
                    <td><?= $i ?></td>
                    <td><?= htmlentities($r['empId']) ?></td>
                    <td><?= htmlentities($r['name']) ?></td>
                    <td><?= $r['date'] ?></td>
                    <td><?= $r['time'] ?></td>
                  </tr>
                <?php $i++; endwhile; endif; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include("footer.php"); ?>

<script>
if (!$.fn.DataTable.isDataTable('#example5')) {
  $('#example5').DataTable({responsive:true});
}
</script>
</body>
</html>

