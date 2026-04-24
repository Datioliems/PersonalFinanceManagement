<?php
// ============================================================
// INCOME TRANSACTION — app/Models/IncomeTransaction.php
// ============================================================
// TODO (TV1 — Ngày 2): Implement validate(), save(), notify()
// ============================================================

namespace App\Models;

use App\Repositories\TransactionRepository;

class IncomeTransaction extends Transaction
{
    public function getType(): string { return 'income'; }

    /**
     * Validate: amount phải > 0.
     * Income không cần check budget.
     *
     * @throws \InvalidArgumentException
     */
    protected function validate(): void
    {
        // TODO
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Số tiền thu nhập phải lớn hơn 0.');
        }
        if (empty($this->transDate)) {
            throw new \InvalidArgumentException('Ngày giao dịch không được để trống.');
        }
    }

    /**
     * Lưu vào bảng transactions với type='income'.
     * TODO: khởi tạo TransactionRepository, gọi save(), set $this->id
     */
    protected function save(): void
    {
        // TODO
        $repo = new TransactionRepository();
        $this->id = $repo->save($this->toArray());
    }

    /**
     * Ghi log sau khi lưu thành công.
     * TODO: ghi vào storage/logs/income.log hoặc bảng log
     */
    protected function notify(): void
    {
        // TODO: ghi log đơn giản
        // error_log("Income added: id={$this->id}, amount={$this->amount}");
    }
}
