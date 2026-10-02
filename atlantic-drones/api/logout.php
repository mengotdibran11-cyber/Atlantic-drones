<?php
/**
 * POST /api/logout.php
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

$userModel = new User(Database::getConnection());
$userModel->logout();
json_success('Logged out.');
