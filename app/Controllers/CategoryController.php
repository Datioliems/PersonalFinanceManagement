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
        $uid     = $this->currentUserId();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 8;

        // Lấy toàn bộ danh mục, chia nhóm theo type
        $allCats     = $this->catRepo->findByUser($uid);
        $expenseCats = array_values(array_filter($allCats, fn($c) => $c['type'] === 'expense'));
        $incomeCats  = array_values(array_filter($allCats, fn($c) => $c['type'] === 'income'));
        $bothCats    = array_values(array_filter($allCats, fn($c) => $c['type'] === 'both'));

        // Paginate từng nhóm với cùng offset
        $offset           = ($page - 1) * $perPage;
        $pagedExpense     = array_slice($expenseCats, $offset, $perPage);
        $pagedIncome      = array_slice($incomeCats,  $offset, $perPage);
        $pagedBoth        = array_slice($bothCats,    $offset, $perPage);

        // Tổng trang = max của các nhóm (nhóm nào nhiều hơn thì quyết định số trang)
        $maxTotal = max(count($expenseCats), count($incomeCats), count($bothCats), 1);
        $pager    = new \App\Helpers\Paginator($maxTotal, $perPage, $page);

        $csrf = CsrfTokenManager::generate();

        $this->render('categories/index', [
            'cats'         => $allCats,        // full list (dùng cho dropdown khi cần)
            'expenseCats'  => $pagedExpense,
            'incomeCats'   => $pagedIncome,
            'bothCats'     => $pagedBoth,
            'hasAny'       => !empty($allCats),
            'pager'        => $pager,
            'csrf'         => $csrf,
            'pageTitle'    => 'Danh mục',
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
        $uid   = $this->currentUserId();

        // Validate
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

        // Auto-random màu — 12 màu cách nhau ~30° trên vòng màu, mỗi màu 1 nhóm sắc riêng biệt
        $palette = [
            '#dc2626', // Đỏ           (0°)
            '#ea580c', // Cam           (22°)
            '#ca8a04', // Vàng hổ phách (45°)
            '#65a30d', // Xanh neon     (80°)
            '#16a34a', // Xanh lá       (142°)
            '#0d9488', // Xanh ngọc     (177°)
            '#0284c7', // Xanh trời     (204°)
            '#1d4ed8', // Xanh dương    (224°)
            '#6d28d9', // Tím đậm       (263°)
            '#a21caf', // Tím hồng      (289°)
            '#be185d', // Hồng đậm      (328°)
            '#9f1239', // Đỏ hoa hồng   (345°)
        ];
        $existingCats  = $this->catRepo->findByUser($uid);
        $usedColors    = array_column($existingCats, 'color');
        $availColors   = array_values(array_diff($palette, $usedColors));
        // Nếu hết màu mới thì dùng lại palette từ đầu
        $color = count($availColors) > 0
            ? $availColors[array_rand($availColors)]
            : $palette[array_rand($palette)];

        $this->catRepo->save([
            'user_id' => $uid,
            'name'    => $name,
            'type'    => $type,
            'icon'    => $icon,
            'color'   => $color,
        ]);

        FlashMessage::set('success', "Đã tạo danh mục \"{$name}\".");
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

        // Kiểm tra trùng màu (trừ chính nó)
        if ($color) {
            $existingColor = $this->catRepo->findByColorAndUser($color, $uid);
            if ($existingColor && (int)$existingColor['id'] !== (int)$id) {
                FlashMessage::set('warning', "Màu sắc này đã được sử dụng cho danh mục \"{$existingColor['name']}\". Vui lòng chọn màu khác.");
                $this->redirect("/categories/{$id}/edit");
            }
        }

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
