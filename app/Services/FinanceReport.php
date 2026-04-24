<?php
// ============================================================
// FINANCE REPORT — app/Services/FinanceReport.php
// ============================================================
// TODO (TV4 Ngày 2 + TV5 Ngày 3): Implement các method
// ============================================================

namespace App\Services;

use App\Repositories\TransactionRepository;

class FinanceReport
{
    public function __construct(
        private TransactionRepository $txRepo
    ) {}

    /**
     * Tổng hợp thu/chi trong tháng.
     *
     * TODO: gọi txRepo->getSummaryByMonth()
     * @return array ['income'=>float, 'expense'=>float, 'balance'=>float]
     */
    public function generateMonthly(int $userId, int $month, int $year): array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV4 Ngày 2');
    }

    /**
     * Chi tiêu theo danh mục — cho biểu đồ tròn Chart.js.
     * @return array [['label'=>..., 'value'=>...], ...]
     */
    public function getByCategory(int $userId, int $month, int $year): array
    {
        // TODO: gọi txRepo->getExpenseByCategory()
        throw new \RuntimeException('Chưa implement — TV5 Ngày 3');
    }

    /**
     * Xu hướng thu/chi theo tuần (4 tuần gần nhất).
     * @return array ['labels'=>[...], 'income'=>[...], 'expense'=>[...]]
     */
    public function getTrend(int $userId, int $weeks = 4): array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV5 Ngày 3');
    }

    /**
     * Xuất CSV toàn bộ giao dịch trong tháng.
     * Headers: Ngày,Loại,Danh mục,Số tiền,Ghi chú
     *
     * TODO:
     *   1. $rows = txRepo->findByMonth(userId, month, year)
     *   2. Dùng fputcsv() với $output = fopen('php://output', 'w')
     *   3. Thêm BOM UTF-8 "\xEF\xBB\xBF" ở đầu để Excel đọc được tiếng Việt
     */
    public function exportCsv(int $userId, int $month, int $year): void
    {
        // TODO: gọi từ ReportController::export()
        // Controller sẽ set header trước khi gọi method này:
        //   header('Content-Type: text/csv; charset=UTF-8');
        //   header('Content-Disposition: attachment; filename="report.csv"');
        throw new \RuntimeException('Chưa implement — TV5 Ngày 6');
    }
}
