<?php
/**
 * classes/ServiceProgram.php
 * Named ServiceProgram to avoid clashing with the reserved-ish "Service" name.
 */
class ServiceProgram
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order');
        return $stmt->fetchAll();
    }

    public function create(string $code, string $title, ?string $desc): int
    {
        $stmt = $this->db->prepare('INSERT INTO services (code, title, description) VALUES (:c, :t, :d)');
        $stmt->execute(['c' => $code, 't' => $title, 'd' => $desc]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE services SET is_active = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
