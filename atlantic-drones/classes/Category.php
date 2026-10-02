<?php
/**
 * classes/Category.php
 */
class Category
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(?string $type = null): array
    {
        if ($type) {
            $stmt = $this->db->prepare('SELECT * FROM categories WHERE type = :type ORDER BY name');
            $stmt->execute(['type' => $type]);
        } else {
            $stmt = $this->db->query('SELECT * FROM categories ORDER BY type, name');
        }
        return $stmt->fetchAll();
    }

    public function create(string $name, string $slug, string $type, ?string $desc): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, slug, type, description) VALUES (:n, :s, :t, :d)'
        );
        $stmt->execute(['n' => $name, 's' => $slug, 't' => $type, 'd' => $desc]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
