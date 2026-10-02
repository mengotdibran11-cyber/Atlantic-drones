<?php
/**
 * POST /api/login.php
 * Rate-limited shopper login.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}
require_csrf();

$data = sanitize_input(read_json_body() ?: $_POST);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!is_valid_email($email) || $password === '') {
    json_error('Please enter a valid email and password.', 422);
}

$db = Database::getConnection();
$ip = client_ip();

if (too_many_attempts($db, $email, $ip)) {
    json_error('Too many login attempts. Please try again in 15 minutes.', 429);
}

$userModel = new User($db);
$user = $userModel->attemptLogin($email, $password);

record_attempt($db, $email, $ip, (bool) $user);

if (!$user) {
    json_error('Incorrect email or password.', 401);
}

$userModel->login($user);
json_success('Welcome back, ' . $user['full_name'] . '.', [
    'user' => ['id' => $user['id'], 'full_name' => $user['full_name'], 'email' => $user['email']],
]);
