<?php
// ============================================================
// TRANSACTION REPOSITORY — app/Repositories/TransactionRepository.php
// ============================================================
// Bảng transactions dùng chung cho cả Income lẫn Expense.
// TV3 viết class này — TV4 và TV5 sẽ dùng lại.
//
// QUAN TRỌNG: Mọi query đều phải có điều kiện user_id = ?
//             Không được bỏ sót — bảo mật multi-user!
//
// TODO (TV3 — Ngày 2): Implement tất cả method bên dưới
// ============================================================

namespace App\Repositories;

class TransactionRepository extends BaseRepository
{
    protected function getTable(): string
    {
        return 'transactions';
    }

    /**
     * Lấy danh sách theo loại (income/expense).
     * Dùng bởi: IncomeController::index(), ExpenseController::index()
     *
     * @param string $type    'income' hoặc 'expense'
     * @param int    $userId  Bắt buộc — filter theo user
     * @param int    $limit   Số record (cho phân trang)
     * @param int    $offset  Vị trí bắt đầu (cho phân trang)
     */
    public function findByType(string $type, int $userId,
                                int $limit = 15, int $offset = 0): array
    {
        // TODO: SELECT * FROM transactions WHERE user_id=? AND type=?
        //       ORDER BY trans_date DESC LIMIT ? OFFSET ?
        throw new \RuntimeException('Chưa implement — TV3 Ngày 2');
    }

    /**
     * Đếm tổng record theo type (cho Paginator).
     */
    public function countByType(string $type, int $userId): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV3 Ngày 2');
    }

    /**
     * Tổng tiền theo danh mục và tháng/năm.
     * Dùng bởi: BudgetService::checkAlert()
     *
     * @return float  0.0 nếu không có giao dịch nào
     */
    public function getSumByCategory(int $categoryId, int $userId,
                                      int $month, int $year): float
    {
        // TODO: SELECT COALESCE(SUM(amount), 0) FROM transactions
        //       WHERE user_id=? AND category_id=? AND type='expense'
        //         AND MONTH(trans_date)=? AND YEAR(trans_date)=?
        throw new \RuntimeException('Chưa implement — TV3 Ngày 2');
    }

    /**
     * Tổng thu và chi theo tháng/năm.
     * Dùng bởi: ReportService::getSummaryByMonth()
     *
     * @return array ['income' => float, 'expense' => float]
     */
    public function getSummaryByMonth(int $userId, int $month, int $year): array
    {
        // TODO: SELECT type, SUM(amount) as total FROM transactions
        //       WHERE user_id=? AND MONTH(trans_date)=? AND YEAR(trans_date)=?
        //       GROUP BY type
        throw new \RuntimeException('Chưa implement — TV3/TV5 Ngày 2/3');
    }

    /**
     * Chi tiêu theo danh mục trong tháng.
     * Dùng bởi: ReportService::getByCategory() — cho biểu đồ tròn
     *
     * @return array [['category_name'=>..., 'total'=>...], ...]
     */
    public function getExpenseByCategory(int $userId, int $month, int $year): array
    {
        // TODO: SELECT c.name, SUM(t.amount) as total
        //       FROM transactions t JOIN categories c ON t.category_id = c.id
        //       WHERE t.user_id=? AND t.type='expense'
        //         AND MONTH(t.trans_date)=? AND YEAR(t.trans_date)=?
        //       GROUP BY t.category_id ORDER BY total DESC
        throw new \RuntimeException('Chưa implement — TV5 Ngày 3');
    }

    /**
     * Lưu giao dịch mới. Trả về ID vừa insert.
     * Dùng bởi: Transaction::save() trong template method process()
     */
    public function save(array $data): int
    {
        // TODO: INSERT INTO transactions (user_id, category_id, type, amount, note, trans_date)
        //       VALUES (?, ?, ?, ?, ?, ?)
        throw new \RuntimeException('Chưa implement — TV3 Ngày 2');
    }

    /**
     * Cập nhật giao dịch.
     * QUAN TRỌNG: Phải kiểm tra user_id trước khi update!
     */
    public function update(int $id, int $userId, array $data): bool
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /**
     * Xoá giao dịch — PHẢI kiểm tra user_id (ownership check).
     */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        // TODO: DELETE FROM transactions WHERE id=? AND user_id=?
        //       Dùng AND user_id=? để tránh user A xoá giao dịch của user B
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /**
     * Lấy tất cả giao dịch trong tháng để export CSV.
     * Dùng bởi: ReportController::export()
     */
    public function findByMonth(int $userId, int $month, int $year): array
    {
        // TODO: JOIN categories để lấy tên danh mục
        throw new \RuntimeException('Chưa implement — TV5 Ngày 6');
    }
}
