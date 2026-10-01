<?php
/**
 * admin/login.php
 * Secure admin login form + handler (rate-limited, session-regenerated).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Admin.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $db = Database::getConnection();
        $ip = client_ip();

        if (too_many_attempts($db, $email, $ip)) {
            $error = 'Too many attempts. Please try again in 15 minutes.';
        } else {
            $adminModel = new Admin($db);
            $admin = $adminModel->attemptLogin($email, $password);
            record_attempt($db, $email, $ip, (bool) $admin);

            if ($admin) {
                $adminModel->login($admin);
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Incorrect email or password.';
        }
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Atlantic Drones</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body">
  <form class="login-card" method="post">
    <div class="admin-logo"><span class="mark"></span>ATLANTIC DRONES</div>
    <h1>Admin Sign In</h1>
    <?php if ($error): ?><div class="alert-error"><?= e($error) ?></div><?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <label>Email
      <input type="email" name="email" required autofocus>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Sign In</button>
  </form>
</body>
</html>
