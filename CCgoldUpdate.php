<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

include("dbConnection.php"); // $con must be a valid mysqli connection
@mysqli_set_charset($con, 'utf8mb4');

$type       = $_SESSION['usertype']       ?? '';
$employeeId = $_SESSION['login_username'] ?? '';
$name       = $_SESSION['employeeName']   ?? '';

$todayDate  = date('Y-m-d');
$nowTime    = date('H:i:s');

/* --------------------------------
   POST: Save today's Gold/Silver
--------------------------------- */
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // small helper to numeric-sanitize
    $gold_rate   = isset($_POST['gold_rate'])   ? preg_replace('/[^0-9.]/', '', $_POST['gold_rate'])   : '';
    $silver_rate = isset($_POST['silver_rate']) ? preg_replace('/[^0-9.]/', '', $_POST['silver_rate']) : '';

    $gold_rate   = ($gold_rate   === '' ? null : $gold_rate);
    $silver_rate = ($silver_rate === '' ? null : $silver_rate);

    $okAll = true;

    // Insert function
    function insertRate($con, $employeeId, $rate, $type, $todayDate, $nowTime){
        $sql = "INSERT INTO `ccgold` (employeeId, rate, type, date, time)
                VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, 'sdsss', $employeeId, $rate, $type, $todayDate, $nowTime);
            $ok = mysqli_stmt_execute($stmt);
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            return [$ok, $err];
        } else {
            return [false, mysqli_error($con)];
        }
    }

    // Insert Gold
    if ($gold_rate !== null) {
        list($ok, $err) = insertRate($con, $employeeId, (float)$gold_rate, 'Gold', $todayDate, $nowTime);
        if (!$ok) {
            $okAll = false;
            $msg .= 'Gold rate save error: ' . htmlspecialchars($err) . ' ';
        }
    }

    // Insert Silver
    if ($silver_rate !== null) {
        list($ok, $err) = insertRate($con, $employeeId, (float)$silver_rate, 'Silver', $todayDate, $nowTime);
        if (!$ok) {
            $okAll = false;
            $msg .= 'Silver rate save error: ' . htmlspecialchars($err) . ' ';
        }
    }

    if ($okAll) {
        $msg = 'ok';
    } elseif ($msg === '') {
        $msg = 'fail';
    }

    // PRG redirect to avoid repost
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    $url .= '?msg=' . urlencode($msg);
    header('Location: '.$url, true, 303);
    exit;
}

/* ---------------------------
   AFTER POST: render page
---------------------------- */
if ($type === 'CallCenterUser') {
    include("header.php");
    include("menucallUser.php");
}
else if($type=='Issuecall'){
		include("header.php");
		include("menuissues.php");
	}
else {
    include("logout.php");
    exit();
}

/* ---------------------------
   Fetch last rates (per type)
---------------------------- */
$lastGold   = null;
$lastSilver = null;

$qGold = mysqli_query(
    $con,
    "SELECT * FROM `ccgold`
     WHERE employeeId = '" . mysqli_real_escape_string($con, $employeeId) . "'
       AND type = 'Gold'
     ORDER BY date DESC, time DESC
     LIMIT 1"
);
if ($qGold) $lastGold = mysqli_fetch_assoc($qGold);

$qSilver = mysqli_query(
    $con,
    "SELECT * FROM `ccgold`
     WHERE employeeId = '" . mysqli_real_escape_string($con, $employeeId) . "'
       AND type = 'Silver'
     ORDER BY date DESC, time DESC
     LIMIT 1"
);
if ($qSilver) $lastSilver = mysqli_fetch_assoc($qSilver);

/* ---------------------------
   Fetch all rate history
---------------------------- */
$history = mysqli_query(
    $con,
    "SELECT * FROM `ccgold`
     WHERE employeeId = '" . mysqli_real_escape_string($con, $employeeId) . "'
     ORDER BY date DESC, time DESC"
);
?>
<style>
  .rate-table { width: 100%; border-collapse: collapse; }
  .rate-table th, .rate-table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
  .rate-table th { background-color: #f2f2f2; }
</style>

<div id="wrapper">
  <div class="row content">
    <div class="panel-heading">
      <h3><span style="color:#900" class="fa fa-line-chart"></span> Daily Gold & Silver Rate</h3>
      <?php if (!empty($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'ok'): ?>
          <div class="alert alert-success" style="margin:10px 0;">Rates saved successfully!</div>
        <?php else: ?>
          <div class="alert alert-danger" style="margin:10px 0;">There was a problem saving the rates. <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- LEFT: FORM -->
    <div class="col-lg-3">
      <div class="hpanel">
        <div class="panel-body" style="box-shadow:10px 15px 15px #555;border-radius:10px;">
          <form id="rateForm" method="POST" action="">
            <div class="row">
              <div class="col-md-12">
                <label class="text-success">Employee ID</label>
                <div class="input-group">
                  <span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book"></span></span>
                  <input type="text" class="form-control" value="<?php echo htmlspecialchars($employeeId); ?>" readonly>
                </div>
                <br>
              </div>

              <div class="col-md-12">
                <label class="text-success">Gold Rate (Today)</label>
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-inr"></span></span>
                  <input type="text" class="form-control" name="gold_rate" id="gold_rate"
                         placeholder="Enter today's gold rate"
                         value="">
                </div>
                <br>
              </div>

              <div class="col-md-12">
                <label class="text-success">Silver Rate (Today)</label>
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-inr"></span></span>
                  <input type="text" class="form-control" name="silver_rate" id="silver_rate"
                         placeholder="Enter today's silver rate"
                         value="">
                </div>
                <br>
              </div>

              <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">
                  <i class="fa fa-save"></i> Save Today’s Rates
                </button>
              </div>
            </div>
          </form>
          <p style="margin-top:8px;color:#777;font-size:12px;">
            You can fill one or both rates. Each filled field will be stored as a separate entry for today.
          </p>
        </div>
      </div>
    </div>

    <!-- RIGHT: LAST RATES -->
    <div class="col-lg-9">
      <div class="hpanel">
        <div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
          <h4>Last Saved Rates (for you):</h4>

          <table class="table table-striped">
            <tr>
              <th>Gold Rate</th>
              <td>
                <?php
                if ($lastGold) {
                  echo htmlspecialchars($lastGold['rate']) . ' &nbsp; ('
                     . htmlspecialchars($lastGold['date']) . ' ' . htmlspecialchars($lastGold['time']) . ')';
                } else {
                  echo 'No gold rate saved yet.';
                }
                ?>
              </td>
            </tr>
            <tr>
              <th>Silver Rate</th>
              <td>
                <?php
                if ($lastSilver) {
                  echo htmlspecialchars($lastSilver['rate']) . ' &nbsp; ('
                     . htmlspecialchars($lastSilver['date']) . ' ' . htmlspecialchars($lastSilver['time']) . ')';
                } else {
                  echo 'No silver rate saved yet.';
                }
                ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- HISTORY TABLE -->
  <div class="row content">
    <div class="panel-heading">
      <h3><span style="color:#900" class="fa fa-history"></span> Rate History</h3>
    </div>
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
          <h4>Saved Rates:</h4>
          <?php if ($history && mysqli_num_rows($history) > 0): ?>
            <table class="rate-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Type</th>
                  <th>Rate</th>
                </tr>
              </thead>
              <tbody>
              <?php while ($row = mysqli_fetch_assoc($history)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['date']); ?></td>
                  <td><?php echo htmlspecialchars($row['time']); ?></td>
                  <td><?php echo htmlspecialchars($row['type']); ?></td>
                  <td><?php echo htmlspecialchars($row['rate']); ?></td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>No rate records found for this employee.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include("footer.php"); ?>
</div>

