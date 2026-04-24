<?php
// ============================================================
// BUDGET MODEL — app/Models/Budget.php
// ============================================================
// TODO (TV2 — Ngày 1): Implement isExceeded()
// ============================================================

namespace App\Models;

class Budget
{
    public function __construct(
        private int    $id,
        private int    $userId,
        private int    $categoryId,
        private float  $limitAmount,
        private int    $month,
        private int    $year
    ) {}

    /**
     * Kiểm tra số tiền đã chi có vượt hạn mức không.
     *
     * @param  float $spent  Tổng đã chi trong tháng (từ TransactionRepository)
     * @return bool  true nếu $spent > $limitAmount
     *
     * TODO: Hỏi team: dùng >= hay > ? (chi đúng bằng hạn mức có cảnh báo không?)
     */
    public function isExceeded(float $spent): bool
    {
        // TODO
        return $spent > $this->limitAmount;
    }

    /**
     * Tính % đã dùng — dùng cho View hiển thị thanh tiến độ.
     * Trả về 0.0 nếu limitAmount = 0 (tránh division by zero).
     */
    public function getUsagePercent(float $spent): float
    {
        if ($this->limitAmount <= 0) {
            return 0.0;
        }
        return round(($spent / $this->limitAmount) * 100, 1);
    }

    // Getters
    public function getId(): int          { return $this->id; }
    public function getLimitAmount(): float { return $this->limitAmount; }
    public function getCategoryId(): int  { return $this->categoryId; }
    public function getMonth(): int       { return $this->month; }
    public function getYear(): int        { return $this->year; }

    /** Factory: tạo từ DB row array */
    public static function fromArray(array $row): self
    {
        return new self(
            id:          (int)   $row['id'],
            userId:      (int)   $row['user_id'],
            categoryId:  (int)   $row['category_id'],
            limitAmount: (float) $row['limit_amount'],
            month:       (int)   $row['month'],
            year:        (int)   $row['year']
        );
    }
}
