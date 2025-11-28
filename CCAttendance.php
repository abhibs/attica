<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');
include("dbConnection.php"); // $con must be a valid mysqli connection
@mysqli_set_charset($con, 'utf8mb4');

$type   = $_SESSION['usertype']       ?? '';
$date   = date('Y-m-d');
$CCTime = date('H:i:s');
$CCempID= $_SESSION['login_username'] ?? '';
$empId  = $_SESSION['login_username'] ?? '';
$name   = $_SESSION['employeeName']   ?? ''; // optional
$employeeId = $_SESSION['employeeId']     ?? '';

// Create image folder if not exists
$imgDir = __DIR__ . '/CCAttendanceImage';
if (!is_dir($imgDir)) { @mkdir($imgDir, 0775, true); }

/* --------------------------------
   POST FIRST, THEN REDIRECT (PRG)
--------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataUri = $_POST['image'] ?? '';
    $savedFilename = '';

    if (!empty($dataUri) && strpos($dataUri, 'data:image/') === 0) {
        $parts = explode(',', $dataUri, 2);
        if (count($parts) === 2) {
            $meta = $parts[0];        // e.g. data:image/jpeg;base64
            $b64  = $parts[1];
            $ext = 'jpg';
            if (stripos($meta, 'image/png')  !== false) $ext = 'png';
            if (stripos($meta, 'image/webp') !== false) $ext = 'webp';

            $safeEmp = preg_replace('/[^A-Za-z0-9_\-]/', '', $empId);
            $savedFilename = 'CC_' . $safeEmp . '_' . date('Ymd_His') . '.' . $ext;

            $imgBin = base64_decode($b64);
            if ($imgBin === false || strlen($imgBin) <= 50 ||
                file_put_contents($imgDir . '/' . $savedFilename, $imgBin) === false) {
                $savedFilename = ''; // fall back to empty
            }
        }
    }

    $CCStatus = 0; // 0 = Active, 1 = Blocked
    $status   = 1;

    // IMPORTANT: keep table name consistent (here: ccattendance)
    $sql = "INSERT INTO `ccattendance` (empId, name, date, time, photo, CCempID, CCStatus, CCTime, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $msg = 'fail';
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssssisi',
            $empId, $name, $date, $CCTime, $savedFilename, $CCempID, $CCStatus, $CCTime, $status
        );
        if (mysqli_stmt_execute($stmt)) $msg = 'ok';
        mysqli_stmt_close($stmt);
    } else {
        $msg = 'prep';
    }

    // Redirect back to self to avoid resubmission & header issues
    $url = strtok($_SERVER['REQUEST_URI'], '?'); // strip old query
    $url .= '?msg=' . urlencode($msg);
    header('Location: ' . $url, true, 303);
    exit;
}

/* ---------------------------
   AFTER POST: render page
---------------------------- */
if ($type == 'CallCenterUser') {
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

// Fetch last & all attendance for this employee
$sql_last_attendance = mysqli_query(
    $con,
    "SELECT * FROM `ccattendance`
     WHERE empId = '" . mysqli_real_escape_string($con, $empId) . "'
     ORDER BY date DESC, time DESC LIMIT 1"
);
$last_attendance = $sql_last_attendance ? mysqli_fetch_assoc($sql_last_attendance) : null;

$sql_attendance = mysqli_query(
    $con,
    "SELECT * FROM `ccattendance`
     WHERE empId = '" . mysqli_real_escape_string($con, $empId) . "'
     ORDER BY date DESC, time DESC"
);
?>
<style>
  .attendance-table { width: 100%; border-collapse: collapse; }
  .attendance-table th, .attendance-table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
  .attendance-table th { background-color: #f2f2f2; }
  .attendance-photo { width: 100px; height:auto; border-radius:4px; }
  #my_camera, #results { width: 210px; height:160px; border: 1px solid #ccc; border-radius:6px; background:#fafafa; }
</style>

<div id="wrapper">
  <div class="row content">
    <div class="panel-heading">
      <h3><span style="color:#900" class="fa fa-users"></span> Daily Attendance</h3>
      <?php if (!empty($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'ok'): ?>
          <div class="alert alert-success" style="margin:10px 0;">Attendance marked successfully!</div>
        <?php elseif ($_GET['msg'] === 'prep'): ?>
          <div class="alert alert-danger" style="margin:10px 0;">Error preparing query.</div>
        <?php else: ?>
          <div class="alert alert-danger" style="margin:10px 0;">Error saving attendance.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="col-lg-3">
      <div class="hpanel">
        <div class="panel-body" style="box-shadow:10px 15px 15px #555;border-radius:10px;">
          <form id="attForm" method="POST" action="">
            <input type="hidden" name="image" id="image_field">
            <div class="row">
              <div class="col-md-12">
                <div id="results" style="margin:0 auto 8px;"></div>
                <div id="my_camera" style="margin:0 auto;"></div>
              </div>
              <div class="col-md-12" style="margin-top:15px;">
                <label class="text-success">Employee ID</label>
                <div class="input-group">
                  <span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book"></span></span>
                  <input type="text" class="form-control" name="empp" id="empp" placeholder="1000***"
                         maxlength="7" pattern="[A-Z0-9]{7}" required autocomplete="off"
                         value="<?php echo htmlspecialchars($_SESSION['login_username'] ?? ''); ?>" readonly>
                </div>
                <br>
              </div>
              <div class="col-md-12">
                <button type="submit" name="submitAttend" id="submitAttend" class="btn btn-primary btn-block">
                  <i class="fa fa-camera"></i> Capture & Mark Attendance
                </button>
                <button type="button" id="btnCaptureOnly" class="btn btn-default btn-block" style="margin-top:6px;">
                  <i class="fa fa-camera"></i> Preview Capture (Optional)
                </button>
              </div>
            </div>
          </form>
          <p style="margin-top:8px;color:#777;font-size:12px;">
            Tip: If there’s no camera or it’s blocked, the form still submits and marks attendance without a photo.
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <div class="hpanel">
        <div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
          <h4>Last Attendance Record:</h4>
          <?php if ($last_attendance): ?>
            <table class="table table-striped">
              <tr>
                <th>Employee ID</th>
                <td><?php echo htmlspecialchars($last_attendance['empId']); ?></td>
              </tr>
              <tr>
                <th>Date</th>
                <td><?php echo htmlspecialchars($last_attendance['date']); ?></td>
              </tr>
              <tr>
                <th>Time</th>
                <td><?php echo htmlspecialchars($last_attendance['time']); ?></td>
              </tr>
              <tr>
                <th>Photo</th>
                <td>
                  <?php if (!empty($last_attendance['photo'])): ?>
                    <a target="_blank" href="CCAttendanceImage/<?php echo rawurlencode($last_attendance['photo']); ?>">
                      <img width="100" class="attendance-photo"
                           src="CCAttendanceImage/<?php echo rawurlencode($last_attendance['photo']); ?>"
                           alt="Attendance Photo">
                    </a>
                  <?php else: ?>
                    No photo captured.
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          <?php else: ?>
            <p>No attendance record found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row content">
    <div class="panel-heading">
      <h3><span style="color:#900" class="fa fa-users"></span> Attendance Records</h3>
    </div>
    <div class="col-lg-12">
      <div class="hpanel">
        <div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
          <h4>Marked Attendance Records:</h4>
          <?php if ($sql_attendance && mysqli_num_rows($sql_attendance) > 0): ?>
            <table class="attendance-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Photo</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($attendance = mysqli_fetch_assoc($sql_attendance)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($attendance['date']); ?></td>
                    <td><?php echo htmlspecialchars($attendance['time']); ?></td>
                    <td>
                      <?php if (!empty($attendance['photo'])): ?>
                        <a target="_blank" href="CCAttendanceImage/<?php echo rawurlencode($attendance['photo']); ?>">
                          <img class="attendance-photo"
                               src="CCAttendanceImage/<?php echo rawurlencode($attendance['photo']); ?>"
                               alt="Attendance Photo">
                        </a>
                      <?php else: ?>
                        No photo captured.
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>No attendance records found for this employee.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include("footer.php"); ?>
</div>

<!-- WebcamJS local + CDN fallback -->
<script src="scripts/webcam.min.js"></script>
<script>
if (typeof Webcam === 'undefined') {
  var s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js';
  document.head.appendChild(s);
}
</script>

<script>
(function(){
  var webcamReady = false;

  function tryAttachWebcam() {
    if (typeof Webcam === 'undefined') return;
    try {
      Webcam.set({ width: 210, height: 160, image_format: 'jpeg', jpeg_quality: 100 });
      Webcam.attach('#my_camera');
      webcamReady = true;
    } catch (e) {
      webcamReady = false;
    }
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(tryAttachWebcam, 100);
  } else {
    document.addEventListener('DOMContentLoaded', function(){ setTimeout(tryAttachWebcam, 100); });
  }

  // Safe snap → data_uri or null
  function safeSnap(timeoutMs) {
    return new Promise(function(resolve){
      if (typeof Webcam === 'undefined' || !webcamReady) return resolve(null);
      var finished = false;
      var t = setTimeout(function(){ if (!finished){ finished = true; resolve(null); } }, Math.max(300, timeoutMs||1200));
      try {
        Webcam.snap(function(data_uri){
          if (finished) return;
          finished = true; clearTimeout(t);
          resolve((typeof data_uri === 'string' && data_uri.indexOf('data:image/') === 0) ? data_uri : null);
        });
      } catch(e) {
        if (!finished){ finished = true; clearTimeout(t); resolve(null); }
      }
    });
  }

  // Preview button (optional)
  var btnPreview = document.getElementById('btnCaptureOnly');
  if (btnPreview) {
    btnPreview.addEventListener('click', async function(){
      var data_uri = await safeSnap(1500);
      var imgField = document.getElementById('image_field');
      var results  = document.getElementById('results');
      if (data_uri) {
        imgField.value = data_uri;
        results.innerHTML = '<img src="'+data_uri+'" style="width:210px;height:160px;border-radius:6px;"/>';
      } else {
        imgField.value = '';
        results.innerHTML = '<div style="font-size:12px;color:#777;">No camera available. Proceeding without photo.</div>';
      }
    });
  }

  // Submit: try to capture; if not, submit without photo
  var form = document.getElementById('attForm');
  if (form) {
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      var imgField = document.getElementById('image_field');
      var results  = document.getElementById('results');

      var data_uri = await safeSnap(1200);
      imgField.value = data_uri || '';

      if (data_uri) {
        results.innerHTML = '<img src="'+data_uri+'" style="width:210px;height:160px;border-radius:6px;"/>';
      } else {
        results.innerHTML = '<div style="font-size:12px;color:#777;">Submitting without photo.</div>';
      }

      form.submit(); // PRG will redirect and show banner
    });
  }
})();
</script>

