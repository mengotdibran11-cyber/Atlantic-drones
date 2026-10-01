<?php
/**
 * admin/includes/admin_auth.php
 * Guards every admin page. Redirects to login if no admin session exists.
 */
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
