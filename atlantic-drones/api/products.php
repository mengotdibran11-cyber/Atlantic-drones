<?php
/**
 * GET /api/products.php
 * Paginated, filterable product listing.
 * Query params: category_id, type, min_price, max_price, search, sort, page, per_page
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed.', 405);
}

$db = Database::getConnection();
$product = new Product($db);

$filters = sanitize_input([
    'category_id' => $_GET['category_id'] ?? '',
    'type'        => $_GET['type'] ?? '',
    'min_price'   => $_GET['min_price'] ?? '',
    'max_price'   => $_GET['max_price'] ?? '',
    'search'      => $_GET['search'] ?? '',
    'sort'        => $_GET['sort'] ?? '',
]);

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = min(48, max(1, (int) ($_GET['per_page'] ?? 12)));

$result = $product->all($filters, $page, $perPage);
json_success('', $result);
