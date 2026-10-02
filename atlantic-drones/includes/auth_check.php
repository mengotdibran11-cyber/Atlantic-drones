<?php
/**
 * includes/auth_check.php
 * Guards user-only API endpoints. Include after bootstrap.php.
 */
if (empty($_SESSION['user_id'])) {
    json_error('You must be logged in.', 401);
}
