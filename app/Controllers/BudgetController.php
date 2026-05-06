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
use App\Repositories\{BudgetRepository, CategoryRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage};

class BudgetController extends BaseController
{
    private BudgetService      $budgetService;
    private CategoryRepository $catRepo;
    private BudgetRepository $budgetRepo;

    public function __construct(
        CategoryRepository $catRepo,
        BudgetService $budgetService,
        BudgetRepository $budgetRepo
    )
    {
        $this->catRepo       = $catRepo;
        $this->budgetService = $budgetService;
        $this->budgetRepo    = $budgetRepo;
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
        $page  = max(1, (int)($_GET['page'] ?? 1));

        // Gọi Service — không gọi Repository trực tiếp
        $allSummary = $this->budgetService->getBudgetSummary($uid, $month, $year);
        $cats       = $this->catRepo->findByUser($uid);
        $csrf       = CsrfTokenManager::generate();

        // Phân trang — 8 mục/trang
        $perPage = 8;
        $pager   = new \App\Helpers\Paginator(count($allSummary), $perPage, $page);
        $summary = array_slice($allSummary, $pager->getOffset(), $perPage);

        $this->render('budget/index', [
            'summary'    => $summary,
            'allSummary' => $allSummary,
            'cats'       => $cats,
            'csrf'       => $csrf,
            'month'      => $month,
            'year'       => $year,
            'pager'      => $pager,
            'pageTitle'  => 'Ngân sách tháng ' . $month . '/' . $year,
        ]);
    }

    // ── POST /budget ──────────────────────────────────────────
    /**
     * Đặt hoặc cập nhật hạn mức ngân sách.
     * Nếu checkbox 'apply_to_end_of_year' được chọn, sẽ áp dụng
     * hạn mức cho tất cả các tháng còn lại trong năm.
     */
    public function setLimit(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/budget');
        }
        CsrfTokenManager::invalidate();

        $uid             = $this->currentUserId();
        $categoryId      = (int)($_POST['category_id']     ?? 0);
        $limitAmount     = (float)($_POST['limit_amount']   ?? 0);
        $threshold       = (int)($_POST['alert_threshold']  ?? 80);
        $month           = (int)($_POST['month']            ?? date('n'));
        $year            = (int)($_POST['year']             ?? date('Y'));
        $applyToEndOfYear = isset($_POST['apply_to_end_of_year']) && $_POST['apply_to_end_of_year'] == '1';

        // Validate
        if ($categoryId <= 0) {
            FlashMessage::set('danger', 'Vui lòng chọn danh mục.');
            $this->redirect('/budget');
        }

        try {
            // Nếu checkbox được chọn, dùng updateMonthlyBudget()
            if ($applyToEndOfYear) {
                $this->budgetService->updateMonthlyBudget($uid, $categoryId, $limitAmount, $month, $year, true);
                FlashMessage::set('success', 'Đã cập nhật ngân sách cho tháng ' . $month . ' và tất cả các tháng còn lại năm ' . $year . '.');
            } else {
                // Nếu không chọn, chỉ cập nhật tháng được chọn
                $this->budgetService->setLimit($uid, $categoryId, $limitAmount, $month, $year, $threshold);
                FlashMessage::set('success', 'Đã cập nhật ngân sách.');
            }
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
        } catch (\RuntimeException $e) {
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

        $deleted = $this->budgetRepo->deleteByIdAndUser((int)$id, $this->currentUserId());

        FlashMessage::set(
            $deleted ? 'success' : 'warning',
            $deleted ? 'Đã xoá hạn mức ngân sách.' : 'Không tìm thấy hoặc bạn không có quyền xoá.'
        );
        $this->redirect('/budget');
    }
}
