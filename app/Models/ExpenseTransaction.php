<?php
// ============================================================
// MODEL — app/Models/ExpenseTransaction.php
// ============================================================
// Kế thừa abstract Transaction (TV1 viết).
// Implement 3 bước cho luồng chi tiêu:
//   validate(): check amount > 0, categoryId hợp lệ
//   save():     lưu DB với type = 'expense'
//   notify():   gọi BudgetService::checkAlert() sau khi lưu
//
// TV3 viết — Ngày 2
// ============================================================

namespace App\Models;

use App\Repositories\TransactionRepository;
use App\Services\BudgetService;

class ExpenseTransaction extends Transaction
{
    /** BudgetService được gán từ ExpenseService trước khi gọi process() */
    private ?BudgetService $budgetService = null;

    /** Kết quả checkAlert() sau notify() — Controller đọc để set flash message */
    private ?string $budgetAlert = null;

    public function getType(): string { return 'expense'; }

    /** ExpenseService gán trước khi process() */
    public function setBudgetService(BudgetService $service): void
    {
        $this->budgetService = $service;
    }

    /** Controller hoặc Service đọc sau process() */
    public function getBudgetAlert(): ?string { return $this->budgetAlert; }

    // Bước 1 — Validate ───────────────────────────────────────
    protected function validate(): void
    {
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Số tiền chi tiêu phải lớn hơn 0.');
        }
        if ($this->categoryId <= 0) {
            throw new \InvalidArgumentException('Vui lòng chọn danh mục.');
        }
        if (empty(trim($this->transDate))) {
            throw new \InvalidArgumentException('Ngày giao dịch không được để trống.');
        }
        // Validate ngày hợp lệ
        $d = \DateTime::createFromFormat('Y-m-d', $this->transDate);
        if (!$d || $d->format('Y-m-d') !== $this->transDate) {
            throw new \InvalidArgumentException('Ngày giao dịch không hợp lệ (định dạng Y-m-d).');
        }
    }

    // Bước 2 — Save ───────────────────────────────────────────
    protected function save(): void
    {
        $repo     = new TransactionRepository();
        $this->id = $repo->save($this->toArray());
    }

    // Bước 3 — Notify: kiểm tra ngân sách ─────────────────────
    /**
     * Sau khi lưu thành công, kiểm tra ngân sách.
     * Nếu có BudgetService và vượt ngưỡng → lưu vào $this->budgetAlert.
     * ExpenseService sẽ đọc và set flash message.
     */
    protected function notify(): void
    {
        if ($this->budgetService === null) {
            return;
        }

        $month = (int) date('n', strtotime($this->transDate));
        $year  = (int) date('Y', strtotime($this->transDate));

        $this->budgetAlert = $this->budgetService->checkAlert(
            $this->categoryId,
            $this->userId,
            $month,
            $year
        );

        error_log(sprintf(
            '[Expense] id=%d | user=%d | cat=%d | amount=%.2f | alert=%s',
            $this->id ?? 0, $this->userId, $this->categoryId,
            $this->amount, $this->budgetAlert ?? 'none'
        ));
    }
}
