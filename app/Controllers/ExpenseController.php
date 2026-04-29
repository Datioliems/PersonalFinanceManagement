<?php
// ============================================================
// CONTROLLER — app/Controllers/ExpenseController.php
// ============================================================
// TV3 viết — Ngày 3
//
// Routes:
//   GET  /expenses              → index()   (danh sách + phân trang)
//   GET  /expenses/create       → create()  (form thêm mới)
//   POST /expenses              → store()   (lưu mới)
//   GET  /expenses/{id}/edit    → edit()    (form sửa)
//   POST /expenses/{id}         → update()  (lưu sửa)
//   POST /expenses/{id}/delete  → destroy() (xoá)
// ============================================================

namespace App\Controllers;

use App\Services\ExpenseService;
use App\Repositories\{TransactionRepository, CategoryRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

class ExpenseController extends BaseController
{
    private ExpenseService     $expenseService;
    private TransactionRepository $txRepo;
    private CategoryRepository $catRepo;

    public function __construct()
    {
        $this->txRepo         = new TransactionRepository();
        $this->catRepo        = new CategoryRepository();
        $this->expenseService = new ExpenseService($this->txRepo);
    }

    // ── GET /expenses ─────────────────────────────────────────
    /**
     * Danh sách chi tiêu với filter tháng + phân trang.
     */
    public function index(): void
    {
        $uid         = $this->currentUserId();
        $page        = max(1, (int)($_GET['page']         ?? 1));
        $filterMonth = $_GET['filter_month'] ?? '';
        $sort        = $_GET['sort']         ?? 'date_desc';

        // Validate filter_month format
        if (!empty($filterMonth) && !preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $filterMonth = '';
        }

        // Đếm tổng để tạo Paginator
        $total = $this->txRepo->countFiltered($uid, 'expense', $filterMonth);
        $pager = new Paginator($total, 15, $page);

        // Lấy data
        $items = $this->txRepo->findFiltered(
            $uid, 'expense', $sort, $filterMonth,
            $pager->getPerPage(), $pager->getOffset()
        );

        $this->render('expenses/index', [
            'items'       => $items,
            'pager'       => $pager,
            'filterMonth' => $filterMonth,
            'sort'        => $sort,
            'pageTitle'   => 'Chi tiêu',
        ]);
    }

    // ── GET /expenses/create ──────────────────────────────────
    public function create(): void
    {
        $uid  = $this->currentUserId();
        $cats = $this->catRepo->findByUserAndType($uid, 'expense');
        $csrf = CsrfTokenManager::generate();

        $this->render('expenses/create', [
            'cats'      => $cats,
            'csrf'      => $csrf,
            'pageTitle' => 'Thêm chi tiêu',
        ]);
    }

    // ── POST /expenses ────────────────────────────────────────
    /**
     * Lưu chi tiêu mới.
     * Sau khi lưu: kiểm tra budget alert → set flash nếu có → redirect.
     */
    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/expenses/create');
        }
        CsrfTokenManager::invalidate();

        try {
            $alert = $this->expenseService->add($_POST, $this->currentUserId());

            if ($alert) {
                // Cảnh báo ngân sách — warning, không phải lỗi
                FlashMessage::set('warning', $alert);
            } else {
                FlashMessage::set('success', 'Đã lưu chi tiêu thành công.');
            }
            $this->redirect('/expenses');

        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
            $this->redirect('/expenses/create');
        }
    }

    // ── GET /expenses/{id}/edit ───────────────────────────────
    public function edit(string $id): void
    {
        $uid = $this->currentUserId();
        $tx  = $this->txRepo->findById((int)$id);

        // Ownership check
        if (!$tx || (int)$tx['user_id'] !== $uid || $tx['type'] !== 'expense') {
            FlashMessage::set('danger', 'Không tìm thấy giao dịch.');
            $this->redirect('/expenses');
        }

        $cats = $this->catRepo->findByUserAndType($uid, 'expense');
        $csrf = CsrfTokenManager::generate();

        $this->render('expenses/edit', [
            'tx'        => $tx,
            'cats'      => $cats,
            'csrf'      => $csrf,
            'pageTitle' => 'Sửa chi tiêu',
        ]);
    }

    // ── POST /expenses/{id} ───────────────────────────────────
    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/expenses');
        }
        CsrfTokenManager::invalidate();

        try {
            $this->expenseService->update((int)$id, $this->currentUserId(), $_POST);
            FlashMessage::set('success', 'Đã cập nhật chi tiêu.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/expenses');
    }

    // ── POST /expenses/{id}/delete ────────────────────────────
    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect('/expenses');
        }
        CsrfTokenManager::invalidate();

        try {
            $this->expenseService->delete((int)$id, $this->currentUserId());
            FlashMessage::set('success', 'Đã xoá chi tiêu.');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect('/expenses');
    }
}
