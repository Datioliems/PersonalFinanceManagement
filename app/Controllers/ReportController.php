<?php
// ============================================================
// CONTROLLER — app/Controllers/ReportController.php
// ============================================================
// TV5 viết — Ngày 3–6
//
// Routes:
//   GET /report          → index()   (báo cáo chi tiết)
//   GET /report/export   → export()  (download CSV)
// ============================================================

namespace App\Controllers;

use App\Services\{ReportService, FinanceReport};
use App\Repositories\TransactionRepository;

class ReportController extends BaseController
{
    private ReportService $reportService;
    private FinanceReport $financeReport;

    public function __construct()
    {
        $txRepo              = new TransactionRepository();
        $this->reportService = new ReportService($txRepo);
        $this->financeReport = new FinanceReport($txRepo);
    }

    // ── GET /report ───────────────────────────────────────────
    /**
     * Báo cáo chi tiết — có thể chọn tháng/năm.
     */
    public function index(): void
    {
        $uid   = $this->currentUserId();
        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));

        $summary   = $this->reportService->getSummaryByMonth($month, $year, $uid);
        $donutData = $this->reportService->getByCategory($month, $year, $uid);
        $barData   = $this->reportService->getTrend(4, $uid);

        $chartJson = json_encode(
            ['donut' => $donutData, 'bar' => $barData],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $this->render('report/index', [
            'summary'     => $summary,
            'chartJson'   => $chartJson,
            'month'       => $month,
            'year'        => $year,
            'pageTitle'   => "Báo cáo tháng {$month}/{$year}",
            'needChartJs' => true,
        ]);
    }

    // ── GET /report/export ────────────────────────────────────
    /**
     * Download CSV — stream trực tiếp, không lưu file trên server.
     *
     * HTTP headers phải set trước khi bất kỳ output nào.
     * BOM UTF-8 được set bên trong FinanceReport::exportCsv().
     */
    public function export(): void
    {
        $uid   = $this->currentUserId();
        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));

        $filename = sprintf('bao-cao-%04d-%02d.csv', $year, $month);

        // Set headers trước khi bất kỳ echo nào
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // FinanceReport::exportCsv() stream trực tiếp ra output
        $this->financeReport->exportCsv($month, $year, $uid);
        exit;
    }
}
