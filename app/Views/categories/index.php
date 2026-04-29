<?php
// ============================================================
// VIEW — app/Views/project/de13_complete/public/categories/index.php
// ============================================================
// Biến nhận từ CategoryController::index():
//   $cats  — array các danh mục
//   $csrf  — CSRF token -- CHƯA CÓ 
// ============================================================
$pageTitle = $pageTitle ?? 'Danh mục';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-semibold mb-1">Danh mục</h2>
        <p class="text-muted small mb-0">Quản lý danh mục thu/chi của bạn</p>
    </div>
    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
    </button>
</div>

<!-- Danh sách danh mục -->
<?php if (empty($cats)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-tags fs-1 d-block mb-2"></i>
        Chưa có danh mục nào.
        <a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate">Tạo ngay</a>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($cats as $cat): ?>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <!-- Icon + màu -->
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:42px;height:42px;
                            background:<?= htmlspecialchars($cat['color'] ?? '#e5e7eb', ENT_QUOTES) ?>22">
                    <i class="<?= htmlspecialchars($cat['icon'] ?? 'bi-tag', ENT_QUOTES) ?> fs-5"
                       style="color:<?= htmlspecialchars($cat['color'] ?? '#6b7280', ENT_QUOTES) ?>"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-medium text-truncate">
                        <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                    </div>
                    <small class="text-muted">
                        <?= match($cat['type']) {
                            'income'  => '<span class="badge bg-success-subtle text-success">Thu nhập</span>',
                            'expense' => '<span class="badge bg-danger-subtle text-danger">Chi tiêu</span>',
                            default   => '<span class="badge bg-secondary-subtle text-secondary">Cả hai</span>',
                        } ?>
                    </small>
                </div>
                <!-- Xoá -->
                <form method="POST"
                      action="<?= BASE_URL ?>/categories/<?= (int)$cat['id'] ?>/delete"
                      class="flex-shrink-0">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <button type="submit"
                            class="btn btn-sm btn-outline-danger py-0 px-2"
                            data-confirm="Xoá danh mục '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>'?">
                        <i class="bi bi-trash3"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal tạo danh mục mới -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/categories">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Tên danh mục <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"
                               placeholder="VD: Ăn uống, Đi lại..."
                               required minlength="2" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Loại</label>
                        <select name="type" class="form-select">
                            <option value="expense">Chi tiêu</option>
                            <option value="income">Thu nhập</option>
                            <option value="both">Cả hai</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Màu sắc</label>
                            <input type="color" name="color" class="form-control form-control-color w-100"
                                   value="#6366f1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Icon Bootstrap</label>
                            <input type="text" name="icon" class="form-control"
                                   placeholder="bi-cup-hot">
                            <div class="form-text">
                                <a href="https://icons.getbootstrap.com" target="_blank">Xem danh sách icon</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i>Tạo danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
