<?php
/**
 * GET /api/product.php?id=  or  ?slug=
 * Single product detail, including images, rating, and related products.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed.', 405);
}

$db = Database::getConnection();
$product = new Product($db);

$item = null;
if (!empty($_GET['id'])) {
    $item = $product->find((int) $_GET['id']);
} elseif (!empty($_GET['slug'])) {
    $item = $product->findBySlug(preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'])));
}

if (!$item) {
    json_error('Product not found.', 404);
}

$item['related'] = $product->related((int) $item['id'], (int) ($item['category_id'] ?? 0));
json_success('', ['product' => $item]);
