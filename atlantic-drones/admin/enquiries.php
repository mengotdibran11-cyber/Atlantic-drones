<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../classes/Enquiry.php';

$db = Database::getConnection();
$enquiryModel = new Enquiry($db);

if (!empty($_GET['status']) && !empty($_GET['id']) && !empty($_GET['t']) && hash_equals($_SESSION['csrf_token'] ?? '', $_GET['t'])) {
    $enquiryModel->updateStatus((int) $_GET['id'], $_GET['status']);
}

$enquiries = $enquiryModel->all(1, 50);
$csrf = csrf_token();
$pageTitle = 'Enquiries';
$active = 'enquiries';
require __DIR__ . '/includes/header.php';
?>
<h1>Customer Enquiries</h1>
<table class="admin-table">
  <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Interest</th><th>Message</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($enquiries as $en): ?>
      <tr>
        <td class="mono"><?= e(date('M j, Y', strtotime($en['created_at']))) ?></td>
        <td><?= e($en['full_name']) ?></td>
        <td><?= e($en['email']) ?></td>
        <td><?= e($en['interest'] ?? '') ?></td>
        <td><?= e(mb_strimwidth($en['message'], 0, 80, '…')) ?></td>
        <td><span class="badge badge-<?= e($en['status']) ?>"><?= e($en['status']) ?></span></td>
        <td>
          <a href="?id=<?= (int) $en['id'] ?>&status=read&t=<?= e($csrf) ?>">Mark Read</a> ·
          <a href="?id=<?= (int) $en['id'] ?>&status=responded&t=<?= e($csrf) ?>">Responded</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$enquiries): ?><tr><td colspan="7">No enquiries yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
