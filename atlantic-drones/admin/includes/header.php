<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>Admin — Atlantic Drones</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-logo"><span class="mark"></span>ATLANTIC DRONES <span class="mono">/admin</span></div>
    <nav class="admin-nav">
      <a href="dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="products.php" class="<?= ($active ?? '') === 'products' ? 'active' : '' ?>">Products</a>
      <a href="categories.php" class="<?= ($active ?? '') === 'categories' ? 'active' : '' ?>">Categories</a>
      <a href="services.php" class="<?= ($active ?? '') === 'services' ? 'active' : '' ?>">Services</a>
      <a href="enquiries.php" class="<?= ($active ?? '') === 'enquiries' ? 'active' : '' ?>">Enquiries</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
    <header class="admin-topbar">
      <span>Signed in as <b><?= e($_SESSION['admin_name'] ?? '') ?></b></span>
    </header>
    <div class="admin-content">
