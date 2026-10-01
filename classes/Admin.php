<?php
/**
 * classes/Admin.php
 * Authentication for the admin dashboard (kept separate from shopper Users).
 */
class Admin
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function attemptLogin(string $email, string $password): ?array
    {
        $admin = $this->findByEmail($email);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return null;
        }
        return $admin;
    }

    public function login(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];

        $stmt = $this->db->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $admin['id']]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
