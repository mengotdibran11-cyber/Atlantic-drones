<?php
/**
 * /api/cart.php
 * GET    -> list cart items for the logged-in user
 * POST   -> { action: 'add'|'update'|'remove'|'clear'|'merge', product_id, quantity, items[] }
 * Guests keep their cart in LocalStorage (assets/js/cart.js); this endpoint
 * only persists carts for authenticated users, and can merge a LocalStorage
 * cart in on login via action=merge.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Cart.php';

$db = Database::getConnection();
$cart = new Cart($db);
$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_success('', ['items' => $cart->items($userId)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $data = sanitize_input(read_json_body() ?: $_POST);
    $action = $data['action'] ?? 'add';

    switch ($action) {
        case 'add':
            $cart->add($userId, (int) $data['product_id'], (int) ($data['quantity'] ?? 1));
            break;
        case 'update':
            $cart->updateQuantity($userId, (int) $data['product_id'], (int) $data['quantity']);
            break;
        case 'remove':
            $cart->remove($userId, (int) $data['product_id']);
            break;
        case 'clear':
            $cart->clear($userId);
            break;
        case 'merge':
            foreach ((array) ($data['items'] ?? []) as $line) {
                if (!empty($line['product_id'])) {
                    $cart->add($userId, (int) $line['product_id'], (int) ($line['quantity'] ?? 1));
                }
            }
            break;
        default:
            json_error('Unknown cart action.', 400);
    }

    json_success('Cart updated.', ['items' => $cart->items($userId)]);
}

json_error('Method not allowed.', 405);
