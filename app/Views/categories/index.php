<?php
// ============================================================
// VIEW — app/Views/categories/index.php
// ============================================================
// Biến nhận từ CategoryController::index() qua extract():
/** @var array  $cats  — danh sách danh mục của user */
/** @var string $csrf  — CSRF token (one-time) */
// ============================================================
$pageTitle = $pageTitle ?? 'Danh mục';
$extraCss  = BASE_URL . '/css/categories.css';   // nạp CSS riêng qua layout
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- ── Header ──────────────────────────────────────────────── -->
<div class="page-header-shared">
    <div>
        <h1 class="page-title">
            <i class="bi bi-tags me-2" style="color:#6366f1"></i>Danh mục
        </h1>
        <p class="page-subtitle">Quản lý danh mục thu / chi cá nhân của bạn</p>
    </div>
    <button class="btn-add-cat" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg"></i> Thêm danh mục
    </button>
</div>

<!-- ── Danh sách danh mục ──────────────────────────────────── -->
<?php if (empty($cats)): ?>
<div class="cat-empty">
    <i class="bi bi-tags cat-empty-icon"></i>
    <h3>Chưa có danh mục nào</h3>
    <p>Tạo danh mục để phân loại thu nhập và chi tiêu dễ dàng hơn.</p>
    <button class="btn-add-cat" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg"></i> Tạo danh mục đầu tiên
    </button>
</div>

<?php else: ?>
<?php
$incomeCats = array_filter($cats, fn($c) => $c['type'] === 'income');
$expenseCats = array_filter($cats, fn($c) => $c['type'] === 'expense');
$bothCats = array_filter($cats, fn($c) => $c['type'] === 'both');

function renderCatCard($cat, $csrf) {
    $color      = htmlspecialchars($cat['color'] ?? '#6366f1', ENT_QUOTES);
    $icon       = htmlspecialchars($cat['icon']  ?? 'bi-tag',  ENT_QUOTES);
    $name       = htmlspecialchars($cat['name'],                ENT_QUOTES);
    $colorBg    = $color . '1e';
    [$badgeClass, $badgeLabel] = match($cat['type']) {
        'income'  => ['cat-badge-income',  'Thu nhập'],
        'expense' => ['cat-badge-expense', 'Chi tiêu'],
        default   => ['cat-badge-both',    'Cả hai'],
    };
    ?>
    <div class="cat-card" style="--cat-color:<?= $color ?>; --cat-color-bg:<?= $colorBg ?>">
        <div class="cat-icon-wrap"><i class="<?= $icon ?>"></i></div>
        <div class="cat-info">
            <div class="cat-name" title="<?= $name ?>"><?= $name ?></div>
            <span class="cat-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
        </div>
        <div class="cat-actions">
            <a href="<?= BASE_URL ?>/categories/<?= (int)$cat['id'] ?>/edit" class="cat-btn cat-btn-edit" title="Sửa"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="<?= BASE_URL ?>/categories/<?= (int)$cat['id'] ?>/delete" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
                <button type="submit" class="cat-btn cat-btn-del" title="Xoá" data-confirm="Xoá danh mục '<?= $name ?>'?"><i class="bi bi-trash3"></i></button>
            </form>
        </div>
    </div>
    <?php
}
?>

<div class="row g-4" id="catGrid">
    <div class="col-12 col-md-6">
        <h4 class="h5 fw-semibold mb-3 text-danger"><i class="bi bi-arrow-up-circle me-2"></i>Danh mục Chi tiêu</h4>
        <div class="d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php foreach ($expenseCats as $cat) renderCatCard($cat, $csrf); ?>
            <?php if (empty($expenseCats)): ?>
                <div class="text-muted small">Chưa có danh mục chi tiêu nào.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <h4 class="h5 fw-semibold mb-3 text-success"><i class="bi bi-arrow-down-circle me-2"></i>Danh mục Thu nhập</h4>
        <div class="d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php foreach ($incomeCats as $cat) renderCatCard($cat, $csrf); ?>
            <?php if (empty($incomeCats)): ?>
                <div class="text-muted small">Chưa có danh mục thu nhập nào.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($bothCats)): ?>
    <div class="col-12">
        <h4 class="h5 fw-semibold mb-3 text-primary"><i class="bi bi-arrow-left-right me-2"></i>Danh mục Cả hai</h4>
        <div class="d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php foreach ($bothCats as $cat) renderCatCard($cat, $csrf); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Modal: Tạo danh mục mới ────────────────────────────── -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-labelledby="modalCreateLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalCreateLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Thêm danh mục mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/categories">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

                <div class="modal-body">

                    <!-- Tên danh mục -->
                    <div class="mb-3">
                        <label for="catName" class="form-label fw-semibold">
                            Tên danh mục <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="catName" name="name" class="form-control"
                               placeholder="VD: Ăn uống, Đi lại, Lương..."
                               required minlength="2" maxlength="100" autocomplete="off">
                    </div>

                    <!-- Loại -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Loại</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="type" id="typeExpense" value="expense" checked>
                                <label class="form-check-label" for="typeExpense">
                                    <span class="cat-badge cat-badge-expense">Chi tiêu</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="type" id="typeIncome" value="income">
                                <label class="form-check-label" for="typeIncome">
                                    <span class="cat-badge cat-badge-income">Thu nhập</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Màu sắc + Icon (cùng hàng) -->
                    <div class="row g-3">
                        <div class="col-auto">
                            <label for="catColor" class="form-label fw-semibold">Màu sắc</label>
                            <input type="color" id="catColor" name="color"
                                   class="form-control form-control-color"
                                   value="#6366f1" title="Chọn màu" style="height: 38px; width: 60px;">
                        </div>

                        <div class="col">
                            <label for="catIcon" class="form-label fw-semibold">Icon</label>
                            <div class="input-group" style="cursor:pointer"
                                 data-icon-picker="catIcon">
                                <span class="input-group-text bg-white border-end-0 px-2" id="iconPreviewBox" style="color: #6366f1;">
                                    <i class="bi bi-tag" id="iconPreviewEl"></i>
                                </span>
                                <input type="text" id="catIcon" name="icon" class="form-control border-start-0 border-end-0 ps-0"
                                       placeholder="Chọn icon..." autocomplete="off" readonly style="cursor: pointer; background-color: #fff;">
                                <span class="input-group-text bg-white text-muted border-start-0">
                                    <i class="bi bi-chevron-down" style="font-size: .8rem"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                </div><!-- /.modal-body -->

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

<!-- ── JS: stagger animation + live preview icon/màu ───────── -->
<script>
(function () {
    /* Stagger animation delay cho từng card */
    document.querySelectorAll('#catGrid .cat-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.05) + 's';
    });

    /* Live preview màu sắc */
    const colorInput = document.getElementById('catColor');
    const iconBox    = document.getElementById('iconPreviewBox');

    if (colorInput && iconBox) {
        colorInput.addEventListener('input', function (e) {
            iconBox.style.color = e.target.value;
        });
    }

    /* Live preview icon */
    const iconInput = document.getElementById('catIcon');
    const iconEl    = document.getElementById('iconPreviewEl');

    if (iconInput && iconEl) {
        iconInput.addEventListener('input', function (e) {
            var cls = e.target.value.trim() || 'bi-tag';
            iconEl.className = cls.startsWith('bi-') ? 'bi ' + cls : 'bi bi-tag';
        });
    }
})();
</script>
