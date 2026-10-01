<?php
/**
 * /api/reviews.php
 * GET  ?product_id=  -> list approved reviews
 * POST { product_id, reviewer_name, rating, comment } -> create review
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Review.php';

$db = Database::getConnection();
$review = new Review($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productId = (int) ($_GET['product_id'] ?? 0);
    json_success('', ['reviews' => $review->forProduct($productId)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $data = sanitize_input(read_json_body() ?: $_POST);
    $productId = (int) ($data['product_id'] ?? 0);
    $name = $data['reviewer_name'] ?? '';
    $rating = (int) ($data['rating'] ?? 0);
    $comment = $data['comment'] ?? null;

    if (!$productId || mb_strlen($name) < 2 || $rating < 1 || $rating > 5) {
        json_error('Please provide a name and a rating from 1-5.', 422);
    }

    $userId = $_SESSION['user_id'] ?? null;
    $review->create($productId, $userId, $name, $rating, $comment);
    json_success('Thanks for your review!');
}

json_error('Method not allowed.', 405);
