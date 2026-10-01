<?php
/**
 * config/config.php
 * Global application configuration & environment constants.
 */

// ---- Error reporting (turn E_ALL/display_errors off in production) ----
error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak errors to JSON API responses

// ---- Core site constants ----
define('SITE_NAME', 'Atlantic Drones');
define('SITE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/products');
define('UPLOAD_URL', 'uploads/products'); // relative to project root -- no leading slash, see README on subfolder deployments

// ---- Admin notification email ----
define('ADMIN_EMAIL', 'sales@atlanticdrones.ca');

// ---- One-time admin setup key ----
// Required by admin/setup.php to create/reset the admin password through a
// form instead of hand-editing SQL. CHANGE THIS before deploying, and delete
// or rename admin/setup.php once you've used it (see comments in that file).
define('ADMIN_SETUP_KEY', 'change-me-before-deploying');

// ---- Security ----
define('SESSION_LIFETIME', 60 * 60 * 4);      // 4 hours
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 15 * 60);     // 15 minutes

// ---- File upload rules ----
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);  // 5MB

// ---- Secure session bootstrap ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // enable once served over HTTPS
    ]);
    session_start();
}

// ---- Timezone ----
date_default_timezone_set('America/Halifax');
