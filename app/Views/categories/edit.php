<?php
// ============================================================
// VIEW — app/Views/categories/edit.php
// ============================================================
/** @var array $cat */
/** @var string $csrf */

$pageTitle = 'Sửa danh mục';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="<?= BASE_URL ?>/categories" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-semibold mb-0">Sửa danh mục</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/categories/<?= (int)$cat['id'] ?>">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-medium">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               required minlength="2" maxlength="100"
                               value="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Loại</label>
                        <select name="type" class="form-select">
                            <option value="expense" <?= $cat['type'] === 'expense' ? 'selected' : '' ?>>Chi tiêu</option>
                            <option value="income" <?= $cat['type'] === 'income' ? 'selected' : '' ?>>Thu nhập</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-medium">Màu sắc</label>
                            <input type="color" name="color" class="form-control form-control-color w-100"
                                   value="<?= htmlspecialchars($cat['color'] ?? '#6b7280', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Icon Bootstrap</label>
                            <input type="text" name="icon" class="form-control"
                                   placeholder="bi-cup-hot" value="<?= htmlspecialchars($cat['icon'] ?? '', ENT_QUOTES) ?>">
                            <div class="form-text">
                                <a href="https://icons.getbootstrap.com" target="_blank">Xem danh sách icon</a>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg me-1"></i>Cập nhật
                        </button>
                        <a href="<?= BASE_URL ?>/categories" class="btn btn-outline-secondary">Huỷ</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
