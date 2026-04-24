<?php
// ============================================================
// EXPENSE TRANSACTION — app/Models/ExpenseTransaction.php
// ============================================================
// TODO (TV3 — Ngày 2): Implement validate(), save(), notify()
// ============================================================

namespace App\Models;

use App\Repositories\TransactionRepository;
use App\Services\BudgetService;

class ExpenseTransaction extends Transaction
{
    /** Gán từ bên ngoài nếu cần check budget trong notify() */
    private ?BudgetService $budgetService = null;
    private ?string $budgetAlert = null;

    public function getType(): string { return 'expense'; }

    public function setBudgetService(BudgetService $service): void
    {
        $this->budgetService = $service;
    }

    public function getBudgetAlert(): ?string { return $this->budgetAlert; }

    /**
     * Validate: amount > 0, categoryId > 0, ngày hợp lệ.
     *
     * @throws \InvalidArgumentException
     */
    protected function validate(): void
    {
        // TODO
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Số tiền chi tiêu phải lớn hơn 0.');
        }
        if ($this->categoryId <= 0) {
            throw new \InvalidArgumentException('Phải chọn danh mục.');
        }
        if (empty($this->transDate)) {
            throw new \InvalidArgumentException('Ngày giao dịch không được để trống.');
        }
    }

    /**
     * Lưu vào bảng transactions với type='expense'.
     * TODO: khởi tạo TransactionRepository, gọi save(), set $this->id
     */
    protected function save(): void
    {
        // TODO
        $repo = new TransactionRepository();
        $this->id = $repo->save($this->toArray());
    }

    /**
     * Sau khi lưu: kiểm tra có vượt ngân sách không.
     * Nếu có BudgetService, gọi checkAlert() và lưu vào $this->budgetAlert.
     *
     * TODO: gọi $this->budgetService->checkAlert(...)
     */
    protected function notify(): void
    {
        // TODO
        if ($this->budgetService !== null) {
            $month = (int) date('n', strtotime($this->transDate));
            $year  = (int) date('Y', strtotime($this->transDate));
            $this->budgetAlert = $this->budgetService->checkAlert(
                $this->categoryId,
                $this->userId,
                $month,
                $year
            );
        }
    }
}
