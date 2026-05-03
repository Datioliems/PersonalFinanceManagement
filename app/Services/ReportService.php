<?php
// ============================================================
// SERVICE — app/Services/ReportService.php (v2)
// ============================================================
// Cập nhật:
//   - getTrend(): tính theo 7 ngày trượt (không dùng "monday this week")
//   - getByPeriod(): hỗ trợ theo ngày / tuần / tháng / năm
//   - getLineData(): data cho line chart theo ngày trong tháng
// ============================================================

namespace App\Services;

use App\Repositories\TransactionRepository;

class ReportService
{
    public function __construct(
        private TransactionRepository $txRepo
    ) {}

    // ── Tổng hợp tháng ────────────────────────────────────────

    public function getSummaryByMonth(int $month, int $year, int $userId): array
    {
        $raw     = $this->txRepo->getSummaryByMonth($userId, $month, $year);
        $income  = (float)($raw['income']  ?? 0);
        $expense = (float)($raw['expense'] ?? 0);
        return ['income' => $income, 'expense' => $expense, 'balance' => $income - $expense];
    }

    // ── Biểu đồ tròn: chi theo danh mục ──────────────────────

    public function getByCategory(int $month, int $year, int $userId): array
    {
        $rows     = $this->txRepo->getExpenseByCategory($userId, $month, $year);
        $labels   = [];
        $data     = [];
        $colors   = [];
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

    // ── Biểu đồ cột/line: xu hướng theo N tuần ───────────────
    /**
     * FIX: tính ngược từ hôm nay theo ngày thực tế (không dùng
     * "monday this week" — sẽ bỏ qua giao dịch đầu tuần/tháng)
     */
    public function getTrend(int $weeks, int $userId): array
    {
        $labels  = [];
        $income  = [];
        $expense = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $endTs   = strtotime('today') - ($i * 7 * 86400);
            $startTs = $endTs - (6 * 86400);

            $start = new \DateTime(date('Y-m-d', $startTs));
            $end   = new \DateTime(date('Y-m-d', $endTs));

            $rows = $this->txRepo->findByDateRange($userId, $start, $end);
            $inc  = 0.0;
            $exp  = 0.0;
            foreach ($rows as $row) {
                if ($row['type'] === 'income') $inc += (float)$row['amount'];
                else                           $exp += (float)$row['amount'];
            }

            $labels[]  = $start->format('d/m') . '–' . $end->format('d/m');
            $income[]  = $inc;
            $expense[] = $exp;
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Thu nhập', 'data' => $income,  'backgroundColor' => '#22c55e', 'borderColor' => '#22c55e', 'borderRadius' => 4, 'tension' => 0.3],
                ['label' => 'Chi tiêu', 'data' => $expense, 'backgroundColor' => '#ef4444', 'borderColor' => '#ef4444', 'borderRadius' => 4, 'tension' => 0.3],
            ],
        ];
    }

    // ── Báo cáo theo ngày trong tháng ─────────────────────────
    /**
     * Trả data cho line chart: tổng thu/chi từng ngày trong tháng.
     * Chỉ trả những ngày có giao dịch.
     */
    public function getLineByDay(int $month, int $year, int $userId): array
    {
        $start = new \DateTime(sprintf('%04d-%02d-01', $year, $month));
        $end   = new \DateTime($start->format('Y-m-t'));

        $rows = $this->txRepo->findByDateRange($userId, $start, $end);

        // Gom theo ngày
        $byDay = [];
        foreach ($rows as $row) {
            $d = $row['trans_date'];
            if (!isset($byDay[$d])) $byDay[$d] = ['income' => 0.0, 'expense' => 0.0];
            if ($row['type'] === 'income') $byDay[$d]['income'] += (float)$row['amount'];
            else                           $byDay[$d]['expense'] += (float)$row['amount'];
        }
        ksort($byDay);

        $labels  = [];
        $income  = [];
        $expense = [];
        foreach ($byDay as $date => $val) {
            $labels[]  = date('d/m', strtotime($date));
            $income[]  = $val['income'];
            $expense[] = $val['expense'];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Thu nhập', 'data' => $income,  'borderColor' => '#22c55e', 'backgroundColor' => '#22c55e22', 'tension' => 0.4, 'fill' => true, 'pointRadius' => 4],
                ['label' => 'Chi tiêu', 'data' => $expense, 'borderColor' => '#ef4444', 'backgroundColor' => '#ef444422', 'tension' => 0.4, 'fill' => true, 'pointRadius' => 4],
            ],
        ];
    }

    // ── Báo cáo theo tháng trong năm ──────────────────────────
    public function getLineByMonth(int $year, int $userId): array
    {
        $labels  = [];
        $income  = [];
        $expense = [];
        for ($m = 1; $m <= 12; $m++) {
            $raw = $this->txRepo->getSummaryByMonth($userId, $m, $year);
            $labels[]  = "T{$m}";
            $income[]  = (float)($raw['income']  ?? 0);
            $expense[] = (float)($raw['expense'] ?? 0);
        }
        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Thu nhập', 'data' => $income,  'borderColor' => '#22c55e', 'backgroundColor' => '#22c55e22', 'tension' => 0.4, 'fill' => true, 'pointRadius' => 4],
                ['label' => 'Chi tiêu', 'data' => $expense, 'borderColor' => '#ef4444', 'backgroundColor' => '#ef444422', 'tension' => 0.4, 'fill' => true, 'pointRadius' => 4],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════
    // BÁO CÁO NÂNG CAO — MỚI
    // ══════════════════════════════════════════════════════════

    /**
     * Chi tiết thu hoặc chi theo từng danh mục trong tháng.
     * Trả thêm: tx_count, avg_amount, min_amount, max_amount.
     *
     * @param string $type 'income' | 'expense'
     */
    public function getDetailByCategory(int $month, int $year, int $userId, string $type='expense'): array
    {
        return $this->txRepo->getDetailByCategory($userId, $month, $year, $type);
    }

    /**
     * Tổng hợp cả năm — cho biểu đồ doanh thu theo năm.
     * @return array Chart.js bar/line data với 12 tháng
     */
    public function getYearlyChart(int $year, int $userId): array
    {
        $rows    = $this->txRepo->getYearlySummary($userId, $year);
        $labels  = [];
        $income  = [];
        $expense = [];
        $balance = [];

        foreach ($rows as $r) {
            $labels[]  = 'T' . $r['month'];
            $income[]  = $r['income'];
            $expense[] = $r['expense'];
            $balance[] = $r['balance'];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label'=>'Thu nhập', 'data'=>$income,  'backgroundColor'=>'#22c55e', 'borderColor'=>'#22c55e', 'borderRadius'=>4, 'tension'=>0.3],
                ['label'=>'Chi tiêu', 'data'=>$expense, 'backgroundColor'=>'#ef4444', 'borderColor'=>'#ef4444', 'borderRadius'=>4, 'tension'=>0.3],
                ['label'=>'Số dư',    'data'=>$balance, 'backgroundColor'=>'#3b82f6', 'borderColor'=>'#3b82f6',
                 'type'=>'line', 'borderWidth'=>2, 'fill'=>false, 'tension'=>0.3, 'pointRadius'=>4],
            ],
        ];
    }

    /**
     * Tổng hợp cả năm dạng bảng (raw data).
     */
    public function getYearlyRaw(int $year, int $userId): array
    {
        return $this->txRepo->getYearlySummary($userId, $year);
    }

    /**
     * So sánh các tháng được chọn — cho biểu đồ grouped bar.
     *
     * @param int[] $months
     */
    public function getMonthlyComparison(int $year, int $userId, array $months): array
    {
        if (empty($months)) {
            // Mặc định so sánh 3 tháng gần nhất
            $curMonth = (int)date('n');
            $months = [];
            for ($i = 2; $i >= 0; $i--) {
                $m = $curMonth - $i;
                if ($m <= 0) $m += 12;
                $months[] = $m;
            }
        }

        $raw = $this->txRepo->getMonthlyComparison($userId, $months, $year);

        return [
            'labels'   => $raw['labels'],
            'datasets' => [
                ['label'=>'Thu nhập', 'data'=>$raw['income'],  'backgroundColor'=>'#22c55e', 'borderRadius'=>4],
                ['label'=>'Chi tiêu', 'data'=>$raw['expense'], 'backgroundColor'=>'#ef4444', 'borderRadius'=>4],
                ['label'=>'Số dư',    'data'=>array_map(
                    fn($i,$e) => $i-$e,
                    $raw['income'], $raw['expense']
                ), 'backgroundColor'=>'#3b82f6', 'borderRadius'=>4],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════
    // BÁO CÁO THEO KỲ TÙY CHỌN (date-range) — MỚI
    // ══════════════════════════════════════════════════════════

    /**
     * Tổng hợp thu/chi trong kỳ + số dư kỳ.
     */
    public function getSummaryByRange(string $dateFrom, string $dateTo, int $userId): array
    {
        $raw     = $this->txRepo->getSummaryByDateRange($userId, $dateFrom, $dateTo);
        $income  = (float)($raw['income']  ?? 0);
        $expense = (float)($raw['expense'] ?? 0);
        return [
            'income'        => $income,
            'expense'       => $expense,
            'period_balance' => $income - $expense,
        ];
    }

    /**
     * Số dư ví lũy kế từ đầu đến ngày cuối kỳ.
     */
    public function getWalletBalance(string $dateTo, int $userId): float
    {
        return $this->txRepo->getWalletBalanceUpTo($userId, $dateTo);
    }

    /**
     * Biểu đồ tròn: thu hoặc chi theo danh mục trong kỳ.
     * @param string $type 'income' | 'expense'
     */
    public function getByCategoryByRange(string $dateFrom, string $dateTo, int $userId, string $type): array
    {
        $rows     = $this->txRepo->getByCategoryByRange($userId, $dateFrom, $dateTo, $type);
        $labels   = []; $data = []; $colors = []; $txCounts = []; $catIds = [];
        $defaults = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF'];
        $i = 0;
        foreach ($rows as $row) {
            $labels[]   = $row['category_name'];
            $data[]     = (float)$row['total'];
            $colors[]   = $row['color'] ?: $defaults[$i % count($defaults)];
            $txCounts[] = (int)$row['tx_count'];
            $catIds[]   = (int)($row['category_id'] ?? 0);
            $i++;
        }
        return [
            'labels'      => $labels,
            'txCounts'    => $txCounts,
            'categoryIds' => $catIds,
            'datasets'    => [[
                'data'            => $data,
                'backgroundColor' => $colors,
                'hoverOffset'     => 6,
                'borderWidth'     => 2,
                'borderColor'     => '#fff',
            ]],
        ];
    }

    // ── Helper: enumerate từng tuần Mon-Sun trong kỳ ──────────────
    /**
     * Trả array các tuần thực sự Mon–Sun trong khoảng dateFrom–dateTo.
     * Mỗi phần tử: ['start'=>'Y-m-d', 'end'=>'Y-m-d', 'label'=>'dd/mm–dd/mm']
     */
    private function enumerateWeeks(string $dateFrom, string $dateTo): array
    {
        // Tìm ngày Thứ 2 của tuần chứa dateFrom
        $monday = new \DateTime($dateFrom);
        $dow = (int)$monday->format('N'); // 1=Mon, 7=Sun
        if ($dow > 1) $monday->modify('-' . ($dow - 1) . ' days');

        $end = new \DateTime($dateTo);
        $weeks = [];
        $cursor = clone $monday;

        while ($cursor <= $end) {
            $wStart = clone $cursor;
            $cursor->modify('+6 days');
            $wEnd   = clone $cursor;

            $weeks[] = [
                'start' => $wStart->format('Y-m-d'),
                'end'   => $wEnd->format('Y-m-d'),
                'label' => $wStart->format('d/m') . '–' . $wEnd->format('d/m'),
            ];
            $cursor->modify('+1 day');
        }
        return $weeks;
    }

    /**
     * Dữ liệu biểu đồ đường Số dư tài khoản (lũy kế rolling).
     * Granularity: 'day' | 'week' | 'month'
     */
    public function getBalanceChartData(string $dateFrom, string $dateTo, int $userId, string $granularity): array
    {
        $runningBalance = $this->txRepo->getBalanceBeforeDate($userId, $dateFrom);

        if ($granularity === 'day') {
            $daily = $this->txRepo->getDailyTotals($userId, $dateFrom, $dateTo);
            $labels = $balData = [];
            $cur = strtotime($dateFrom);
            $end = strtotime($dateTo);
            while ($cur <= $end) {
                $d = date('Y-m-d', $cur);
                $dayData = $daily[$d] ?? ['income' => 0, 'expense' => 0];
                $runningBalance += $dayData['income'] - $dayData['expense'];
                $labels[]  = date('d/m', $cur);
                $balData[] = round($runningBalance, 2);
                $cur = strtotime('+1 day', $cur);
            }
        } elseif ($granularity === 'week') {
            // Weeks Mon–Sun thực sự
            $daily   = $this->txRepo->getDailyTotals($userId, $dateFrom, $dateTo);
            $weeks   = $this->enumerateWeeks($dateFrom, $dateTo);
            $labels  = $balData = [];
            foreach ($weeks as $w) {
                $weekInc = $weekExp = 0.0;
                $cur = strtotime($w['start']);
                $wEnd = strtotime($w['end']);
                while ($cur <= $wEnd) {
                    $d = date('Y-m-d', $cur);
                    $weekInc += $daily[$d]['income']  ?? 0;
                    $weekExp += $daily[$d]['expense'] ?? 0;
                    $cur = strtotime('+1 day', $cur);
                }
                $runningBalance += $weekInc - $weekExp;
                $labels[]  = $w['label'];
                $balData[] = round($runningBalance, 2);
            }
        } else { // month — enumerate ALL months in range
            $monthly = $this->txRepo->getMonthlyTotals($userId, $dateFrom, $dateTo);
            $labels  = $balData = [];
            $curDt = new \DateTime($dateFrom);
            $curDt->modify('first day of this month');
            $endDt = new \DateTime($dateTo);
            $endDt->modify('first day of this month');
            while ($curDt <= $endDt) {
                $ym  = $curDt->format('Y-m');
                $row = $monthly[$ym] ?? ['income' => 0, 'expense' => 0];
                $runningBalance += $row['income'] - $row['expense'];
                $y = $curDt->format('Y'); $m = (int)$curDt->format('m');
                $labels[]  = 'T'.$m.($y != date('Y') ? '/'.$curDt->format('y') : '');
                $balData[] = round($runningBalance, 2);
                $curDt->modify('+1 month');
            }
        }

        return [
            'labels'   => $labels ?? [],
            'datasets' => [[
                'label'            => 'Số dư',
                'data'             => $balData ?? [],
                'borderColor'      => '#22c55e',
                'backgroundColor'  => '#22c55e18',
                'tension'          => 0.3,
                'fill'             => true,
                'pointRadius'      => 4,
                'pointHoverRadius' => 6,
            ]],
        ];
    }

    /**
     * Dữ liệu biểu đồ cột Thống kê Thu & Chi (theo ngày/tuần/tháng).
     */
    public function getIncomeExpenseChartData(string $dateFrom, string $dateTo, int $userId, string $granularity): array
    {
        if ($granularity === 'day') {
            $raw = $this->txRepo->getDailyTotals($userId, $dateFrom, $dateTo);
            $labels = $income = $expense = [];
            $cur = strtotime($dateFrom);
            $end = strtotime($dateTo);
            while ($cur <= $end) {
                $d   = date('Y-m-d', $cur);
                $day = $raw[$d] ?? ['income' => 0, 'expense' => 0];
                $labels[]  = date('d/m', $cur);
                $income[]  = $day['income'];
                $expense[] = $day['expense'];
                $cur = strtotime('+1 day', $cur);
            }
        } elseif ($granularity === 'week') {
            // Weeks Mon–Sun thực sự
            $daily   = $this->txRepo->getDailyTotals($userId, $dateFrom, $dateTo);
            $weeks   = $this->enumerateWeeks($dateFrom, $dateTo);
            $labels  = $income = $expense = [];
            foreach ($weeks as $w) {
                $weekInc = $weekExp = 0.0;
                $cur  = strtotime($w['start']);
                $wEnd = strtotime($w['end']);
                while ($cur <= $wEnd) {
                    $d = date('Y-m-d', $cur);
                    $weekInc += $daily[$d]['income']  ?? 0;
                    $weekExp += $daily[$d]['expense'] ?? 0;
                    $cur = strtotime('+1 day', $cur);
                }
                $labels[]  = $w['label'];
                $income[]  = $weekInc;
                $expense[] = $weekExp;
            }
        } else { // month — enumerate ALL months in range
            $raw    = $this->txRepo->getMonthlyTotals($userId, $dateFrom, $dateTo);
            $labels = $income = $expense = [];
            $curDt = new \DateTime($dateFrom);
            $curDt->modify('first day of this month');
            $endDt = new \DateTime($dateTo);
            $endDt->modify('first day of this month');
            while ($curDt <= $endDt) {
                $ym  = $curDt->format('Y-m');
                $row = $raw[$ym] ?? ['income' => 0, 'expense' => 0];
                $y   = $curDt->format('Y'); $m = (int)$curDt->format('m');
                $labels[]  = 'T'.$m.($y != date('Y') ? '/'.$curDt->format('y') : '');
                $income[]  = $row['income'];
                $expense[] = $row['expense'];
                $curDt->modify('+1 month');
            }
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Thu nhập', 'data' => $income,  'backgroundColor' => '#22c55e', 'borderColor' => '#22c55e', 'borderRadius' => 4, 'tension' => 0.3],
                ['label' => 'Chi tiêu',  'data' => $expense, 'backgroundColor' => '#ef4444', 'borderColor' => '#ef4444', 'borderRadius' => 4, 'tension' => 0.3],
            ],
        ];
    }
}