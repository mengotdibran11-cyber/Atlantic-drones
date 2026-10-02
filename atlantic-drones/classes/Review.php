<?php
/**
 * classes/Review.php
 */
class Review
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function forProduct(int $productId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM reviews WHERE product_id = :pid AND is_approved = 1 ORDER BY created_at DESC'
        );
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll();
    }

    public function create(int $productId, ?int $userId, string $name, int $rating, ?string $comment): int
    {
        $rating = max(1, min(5, $rating));
        $stmt = $this->db->prepare(
            'INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment) VALUES (:p, :u, :n, :r, :c)'
        );
        $stmt->execute(['p' => $productId, 'u' => $userId, 'n' => $name, 'r' => $rating, 'c' => $comment]);
        return (int) $this->db->lastInsertId();
    }
}
