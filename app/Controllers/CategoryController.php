<?php
namespace App\Controllers;
use App\Repositories\CategoryRepository;
use App\Helpers\{CsrfTokenManager, FlashMessage};

// ============================================================
// CATEGORY CONTROLLER — TV2 Ngày 3
// ============================================================
class CategoryController extends BaseController
{
    private CategoryRepository $catRepo;

    public function __construct()
    {
        $this->catRepo = new CategoryRepository();
    }

    /** GET /categories */
    public function index(): void
    {
        $cats = $this->catRepo->findByUser($this->currentUserId());
        $csrf = CsrfTokenManager::generate();
        $this->render('categories/index', compact('cats', 'csrf'));
    }

    /** POST /categories */
    public function store(): void
    {
        // TODO: validate CSRF, validate name, save, redirect
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }

    /** POST /categories/{id}/delete */
    public function destroy(string $id): void
    {
        // TODO: CSRF, deleteByIdAndUser, catch FK violation (có giao dịch đang dùng danh mục này)
        throw new \RuntimeException('Chưa implement — TV2 Ngày 3');
    }
}
