<?php
/**
 * admin/debug_check.php
 * TEMPORARY, READ-ONLY diagnostic for "incorrect password" troubleshooting.
 * Gated by the same ADMIN_SETUP_KEY as setup.php. Shows whether an admin
 * row exists for a given email, and whether its hash looks like a real
 * bcrypt hash -- without ever displaying the hash itself or accepting a
 * password to test against.
 *
 * DELETE THIS FILE once you've diagnosed the problem. It is not meant to
 * live on a deployed site, even with the key gate.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Session expired, please reload and try again.';
    } elseif (!hash_equals(ADMIN_SETUP_KEY, $_POST['setup_key'] ?? '')) {
        $error = 'Incorrect setup key.';
    } else {
        $email = trim($_POST['email'] ?? '');
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, full_name, email, password_hash, created_at FROM admins WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch();

            if (!$row) {
                $result = ['found' => false];
            } else {
                $hash = $row['password_hash'];
                $looksValid = (bool) preg_match('/^\$2[axy]\$\d{2}\$/', $hash);
                $result = [
                    'found'        => true,
                    'id'           => $row['id'],
                    'full_name'    => $row['full_name'],
                    'email'        => $row['email'],
                    'created_at'   => $row['created_at'],
                    'hash_prefix'  => substr($hash, 0, 7) . '…',
                    'hash_length'  => strlen($hash),
                    'looks_valid'  => $looksValid,
                ];
            }
        } catch (Throwable $e) {
            $error = 'Database error: ' . $e->getMessage();
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
<title>Admin Debug Check — Atlantic Drones</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body">
  <form class="login-card" method="post" style="width:420px;">
    <div class="admin-logo"><span class="mark"></span>ATLANTIC DRONES</div>
    <h1>Admin Account Diagnostic</h1>
    <p style="color:var(--chrome); font-size:12.5px; line-height:1.6; margin:-4px 0 4px;">
      Read-only. Checks whether an admin row exists for an email and whether its
      password hash looks well-formed -- it never reveals the hash or tests a password.
    </p>

    <?php if ($error): ?><div class="alert-error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($result !== null): ?>
      <div class="<?= $result['found'] ? 'alert-ok' : 'alert-error' ?>" style="line-height:1.7;">
        <?php if (!$result['found']): ?>
          No admin row exists for that email. Run <code>admin/setup.php</code> again with
          this exact email address.
        <?php else: ?>
          <strong>Found admin #<?= (int) $result['id'] ?></strong><br>
          Name: <?= e($result['full_name']) ?><br>
          Email: <?= e($result['email']) ?><br>
          Created: <?= e($result['created_at']) ?><br>
          Hash starts with: <code><?= e($result['hash_prefix']) ?></code>
          (length <?= (int) $result['hash_length'] ?>)<br>
          Looks like a valid bcrypt hash:
          <strong><?= $result['looks_valid'] ? 'YES' : 'NO — this is the placeholder seed hash or corrupted. Re-run admin/setup.php.' ?></strong>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <label>Setup Key
      <input type="password" name="setup_key" required autofocus>
    </label>
    <label>Admin Email to Check
      <input type="email" name="email" required value="admin@atlanticdrones.ca">
    </label>
    <button type="submit">Check Account</button>
  </form>
</body>
</html>
