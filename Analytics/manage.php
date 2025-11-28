<?php
session_start();
$clientsFile = __DIR__ . '/clients.json';
$response = "";

// Must be logged in as admin
if (empty($_SESSION['branchId'])) {
    header("Location: login.php");
    exit;
}

$clients = file_exists($clientsFile) ? json_decode(file_get_contents($clientsFile), true) : [];
$currentUser = $_SESSION['branchId'];
if (!isset($clients[$currentUser]) || ($clients[$currentUser]['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $branchId       = trim($_POST['branchId'] ?? '');
    $clientHash     = trim($_POST['password'] ?? '');
    $role           = $_POST['role'] ?? 'branch';
    $branchesInput  = trim($_POST['branches'] ?? '');
    $branchName     = trim($_POST['branchName'] ?? '');

    if ($branchId === '' || $clientHash === '') {
        $response = "ID and password are required.";
    } elseif (isset($clients[$branchId])) {
        $response = "ID '{$branchId}' already exists.";
    } else {
        $serverHash = password_hash($clientHash, PASSWORD_DEFAULT);
        $clients[$branchId] = [
            "password"       => $serverHash,
            "desired"        => true,
            "listener_status"=> "offline",
            "last_seen"      => time(),
            "role"           => $role,
            "name"           => $branchName
        ];
        if ($role === 'admin') {
            $clients[$branchId]['branches'] = $branchesInput;
        }
        if ($role === 'branch' && isset($clients['admin'])) {
            $adminList = isset($clients['admin']['branches']) ? explode(',', $clients['admin']['branches']) : [];
            $adminList = array_unique(array_filter(array_map('trim', $adminList)));
            if (!in_array($branchId, $adminList, true)) {
                $adminList[] = $branchId;
            }
            $clients['admin']['branches'] = implode(',', $adminList);
        }
        file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
        $response = "Registration successful for '{$branchId}'.";
    }
}

// Handle admin update and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_admin'])) {
        $adminIdRaw = $_POST['adminId'];
        $assignedRaw = $_POST['assignedBranches'] ?? '';
        // handle array or string
        if (is_array($assignedRaw)) {
            $assignedBranches = trim($assignedRaw[$adminIdRaw] ?? '');
        } else {
            $assignedBranches = trim($assignedRaw);
        }
        $adminId = trim($adminIdRaw);
        if ($adminId !== '' && isset($clients[$adminId]) && $clients[$adminId]['role'] === 'admin') {
            $clients[$adminId]['branches'] = $assignedBranches;
            file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
            $response = "Updated branches for Admin '{$adminId}'.";
        }
    } elseif (isset($_POST['delete_admin'])) {
        $delId = trim($_POST['adminId'] ?? '');
        if (isset($clients[$delId]) && $clients[$delId]['role'] === 'admin' && $delId !== 'admin' && $delId !== 'AtticaMaster') {
            unset($clients[$delId]);
            file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
            $response = "Deleted admin '{$delId}'.";
        }
    }
}

// Handle branch updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_branch'])) {
        $branchIdRaw = $_POST['branchId'] ?? '';
        $nameRaw = $_POST['branchNameEdit'] ?? '';
        if (is_array($nameRaw)) {
            $branchName = trim($nameRaw[$branchIdRaw] ?? '');
        } else {
            $branchName = trim($nameRaw);
        }
        $branchId = trim($branchIdRaw);
        if ($branchId !== '' && isset($clients[$branchId]) && $clients[$branchId]['role'] === 'branch') {
            $clients[$branchId]['name'] = $branchName;
            file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
            $response = "Updated branch name for '{$branchId}'.";
        }
    } elseif (isset($_POST['delete_branch'])) {
        $delId = trim($_POST['deleteId'] ?? '');
        if (isset($clients[$delId]) && $clients[$delId]['role'] === 'branch') {
            unset($clients[$delId]);
            file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));
            $response = "Deleted branch '{$delId}'.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Attica Analytics - User Management</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <style>
        body { font-family: Arial, sans-serif; background: #760107; margin: 0; padding: 0; color: #333; }
        .header { background: #760107; padding: 10px 20px; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .header img { height: 60px; }
        .header h1 { margin: 0 0 0 20px; color: white; font-size: 24px; }
        .container { max-width: 1000px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        h2 { color: #900; margin-bottom: 10px; }
        label { display: block; margin-top: 1em; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 0.5em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"], button { padding: 6px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .register-btn { background: #f8e75c; color: #900; }
        .update-btn { background: #28a745; color: white; margin-right:5px; }
        .delete-btn { background: #dc3545; color: white; }
        .message { margin: 1em 0; font-weight: bold; color: #d00; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; word-wrap: break-word; }
        th { background: #900; color: white; }
    </style>
    <script>
    // SHA-256 hashing for registration form
    async function sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    document.addEventListener('DOMContentLoaded', () => {
        const regForm = document.getElementById('register-form');
        if (regForm) {
            regForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const plain = document.getElementById('plain-password').value;
                const hashed = await sha256(plain);
                document.getElementById('password').value = hashed;
                this.submit();
            });
        }
    });
    function toggleFields() {
        const role = document.getElementById('role').value;
        document.getElementById('branchNameField').style.display = (role === 'branch') ? 'block' : 'none';
        document.getElementById('branchesField').style.display = (role === 'admin') ? 'block' : 'none';
    }
    </script>
</head>
<body>
<div class="header"
     style="display: flex; justify-content: space-between; align-items: center;">   
    <div style="display: flex; align-items: center;">
        <img src="images/group-of-attica-gold-companies.jpg" alt="Logo">
        <h1 style="margin-left: 20px;">User Management Dashboard</h1>
    </div>   
    <div>
        <a href="handler.php" style="background: #f8e75c; color: #900; padding:8px 16px; margin-right:10px; text-decoration:none; border-radius:5px;">Go Back</a>
        <a href="logout.php" style="background:#dc3545; color:white; padding:8px 16px; text-decoration:none; border-radius:5px;">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Register New User</h2>
    <?php if (!empty($response)): ?><div class="message"><?= htmlspecialchars($response) ?></div><?php endif; ?>
    <form id="register-form" method="POST" autocomplete="off">
        <input type="hidden" name="register" value="1">
        <label>User</label>
        <input type="text" name="branchId" autocomplete="off" required>

        <label>Password</label>
        <input type="password" id="plain-password" required>
        <input type="hidden" name="password" id="password">

        <label>Role</label>
        <select name="role" id="role" onchange="toggleFields()">
            <option value="branch">Branch</option>
            <option value="admin">Admin</option>
        </select>

        <div id="branchNameField">
            <label>Branch Name</label>
            <input type="text" name="branchName">
        </div>

        <div id="branchesField" style="display:none">
            <label>Assigned Branches</label>
            <input type="text" name="branches" placeholder="e.g. AGPL001,AGPL002">
        </div>
        <br><br>
        <center>
        <input type="submit" class="register-btn" value="Register">
        </center>
    </form>

    <h2>Existing Admins</h2>
    <table>
        <tr><th>Admin ID</th><th>Assigned Branches</th><th>Action</th></tr>
        <?php foreach ($clients as $id => $data): if ($data['role']==='admin' && $id!=='admin' && $id!=='AtticaMaster'): ?>
        <tr>
            <td><?=htmlspecialchars($id)?></td>
            <td><input type="text" name="assignedBranches[<?=htmlspecialchars($id)?>]" form="form-admin-<?=htmlspecialchars($id)?>" value="<?=htmlspecialchars($data['branches']??'')?>"></td>
            <td>
                <form id="form-admin-<?=htmlspecialchars($id)?>" method="POST" style="display:inline">
                    <input type="hidden" name="adminId" value="<?=htmlspecialchars($id)?>">
                    <button type="submit" name="update_admin" class="update-btn">Update</button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete admin <?=htmlspecialchars($id)?>?');">
                    <input type="hidden" name="adminId" value="<?=htmlspecialchars($id)?>">
                    <button type="submit" name="delete_admin" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endif; endforeach; ?>
    </table>

    <h2>Existing Branches</h2>
    <table>
        <tr><th>Branch ID</th><th>Branch Name</th><th>Action</th></tr>
        <?php foreach ($clients as $id => $data): if ($data['role']==='branch'): ?>
        <tr>
            <td><?=htmlspecialchars($id)?></td>
            <td><input type="text" name="branchNameEdit[<?=htmlspecialchars($id)?>]" form="form-branch-<?=htmlspecialchars($id)?>" value="<?=htmlspecialchars($data['name']??'')?>"></td>
            <td>
                <form id="form-branch-<?=htmlspecialchars($id)?>" method="POST" style="display:inline">
                    <input type="hidden" name="branchId" value="<?=htmlspecialchars($id)?>">
                    <button type="submit" name="update_branch" class="update-btn">Update</button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete branch <?=htmlspecialchars($id)?>?');">
                    <input type="hidden" name="deleteId" value="<?=htmlspecialchars($id)?>">
                    <button type="submit" name="delete_branch" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endif; endforeach; ?>
    </table>
</div>
</body>
</html>

