<?php
// ============================================================
// REPOSITORY — app/Repositories/BudgetRepository.php
// ============================================================
// Interface + Implementation cho bảng budgets.
// TV2 viết — Ngày 2
//
// Interface tách biệt contract khỏi implementation:
//   BudgetService chỉ biết về BudgetRepositoryInterface,
//   không biết cụ thể dùng MySQL hay gì khác.
//   → Dễ test (mock), dễ thay implementation.
// ============================================================

namespace App\Repositories;

// ── Interface ─────────────────────────────────────────────────
interface BudgetRepositoryInterface
{
    public function findByUserAndMonth(int $userId, int $month, int $year): array;
    public function findByCategoryAndMonth(int $userId, int $categoryId, int $month, int $year): ?\App\Models\Budget;
    public function upsert(array $data): bool;
    public function deleteByIdAndUser(int $id, int $userId): bool;

    // Kiểm tra tồn tại hạn mức cho danh mục/tháng/năm
    public function existsForCategoryMonth(int $userId, int $categoryId, int $month, int $year): bool;

    // Cập nhật hoặc tạo nhiều bản ghi (bulk upsert)
    public function bulkUpsert(array $rows): bool;
}

// ── Implementation ────────────────────────────────────────────
class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    protected function getTable(): string { return 'budgets'; }

    // ── READ ──────────────────────────────────────────────────

    /**
     * Lấy tất cả budget của user trong tháng, kèm tên + màu danh mục.
     * Dùng bởi: BudgetController::index()
     *
     * @return array Mỗi row gồm budget + category_name + category_color + category_icon
     */
    public function findByUserAndMonth(int $userId, int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*,
                    c.name  AS category_name,
                    c.color AS category_color,
                    c.icon  AS category_icon
             FROM   budgets b
             JOIN   categories c ON b.category_id = c.id
             WHERE  b.user_id = :uid
               AND  b.month   = :month
               AND  b.year    = :year
             ORDER BY c.name ASC'
        );
        $stmt->execute([':uid' => $userId, ':month' => $month, ':year' => $year]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy budget của 1 danh mục cụ thể trong tháng.
     * Dùng bởi: BudgetService::checkAlert()
     */
    public function findByCategoryAndMonth(
        int $userId, int $categoryId, int $month, int $year
    ): ?\App\Models\Budget {
        $stmt = $this->db->prepare(
            'SELECT * FROM budgets
             WHERE  user_id     = :uid
               AND  category_id = :cid
               AND  month       = :month
               AND  year        = :year
             LIMIT 1'
        );
        $stmt->execute([
            ':uid'   => $userId,
            ':cid'   => $categoryId,
            ':month' => $month,
            ':year'  => $year,
        ]);
        $row = $stmt->fetch();
        return $row ? \App\Models\Budget::fromArray($row) : null;
    }

    // ── WRITE ─────────────────────────────────────────────────

    /**
     * Tạo mới hoặc cập nhật budget (INSERT ... ON DUPLICATE KEY UPDATE).
     * Dựa vào UNIQUE KEY (user_id, category_id, month, year).
     */
    public function upsert(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO budgets
                (user_id, category_id, limit_amount, alert_threshold, month, year)
             VALUES
                (:uid, :cid, :limit, :threshold, :month, :year)
             ON DUPLICATE KEY UPDATE
                limit_amount    = VALUES(limit_amount),
                alert_threshold = VALUES(alert_threshold)'
        );
        $stmt->execute([
            ':uid'       => $data['user_id'],
            ':cid'       => $data['category_id'],
            ':limit'     => $data['limit_amount'],
            ':threshold' => $data['alert_threshold'] ?? 80,
            ':month'     => $data['month'],
            ':year'      => $data['year'],
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Xoá budget — chỉ được xoá budget của mình.
     */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM budgets WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Kiểm tra xem đã tồn tại bản ghi ngân sách cho user/category/month/year hay chưa.
     * Trả về true nếu đã có, false nếu chưa.
     *
     * @param int $userId
     * @param int $categoryId
     * @param int $month
     * @param int $year
     * @return bool
     */
    public function existsForCategoryMonth(int $userId, int $categoryId, int $month, int $year): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM budgets
             WHERE user_id = :uid
               AND category_id = :cid
               AND month = :month
               AND year  = :year
             LIMIT 1'
        );
        $stmt->execute([
            ':uid'   => $userId,
            ':cid'   => $categoryId,
            ':month' => $month,
            ':year'  => $year,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Bulk upsert: nhận mảng các bản ghi và chạy INSERT ... ON DUPLICATE cho từng dòng.
     * Lưu ý: không bắt transaction ở đây → caller (Service) phải quản lý transaction.
     *
     * @param array $rows Mỗi phần tử là mảng có keys: user_id, category_id, limit_amount, alert_threshold, month, year
     * @return bool true nếu chạy xong (không kiểm tra từng row chi tiết)
     */
    public function bulkUpsert(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO budgets
                (user_id, category_id, limit_amount, alert_threshold, month, year)
             VALUES
                (:uid, :cid, :limit, :threshold, :month, :year)
             ON DUPLICATE KEY UPDATE
                limit_amount    = VALUES(limit_amount),
                alert_threshold = VALUES(alert_threshold)'
        );

        foreach ($rows as $r) {
            $stmt->execute([
                ':uid'       => $r['user_id'],
                ':cid'       => $r['category_id'],
                ':limit'     => $r['limit_amount'],
                ':threshold' => $r['alert_threshold'] ?? 80,
                ':month'     => $r['month'],
                ':year'      => $r['year'],
            ]);
        }

        return true;
    }
}
