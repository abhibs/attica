<?php
// login.php
session_start();

$clientsFile = __DIR__ . '/clients.json';

// If already logged in, redirect immediately
if (!empty($_SESSION['branchId'])) {
    $branchId = $_SESSION['branchId'];
    $clients = file_exists($clientsFile) ? json_decode(file_get_contents($clientsFile), true) : [];
    $role = $clients[$branchId]['role'] ?? 'branch';
    if ($role === 'admin' || $role === 'superadmin') {
        header("Location: handler.php");
        exit;
    } else {
        header("Location: listener.php");
        exit;
    }
}

$response = "";

// Handle form submission (only login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branchId = trim($_POST['branchId'] ?? '');
    $clientHashedPassword = trim($_POST['password'] ?? '');

    if ($branchId === '' || $clientHashedPassword === '') {
        $response = "Branch ID and password are required.";
    } else {
        $clients = file_exists($clientsFile) ? json_decode(file_get_contents($clientsFile), true) : [];
        if (!is_array($clients)) $clients = [];

        if (isset($clients[$branchId])) {
            $storedHash = $clients[$branchId]['password'] ?? '';
            if (!password_verify($clientHashedPassword, $storedHash)) {
                $response = "Invalid password for branch '{$branchId}'.";
            } else {
                $clients[$branchId]['last_seen'] = time();
                file_put_contents($clientsFile, json_encode($clients, JSON_PRETTY_PRINT));

                $_SESSION['branchId'] = $branchId;
                $role = $clients[$branchId]['role'] ?? 'branch';
                if ($role === 'admin' || $role === 'superadmin') {
                    header("Location: handler.php");
                    exit;
                } else {
                    header("Location: listener.php");
                    exit;
                }
            }
        } else {
            $response = "Branch ID '{$branchId}' not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="images/favicon.png">
    <title>Attica Analytics - Branch Authentication</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f1f1; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; }
        #auth-form { background: white; padding: 2em; box-shadow: 0 0 10px #ccc; border-radius: 8px; width: 320px; box-sizing: border-box; }
        #auth-form h2 { margin-top: 0; text-align: center; font-weight: normal; color: #333; }
        label { display: block; margin-top: 1em; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.6em; margin-top: 0.3em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        #submit-btn { background: #007bff; color: white; border: none; padding: 0.8em; cursor: pointer; width: 100%; border-radius: 4px; margin-top: 1.5em; font-size: 1em; }
        .message { margin-top: 1em; font-weight: bold; color: #d00; text-align: center; }
    </style>
</head>
<body style="background:#760107">
<center><img src="images/group-of-attica-gold-companies.jpg" style="width:80%;padding:40px"></center>

<form id="auth-form" method="POST" action="" style="background:#900;font-weight:bold;border:none">
    <h2 style="color:white;">Login</h2>

    <label for="branchId" style="color:white; text-align:center">Branch ID</label>
    <input type="text" id="branchId" name="branchId" required>

    <label for="password" style="color:white; text-align:center">Password</label>
    <input type="password" id="plain-password" required>
    <input type="hidden" id="password" name="password">

    <input type="submit" style="background:#f8e75c;color:#900;font-weight:bold;border:none" id="submit-btn" value="Login">

    <?php if (!empty($response)): ?>
        <div class="message"><?= htmlspecialchars($response) ?></div>
    <?php endif; ?>
</form>

<script>
    const form = document.getElementById('auth-form');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const pw = document.getElementById('plain-password').value;
        const hashed = await sha256(pw);
        document.getElementById('password').value = hashed;
        this.submit();
    });

    async function sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        return hashHex;
    }
</script>
</body>
</html>
