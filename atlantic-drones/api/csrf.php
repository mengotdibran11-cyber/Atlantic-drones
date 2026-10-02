<?php
/**
 * GET /api/csrf.php -- returns a fresh CSRF token for the frontend to attach
 * to subsequent POST requests (fetch header X-CSRF-Token).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
json_success('', ['csrf_token' => csrf_token()]);
