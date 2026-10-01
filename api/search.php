<?php
/**
 * GET /api/search.php?q=
 * Lightweight typeahead search (debounced client-side).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Product.php';

$term = trim($_GET['q'] ?? '');
if ($term === '' || mb_strlen($term) < 2) {
    json_success('', ['results' => []]);
}

$db = Database::getConnection();
$product = new Product($db);
$results = $product->search($term, 12);
json_success('', ['results' => $results]);
