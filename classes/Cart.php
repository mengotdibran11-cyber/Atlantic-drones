<?php
/**
 * classes/Cart.php
 * Server-side cart persistence for logged-in users.
 * Guests use client-side LocalStorage (see assets/js/cart.js); on login the
 * client merges its LocalStorage cart into the server via POST /api/cart.php.
 */
class Cart
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function items(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.primary_image, p.slug
             FROM cart c JOIN products p ON p.id = c.product_id
             WHERE c.user_id = :uid ORDER BY c.created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $productId, int $qty = 1): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cart (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
        );
        $stmt->execute(['uid' => $userId, 'pid' => $productId, 'qty' => max(1, $qty)]);
    }

    public function updateQuantity(int $userId, int $productId, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($userId, $productId);
            return;
        }
        $stmt = $this->db->prepare(
            'UPDATE cart SET quantity = :qty WHERE user_id = :uid AND product_id = :pid'
        );
        $stmt->execute(['qty' => $qty, 'uid' => $userId, 'pid' => $productId]);
    }

    public function remove(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :uid AND product_id = :pid');
        $stmt->execute(['uid' => $userId, 'pid' => $productId]);
    }

    public function clear(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
    }
}
