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
     * Render HTML phân trang Bootstrap.
     * Dùng trực tiếp trong View.
     */
    public function render(string $basePath): string
    {
        if ($this->getTotalPages() <= 1) {
            return '';
        }

        $html  = '<nav aria-label="Phân trang"><ul class="pagination justify-content-center mb-0">';

        // Nút Trước
        $html .= '<li class="page-item ' . ($this->hasPrevious() ? '' : 'disabled') . '">';
        $html .= '<a class="page-link" href="'
               . ($this->hasPrevious() ? htmlspecialchars($this->buildUrl($basePath, $this->currentPage - 1)) : '#')
               . '">&laquo;</a></li>';

        // Nút trang
        $start = max(1, $this->currentPage - 2);
        $end   = min($this->getTotalPages(), $this->currentPage + 2);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="'
                   . htmlspecialchars($this->buildUrl($basePath, 1)) . '">1</a></li>';
            if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $this->currentPage ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($this->buildUrl($basePath, $i)) . '">' . $i . '</a>';
            $html .= '</li>';
        }

        if ($end < $this->getTotalPages()) {
            if ($end < $this->getTotalPages() - 1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            $html .= '<li class="page-item"><a class="page-link" href="'
                   . htmlspecialchars($this->buildUrl($basePath, $this->getTotalPages()))
                   . '">' . $this->getTotalPages() . '</a></li>';
        }

        // Nút Sau
        $html .= '<li class="page-item ' . ($this->hasNext() ? '' : 'disabled') . '">';
        $html .= '<a class="page-link" href="'
               . ($this->hasNext() ? htmlspecialchars($this->buildUrl($basePath, $this->currentPage + 1)) : '#')
               . '">&raquo;</a></li>';

        $html .= '</ul></nav>';
        return $html;
    }
}
