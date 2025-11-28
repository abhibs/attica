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
$dsn    = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $dbUser, $dbPass, $options);

function fetchMembersByType(PDO $pdo, int $teamId, string $type, string $tbl): array {
    $stm = $pdo->prepare(
        "SELECT l.name
           FROM team_members tm
           JOIN {$tbl} l ON tm.member_id = l.id
          WHERE tm.team_id = ? AND tm.type = ?"
    );
    $stm->execute([$teamId, $type]);
    return $stm->fetchAll(PDO::FETCH_COLUMN);
}

// ─── FETCH DATA FOR TODAY ─────────────────────────────────────────────────────
$pdo->exec("USE atticaaws");
$stmt = $pdo->query("
  SELECT e.name AS empName,
         ROUND(
           IF(
             (COUNT(DISTINCT t.id) + COUNT(DISTINCT w.id)) > 0,
             (COUNT(DISTINCT t.id) / (COUNT(DISTINCT t.id) + COUNT(DISTINCT w.id))) * 100,
             0
           ), 2
         ) AS COG
    FROM branch b
    LEFT JOIN walkin w ON w.branchId = b.branchId AND w.date=CURDATE() AND w.issue != 'Rejected'
    LEFT JOIN trans t  ON t.branchId = b.branchId AND t.date=CURDATE() AND t.status='Approved'
    LEFT JOIN employee e ON e.empId = b.ezviz_vc
	 WHERE b.ezviz_vc IN ('1003445','1000211','1003665','1000036','1000423','1002638',
                       '1002342','1001627','1003908','1000336','1004104','1002063',
                       '1000524','1000735','1000816')
  GROUP BY e.name
");
$cogMap = [];
foreach ($stmt->fetchAll() as $r) {
    $cogMap[strtolower($r['empName'])] = $r['COG'];
}
$pdo->exec("USE cog");

$stmt = $pdo->prepare("
  SELECT w.position, w.team_id, t.name AS team_name
  FROM winners w
  JOIN teams t ON w.team_id = t.id
  WHERE w.date = CURDATE()
  ORDER BY w.position
");
$stmt->execute();
$rows = $stmt->fetchAll();
$toppers = [];
$loser = null;
foreach ($rows as $r) {
    if ($r['position'] <= 3) $toppers[$r['position']] = $r;
    if ($r['position'] == 4) $loser = $r;
}

// ─── IF AJAX HTML REQUEST ─────────────────────────────────────────────────────
if (isset($_GET['json'])) {
    ob_start();
    ?>
    <?php if (empty($rows)): ?>
      <p><em>No winners for today.</em></p>
    <?php else: ?>
      <?php foreach ($toppers as $pos => $win):
        $icon = $pos==1?'🏆':($pos==2?'🥈':'🥉');
        $zonals = fetchMembersByType($pdo, $win['team_id'], 'zonal', 'zonals');
        $vms = fetchMembersByType($pdo, $win['team_id'], 'vm', 'vms');
        $c1 = $cogMap[strtolower($zonals[0] ?? '')] ?? 0;
        $c2 = $cogMap[strtolower($zonals[1] ?? '')] ?? 0;
        $avgCog = round(($c1 + $c2) / 2, 2);
      ?>
      <div class="panel topper-panel">
        <div style="font-size:4rem; color:#ffd700;"><?= $icon ?> <?= strtoupper($win['team_name']) ?></div>
        <div style="font-size:4rem; text-align:center;">
          <span style="color:#ffd700;font-size:4rem;"><?= $avgCog ?>%</span>
                </div>
        <div class="zonal-grid" style="font-size:4rem; text-align:center;">
          <ul class="member-list"><?php foreach ($zonals as $zm):
            $cog = $cogMap[strtolower($zm)] ?? 0;
          ?><li><?= htmlspecialchars($zm,ENT_QUOTES) ?> (<?= $cog ?>%)</li><?php endforeach; ?></ul>
        </div>
        <div class="vm-grid" style="font-size:3rem; text-align:center;">
          <ul class="member-list"><?php foreach ($vms as $vm): ?><li><?= htmlspecialchars($vm,ENT_QUOTES) ?></li><?php endforeach; ?></ul>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if ($loser):
        $zonals = fetchMembersByType($pdo, $loser['team_id'], 'zonal', 'zonals');
        $vms = fetchMembersByType($pdo, $loser['team_id'], 'vm', 'vms');
        $c1 = $cogMap[strtolower($zonals[0] ?? '')] ?? 0;
        $c2 = $cogMap[strtolower($zonals[1] ?? '')] ?? 0;
        $avgCog = round(($c1 + $c2) / 2, 2);
      ?>
      <div class="panel topper-panel loser-panel" style="color:#ff5555;">
        <div style="font-size:4rem;">😭 <?= strtoupper($loser['team_name']) ?></div>
        <div style="font-size:4rem; text-align:center;">
          <span style="color:#ff5555;font-size:4rem;"><?= $avgCog ?>%</span></div>
        <div class="zonal-grid" style="font-size:4rem; text-align:center;">
          <ul class="member-list"><?php foreach ($zonals as $zm):
            $cog = $cogMap[strtolower($zm)] ?? 0;
          ?><li><?= htmlspecialchars($zm,ENT_QUOTES) ?> (<?= $cog ?>%)</li><?php endforeach; ?></ul>
        </div>
        <div class="vm-grid" style="font-size:3rem; text-align:center;">
          <ul class="member-list"><?php foreach ($vms as $vm): ?><li><?= htmlspecialchars($vm,ENT_QUOTES) ?></li><?php endforeach; ?></ul>
        </div>
      </div>
      <?php endif; ?>
    <?php endif;
    echo ob_get_clean();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<link rel="icon" type="image/png" href="images/favicon.png">
<title>COG Winners Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<style>
  html { zoom: .444; }
  *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
  body { font: 18px sans-serif; background: radial-gradient(circle at top, #1a001a, #000);
         color: #fff; display: flex; flex-direction: column; min-height: 100vh; }
  header, footer { background: #8b0000; padding: 1.5rem; }
  main { flex: 1; padding: 2rem; }
  .panel { background: rgba(0,0,0,0.6); border-radius: 1rem;
           box-shadow: 0 0 20px rgba(255,223,0,0.8); margin-bottom: 2rem; }
  .panel.topper-panel { display: grid; grid-template-columns: 25% 20% 25% 30%;
                        column-gap:2rem; align-items:start; padding:2rem; }
  .zonal-grid,.vm-grid { width:90%; display:grid; grid-template-rows:auto 1fr; row-gap:.5rem; }
  ul.member-list { list-style: none; padding-left: 0; }
  .zonal-grid .member-list li,.vm-grid .member-list li { font-size:3rem; text-align:center; }
  #confetti-canvas { position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none;}
</style>
</head>
<body>
<header style="font-size:3rem">&nbsp; &nbsp;🎊 Today's Toppers 🎊</header>
<main>
  <div class="toppers">

  </div>
</main>
<canvas id="confetti-canvas"></canvas>
<footer style="position:fixed; bottom:0; left:0; width:100%; font-size:1rem; color:#fff; padding:0.75rem 0; text-align:center; z-index:1000; background:#8b0000;">
  By Software Team<br>&copy; <?= date('Y') ?> Attica Gold Company
</footer>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const conf = confetti.create(document.getElementById('confetti-canvas'),{resize:true,useWorker:true});
  const money = confetti.shapeFromText({text:'💵',scalar:1.2});
  const opts = {particleCount:7,spread:60,shapes:[money],scalar:1.2};
  setInterval(()=>conf({...opts,origin:{x:Math.random(),y:0.6}}),10000);

  const container = document.querySelector('.toppers');
  async function updateToppers() {
    try {
      const res = await fetch('?json=1');
      const html = await res.text();
      container.innerHTML = html;
    } catch (err) {
      console.error("Failed to fetch toppers", err);
    }
  }
  setInterval(updateToppers, 10000);
  updateToppers();
});
</script>
</body>
</html>

