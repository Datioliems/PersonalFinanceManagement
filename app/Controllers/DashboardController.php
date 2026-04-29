<?php
// ============================================================
// CONTROLLER — app/Controllers/DashboardController.php
// ============================================================
// TV5 viết — Ngày 4
//
// Tổng hợp dữ liệu cho trang chủ sau khi đăng nhập.
// Dùng ReportService để lấy data — không query DB trực tiếp.
// ============================================================

namespace App\Controllers;

use App\Services\{ReportService, FinanceReport};
use App\Repositories\TransactionRepository;

class DashboardController extends BaseController
{
    private ReportService $reportService;
    private FinanceReport $financeReport;

    public function __construct()
    {
        $txRepo              = new TransactionRepository();
        $this->reportService = new ReportService($txRepo);
        $this->financeReport = new FinanceReport($txRepo);
    }

    // ── GET /dashboard ────────────────────────────────────────
    /**
     * Dashboard chính — tổng quan + 2 biểu đồ Chart.js.
     *
     * Data truyền vào Chart.js qua json_encode() thay vì AJAX
     * vì đơn giản hơn và không cần thêm API endpoint.
     * json_encode() với JSON_UNESCAPED_UNICODE để tiếng Việt đúng.
     */
    public function index(): void
    {
        $uid   = $this->currentUserId();
        $month = (int)date('n');
        $year  = (int)date('Y');

        // Tổng thu/chi tháng này
        $summary = $this->reportService->getSummaryByMonth($month, $year, $uid);

        // Data cho biểu đồ tròn (chi theo danh mục)
        $donutData = $this->reportService->getByCategory($month, $year, $uid);

        // Data cho biểu đồ cột (4 tuần gần nhất)
        $barData = $this->reportService->getTrend(4, $uid);

        // Encode JSON một lần — tránh gọi json_encode nhiều lần trong View
        $chartJson = json_encode(
            ['donut' => $donutData, 'bar' => $barData],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $this->render('dashboard/index', [
            'summary'   => $summary,
            'chartJson' => $chartJson,
            'month'     => $month,
            'year'      => $year,
            'pageTitle' => 'Dashboard',
            'needChartJs' => true,
        ]);
    }
}
