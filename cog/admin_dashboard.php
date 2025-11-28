<?php
// ─── ERROR REPORTING ──────────────────────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ─── CONFIGURATION ────────────────────────────────────────────────────────────
require_once __DIR__ . '/../Config/Database.php';

// Build the DSN and other PDO params from constants
$dbHost = HOSTNAME;
$dbName = 'cog';
$dbUser = USERNAME;
$dbPass = PASSWORD;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

// Optional: simple test connection
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass === '' ? null : $dbPass, $options);
    // echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $dbUser, $dbPass, $options);

// ─── GET SEARCH DATE ──────────────────────────────────────────────────────────
$searchDate = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchDate)) {
    $searchDate = date('Y-m-d');
}

if (isset($_POST['toggle_team'])) {
    $tid = (int)$_POST['team_id'];
    $status = $_POST['current_status'] === 'enabled' ? 'disabled' : 'enabled';
    $pdo->prepare("UPDATE teams SET status=? WHERE id=?")->execute([$status, $tid]);
    header('Location: admin_dashboard.php?tab=teams');
    exit;
}
// ─── HANDLERS ────────────────────────────────────────────────────────────────
// Add Zonal
if (isset($_POST['add_zonal'])) {
    $name = trim($_POST['zonal_name']);
    if ($name !== '') {
        $pdo->prepare("INSERT INTO zonals(name) VALUES (?)")->execute([$name]);
    }
    header('Location: admin_dashboard.php?tab=zonals'); exit;
}
// Delete Zonal
if (isset($_POST['delete_zonal'])) {
    $pdo->prepare("DELETE FROM zonals WHERE id = ?")->execute([(int)$_POST['zonal_id']]);
    header('Location: admin_dashboard.php?tab=zonals'); exit;
}
// Add VM
if (isset($_POST['add_vm'])) {
    $name = trim($_POST['vm_name']);
    if ($name !== '') {
        $pdo->prepare("INSERT INTO vms(name) VALUES (?)")->execute([$name]);
    }
    header('Location: admin_dashboard.php?tab=vms'); exit;
}
// Delete VM
if (isset($_POST['delete_vm'])) {
    $pdo->prepare("DELETE FROM vms WHERE id = ?")->execute([(int)$_POST['vm_id']]);
    header('Location: admin_dashboard.php?tab=vms'); exit;
}
// Assign VMs to Zonals
if (isset($_POST['save_zonal_vms'])) {
    $zonalId = (int)$_POST['zonal_id'];
    $pdo->prepare("DELETE FROM zonal_vm WHERE zonal_id = ?")->execute([$zonalId]);
    foreach ($_POST['vms'] ?? [] as $vmId) {
        $pdo->prepare("INSERT INTO zonal_vm(zonal_id,vm_id) VALUES(?,?)")->execute([$zonalId, (int)$vmId]);
    }
    header('Location: admin_dashboard.php?tab=zonal_vms'); exit;
}
// Pair two Zonals into a Team
if (isset($_POST['pair_zonals_team'])) {
    $z1 = (int)($_POST['pair_zonal1'] ?? 0);
    $z2 = (int)($_POST['pair_zonal2'] ?? 0);
    if ($z1 && $z2 && $z1 !== $z2) {
        $stmt = $pdo->prepare("SELECT name FROM zonals WHERE id = ?");
        $stmt->execute([$z1]); $n1 = $stmt->fetchColumn();
        $stmt->execute([$z2]); $n2 = $stmt->fetchColumn();
        $tname = "$n1 & $n2";
        try {
            $pdo->prepare("INSERT INTO teams(name) VALUES (?)")->execute([$tname]);
            $tid = $pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $tid = $pdo->prepare("SELECT id FROM teams WHERE name = ?")->execute([$tname]);
                $tid = $pdo->lastInsertId();
            } else { throw $e; }
        }
        foreach ([$z1,$z2] as $z) {
            $pdo->prepare("INSERT INTO team_members(team_id,type,member_id) VALUES(?,?,?)")->execute([$tid,'zonal',$z]);
        }
        $vmStmt = $pdo->prepare("SELECT vm_id FROM zonal_vm WHERE zonal_id = ?"); $added = [];
        foreach ([$z1,$z2] as $z) {
            $vmStmt->execute([$z]);
            while ($vm = (int)$vmStmt->fetchColumn()) {
                if (!in_array($vm, $added, true)) {
                    $pdo->prepare("INSERT INTO team_members(team_id,type,member_id) VALUES(?,?,?)")->execute([$tid,'vm',$vm]);
                    $added[] = $vm;
                }
            }
        }
    }
    header('Location: admin_dashboard.php?tab=teams'); exit;
}
// Add Team
if (isset($_POST['add_team'])) {
    $tname = trim($_POST['team_name']);
    $z1 = (int)($_POST['zonal1'] ?? 0);
    $z2 = (int)($_POST['zonal2'] ?? 0);
    if ($tname !== '' && $z1 || $z2) {
        try {
            $pdo->prepare("INSERT INTO teams(name) VALUES (?)")->execute([$tname]);
            $tid = $pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $pdo->prepare("SELECT id FROM teams WHERE name = ?")->execute([$tname]);
                $tid = $pdo->lastInsertId();
            } else { throw $e; }
        }
        foreach ([$z1,$z2] as $z) {
            $pdo->prepare("INSERT INTO team_members(team_id,type,member_id) VALUES(?,?,?)")->execute([$tid,'zonal',$z]);
        }
        $vmStmt = $pdo->prepare("SELECT vm_id FROM zonal_vm WHERE zonal_id = ?"); $attached = [];
        foreach ([$z1,$z2] as $z) {
            $vmStmt->execute([$z]); while ($vm = (int)$vmStmt->fetchColumn()) {
                if (!in_array($vm, $attached, true)) {
                    $pdo->prepare("INSERT INTO team_members(team_id,type,member_id) VALUES(?,?,?)")->execute([$tid,'vm',$vm]);
                    $attached[] = $vm;
                }
            }
        }
    }
    header('Location: admin_dashboard.php?tab=teams'); exit;
}
// Delete Team
if (isset($_POST['delete_team'])) {
    $tid = (int)$_POST['team_id'];
    $pdo->prepare("DELETE FROM winners WHERE team_id = ?")->execute([$tid]);
    $pdo->prepare("DELETE FROM team_members WHERE team_id = ?")->execute([$tid]);
    $pdo->prepare("DELETE FROM teams WHERE id = ?")->execute([$tid]);
    header('Location: admin_dashboard.php?tab=teams'); exit;
}
        // Save Winners (manual form submission)
if (isset($_POST['save_winners'])) {
    // 1) Delete any existing winners for this date
    $pdo->prepare("DELETE FROM winners WHERE date = ?")
        ->execute([$searchDate]);

    // 2) Re-insert the positions
    $posArr = [
        1 => $_POST['winner1'] ?? null,
        2 => $_POST['winner2'] ?? null,
        3 => $_POST['winner3'] ?? null,
        4 => $_POST['loser']   ?? null,
    ];
    $st = $pdo->prepare("
        INSERT INTO winners (date, position, team_id)
        VALUES (?, ?, ?)
    ");
    foreach ($posArr as $position => $teamId) {
        if ($teamId) {
				 $st->execute([
                $searchDate,
                $position,
                (int)$teamId
            ]);
        }
    }

    // 3) Redirect back
    header("Location: admin_dashboard.php?tab=select_winner&date={$searchDate}");
    exit;
}

// Handle AJAX JSON request for saving winners without reload
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_SERVER["CONTENT_TYPE"])
    && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input["positions"]) && is_array($input["positions"])) {
        $today = date('Y-m-d');
        $positions = $input["positions"];

        // switch to cog DB
        $pdo->exec("USE cog");

        // delete existing winners
        $pdo->prepare("DELETE FROM winners WHERE date = ?")->execute([$today]);

        // insert new winners
        $ins = $pdo->prepare("INSERT INTO winners (date, position, team_id) VALUES (?, ?, ?)");
        foreach ($positions as $pos => $teamId) {
            if ($teamId) {
                $ins->execute([$today, $pos, (int)$teamId]);
            }
        }

        echo json_encode(["success" => true]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data structure"]);
        exit;
    }
}

// ─── FETCH STATIC DATA ───────────────────────────────────────────────────────
$zonals      = $pdo->query("SELECT * FROM zonals ORDER BY id")->fetchAll();
$vms         = $pdo->query("SELECT * FROM vms ORDER BY id")->fetchAll();
$teams       = $pdo->query("SELECT * FROM teams WHERE status='enabled' ORDER BY id")->fetchAll();
$members     = $pdo->query("SELECT * FROM team_members")->fetchAll();
$zonalVmRows = $pdo->query("SELECT zonal_id,vm_id FROM zonal_vm")->fetchAll();
$winnerRows  = $pdo->prepare("SELECT position,team_id FROM winners WHERE date = ?");
$winnerRows->execute([$searchDate]);
$winnerRows  = $winnerRows->fetchAll();
$zonals      = $pdo->query("SELECT * FROM zonals ORDER BY id")->fetchAll();
$vms         = $pdo->query("SELECT * FROM vms ORDER BY id")->fetchAll();
$teamsAll    = $pdo->query("SELECT * FROM teams ORDER BY id")->fetchAll(); // fetch ALL for admin
$teamsEnabled= $pdo->query("SELECT * FROM teams WHERE status='enabled' ORDER BY id")->fetchAll(); // only enabled for winners & averages
$members     = $pdo->query("SELECT * FROM team_members")->fetchAll();
$zonalVmRows = $pdo->query("SELECT zonal_id,vm_id FROM zonal_vm")->fetchAll();

$memberMap   = [];
foreach ($members as $m) {
    $memberMap[$m['team_id']][$m['type']][] = $m['member_id'];
}
$zonalVmMap = [];
foreach ($zonalVmRows as $zr) {
    $zonalVmMap[$zr['zonal_id']][] = $zr['vm_id'];
}
$todayWins = [];
foreach ($winnerRows as $w) {
    $todayWins[(int)$w['position']] = (int)$w['team_id'];
}

$tabs = ['zonals','vms','teams','zonal_vms','select_winner','winner_history'];
$tab  = $_GET['tab'] ?? 'teams';
if (!in_array($tab, $tabs, true)) {
    $tab = 'teams';
}

function fetchMembers(PDO $pdo, int $teamId) {
    $map   = [['type'=>'zonal','tbl'=>'zonals'], ['type'=>'vm','tbl'=>'vms']];
    $names = [];
    foreach ($map as $m) {
        $s = $pdo->prepare("
            SELECT l.name
              FROM team_members tm
              JOIN {$m['tbl']} l
                ON tm.member_id = l.id
             WHERE tm.team_id = ?
               AND tm.type = ?
             ORDER BY l.name DESC
        ");
        $s->execute([$teamId, $m['type']]);
        while ($n = $s->fetchColumn()) {
            $names[] = $n;
        }
    }
    return $names;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php if ($tab === 'select_winner'): ?>
	<meta http-equiv="refresh" content="5">
  <?php endif; ?>
  <meta charset="UTF-8"/>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>COG Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>.nav-link.active{background:#ffd700;color:#8b0000;}body{padding:1rem;}</style>
</head>
<body>
  <div class="d-flex justify-content-between align-items-center mb-3">
  <h1>COG Admin Dashboard</h1>
  <form class="d-inline">
<button type="submit" href="atticagold.in/cog" name="logout" class="btn btn-outline-danger">Logout</button>
  </form>
</div>
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link <?= $tab==='zonals'?'active':'' ?>" href="?tab=zonals&date=<?php echo urlencode($searchDate); ?>">Zonals</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='vms'?'active':'' ?>" href="?tab=vms&date=<?php echo urlencode($searchDate); ?>">VMs</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='teams'?'active':'' ?>" href="?tab=teams&date=<?php echo urlencode($searchDate); ?>">Teams</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='zonal_vms'?'active':'' ?>" href="?tab=zonal_vms&date=<?php echo urlencode($searchDate); ?>">Assign VMs→Zonal</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='select_winner'?'active':'' ?>" href="?tab=select_winner&date=<?php echo urlencode($searchDate); ?>">Winners</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='winner_history'?'active':'' ?>" href="?tab=winner_history&date=<?php echo urlencode($searchDate); ?>">Winner History</a></li>
  </ul>

  <!-- ZONALS -->
  <?php if($tab==='zonals'): ?>
    <h2>Manage Zonals</h2>
    <form method="post" class="mb-3">
      <div class="input-group">
        <input name="zonal_name" class="form-control" placeholder="New Zonal" required>
        <button name="add_zonal" class="btn btn-primary">Add</button>
      </div>
    </form>
    <?php if($zonals): ?>
      <table class="table table-bordered">
        <tr><th>ID</th><th>Name</th><th>Delete</th></tr>
        <?php foreach($zonals as $z): ?>
          <tr>
            <td><?= $z['id'] ?></td>
            <td><?= htmlspecialchars($z['name'],ENT_QUOTES) ?></td>
            <td>
              <form method="post" class="d-inline">
                <input hidden name="zonal_id" value="<?= $z['id']?>">
                <button name="delete_zonal" class="btn btn-danger btn-sm">×</button>
              </form>
            </td>
          </tr>
        <?php endforeach;?>
      </table>
    <?php endif;?>
  <?php endif;?>

  <!-- VMS -->
  <?php if($tab==='vms'): ?>
    <h2>Manage VMs</h2>
    <form method="post" class="mb-3">
      <div class="input-group">
        <input name="vm_name" class="form-control" placeholder="New VM" required>
        <button name="add_vm" class="btn btn-primary">Add</button>
      </div>
    </form>
    <?php if($vms): ?>
      <table class="table table-bordered">
        <tr><th>ID</th><th>Name</th><th>Delete</th></tr>
        <?php foreach($vms as $v): ?>
 <tr>
            <td><?= $v['id']?></td>
            <td><?= htmlspecialchars($v['name'],ENT_QUOTES)?></td>
            <td>
              <form method="post" class="d-inline">
                <input hidden name="vm_id" value="<?= $v['id']?>">
                <button name="delete_vm" class="btn btn-danger btn-sm">×</button>
              </form>
            </td>
          </tr>
        <?php endforeach;?>
      </table>
    <?php endif;?>
  <?php endif;?>

  <!-- ASSIGN VMs→ZONAL -->
  <?php if($tab==='zonal_vms'): ?>
    <h2>Assign VMs to Zonal</h2>
    <form method="post" class="mb-3">
      <div class="mb-2"><label>Zonal:</label>
        <select name="zonal_id" class="form-select" required>
          <option value="">-- select --</option>
          <?php foreach($zonals as $z): ?>
            <option value="<?= $z['id']?>"><?= htmlspecialchars($z['name'],ENT_QUOTES)?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div class="row mb-2">
        <?php foreach($vms as $v): ?>
          <div class="col-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="vms[]" id="vm<?= $v['id']?>" value="<?= $v['id']?>">
              <label class="form-check-label" for="vm<?= $v['id']?>"><?= htmlspecialchars($v['name'],ENT_QUOTES)?></label>
            </div>
          </div>
        <?php endforeach;?>
      </div>
      <button name="save_zonal_vms" class="btn btn-success">Save</button>
    </form>
    <?php if($zonalVmMap): ?>
      <h5>Current</h5>
      <ul class="list-group">
        <?php foreach($zonalVmMap as $zid=>$list): ?>
          <li class="list-group-item">
            <strong><?= htmlspecialchars(array_column($zonals,'name','id')[$zid]??'',ENT_QUOTES)?></strong>:
            <?= implode(', ', array_map(fn($i)=>htmlspecialchars(array_column($vms,'name','id')[$i]??'',ENT_QUOTES),$list)) ?>
          </li>
        <?php endforeach;?>
      </ul>
    <?php endif;?>
  <?php endif;?>

  <!-- TEAMS -->
  <?php if ($tab === 'teams'): ?>
    <h2>Manage Teams</h2>
    <!-- Add Team Form -->
 <form method="post" class="mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Team Name</label>
          <input name="team_name" class="form-control" placeholder="Enter team name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Zonal A</label>
          <select name="zonal1" class="form-select">
            <option value="">-- select zonal A --</option>
            <?php foreach ($zonals as $z): ?>
              <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name'], ENT_QUOTES)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Zonal B</label>
          <select name="zonal2" class="form-select">
            <option value="">-- select zonal B --</option>
            <?php foreach ($zonals as $z): ?>
              <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name'], ENT_QUOTES)?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <button name="add_team" class="btn btn-primary mt-3">
        <i class="fas fa-plus-circle me-1"></i> Create Team
      </button>
    </form>
    <!-- Existing Teams -->
    <?php if ($teams): ?>
      <table class="table table-bordered">
  <thead class="table-light">
    <tr>
      <th>Team Name</th>
      <th>Zonals</th>
      <th>VMs</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($teamsAll as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['name'], ENT_QUOTES)?></td>
        <td><?= implode(', ', array_map(fn($i)=>htmlspecialchars(array_column($zonals,'name','id')[$i]??'',ENT_QUOTES), $memberMap[$t['id']]['zonal'] ?? [])) ?></td>
        <td><?= implode(', ', array_map(fn($i)=>htmlspecialchars(array_column($vms,'name','id')[$i]??'',ENT_QUOTES), $memberMap[$t['id']]['vm'] ?? [])) ?></td>
        <td>
          <?php if ($t['status']==='enabled'): ?>
            <span class="badge bg-success">Enabled</span>
          <?php else: ?>
            <span class="badge bg-secondary">Disabled</span>
          <?php endif; ?>
        </td>
        <td>
          <form method="post" class="d-inline">
            <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
            <input type="hidden" name="current_status" value="<?= $t['status'] ?>">
<button name="toggle_team" class="btn btn-sm <?= $t['status']==='enabled' ? 'btn-warning' : 'btn-success' ?>">
              <?= $t['status']==='enabled' ? 'Disable' : 'Enable' ?>
            </button>
          </form>
          <form method="post" class="d-inline">
            <input hidden name="team_id" value="<?= $t['id'] ?>">
            <button name="delete_team" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
    <?php endif; ?>
  <?php endif; ?>

<?php if ($tab === 'select_winner'): ?>
<?php
  $pdo->exec("USE atticaaws");

  // FETCH COG METRICS
  $sql = "
    SELECT e.name AS empName,
           ROUND(
             IF(
               (COUNT(DISTINCT t.id) + COUNT(DISTINCT w.id)) > 0,
               (COUNT(DISTINCT t.id) / (COUNT(DISTINCT t.id) + COUNT(DISTINCT w.id))) * 100,
               0
             ), 2
           ) AS COG
    FROM branch b
    LEFT JOIN walkin w
      ON w.branchId = b.branchId
     AND w.date   = :searchDate
     AND w.issue != 'Rejected'
    LEFT JOIN trans t
      ON t.branchId = b.branchId
     AND t.date   = :searchDate
     AND t.status= 'Approved'
    LEFT JOIN employee e
      ON e.empId = b.ezviz_vc
    WHERE b.ezviz_vc IN (
      '1003445','1000211','1003665','1000036','1000423','1002638',
      '1002342','1001627','1003908','1000336','1004104','1002063',
      '1000524','1000735','1000816','1003764'
    )
    GROUP BY e.name
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['searchDate'=>$searchDate]);
  $rows = $stmt->fetchAll();

  $cogMap = [];
  foreach ($rows as $r) {
      $cogMap[strtolower($r['empName'])] = $r['COG'];
  }

  // BUILD SINGLE TEAM AVGS ARRAY
  $teamAvgs = [];
  $zonalNames = array_column($zonals, 'name', 'id');

  foreach ($teamsEnabled as $t) {
      $tid   = $t['id'];
      $zonal = $memberMap[$tid]['zonal'] ?? [];

      if (count($zonal) === 2 &&  $zonalNames[$zonal[0]] != $zonalNames[$zonal[1]]) {
          $n1 = $zonalNames[$zonal[0]] ?? '';
          $n2 = $zonalNames[$zonal[1]] ?? '';
          $c1 = $cogMap[strtolower($n1)] ?? 0;
          $c2 = $cogMap[strtolower($n2)] ?? 0;
          $avg = ($c1 + $c2) / 2;
          $teamAvgs[] = ['id'=>$tid,'team'=>$t['name'],'z1'=>$n1,'c1'=>$c1,'z2'=>$n2,'c2'=>$c2,'avg'=>$avg];
      } elseif (count($zonal) === 2 && $zonalNames[$zonal[0]] == $zonalNames[$zonal[1]]) {
          $n1 = $zonalNames[$zonal[0]] ?? '';
	  $c1 = $cogMap[strtolower($n1)] ?? 0;
	  $c2 = $c1;
	  $avg = ($c1 +$c2) / 2;
          $teamAvgs[] = ['id'=>$tid,'team'=>$t['name'],'z1'=>$n1,'c1'=>$c1,'z2'=>" ",'c2'=>$c1,'avg'=>$c1];
      }
  }

  usort($teamAvgs, fn($a,$b)=> $b['avg'] <=> $a['avg']);
  $defaults = array_column(array_slice($teamAvgs, 0, 3), 'id');
  $low      = $teamAvgs ? $teamAvgs[count($teamAvgs) - 1]['id'] : null;

  // AUTO SAVE FOR TODAY
  if ($searchDate === date('Y-m-d')) {
      $pdo->exec("USE cog");
      $today = date('Y-m-d');
      $pdo->prepare("DELETE FROM winners WHERE date = ?")->execute([$today]);
      $ins = $pdo->prepare("INSERT INTO winners (date, position, team_id) VALUES (?, ?, ?)");
      $auto = [
          1 => $defaults[0] ?? null,
          2 => $defaults[1] ?? null,
          3 => $defaults[2] ?? null,
          4 => $low
      ];
      foreach ($auto as $pos => $teamId) {
          if ($teamId) {
              $ins->execute([$today, $pos, (int)$teamId]);
          }
      }
  }

  // LOAD SELECTED FOR FORM
  $pdo->exec("USE cog");
  $wr = $pdo->prepare("SELECT position, team_id FROM winners WHERE date = ?");
  $wr->execute([$searchDate]);
  $todayWins = [];
  foreach ($wr->fetchAll() as $w) {
      $todayWins[(int)$w['position']] = (int)$w['team_id'];
  }
?>

<h2>Select Today's Top-3 & Loser</h2>
<form method="post" id="select_winners">
  <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="mb-2">
      <label>Position <?=$i?></label>
      <select name="winner<?=$i?>" class="form-select">
        <option value="">-- none --</option>
        <?php foreach ($teamAvgs as $t):
          $tid = $t['id'];
          $sel = (isset($todayWins[$i]) && $todayWins[$i] === $tid)
               || (!isset($todayWins[$i]) && isset($defaults[$i-1]) && $defaults[$i-1] === $tid);
        ?>
          <option value="<?=$tid?>" <?=$sel?'selected':''?>><?=htmlspecialchars($t['team'],ENT_QUOTES)?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endfor; ?>

  <div class="mb-2">
    <label>Loser (4️⃣)</label>
    <select name="loser" class="form-select">
      <option value="">-- none --</option>
      <?php foreach ($teamAvgs as $t):
        $tid = $t['id'];
        $sel = (isset($todayWins[4]) && $todayWins[4] === $tid)
             || (!isset($todayWins[4]) && $low === $tid);
      ?>
        <option value="<?=$tid?>" <?=$sel?'selected':''?>><?=htmlspecialchars($t['team'],ENT_QUOTES)?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <button name="save_winners" class="btn btn-success">Save All</button>
</form>

<h3>Team COG Averages (<?=htmlspecialchars($searchDate,ENT_QUOTES)?>)</h3>
<?php if ($teamAvgs): ?>
  <table class="table table-striped table-bordered">
    <thead class="table-light">
      <tr>
        <th>Team Name</th>
        <th>Zonal</th>
	<th>Zonal COG (%)</th>
       <!-- <th>Zonal B</th>
        <th>Zonal B COG (%)</th>-->
        <!--<th>Avg COG (%)</th>-->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($teamAvgs as $t): ?>
        <tr>
          <td><?=htmlspecialchars($t['team'],ENT_QUOTES)?></td>
          <td><?=htmlspecialchars($t['z1'],ENT_QUOTES)?></td>
          <td><?=htmlspecialchars($t['c1'],ENT_QUOTES)?>%</td>
         <!-- <td><!?=htmlspecialchars($t['z2'],ENT_QUOTES)?></td>
          <td class="text-end"><!?=htmlspecialchars($t['c2'],ENT_QUOTES)?>%</td>-->
          <!--<td class="text-end"><!?=htmlspecialchars($t['avg'],ENT_QUOTES)?>%</td>-->
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p><em>No data found for <?=htmlspecialchars($searchDate,ENT_QUOTES)?>.</em></p>
<?php endif; ?>
<?php endif; ?>


<?php if($tab==='winner_history'): ?>
  <h2>Winner History</h2>

  <!-- Date picker form -->
  <form method="get" class="mb-3">
    <input type="hidden" name="tab" value="winner_history">
    <label>Select Date:</label>
    <input type="date" name="history_date" class="form-control d-inline-block" style="width:auto;" value="<?=htmlspecialchars($_GET['history_date'] ?? '', ENT_QUOTES)?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <?php
    $historyDate = $_GET['history_date'] ?? null;

    if ($historyDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $historyDate)) {
      $stmt = $pdo->prepare("
        SELECT w.date, w.position, w.team_id, t.name AS team_name
          FROM winners w
          JOIN teams t ON w.team_id = t.id
         WHERE w.date = ?
         ORDER BY w.position
      ");
      $stmt->execute([$historyDate]);
      $rows = $stmt->fetchAll();
      if ($rows):
  ?>
    <h5>Winners on <?=htmlspecialchars($historyDate, ENT_QUOTES)?></h5>
    <table class="table table-bordered">
      <tr><th>Position</th><th>Team</th></tr>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['position'], ENT_QUOTES)?></td>
		 <td><?=htmlspecialchars($r['team_name'], ENT_QUOTES)?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No winners found on <?=htmlspecialchars($historyDate, ENT_QUOTES)?>.</p>
  <?php endif; } ?>

  <h4 class="mt-5">Winners for Current Month</h4>
  <?php
    $thisMonth = date('Y-m');
    $stmt = $pdo->prepare("
      SELECT w.date, w.position, t.name AS team_name
        FROM winners w
        JOIN teams t ON w.team_id = t.id
       WHERE w.date LIKE ?
       ORDER BY w.date, w.position
    ");
    $stmt->execute(["$thisMonth%"]);
    $monthRows = $stmt->fetchAll();

    $grouped = [];
    foreach ($monthRows as $row) {
      $grouped[$row['date']][] = $row;
    }

    if ($grouped):
  ?>
    <?php foreach ($grouped as $date => $entries): ?>
      <h6><?=htmlspecialchars($date, ENT_QUOTES)?></h6>
      <table class="table table-sm table-striped mb-4">
        <tr><th>Position</th><th>Team</th></tr>
        <?php foreach ($entries as $e): ?>
          <tr>
            <td><?=htmlspecialchars($e['position'], ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($e['team_name'], ENT_QUOTES)?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No winners recorded for this month yet.</p>
  <?php endif; ?>
<?php endif; ?>


</body>
<script>
const currentTab = new URLSearchParams(window.location.search).get('tab');
if (currentTab === 'select_winner') {
    window.addEventListener('load', () => {
        setTimeout(() => {
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('date');
            if (!dateInput || dateInput.value !== today) return;

            const positions = {};
            for (let i = 1; i <= 3; i++) {
                const val = document.querySelector(`select[name=winner${i}]`)?.value || null;
                positions[i] = val && val !== "" ? parseInt(val) : null;
 		}
            const loserVal = document.querySelector(`select[name=loser]`)?.value || null;
            positions[4] = loserVal && loserVal !== "" ? parseInt(loserVal) : null;

            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ positions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notice = document.createElement('div');
                    notice.textContent = "✅ Winners saved successfully!";
                    notice.style.cssText = "position:fixed;top:10px;right:10px;background:#28a745;color:#fff;padding:10px;border-radius:5px;z-index:9999;";
                    document.body.appendChild(notice);
                    setTimeout(() => notice.remove(), 2000);
                } else {
                    console.error('Failed:', data.message);
                    alert("Failed to save winners. Check console.");
                }
            })
            .catch(err => {
                console.error('AJAX error', err);
                alert("Network error while saving winners.");
            });
        }, 10000);
    });
}
</script>

</html>


