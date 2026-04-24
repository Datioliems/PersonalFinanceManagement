<?php
namespace App\Controllers;
use App\Services\FinanceReport;
use App\Repositories\TransactionRepository;

// ============================================================
// DASHBOARD CONTROLLER — TV5 Ngày 4
// ============================================================
class DashboardController extends BaseController
{
    private FinanceReport $report;

    public function __construct()
    {
        $this->report = new FinanceReport(new TransactionRepository());
    }

    /** GET / và GET /dashboard */
    public function index(): void
    {
        $uid     = $this->currentUserId();
        $month   = (int) date('n');
        $year    = (int) date('Y');
        // TODO: lấy dữ liệu cho Chart.js
        $summary    = $this->report->generateMonthly($uid, $month, $year);
        $byCategory = $this->report->getByCategory($uid, $month, $year);
        $trend      = $this->report->getTrend($uid, 4);
        // Encode sẵn cho Chart.js trong View
        $chartData  = json_encode([
            'summary'    => $summary,
            'byCategory' => $byCategory,
            'trend'      => $trend,
        ], JSON_UNESCAPED_UNICODE);
        $this->render('report/dashboard', compact('summary','chartData','month','year'));
    }
}
