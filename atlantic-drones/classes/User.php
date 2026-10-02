<?php
/**
 * classes/User.php
 * Handles registration, login, password reset and session identity for shoppers.
 */
class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function register(string $fullName, string $email, string $password, ?string $phone = null): int
    {
        if ($this->findByEmail($email)) {
            throw new InvalidArgumentException('An account with that email already exists.');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone) VALUES (:n, :e, :p, :ph)'
        );
        $stmt->execute(['n' => $fullName, 'e' => $email, 'p' => $hash, 'ph' => $phone]);
        return (int) $this->db->lastInsertId();
    }

    /** Verify credentials; returns the user row on success, null on failure. */
    public function attemptLogin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public function createResetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = :t, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = :id'
        );
        $stmt->execute(['t' => $token, 'id' => $user['id']]);
        return $token;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE reset_token = :t AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute(['t' => $token]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $this->db->prepare(
            'UPDATE users SET password_hash = :h, reset_token = NULL, reset_expires = NULL WHERE id = :id'
        );
        $update->execute(['h' => $hash, 'id' => $row['id']]);
        return true;
    }

    /** Regenerate the session ID to prevent fixation, then store identity. */
    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
