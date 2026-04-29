<?php
// ============================================================
// REPOSITORY — app/Repositories/TransactionRepository.php
// ============================================================
// TV3 viết và sở hữu file này — Ngày 2.
// TV4 và TV5 dùng lại: KHÔNG đổi method signature sau khi confirm.
//
// Mọi query đều filter theo user_id — bảo mật multi-user.
// ============================================================

namespace App\Repositories;

class TransactionRepository extends BaseRepository
{
    protected function getTable(): string { return 'transactions'; }

    // ── READ ──────────────────────────────────────────────────

    /**
     * Danh sách giao dịch theo type + phân trang.
     * Dùng bởi: ExpenseController::index(), IncomeController::index()
     *
     * @param string $type    'income' | 'expense'
     * @param int    $userId  bắt buộc
     * @param int    $limit   số record/trang (Paginator::getPerPage())
     * @param int    $offset  vị trí bắt đầu (Paginator::getOffset())
     */
    public function findByType(
        string $type, int $userId,
        int $limit = 15, int $offset = 0
    ): array {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM   transactions t
             JOIN   categories   c ON t.category_id = c.id
             WHERE  t.user_id = :uid AND t.type = :type
             ORDER BY t.trans_date DESC, t.id DESC
             LIMIT  :limit OFFSET :offset'
        );
        $stmt->bindValue(':uid',    $userId,   \PDO::PARAM_INT);
        $stmt->bindValue(':type',   $type,     \PDO::PARAM_STR);
        $stmt->bindValue(':limit',  $limit,    \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng record (cho Paginator).
     */
    public function countByType(string $type, int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM transactions WHERE user_id = ? AND type = ?'
        );
        $stmt->execute([$userId, $type]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Danh sách với filter + sort (TV4 dùng cho filter/sort nâng cao).
     * Dùng whitelist để tránh SQL injection từ $_GET params.
     *
     * @param string $sort        'date_desc'|'date_asc'|'amount_desc'|'amount_asc'
     * @param string $filterMonth 'Y-m' VD: '2025-04', '' = tất cả
     */
    public function findFiltered(
        int    $userId,
        string $type,
        string $sort        = 'date_desc',
        string $filterMonth = '',
        int    $limit       = 15,
        int    $offset      = 0
    ): array {
        // Whitelist sort để tránh SQL injection
        $orderMap = [
            'date_desc'   => 't.trans_date DESC, t.id DESC',
            'date_asc'    => 't.trans_date ASC,  t.id ASC',
            'amount_desc' => 't.amount DESC',
            'amount_asc'  => 't.amount ASC',
        ];
        $orderBy = $orderMap[$sort] ?? $orderMap['date_desc'];

        $sql    = 'SELECT t.*, c.name AS category_name, c.color AS category_color
                   FROM   transactions t
                   JOIN   categories   c ON t.category_id = c.id
                   WHERE  t.user_id = :uid AND t.type = :type';
        $params = [':uid' => $userId, ':type' => $type];

        if (!empty($filterMonth) && preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $sql          .= ' AND DATE_FORMAT(t.trans_date, \'%Y-%m\') = :month';
            $params[':month'] = $filterMonth;
        }

        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm theo filter (cho Paginator khi dùng findFiltered).
     */
    public function countFiltered(int $userId, string $type, string $filterMonth = ''): int
    {
        $sql    = 'SELECT COUNT(*) FROM transactions WHERE user_id = :uid AND type = :type';
        $params = [':uid' => $userId, ':type' => $type];

        if (!empty($filterMonth) && preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $sql          .= ' AND DATE_FORMAT(trans_date, \'%Y-%m\') = :month';
            $params[':month'] = $filterMonth;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Tổng tiền chi theo danh mục trong tháng.
     * Dùng bởi: BudgetService::checkAlert() và BudgetService::getBudgetSummary()
     */
    public function getSumByCategory(int $categoryId, int $userId, int $month, int $year): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM   transactions
             WHERE  user_id     = :uid
               AND  category_id = :cid
               AND  type        = 'expense'
               AND  MONTH(trans_date) = :month
               AND  YEAR(trans_date)  = :year"
        );
        $stmt->execute([':uid' => $userId, ':cid' => $categoryId, ':month' => $month, ':year' => $year]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Tổng thu/chi theo tháng — cho FinanceReport::generateMonthly() (TV5 dùng).
     * @return array ['income' => float, 'expense' => float]
     */
    public function getSummaryByMonth(int $userId, int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT type, COALESCE(SUM(amount), 0) AS total
             FROM   transactions
             WHERE  user_id = :uid
               AND  MONTH(trans_date) = :month
               AND  YEAR(trans_date)  = :year
             GROUP BY type'
        );
        $stmt->execute([':uid' => $userId, ':month' => $month, ':year' => $year]);
        $rows   = $stmt->fetchAll();
        $result = ['income' => 0.0, 'expense' => 0.0];
        foreach ($rows as $row) {
            $result[$row['type']] = (float) $row['total'];
        }
        return $result;
    }

    /**
     * Chi tiêu theo danh mục trong tháng — cho biểu đồ tròn Chart.js (TV5 dùng).
     * @return array [['category_name'=>..., 'color'=>..., 'total'=>...], ...]
     */
    public function getExpenseByCategory(int $userId, int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.name AS category_name, c.color, SUM(t.amount) AS total
             FROM   transactions t
             JOIN   categories   c ON t.category_id = c.id
             WHERE  t.user_id = :uid AND t.type = \'expense\'
               AND  MONTH(t.trans_date) = :month
               AND  YEAR(t.trans_date)  = :year
             GROUP BY t.category_id
             ORDER BY total DESC'
        );
        $stmt->execute([':uid' => $userId, ':month' => $month, ':year' => $year]);
        return $stmt->fetchAll();
    }

    /**
     * Tất cả giao dịch trong tháng để export CSV (TV5 dùng).
     */
    public function findByMonth(int $userId, int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.trans_date, t.type, c.name AS category_name,
                    t.amount, t.note
             FROM   transactions t
             JOIN   categories   c ON t.category_id = c.id
             WHERE  t.user_id = :uid
               AND  MONTH(t.trans_date) = :month
               AND  YEAR(t.trans_date)  = :year
             ORDER BY t.trans_date ASC, t.id ASC'
        );
        $stmt->execute([':uid' => $userId, ':month' => $month, ':year' => $year]);
        return $stmt->fetchAll();
    }

    // ── WRITE ─────────────────────────────────────────────────

    /**
     * Lưu giao dịch mới. Trả về ID vừa insert.
     * Dùng bởi: IncomeTransaction::save(), ExpenseTransaction::save()
     */
    public function save(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions
                (user_id, category_id, type, amount, note, trans_date)
             VALUES
                (:uid, :cid, :type, :amount, :note, :date)'
        );
        $stmt->execute([
            ':uid'    => $data['user_id'],
            ':cid'    => $data['category_id'],
            ':type'   => $data['type'],
            ':amount' => $data['amount'],
            ':note'   => $data['note'] ?? '',
            ':date'   => $data['trans_date'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật giao dịch.
     * Kiểm tra user_id để tránh sửa dữ liệu người khác.
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE transactions
             SET    category_id = :cid,
                    amount      = :amount,
                    note        = :note,
                    trans_date  = :date,
                    updated_at  = NOW()
             WHERE  id      = :id
               AND  user_id = :uid'
        );
        $stmt->execute([
            ':cid'    => $data['category_id'],
            ':amount' => $data['amount'],
            ':note'   => $data['note'] ?? '',
            ':date'   => $data['trans_date'],
            ':id'     => $id,
            ':uid'    => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Xoá giao dịch — PHẢI có user_id để tránh xoá của người khác.
     * Ownership check: WHERE id = ? AND user_id = ?
     */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM transactions WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }


    /**
     * Lấy tất cả giao dịch trong khoảng ngày.
     * Dùng bởi: FinanceReport::getTrend(), ReportService::getTrend()
     */
    public function findByDateRange(int $userId, \DateTime $from, \DateTime $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM transactions
             WHERE  user_id    = :uid
               AND  trans_date >= :from
               AND  trans_date <= :to
             ORDER BY trans_date ASC'
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':from' => $from->format('Y-m-d'),
            ':to'   => $to->format('Y-m-d'),
        ]);
        return $stmt->fetchAll();
    }
}
