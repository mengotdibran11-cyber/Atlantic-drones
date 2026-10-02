<?php
/**
 * admin/products.php — list + create/edit/delete products, upload/delete images.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Category.php';

$db = Database::getConnection();
$productModel = new Product($db);
$categoryModel = new Category($db);
$notice = '';

// ---- Handle create/update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'product') {
    if (!csrf_verify()) {
        $notice = 'Session expired, please retry.';
    } else {
        $data = sanitize_input($_POST);
        $data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name'] ?? ''), '-'));

        // Optional primary image upload
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            if ($file['error'] === UPLOAD_ERR_OK
                && in_array(mime_content_type($file['tmp_name']), ALLOWED_IMAGE_TYPES, true)
                && $file['size'] <= MAX_UPLOAD_BYTES) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'prod_' . bin2hex(random_bytes(6)) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $filename);
                $data['primary_image'] = UPLOAD_URL . '/' . $filename;
            } else {
                $notice = 'Image upload rejected (must be JPG/PNG/WebP under 5MB).';
            }
        }

        if (!empty($_POST['product_id'])) {
            $productModel->update((int) $_POST['product_id'], $data);
            $notice = $notice ?: 'Product updated.';
        } else {
            $data['stock_qty'] = $data['stock_qty'] ?? 0;
            $productModel->create($data);
            $notice = $notice ?: 'Product created.';
        }
    }
}

// ---- Handle delete ----
if (!empty($_GET['delete']) && csrf_verify_get()) {
    $productModel->delete((int) $_GET['delete']);
    $notice = 'Product removed.';
}

// simple GET-based CSRF check helper (admin UI convenience)
function csrf_verify_get(): bool
{
    return !empty($_GET['t']) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_GET['t']);
}

$products = $productModel->all([], 1, 100);
$categories = $categoryModel->all();
$csrf = csrf_token();

$pageTitle = 'Products';
$active = 'products';
require __DIR__ . '/includes/header.php';
?>
<h1>Products</h1>
<?php if ($notice): ?><div class="alert-ok"><?= e($notice) ?></div><?php endif; ?>

<details class="admin-panel">
  <summary>+ Add New Product</summary>
  <form method="post" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="form" value="product">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <label>Name <input type="text" name="name" required></label>
    <label>SKU <input type="text" name="sku" required></label>
    <label>Category
      <select name="category_id">
        <option value="">— None —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['type']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Tag <input type="text" name="tag"></label>
    <label>Price <input type="number" step="0.01" name="price" required></label>
    <label>Stock Qty <input type="number" name="stock_qty" value="0"></label>
    <label>Short Description <input type="text" name="short_desc"></label>
    <label>Full Description <textarea name="description"></textarea></label>
    <label>Primary Image <input type="file" name="image" accept="image/*"></label>
    <label><input type="checkbox" name="is_featured" value="1"> Featured</label>
    <button type="submit">Save Product</button>
  </form>
</details>

<table class="admin-table">
  <thead><tr><th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($products['items'] as $p): ?>
      <tr>
        <td><?php if ($p['primary_image']): ?><img src="../<?= e(ltrim($p['primary_image'], '/')) ?>" class="thumb"><?php endif; ?></td>
        <td><?= e($p['name']) ?></td>
        <td class="mono"><?= e($p['sku']) ?></td>
        <td>$<?= number_format((float) $p['price'], 2) ?></td>
        <td><?= (int) $p['stock_qty'] ?></td>
        <td><a href="?delete=<?= (int) $p['id'] ?>&t=<?= e($csrf) ?>" onclick="return confirm('Remove this product?')">Delete</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
