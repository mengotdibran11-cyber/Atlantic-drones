<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Admin.php';
(new Admin(Database::getConnection()))->logout();
header('Location: login.php');
exit;
