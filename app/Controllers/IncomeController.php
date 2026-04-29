<?php
// ============================================================
// CONTROLLER — app/Controllers/IncomeController.php
// ============================================================
// TV4 viết — Ngày 3
// Pattern giống ExpenseController (TV3) — dùng chung Paginator.
//
// Routes:
//   GET  /incomes              → index()
//   GET  /incomes/create       → create()
//   POST /incomes              → store()
//   GET  /incomes/{id}/edit    → edit()
//   POST /incomes/{id}         → update()
//   POST /incomes/{id}/delete  → destroy()
//
// Điểm tích hợp Ngày 5:
//   index() dùng FinanceReport::generateMonthly() để hiện tổng thu tháng.
// ============================================================

namespace App\Controllers;

use App\Services\{IncomeService, FinanceReport};
use App\Repositories\{TransactionRepository, CategoryRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

class IncomeController extends BaseController
{
    private IncomeService         $incomeService;
    private FinanceReport         $report;
    private TransactionRepository $txRepo;
    private CategoryRepository    $catRepo;

    public function __construct()
    {
        $this->txRepo        = new TransactionRepository();
        $this->catRepo       = new CategoryRepository();
        $this->incomeService = new IncomeService($this->txRepo);
        $this->report        = new FinanceReport($this->txRepo);
    }

    // ── GET /incomes ──────────────────────────────────────────
    /**
     * Danh sách thu nhập với filter + sort + phân trang.
     * Card tóm tắt: tổng thu tháng hiện tại (từ FinanceReport — không PHP array_sum).
     */
    public function index(): void
    {
        $uid         = $this->currentUserId();
        $page        = max(1, (int)($_GET['page']         ?? 1));
        $filterMonth = $_GET['filter_month'] ?? '';
        $sort        = $_GET['sort']         ?? 'date_desc';

        if (!empty($filterMonth) && !preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $filterMonth = '';
        }

        // Phân trang
        $total = $this->txRepo->countFiltered($uid, 'income', $filterMonth);
        $pager = new Paginator($total, 15, $page);

        $items = $this->txRepo->findFiltered(
            $uid, 'income', $sort, $filterMonth,
            $pager->getPerPage(), $pager->getOffset()
        );

        // Tổng thu tháng hiện tại — tích hợp Ngày 5
        $monthNow   = (int)date('n');
        $yearNow    = (int)date('Y');
        $summary    = $this->report->generateMonthly($monthNow, $yearNow, $uid);

        $this->render('incomes/index', [
            'items'       => $items,
            'pager'       => $pager,
            'filterMonth' => $filterMonth,
            'sort'        => $sort,
            'totalIncome' => $summary['income'],
            'monthNow'    => $monthNow,
            'yearNow'     => $yearNow,
            'pageTitle'   => 'Thu nhập',
        ]);
    }

    // ── GET /incomes/create ───────────────────────────────────
    public function create(): void
    {
        $uid  = $this->currentUserId();
        $cats = $this->catRepo->findByUserAndType($uid, 'income');
        $csrf = CsrfTokenManager::generate();

        $this->render('incomes/create', [
            'cats'      => $cats,
            'csrf'      => $csrf,
            'pageTitle' => 'Thêm thu nhập',
        ]);
    }

    // ── POST /incomes ─────────────────────────────────────────
    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/incomes/create');
        }
        CsrfTokenManager::invalidate();

        try {
            $this->incomeService->add($_POST, $this->currentUserId());
            FlashMessage::set('success', 'Đã lưu thu nhập thành công.');
            $this->redirect('/incomes');
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
            $this->redirect('/incomes/create');
        }
    }

    // ── GET /incomes/{id}/edit ────────────────────────────────
    public function edit(string $id): void
    {
        $uid = $this->currentUserId();
        $tx  = $this->txRepo->findById((int)$id);

        if (!$tx || (int)$tx['user_id'] !== $uid || $tx['type'] !== 'income') {
            FlashMessage::set('danger', 'Không tìm thấy giao dịch.');
            $this->redirect('/incomes');
        }

        $cats = $this->catRepo->findByUserAndType($uid, 'income');
        $csrf = CsrfTokenManager::generate();

        $this->render('incomes/edit', [
            'tx'        => $tx,
            'cats'      => $cats,
            'csrf'      => $csrf,
            'pageTitle' => 'Sửa thu nhập',
        ]);
    }

    // ── POST /incomes/{id} ────────────────────────────────────
    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/incomes');
        }
        CsrfTokenManager::invalidate();

        try {
            $this->incomeService->update((int)$id, $this->currentUserId(), $_POST);
            FlashMessage::set('success', 'Đã cập nhật thu nhập.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/incomes');
    }

    // ── POST /incomes/{id}/delete ─────────────────────────────
    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/incomes');
        }
        CsrfTokenManager::invalidate();

        try {
            $this->incomeService->delete((int)$id, $this->currentUserId());
            FlashMessage::set('success', 'Đã xoá thu nhập.');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/incomes');
    }
}
