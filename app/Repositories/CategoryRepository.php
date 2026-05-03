<?php
// ============================================================
// REPOSITORY — app/Repositories/CategoryRepository.php
// ============================================================
// TV2 viết — Ngày 2
// ============================================================

namespace App\Repositories;

class CategoryRepository extends BaseRepository
{
    protected function getTable(): string { return 'categories'; }

    // ── READ ──────────────────────────────────────────────────

    /**
     * Lấy tất cả danh mục của user.
     * Dùng bởi: dropdown trong form chi tiêu/thu nhập (TV3/TV4 gọi).
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM categories
             WHERE  user_id = ?
             ORDER BY name ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh mục theo type của user.
     * type: 'income' | 'expense' | 'both'
     * Dùng: lọc dropdown cho form chi tiêu chỉ hiện expense/both
     */
    public function findByUserAndType(int $userId, string $type): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM categories
             WHERE  user_id = ?
               AND  (type = ? OR type = 'both')
             ORDER BY name ASC"
        );
        $stmt->execute([$userId, $type]);
        return $stmt->fetchAll();
    }

    /**
     * Tìm theo tên (check trùng khi tạo mới).
     */
    public function findByNameAndUser(string $name, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM categories
             WHERE name = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$name, $userId]);
        return $stmt->fetch() ?: null;
    }

    // ── WRITE ─────────────────────────────────────────────────

    /**
     * Tạo danh mục mới. Trả về ID vừa insert.
     * 
     * @throws \InvalidArgumentException nếu user_id <= 0
     */
    public function save(array $data): int
    {
        // ❌ Validation: user_id phải > 0 (tránh xung đột FK)
        if (empty($data['user_id']) || (int)$data['user_id'] <= 0) {
            throw new \InvalidArgumentException('User ID không hợp lệ. Bạn phải đăng nhập.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO categories (user_id, name, type, icon, color)
             VALUES (:uid, :name, :type, :icon, :color)'
        );
        $stmt->execute([
            ':uid'   => $data['user_id'],
            ':name'  => $data['name'],
            ':type'  => $data['type']  ?? 'expense',
            ':icon'  => $data['icon']  ?? null,
            ':color' => $data['color'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật danh mục.
     * 
     * @throws \InvalidArgumentException nếu user_id <= 0
     */
    public function update(int $id, int $userId, array $data): bool
    {
        // ❌ Validation: user_id phải > 0 (tránh xung đột FK)
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID không hợp lệ. Bạn phải đăng nhập.');
        }

        $stmt = $this->db->prepare(
            'UPDATE categories
             SET    name  = :name,
                    type  = :type,
                    color = :color,
                    icon  = :icon
             WHERE  id = :id AND user_id = :uid'
        );
        $stmt->execute([
            ':name'  => $data['name'],
            ':type'  => $data['type']  ?? 'expense',
            ':color' => $data['color'] ?? null,
            ':icon'  => $data['icon']  ?? null,
            ':id'    => $id,
            ':uid'   => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Xoá danh mục — chỉ xoá của mình.
     * Nếu có FK constraint (transactions dùng danh mục này),
     * MySQL sẽ throw PDOException với SQLSTATE 23000.
     * Controller phải bắt exception đó và hiện lỗi thân thiện.
     */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM categories WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
