<?php
// ============================================================
// MODEL — app/Models/Budget.php
// ============================================================
// Đại diện cho 1 hạn mức ngân sách.
// TV2 viết — Ngày 1
//
// Quan trọng: isExceeded() dùng alert_threshold (mặc định 80%)
// không phải 100%. TV3 gọi method này qua BudgetService.
// ============================================================

namespace App\Models;

class Budget
{
    public function __construct(
        private int   $id,
        private int   $userId,
        private int   $categoryId,
        private float $limitAmount,
        private int   $alertThreshold = 80,   // Cảnh báo khi chi >= 80% hạn mức
        private int   $month          = 0,
        private int   $year           = 0,
    ) {}

    // ── Logic nghiệp vụ ───────────────────────────────────────

    /**
     * Kiểm tra số tiền đã chi có vượt ngưỡng cảnh báo không.
     *
     * Dùng alert_threshold thay vì cứng 100%:
     *   - spent >= limit * threshold / 100 → true
     *
     * Ví dụ: limit=500k, threshold=80 → cảnh báo khi spent >= 400k
     *
     * @param  float $spent Tổng đã chi trong tháng (từ TransactionRepository)
     * @return bool  true nếu đã vượt ngưỡng
     */
    public function isExceeded(float $spent): bool
    {
        if ($this->limitAmount <= 0) {
            return false;  // tránh division by zero
        }
        $threshold = $this->limitAmount * $this->alertThreshold / 100;
        return $spent >= $threshold;
    }

    /**
     * Tính % đã sử dụng — dùng cho thanh tiến độ trong View.
     * Trả về 0.0 nếu limitAmount = 0.
     */
    public function getUsagePercent(float $spent): float
    {
        if ($this->limitAmount <= 0) {
            return 0.0;
        }
        return min(round(($spent / $this->limitAmount) * 100, 1), 999.9);
    }

    /**
     * CSS class Bootstrap cho thanh progress — dựa trên % đã dùng.
     * <50%: success (xanh), 50–80%: warning (vàng), >80%: danger (đỏ)
     */
    public function getStatusClass(float $spent): string
    {
        $pct = $this->getUsagePercent($spent);
        if ($pct >= 80) return 'danger';
        if ($pct >= 50) return 'warning';
        return 'success';
    }

    // ── Getters ───────────────────────────────────────────────
    public function getId(): int           { return $this->id; }
    public function getUserId(): int       { return $this->userId; }
    public function getCategoryId(): int   { return $this->categoryId; }
    public function getLimitAmount(): float{ return $this->limitAmount; }
    public function getAlertThreshold(): int{ return $this->alertThreshold; }
    public function getMonth(): int        { return $this->month; }
    public function getYear(): int         { return $this->year; }

    /** Tạo từ DB row array */
    public static function fromArray(array $r): self
    {
        return new self(
            id:             (int)   $r['id'],
            userId:         (int)   $r['user_id'],
            categoryId:     (int)   $r['category_id'],
            limitAmount:    (float) $r['limit_amount'],
            alertThreshold: (int)  ($r['alert_threshold'] ?? 80),
            month:          (int)   $r['month'],
            year:           (int)   $r['year'],
        );
    }
}
