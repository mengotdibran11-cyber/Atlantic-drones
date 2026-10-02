<?php
/**
 * POST /api/contact.php
 * AJAX contact form -> validates, stores in MySQL, emails admin, returns JSON.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Enquiry.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

require_csrf();

$data = sanitize_input(read_json_body() ?: $_POST);

$name    = $data['name'] ?? '';
$email   = $data['email'] ?? '';
$interest = $data['interest'] ?? null;
$message = $data['message'] ?? '';

$errors = [];
if (mb_strlen($name) < 2)     $errors['name'] = 'Please enter your full name.';
if (!is_valid_email($email))  $errors['email'] = 'Please enter a valid email address.';
if (mb_strlen($message) < 10) $errors['message'] = 'Message must be at least 10 characters.';

if ($errors) {
    json_error('Please correct the highlighted fields.', 422, ['errors' => $errors]);
}

$db = Database::getConnection();
$enquiry = new Enquiry($db);
$id = $enquiry->create($name, $email, $interest, $message, client_ip());

send_mail_notification(
    ADMIN_EMAIL,
    'New enquiry from ' . $name,
    "Name: $name\nEmail: $email\nInterest: $interest\n\n$message"
);

json_success('Thanks — a specialist will get back to you within one business day.', ['id' => $id]);
