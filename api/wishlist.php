<?php
/**
 * /api/wishlist.php
 * GET  -> list wishlist for logged-in user
 * POST -> { product_id }  toggles add/remove
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Wishlist.php';

$db = Database::getConnection();
$wishlist = new Wishlist($db);
$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_success('', ['items' => $wishlist->items($userId)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $data = sanitize_input(read_json_body() ?: $_POST);
    $productId = (int) ($data['product_id'] ?? 0);
    if (!$productId) json_error('Missing product_id.', 422);

    $status = $wishlist->toggle($userId, $productId);
    json_success('Wishlist updated.', ['status' => $status, 'items' => $wishlist->items($userId)]);
}

json_error('Method not allowed.', 405);
