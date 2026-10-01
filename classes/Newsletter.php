<?php
/**
 * classes/Newsletter.php
 */
class Newsletter
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function subscribe(string $email): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO newsletter (email) VALUES (:email)'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->rowCount() > 0;
    }
}
