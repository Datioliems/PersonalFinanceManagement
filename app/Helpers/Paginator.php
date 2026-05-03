<?php
// ============================================================
// HELPER — app/Helpers/Paginator.php
// ============================================================
// Class phân trang tái sử dụng — TV3 viết, TV4 dùng lại.
// TV3 viết — Ngày 6 (Nâng cao)
//
// Quan trọng: Paginator KHÔNG biết về DB hay HTTP.
// Chỉ làm toán thuần: tổng trang, offset, prev/next.
// ============================================================

namespace App\Helpers;

class Paginator
{
    public function __construct(
        private int $total,       // Tổng số record (từ countBy*)
        private int $perPage,     // Số record mỗi trang (VD: 15)
        private int $currentPage  // Trang hiện tại (từ $_GET['page'], min 1)
    ) {
        // Đảm bảo currentPage hợp lệ
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
        if ($this->currentPage > $this->getTotalPages() && $this->getTotalPages() > 0) {
            $this->currentPage = $this->getTotalPages();
        }
    }

    /** Tổng số trang */
    public function getTotalPages(): int
    {
        if ($this->total === 0) return 1;
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Offset cho SQL: LIMIT ? OFFSET ?
     * Ví dụ: trang 3, 15 mục/trang → offset = (3-1)*15 = 30
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function hasPrevious(): bool { return $this->currentPage > 1; }
    public function hasNext(): bool     { return $this->currentPage < $this->getTotalPages(); }

    public function getCurrentPage(): int { return $this->currentPage; }
    public function getPerPage(): int     { return $this->perPage; }
    public function getTotal(): int       { return $this->total; }

    /**
     * Tạo URL cho trang N (giữ các GET param khác như filter/sort).
     */
    public function buildUrl(string $basePath, int $page): string
    {
        $params         = $_GET ?? [];
        $params['page'] = $page;
        return $basePath . '?' . http_build_query($params);
    }

    /**
     * Render HTML phân trang hiện đại (class .pager-*).
     * CSS định nghĩa tại: public/css/transactions.css
     */
    public function render(string $basePath): string
    {
        $totalPages  = $this->getTotalPages();
        $currentPage = $this->currentPage;

        // Ẩn nếu chỉ có 1 trang
        if ($totalPages <= 1) return '';

        $html  = '<nav class="pager-wrap" aria-label="Phân trang">';

        /* ── Thông tin tóm tắt ── */
        $from = ($currentPage - 1) * $this->perPage + 1;
        $to   = min($currentPage * $this->perPage, $this->total);
        $html .= '<span class="pager-info">'
               . number_format($from) . '–' . number_format($to)
               . ' / ' . number_format($this->total) . ' kết quả'
               . '</span>';

        /* ── Danh sách nút ── */
        $html .= '<ul class="pager-list">';

        /* Nút Prev */
        if ($this->hasPrevious()) {
            $html .= '<li><a class="pager-btn" href="'
                   . htmlspecialchars($this->buildUrl($basePath, $currentPage - 1))
                   . '" aria-label="Trang trước">'
                   . '<i class="bi bi-chevron-left"></i>'
                   . '</a></li>';
        } else {
            $html .= '<li><span class="pager-btn pager-disabled" aria-disabled="true">'
                   . '<i class="bi bi-chevron-left"></i>'
                   . '</span></li>';
        }

        /* Số trang — window ±2 quanh trang hiện tại */
        $start = max(1, $currentPage - 2);
        $end   = min($totalPages, $currentPage + 2);

        if ($start > 1) {
            $html .= '<li><a class="pager-btn" href="'
                   . htmlspecialchars($this->buildUrl($basePath, 1)) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li><span class="pager-ellipsis">…</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i === $currentPage) {
                $html .= '<li><span class="pager-btn pager-active" aria-current="page">'
                       . $i . '</span></li>';
            } else {
                $html .= '<li><a class="pager-btn" href="'
                       . htmlspecialchars($this->buildUrl($basePath, $i)) . '">'
                       . $i . '</a></li>';
            }
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li><span class="pager-ellipsis">…</span></li>';
            }
            $html .= '<li><a class="pager-btn" href="'
                   . htmlspecialchars($this->buildUrl($basePath, $totalPages)) . '">'
                   . $totalPages . '</a></li>';
        }

        /* Nút Next */
        if ($this->hasNext()) {
            $html .= '<li><a class="pager-btn" href="'
                   . htmlspecialchars($this->buildUrl($basePath, $currentPage + 1))
                   . '" aria-label="Trang sau">'
                   . '<i class="bi bi-chevron-right"></i>'
                   . '</a></li>';
        } else {
            $html .= '<li><span class="pager-btn pager-disabled" aria-disabled="true">'
                   . '<i class="bi bi-chevron-right"></i>'
                   . '</span></li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';
        return $html;
    }
}
