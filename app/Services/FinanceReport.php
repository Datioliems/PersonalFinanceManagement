<?php
// ============================================================
// SERVICE — app/Services/FinanceReport.php
// ============================================================
// TV4 viết — Ngày 2.
// TV5 dùng lại generateMonthly() và getByCategory() cho Dashboard.
//
// Design: Dependency Injection — TransactionRepository inject
// qua constructor, không new bên trong method.
//
// Câu hỏi demo: "Tại sao dùng SQL GROUP BY thay vì PHP loop?"
// → SQL tính trên DB server (nhanh hơn, ít bộ nhớ PHP).
// ============================================================

namespace App\Services;

use App\Repositories\TransactionRepository;

class FinanceReport
{
    public function __construct(
        private TransactionRepository $repo
    ) {}

    // ── generateMonthly() ────────────────────────────────────
    /**
     * Tổng hợp thu/chi trong tháng.
     * Dùng SQL GROUP BY — không tính bằng PHP array_sum.
     *
     * @return array [
     *   'income'  => float,
     *   'expense' => float,
     *   'balance' => float,  // income - expense
     *   'month'   => int,
     *   'year'    => int,
     * ]
     */
    public function generateMonthly(int $month, int $year, int $userId): array
    {
        $summary = $this->repo->getSummaryByMonth($userId, $month, $year);

        $income  = (float)($summary['income']  ?? 0);
        $expense = (float)($summary['expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'month'   => $month,
            'year'    => $year,
        ];
    }

    /**
     * Tổng hợp thu/chi trong khoảng thời gian.
     */
    public function generateRange(string $startDate, string $endDate, int $userId): array
    {
        $summary = $this->repo->getSummaryByRange($userId, $startDate, $endDate);

        $income  = (float)($summary['income']  ?? 0);
        $expense = (float)($summary['expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    // ── getByCategory() ──────────────────────────────────────
    /**
     * Chi tiêu theo từng danh mục trong tháng — cho biểu đồ tròn.
     *
     * @return array [
     *   'labels' => ['Ăn uống', 'Đi lại', ...],
     *   'data'   => [500000, 200000, ...],
     *   'colors' => ['#FF6384', '#36A2EB', ...],
     * ]
     */
    public function getByCategory(int $month, int $year, int $userId): array
    {
        $rows   = $this->repo->getExpenseByCategory($userId, $month, $year);
        $labels = [];
        $data   = [];
        $colors = [];

        // Màu mặc định nếu danh mục không có màu
        $defaultColors = [
            '#FF6384','#36A2EB','#FFCE56','#4BC0C0',
            '#9966FF','#FF9F40','#C9CBCF','#4BC0C0',
        ];
        $i = 0;

        foreach ($rows as $row) {
            $labels[] = $row['category_name'];
            $data[]   = (float)$row['total'];
            $colors[] = $row['color'] ?: ($defaultColors[$i % count($defaultColors)]);
            $i++;
        }

        return compact('labels', 'data', 'colors');
    }

    // ── getTrend() ────────────────────────────────────────────
    /**
     * Xu hướng thu/chi theo tuần — cho biểu đồ cột.
     * Lấy N tuần gần nhất tính từ hôm nay.
     *
     * @return array [
     *   'labels'  => ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
     *   'income'  => [float, ...],
     *   'expense' => [float, ...],
     * ]
     */
    public function getTrend(int $weeks, int $userId): array
    {
        $labels  = [];
        $income  = [];
        $expense = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            // Tính ngày đầu/cuối tuần
            $start = new \DateTime("-{$i} weeks monday this week");
            $end   = new \DateTime("-{$i} weeks sunday this week");

            $rows = $this->repo->findByDateRange($userId, $start, $end);

            $inc = 0.0;
            $exp = 0.0;
            foreach ($rows as $row) {
                if ($row['type'] === 'income') {
                    $inc += (float)$row['amount'];
                } else {
                    $exp += (float)$row['amount'];
                }
            }

            $weekNum  = $start->format('W');
            $labels[] = 'Tuần ' . $weekNum;
            $income[] = $inc;
            $expense[]= $exp;
        }

        return compact('labels', 'income', 'expense');
    }

    // ── exportCsv() ───────────────────────────────────────────
    /**
     * Xuất CSV — stream trực tiếp ra output (không return string).
     * Controller phải set HTTP headers trước khi gọi method này.
     *
     * BOM UTF-8 (\xEF\xBB\xBF) đặt đầu file để Excel đọc được tiếng Việt.
     * fputcsv() tự xử lý dấu phẩy trong nội dung (wrap trong dấu ngoặc kép).
     *
     * @param int $month
     * @param int $year
     * @param int $userId
     */
    public function exportCsv(int $month, int $year, int $userId): void
    {
        $rows = $this->repo->findByMonth($userId, $month, $year);

        // BOM UTF-8 — cần thiết để Excel hiển thị đúng tiếng Việt
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, ['Ngày', 'Loại', 'Danh mục', 'Số tiền (đ)', 'Ghi chú']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['trans_date'],
                $row['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu',
                $row['category_name'],
                number_format((float)$row['amount'], 0, ',', '.'),
                $row['note'] ?? '',
            ]);
        }

        fclose($output);
    }
    /**
     * Xuất CSV theo kỳ chọn (dùng cho trang Báo cáo).
     * Format: 1 dòng header, các dòng giao dịch — giống file mẫu.
     * Tên file: Bao-cao-YYYY-MM-DD_YYYY-MM-DD.csv
     */
    public function exportCsvByRange(string $dateFrom, string $dateTo, int $userId): void
    {
        $rows     = $this->repo->findFiltered($userId, '', 'date_asc', $dateFrom, $dateTo, 9999, 0, 0);
        $filename = 'Bao-cao-'.$dateFrom.'_'.$dateTo.'.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: no-cache');
        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $out = fopen('php://output', 'w');
        // Dòng header: in đậm căn giữa (theo yêu cầu ảnh 1)
        fputcsv($out, ['Ngày', 'Loại', 'Danh mục', 'Số tiền (đ)', 'Ghi chú']);
        foreach ($rows as $row) {
            fputcsv($out, [
                date('d/m/Y', strtotime($row['trans_date'])), // d/m/Y tránh ### trong Excel
                $row['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu',
                $row['category_name'] ?? '',
                (int)round((float)$row['amount']),            // số nguyên, tránh mất số 0
                $row['note'] ?? '',
            ]);
        }
        fclose($out);

    }

    /**
     * Xuất CSV tháng hiện tại (dùng cho Dashboard).
     * Tên file: Bao-cao-YYYY-thang-MM.csv
     */
    public function exportCsvDashboard(string $dateFrom, string $dateTo, int $userId): void
    {
        $rows     = $this->repo->findFiltered($userId, '', 'date_asc', $dateFrom, $dateTo, 9999, 0, 0);
        $month    = date('m', strtotime($dateFrom));
        $year     = date('Y', strtotime($dateFrom));
        $filename = 'Bao-cao-'.$year.'-thang-'.$month.'.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: no-cache');
        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ngày', 'Loại', 'Danh mục', 'Số tiền (đ)', 'Ghi chú']);
        foreach ($rows as $row) {
            fputcsv($out, [
                date('d/m/Y', strtotime($row['trans_date'])),
                $row['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu',
                $row['category_name'] ?? '',
                (int)round((float)$row['amount']),
                $row['note'] ?? '',
            ]);
        }
        fclose($out);

    }
}
