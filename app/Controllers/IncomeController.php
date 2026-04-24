<?php
namespace App\Controllers;
use App\Models\IncomeTransaction;
use App\Repositories\{TransactionRepository, CategoryRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

// ============================================================
// INCOME CONTROLLER — TV4 Ngày 3
// ============================================================
class IncomeController extends BaseController
{
    private TransactionRepository $txRepo;
    private CategoryRepository    $catRepo;

    public function __construct()
    {
        $this->txRepo  = new TransactionRepository();
        $this->catRepo = new CategoryRepository();
    }

    /** GET /incomes */
    public function index(): void
    {
        // TODO: tương tự ExpenseController::index() nhưng type='income'
        throw new \RuntimeException('Chưa implement — TV4 Ngày 3');
    }

    /** GET /incomes/create */
    public function create(): void
    {
        $categories = $this->catRepo->findByUser($this->currentUserId());
        $csrf       = CsrfTokenManager::generate();
        $this->render('incomes/create', compact('categories', 'csrf'));
    }

    /**
     * POST /incomes — Thêm thu nhập
     * TODO: validate CSRF → new IncomeTransaction → process() → redirect
     */
    public function store(): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV4 Ngày 3');
    }

    public function edit(string $id): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV4 Ngày 3');
    }

    public function update(string $id): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV4 Ngày 3');
    }

    public function destroy(string $id): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV4 Ngày 3');
    }
}
