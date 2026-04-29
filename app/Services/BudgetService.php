<?php
// ============================================================
// SERVICE — app/Services/BudgetService.php
// ============================================================
// Business logic cho ngân sách.
// Không biết về HTTP hay HTML.
//
// Dependency Injection: 2 Repository inject qua constructor.
// TV2 viết — Ngày 3
//
// Quan trọng: TV3 sẽ gọi checkAlert() sau mỗi expense.
// Không được đổi signature method này sau khi TV3 đã dùng.
// ============================================================

namespace App\Services;

use App\Models\Budget;
use App\Repositories\BudgetRepository;
use App\Repositories\BudgetRepositoryInterface;
use App\Repositories\TransactionRepository;

class BudgetService
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepo,
        private TransactionRepository     $txRepo,
    ) {}

    // ── checkAlert() ─────────────────────────────────────────
    /**
     * Kiểm tra chi tiêu tháng hiện tại có vượt ngưỡng cảnh báo không.
     * Được TV3 gọi trong ExpenseService::add() sau mỗi giao dịch chi.
     *
     * Luồng:
     *   1. Lấy tổng chi theo danh mục từ TransactionRepository
     *   2. Lấy budget của danh mục đó
     *   3. Nếu không có budget → null (chưa đặt hạn mức)
     *   4. Gọi Budget::isExceeded() → trả cảnh báo hay null
     *
     * @param  int         $categoryId
     * @param  int         $userId
     * @param  int         $month      1–12
     * @param  int         $year
     * @return string|null Chuỗi cảnh báo hoặc null
     */
    public function checkAlert(int $categoryId, int $userId, int $month, int $year): ?string
    {
        // Lấy tổng đã chi trong tháng
        $spent  = $this->txRepo->getSumByCategory($categoryId, $userId, $month, $year);

        // Lấy hạn mức
        $budget = $this->budgetRepo->findByCategoryAndMonth($userId, $categoryId, $month, $year);

        if (!$budget) {
            return null;  // chưa đặt hạn mức → không cảnh báo
        }

        if (!$budget->isExceeded($spent)) {
            return null;  // chưa vượt ngưỡng
        }

        $pct     = round($budget->getUsagePercent($spent), 1);
        $limit   = number_format($budget->getLimitAmount(), 0, ',', '.');
        $spentFmt = number_format($spent, 0, ',', '.');

        return "⚠️ Đã chi {$spentFmt}đ / {$limit}đ ({$pct}%) ngân sách tháng này!";
    }

    // ── setLimit() ───────────────────────────────────────────
    /**
     * Đặt hoặc cập nhật hạn mức ngân sách.
     * Dùng INSERT ... ON DUPLICATE KEY UPDATE.
     *
     * @throws \InvalidArgumentException nếu limit <= 0
     */
    public function setLimit(
        int   $userId,
        int   $categoryId,
        float $limitAmount,
        int   $month,
        int   $year,
        int   $alertThreshold = 80
    ): bool {
        if ($limitAmount <= 0) {
            throw new \InvalidArgumentException('Hạn mức phải lớn hơn 0.');
        }
        if ($alertThreshold < 1 || $alertThreshold > 100) {
            throw new \InvalidArgumentException('Ngưỡng cảnh báo phải từ 1–100%.');
        }

        return $this->budgetRepo->upsert([
            'user_id'         => $userId,
            'category_id'     => $categoryId,
            'limit_amount'    => $limitAmount,
            'alert_threshold' => $alertThreshold,
            'month'           => $month,
            'year'            => $year,
        ]);
    }

    // ── getBudgetSummary() ────────────────────────────────────
    /**
     * Lấy danh sách budget của user trong tháng, kèm số đã chi
     * và thông tin cần để render thanh tiến độ màu sắc.
     *
     * @return array Mỗi phần tử gồm: budget row + spent + pct + status_class
     */
    public function getBudgetSummary(int $userId, int $month, int $year): array
    {
        $rows = $this->budgetRepo->findByUserAndMonth($userId, $month, $year);

        return array_map(function (array $row) use ($userId, $month, $year) {
            $budget = Budget::fromArray($row);
            $spent  = $this->txRepo->getSumByCategory(
                $row['category_id'], $userId, $month, $year
            );

            return array_merge($row, [
                'spent'        => $spent,
                'pct'          => $budget->getUsagePercent($spent),
                'status_class' => $budget->getStatusClass($spent),
                'is_exceeded'  => $budget->isExceeded($spent),
            ]);
        }, $rows);
    }
}
