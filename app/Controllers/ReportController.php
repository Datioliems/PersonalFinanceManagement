<?php

namespace App\Controllers;

use App\Services\{ReportService, FinanceReport};
use App\Repositories\TransactionRepository;

class ReportController extends BaseController
{
    private ReportService         $reportService;
    private FinanceReport         $financeReport;
    private TransactionRepository $txRepo;

    public function __construct(
        TransactionRepository $txRepo,
        ReportService $reportService,
        FinanceReport $financeReport
    )
    {
        $this->txRepo        = $txRepo;
        $this->reportService = $reportService;
        $this->financeReport = $financeReport;
    }

    public function index(): void
    {
        $uid = $this->currentUserId();

        // ── Xác định kỳ (date range) ─────────────────────────
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        $dateFrom = $_GET['date_from'] ?? $monthStart;
        $dateTo   = $_GET['date_to']   ?? $monthEnd;

        // Validate format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $monthStart;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = $monthEnd;
        if ($dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

        $totalDays = (int)(
            (new \DateTime($dateTo))->diff(new \DateTime($dateFrom))->days
        ) + 1;

        // ── Granularity rules ────────────────────────────────
        // ≤7    → day only              (quá ngắn, tuần/tháng vô nghĩa)
        // 8-31  → day, week             (< 32 ngày: ẩn Tháng)
        // 32-50 → day, week, month      (đủ cả 3)
        // 51-366→ week, month           (> 50 ngày: ẩn Ngày vì quá nhiều điểm)
        // >366  → month only            (> 1 năm: ẩn cả Tuần)
        if ($totalDays <= 7)         $allowedGranularity = ['day'];
        elseif ($totalDays < 32)     $allowedGranularity = ['day', 'week'];
        elseif ($totalDays <= 50)    $allowedGranularity = ['day', 'week', 'month'];
        elseif ($totalDays <= 366)   $allowedGranularity = ['week', 'month'];
        else                         $allowedGranularity = ['month'];

        // Không lưu granularity vào URL nữa — JS xử lý client-side
        // Tab
        $tab     = in_array($_GET['tab'] ?? '', ['overview','category'], true)
            ? $_GET['tab'] : 'overview';
        $catType = ($_GET['cat_type'] ?? 'expense') === 'income' ? 'income' : 'expense';

        // ── Data ─────────────────────────────────────────────
        $summary       = $this->reportService->getSummaryByRange($dateFrom, $dateTo, $uid);
        $walletBalance = $this->reportService->getWalletBalance($dateTo, $uid);

        // Pre-compute ALL granularities cho cả 2 charts (JS dùng không reload)
        $allCharts = [];
        foreach ($allowedGranularity as $g) {
            $allCharts['balance'][$g] = $this->reportService->getBalanceChartData($dateFrom, $dateTo, $uid, $g);
            $allCharts['incExp'][$g]  = $this->reportService->getIncomeExpenseChartData($dateFrom, $dateTo, $uid, $g);
        }
        $allCharts['incomeDonut']  = $this->reportService->getByCategoryByRange($dateFrom, $dateTo, $uid, 'income');
        $allCharts['expenseDonut'] = $this->reportService->getByCategoryByRange($dateFrom, $dateTo, $uid, 'expense');

        // Chi tiết danh mục tab
        $categoryDetail = $tab === 'category'
            ? $this->txRepo->getByCategoryByRange($uid, $dateFrom, $dateTo, $catType)
            : [];

        $this->render('report/index', [
            'summary'            => $summary,
            'walletBalance'      => $walletBalance,
            'allChartsJson'      => json_encode($allCharts, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'allowedGranJson'    => json_encode($allowedGranularity),
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'totalDays'          => $totalDays,
            'allowedGranularity' => $allowedGranularity,
            'tab'                => $tab,
            'catType'            => $catType,
            'categoryDetail'     => $categoryDetail,
            'pageTitle'          => 'Báo cáo tài chính cá nhân',
            'needChartJs'        => true,
        ]);
    }

        /**
         * GET /report/transactions?category_id=X&date_from=Y&date_to=Z&type=income|expense
         * Trả JSON cho popup/chi tiết động.
         */
        public function transactions(): void
        {
        $uid        = $this->currentUserId();
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $dateFrom   = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo     = $_GET['date_to']   ?? date('Y-m-t');
        $type       = in_array($_GET['type'] ?? '', ['income','expense'], true) ? $_GET['type'] : '';

        $rows = $this->txRepo->findFiltered(
            $uid, $type, 'date_desc', $dateFrom, $dateTo, 200, 0, $categoryId
        );

        $out = array_map(fn($r) => [
            'date'          => date('d/m/Y', strtotime($r['trans_date'])),
            'category_name' => $r['category_name'],
            'amount'        => (float)$r['amount'],
            'type'          => $r['type'],
            'note'          => $r['note'] ?? '',
        ], $rows);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function export(): void
    {
        $uid      = $this->currentUserId();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-t');
        $this->financeReport->exportCsvByRange($dateFrom, $dateTo, $uid);
        exit;
    }

    /**
     * AJAX: chi tiet danh muc - khong reload trang
     * GET /report/category-detail?cat_type=expense&date_from=Y&date_to=Z
     */
    public function categoryDetail(): void
    {
        $uid     = $this->currentUserId();
        $catType = ($_GET['cat_type'] ?? 'expense') === 'income' ? 'income' : 'expense';
        $df      = $_GET['date_from'] ?? date('Y-m-01');
        $dt      = $_GET['date_to']   ?? date('Y-m-t');

        $rows = $this->txRepo->getByCategoryByRange($uid, $df, $dt, $catType);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
        exit;
    }
}