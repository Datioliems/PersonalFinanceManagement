<?php
namespace App\Controllers;
use App\Models\ExpenseTransaction;
use App\Services\BudgetService;
use App\Repositories\{TransactionRepository, CategoryRepository, BudgetRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

// ============================================================
// EXPENSE CONTROLLER — TV3 Ngày 3
// ============================================================
class ExpenseController extends BaseController
{
    private TransactionRepository $txRepo;
    private CategoryRepository    $catRepo;
    private BudgetService         $budgetService;

    public function __construct()
    {
        $this->txRepo        = new TransactionRepository();
        $this->catRepo       = new CategoryRepository();
        $this->budgetService = new BudgetService(
            new BudgetRepository(),
            $this->txRepo
        );
    }

    /** GET /expenses — Danh sách chi tiêu có phân trang */
    public function index(): void
    {
        // TODO: lấy page từ $_GET, đếm total, tạo Paginator
        // $page  = (int) ($_GET['page'] ?? 1);
        // $total = $this->txRepo->countByType('expense', $this->currentUserId());
        // $pager = new Paginator($total, 15, $page);
        // $items = $this->txRepo->findByType('expense', $this->currentUserId(), 15, $pager->getOffset());
        // $this->render('expenses/index', ['items'=>$items, 'pager'=>$pager]);
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /** GET /expenses/create */
    public function create(): void
    {
        $categories = $this->catRepo->findByUser($this->currentUserId());
        $csrf       = CsrfTokenManager::generate();
        $this->render('expenses/create', compact('categories', 'csrf'));
    }

    /**
     * POST /expenses — Thêm chi tiêu mới
     * TODO: validate CSRF → new ExpenseTransaction → setBudgetService → process()
     *       → nếu có budgetAlert → FlashMessage warning → redirect
     */
    public function store(): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /** GET /expenses/{id} — Form sửa */
    public function edit(string $id): void
    {
        // TODO: findById, kiểm tra ownership, render form
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /** POST /expenses/{id} — Cập nhật */
    public function update(string $id): void
    {
        // TODO: validate CSRF, ownership, update
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }

    /** POST /expenses/{id}/delete */
    public function destroy(string $id): void
    {
        // TODO: CSRF, ownership check, deleteByIdAndUser
        throw new \RuntimeException('Chưa implement — TV3 Ngày 3');
    }
}
