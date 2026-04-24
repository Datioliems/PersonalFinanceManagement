<?php
// ============================================================
// BUDGET SERVICE — app/Services/BudgetService.php
// ============================================================
// TODO (TV2 — Ngày 3): Implement checkAlert(), setLimit()
// ============================================================

namespace App\Services;

use App\Models\Budget;
use App\Repositories\BudgetRepository;
use App\Repositories\TransactionRepository;

class BudgetService
{
    public function __construct(
        private BudgetRepository      $budgetRepo,
        private TransactionRepository $txRepo
    ) {}

    /**
     * Kiểm tra chi tiêu có vượt ngân sách không.
     * Gọi sau mỗi lần thêm expense.
     *
     * TODO:
     *   1. $spent = txRepo->getSumByCategory(categoryId, userId, month, year)
     *   2. $budget = budgetRepo->findByCategoryAndMonth(...)
     *   3. Nếu không có budget → return null (chưa đặt hạn mức)
     *   4. Nếu $budget->isExceeded($spent) → return string cảnh báo
     *   5. Ngược lại → return null
     *
     * @return string|null  Chuỗi cảnh báo hoặc null nếu không vượt
     */
    public function checkAlert(int $categoryId, int $userId,
                                int $month, int $year): ?string
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }

    /**
     * Đặt/cập nhật hạn mức ngân sách.
     * Dùng INSERT ... ON DUPLICATE KEY UPDATE.
     */
    public function setLimit(int $userId, int $categoryId,
                              float $limitAmount, int $month, int $year): bool
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }

    /**
     * Lấy danh sách budget kèm % đã dùng — cho View.
     *
     * @return array [['category_name'=>..., 'limit'=>..., 'spent'=>..., 'percent'=>..., 'status'=>'safe|warning|danger'], ...]
     */
    public function getBudgetSummary(int $userId, int $month, int $year): array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }
}
