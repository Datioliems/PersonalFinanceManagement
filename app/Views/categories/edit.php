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
                        <div class="col-auto">
                            <label for="editCatColor" class="form-label fw-medium">Màu sắc</label>
                            <input type="color" id="editCatColor" name="color" class="form-control form-control-color"
                                   value="<?= htmlspecialchars($cat['color'] ?? '#6b7280', ENT_QUOTES) ?>" style="height: 38px; width: 60px;">
                        </div>
                        <div class="col">
                            <label for="editCatIcon" class="form-label fw-medium">Icon</label>
                            <div class="input-group" style="cursor:pointer" data-icon-picker="editCatIcon">
                                <span class="input-group-text bg-white border-end-0 px-2" id="editIconPreviewBox" style="color: <?= htmlspecialchars($cat['color'] ?? '#6b7280', ENT_QUOTES) ?>">
                                    <i class="<?= htmlspecialchars($cat['icon'] ?? 'bi-tag', ENT_QUOTES) ?>" id="editIconPreviewEl"></i>
                                </span>
                                <input type="text" id="editCatIcon" name="icon" class="form-control border-start-0 border-end-0 ps-0"
                                       placeholder="Chọn icon..." value="<?= htmlspecialchars($cat['icon'] ?? '', ENT_QUOTES) ?>" autocomplete="off" readonly style="cursor: pointer; background-color: #fff;">
                                <span class="input-group-text bg-white text-muted border-start-0">
                                    <i class="bi bi-chevron-down" style="font-size: .8rem"></i>
                                </span>
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

<?php ob_start(); ?>
<script>
(function() {
    const colorInput = document.getElementById('editCatColor');
    const iconBox    = document.getElementById('editIconPreviewBox');
    const iconInput  = document.getElementById('editCatIcon');
    const iconEl     = document.getElementById('editIconPreviewEl');

    if (colorInput && iconBox) {
        colorInput.addEventListener('input', e => iconBox.style.color = e.target.value);
    }
    
    if (iconInput && iconEl) {
        iconInput.addEventListener('input', e => {
            let cls = e.target.value.trim() || 'bi-tag';
            iconEl.className = cls.startsWith('bi-') ? 'bi ' + cls : 'bi bi-tag';
        });
    }
})();
</script>
<?php 
$extraJs = ob_get_clean();
require BASE_PATH . '/app/Views/partials/footer.php'; 
?>
