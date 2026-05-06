<?php
namespace App\Controllers;

use App\Services\{ReportService, FinanceReport};
use App\Repositories\TransactionRepository;

class DashboardController extends BaseController
{
    private ReportService $reportService;
    private FinanceReport $financeReport;

    public function __construct(ReportService $reportService, FinanceReport $financeReport)
    {
        $this->reportService = $reportService;
        $this->financeReport = $financeReport;
    }

    public function index(): void
    {
        $uid   = $this->currentUserId();
        $month = (int)date('n');
        $year  = (int)date('Y');

        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        // Tổng thu/chi tháng này (kỳ cố định = tháng hiện tại)
        $summary = $this->reportService->getSummaryByRange($monthStart, $monthEnd, $uid);

        // Số dư ví lũy kế đến cuối tháng hiện tại
        $walletBalance = $this->reportService->getWalletBalance($monthEnd, $uid);

        // Data cho biểu đồ cột (4 tuần gần nhất)
        $barData = $this->reportService->getTrend(4, $uid);

        // Biểu đồ tròn thu nhập theo tháng hiện tại
        $incomeDonut  = $this->reportService->getByCategoryByRange($monthStart, $monthEnd, $uid, 'income');
        // Biểu đồ tròn chi tiêu theo tháng hiện tại
        $expenseDonut = $this->reportService->getByCategoryByRange($monthStart, $monthEnd, $uid, 'expense');

        $chartJson = json_encode([
            'bar'          => $barData,
            'incomeDonut'  => $incomeDonut,
            'expenseDonut' => $expenseDonut,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $this->render('dashboard/index', [
            'summary'       => $summary,
            'walletBalance' => $walletBalance,
            'chartJson'     => $chartJson,
            'month'         => $month,
            'year'          => $year,
            'pageTitle'     => 'Dashboard',
            'needChartJs'   => true,
        ]);
    }

    /** Xuất CSV tháng hiện tại */
    public function export(): void
    {
        $uid = $this->currentUserId();
        $df  = date('Y-m-01');
        $dt  = date('Y-m-t');
        $this->financeReport->exportCsvDashboard($df, $dt, $uid);
        exit;
    }

    /**
     * AJAX: giao dịch theo category + kỳ (dùng cho donut popup trên dashboard).
     * Kỳ cố định = tháng hiện tại.
     */
    public function transactions(): void
    {
        $uid        = $this->currentUserId();
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $type       = in_array($_GET['type'] ?? '', ['income','expense'], true) ? $_GET['type'] : '';
        $df         = date('Y-m-01');
        $dt         = date('Y-m-t');

        $txRepo = new TransactionRepository();
        $rows   = $txRepo->findFiltered($uid, $type, 'date_desc', $df, $dt, 200, 0, $categoryId);

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
}
