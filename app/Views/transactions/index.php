<?php
// ============================================================
// VIEW — app/Views/transactions/index.php
// ============================================================
/** @var array $items */
/** @var App\Helpers\Paginator $pager */
/** @var string $filterMonth */
/** @var string $sort */
/** @var array $summary */
/** @var int $monthNow */
/** @var int $yearNow */

$pageTitle = $pageTitle ?? 'Giao dịch';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- Card tóm tắt tổng thu/chi tháng -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 bg-success bg-opacity-10 h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-down-circle me-1 text-success"></i>
                    Tổng thu <?= $monthNow ?>/<?= $yearNow ?>
                </div>
                <div class="h4 fw-semibold text-success mb-0">
                    +<?= number_format($summary['income'] ?? 0, 0, ',', '.') ?>đ
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 bg-danger bg-opacity-10 h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-up-circle me-1 text-danger"></i>
                    Tổng chi <?= $monthNow ?>/<?= $yearNow ?>
                </div>
                <div class="h4 fw-semibold text-danger mb-0">
                    -<?= number_format($summary['expense'] ?? 0, 0, ',', '.') ?>đ
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 bg-secondary bg-opacity-10 h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-list-ul me-1"></i>
                    Số giao dịch
                </div>
                <div class="h4 fw-semibold mb-0">
                    <?= number_format($pager->getTotal(), 0, ',', '.') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header + nút thêm -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 fw-semibold mb-0">Lịch sử giao dịch</h2>
    <a href="<?= BASE_URL ?>/transactions/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Thêm giao dịch
    </a>
</div>

<!-- Filter + Sort -->
<form method="GET" action="<?= BASE_URL ?>/transactions" class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Tháng</label>
                <input type="month" name="filter_month" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($filterMonth, ENT_QUOTES) ?>">
            </div>
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Loại</label>
                <select name="filter_type" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    <option value="income"  <?= ($filterType ?? '') === 'income'  ? 'selected' : '' ?>>Thu nhập</option>
                    <option value="expense" <?= ($filterType ?? '') === 'expense' ? 'selected' : '' ?>>Chi tiêu</option>
                </select>
            </div>
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Danh mục</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="0">Tất cả danh mục</option>
                    <?php foreach (($cats ?? []) as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($catFilter ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name'], ENT_QUOTES) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Sắp xếp</label>
                <select name="sort" class="form-select form-select-sm">
                    <?php foreach (['date_desc'=>'Mới nhất','date_asc'=>'Cũ nhất','amount_desc'=>'Tiền cao','amount_asc'=>'Tiền thấp'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $sort === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="<?= BASE_URL ?>/transactions" class="btn btn-sm btn-outline-secondary">Xoá lọc</a>
            </div>
        </div>
    </div>
</form>

<!-- Tổng thu/chi từng ngày -->
<?php if (!empty($dailySummary)): ?>
<div class="card mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <span class="fw-medium small"><i class="bi bi-calendar3 me-1"></i>Tổng thu/chi từng ngày — <?= htmlspecialchars($filterMonth) ?></span>
        <button class="btn btn-sm btn-link p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#dailySummary">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div id="dailySummary" class="collapse show">
        <div class="table-responsive">
            <table class="table table-sm mb-0 small">
                <thead class="table-light">
                    <tr><th class="ps-3">Ngày</th><th class="text-end text-success">Thu</th><th class="text-end text-danger">Chi</th><th class="text-end">Số dư ngày</th></tr>
                </thead>
                <tbody>
                <?php foreach ($dailySummary as $d): $bal = (float)$d['balance']; ?>
                <tr>
                    <td class="ps-3 text-muted"><?= htmlspecialchars($d['trans_date']) ?></td>
                    <td class="text-end text-success"><?= $d['income']  > 0 ? '+'.number_format($d['income'], 0,',','.').'đ'  : '—' ?></td>
                    <td class="text-end text-danger"> <?= $d['expense'] > 0 ? '-'.number_format($d['expense'],0,',','.').'đ' : '—' ?></td>
                    <td class="text-end fw-medium <?= $bal >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= ($bal >= 0 ? '+' : '').number_format($bal, 0, ',', '.') ?>đ
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if (empty($items)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
        Chưa có giao dịch nào<?= $filterMonth ? " trong tháng {$filterMonth}" : '' ?>.
        <a href="<?= BASE_URL ?>/transactions/create">Thêm ngay</a>
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
            $csrfDel = \App\Helpers\CsrfTokenManager::generate();
            foreach ($items as $item):
                $color = $item['category_color'] ?? '#6b7280';
                $isIncome = ($item['type'] === 'income');
                $amountClass = $isIncome ? 'text-success' : 'text-danger';
                $amountPrefix = $isIncome ? '+' : '-';
            ?>
            <tr>
                <td class="ps-3 text-nowrap text-muted small">
                    <?= htmlspecialchars($item['trans_date'], ENT_QUOTES) ?>
                </td>
                <td>
                    <span class="badge rounded-pill fw-normal"
                          style="background:<?= htmlspecialchars($color,ENT_QUOTES) ?>22;
                                 color:<?= htmlspecialchars($color,ENT_QUOTES) ?>;
                                 border:1px solid <?= htmlspecialchars($color,ENT_QUOTES) ?>66;
                                 padding:4px 10px">
                        <?= htmlspecialchars($item['category_name'], ENT_QUOTES) ?>
                    </span>
                    <span class="badge rounded-pill ms-1"
                          style="background:<?= $isIncome ? '#dcfce7' : '#fee2e2' ?>;
                                 color:<?= $isIncome ? '#16a34a' : '#dc2626' ?>;
                                 font-size:0.65em;padding:3px 7px">
                        <?= $isIncome ? 'Thu' : 'Chi' ?>
                    </span>
                </td>
                <td class="text-end fw-medium <?= $amountClass ?>">
                    <?= $amountPrefix ?><?= number_format($item['amount'], 0, ',', '.') ?>đ
                </td>
                <td class="text-muted small text-truncate" style="max-width:200px">
                    <?= htmlspecialchars($item['note'] ?? '', ENT_QUOTES) ?>
                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <a href="<?= BASE_URL ?>/transactions/<?= (int)$item['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST"
                              action="<?= BASE_URL ?>/transactions/<?= (int)$item['id'] ?>/delete"
                              class="d-inline">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($csrfDel, ENT_QUOTES) ?>">
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger py-0 px-2"
                                    data-confirm="Xoá giao dịch này?">
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

<?php if ($pager->getTotalPages() > 1): ?>
<div class="mt-3"><?= $pager->render('/transactions') ?></div>
<?php endif; ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
