<?php
namespace App\Controllers;
use App\Services\FinanceReport;
use App\Repositories\TransactionRepository;

// ============================================================
// REPORT CONTROLLER — TV5 Ngày 3-6
// ============================================================
class ReportController extends BaseController
{
    private FinanceReport $report;

    public function __construct()
    {
        $this->report = new FinanceReport(new TransactionRepository());
    }

    /** GET /report */
    public function index(): void
    {
        $month   = (int) ($_GET['month'] ?? date('n'));
        $year    = (int) ($_GET['year']  ?? date('Y'));
        $uid     = $this->currentUserId();
        $summary    = $this->report->generateMonthly($uid, $month, $year);
        $byCategory = $this->report->getByCategory($uid, $month, $year);
        $trend      = $this->report->getTrend($uid, 4);
        $this->render('report/index', compact('summary','byCategory','trend','month','year'));
    }

    /** GET /report/export — Download CSV */
    public function export(): void
    {
        $month = (int) ($_GET['month'] ?? date('n'));
        $year  = (int) ($_GET['year']  ?? date('Y'));
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bao-cao-' . $year . '-' . $month . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->report->exportCsv($this->currentUserId(), $month, $year);
    }
}
