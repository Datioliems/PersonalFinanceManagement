<?php
// ============================================================
// SERVICE — app/Services/ReportService.php
// ============================================================
// Tổng hợp dữ liệu cho Dashboard và báo cáo.
// TV5 viết — Ngày 3
//
// Cung cấp data đúng format cho Chart.js:
//   Biểu đồ cột (bar): { labels, datasets: [{data}, {data}] }
//   Biểu đồ tròn (doughnut): { labels, datasets: [{data, backgroundColor}] }
// ============================================================

namespace App\Services;

use App\Repositories\TransactionRepository;

class ReportService
{
    public function __construct(
        private TransactionRepository $txRepo
    ) {}

    // ── getSummaryByMonth() ───────────────────────────────────
    /**
     * Tổng hợp thu/chi/balance trong tháng.
     * Dùng SQL GROUP BY — không tính bằng PHP array_sum.
     *
     * @return array ['income'=>float, 'expense'=>float, 'balance'=>float]
     */
    public function getSummaryByMonth(int $month, int $year, int $userId): array
    {
        $raw     = $this->txRepo->getSummaryByMonth($userId, $month, $year);
        $income  = (float)($raw['income']  ?? 0);
        $expense = (float)($raw['expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    // ── getByCategory() ───────────────────────────────────────
    /**
     * Chi tiêu theo danh mục — cho biểu đồ tròn Chart.js.
     *
     * @return array format Chart.js doughnut:
     * {
     *   "labels":  ["Ăn uống", "Đi lại"],
     *   "datasets": [{
     *     "data":            [500000, 200000],
     *     "backgroundColor": ["#FF6384", "#36A2EB"]
     *   }]
     * }
     */
    public function getByCategory(int $month, int $year, int $userId): array
    {
        $rows   = $this->txRepo->getExpenseByCategory($userId, $month, $year);
        $labels = [];
        $data   = [];
        $colors = [];

        $defaults = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF'];
        $i = 0;
        foreach ($rows as $row) {
            $labels[] = $row['category_name'];
            $data[]   = (float)$row['total'];
            $colors[] = $row['color'] ?: $defaults[$i % count($defaults)];
            $i++;
        }

        return [
            'labels'   => $labels,
            'datasets' => [[
                'data'            => $data,
                'backgroundColor' => $colors,
                'hoverOffset'     => 6,
            ]],
        ];
    }

    // ── getTrend() ────────────────────────────────────────────
    /**
     * Thu/chi theo tuần — cho biểu đồ cột Chart.js.
     *
     * @return array format Chart.js bar:
     * {
     *   "labels":   ["Tuần 17", "Tuần 18", ...],
     *   "datasets": [
     *     {"label": "Thu nhập", "data": [...], "backgroundColor": "#22c55e"},
     *     {"label": "Chi tiêu", "data": [...], "backgroundColor": "#ef4444"}
     *   ]
     * }
     */
    public function getTrend(int $weeks, int $userId): array
    {
        $labels  = [];
        $income  = [];
        $expense = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = new \DateTime("-{$i} weeks monday this week");
            $end   = new \DateTime("-{$i} weeks sunday this week");

            $rows = $this->txRepo->findByDateRange($userId, $start, $end);

            $inc = 0.0;
            $exp = 0.0;
            foreach ($rows as $row) {
                if ($row['type'] === 'income') $inc += (float)$row['amount'];
                else                           $exp += (float)$row['amount'];
            }

            $labels[]  = 'Tuần ' . $start->format('W');
            $income[]  = $inc;
            $expense[] = $exp;
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Thu nhập',
                    'data'            => $income,
                    'backgroundColor' => '#22c55e',
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Chi tiêu',
                    'data'            => $expense,
                    'backgroundColor' => '#ef4444',
                    'borderRadius'    => 4,
                ],
            ],
        ];
    }
}
