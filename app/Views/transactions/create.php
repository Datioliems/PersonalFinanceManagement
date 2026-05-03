<?php
// ============================================================
// VIEW — app/Views/transactions/create.php
// ============================================================
/** @var array $incomeCats */
/** @var array $expenseCats */
/** @var string $csrf */

$pageTitle = 'Thêm giao dịch';
$extraCss  = BASE_URL . '/css/transactions.css';
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
            <?php 
                $oldAmount = $_POST['amount'] ?? '';
                $displayAmount = $oldAmount ? number_format((float)$oldAmount, 0, ',', '.') : '';
            ?>
            <input type="hidden" name="amount" id="realAmount" value="<?= htmlspecialchars($oldAmount, ENT_QUOTES) ?>">
            <input type="text" id="displayAmount" class="form-control" required
                   placeholder="VD: 150.000"
                   value="<?= htmlspecialchars($displayAmount, ENT_QUOTES) ?>">
            <span class="input-group-text bg-white text-muted">đ</span>
        </div>
    </div>

    <!-- Loại giao dịch -->
    <div class="mb-3">
        <label class="form-label fw-medium">Loại giao dịch <span class="text-danger">*</span></label>
        <div class="d-flex gap-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="trans_type" id="typeExpense" value="expense" checked>
                <label class="form-check-label text-danger" for="typeExpense">Chi tiêu </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="trans_type" id="typeIncome" value="income">
                <label class="form-check-label text-success" for="typeIncome">Thu nhập </label>
            </div>
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
                <?php foreach ($expenseCats as $cat):
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',    ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#dc2626',   ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                  ENT_QUOTES);
                ?>
                <li class="cat-item" data-type="expense">
                    <a class="dropdown-item cat-option d-flex align-items-center gap-2" href="#"
                       data-value="expense_<?= (int)$cat['id'] ?>"
                       data-icon="<?= $icon ?>" data-color="<?= $color ?>" data-name="<?= $name ?>">
                        <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i>
                        <span><?= $name ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (empty($expenseCats)): ?>
                <li class="cat-item" data-type="expense"><span class="dropdown-item-text text-muted small">Chưa có danh mục chi tiêu</span></li>
                <?php endif; ?>

                <?php foreach ($incomeCats as $cat):
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',    ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#16a34a',   ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                  ENT_QUOTES);
                ?>
                <li class="cat-item" data-type="income" style="display:none;">
                    <a class="dropdown-item cat-option d-flex align-items-center gap-2" href="#"
                       data-value="income_<?= (int)$cat['id'] ?>"
                       data-icon="<?= $icon ?>" data-color="<?= $color ?>" data-name="<?= $name ?>">
                        <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i>
                        <span><?= $name ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (empty($incomeCats)): ?>
                <li class="cat-item" data-type="income" style="display:none;"><span class="dropdown-item-text text-muted small">Chưa có danh mục thu nhập</span></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Ngày -->
    <div class="mb-3">
        <label class="form-label fw-medium">
            Ngày <span class="text-danger">*</span>
        </label>
        <input type="date" name="trans_date" class="form-control" required max="<?= date('Y-m-d') ?>"
               value="<?= htmlspecialchars($_POST['trans_date'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
    </div>

    <!-- Ghi chú -->
    <div class="mb-4">
        <label class="form-label fw-medium">Ghi chú</label>
        <textarea name="note" class="form-control" rows="2"
                  placeholder="Mô tả giao dịch (tuỳ chọn)"
                  maxlength="20"><?= htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES) ?></textarea>
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
                    <div class="col-auto">
                        <label for="quickCatColor" class="form-label">Màu sắc</label>
                        <input type="color" id="quickCatColor" name="color" class="form-control form-control-color" value="#16a34a" title="Chọn màu sắc" style="height: 38px; width: 60px;">
                    </div>
                    <div class="col">
                        <label for="quickCatIcon" class="form-label">Icon</label>
                        <div class="input-group" style="cursor:pointer"
                             data-icon-picker="quickCatIcon">
                            <span class="input-group-text bg-white border-end-0 px-2" id="quickIconPreviewBox" style="color: #16a34a;">
                                <i class="bi bi-tag" id="quickIconPreviewEl"></i>
                            </span>
                            <input type="text" id="quickCatIcon" name="icon" class="form-control border-start-0 border-end-0 ps-0" placeholder="Chọn icon..." autocomplete="off" readonly style="cursor: pointer; background-color: #fff;">
                            <span class="input-group-text bg-white text-muted border-start-0">
                                <i class="bi bi-chevron-down" style="font-size: .8rem"></i>
                            </span>
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
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="trans_type"]');
    const catItems = document.querySelectorAll('.cat-item');
    const catHiddenInput = document.getElementById('catHiddenInput');
    const catDisplay = document.getElementById('catDisplay');

    function filterCategories() {
        const selectedType = document.querySelector('input[name="trans_type"]:checked').value;
        catItems.forEach(item => {
            if (item.dataset.type === selectedType) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Reset selection if the hidden input's value doesn't match the new type
        if (catHiddenInput.value && !catHiddenInput.value.startsWith(selectedType + '_')) {
            catHiddenInput.value = '';
            catDisplay.innerHTML = '-- Chọn danh mục --';
            catDisplay.classList.add('text-muted');
        }
    }

    typeRadios.forEach(radio => radio.addEventListener('change', filterCategories));
    
    // Initial filter on load
    filterCategories();

    document.querySelectorAll('.cat-option').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var val   = this.dataset.value;
            var icon  = this.dataset.icon;
            var color = this.dataset.color;
            var name  = this.dataset.name;
            catHiddenInput.value = val;
            catDisplay.innerHTML =
                '<i class="' + icon + ' me-1" style="color:' + color + '"></i>' + name;
            catDisplay.classList.remove('text-muted');
        });
    });

    // Format tiền tệ
    const displayAmount = document.getElementById('displayAmount');
    const realAmount = document.getElementById('realAmount');

    if (displayAmount && realAmount) {
        displayAmount.addEventListener('input', function(e) {
            // Remove non-digit characters
            let val = this.value.replace(/\D/g, '');
            realAmount.value = val;
            
            // Format with dots
            if (val !== '') {
                this.value = parseInt(val, 10).toLocaleString('vi-VN');
            } else {
                this.value = '';
            }
        });
    }
    // Live preview cho modal tạo nhanh danh mục
    const quickColorInput = document.getElementById('quickCatColor');
    const quickIconBox    = document.getElementById('quickIconPreviewBox');
    const quickIconInput  = document.getElementById('quickCatIcon');
    const quickIconEl     = document.getElementById('quickIconPreviewEl');

    if (quickColorInput && quickIconBox) {
        quickColorInput.addEventListener('input', e => quickIconBox.style.color = e.target.value);
    }
    if (quickIconInput && quickIconEl) {
        quickIconInput.addEventListener('input', e => {
            let cls = e.target.value.trim() || 'bi-tag';
            quickIconEl.className = cls.startsWith('bi-') ? 'bi ' + cls : 'bi bi-tag';
        });
    }
});
</script>
