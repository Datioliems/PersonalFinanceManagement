<?php
namespace App\Controllers;
use App\Services\BudgetService;
use App\Repositories\{BudgetRepository, CategoryRepository, TransactionRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage};

// ============================================================
// BUDGET CONTROLLER — TV2 Ngày 3
// ============================================================
class BudgetController extends BaseController
{
    private BudgetService      $budgetService;
    private CategoryRepository $catRepo;

    public function __construct()
    {
        $this->catRepo       = new CategoryRepository();
        $this->budgetService = new BudgetService(
            new BudgetRepository(),
            new TransactionRepository()
        );
    }

    /** GET /budget — Danh sách ngân sách với % đã dùng */
    public function index(): void
    {
        $month   = (int) ($_GET['month'] ?? date('n'));
        $year    = (int) ($_GET['year']  ?? date('Y'));
        $summary = $this->budgetService->getBudgetSummary($this->currentUserId(), $month, $year);
        $cats    = $this->catRepo->findByUser($this->currentUserId());
        $csrf    = CsrfTokenManager::generate();
        $this->render('budget/index', compact('summary','cats','csrf','month','year'));
    }

    /** POST /budget — Đặt/cập nhật hạn mức */
    public function setLimit(): void
    {
        // TODO: validate CSRF → budgetService->setLimit() → redirect
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }
}
