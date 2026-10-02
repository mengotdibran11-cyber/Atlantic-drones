<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../classes/ServiceProgram.php';

$db = Database::getConnection();
$serviceModel = new ServiceProgram($db);
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $data = sanitize_input($_POST);
    $serviceModel->create($data['code'], $data['title'], $data['description'] ?? null);
    $notice = 'Service added.';
}
if (!empty($_GET['delete']) && !empty($_GET['t']) && hash_equals($_SESSION['csrf_token'] ?? '', $_GET['t'])) {
    $serviceModel->delete((int) $_GET['delete']);
    $notice = 'Service removed.';
}

$services = $serviceModel->all();
$csrf = csrf_token();
$pageTitle = 'Services';
$active = 'services';
require __DIR__ . '/includes/header.php';
?>
<h1>Services</h1>
<?php if ($notice): ?><div class="alert-ok"><?= e($notice) ?></div><?php endif; ?>
<form method="post" class="admin-form">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <label>Code <input type="text" name="code" placeholder="SVC.04" required></label>
  <label>Title <input type="text" name="title" required></label>
  <label>Description <input type="text" name="description"></label>
  <button type="submit">Add Service</button>
</form>
<table class="admin-table">
  <thead><tr><th>Code</th><th>Title</th><th>Description</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($services as $s): ?>
      <tr>
        <td class="mono"><?= e($s['code']) ?></td>
        <td><?= e($s['title']) ?></td>
        <td><?= e($s['description'] ?? '') ?></td>
        <td><a href="?delete=<?= (int) $s['id'] ?>&t=<?= e($csrf) ?>" onclick="return confirm('Remove this service?')">Delete</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
