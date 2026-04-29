<?php
// ============================================================
// VIEW — app/Views/transactions/create.php
// ============================================================
/** @var array $incomeCats */
/** @var array $expenseCats */
/** @var string $csrf */

$pageTitle = 'Thêm giao dịch';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center">
<div class="col-12 col-md-7 col-lg-5">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/transactions" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="h4 fw-semibold mb-0">Thêm giao dịch</h2>
</div>

<div class="card shadow-sm">
<div class="card-body p-4">
<form method="POST" action="<?= BASE_URL ?>/transactions">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <!-- Số tiền -->
    <div class="mb-3">
        <label class="form-label fw-medium">
            Số tiền (VNĐ) <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-white text-muted">
                <i class="bi bi-cash-stack"></i>
            </span>
            <input type="number" name="amount" class="form-control"
                   min="1" required
                   placeholder="VD: 150000"
                   value="<?= htmlspecialchars($_POST['amount'] ?? '', ENT_QUOTES) ?>">
            <span class="input-group-text bg-white text-muted">đ</span>
        </div>
    </div>

    <!-- Danh mục gộp (custom dropdown với icon) -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-end mb-1">
            <label class="form-label fw-medium mb-0">
                Danh mục <span class="text-danger">*</span>
            </label>
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                    data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle me-1"></i>Tạo nhanh
            </button>
        </div>

        <input type="hidden" name="type_category_id" id="catHiddenInput" required>
        <div class="dropdown">
            <button type="button" id="catDropdownBtn"
                    class="btn btn-outline-secondary w-100 text-start d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown" aria-expanded="false" style="min-height:38px">
                <span id="catDisplay" class="text-muted flex-grow-1">-- Chọn danh mục --</span>
                <i class="bi bi-chevron-down flex-shrink-0"></i>
            </button>
            <ul class="dropdown-menu w-100 shadow-sm" style="max-height:300px;overflow-y:auto;">
                <li><h6 class="dropdown-header">Thu nhập</h6></li>
                <?php foreach ($incomeCats as $cat):
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',    ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#16a34a',   ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                  ENT_QUOTES);
                ?>
                <li>
                    <a class="dropdown-item cat-option d-flex align-items-center gap-2" href="#"
                       data-value="income_<?= (int)$cat['id'] ?>"
                       data-icon="<?= $icon ?>" data-color="<?= $color ?>" data-name="<?= $name ?>">
                        <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i>
                        <span><?= $name ?></span>
                        <span class="badge ms-auto" style="background:#dcfce8;color:#16a34a;font-size:.65em">Thu</span>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (empty($incomeCats)): ?>
                <li><span class="dropdown-item-text text-muted small">Chưa có danh mục thu nhập</span></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">Chi tiêu</h6></li>
                <?php foreach ($expenseCats as $cat):
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',    ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#dc2626',   ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                  ENT_QUOTES);
                ?>
                <li>
                    <a class="dropdown-item cat-option d-flex align-items-center gap-2" href="#"
                       data-value="expense_<?= (int)$cat['id'] ?>"
                       data-icon="<?= $icon ?>" data-color="<?= $color ?>" data-name="<?= $name ?>">
                        <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i>
                        <span><?= $name ?></span>
                        <span class="badge ms-auto" style="background:#fee2e2;color:#dc2626;font-size:.65em">Chi</span>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (empty($expenseCats)): ?>
                <li><span class="dropdown-item-text text-muted small">Chưa có danh mục chi tiêu</span></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="form-text text-muted">Chọn Thu nhập = cộng tiền &bull; Chọn Chi tiêu = trừ tiền</div>
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
                  placeholder="Mô tả giao dịch (tuỳ chọn)"
                  maxlength="500"><?= htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES) ?></textarea>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Lưu giao dịch
        </button>
        <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-secondary">Huỷ</a>
    </div>
</form>
</div>
</div>

</div>
</div>

<!-- Modal Tạo Danh Mục Nhanh -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= BASE_URL ?>/categories" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
            <input type="hidden" name="return_url" value="<?= BASE_URL ?>/transactions/create">
            
            <div class="modal-header">
                <h5 class="modal-title">Tạo danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Nhập tên danh mục">
                </div>
                <div class="mb-3">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select">
                        <option value="expense">Chi tiêu</option>
                        <option value="income">Thu nhập</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Màu sắc</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="#16a34a">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Icon Bootstrap</label>
                        <input type="text" name="icon" class="form-control" placeholder="bi-cup-hot">
                        <div class="form-text">
                            <a href="https://icons.getbootstrap.com" target="_blank">Xem icon</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="submit" class="btn btn-primary">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>

<script>
document.querySelectorAll('.cat-option').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        var val   = this.dataset.value;
        var icon  = this.dataset.icon;
        var color = this.dataset.color;
        var name  = this.dataset.name;
        document.getElementById('catHiddenInput').value = val;
        document.getElementById('catDisplay').innerHTML =
            '<i class="' + icon + ' me-1" style="color:' + color + '"></i>' + name;
        document.getElementById('catDisplay').classList.remove('text-muted');
    });
});
</script>
