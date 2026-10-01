<?php
/**
 * admin/setup.php
 * ONE-TIME admin account setup — lets you create the first admin account or
 * reset an admin's password through a form instead of hand-editing SQL /
 * generating password_hash() output yourself.
 *
 * Protected by ADMIN_SETUP_KEY (config/config.php) so a stranger can't hit
 * this URL and reset your admin password. Still: DELETE OR RENAME THIS FILE
 * once you're done using it -- it's a standing risk to leave on a live site,
 * even with the key in place.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Admin.php';

$notice = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please reload this page and try again.';
    } else {
        $data = sanitize_input($_POST);
        $setupKey = $data['setup_key'] ?? '';
        $email    = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $confirm  = $data['confirm_password'] ?? '';

        if (!hash_equals(ADMIN_SETUP_KEY, $setupKey)) {
            $error = 'Incorrect setup key.';
        } elseif (!is_valid_email($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (mb_strlen($password) < 10) {
            $error = 'Password must be at least 10 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $db = Database::getConnection();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $existing = $db->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
            $existing->execute(['email' => $email]);
            $row = $existing->fetch();

            if ($row) {
                $update = $db->prepare('UPDATE admins SET password_hash = :h WHERE id = :id');
                $update->execute(['h' => $hash, 'id' => $row['id']]);
                $notice = 'Password updated for ' . $email . '.';
            } else {
                $insert = $db->prepare(
                    'INSERT INTO admins (full_name, email, password_hash, role) VALUES (:n, :e, :h, :r)'
                );
                $insert->execute([
                    'n' => $data['full_name'] ?: 'Site Administrator',
                    'e' => $email,
                    'h' => $hash,
                    'r' => 'super_admin',
                ]);
                $notice = 'Admin account created for ' . $email . '.';
            }
            $success = true;
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
<title>Admin Setup — Atlantic Drones</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body">
  <form class="login-card" method="post" style="width:380px;">
    <div class="admin-logo"><span class="mark"></span>ATLANTIC DRONES</div>
    <h1>Admin Account Setup</h1>

    <?php if ($error): ?><div class="alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="alert-ok"><?= e($notice) ?> You can now <a href="login.php" style="color:#1E7A3D;text-decoration:underline;">sign in</a>.</div>
    <?php endif; ?>

    <p style="color:var(--chrome); font-size:12.5px; line-height:1.6; margin:-4px 0 4px;">
      Enter the setup key from <code>config/config.php</code> (<code>ADMIN_SETUP_KEY</code>) along with
      the email/password you want. If that email already has an admin account, this resets its password;
      otherwise it creates a new one.
    </p>

    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <label>Setup Key
      <input type="password" name="setup_key" required autofocus>
    </label>
    <label>Full Name <span style="opacity:.6;">(only used if creating a new account)</span>
      <input type="text" name="full_name" placeholder="Site Administrator">
    </label>
    <label>Admin Email
      <input type="email" name="email" required value="admin@atlanticdrones.ca">
    </label>
    <label>New Password <span style="opacity:.6;">(min 10 characters)</span>
      <input type="password" name="password" required minlength="10">
    </label>
    <label>Confirm Password
      <input type="password" name="confirm_password" required minlength="10">
    </label>
    <button type="submit">Save Admin Account</button>
  </form>
</body>
</html>
