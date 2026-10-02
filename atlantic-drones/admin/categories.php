<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../classes/Category.php';

$db = Database::getConnection();
$categoryModel = new Category($db);
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $data = sanitize_input($_POST);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']), '-'));
    $categoryModel->create($data['name'], $slug, $data['type'], $data['description'] ?? null);
    $notice = 'Category added.';
}
if (!empty($_GET['delete']) && !empty($_GET['t']) && hash_equals($_SESSION['csrf_token'] ?? '', $_GET['t'])) {
    $categoryModel->delete((int) $_GET['delete']);
    $notice = 'Category removed.';
}

$categories = $categoryModel->all();
$csrf = csrf_token();
$pageTitle = 'Categories';
$active = 'categories';
require __DIR__ . '/includes/header.php';
?>
<h1>Categories</h1>
<?php if ($notice): ?><div class="alert-ok"><?= e($notice) ?></div><?php endif; ?>
<form method="post" class="admin-form">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <label>Name <input type="text" name="name" required></label>
  <label>Type
    <select name="type">
      <option value="drone">Drone</option>
      <option value="accessory">Accessory</option>
      <option value="service">Service</option>
    </select>
  </label>
  <label>Description <input type="text" name="description"></label>
  <button type="submit">Add Category</button>
</form>
<table class="admin-table">
  <thead><tr><th>Name</th><th>Type</th><th>Description</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['type']) ?></td>
        <td><?= e($c['description'] ?? '') ?></td>
        <td><a href="?delete=<?= (int) $c['id'] ?>&t=<?= e($csrf) ?>" onclick="return confirm('Remove this category?')">Delete</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
