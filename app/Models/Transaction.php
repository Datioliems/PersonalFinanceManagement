<?php
// ============================================================
// MODEL — app/Models/Transaction.php
// ============================================================
// Design Pattern: TEMPLATE METHOD
//
//   abstract class Transaction
//       └── final process()  ← luồng cố định, không ai override được
//             ├── validate() ← abstract — subclass tự implement
//             ├── save()     ← abstract — subclass tự implement
//             └── notify()   ← abstract — subclass tự implement
//
// TV1 viết class này — TV3 (Expense) và TV4 (Income) kế thừa.
// KHÔNG đổi signature sau khi cả team đã confirm.
// ============================================================

namespace App\Models;

abstract class Transaction
{
    // ── Properties ────────────────────────────────────────────
    protected ?int   $id         = null;
    protected int    $userId;
    protected int    $categoryId;
    protected float  $amount;
    protected string $note;
    protected string $transDate;  // format: Y-m-d

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
     * final: KHÔNG subclass nào được override thứ tự này.
     *
     * Luồng bất biến: validate → save → notify
     */
    final public function process(): void
    {
        $this->validate();  // Bước 1: kiểm tra dữ liệu
        $this->save();      // Bước 2: lưu vào DB
        $this->notify();    // Bước 3: xử lý sau khi lưu
    }

    // ── Abstract steps (subclass phải implement) ─────────────
    /**
     * Kiểm tra dữ liệu.
     * @throws \InvalidArgumentException nếu dữ liệu không hợp lệ
     */
    abstract protected function validate(): void;

    /**
     * Lưu vào DB qua TransactionRepository.
     * Sau khi xong, $this->id phải được set.
     */
    abstract protected function save(): void;

    /**
     * Hành động sau khi lưu thành công.
     * Income: ghi log.
     * Expense: kiểm tra ngân sách, cảnh báo nếu vượt.
     */
    abstract protected function notify(): void;

    // ── Phải implement: trả 'income' hoặc 'expense' ──────────
    abstract public function getType(): string;

    // ── Getters ───────────────────────────────────────────────
    public function getId(): ?int         { return $this->id; }
    public function getUserId(): int      { return $this->userId; }
    public function getCategoryId(): int  { return $this->categoryId; }
    public function getAmount(): float    { return $this->amount; }
    public function getTransDate(): string { return $this->transDate; }
    public function getNote(): string     { return $this->note; }

    /**
     * Trả array để Repository INSERT vào DB.
     * Cột type được lấy từ getType() — không hardcode.
     */
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
