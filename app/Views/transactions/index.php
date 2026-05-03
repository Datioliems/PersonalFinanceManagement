<?php
// ============================================================
// VIEW — app/Views/transactions/index.php
// ============================================================
/** 
 * @var array $items 
 * @var App\Helpers\Paginator $pager 
 * @var string $startDate 
 * @var string $endDate 
 * @var string $filterType 
 * @var string $sort 
 * @var int $catFilter 
 * @var array $cats 
 * @var array $dailySummary 
 * @var array $summary 
 * @var string $pageTitle 
 */

$pageTitle = $pageTitle ?? 'Giao dịch';
$extraCss  = BASE_URL . '/css/transactions.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 h-100" style="background:#f0fdf4">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-down-circle me-1 text-success"></i>Tổng thu nhập
                </div>
                <div class="h3 fw-semibold text-success mb-0">
                    +<?= number_format($summary['income'] ?? 0, 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Kỳ được chọn</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 h-100" style="background:#fef2f2">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-up-circle me-1 text-danger"></i>Tổng chi tiêu
                </div>
                <div class="h3 fw-semibold text-danger mb-0">
                    -<?= number_format($summary['expense'] ?? 0, 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Kỳ được chọn</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <?php $bal = $summary['balance'] ?? 0; $balPositive = $bal >= 0; ?>
        <div class="card border-0 h-100" style="background:<?= $balPositive ? '#eff6ff' : '#fef2f2' ?>">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-wallet2 me-1 <?= $balPositive ? 'text-primary' : 'text-danger' ?>"></i>Chênh lệch
                </div>
                <div class="h3 fw-semibold mb-0 <?= $balPositive ? 'text-primary' : 'text-danger' ?>">
                    <?= ($balPositive ? '+' : '') . number_format($bal, 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Kỳ được chọn</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 h-100" style="background:#f8fafc">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-list-ul me-1 text-secondary"></i>Số giao dịch
                </div>
                <div class="h3 fw-semibold text-dark mb-0">
                    <?= number_format($pager->getTotal(), 0, ',', '.') ?>
                </div>
                <div class="small text-muted mt-1">Kỳ được chọn</div>
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
<form method="GET" action="<?= BASE_URL ?>/transactions" class="card tx-filter-card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Từ ngày</label>
                <input type="date" name="start_date" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($startDate ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-6 col-sm-auto">
                <label class="form-label small text-muted mb-1">Đến ngày</label>
                <input type="date" name="end_date" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($endDate ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-6 col-sm-auto" style="min-width: 140px;">
                <label class="form-label small text-muted mb-1">Loại</label>
                <input type="hidden" name="filter_type" id="typeHiddenInput" value="<?= $filterType ?? '' ?>">
                <div class="dropdown">
                    <button type="button" id="typeDropdownBtn"
                            class="form-select form-select-sm text-start w-100 shadow-none d-flex align-items-center"
                            data-bs-toggle="dropdown" aria-expanded="false" style="padding-right: 2rem;">
                        <span id="typeDisplay" class="text-truncate w-100">
                            <?php
                            if (($filterType ?? '') === 'income') {
                                echo '<i class="bi bi-arrow-down-circle text-success me-2"></i>Thu nhập';
                            } elseif (($filterType ?? '') === 'expense') {
                                echo '<i class="bi bi-arrow-up-circle text-danger me-2"></i>Chi tiêu';
                            } else {
                                echo 'Tất cả';
                            }
                            ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu shadow" style="font-size: 0.9rem; min-width: 100%; border-radius: 12px; padding: 8px;">
                        <li><a class="dropdown-item type-option rounded-2 mb-1" href="#" data-value="">Tất cả</a></li>
                        <li><a class="dropdown-item type-option rounded-2 mb-1 d-flex align-items-center gap-2" href="#" data-value="income">
                            <i class="bi bi-arrow-down-circle text-success" style="width:16px"></i><span>Thu nhập</span>
                        </a></li>
                        <li><a class="dropdown-item type-option rounded-2 mb-1 d-flex align-items-center gap-2" href="#" data-value="expense">
                            <i class="bi bi-arrow-up-circle text-danger" style="width:16px"></i><span>Chi tiêu</span>
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-sm-auto" style="min-width: 180px;">
                <label class="form-label small text-muted mb-1">Danh mục</label>
                <input type="hidden" name="category_id" id="catHiddenInput" value="<?= $catFilter ?? 0 ?>">
                <div class="dropdown">
                    <button type="button" id="catDropdownBtn"
                            class="form-select form-select-sm text-start w-100 shadow-none d-flex align-items-center"
                            data-bs-toggle="dropdown" aria-expanded="false" style="padding-right: 2rem;">
                        <span id="catDisplay" class="text-truncate w-100">
                            <?php
                            $selectedName = 'Tất cả danh mục';
                            $selectedIcon = '';
                            $selectedColor = '';
                            if (($catFilter ?? 0) !== 0) {
                                foreach ($cats ?? [] as $c) {
                                    if ((int)$c['id'] === (int)$catFilter) {
                                        $selectedName = $c['name'];
                                        $selectedIcon = $c['icon'] ?? 'bi-tag';
                                        $selectedColor = $c['color'] ?? '#64748b';
                                        break;
                                    }
                                }
                            }
                            if ($selectedIcon) {
                                echo '<i class="' . htmlspecialchars($selectedIcon) . ' me-2" style="color:' . htmlspecialchars($selectedColor) . '"></i>';
                            }
                            echo htmlspecialchars($selectedName);
                            ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu shadow" style="max-height:300px;overflow-y:auto; font-size: 0.9rem; min-width: 100%; border-radius: 12px; padding: 8px;">
                        <li>
                            <a class="dropdown-item cat-option rounded-2 mb-1" href="#" data-value="0" data-type="all">
                                Tất cả danh mục
                            </a>
                        </li>
                        <?php foreach (($cats ?? []) as $c): 
                            $icon = htmlspecialchars($c['icon'] ?? 'bi-tag', ENT_QUOTES);
                            $color = htmlspecialchars($c['color'] ?? '#64748b', ENT_QUOTES);
                            $name = htmlspecialchars($c['name'], ENT_QUOTES);
                        ?>
                        <li class="cat-item" data-type="<?= $c['type'] ?>">
                            <a class="dropdown-item cat-option d-flex align-items-center gap-2 rounded-2 mb-1" href="#"
                               data-value="<?= (int)$c['id'] ?>"
                               data-icon="<?= $icon ?>" data-color="<?= $color ?>" data-name="<?= $name ?>">
                                <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i>
                                <span><?= $name ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-6 col-sm-auto" style="min-width: 150px;">
                <label class="form-label small text-muted mb-1">Sắp xếp</label>
                <input type="hidden" name="sort" id="sortHiddenInput" value="<?= $sort ?? 'date_desc' ?>">
                <div class="dropdown">
                    <button type="button" id="sortDropdownBtn"
                            class="form-select form-select-sm text-start w-100 shadow-none d-flex align-items-center"
                            data-bs-toggle="dropdown" aria-expanded="false" style="padding-right: 2rem;">
                        <span id="sortDisplay" class="text-truncate w-100">
                            <?php
                            $sortMap = [
                                'date_desc' => 'Mới nhất',
                                'date_asc' => 'Cũ nhất',
                                'amount_desc' => 'Tiền cao',
                                'amount_asc' => 'Tiền thấp'
                            ];
                            echo $sortMap[$sort ?? 'date_desc'] ?? 'Mới nhất';
                            ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu shadow" style="font-size: 0.9rem; min-width: 100%; border-radius: 12px; padding: 8px;">
                        <?php foreach ($sortMap as $v => $l): ?>
                        <li><a class="dropdown-item sort-option rounded-2 mb-1" href="#" data-value="<?= $v ?>"><?= $l ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
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
        <span class="fw-medium small"><i class="bi bi-calendar3 me-1"></i>Tổng thu/chi từng ngày</span>
        <button class="btn btn-sm btn-link p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#dailySummary">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div id="dailySummary" class="collapse show">
        <div class="table-responsive">
            <table class="table table-sm mb-0 small">
                <thead class="table-light">
                    <tr><th class="ps-3">Ngày</th><th class="text-center">Số GD</th><th class="text-end text-success">Thu</th><th class="text-end text-danger">Chi</th><th class="text-end">Số dư ngày</th></tr>
                </thead>
                <tbody>
                <?php foreach ($dailySummary as $d): $bal = (float)$d['balance']; ?>
                <tr>
                    <td class="ps-3"><a href="<?= BASE_URL ?>/transactions?start_date=<?= $d['trans_date'] ?>&end_date=<?= $d['trans_date'] ?>" class="text-decoration-none fw-medium"><?= htmlspecialchars($d['trans_date']) ?></a></td>
                    <td class="text-center text-muted small"><?= $d['total_tx'] ?></td>
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
        Chưa có giao dịch nào trong khoảng thời gian này.
        <a href="<?= BASE_URL ?>/transactions/create">Thêm ngay</a>
    </div>
<?php else: ?>
<div class="card border-0 bg-transparent shadow-none">
    <div class="table-responsive-md">
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
                          style="background:<?= htmlspecialchars($color,ENT_QUOTES) ?>;
                                 color:#fff;
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

<div class="mt-4 mb-3"><?= $pager->render(BASE_URL . '/transactions') ?></div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Type Dropdown
    const hiddenType = document.getElementById('typeHiddenInput');
    const typeDisplay = document.getElementById('typeDisplay');
    const typeOptions = document.querySelectorAll('.type-option');
    
    // Category Dropdown
    const hiddenCat = document.getElementById('catHiddenInput');
    const catDisplay = document.getElementById('catDisplay');
    const catItems = document.querySelectorAll('.cat-item');
    const catOptions = document.querySelectorAll('.cat-option');
    
    // Sort Dropdown
    const hiddenSort = document.getElementById('sortHiddenInput');
    const sortDisplay = document.getElementById('sortDisplay');
    const sortOptions = document.querySelectorAll('.sort-option');

    function updateCats() {
        if (!hiddenType || !hiddenCat) return;
        const type = hiddenType.value;
        let hasSelected = false;
        catItems.forEach(item => {
            if (type === '' || item.dataset.type === type || item.dataset.type === 'both') {
                item.style.display = '';
                const optionLink = item.querySelector('.cat-option');
                if (optionLink && optionLink.dataset.value === hiddenCat.value) {
                    hasSelected = true;
                }
            } else {
                item.style.display = 'none';
            }
        });
        if (!hasSelected && hiddenCat.value !== '0') {
            hiddenCat.value = '0';
            catDisplay.innerHTML = 'Tất cả danh mục';
        }
    }

    if (hiddenType && hiddenCat) {
        updateCats(); // Lọc ngay lúc load
        
        typeOptions.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                hiddenType.value = this.dataset.value;
                typeDisplay.innerHTML = this.innerHTML;
                updateCats();
            });
        });
        
        catOptions.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                const val = this.dataset.value;
                hiddenCat.value = val;
                
                if (val === '0') {
                    catDisplay.innerHTML = 'Tất cả danh mục';
                } else {
                    const icon = this.dataset.icon;
                    const color = this.dataset.color;
                    const name = this.dataset.name;
                    catDisplay.innerHTML = '<i class="' + icon + ' me-2" style="color:' + color + '"></i>' + name;
                }
            });
        });
    }

    if (hiddenSort) {
        sortOptions.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                hiddenSort.value = this.dataset.value;
                sortDisplay.innerHTML = this.innerHTML;
            });
        });
    }
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
