<?php
/**
 * POST /api/register.php
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}
require_csrf();

$data = sanitize_input(read_json_body() ?: $_POST);
$name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$phone = $data['phone'] ?? null;

$errors = [];
if (mb_strlen($name) < 2) $errors['full_name'] = 'Please enter your full name.';
if (!is_valid_email($email)) $errors['email'] = 'Please enter a valid email address.';
if (mb_strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';

if ($errors) {
    json_error('Please correct the highlighted fields.', 422, ['errors' => $errors]);
}

$db = Database::getConnection();
$userModel = new User($db);

try {
    $id = $userModel->register($name, $email, $password, $phone);
} catch (InvalidArgumentException $e) {
    json_error($e->getMessage(), 409);
}

$user = $userModel->findByEmail($email);
$userModel->login($user);

json_success('Account created.', ['user' => ['id' => $id, 'full_name' => $name, 'email' => $email]]);
