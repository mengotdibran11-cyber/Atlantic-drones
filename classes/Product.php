<?php
/**
 * classes/Product.php
 * CRUD + query helpers for drones/accessories (the `products` table).
 */
class Product
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch a paginated, filterable, sortable product list.
     * $filters keys: category_id, min_price, max_price, search, sort, type
     */
    public function all(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where  = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'c.type = :type';
            $params['type'] = $filters['type'];
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = 'p.price >= :min_price';
            $params['min_price'] = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = 'p.price <= :max_price';
            $params['max_price'] = (float) $filters['max_price'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :search OR p.short_desc LIKE :search OR p.tag LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sortMap = [
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
            'name'       => 'p.name ASC',
        ];
        $orderBy = $sortMap[$filters['sort'] ?? ''] ?? 'p.is_featured DESC, p.created_at DESC';

        $whereSql = implode(' AND ', $where);
        $offset   = max(0, ($page - 1) * $perPage);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $whereSql"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.type AS category_type
                FROM products p LEFT JOIN categories c ON c.id = p.category_id
                WHERE $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page'  => $page,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.type AS category_type
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.is_active = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        if (!$product) return null;

        $product['images'] = $this->images($id);
        $product['avg_rating'] = $this->averageRating($id);
        return $product;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? $this->find((int) $row['id']) : null;
    }

    public function images(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC');
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }

    public function averageRating(int $productId): float
    {
        $stmt = $this->db->prepare('SELECT AVG(rating) FROM reviews WHERE product_id = :id AND is_approved = 1');
        $stmt->execute(['id' => $productId]);
        return round((float) $stmt->fetchColumn(), 1);
    }

    /** Products sharing the same category, excluding the current one. */
    public function related(int $productId, int $categoryId, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, slug, price, primary_image FROM products
             WHERE category_id = :cat AND id != :id AND is_active = 1
             ORDER BY RAND() LIMIT :lim'
        );
        $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search(string $term, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, slug, price, primary_image, short_desc FROM products
             WHERE is_active = 1 AND (name LIKE :t OR short_desc LIKE :t OR tag LIKE :t)
             ORDER BY is_featured DESC LIMIT :lim'
        );
        $stmt->bindValue(':t', '%' . $term . '%');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ---------------- Admin CRUD ----------------

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (category_id, sku, name, slug, tag, short_desc, description, price, stock_qty, is_featured, primary_image)
             VALUES (:category_id, :sku, :name, :slug, :tag, :short_desc, :description, :price, :stock_qty, :is_featured, :primary_image)'
        );
        $stmt->execute([
            'category_id' => $data['category_id'] ?: null,
            'sku'         => $data['sku'],
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'tag'         => $data['tag'] ?? null,
            'short_desc'  => $data['short_desc'] ?? null,
            'description' => $data['description'] ?? null,
            'price'       => $data['price'],
            'stock_qty'   => $data['stock_qty'] ?? 0,
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'primary_image' => $data['primary_image'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products SET category_id=:category_id, sku=:sku, name=:name, slug=:slug, tag=:tag,
             short_desc=:short_desc, description=:description, price=:price, stock_qty=:stock_qty,
             is_featured=:is_featured, in_stock=:in_stock WHERE id=:id'
        );
        return $stmt->execute([
            'category_id' => $data['category_id'] ?: null,
            'sku'         => $data['sku'],
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'tag'         => $data['tag'] ?? null,
            'short_desc'  => $data['short_desc'] ?? null,
            'description' => $data['description'] ?? null,
            'price'       => $data['price'],
            'stock_qty'   => $data['stock_qty'] ?? 0,
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'in_stock'    => !empty($data['in_stock']) ? 1 : 0,
            'id'          => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET is_active = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function addImage(int $productId, string $path, string $alt = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO product_images (product_id, image_path, alt_text) VALUES (:pid, :path, :alt)'
        );
        $stmt->execute(['pid' => $productId, 'path' => $path, 'alt' => $alt]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteImage(int $imageId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM product_images WHERE id = :id');
        return $stmt->execute(['id' => $imageId]);
    }
}
