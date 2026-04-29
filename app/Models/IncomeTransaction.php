<?php
// ============================================================
// MODEL — app/Models/IncomeTransaction.php
// ============================================================
// Kế thừa Transaction — implement 3 bước cho luồng thu nhập:
//   validate(): chỉ check amount > 0
//   save():     lưu vào DB với type = 'income'
//   notify():   ghi log đơn giản
// ============================================================

namespace App\Models;

use App\Repositories\TransactionRepository;

class IncomeTransaction extends Transaction
{
    public function getType(): string
    {
        return 'income';
    }

    // Bước 1 — Validate
    protected function validate(): void
    {
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Số tiền thu nhập phải lớn hơn 0.');
        }
        if (empty(trim($this->transDate))) {
            throw new \InvalidArgumentException('Ngày giao dịch không được để trống.');
        }
        if ($this->categoryId <= 0) {
            throw new \InvalidArgumentException('Vui lòng chọn danh mục.');
        }
    }

    // Bước 2 — Save
    protected function save(): void
    {
        $repo     = new TransactionRepository();
        $this->id = $repo->save($this->toArray());
    }

    // Bước 3 — Notify (ghi log)
    protected function notify(): void
    {
        error_log(sprintf(
            '[Income] id=%d | user=%d | amount=%.2f | date=%s',
            $this->id ?? 0,
            $this->userId,
            $this->amount,
            $this->transDate
        ));
    }
}
