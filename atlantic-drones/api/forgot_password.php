<?php
/**
 * POST /api/forgot_password.php  { email }
 * POST /api/forgot_password.php  { token, password }  -- to complete reset
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}
require_csrf();

$data = sanitize_input(read_json_body() ?: $_POST);
$db = Database::getConnection();
$userModel = new User($db);

if (!empty($data['token'])) {
    if (mb_strlen($data['password'] ?? '') < 8) {
        json_error('Password must be at least 8 characters.', 422);
    }
    $ok = $userModel->resetPassword($data['token'], $data['password']);
    if (!$ok) json_error('This reset link is invalid or has expired.', 400);
    json_success('Password updated. You can now log in.');
}

if (!is_valid_email($data['email'] ?? '')) {
    json_error('Please enter a valid email address.', 422);
}

$token = $userModel->createResetToken($data['email']);
// Always respond success (don't leak whether the email exists).
if ($token) {
    $resetLink = SITE_URL . '/reset-password.html?token=' . $token;
    send_mail_notification($data['email'], 'Reset your password', "Reset link: $resetLink (valid 1 hour)");
}
json_success('If that email exists in our system, a reset link has been sent.');
