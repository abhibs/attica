<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$branchId = $_SESSION['branchId'] ?? '';

if (empty($branchId) || $branchId !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$clientsFile = __DIR__ . '/clients.json';
$clients = [];
if (file_exists($clientsFile)) {
    $clients = json_decode(file_get_contents($clientsFile), true) ?: [];
}

$branchId = $_SESSION['branchId'];
if (!isset($clients[$branchId]) || ($clients[$branchId]['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// -------------------------
// CONFIG
// -------------------------
$uploadRoot = __DIR__ . '/uploads';

// -------------------------
// Safe helpers (avoid array_filter on false)
// -------------------------
function safe_scandir(string $dir): array {
    $out = @scandir($dir);
    return is_array($out) ? $out : [];
}
function safe_list_dirs(string $dir): array {
    $list = safe_scandir($dir);
    return array_values(array_filter($list, function($item) use ($dir) {
        if ($item === '.' || $item === '..') return false;
        $p = $dir . '/' . $item;
        return is_dir($p) && is_readable($p);
    }));
}
function safe_list_files(string $dir): array {
    $list = safe_scandir($dir);
    return array_values(array_filter($list, function($item) use ($dir) {
        if ($item === '.' || $item === '..') return false;
        $p = $dir . '/' . $item;
        return is_file($p) && is_readable($p);
    }));
}

/**
 * Extract duration from filename
 */
function getDurationFromFilename(string $filename): string {
    if (preg_match('/_recording_\d{8}_\d{6}_(\d{2}-\d{2}-\d{2})\./', $filename, $m)) {
        return str_replace('-', ':', $m[1]);
    }
    return '00:00:00';
}

// -------------------------
// Actions
// -------------------------
$message = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['branch'], $_POST['date'], $_POST['file'])) {
    $b = basename($_POST['branch']);
    $d = basename($_POST['date']);
    $f = basename($_POST['file']);
    $path = __DIR__ . "/uploads/{$b}/{$d}/{$f}";
    if (is_file($path) && is_writable($path) && @unlink($path)) {
        $message = "Deleted file: {$f}";
    } else {
        $message = "Failed to delete: {$f}";
    }
}

// Handle transcribe action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transcribe'], $_POST['branch'], $_POST['date'], $_POST['file'])) {
    $b = basename($_POST['branch']);
    $d = basename($_POST['date']);
    $f = basename($_POST['file']);
    $audioPath = __DIR__ . "/uploads/{$b}/{$d}/{$f}";
    $outputTxt = pathinfo($audioPath, PATHINFO_FILENAME) . ".txt";
    $outputPath = __DIR__ . "/uploads/{$b}/{$d}/{$outputTxt}";

    if (is_file($audioPath) && is_readable($audioPath)) {
        // IMPORTANT: never hard-code secrets in code. Use env var.
        // Also revoke the previously exposed key in your snippet.
        $apiKey = getenv('OPENAI_API_KEY') ?: '';
        if (!$apiKey) {
            $message = "Transcription error: OPENAI_API_KEY is not set on the server.";
        } else {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.openai.com/v1/audio/transcriptions",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer $apiKey"
                ],
                CURLOPT_POSTFIELDS => [
                    "file" => new CURLFile($audioPath),
                    "model" => "whisper-1"
                ],
                CURLOPT_TIMEOUT => 120
            ]);
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                $message = "Error during transcription: $error";
            } else {
                $data = json_decode($response, true);
                if (isset($data['text'])) {
                    file_put_contents($outputPath, $data['text']);
                    $message = "Transcription saved successfully.";
                } else {
                    $message = "Transcription failed: Invalid response.";
                }
            }
        }
    } else {
        $message = "Audio file not found.";
    }
}

// -------------------------
// Scan branches safely
// -------------------------
$branches = [];
if (!is_dir($uploadRoot) || !is_readable($uploadRoot)) {
    $branches = [];
} else {
    $branches = safe_list_dirs($uploadRoot);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="icon" type="image/png" href="images/favicon.png">
  <title>Admin Dashboard - Uploads</title>
 <style>
  a { text-decoration: none; color: #900; }
  body { font-family: Arial, sans-serif; padding: 20px; background: #f8e75c; margin: 0; }
  h1 { color: #333; }
  details { margin-bottom: 1em; }
  summary { font-weight: bold; font-size: 1.1em; cursor: pointer; }
  table {
    width: 100%; border-collapse: collapse; margin-top: 0.5em;
    background: white; table-layout: fixed;
  }
  th, td {
    border: 1px solid white; padding: 0.5em; text-align: center;
    word-break: break-word;
  }
  th:nth-child(1) { width: 35%; }
  th:nth-child(2) { width: 20%; }
  th:nth-child(3) { width: 10%; }
  th:nth-child(4) { width: 10%; }
  th:nth-child(5) { width: 15%; align:center; }
  .message { padding: 0.5em; background: #e0ffe0; border: 1px solid #b0ffb0; margin-bottom: 1em; }
  #branchSearch::placeholder {
    font-weight: bold;
    text-align: center;
    color: red;
  }
  #logout { position: fixed; top: 20px; right: 20px; background: #c0392b; color: #fff; border: none; padding: 0.5em 1em; cursor: pointer; border-radius: 4px; }
  #goback { position: fixed; top: 20px; left: 20px; background: #c0392b; color: #fff; border: none; padding: 0.5em 1em; cursor: pointer; border-radius: 4px; }
  button.delete-btn {
    background: #900; color: #f8e75c; font-weight: bold;
    border: none; border-radius: 15px; padding: 10px; cursor: pointer;
  }
  th { cursor: pointer; background: #900; color: #fff; }
  th.asc::after { content: " 🔼"; }
  th.desc::after { content: " 🔽"; }
</style>
</head>
<body>
  <button id="logout" onclick="window.location.href='logout.php'">Log Out</button>
  <button id="goback" onclick="window.location.href='handler.php'">Go Back</button>
  <h1 style="text-align:center">Admin Dashboard - Recordings</h1>

  <?php if (!empty($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div style="text-align:center; margin-bottom:1em;">
    <input type="text" id="branchSearch" placeholder="Search with BRANCH ID" onkeyup="filterBranches()" style="padding:6px; width:250px; background:#f8e75c; color: #900" />
  </div>
<br><br>
  <?php if (empty($branches)): ?>
    <p><?= is_dir($uploadRoot) ? 'No branches found under uploads.' : 'Uploads folder not found or unreadable.'; ?></p>
  <?php else: ?>
    <div id="branchContainer">
      <?php foreach ($branches as $branch): ?>
        <?php
          $branchDir = "{$uploadRoot}/{$branch}";
          $dates = safe_list_dirs($branchDir);
        ?>
        <details class="branch" data-branch="<?= htmlspecialchars(strtolower($branch)) ?>">
          <summary><strong>Branch: <?= htmlspecialchars($branch) ?></strong></summary>
          <?php if (empty($dates)): ?>
            <p style="margin-left:1em;"><em>No recordings</em></p>
          <?php else: ?>
            <?php foreach ($dates as $date): ?>
              <?php
                $fileDir = "{$branchDir}/{$date}";
                $files = safe_list_files($fileDir);
              ?>
              <details style="margin-left:2em;">
                <summary><strong>Date: <?= htmlspecialchars($date) ?></strong></summary>
                <?php if (empty($files)): ?>
                  <p style="margin-left:1em;"><em>No files</em></p>
                <?php else: ?>
                  <table class="recordingTable" border="1" cellpadding="5" cellspacing="0" style="width:95%; margin:1em;">
                    <thead>
                      <tr>
                        <th>File</th>
                        <th onclick="sortTable(this)">Recorded</th>
                        <th onclick="sortTable(this)">Length</th>
                        <th onclick="sortTable(this)">Size</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($files as $file): ?>
                        <?php
                          $fullPath = "{$fileDir}/{$file}";
                          $dtStr = 'Unknown';
                          $sortDate = '';
                          if (preg_match('/_recording_(\d{8}_\d{6})/', $file, $matches)) {
                              $dt = DateTime::createFromFormat('Ymd_His', $matches[1]);
                              if ($dt) {
                                  $dtStr = $dt->format('Y-m-d H:i:s');
                                  $sortDate = $dt->format('YmdHis');
                              }
                          }

                          $duration = getDurationFromFilename($file);

                          $bytes = @filesize($fullPath);
                          if ($bytes === false) {
                              $sizeHR = 'Unknown';
                          } elseif ($bytes < 1024) {
                              $sizeHR = $bytes . ' B';
                          } elseif ($bytes < 1048576) {
                              $sizeHR = round($bytes / 1024, 2) . ' KB';
                          } else {
                              $sizeHR = round($bytes / 1048576, 2) . ' MB';
                          }

                          // transcript path (not shown in table, but kept for future use)
                          $transcriptFile = pathinfo($file, PATHINFO_FILENAME) . ".txt";
                          $transcriptPath = "{$fileDir}/{$transcriptFile}";
                        ?>
                        <tr data-sort="<?= htmlspecialchars($sortDate) ?>">
                          <td><strong><a href="uploads/<?= urlencode($branch) ?>/<?= urlencode($date) ?>/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a></strong></td>
                          <td><?= htmlspecialchars($dtStr) ?></td>
                          <td><?= htmlspecialchars($duration) ?></td>
                          <td><?= htmlspecialchars($sizeHR) ?></td>
                          <td>
                            <div style="display: flex; justify-content: center; gap: 5px;">
                              <form method="POST" style="margin: 0;">
                                <input type="hidden" name="branch" value="<?= htmlspecialchars($branch) ?>">
                                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                                <button type="submit" name="delete" style="background:#900;color:#f8e75c;font-weight:bold;border:none;border-radius:10px;padding:6px 12px;cursor:pointer;font-size:0.7vw;">Delete</button>
                              </form>

                              <a href="uploads/<?= urlencode($branch) ?>/<?= urlencode($date) ?>/<?= urlencode($file) ?>" download
                                 style="font-size:0.7vw; background:#006400;color:#f8e75c;font-weight:bold;border:none;border-radius:10px;padding:6px 12px;text-decoration:none;">
                                 Download
                              </a>

                              <?php if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'txt'): ?>
                                <form method="POST" style="margin: 0;">
                                  <input type="hidden" name="transcribe" value="1">
                                  <input type="hidden" name="branch" value="<?= htmlspecialchars($branch) ?>">
                                  <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                                  <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                                  <button type="submit" style="background:#ffa500;color:#000;font-weight:bold;border:none;border-radius:10px;padding:6px 12px;cursor:pointer;font-size:0.7vw;">Transcribe</button>
                                </form>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </details>
            <?php endforeach; ?>
          <?php endif; ?>
        </details>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <script>
    // Search branches
    function filterBranches() {
      const input = document.getElementById('branchSearch').value.toLowerCase();
      const branches = document.querySelectorAll('#branchContainer details.branch');
      branches.forEach(branch => {
        const name = branch.getAttribute('data-branch');
        branch.style.display = name.includes(input) ? '' : 'none';
      });
    }

    // Sort table (simple text sort)
    function sortTable(header) {
      const table = header.closest('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const index = Array.from(header.parentNode.children).indexOf(header);
      const asc = !header.classList.contains('asc');

      rows.sort((a, b) => {
        // use data-sort for "Recorded" if present
        if (index === 1) {
          const av = a.getAttribute('data-sort') || a.children[index].innerText.trim();
          const bv = b.getAttribute('data-sort') || b.children[index].innerText.trim();
          return asc ? av.localeCompare(bv) : bv.localeCompare(av);
        }
        const aVal = a.children[index].innerText.trim();
        const bVal = b.children[index].innerText.trim();
        return asc
          ? aVal.localeCompare(bVal, undefined, { numeric: true })
          : bVal.localeCompare(aVal, undefined, { numeric: true });
      });

      header.parentNode.querySelectorAll('th').forEach(th => th.classList.remove('asc', 'desc'));
      header.classList.add(asc ? 'asc' : 'desc');

      rows.forEach(row => tbody.appendChild(row));
    }
  </script>

  <style>
    th { cursor: pointer; }
    th.asc::after { content: " 🔼"; }
    th.desc::after { content: " 🔽"; }
  </style>
</body>
</html>

