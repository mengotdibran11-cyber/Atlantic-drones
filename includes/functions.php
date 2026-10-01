<?php
/**
 * includes/functions.php
 * Shared helper functions: sanitization, JSON responses, CSRF, validation.
 */

/** Send a JSON response and stop execution. */
function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $statusCode = 400, array $extra = []): void
{
    json_response(array_merge(['success' => false, 'message' => $message], $extra), $statusCode);
}

function json_success(string $message = '', array $data = []): void
{
    json_response(array_merge(['success' => true, 'message' => $message], $data));
}

/** Trim + strip tags on every string in an array (XSS / input hygiene). */
function sanitize_input(array $data): array
{
    $clean = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $clean[$key] = sanitize_input($value);
        } else {
            $clean[$key] = trim(strip_tags((string) $value));
        }
    }
    return $clean;
}

/** Escape output for safe HTML rendering (defense-in-depth alongside sanitize_input). */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Generate & stash a CSRF token in the session. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Validate a CSRF token sent by the client (header X-CSRF-Token or POST field). */
function csrf_verify(): bool
{
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || empty($sent)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $sent);
}

function require_csrf(): void
{
    if (!csrf_verify()) {
        json_error('Invalid or missing security token. Please refresh and try again.', 419);
    }
}

/** Read a JSON request body into an assoc array. */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function is_valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/** Basic per-identifier rate limiting backed by the login_attempts table. */
function too_many_attempts(PDO $db, string $identifier, string $ip): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE identifier = :id AND ip_address = :ip AND success = 0
           AND attempted_at > (NOW() - INTERVAL ' . (int) LOGIN_LOCKOUT_SECONDS . ' SECOND)'
    );
    $stmt->execute(['id' => $identifier, 'ip' => $ip]);
    return (int) $stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function record_attempt(PDO $db, string $identifier, string $ip, bool $success): void
{
    $stmt = $db->prepare('INSERT INTO login_attempts (identifier, ip_address, success) VALUES (:id, :ip, :s)');
    $stmt->execute(['id' => $identifier, 'ip' => $ip, 's' => $success ? 1 : 0]);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Very small mail wrapper -- swap in PHPMailer/SMTP for real delivery. */
function send_mail_notification(string $to, string $subject, string $body): bool
{
    $headers = "From: " . SITE_NAME . " <no-reply@atlanticdrones.ca>\r\nContent-Type: text/plain; charset=UTF-8";
    // mail() requires a configured MTA; in local dev this call is best-effort.
    return @mail($to, $subject, $body, $headers);
}
