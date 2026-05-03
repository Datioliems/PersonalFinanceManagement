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
        $returnUrl = $_POST['return_url'] ?? '/categories';

        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect($returnUrl);
        }
        CsrfTokenManager::invalidate();

        $name  = trim($_POST['name']  ?? '');
        $type  = $_POST['type']        ?? 'expense';
        $icon  = trim($_POST['icon']  ?? '') ?: null;
        $color = trim($_POST['color'] ?? '') ?: null;
        $uid   = $this->currentUserId();

        // Validate
        // ❌ Kiểm tra user_id hợp lệ (phải > 0)
        if ($uid <= 0) {
            FlashMessage::set('danger', 'Bạn phải đăng nhập để thêm danh mục.');
            $this->redirect('/auth/login');
        }

        if (strlen($name) < 2) {
            FlashMessage::set('danger', 'Tên danh mục phải có ít nhất 2 ký tự.');
            $this->redirect($returnUrl);
        }
        if (!in_array($type, ['income', 'expense'], true)) {
            FlashMessage::set('danger', 'Loại danh mục không hợp lệ.');
            $this->redirect($returnUrl);
        }

        // Kiểm tra trùng tên
        if ($this->catRepo->findByNameAndUser($name, $uid)) {
            FlashMessage::set('warning', "Danh mục \"{$name}\" đã tồn tại.");
            $this->redirect($returnUrl);
        }

        try {
            $this->catRepo->save([
                'user_id' => $uid,
                'name'    => $name,
                'type'    => $type,
                'icon'    => $icon,
                'color'   => $color,
            ]);
            FlashMessage::set('success', "Đã tạo danh mục \"{$name}\".");
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
        } catch (\PDOException $e) {
            // ❌ Bắt xung đột FK từ MySQL
            if (strpos($e->getMessage(), '23000') !== false || strpos($e->getMessage(), '1452') !== false) {
                FlashMessage::set('danger', 'Lỗi: User_id không tồn tại. Vui lòng đăng nhập lại.');
            } else {
                FlashMessage::set('danger', 'Có lỗi khi tạo danh mục: ' . $e->getMessage());
            }
        }

        $this->redirect($returnUrl);
    }

    // ── GET /categories/{id}/edit ────────────────────────────
    /**
     * Hiển thị form sửa danh mục.
     */
    public function edit(string $id): void
    {
        $uid = $this->currentUserId();
        $cat = $this->catRepo->findById((int)$id);

        if (!$cat || (int)$cat['user_id'] !== $uid) {
            FlashMessage::set('danger', 'Không tìm thấy danh mục.');
            $this->redirect('/categories');
        }

        $csrf = CsrfTokenManager::generate();
        $this->render('categories/edit', [
            'cat'       => $cat,
            'csrf'      => $csrf,
            'pageTitle' => 'Sửa danh mục',
        ]);
    }

    // ── POST /categories/{id} ────────────────────────────────
    /**
     * Xử lý cập nhật danh mục.
     */
    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('/categories');
        }
        CsrfTokenManager::invalidate();

        $uid   = $this->currentUserId();
        $name  = trim($_POST['name']  ?? '');
        $type  = $_POST['type']        ?? 'expense';
        $color = trim($_POST['color'] ?? '') ?: null;
        $icon  = trim($_POST['icon']  ?? '') ?: null;

        // Validate
        // ❌ Kiểm tra user_id hợp lệ (phải > 0)
        if ($uid <= 0) {
            FlashMessage::set('danger', 'Bạn phải đăng nhập để chỉnh sửa danh mục.');
            $this->redirect('/auth/login');
        }

        if (strlen($name) < 2) {
            FlashMessage::set('danger', 'Tên danh mục phải có ít nhất 2 ký tự.');
            $this->redirect("/categories/{$id}/edit");
        }
        if (!in_array($type, ['income', 'expense'], true)) {
            FlashMessage::set('danger', 'Loại danh mục không hợp lệ.');
            $this->redirect("/categories/{$id}/edit");
        }

        // Kiểm tra trùng tên (trừ chính nó)
        $existing = $this->catRepo->findByNameAndUser($name, $uid);
        if ($existing && (int)$existing['id'] !== (int)$id) {
            FlashMessage::set('warning', "Danh mục \"{$name}\" đã tồn tại.");
            $this->redirect("/categories/{$id}/edit");
        }

        try {
            $updated = $this->catRepo->update((int)$id, $uid, [
                'name'  => $name,
                'type'  => $type,
                'color' => $color,
                'icon'  => $icon,
            ]);

            if ($updated) {
                FlashMessage::set('success', 'Đã cập nhật danh mục thành công.');
            } else {
                FlashMessage::set('warning', 'Không tìm thấy danh mục hoặc không có thay đổi.');
            }
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger', $e->getMessage());
        } catch (\PDOException $e) {
            // ❌ Bắt xung đột FK từ MySQL
            if (strpos($e->getMessage(), '23000') !== false || strpos($e->getMessage(), '1452') !== false) {
                FlashMessage::set('danger', 'Lỗi: User_id không tồn tại. Vui lòng đăng nhập lại.');
            } else {
                FlashMessage::set('danger', 'Có lỗi khi cập nhật danh mục: ' . $e->getMessage());
            }
        }

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
