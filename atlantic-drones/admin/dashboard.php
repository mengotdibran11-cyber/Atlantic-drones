<?php
/**
 * admin/dashboard.php — key stats overview.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../classes/Enquiry.php';

$db = Database::getConnection();
$productCount = (int) $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$categoryCount = (int) $db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$enquiryModel = new Enquiry($db);
$enquiryCount = $enquiryModel->count();
$newEnquiries = (int) $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn();
$newsletterCount = (int) $db->query('SELECT COUNT(*) FROM newsletter')->fetchColumn();
$lowStock = $db->query('SELECT name, stock_qty FROM products WHERE is_active = 1 AND stock_qty <= 5 ORDER BY stock_qty ASC LIMIT 6')->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Dashboard</h1>
<div class="stat-grid">
  <div class="stat-card"><span class="num"><?= $productCount ?></span><span class="label">Active Products</span></div>
  <div class="stat-card"><span class="num"><?= $categoryCount ?></span><span class="label">Categories</span></div>
  <div class="stat-card"><span class="num"><?= $enquiryCount ?></span><span class="label">Total Enquiries</span></div>
  <div class="stat-card"><span class="num"><?= $newEnquiries ?></span><span class="label">New Enquiries</span></div>
  <div class="stat-card"><span class="num"><?= $newsletterCount ?></span><span class="label">Newsletter Subs</span></div>
</div>

<h2>Low Stock Watch</h2>
<table class="admin-table">
  <thead><tr><th>Product</th><th>Stock Left</th></tr></thead>
  <tbody>
    <?php foreach ($lowStock as $row): ?>
      <tr><td><?= e($row['name']) ?></td><td><?= (int) $row['stock_qty'] ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$lowStock): ?><tr><td colspan="2">Nothing low on stock right now.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
