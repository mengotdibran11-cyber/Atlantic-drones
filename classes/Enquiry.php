<?php
/**
 * classes/Enquiry.php
 * Contact-form submissions.
 */
class Enquiry
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $name, string $email, ?string $interest, string $message, string $ip): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO enquiries (full_name, email, interest, message, ip_address) VALUES (:n, :e, :i, :m, :ip)'
        );
        $stmt->execute(['n' => $name, 'e' => $email, 'i' => $interest, 'm' => $message, 'ip' => $ip]);
        return (int) $this->db->lastInsertId();
    }

    public function all(int $page = 1, int $perPage = 20, ?string $status = null): array
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where = 'status = :status';
            $params['status'] = $status;
        }
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->db->prepare("SELECT * FROM enquiries WHERE $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
        foreach ($params as $k => $v) $stmt->bindValue(':' . $k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE enquiries SET status = :s WHERE id = :id');
        return $stmt->execute(['s' => $status, 'id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
    }
}
