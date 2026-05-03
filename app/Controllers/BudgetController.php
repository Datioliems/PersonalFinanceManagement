<?php
// ============================================================
// CONTROLLER — app/Controllers/budgetController.php
// ============================================================
// TV2 viết — Ngày 3
//
// Routes:
//   GET  /budget          → index()
//   POST /budget          → setLimit()
//   POST /budget/{id}/delete → destroy()
// ============================================================

namespace App\Controllers;

use App\Services\BudgetService;
use App\Repositories\{BudgetRepository, CategoryRepository, TransactionRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage};

class BudgetController extends BaseController
{
    private BudgetService      $budgetService;
    private CategoryRepository $catRepo;

    public function __construct()
    {
        // Dependency Injection thủ công
        $this->catRepo       = new CategoryRepository();
        $this->budgetService = new BudgetService(
            new BudgetRepository(),
            new TransactionRepository()
        );
    }

    // ── GET /budget ───────────────────────────────────────────
    /**
     * Hiển thị danh sách budget tháng hiện tại + % đã dùng.
     * Truyền vào View: summary (budget + spent + pct + status_class)
     */
    public function index(): void
    {
        $uid   = $this->currentUserId();
        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));

        // Gọi Service — không gọi Repository trực tiếp
        $summary = $this->budgetService->getBudgetSummary($uid, $month, $year);
        $cats    = $this->catRepo->findByUser($uid);
        $csrf    = CsrfTokenManager::generate();

        $this->render('budget/index', [
            'summary'   => $summary,
            'cats'      => $cats,
            'csrf'      => $csrf,
            'month'     => $month,
            'year'      => $year,
            'pageTitle' => 'Ngân sách tháng ' . $month . '/' . $year,
        ]);
    }

    // ── POST /budget ──────────────────────────────────────────
    /**
     * Đặt hoặc cập nhật hạn mức ngân sách.
     */
    public function setLimit(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/budget');
        }
        CsrfTokenManager::invalidate();

        $uid         = $this->currentUserId();
        $categoryId  = (int)($_POST['category_id']     ?? 0);
        $limitAmount = (float)($_POST['limit_amount']   ?? 0);
        $threshold   = (int)($_POST['alert_threshold']  ?? 80);
        $month       = (int)($_POST['month']            ?? date('n'));
        $year        = (int)($_POST['year']             ?? date('Y'));

        // Validate
        if ($categoryId <= 0) {
            FlashMessage::set('danger', 'Vui lòng chọn danh mục.');
            $this->redirect('/budget');
        }

        try {
            $this->budgetService->setLimit($uid, $categoryId, $limitAmount, $month, $year, $threshold);
            FlashMessage::set('success', 'Đã cập nhật ngân sách.');
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }

        $this->redirect('/budget?month=' . $month . '&year=' . $year);
    }

    // ── POST /budget/{id}/delete ──────────────────────────────
    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/budget');
        }
        CsrfTokenManager::invalidate();

        $repo    = new BudgetRepository();
        $deleted = $repo->deleteByIdAndUser((int)$id, $this->currentUserId());

        FlashMessage::set(
            $deleted ? 'success' : 'warning',
            $deleted ? 'Đã xoá hạn mức ngân sách.' : 'Không tìm thấy hoặc bạn không có quyền xoá.'
        );
        $this->redirect('/budget');
    }
}
