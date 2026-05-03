<?php
// ============================================================
// VIEW — app/Views/budget/index.php
// ============================================================
/**
 * Biến nhận từ BudgetController::index():
 * @var array  $summary — array budget + spent + pct + status_class
 * @var array  $cats    — array tất cả danh mục (cho dropdown)
 * @var string $csrf    — CSRF token
 * @var int    $month   — Tháng hiện tại
 * @var int    $year    — Năm hiện tại
 * @var string|null $pageTitle
 */
// ============================================================
$pageTitle = $pageTitle ?? 'Ngân sách';
$month    = $month ?? date('n');
$year     = $year  ?? date('Y');
$extraCss = BASE_URL . '/css/budget.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- Header + điều hướng tháng -->
<div class="page-header-shared">
    <div>
        <h1 class="page-title">
            <i class="bi bi-pie-chart me-2" style="color:#f59e0b"></i>Ngân sách
        </h1>
        <p class="page-subtitle">Quản lý hạn mức chi tiêu theo danh mục</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Điều hướng tháng -->
        <?php
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear  = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear  = $month == 12 ? $year + 1 : $year;
        ?>
        <div class="month-nav">
            <a href="<?= BASE_URL ?>/budget?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"
               class="btn-nav" title="Tháng trước">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="month-label">
                Tháng <?= $month ?>/<?= $year ?>
            </span>
            <a href="<?= BASE_URL ?>/budget?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"
               class="btn-nav" title="Tháng sau">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <button class="btn btn-dark" style="border-radius:10px; padding:.45rem 1rem;"
                data-bs-toggle="modal" data-bs-target="#modalSetLimit">
            <i class="bi bi-plus-lg me-1"></i> Đặt hạn mức
        </button>
    </div>
</div>

<!-- Danh sách ngân sách với thanh tiến độ -->
<?php if (empty($summary)): ?>
    <div class="budget-empty">
        <i class="bi bi-piggy-bank budget-empty-icon"></i>
        <h3>Chưa có ngân sách nào</h3>
        <p>Đặt hạn mức chi tiêu để kiểm soát tài chính tốt hơn trong tháng <?= $month ?>/<?= $year ?>.</p>
        <button class="btn btn-dark mt-2" style="border-radius:10px;" data-bs-toggle="modal" data-bs-target="#modalSetLimit">
            <i class="bi bi-plus-lg me-1"></i> Đặt hạn mức ngay
        </button>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($summary as $index => $row): ?>
    <?php
        $pct         = (float)$row['pct'];
        $statusClass = $row['status_class'];  // success | warning | danger
        $isExceeded  = $row['is_exceeded'];
        $spent       = (float)$row['spent'];
        $limit       = (float)$row['limit_amount'];
        $catName     = htmlspecialchars($row['category_name'], ENT_QUOTES);
        $catColor    = htmlspecialchars($row['category_color'] ?? '#6b7280', ENT_QUOTES);
        $catIcon     = htmlspecialchars($row['category_icon']  ?? 'bi-tag', ENT_QUOTES);
    ?>
    <div class="col-12 col-md-6 col-xl-4" style="animation-delay: <?= $index * 0.05 ?? 0 ?>s">
        <div class="budget-card <?= $isExceeded ? 'is-exceeded' : '' ?>">
            
            <!-- Header danh mục -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="budget-cat-icon" style="--cat-color:<?= $catColor ?>; --cat-color-bg:<?= $catColor ?>22;">
                    <i class="<?= $catIcon ?>"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate text-dark" style="font-size: 1.05rem;" title="<?= $catName ?>"><?= $catName ?></div>
                    <div class="small text-muted">Hạn mức: <?= number_format($limit, 0, ',', '.') ?>đ</div>
                </div>
                <?php if ($isExceeded): ?>
                    <span class="exceeded-badge"><i class="bi bi-exclamation-triangle"></i> Vượt!</span>
                <?php endif; ?>
            </div>

            <!-- Số tiền đã chi -->
            <div class="d-flex justify-content-between align-items-end mb-1">
                <span class="small text-muted">Đã chi tiêu</span>
                <strong class="fs-5 text-<?= $statusClass ?> <?= $isExceeded ? 'text-danger' : '' ?>">
                    <?= number_format($spent, 0, ',', '.') ?>đ
                </strong>
            </div>

            <!-- Thanh tiến độ từ CSS custom -->
            <div class="budget-progress">
                <div class="budget-progress-bar <?= $statusClass ?>"
                     style="width:<?= min($pct, 100) ?>%;">
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-2 align-items-center">
                <small class="text-<?= $statusClass ?> fw-bold <?= $isExceeded ? 'text-danger' : '' ?>">
                    <?= number_format($pct, 1) ?>%
                </small>
                <?php $remaining = $limit - $spent; ?>
                <small class="fw-semibold <?= $remaining >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $remaining >= 0 ? 'Còn lại: ' : 'Vượt: ' ?><?= number_format(abs($remaining), 0, ',', '.') ?>đ
                </small>
            </div>
            
            <div class="text-end mt-1">
                <small class="text-muted" style="font-size: 0.72rem;">
                    Ngưỡng cảnh báo: <?= (int)$row['alert_threshold'] ?>%
                </small>
            </div>

            <!-- Thao tác -->
            <div class="budget-card-actions mt-3 pt-3 border-top">
                <button type="button" class="btn btn-sm btn-light text-secondary border"
                        style="border-radius:8px"
                        data-bs-toggle="modal" data-bs-target="#modalEditLimit<?= (int)$row['id'] ?>">
                    <i class="bi bi-pencil me-1"></i>Sửa
                </button>
                <form method="POST"
                      action="<?= BASE_URL ?>/budget/<?= (int)$row['id'] ?>/delete"
                      class="m-0">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <button type="submit"
                            class="btn btn-sm btn-light text-danger border border-danger-subtle"
                            style="border-radius:8px; background:#fff5f5"
                            data-confirm="Xoá hạn mức ngân sách cho '<?= $catName ?>'?">
                        <i class="bi bi-trash3 me-1"></i>Xoá
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal đặt hạn mức -->
<div class="modal fade" id="modalSetLimit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Đặt hạn mức ngân sách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/budget">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                <input type="hidden" name="month" value="<?= $month ?>">
                <input type="hidden" name="year"  value="<?= $year ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Danh mục <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục chi tiêu --</option>
                            <?php 
                            $budgetedCatIds = array_column($summary ?? [], 'category_id');
                            $hasAvailable = false;
                            foreach ($cats as $cat): 
                                if ($cat['type'] !== 'income' && !in_array($cat['id'], $budgetedCatIds)):
                                    $hasAvailable = true;
                            ?>
                            <option value="<?= (int)$cat['id'] ?>">
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                            </option>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$hasAvailable):
                            ?>
                            <option value="" disabled>Đã đặt ngân sách cho tất cả danh mục</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Hạn mức (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control currency-input"
                               placeholder="VD: 2.000.000"
                               required>
                        <input type="hidden" name="limit_amount" class="real-amount">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium d-flex justify-content-between">
                            <span>Ngưỡng cảnh báo</span>
                            <span id="thresholdVal" class="text-muted">80%</span>
                        </label>
                        <input type="range" name="alert_threshold"
                               class="form-range"
                               min="10" max="100" step="5" value="80"
                               oninput="document.getElementById('thresholdVal').textContent = this.value + '%'">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">10% (cảnh báo sớm)</small>
                            <small class="text-muted">100% (khi vượt)</small>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Hạn mức sẽ được áp dụng cho toàn bộ tháng <?= $month ?>/<?= $year ?>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-check-lg me-1"></i>Lưu hạn mức
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Render Modals Sửa Hạn Mức -->
<?php if (!empty($summary)): ?>
<?php foreach ($summary as $row): ?>
<div class="modal fade" id="modalEditLimit<?= (int)$row['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Sửa hạn mức ngân sách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/budget">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                <input type="hidden" name="month" value="<?= $month ?>">
                <input type="hidden" name="year"  value="<?= $year ?>">
                <input type="hidden" name="category_id" value="<?= (int)$row['category_id'] ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Danh mục</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Hạn mức (VNĐ) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control currency-input" value="<?= number_format((int)$row['limit_amount'], 0, ',', '.') ?>" required>
                        <input type="hidden" name="limit_amount" class="real-amount" value="<?= (int)$row['limit_amount'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium d-flex justify-content-between">
                            <span>Ngưỡng cảnh báo</span>
                            <span id="editThresholdVal<?= (int)$row['id'] ?>" class="text-muted"><?= (int)$row['alert_threshold'] ?>%</span>
                        </label>
                        <input type="range" name="alert_threshold" class="form-range" min="10" max="100" step="5" value="<?= (int)$row['alert_threshold'] ?>"
                               oninput="document.getElementById('editThresholdVal<?= (int)$row['id'] ?>').textContent = this.value + '%'">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg me-1"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.currency-input').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let val = this.value.replace(/\D/g, '');
            let hiddenInput = this.parentElement.querySelector('.real-amount');
            if (hiddenInput) {
                hiddenInput.value = val;
            }
            if (val !== '') {
                this.value = parseInt(val, 10).toLocaleString('vi-VN');
            } else {
                this.value = '';
            }
        });
    });
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
