<?php
session_start();

// -------------------------
// Set timezone to Kolkata/IST
// -------------------------
date_default_timezone_set('Asia/Kolkata');

// -------------------------
// 0) AUTH & ROLE CHECK
// -------------------------
if (empty($_SESSION['branchId'])) {
    header('Location: login.php');
    exit;
}

$branchId    = $_SESSION['branchId'];
$statusPath  = __DIR__ . '/status.json';
$clientsFile = __DIR__ . '/clients.json';

// Load clients.json
$clients = file_exists($clientsFile)
    ? json_decode(file_get_contents($clientsFile), true) ?: []
    : [];

if (($clients[$branchId]['role'] ?? 'branch') === 'admin') {
    header('Location: handler.php');
    exit;
}

// -------------------------
// 1) ENSURE FILES EXIST
// -------------------------
if (! file_exists($statusPath)) {
    file_put_contents($statusPath, json_encode(new stdClass()));
}
if (! file_exists($clientsFile)) {
    file_put_contents($clientsFile, json_encode(new stdClass(), JSON_PRETTY_PRINT));
}

// -------------------------
// 2) UPLOAD DIRECTORY
// -------------------------
$today     = date('Y-m-d');
$uploadDir = __DIR__ . "/uploads/{$branchId}/{$today}";

// -------------------------
// 3) UPDATE LISTENER STATUS
// -------------------------
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0 &&
    !isset($_FILES['audio_data'])
) {
    header('Content-Type: application/json');
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    if (isset($payload['status'])) {
        $allClients = json_decode(file_get_contents($clientsFile), true) ?: [];
        if (! isset($allClients[$branchId])) {
            $allClients[$branchId] = [];
        }
        $allClients[$branchId]['listener_status'] = $payload['status'];
        $allClients[$branchId]['last_seen']       = time();
        file_put_contents($clientsFile, json_encode($allClients, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No status provided']);
    }
    exit;
}

// -------------------------
// 4) RECORD FLAG ENDPOINT
// -------------------------
if (isset($_GET['status']) && $_GET['status'] === '1') {
    header('Content-Type: application/json');
    $allStatuses   = json_decode(file_get_contents($statusPath), true) ?: [];
    $shouldRecord  = ! empty($allStatuses[$branchId]['record']);
    echo json_encode(['record' => $shouldRecord]);
    exit;
}

// -------------------------
// 5) AUDIO UPLOAD ENDPOINT
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio_data'])) {
    header('Content-Type: application/json');

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['audio_data'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload error: ' . $file['error']]);
        exit;
    }

    // Capture client-reported recording length (HH-MM-SS)
    $lengthStr = '';
    if (isset($_POST['length'])) {
        $lengthStr = str_replace(':', '-', preg_replace('/[^0-9:]/', '', $_POST['length']));
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'webm';

    // Use server (now in IST) time for the filename
    if (!empty($_POST['clientTimestamp'])) {
        $timestamp = preg_replace('/[^0-9_]/', '', $_POST['clientTimestamp']);
    } else {
        $timestamp = date('Ymd_His');
    }

    // Build final filename
    $finalName = "{$branchId}_recording_{$timestamp}";
    if ($lengthStr !== '') {
        $finalName .= "_{$lengthStr}";
    }
    $finalName .= ".{$ext}";

    $destination = "{$uploadDir}/{$finalName}";

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
        exit;
    }

    // After upload, reset listener_status → idle
    $allClients = json_decode(file_get_contents($clientsFile), true) ?: [];
    if (isset($allClients[$branchId])) {
        $allClients[$branchId]['listener_status'] = 'idle';
        $allClients[$branchId]['last_seen']       = time();
        file_put_contents($clientsFile, json_encode($allClients, JSON_PRETTY_PRINT));
    }

    echo json_encode(['success' => true, 'filename' => $finalName]);
    exit;
}

// -------------------------
// 6) HEARTBEAT ENDPOINT FOR STALE CHECK
// -------------------------
// if (isset($_GET['heartbeat'])) {
    // header('Content-Type: application/json');
    // $allClients = json_decode(file_get_contents($clientsFile), true) ?: [];
    // $lastSeen = $allClients[$branchId]['last_seen'] ?? 0;
    // echo json_encode(['last_seen' => $lastSeen]);
    // exit;
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="images/favicon.png">
  <title>Attica Analytics - Branch Login <?= htmlspecialchars($branchId) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #eef2f5;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px;
    }
    #info {
      text-align: center;
      margin-bottom: 20px;
    }
    #status {
      font-weight: bold;
      margin-top: 10px;
    }
    #logout {
      margin-top: 20px;
      background: #c0392b;
      color: white;
      border: none;
      padding: .5em 1em;
      cursor: pointer;
      border-radius: 4px;
    }
  </style>
</head>
<body style="background:#760107">
  <div id="info" style="color:white">
    <center><img src="images/group-of-attica-gold-companies.jpg" style="width:60%;padding:30px"></center>
    <p>Branch: <strong><?= htmlspecialchars($branchId) ?></strong></p>
    <p><strong>Do Not Close this Window till End of Day.</strong></p>
    <p>Logged in at: <span id="login-time"></span></p>
    <p>Time since login: <span id="elapsed-time">00:00:00</span></p>
  </div>
  <button id="logout">Log Out</button>

  <script>
    let currentListenerStatus = 'offline';
    let mediaRecorder, recordedChunks = [];
    let isRecording = false, recordStartTime = 0, chunkTimeout;

    // format with leading zero
    const pad = n => n < 10 ? '0' + n : n;

    // update displayed login time
    function updateLoginTime() {
      let stored = sessionStorage.getItem('loginTime');
      let loginTime = stored ? new Date(stored) : new Date();
      if (!stored) sessionStorage.setItem('loginTime', loginTime.toISOString());
      document.getElementById('login-time').textContent =
        `${loginTime.getFullYear()}-${pad(loginTime.getMonth()+1)}-${pad(loginTime.getDate())}` +
        ` ${pad(loginTime.getHours())}:${pad(loginTime.getMinutes())}:${pad(loginTime.getSeconds())}`;
    }
    updateLoginTime();
    setInterval(() => {
      let stored = sessionStorage.getItem('loginTime');
      let loginTime = new Date(stored);
      let diff = Date.now() - loginTime;
      let s = Math.floor(diff/1000), h = Math.floor(s/3600);
      let m = Math.floor((s%3600)/60);
      document.getElementById('elapsed-time').textContent = `${pad(h)}:${pad(m)}:${pad(s%60)}`;
    }, 1000);

    // send status updates to server
    async function updateListenerStatus(st) {
      currentListenerStatus = st;
      await fetch('listener.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: st })
      });
    }

    // heartbeat to keep last_seen fresh
    async function updateLastSeen() {
      await fetch('listener.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: currentListenerStatus })
      });
    }

    // initialize recorder
    async function initRecorder() {
      if (!navigator.mediaDevices?.getUserMedia) {
        return updateListenerStatus('mic unsupported');
      }
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm;codecs=opus' });
        mediaRecorder.ondataavailable = e => { if (e.data.size) recordedChunks.push(e.data); };
        mediaRecorder.onstop = handleChunkStop;
        setInterval(checkRecordFlag, 1000);
      } catch (e) {
        updateListenerStatus('mic denied');
      }
    }

    // check if should be recording
    async function checkRecordFlag() {
      let resp = await fetch('listener.php?status=1');
      let { record } = await resp.json();
      if (record && !isRecording) {
        startChunk();
      } else if (!record && isRecording) {
        clearTimeout(chunkTimeout);
        mediaRecorder.stop();
      }
    }

    // start a new 5-minute chunk
    async function startChunk() {
      recordedChunks = [];
      recordStartTime = Date.now();
      mediaRecorder.start();
      isRecording = true;
      await updateListenerStatus('recording');
      chunkTimeout = setTimeout(() => mediaRecorder.stop(), 5 * 60 * 1000);
    }

    // upload a finished chunk and restart if needed
    async function handleChunkStop() {
      clearTimeout(chunkTimeout);
      isRecording = false;
      const blob = new Blob(recordedChunks, { type: 'audio/webm' });
      recordedChunks = [];

      // compute length
      const elapsedMs = Date.now() - recordStartTime;
      let sec = Math.floor(elapsedMs/1000), h = Math.floor(sec/3600);
      sec %= 3600;
      let m = Math.floor(sec/60), s = sec%60;
      const lengthStr = `${pad(h)}:${pad(m)}:${pad(s)}`;

      // timestamp
      const now = new Date();
      const YYYY = now.getFullYear(), MM = pad(now.getMonth()+1), DD = pad(now.getDate());
      const hh = pad(now.getHours()), mm = pad(now.getMinutes()), ss = pad(now.getSeconds());
      const clientTimestamp = `${YYYY}${MM}${DD}_${hh}${mm}${ss}`;

      let fd = new FormData();
      fd.append('length', lengthStr);
      fd.append('clientTimestamp', clientTimestamp);
      fd.append('audio_data', blob, `${clientTimestamp}.webm`);
      await fetch('listener.php', { method: 'POST', body: fd });

      await updateListenerStatus('idle');

      let resp = await fetch('listener.php?status=1');
      let { record } = await resp.json();
      if (record) startChunk();
    }

    // send offline on unload
    window.addEventListener('beforeunload', () => {
      navigator.sendBeacon('listener.php', JSON.stringify({ status: 'offline' }));
    });

    // logout button
    document.getElementById('logout').addEventListener('click', async () => {
      await updateListenerStatus('offline');
      sessionStorage.removeItem('loginTime');
      window.location.href = 'logout.php';
    });

    // on load
    window.addEventListener('load', async () => {
      await updateListenerStatus('ready');
      initRecorder();
      setInterval(updateLastSeen, 2000);
    });
  </script>
</body>
</html>

