<?php
// ============================================================
// CONTROLLER — app/Controllers/TransactionController.php
// ============================================================
// Hợp nhất IncomeController và ExpenseController thành một.
// ============================================================

namespace App\Controllers;

use App\Services\{IncomeService, ExpenseService, FinanceReport};
use App\Repositories\{TransactionRepository, CategoryRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

class TransactionController extends BaseController
{
    private IncomeService         $incomeService;
    private ExpenseService        $expenseService;
    private FinanceReport         $report;
    private TransactionRepository $txRepo;
    private CategoryRepository    $catRepo;

    public function __construct()
    {
        $this->txRepo         = new TransactionRepository();
        $this->catRepo        = new CategoryRepository();
        $this->incomeService  = new IncomeService($this->txRepo);
        $this->expenseService = new ExpenseService($this->txRepo);
        $this->report         = new FinanceReport($this->txRepo);
    }

    // ── GET /transactions ──────────────────────────────────────────
    public function index(): void
    {
        $uid         = $this->currentUserId();
        $page        = max(1, (int)($_GET['page'] ?? 1));
        
        $startDate   = $_GET['start_date'] ?? date('Y-m-01');
        $endDate     = $_GET['end_date'] ?? date('Y-m-t');
        
        $filterType  = $_GET['filter_type'] ?? '';
        $sort        = $_GET['sort'] ?? 'date_desc';
        $catFilter   = (int)($_GET['category_id'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDate = date('Y-m-t');
        if (!in_array($filterType, ['income', 'expense', ''], true)) $filterType = '';

        $total = $this->txRepo->countFiltered($uid, $filterType, $startDate, $endDate, $catFilter);
        $pager = new Paginator($total, 10, $page);
        $items = $this->txRepo->findFiltered(
            $uid, $filterType, $sort, $startDate, $endDate,
            $pager->getPerPage(), $pager->getOffset(), $catFilter
        );

        // Tổng thu/chi từng ngày trong khoảng thời gian đang xem
        $dailySummary = $this->txRepo->getDailySummary($uid, $startDate, $endDate);

        // Tổng khoảng thời gian hiện tại cho stat cards
        $summary = $this->report->generateRange($startDate, $endDate, $uid);

        // Danh mục cho dropdown filter
        $cats = $this->catRepo->findByUser($uid);

        // Lấy danh sách ngân sách bị vượt
        $budgetRepo = new \App\Repositories\BudgetRepository();
        $budgetService = new \App\Services\BudgetService($budgetRepo, $this->txRepo);
        $budgetSummary = $budgetService->getBudgetSummary($uid, (int)date('m'), (int)date('Y'));
        $overBudgets = array_filter($budgetSummary, fn($b) => $b['is_exceeded']);

        $this->render('transactions/index', [
            'items'        => $items,
            'pager'        => $pager,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'filterType'   => $filterType,
            'sort'         => $sort,
            'catFilter'    => $catFilter,
            'cats'         => $cats,
            'dailySummary' => $dailySummary,
            'summary'      => $summary,
            'overBudgets'  => $overBudgets,
            'pageTitle'    => 'Giao dịch',
        ]);
    }

    // ── GET /transactions/create ───────────────────────────────────
    public function create(): void
    {
        $uid  = $this->currentUserId();
        $incomeCats  = $this->catRepo->findByUserAndType($uid, 'income');
        $expenseCats = $this->catRepo->findByUserAndType($uid, 'expense');
        $csrf = CsrfTokenManager::generate();

        $this->render('transactions/create', [
            'incomeCats'  => $incomeCats,
            'expenseCats' => $expenseCats,
            'csrf'        => $csrf,
            'pageTitle'   => 'Thêm giao dịch',
        ]);
    }

    // ── POST /transactions ─────────────────────────────────────────
    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/transactions/create');
        }
        CsrfTokenManager::invalidate();

        // Chặn giao dịch trong tương lai
        if (($_POST['trans_date'] ?? '') > date('Y-m-d')) {
            FlashMessage::set('danger', 'Không được nhập giao dịch trong tương lai.');
            $this->redirect(BASE_URL . '/transactions/create');
        }

        $typeCategoryId = $_POST['type_category_id'] ?? '';
        if (empty($typeCategoryId) || !str_contains($typeCategoryId, '_')) {
            FlashMessage::set('danger', 'Vui lòng chọn danh mục hợp lệ.');
            $this->redirect(BASE_URL . '/transactions/create');
        }

        [$type, $categoryId]  = explode('_', $typeCategoryId, 2);
        $_POST['category_id'] = $categoryId;
        $_POST['type']        = $type;

        try {
            if ($type === 'income') {
                $this->incomeService->add($_POST, $this->currentUserId());
                FlashMessage::set('success', 'Đã lưu thu nhập thành công.');
            } else {
                $alert = $this->expenseService->add($_POST, $this->currentUserId());
                if ($alert) {
                    FlashMessage::set('warning', 'Đã lưu chi tiêu. ' . $alert);
                } else {
                    FlashMessage::set('success', 'Đã lưu chi tiêu thành công.');
                }
            }
            $this->redirect('/transactions');
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
            $this->redirect('/transactions/create');
        }
    }

    // ── GET /transactions/{id}/edit ────────────────────────────────
    public function edit(string $id): void
    {
        $uid = $this->currentUserId();
        $tx  = $this->txRepo->findById((int)$id);

        if (!$tx || (int)$tx['user_id'] !== $uid) {
            FlashMessage::set('danger', 'Không tìm thấy giao dịch.');
            $this->redirect('/transactions');
        }

        $incomeCats  = $this->catRepo->findByUserAndType($uid, 'income');
        $expenseCats = $this->catRepo->findByUserAndType($uid, 'expense');
        $csrf = CsrfTokenManager::generate();

        $this->render('transactions/edit', [
            'tx'          => $tx,
            'incomeCats'  => $incomeCats,
            'expenseCats' => $expenseCats,
            'csrf'        => $csrf,
            'pageTitle'   => 'Sửa giao dịch',
        ]);
    }

    // ── POST /transactions/{id} ────────────────────────────────────
    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/transactions');
        }
        CsrfTokenManager::invalidate();

        // Chặn giao dịch trong tương lai
        if (($_POST['trans_date'] ?? '') > date('Y-m-d')) {
            FlashMessage::set('danger', 'Không được nhập giao dịch trong tương lai.');
            $this->redirect(BASE_URL . '/transactions/' . (int)$id . '/edit');
        }

        $typeCategoryId = $_POST['type_category_id'] ?? '';
        if (empty($typeCategoryId) || !str_contains($typeCategoryId, '_')) {
            FlashMessage::set('danger', 'Vui lòng chọn danh mục hợp lệ.');
            $this->redirect(BASE_URL . '/transactions');
        }

        [$type, $categoryId]  = explode('_', $typeCategoryId, 2);

        // Chỉ được sửa trong cùng loại — lấy type gốc từ DB để validate
        $original = $this->txRepo->findById((int)$id);
        if ($original && $original['type'] !== $type) {
            FlashMessage::set('danger', 'Không thể đổi loại giao dịch khi sửa. Thu nhập chỉ sửa thành thu nhập, chi tiêu chỉ sửa thành chi tiêu.');
            $this->redirect(BASE_URL . '/transactions/' . (int)$id . '/edit');
        }

        $_POST['category_id'] = $categoryId;
        $_POST['type']        = $type;

        try {
            if ($type === 'income') {
                $this->incomeService->update((int)$id, $this->currentUserId(), $_POST);
            } else {
                $this->expenseService->update((int)$id, $this->currentUserId(), $_POST);
            }
            FlashMessage::set('success', 'Đã cập nhật giao dịch.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/transactions');
    }

    // ── POST /transactions/{id}/delete ─────────────────────────────
    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/transactions');
        }
        CsrfTokenManager::invalidate();

        // Use any service to delete, since they both just call txRepo->deleteByIdAndUser
        try {
            $this->incomeService->delete((int)$id, $this->currentUserId());
            FlashMessage::set('success', 'Đã xoá giao dịch.');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/transactions');
    }
}