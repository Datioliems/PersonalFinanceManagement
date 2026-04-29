<?php
// ============================================================
// VIEW — app/Views/expenses/create.php
// ============================================================
$pageTitle = 'Thêm chi tiêu';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center">
<div class="col-12 col-md-7 col-lg-5">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/expenses" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="h4 fw-semibold mb-0">Thêm chi tiêu</h2>
</div>

<div class="card shadow-sm">
<div class="card-body p-4">
<form method="POST" action="<?= BASE_URL ?>/expenses">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <!-- Số tiền -->
    <div class="mb-3">
        <label class="form-label fw-medium">
            Số tiền (VNĐ) <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-white text-danger">
                <i class="bi bi-dash-circle"></i>
            </span>
            <input type="number" name="amount" class="form-control"
                   min="1" step="1000" required
                   placeholder="VD: 150000"
                   value="<?= htmlspecialchars($_POST['amount'] ?? '', ENT_QUOTES) ?>">
            <span class="input-group-text bg-white text-muted">đ</span>
        </div>
    </div>

    <!-- Danh mục -->
    <div class="mb-3">
        <label class="form-label fw-medium">
            Danh mục <span class="text-danger">*</span>
        </label>
        <select name="category_id" class="form-select" required>
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($cats as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>"
                <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($cats)): ?>
        <div class="form-text text-warning">
            Chưa có danh mục chi tiêu.
            <a href="<?= BASE_URL ?>/categories">Tạo danh mục trước</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ngày -->
    <div class="mb-3">
        <label class="form-label fw-medium">
            Ngày <span class="text-danger">*</span>
        </label>
        <input type="date" name="trans_date" class="form-control" required
               value="<?= htmlspecialchars($_POST['trans_date'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
    </div>

    <!-- Ghi chú -->
    <div class="mb-4">
        <label class="form-label fw-medium">Ghi chú</label>
        <textarea name="note" class="form-control" rows="2"
                  placeholder="Mô tả chi tiêu (tuỳ chọn)"
                  maxlength="500"><?= htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES) ?></textarea>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-check-lg me-1"></i>Lưu chi tiêu
        </button>
        <a href="<?= BASE_URL ?>/expenses" class="btn btn-outline-secondary">Huỷ</a>
    </div>
</form>
</div>
</div>

</div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
