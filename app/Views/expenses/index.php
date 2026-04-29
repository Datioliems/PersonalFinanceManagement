<?php
// ============================================================
// VIEW — app/Views/expenses/index.php
// ============================================================
// Biến nhận từ ExpenseController::index():
//   $items       — array giao dịch chi tiêu
//   $pager       — Paginator object
//   $filterMonth — 'Y-m' hoặc ''
//   $sort        — sort key hiện tại
// ============================================================
$pageTitle = $pageTitle ?? 'Chi tiêu';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-semibold mb-1">Chi tiêu</h2>
        <p class="text-muted small mb-0">
            Tổng: <?= number_format($pager->getTotal(), 0, ',', '.') ?> giao dịch
        </p>
    </div>
    <a href="<?= BASE_URL ?>/expenses/create" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Thêm chi tiêu
    </a>
</div>

<!-- Filter + Sort bar -->
<form method="GET" action="<?= BASE_URL ?>/expenses" class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-sm-auto">
                <label class="form-label small text-muted mb-1">Tháng</label>
                <input type="month" name="filter_month"
                       class="form-control form-control-sm"
                       value="<?= htmlspecialchars($filterMonth, ENT_QUOTES) ?>">
            </div>
            <div class="col-12 col-sm-auto">
                <label class="form-label small text-muted mb-1">Sắp xếp</label>
                <select name="sort" class="form-select form-select-sm">
                    <?php
                    $sorts = [
                        'date_desc'   => 'Ngày (mới nhất)',
                        'date_asc'    => 'Ngày (cũ nhất)',
                        'amount_desc' => 'Số tiền (cao nhất)',
                        'amount_asc'  => 'Số tiền (thấp nhất)',
                    ];
                    foreach ($sorts as $val => $label): ?>
                    <option value="<?= $val ?>"
                        <?= $sort === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="<?= BASE_URL ?>/expenses" class="btn btn-sm btn-outline-secondary">Xoá lọc</a>
            </div>
        </div>
    </div>
</form>

<!-- Danh sách -->
<?php if (empty($items)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
        Chưa có chi tiêu nào<?= $filterMonth ? " trong tháng {$filterMonth}" : '' ?>.
        <a href="<?= BASE_URL ?>/expenses/create">Thêm ngay</a>
    </div>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Ngày</th>
                    <th>Danh mục</th>
                    <th class="text-end">Số tiền</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // CSRF dùng chung cho tất cả form delete trong bảng
            $csrfDel = \App\Helpers\CsrfTokenManager::generate();
            foreach ($items as $item):
                $color = $item['category_color'] ?? '#6b7280';
            ?>
            <tr>
                <td class="ps-3 text-nowrap text-muted small">
                    <?= htmlspecialchars($item['trans_date'], ENT_QUOTES) ?>
                </td>
                <td>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="rounded-circle d-inline-block"
                              style="width:10px;height:10px;background:<?= htmlspecialchars($color, ENT_QUOTES) ?>"></span>
                        <?= htmlspecialchars($item['category_name'], ENT_QUOTES) ?>
                    </span>
                </td>
                <td class="text-end fw-medium text-danger">
                    -<?= number_format($item['amount'], 0, ',', '.') ?>đ
                </td>
                <td class="text-muted small text-truncate" style="max-width:200px">
                    <?= htmlspecialchars($item['note'] ?? '', ENT_QUOTES) ?>
                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <a href="<?= BASE_URL ?>/expenses/<?= (int)$item['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST"
                              action="<?= BASE_URL ?>/expenses/<?= (int)$item['id'] ?>/delete"
                              class="d-inline">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($csrfDel, ENT_QUOTES) ?>">
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger py-0 px-2"
                                    data-confirm="Xoá chi tiêu này?">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Phân trang -->
<?php if ($pager->getTotalPages() > 1): ?>
<div class="mt-3">
    <?= $pager->render('/expenses') ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
