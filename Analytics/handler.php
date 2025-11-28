	<?php
	session_start();

	// ─── CONFIG & AUTH ──────────────────────────────────────────────────────────
	$clientsFile = __DIR__ . '/clients.json';
	$statusPath  = __DIR__ . '/status.json';

	// Load clients.json
	$clients = file_exists($clientsFile)
		? json_decode(file_get_contents($clientsFile), true) ?: []
		: [];

	// Must be logged in as admin
	$currentAdmin = $_SESSION['branchId'] ?? null;
	if (!$currentAdmin || ($clients[$currentAdmin]['role'] ?? '') !== 'admin') {
		header('Location: login.php');
		exit;
	}

	// Determine branches assigned to this admin
	$assignedList = $clients[$currentAdmin]['branches'] ?? '';
	$parts        = $assignedList === '' ? [] : array_map('trim', explode(',', $assignedList));
	$assigned     = array_values(array_intersect($parts, array_keys($clients)));
	sort($assigned);

	// Load statuses once
	$allStatuses = file_exists($statusPath)
		? json_decode(file_get_contents($statusPath), true) ?: []
		: [];

	// ─── POLLING ENDPOINT ────────────────────────────────────────────────────
	if (isset($_GET['poll']) && $_GET['poll'] === '1') {
		header('Content-Type: application/json');
		$out = [];
		foreach ($assigned as $bid) {
			$listenerStatus = $clients[$bid]['listener_status'] ?? 'offline';
			$lastSeen = $clients[$bid]['last_seen'] ?? 0;
			$now = time();

			// Check time differences
			if ($listenerStatus !== 'offline') {
				if (($now - $lastSeen) > 30) {
					$clients[$bid]['listener_status'] = 'offline';
					file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
					$listenerStatus = 'offline';
				} elseif (($now - $lastSeen) > 2) {
					$listenerStatus .= ' (stale)';
				}
			}

			$out[] = [
				'branchId'       => $bid,
				'listenerStatus' => $listenerStatus,
				'shouldRecord'   => !empty($allStatuses[$bid]['record']),
			];
		}
		echo json_encode($out);
		exit;
	}

	// ─── TOGGLE ENDPOINT ───────────────────────────────────────────────
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		header('Content-Type: application/json');
		$payload = json_decode(file_get_contents('php://input'), true) ?: [];
		if (empty($payload['branchId']) || empty($payload['action'])) {
			echo json_encode(['success' => false, 'error' => 'Missing branchId or action.']);
			exit;
		}

		$target = $payload['branchId'];
		$action = $payload['action'];

		$all = file_exists($statusPath)
			? json_decode(file_get_contents($statusPath), true) ?: []
			: [];
		if (!is_array($all[$target] ?? null)) {
			$all[$target] = ['record' => false];
		}
		$all[$target]['record'] = ($action === 'start');
		file_put_contents($statusPath, json_encode($all, JSON_PRETTY_PRINT));

		echo json_encode(['success' => true]);
		exit;
	}

	// ─── RENDER DASHBOARD ────────────────────────────────────────────────────────
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="icon" type="image/png" href="images/favicon.png">
		<title>Attica Analytics - Admin Dashboard – <?= htmlspecialchars($currentAdmin) ?></title>
		<style>
			body { font-family: Arial; background: #760107; margin: 0; padding: 0; }
			#sidebar {
				position: fixed; top: 0; left: 0; width: 220px; height: 100%;
				background: #900; color: white; display: flex; flex-direction: column;
				justify-content: space-between; box-shadow: 2px 0 5px rgba(0,0,0,0.3); z-index: 1000;
			}
			#sidebar h2 { margin: 20px 20px 0; }
			#sidebar a {
				color: white; text-decoration: none; display: block; margin: 15px 20px;
				font-weight: bold;
			}
			#sidebar .bottom {
				padding: 20px; border-top: 1px solid #f8e75c;
			}
			#main-content {
				margin-left: 240px; padding: 20px;
			}
			table {
				width: 100%; max-width: 900px; margin: 20px auto;
				border-collapse: collapse; background: white;
				box-shadow: 0 0 10px rgba(0,0,0,0.1);
			}
			th, td {
				padding: 12px 8px; text-align: center;
				border-bottom: 1px solid #ddd;
				word-break: break-word;
			}
			th { background: #f8e75c; color: #900; font-weight: bold; }
			button.toggle-btn {
				padding: 6px 12px; border: none; border-radius: 4px;
				cursor: pointer; font-weight: bold;
			}
			button.start, button.stop {
				background: #f8e75c; color: #900;
			}
			 .header { background: #760107; padding: 10px 20px; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
			.header img { height: 60px; }
			.header h1 { margin: 0 0 0 20px; color: white; font-size: 24px; }
		</style>
	</head>
	<body>

	<div id="sidebar">
		<div>
			<h2>Dashboard</h2>
			<?php if (($currentAdmin === 'admin') || ($currentAdmin === 'AtticaMaster')): ?>
				<a href="recordings.php">🎙 View Recordings</a>
				<a href="manage.php">👤 Manage Users</a>
			<?php endif; ?>
		</div>
		<div class="bottom">
			<a href="logout.php" style="background:#f8e75c; color:#900; padding:10px 15px; border-radius:5px; text-align:center;">Logout</a>
		</div>
	</div>

	<div id="main-content">
	<div class="header" style="display: flex; justify-content: space-between; align-items: center;">
		<div style="display: flex; align-items: center;">
			<img src="images/group-of-attica-gold-companies.jpg" alt="Logo">
			<h1 style="color:white;">Admin Dashboard</h1>    
		</div>
	</div>
	<p style="color:white;">Logged in as <strong><?= htmlspecialchars($currentAdmin) ?></strong></p>
		

		<table>
			<thead>
				<tr>
					<th>Branch ID</th>
					<th>Branch Name</th>
					<th>Assigned Admin</th>
					<th>Listener Status</th>
					<th>Recording Flag</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
	<?php foreach ($assigned as $bid):
		$listenerStatus = $clients[$bid]['listener_status'] ?? 'offline';
		$lastSeen = $clients[$bid]['last_seen'] ?? 0;
		$now = time();
		if ($listenerStatus !== 'offline' && ($now - $lastSeen > 5)) {
			$listenerStatus = 'stale';
		}
		$isRecording = !empty($allStatuses[$bid]['record']);
		$branchName = $clients[$bid]['name'] ?? 'N/A';

		$adminListArray = [];
		foreach ($clients as $cid => $cdata) {
			if (($cdata['role'] ?? '') === 'admin') {
				$branches = array_map('trim', explode(',', $cdata['branches'] ?? ''));
				if (in_array($bid, $branches, true) && $cid !== 'admin' && $cid !== 'AtticaMaster') {
					$adminListArray[] = $cid;
				}
			}
		}
		$adminList = implode(', ', $adminListArray);
	?>
	<tr data-branch="<?= htmlspecialchars($bid) ?>">
		<td><?= htmlspecialchars($bid) ?></td>
		<td><?= htmlspecialchars($branchName) ?></td>
		<td><?= htmlspecialchars($adminList) ?></td>
		<td class="listener-status"><?= htmlspecialchars($listenerStatus) ?></td>
		<td class="rec-flag"><?= $isRecording ? 'ON' : 'OFF' ?></td>
		<td>
			<?php if (!$isRecording): ?>
				<button class="toggle-btn start">Start</button>
			<?php else: ?>
				<button class="toggle-btn stop">Stop</button>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<script>
	document.querySelectorAll('button.toggle-btn').forEach(btn => {
		btn.addEventListener('click', async e => {
			const row = e.target.closest('tr');
			const branchId = row.dataset.branch;
			const action = e.target.classList.contains('start') ? 'start' : 'stop';
			e.target.disabled = true;
			try {
				const resp = await fetch('handler.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ branchId, action })
				});
				const json = await resp.json();
				if (json.success) {
					const recTd = row.querySelector('.rec-flag');
					if (action === 'start') {
						recTd.textContent = 'ON';
						e.target.textContent = 'Stop';
						e.target.classList.replace('start', 'stop');
					} else {
						recTd.textContent = 'OFF';
						e.target.textContent = 'Start';
						e.target.classList.replace('stop', 'start');
					}
				}
			} catch (_) {}
			e.target.disabled = false;
		});
	});
	async function refreshStatuses() {
		const resp = await fetch('handler.php?poll=1');
		const list = await resp.json();
		list.forEach(item => {
			const row = document.querySelector(`tr[data-branch="${item.branchId}"]`);
			if (!row) return;
			row.querySelector('.listener-status').textContent = item.listenerStatus;
			const recTd = row.querySelector('.rec-flag');
			const btn = row.querySelector('button.toggle-btn');
			if (item.shouldRecord) {
				recTd.textContent = 'ON';
				btn.textContent = 'Stop';
				btn.classList.replace('start', 'stop');
			} else {
				recTd.textContent = 'OFF';
				btn.textContent = 'Start';
				btn.classList.replace('stop', 'start');
			}
			if ((item.listenerStatus === 'ready' || item.listenerStatus === 'idle') && !item.shouldRecord) {
				btn.disabled = false;
			} else if (item.shouldRecord) {
				btn.disabled = false;
			} else {
				btn.disabled = true;
			}
		});
	}
	document.querySelectorAll('button.toggle-btn').forEach(btn => btn.disabled = true);
	refreshStatuses();
	setInterval(refreshStatuses, 1000);
	</script>
	</body>
	</html>
