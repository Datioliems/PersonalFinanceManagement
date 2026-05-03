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
$periodLabel = date('d/m/Y', strtotime($startDate)) . ' – ' . date('d/m/Y', strtotime($endDate));
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/report.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card income-card">
            <div class="stat-label">
                <i class="bi bi-arrow-down-circle text-success"></i> Tổng thu nhập
            </div>
            <div class="stat-value text-success">
                +<?= number_format($summary['income'] ?? 0, 0, ',', '.') ?>đ
            </div>
            <div class="stat-period"><?= $periodLabel ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card expense-card">
            <div class="stat-label">
                <i class="bi bi-arrow-up-circle text-danger"></i> Tổng chi tiêu
            </div>
            <div class="stat-value text-danger">
                -<?= number_format($summary['expense'] ?? 0, 0, ',', '.') ?>đ
            </div>
            <div class="stat-period"><?= $periodLabel ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <?php $bal = $summary['balance'] ?? 0; $balPositive = $bal >= 0; ?>
        <div class="stat-card <?= $balPositive ? 'balance-card-positive' : 'balance-card-negative' ?>">
            <div class="stat-label">
                <i class="bi bi-wallet2 <?= $balPositive ? 'text-primary' : 'text-danger' ?>"></i> Chênh lệch
            </div>
            <div class="stat-value <?= $balPositive ? 'text-primary' : 'text-danger' ?>">
                <?= ($balPositive ? '+' : '') . number_format($bal, 0, ',', '.') ?>đ
            </div>
            <div class="stat-period"><?= $periodLabel ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card" style="background:#f8fafc">
            <div class="stat-label">
                <i class="bi bi-list-ul text-secondary"></i> Số giao dịch
            </div>
            <div class="stat-value text-dark">
                <?= number_format($pager->getTotal(), 0, ',', '.') ?>
            </div>
            <div class="stat-period"><?= $periodLabel ?></div>
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

<?php if (!empty($overBudgets)): ?>
<div class="alert border-0 d-flex align-items-center mb-4" style="background-color: #fef2f2; color: #991b1b; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
    <div>
        <strong style="font-size: 1rem;">Cảnh báo vượt ngân sách tháng <?= date('m/Y') ?>:</strong>
        <ul class="mb-0 mt-1 ps-3" style="font-size: 0.9rem;">
            <?php foreach ($overBudgets as $ob): 
                $limit = number_format($ob['limit_amount'], 0, ',', '.');
                $spent = number_format($ob['spent'], 0, ',', '.');
                $pct = number_format($ob['pct'], 1);
            ?>
                <li>Danh mục <b><?= htmlspecialchars($ob['category_name']) ?></b> đã chi <?= $spent ?>đ / <?= $limit ?>đ (<?= $pct ?>%)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Filter + Sort -->
<form method="GET" action="<?= BASE_URL ?>/transactions" class="card tx-filter-card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-auto" style="min-width: 240px;">
                <label class="form-label small text-muted mb-1">Thời gian</label>
                <div class="rp-bar w-100" id="rpTrigger">
                    <i class="bi bi-calendar3 me-2 text-muted"></i>
                    <span id="dateRangeDisplay"><?= htmlspecialchars($periodLabel) ?></span>
                </div>
                <input type="hidden" name="start_date" id="startDateInput" value="<?= htmlspecialchars($startDate ?? '', ENT_QUOTES) ?>">
                <input type="hidden" name="end_date" id="endDateInput" value="<?= htmlspecialchars($endDate ?? '', ENT_QUOTES) ?>">
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
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="<?= BASE_URL ?>/transactions" class="btn btn-sm btn-primary" style="opacity:.75;">
                    <i class="bi bi-x-circle me-1"></i>Xoá lọc
                </a>
            </div>
        </div>
    </div>
</form>

<!-- Date Picker Popup (Giống trang báo cáo) -->
<div class="rp-popup d-none" id="rpPopup" style="z-index: 1060; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
  <div class="rp-popup-inner">
    <div class="rp-calendars" id="rpCalendars"></div>
    <div class="rp-quick">
      <div class="rp-quick-title">Chọn nhanh</div>
      <?php
      $presets=[
        'Hôm nay'=>[date('Y-m-d'),date('Y-m-d')],
        'Hôm qua'=>[date('Y-m-d',strtotime('-1 day')),date('Y-m-d',strtotime('-1 day'))],
        'Tuần này'=>[date('Y-m-d',strtotime('monday this week')),date('Y-m-d',strtotime('sunday this week'))],
        'Tháng này'=>[date('Y-m-01'),date('Y-m-t')],
        'Tháng trước'=>[date('Y-m-01',strtotime('first day of last month')),date('Y-m-t',strtotime('last day of last month'))],
        'Năm nay'=>[date('Y-01-01'),date('Y-12-31')],
      ];
      foreach($presets as $lbl=>[$f,$t]):
        $active=($startDate===$f&&$endDate===$t)?'active':'';
      ?><button type="button" class="rp-quick-btn <?= $active ?>" data-from="<?= $f ?>" data-to="<?= $t ?>"><?= $lbl ?></button><?php endforeach;?>
    </div>
  </div>
  <div class="rp-popup-foot">
    <span class="rp-selected-label" id="rpSelectedLabel"><?= htmlspecialchars($periodLabel) ?></span>
    <button type="button" class="btn btn-sm btn-success" id="rpConfirm">Chọn</button>
  </div>
</div>
<div class="rp-backdrop d-none" id="rpBackdrop" style="z-index: 1050; position: fixed; inset: 0; background: rgba(0,0,0,0.4);"></div>

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
                    <tr>
                        <th class="text-center">Ngày</th>
                        <th class="text-center">Số Giao Dịch</th>
                        <th class="text-center text-success">Thu</th>
                        <th class="text-center text-danger">Chi</th>
                        <th class="text-center">Chênh lệch</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($dailySummary as $d): $bal = (float)$d['balance']; ?>
                <tr>
                    <td class="text-center"><a href="<?= BASE_URL ?>/transactions?start_date=<?= $d['trans_date'] ?>&end_date=<?= $d['trans_date'] ?>" class="text-decoration-none fw-medium"><?= htmlspecialchars($d['trans_date']) ?></a></td>
                    <td class="text-center text-muted small"><?= $d['total_tx'] ?></td>
                    <td class="text-center text-success"><?= $d['income']  > 0 ? '+'.number_format($d['income'], 0,',','.').'đ'  : '—' ?></td>
                    <td class="text-center text-danger"> <?= $d['expense'] > 0 ? '-'.number_format($d['expense'],0,',','.').'đ' : '—' ?></td>
                    <td class="text-center fw-medium <?= $bal >= 0 ? 'text-success' : 'text-danger' ?>">
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
                    <th class="ps-3 text-center">Ngày</th>
                    <th class="text-center">Loại</th>
                    <th class="text-center">Danh mục</th>
                    <th class="text-center">Số tiền</th>
                    <th class="text-center">Ghi chú</th>
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
                <td class="text-center text-nowrap text-muted small">
                    <?= htmlspecialchars($item['trans_date'], ENT_QUOTES) ?>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill"
                          style="background:<?= $isIncome ? '#dcfce7' : '#fee2e2' ?>;
                                 color:<?= $isIncome ? '#16a34a' : '#dc2626' ?>;
                                 padding:4px 10px; font-weight:500">
                        <?= $isIncome ? 'Thu nhập' : 'Chi tiêu' ?>
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill fw-normal"
                          style="background:<?= htmlspecialchars($color,ENT_QUOTES) ?>;
                                 color:#fff;
                                 padding:4px 10px">
                        <?= htmlspecialchars($item['category_name'], ENT_QUOTES) ?>
                    </span>
                </td>
                <td class="text-center fw-medium <?= $amountClass ?>">
                    <?= $amountPrefix ?><?= number_format($item['amount'], 0, ',', '.') ?>đ
                </td>
                <td class="text-center text-muted small text-truncate" style="max-width:200px">
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

    // Date Range Picker Logic
    const trigger=document.getElementById('rpTrigger'), popup=document.getElementById('rpPopup'),
          backdrop=document.getElementById('rpBackdrop'), confirmBtn=document.getElementById('rpConfirm'),
          selLabel=document.getElementById('rpSelectedLabel'), calsEl=document.getElementById('rpCalendars'),
          startDateInput=document.getElementById('startDateInput'), endDateInput=document.getElementById('endDateInput');
    
    let selFrom=startDateInput.value, selTo=endDateInput.value, picking=null;
    const MVN=['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
    const p2=n=>String(n).padStart(2,'0');
    const ymd=(y,m,d)=>`${y}-${p2(m)}-${p2(d)}`;
    const fmtD=s=>{const[y,mo,d]=s.split('-');return`${d}/${mo}/${y}`;};
    let calY,calM;

    function initCal(){const d=new Date(selFrom);calY=d.getFullYear();calM=d.getMonth()+1;}
    function renderCals(){
      let m2=calM+1,y2=calY;if(m2>12){m2=1;y2++;}
      calsEl.innerHTML=`<div class="rp-cal-wrap"><div class="rp-cal-nav">
        <button type="button" id="calPrev"><i class="bi bi-chevron-left"></i></button>
        <span>${MVN[calM-1]} ${calY}</span><span></span><span>${MVN[m2-1]} ${y2}</span>
        <button type="button" id="calNext"><i class="bi bi-chevron-right"></i></button>
        </div><div class="rp-two-cals">${bCal(calY,calM)}${bCal(y2,m2)}</div></div>`;
      document.getElementById('calPrev').onclick=()=>{calM--;if(calM<1){calM=12;calY--;}renderCals();};
      document.getElementById('calNext').onclick=()=>{calM++;if(calM>12){calM=1;calY++;}renderCals();};
      calsEl.querySelectorAll('[data-d]').forEach(el=>el.onclick=()=>pickDay(el.dataset.d));
    }
    function bCal(y,m){
      const first=(new Date(y,m-1,1).getDay()+6)%7;
      const dim=new Date(y,m,0).getDate();
      let h='<table class="rp-cal-table"><thead><tr>';
      ['T2','T3','T4','T5','T6','T7','CN'].forEach(d=>h+=`<th>${d}</th>`);
      h+='</tr></thead><tbody><tr>';
      for(let i=0;i<first;i++)h+='<td></td>';
      let dow=first;
      for(let d=1;d<=dim;d++){
        const ds=ymd(y,m,d);
        let c='rp-day';
        if(ds===selFrom)c+=' rp-sel-start';
        if(ds===selTo)c+=' rp-sel-end';
        if(ds>selFrom&&ds<selTo)c+=' rp-in-range';
        h+=`<td><button type="button" class="${c}" data-d="${ds}">${d}</button></td>`;
        if(++dow%7===0&&d<dim)h+='</tr><tr>';
      }
      return h+'</tr></tbody></table>';
    }
    function pickDay(d){
      if(!picking||picking==='from'){selFrom=d;selTo=d;picking='to';}
      else{if(d<selFrom){selTo=selFrom;selFrom=d;}else{selTo=d;}picking='from';}
      selLabel.textContent=fmtD(selFrom)+' – '+fmtD(selTo);
      renderCals();
    }
    if(trigger){
      trigger.onclick=()=>{initCal();renderCals();popup.classList.remove('d-none');backdrop.classList.remove('d-none');};
      backdrop.onclick=()=>{popup.classList.add('d-none');backdrop.classList.add('d-none');};
      confirmBtn.onclick=()=>{
        startDateInput.value = selFrom;
        endDateInput.value = selTo;
        document.getElementById('dateRangeDisplay').textContent = `${fmtD(selFrom)} – ${fmtD(selTo)}`;
        popup.classList.add('d-none');backdrop.classList.add('d-none');
        // Không tự submit form, đợi người dùng bấm "Lọc"
      };
      document.querySelectorAll('.rp-quick-btn').forEach(btn => {
        btn.onclick = function() {
            selFrom = this.dataset.from;
            selTo = this.dataset.to;
            selLabel.textContent = fmtD(selFrom) + ' – ' + fmtD(selTo);
            renderCals();
            document.querySelectorAll('.rp-quick-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        }
      });
    }
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
