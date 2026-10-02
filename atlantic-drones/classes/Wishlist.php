<?php
/**
 * classes/Wishlist.php
 * Server-side wishlist persistence for logged-in users (mirrors LocalStorage
 * wishlist used for guests -- see assets/js/wishlist.js).
 */
class Wishlist
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function items(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT w.id, p.id AS product_id, p.name, p.price, p.primary_image, p.slug
             FROM wishlist w JOIN products p ON p.id = w.product_id
             WHERE w.user_id = :uid ORDER BY w.created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function toggle(int $userId, int $productId): string
    {
        $check = $this->db->prepare('SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid');
        $check->execute(['uid' => $userId, 'pid' => $productId]);
        if ($row = $check->fetch()) {
            $del = $this->db->prepare('DELETE FROM wishlist WHERE id = :id');
            $del->execute(['id' => $row['id']]);
            return 'removed';
        }
        $ins = $this->db->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (:uid, :pid)');
        $ins->execute(['uid' => $userId, 'pid' => $productId]);
        return 'added';
    }
}
