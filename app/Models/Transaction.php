<?php
// ============================================================
// ABSTRACT TRANSACTION — app/Models/Transaction.php
// ============================================================
// Design Pattern: TEMPLATE METHOD
//
// process() là "template" — định nghĩa khung cố định:
//   validate() → save() → notify()
// Subclass (Income/Expense) override từng bước, không override process().
//
// TODO (TV1 — Ngày 1): Viết class này đầu tiên
//       TV3 và TV4 sẽ kế thừa — đừng đổi signature sau khi confirm
// ============================================================

namespace App\Models;

use App\Core\Database;

abstract class Transaction
{
    protected ?int   $id         = null;
    protected int    $userId;
    protected int    $categoryId;
    protected float  $amount;
    protected string $note;
    protected string $transDate; // format: Y-m-d

    public function __construct(
        int    $userId,
        int    $categoryId,
        float  $amount,
        string $transDate,
        string $note = ''
    ) {
        $this->userId     = $userId;
        $this->categoryId = $categoryId;
        $this->amount     = $amount;
        $this->transDate  = $transDate;
        $this->note       = $note;
    }

    // ── Template Method ──────────────────────────────────────
    /**
     * Điểm gọi duy nhất từ bên ngoài.
     * final: không ai được phép override trình tự này.
     *
     * Luồng: validate → save → notify
     */
    final public function process(): void
    {
        $this->validate();
        $this->save();
        $this->notify();
    }

    // ── Abstract steps (subclass phải implement) ─────────────
    /**
     * Kiểm tra dữ liệu đầu vào.
     * Income: chỉ check amount > 0
     * Expense: check amount > 0, category hợp lệ
     *
     * @throws \InvalidArgumentException nếu dữ liệu không hợp lệ
     */
    abstract protected function validate(): void;

    /**
     * Lưu vào DB qua TransactionRepository.
     * Sau khi save, $this->id được set.
     */
    abstract protected function save(): void;

    /**
     * Hành động sau khi lưu thành công.
     * Income: ghi log
     * Expense: ghi log + trigger check budget
     */
    abstract protected function notify(): void;

    // ── Helper methods ────────────────────────────────────────
    abstract public function getType(): string; // trả 'income' hoặc 'expense'

    public function getId(): ?int         { return $this->id; }
    public function getUserId(): int      { return $this->userId; }
    public function getCategoryId(): int  { return $this->categoryId; }
    public function getAmount(): float    { return $this->amount; }
    public function getTransDate(): string { return $this->transDate; }
    public function getNote(): string     { return $this->note; }

    public function toArray(): array
    {
        return [
            'user_id'     => $this->userId,
            'category_id' => $this->categoryId,
            'type'        => $this->getType(),
            'amount'      => $this->amount,
            'note'        => $this->note,
            'trans_date'  => $this->transDate,
        ];
    }
}
