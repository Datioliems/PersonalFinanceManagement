<?php
// ============================================================
// CONTROLLER — app/Controllers/CategoryController.php
// ============================================================
// TV2 viết — Ngày 3
//
// Routes:
//   GET  /categories          → index()
//   POST /categories          → store()
//   POST /categories/{id}/delete → destroy()
// ============================================================

namespace App\Controllers;

use App\Repositories\CategoryRepository;
use App\Helpers\CsrfTokenManager;
use App\Helpers\FlashMessage;

class CategoryController extends BaseController
{
    private CategoryRepository $catRepo;

    public function __construct()
    {
        $this->catRepo = new CategoryRepository();
    }

    // ── GET /categories ───────────────────────────────────────
    /**
     * Hiển thị danh sách danh mục + form tạo mới inline.
     */
    public function index(): void
    {
        $uid  = $this->currentUserId();
        $cats = $this->catRepo->findByUser($uid);
        $csrf = CsrfTokenManager::generate();

        $this->render('categories/index', [
            'cats'      => $cats,
            'csrf'      => $csrf,
            'pageTitle' => 'Danh mục',
        ]);
    }

    // ── POST /categories ──────────────────────────────────────
    /**
     * Tạo danh mục mới.
     */
    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/categories');
        }
        CsrfTokenManager::invalidate();

        $name  = trim($_POST['name']  ?? '');
        $type  = $_POST['type']        ?? 'both';
        $icon  = trim($_POST['icon']  ?? '') ?: null;
        $color = trim($_POST['color'] ?? '') ?: null;
        $uid   = $this->currentUserId();

        // Validate
        if (strlen($name) < 2) {
            FlashMessage::set('danger', 'Tên danh mục phải có ít nhất 2 ký tự.');
            $this->redirect('/categories');
        }
        if (!in_array($type, ['income', 'expense', 'both'], true)) {
            FlashMessage::set('danger', 'Loại danh mục không hợp lệ.');
            $this->redirect('/categories');
        }

        // Kiểm tra trùng tên
        if ($this->catRepo->findByNameAndUser($name, $uid)) {
            FlashMessage::set('warning', "Danh mục \"{$name}\" đã tồn tại.");
            $this->redirect('/categories');
        }

        $this->catRepo->save([
            'user_id' => $uid,
            'name'    => $name,
            'type'    => $type,
            'icon'    => $icon,
            'color'   => $color,
        ]);

        FlashMessage::set('success', "Đã tạo danh mục \"{$name}\".");
        $this->redirect('/categories');
    }

    // ── POST /categories/{id}/delete ─────────────────────────
    /**
     * Xoá danh mục.
     * Bắt PDOException SQLSTATE 23000 khi danh mục đang có giao dịch.
     */
    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/categories');
        }
        CsrfTokenManager::invalidate();

        try {
            $deleted = $this->catRepo->deleteByIdAndUser((int)$id, $this->currentUserId());
            if ($deleted) {
                FlashMessage::set('success', 'Đã xoá danh mục.');
            } else {
                FlashMessage::set('warning', 'Không tìm thấy danh mục hoặc bạn không có quyền xoá.');
            }
        } catch (\PDOException $e) {
            // SQLSTATE 23000 = FK constraint violation
            if (str_starts_with($e->getCode(), '23')) {
                FlashMessage::set('danger', 'Không thể xoá — danh mục này đang được dùng bởi một số giao dịch.');
            } else {
                FlashMessage::set('danger', 'Có lỗi xảy ra. Vui lòng thử lại.');
                error_log('[CategoryController] ' . $e->getMessage());
            }
        }

        $this->redirect('/categories');
    }
}
