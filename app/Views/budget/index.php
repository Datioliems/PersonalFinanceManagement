<?php
// ============================================================
// VIEW — app/Views/budget/index.php
// ============================================================
// Biến nhận từ BudgetController::index():
//   $summary — array budget + spent + pct + status_class
//   $cats    — array tất cả danh mục (cho dropdown)
//   $csrf    — CSRF token
//   $month, $year
// ============================================================
$pageTitle = $pageTitle ?? 'Ngân sách';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- Header + điều hướng tháng -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-semibold mb-1">Ngân sách</h2>
        <p class="text-muted small mb-0">Hạn mức chi tiêu theo danh mục</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Điều hướng tháng -->
        <?php
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear  = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear  = $month == 12 ? $year + 1 : $year;
        ?>
        <a href="<?= BASE_URL ?>/budget?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left"></i>
        </a>
        <span class="fw-medium px-2">
            Tháng <?= $month ?>/<?= $year ?>
        </span>
        <a href="<?= BASE_URL ?>/budget?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-right"></i>
        </a>
        <button class="btn btn-dark btn-sm ms-2"
                data-bs-toggle="modal" data-bs-target="#modalSetLimit">
            <i class="bi bi-plus-lg me-1"></i> Đặt hạn mức
        </button>
    </div>
</div>

<!-- Danh sách ngân sách với thanh tiến độ -->
<?php if (empty($summary)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-piggy-bank fs-1 d-block mb-2"></i>
        Chưa có ngân sách nào cho tháng <?= $month ?>/<?= $year ?>.
        <a href="#" data-bs-toggle="modal" data-bs-target="#modalSetLimit">Đặt hạn mức ngay</a>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($summary as $row): ?>
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
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 <?= $isExceeded ? 'border-danger' : '' ?>">
            <div class="card-body">
                <!-- Header danh mục -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;background:<?= $catColor ?>22">
                        <i class="<?= $catIcon ?>"
                           style="color:<?= $catColor ?>"></i>
                    </div>
                    <span class="fw-medium"><?= $catName ?></span>
                    <?php if ($isExceeded): ?>
                    <span class="badge bg-danger ms-auto">Vượt ngưỡng!</span>
                    <?php endif; ?>
                </div>

                <!-- Số tiền -->
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Đã chi: <strong class="text-<?= $statusClass ?>">
                        <?= number_format($spent, 0, ',', '.') ?>đ
                    </strong></span>
                    <span>Hạn mức: <?= number_format($limit, 0, ',', '.') ?>đ</span>
                </div>

                <!-- Thanh tiến độ — màu từ CSS class, không hardcode -->
                <div class="progress" style="height:10px" role="progressbar"
                     aria-valuenow="<?= min($pct, 100) ?>"
                     aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-<?= $statusClass ?>"
                         style="width:<?= min($pct, 100) ?>%;transition:width .4s ease">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-<?= $statusClass ?> fw-medium">
                        <?= number_format($pct, 1) ?>%
                    </small>
                    <small class="text-muted">
                        Cảnh báo &ge; <?= (int)$row['alert_threshold'] ?>%
                    </small>
                </div>

                <!-- Xoá budget -->
                <form method="POST"
                      action="<?= BASE_URL ?>/budget/<?= (int)$row['id'] ?>/delete"
                      class="mt-3 text-end">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <button type="submit"
                            class="btn btn-sm btn-outline-secondary py-0"
                            data-confirm="Xoá hạn mức ngân sách cho '<?= $catName ?>'?">
                        <i class="bi bi-trash3"></i> Xoá
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
                            <?php foreach ($cats as $cat): ?>
                            <?php if ($cat['type'] !== 'income'): ?>
                            <option value="<?= (int)$cat['id'] ?>">
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Hạn mức (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="limit_amount"
                               class="form-control"
                               min="1000" step="1000"
                               placeholder="VD: 2000000"
                               required>
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
                        Nếu đã có hạn mức cho danh mục này tháng <?= $month ?>/<?= $year ?>,
                        sẽ được cập nhật.
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

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
