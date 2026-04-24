<?php
// ============================================================
// PAGINATOR — app/Helpers/Paginator.php
// ============================================================
// TODO (TV3 — Ngày 6): Implement getTotalPages(), getOffset()
// ============================================================

namespace App\Helpers;

class Paginator
{
    public function __construct(
        private int $total,       // Tổng số record
        private int $perPage,     // Số record mỗi trang
        private int $currentPage  // Trang hiện tại (bắt đầu từ 1)
    ) {}

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /** Dùng cho SQL: LIMIT ? OFFSET ? */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function hasPrevious(): bool { return $this->currentPage > 1; }
    public function hasNext(): bool     { return $this->currentPage < $this->getTotalPages(); }
    public function getCurrentPage(): int { return $this->currentPage; }
    public function getPerPage(): int   { return $this->perPage; }
    public function getTotal(): int     { return $this->total; }

    /** Build URL với query string cho nút phân trang */
    public function buildUrl(string $basePath, int $page): string
    {
        $params = $_GET;
        $params['page'] = $page;
        return $basePath . '?' . http_build_query($params);
    }
}
