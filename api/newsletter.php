<?php
/**
 * POST /api/newsletter.php { email }
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Newsletter.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed.', 405);
require_csrf();

$data = sanitize_input(read_json_body() ?: $_POST);
if (!is_valid_email($data['email'] ?? '')) {
    json_error('Please enter a valid email address.', 422);
}

$newsletter = new Newsletter(Database::getConnection());
$added = $newsletter->subscribe($data['email']);
json_success($added ? 'You\'re subscribed — welcome aboard.' : 'You\'re already on the list.');
