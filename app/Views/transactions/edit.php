<?php
// ============================================================
// VIEW — app/Views/transactions/edit.php
// ============================================================
/** @var array $tx */
/** @var array $incomeCats */
/** @var array $expenseCats */
/** @var string $csrf */

$pageTitle = 'Sửa giao dịch';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center">
<div class="col-12 col-md-7 col-lg-5">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/transactions" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="h4 fw-semibold mb-0">Sửa giao dịch</h2>
</div>

<div class="card shadow-sm">
<div class="card-body p-4">
<form method="POST" action="<?= BASE_URL ?>/transactions/<?= (int)$tx['id'] ?>">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <div class="mb-3">
        <label class="form-label fw-medium">Số tiền (VNĐ) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white text-muted"><i class="bi bi-cash-stack"></i></span>
            <input type="number" name="amount" class="form-control"
                   min="1" required
                   value="<?= htmlspecialchars($tx['amount'], ENT_QUOTES) ?>">
            <span class="input-group-text bg-white text-muted">đ</span>
        </div>
    </div>

    <!-- Danh mục gộp (custom dropdown với icon) -->
    <div class="mb-3">
        <label class="form-label fw-medium">Danh mục <span class="text-danger">*</span></label>

        <input type="hidden" name="type_category_id" id="catHiddenInput" required
               value="<?= htmlspecialchars($tx['type'] . '_' . $tx['category_id'], ENT_QUOTES) ?>">
        <div class="dropdown">
            <button type="button" id="catDropdownBtn"
                    class="btn btn-outline-secondary w-100 text-start d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown" aria-expanded="false" style="min-height:38px">
                <span id="catDisplay" class="flex-grow-1">
                    <?php
                    // Pre-fill button label with current category
                    $allCats = array_merge(
                        array_map(fn($c) => $c + ['_txtype' => 'income'],  $incomeCats),
                        array_map(fn($c) => $c + ['_txtype' => 'expense'], $expenseCats)
                    );
                    $currentDisplay = '-- Chọn danh mục --';
                    foreach ($allCats as $c) {
                        if ($c['_txtype'] === $tx['type'] && (int)$c['id'] === (int)$tx['category_id']) {
                            $ci = htmlspecialchars($c['icon']  ?? 'bi-tag',   ENT_QUOTES);
                            $cc = htmlspecialchars($c['color'] ?? '#6b7280',  ENT_QUOTES);
                            $cn = htmlspecialchars($c['name'],                 ENT_QUOTES);
                            $currentDisplay = "<i class='{$ci} me-1' style='color:{$cc}'></i>{$cn}";
                            break;
                        }
                    }
                    echo $currentDisplay;
                    ?>
                </span>
                <i class="bi bi-chevron-down flex-shrink-0"></i>
            </button>
            <ul class="dropdown-menu w-100 shadow-sm" style="max-height:300px;overflow-y:auto;">
                <li><h6 class="dropdown-header">Thu nhập</h6></li>
                <?php foreach ($incomeCats as $cat):
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',  ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#16a34a', ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                ENT_QUOTES);
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
                    $icon  = htmlspecialchars($cat['icon']  ?? 'bi-tag',  ENT_QUOTES);
                    $color = htmlspecialchars($cat['color'] ?? '#dc2626', ENT_QUOTES);
                    $name  = htmlspecialchars($cat['name'],                ENT_QUOTES);
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

    <div class="mb-3">
        <label class="form-label fw-medium">Ngày <span class="text-danger">*</span></label>
        <input type="date" name="trans_date" class="form-control" required
               value="<?= htmlspecialchars($tx['trans_date'], ENT_QUOTES) ?>">
    </div>

    <div class="mb-4">
        <label class="form-label fw-medium">Ghi chú</label>
        <textarea name="note" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars($tx['note'] ?? '', ENT_QUOTES) ?></textarea>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-dark">
            <i class="bi bi-check-lg me-1"></i>Cập nhật
        </button>
        <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-secondary">Huỷ</a>
    </div>
</form>
</div>
</div>

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
