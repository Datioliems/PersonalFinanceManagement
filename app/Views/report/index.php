<?php
$pageTitle='Báo cáo tài chính cá nhân'; $extraCss=BASE_URL.'/css/report.css'; $needChartJs=true;
require BASE_PATH.'/app/Views/partials/layout.php';
$df=$dateFrom??date('Y-m-01'); $dt=$dateTo??date('Y-m-t');
$tab=$tab??'overview'; $catType=$catType??'expense';
$allowed=$allowedGranularity??['day'];
$GLOBALS['df']=$df;$GLOBALS['dt']=$dt;$GLOBALS['tab']=$tab;$GLOBALS['catType']=$catType;
function rp(array $ov=[]){
  return BASE_URL.'/report?'.http_build_query(array_merge(['date_from'=>$GLOBALS['df'],'date_to'=>$GLOBALS['dt'],'tab'=>$GLOBALS['tab'],'cat_type'=>$GLOBALS['catType']],$ov));
}
function fmtVi(string $d){$ts=strtotime($d);return 'Th'.date('m',$ts).' '.date('d',$ts).', '.date('Y',$ts);}
$displayRange=fmtVi($df).' – '.fmtVi($dt);
$days=$totalDays??30;
$prevFrom=date('Y-m-d',strtotime("$df -$days days")); $prevTo=date('Y-m-d',strtotime("$dt -$days days"));
$nextFrom=date('Y-m-d',strtotime("$df +$days days")); $nextTo=date('Y-m-d',strtotime("$dt +$days days"));
$walletBal=$walletBalance??0; $periodBal=$summary['period_balance']??0;
$periodInc=$summary['income']??0; $periodExp=$summary['expense']??0;
$allData=json_decode($allChartsJson??'{}',true);
$hasIncome=!empty($allData['incomeDonut']['labels']??[]);
$hasExpense=!empty($allData['expenseDonut']['labels']??[]);
?>
<!-- Header (sticky) -->
<div class="page-header-shared mb-3 flex-wrap gap-2 rp-sticky-header">
  <div>
    <h1 class="page-title">
      <i class="bi bi-bar-chart-line me-2 text-success"></i>Báo cáo
    </h1>
    <p class="page-subtitle">Thống kê và phân tích tài chính</p>
  </div>

  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span class="text-muted small fw-medium">Kỳ lựa chọn:</span>
    <div class="rp-bar" id="rpBar">
      <a href="<?=rp(['date_from'=>$prevFrom,'date_to'=>$prevTo])?>" class="rp-nav-btn"><i class="bi bi-chevron-left"></i></a>
      <button class="rp-label" id="rpTrigger" type="button">
        <i class="bi bi-calendar3 me-1"></i><?=htmlspecialchars($displayRange)?>
      </button>
      <a href="<?=rp(['date_from'=>$nextFrom,'date_to'=>$nextTo])?>" class="rp-nav-btn"><i class="bi bi-chevron-right"></i></a>
    </div>
    <a href="<?=BASE_URL?>/report/export?date_from=<?=$df?>&date_to=<?=$dt?>" class="btn btn-sm btn-outline-success">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Xuất CSV
    </a>
  </div>
</div>
<div style="margin-bottom:.85rem"></div>

<!-- Date Picker Popup -->
<div class="rp-popup d-none" id="rpPopup">
  <div class="rp-popup-inner">
    <div class="rp-calendars" id="rpCalendars"></div>
    <div class="rp-quick">
      <div class="rp-quick-title">Chọn nhanh</div>
      <?php
      $presets=[
        'Tuần này'=>[date('Y-m-d',strtotime('monday this week')),date('Y-m-d',strtotime('sunday this week'))],
        'Tuần trước'=>[date('Y-m-d',strtotime('monday last week')),date('Y-m-d',strtotime('sunday last week'))],
        'Tháng này'=>[date('Y-m-01'),date('Y-m-t')],
        'Tháng trước'=>[date('Y-m-01',strtotime('first day of last month')),date('Y-m-t',strtotime('last day of last month'))],
        'Năm nay'=>[date('Y-01-01'),date('Y-12-31')],
        'Năm ngoái'=>[(date('Y')-1).'-01-01',(date('Y')-1).'-12-31'],
      ];
      foreach($presets as $lbl=>[$f,$t]):
        $active=($df===$f&&$dt===$t)?'active':'';
      ?><a href="<?=rp(['date_from'=>$f,'date_to'=>$t])?>" class="rp-quick-btn <?=$active?>"><?=$lbl?></a><?php endforeach;?>
    </div>
  </div>
  <div class="rp-popup-foot">
    <span class="rp-selected-label" id="rpSelectedLabel"><?=htmlspecialchars($displayRange)?></span>
    <button class="btn btn-sm btn-success" id="rpConfirm">Chọn</button>
  </div>
</div>
<div class="rp-backdrop d-none" id="rpBackdrop"></div>

<!-- 4 Stat Cards -->
<div class="row g-3 mb-3">
<?php
$cards=[
  ['Số dư ví hiện tại',$walletBal,$walletBal>=0?'success':'danger','bi-wallet2','rp-card-wallet'],
  ['Tổng chênh lệch theo kỳ',$periodBal,$periodBal>=0?'success':'danger','bi-arrow-left-right','rp-card-balance'],
  ['Tổng chi tiêu theo kỳ',-$periodExp,'danger','bi-arrow-up-circle','rp-card-expense'],
  ['Tổng thu nhập theo kỳ',$periodInc,'success','bi-arrow-down-circle','rp-card-income'],
];
$periodLabel = date('d/m/Y', strtotime($df)).' – '.date('d/m/Y', strtotime($dt));
foreach($cards as [$lbl,$val,$col,$ico,$cardCls]):
  $signed=($val>=0?'+':'').number_format($val,0,',','.');
?>
<div class="col-6 col-lg-3">
  <div class="rp-stat-card <?=$cardCls?>">
    <div class="rp-stat-label"><i class="bi <?=$ico?> text-<?=$col?> me-1"></i><?=$lbl?></div>
    <div class="rp-stat-value text-<?=$col?>"><?=$signed?>đ</div>
    <div class="rp-stat-period"><?=$periodLabel?></div>
  </div>
</div>
<?php endforeach;?>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?=$tab==='overview'?'active':''?>" href="<?=rp(['tab'=>'overview'])?>"><i class="bi bi-grid me-1"></i>Tổng quan</a></li>
  <li class="nav-item"><a class="nav-link <?=$tab==='category'?'active':''?>" href="<?=rp(['tab'=>'category'])?>"><i class="bi bi-tags me-1"></i>Chi tiết danh mục</a></li>
</ul>

<?php if($tab==='overview'):?>
<!-- ── TAB TỔNG QUAN ── -->
<div class="row g-4 mb-4">
  <!-- Ô 6: Balance line -->
  <div class="col-12 col-lg-6">
    <div class="rp-chart-card">
      <div class="rp-chart-header">
        <div><div class="rp-chart-title">Số dư tài khoản</div><div class="rp-chart-sub"><?=date('d/m',strtotime($df)).' – '.date('d/m',strtotime($dt))?></div></div>
        <div class="rp-gr-group" id="grpBalance">
          <?php foreach(['day'=>'Ngày','week'=>'Tuần','month'=>'Tháng'] as $g=>$lbl):
            $isAllowed = in_array($g,$allowed);
            $isFirst   = $g === $allowed[0];
          ?>
          <button class="rp-gr-btn <?=$isAllowed&&$isFirst?'active':''?>" data-chart="balance" data-gran="<?=$g?>"
            <?= $isAllowed ? '' : 'disabled title="Không khả dụng với kỳ này"'?>><?=$lbl?></button>
          <?php endforeach;?>
        </div>

      </div>
      <div class="rp-chart-body"><canvas id="balanceChart" style="max-height:260px"></canvas></div>
    </div>
  </div>
  <!-- Ô 7: Bar -->
  <div class="col-12 col-lg-6">
    <div class="rp-chart-card">
      <div class="rp-chart-header">
        <div><div class="rp-chart-title">Thống kê Thu &amp; Chi</div><div class="rp-chart-sub"><?=date('d/m',strtotime($df)).' – '.date('d/m',strtotime($dt))?></div></div>
        <div class="rp-gr-group" id="grpIncExp">
          <?php foreach(['day'=>'Ngày','week'=>'Tuần','month'=>'Tháng'] as $g=>$lbl):
            $isAllowed = in_array($g,$allowed);
            $isFirst   = $g === $allowed[0];
          ?>
          <button class="rp-gr-btn <?=$isAllowed&&$isFirst?'active':''?>" data-chart="incExp" data-gran="<?=$g?>"
            <?= $isAllowed ? '' : 'disabled title="Không khả dụng với kỳ này"'?>><?=$lbl?></button>
          <?php endforeach;?>
        </div>
      </div>
      <div class="rp-chart-body"><canvas id="incExpChart" style="max-height:260px"></canvas></div>
    </div>
  </div>
</div>

<!-- Ô 8 & 9: Donut charts -->
<div class="row g-4 mb-4">
<?php if($hasIncome):?>
<div class="col-12 col-md-6">
  <div class="rp-chart-card">
    <div class="rp-chart-header"><div class="rp-chart-title">Thu nhập theo kỳ</div><div class="rp-chart-sub"><?=date('d/m',strtotime($df)).' – '.date('d/m',strtotime($dt))?></div></div>
    <div class="rp-chart-body">
      <div class="rp-donut-wrap">
        <div class="rp-donut-canvas"><canvas id="incomeDonut"></canvas></div>
        <table class="rp-donut-legend" id="incomeDonutLegend"></table>
      </div>
    </div>
  </div>
</div>
<?php endif;?>
<?php if($hasExpense):?>
<div class="col-12 col-md-6">
  <div class="rp-chart-card">
    <div class="rp-chart-header"><div class="rp-chart-title">Chi tiêu theo kỳ</div><div class="rp-chart-sub"><?=date('d/m',strtotime($df)).' – '.date('d/m',strtotime($dt))?></div></div>
    <div class="rp-chart-body">
      <div class="rp-donut-wrap">
        <div class="rp-donut-canvas"><canvas id="expenseDonut"></canvas></div>
        <table class="rp-donut-legend" id="expenseDonutLegend"></table>
      </div>
    </div>
  </div>
</div>
<?php endif;?>
</div>

<?php elseif($tab==='category'):?>
<!-- ── TAB CHI TIẾT DANH MỤC ── -->
<div class="d-flex gap-2 mb-3">
  <button data-cat-switch="expense" class="btn btn-sm <?=$catType==='expense'?'btn-danger':'btn-outline-danger'?>">
    <i class="bi bi-arrow-up-circle me-1"></i>Chi tiêu
  </button>
  <button data-cat-switch="income" class="btn btn-sm <?=$catType==='income'?'btn-success':'btn-outline-success'?>">
    <i class="bi bi-arrow-down-circle me-1"></i>Thu nhập
  </button>
</div>
<div id="catTableWrap">
<?php if(empty($categoryDetail)):?>
<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Chưa có dữ liệu <?=$catType==='expense'?'chi tiêu':'thu nhập'?> trong kỳ này</div>
<?php else:
  $total=array_sum(array_column($categoryDetail,'total'));
  $tColor=$catType==='expense'?'text-danger':'text-success';
?>
<div class="rp-cat-table-wrap">
  <table class="rp-cat-table">
    <thead>
      <tr><th>Danh mục</th><th>Số GD</th><th>Tổng</th><th>Tỷ lệ</th></tr>
    </thead>
    <tbody>
    <?php foreach($categoryDetail as $row):
      $pct=$total>0?round($row['total']/$total*100,1):0;
      $color=$row['color']?:'#6b7280';
      $catId=$row['category_id']??0;
    ?>
    <tr class="rp-cat-row" data-cat-id="<?=$catId?>" data-cat-name="<?=htmlspecialchars($row['category_name'])?>" data-type="<?=$catType?>" data-color="<?=htmlspecialchars($color)?>" style="cursor:pointer" title="Nhấn để xem chi tiết">
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="rp-cat-dot" style="background:<?=htmlspecialchars($color)?>"></span>
          <span class="fw-medium"><?=htmlspecialchars($row['category_name'])?></span>
        </div>
        <div class="rp-cat-bar"><div class="rp-cat-bar-fill <?=$catType==='expense'?'bg-danger':'bg-success'?>" style="width:<?=$pct?>%"></div></div>
      </td>
      <td class="text-center text-muted"><?=(int)$row['tx_count']?></td>
      <td class="fw-semibold <?=$tColor?> text-end"><?=number_format($row['total'],0,',','.')?>đ</td>
      <td class="text-end"><span class="rp-pct-badge" style="background:<?=htmlspecialchars($color)?>22;color:<?=htmlspecialchars($color)?>;"><?=$pct?>%</span></td>
    </tr>
    <?php endforeach;?>
    </tbody>
    <tfoot>
      <tr class="rp-cat-foot">
        <td class="fw-semibold">Tổng cộng</td>
        <td></td>
        <td class="fw-bold <?=$tColor?> text-end"><?=number_format($total,0,',','.')?>đ</td>
        <td class="text-end fw-semibold">100%</td>
      </tr>
    </tfoot>
  </table>
</div>
<?php endif;?>
</div><!-- /#catTableWrap -->
<?php endif;?>


<!-- Transaction popup modal (dùng chung cho cả report + dashboard click) -->
<div class="modal fade" id="txModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="txModalTitle">Chi tiết giao dịch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="txModalBody">
        <div class="text-center py-4"><div class="spinner-border text-success"></div></div>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH.'/app/Views/partials/footer.php';?>
<script>
(function(){
'use strict';
// expose ra global để inline onclick và AJAX rows có thể gọi
window._rpOpenModal = null;
window.openTxModal = null; // sẽ được gán sau khi IIFE khởi tạo
const ALL   = <?=$allChartsJson??'{}'?>;
const GRALL = <?=$allowedGranJson??'["day"]'?>;
const DF    = '<?=$df?>';
const DT    = '<?=$dt?>';
const BASE  = '<?=BASE_URL?>';
const fmt   = v => new Intl.NumberFormat('vi-VN').format(v)+'đ';
const fmtC  = v => new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v)+'đ';

// ── Granularity state (per chart, independent) ──
let curGran = { balance: GRALL[0], incExp: GRALL[0] };
let charts  = {};

// ── Crosshair plugin (vertical line + column shadow on hover) ──
const crosshairPlugin = {
  id: 'crosshairLine',
  afterDraw(chart) {
    if (!chart._hoverX) return;
    const { ctx, chartArea: { top, bottom } } = chart;
    ctx.save();
    ctx.strokeStyle = 'rgba(100,116,139,0.35)';
    ctx.lineWidth = 1.5;
    ctx.setLineDash([4, 3]);
    ctx.beginPath();
    ctx.moveTo(chart._hoverX, top);
    ctx.lineTo(chart._hoverX, bottom);
    ctx.stroke();
    ctx.restore();
  },
  afterEvent(chart, args) {
    if (args.event.type === 'mousemove') {
      chart._hoverX = args.event.x;
      chart.draw();
    } else if (args.event.type === 'mouseout') {
      chart._hoverX = null;
      chart.draw();
    }
  }
};

// ── Column shadow plugin — canh chính xác theo bounds thực của nhóm cột ──
const barShadowPlugin = {
  id: 'barColumnShadow',
  beforeDatasetsDraw(chart) {
    const idx = chart._hoverIndex;
    if (idx == null) return;

    // Tính left/right edge thực tế từ tất cả datasets (bỏ qua dataset ẩn)
    let leftEdge  = Infinity;
    let rightEdge = -Infinity;
    for (let di = 0; di < chart.data.datasets.length; di++) {
      const meta = chart.getDatasetMeta(di);
      if (meta.hidden) continue;
      const bar = meta.data?.[idx];
      if (!bar) continue;
      const hw = (bar.width ?? 20) / 2;
      if (bar.x - hw < leftEdge)  leftEdge  = bar.x - hw;
      if (bar.x + hw > rightEdge) rightEdge = bar.x + hw;
    }
    if (!isFinite(leftEdge)) return;

    const pad = 4; // padding nhỏ mỗi bên
    const { ctx, chartArea: { top, bottom } } = chart;
    ctx.save();
    ctx.fillStyle = 'rgba(100,116,139,0.09)';
    ctx.fillRect(leftEdge - pad, top, rightEdge - leftEdge + pad * 2, bottom - top);
    ctx.restore();
  },
};

// Smart ticks limit: avoid crowding x-axis labels
function smartMaxTicks(labelCount) {
  if (labelCount <= 12)  return labelCount;
  if (labelCount <= 31)  return 8;
  if (labelCount <= 60)  return 10;
  if (labelCount <= 120) return 8;
  return 7;
}

function buildBalanceChart(gran){
  const d = ALL.balance?.[gran];
  if(!d?.labels?.length) return;
  if(charts.balance) charts.balance.destroy();
  const ctx = document.getElementById('balanceChart');
  if(!ctx) return;
  const bData = d.datasets[0].data;
  const maxTicks = smartMaxTicks(d.labels.length);
  charts.balance = new Chart(ctx,{
    type:'line',
    plugins:[crosshairPlugin],
    data:{ labels:d.labels, datasets:[{
      ...d.datasets[0],
      segment:{
        borderColor:     c => c.p0.parsed.y < 0 ? '#ef4444' : '#22c55e',
        backgroundColor: c => c.p0.parsed.y < 0 ? '#ef444418' : '#22c55e18',
      },
      pointBackgroundColor: bData.map(v => v<0?'#ef4444':'#22c55e'),
      pointBorderColor:'#fff',
      pointRadius: d.labels.length > 60 ? 0 : 3,
      pointHoverRadius: 5,
    }]},
    options:{
      responsive:true,
      interaction:{mode:'index',intersect:false},
      plugins:{
        legend:{display:false},
        tooltip:{callbacks:{label:c=>'Số dư: '+fmt(c.raw)}}
      },
      scales:{
        y:{ticks:{callback:fmtC},grid:{color:'#f1f5f9'}},
        x:{grid:{display:false},ticks:{maxTicksLimit:maxTicks,autoSkip:true,maxRotation:0}}
      }
    }
  });
}

function buildIncExpChart(gran){
  const d = ALL.incExp?.[gran];
  if(!d?.labels?.length) return;
  if(charts.incExp) charts.incExp.destroy();
  const ctx = document.getElementById('incExpChart');
  if(!ctx) return;
  const maxTicks = smartMaxTicks(d.labels.length);
  charts.incExp = new Chart(ctx,{
    type:'bar',
    plugins:[barShadowPlugin],
    data:d,
    options:{
      responsive:true,
      interaction:{mode:'index',intersect:false},
      plugins:{
        legend:{position:'top'},
        tooltip:{callbacks:{label:c=>c.dataset.label+': '+fmt(c.raw)}}
      },
      scales:{
        y:{beginAtZero:true,ticks:{callback:fmtC},grid:{color:'#f1f5f9'}},
        x:{grid:{display:false},ticks:{maxTicksLimit:maxTicks,autoSkip:true,maxRotation:0}}
      },
      onHover:(evt,els)=>{
        charts.incExp._hoverIndex = els.length ? els[0].index : null;
      }
    }
  });
}

// ── Donut helper ──
function buildDonut(canvasId, legendId, data, type){
  const ctx = document.getElementById(canvasId);
  const lgEl = document.getElementById(legendId);
  if(!ctx || !data?.labels?.length) return;
  const dChart = new Chart(ctx,{
    type:'doughnut', data:data,
    options:{responsive:true,cutout:'60%',
      plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': '+fmt(c.raw)}}},
      onClick:(e,els)=>{
        if(!els.length) return;
        const i = els[0].index;
        openTxModal(data.categoryIds?.[i]??0, data.labels[i], type, data.datasets[0].backgroundColor[i]);
      }
    }
  });
  if(lgEl){
    const colors  = data.datasets[0].backgroundColor;
    const counts  = data.txCounts||[];
    const amounts = data.datasets[0].data;
    const catIds  = data.categoryIds||[];
    const hoverColor = type==='income' ? '#16a34a' : '#dc2626';
    // Dùng DOM API để gắn hover đúng màu theo type
    const tbody = document.createElement('tbody');
    data.labels.forEach((l,i)=>{
      const tr = document.createElement('tr');
      tr.className = 'rp-legend-row';
      tr.dataset.catId  = catIds[i]??0;
      tr.dataset.type   = type;
      tr.dataset.color  = colors[i];
      tr.style.cursor   = 'pointer';
      tr.title = 'Nhấn để xem giao dịch';
      const tdDot = document.createElement('td');
      tdDot.innerHTML = `<span class="rp-cat-dot" style="background:${colors[i]}"></span>`;
      const tdName = document.createElement('td');
      tdName.className = 'rp-legend-name';
      tdName.textContent = l;
      const tdCount = document.createElement('td');
      tdCount.className = 'text-muted small px-2';
      tdCount.textContent = (counts[i]??0)+' giao dịch';
      const tdAmt = document.createElement('td');
      tdAmt.className = 'fw-semibold text-end';
      tdAmt.textContent = fmt(amounts[i]);
      tr.append(tdDot, tdName, tdCount, tdAmt);
      tr.addEventListener('mouseenter',()=>{
        tr.querySelectorAll('td').forEach(td=>td.style.background='#f8fafc');
        tdName.style.color      = hoverColor;
        tdName.style.fontWeight = '600';
      });
      tr.addEventListener('mouseleave',()=>{
        tr.querySelectorAll('td').forEach(td=>td.style.background='');
        tdName.style.color      = '';
        tdName.style.fontWeight = '';
      });
      tr.onclick = ()=>openTxModal(+tr.dataset.catId, l, type, colors[i]);
      tbody.appendChild(tr);
    });
    lgEl.innerHTML = '';
    lgEl.appendChild(tbody);
  }
}

// ── Transaction popup (AJAX) ──
let txModal;
function _openTxModal(catId, catName, type, color){
  if(!txModal) txModal = new bootstrap.Modal(document.getElementById('txModal'));
  document.getElementById('txModalTitle').innerHTML =
    `<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${color};margin-right:6px"></span>${catName}`;
  document.getElementById('txModalBody').innerHTML =
    `<div class="text-center py-4"><div class="spinner-border text-success"></div></div>`;
  txModal.show();
  const url = `${BASE}/report/transactions?category_id=${catId}&date_from=${DF}&date_to=${DT}&type=${type}`;
  fetch(url).then(r=>r.json()).then(rows=>{
    if(!rows.length){
      document.getElementById('txModalBody').innerHTML='<p class="text-muted text-center py-3">Không có giao dịch nào.</p>';
      return;
    }
    const tColor = type==='expense'?'text-danger':'text-success';
    let html=`<table class="table table-sm table-hover mb-0"><thead class="table-light">
      <tr><th class="tx-th">Ngày</th><th class="tx-th">Ghi chú</th><th class="tx-th text-end">Số tiền</th></tr></thead><tbody>`;
    rows.forEach(r=>{
      html+=`<tr><td class="tx-td text-muted small">${r.date}</td><td class="tx-td small">${r.note||'—'}</td><td class="tx-td fw-semibold ${tColor} text-end">${fmt(r.amount)}</td></tr>`;
    });
    html+=`</tbody></table>`;
    document.getElementById('txModalBody').innerHTML=html;
  }).catch(()=>{
    document.getElementById('txModalBody').innerHTML='<p class="text-danger text-center py-3">Có lỗi khi tải dữ liệu.</p>';
  });
}
// Expose to global scope
window.openTxModal = _openTxModal;

// ── Granularity button clicks ──
document.querySelectorAll('.rp-gr-btn[data-chart]').forEach(btn=>{
  btn.onclick = ()=>{
    const chart = btn.dataset.chart;
    const gran  = btn.dataset.gran;
    curGran[chart] = gran;
    btn.closest('.rp-gr-group').querySelectorAll('.rp-gr-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    if(chart==='balance') buildBalanceChart(gran);
    else                  buildIncExpChart(gran);
  };
});

// ── Category AJAX tab switch ──
const catBtns = document.querySelectorAll('[data-cat-switch]');
const catTableWrap = document.getElementById('catTableWrap');
// Event delegation cho cả SSR lẫn AJAX rows
if(catTableWrap){
  catTableWrap.addEventListener('click', e=>{
    const row = e.target.closest('[data-cat-id]');
    if(!row) return;
    _openTxModal(+row.dataset.catId||0, row.dataset.catName, row.dataset.type, row.dataset.color);
  });
}
if(catBtns.length && catTableWrap){
  catBtns.forEach(btn=>{
    btn.addEventListener('click', e=>{
      e.preventDefault();
      const newType = btn.dataset.catSwitch;
      catBtns.forEach(b=>{
        const isInc = b.dataset.catSwitch==='income';
        b.className = `btn btn-sm ${b.dataset.catSwitch===newType
          ? (isInc?'btn-success':'btn-danger')
          : (isInc?'btn-outline-success':'btn-outline-danger')}`;
      });
      catTableWrap.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';
      fetch(`${BASE}/report/category-detail?cat_type=${newType}&date_from=${DF}&date_to=${DT}`)
        .then(r=>r.json()).then(rows=>{
          if(!rows.length){
            catTableWrap.innerHTML=`<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Chưa có dữ liệu ${newType==='expense'?'chi tiêu':'thu nhập'} trong kỳ này</div>`;
            return;
          }
          const tColor=newType==='expense'?'text-danger':'text-success';
          const barCls=newType==='expense'?'bg-danger':'bg-success';
          const total=rows.reduce((s,r)=>s+(+r.total),0);
          let html=`<div class="rp-cat-table-wrap"><table class="rp-cat-table"><thead><tr><th>Danh mục</th><th>Số GD</th><th>Tổng</th><th>Tỷ lệ</th></tr></thead><tbody>`;
          rows.forEach(row=>{
            const pct=total>0?+(row.total/total*100).toFixed(1):0;
            const color=row.color||'#6b7280';
            // Dùng data-* thay inline onclick — event delegation sẽ xử lý
            html+=`<tr class="rp-cat-row"
              data-cat-id="${+(row.category_id)||0}"
              data-cat-name="${(row.category_name||'').replace(/"/g,'&quot;')}"
              data-type="${newType}"
              data-color="${color.replace(/"/g,'&quot;')}"
              style="cursor:pointer" title="Nhấn để xem chi tiết">
              <td><div class="d-flex align-items-center gap-2">
                <span class="rp-cat-dot" style="background:${color}"></span>
                <span class="fw-medium">${row.category_name}</span>
              </div><div class="rp-cat-bar"><div class="rp-cat-bar-fill ${barCls}" style="width:${pct}%"></div></div></td>
              <td class="text-center text-muted">${row.tx_count}</td>
              <td class="fw-semibold ${tColor} text-end">${fmt(+row.total)}đ</td>
              <td class="text-end"><span class="rp-pct-badge" style="background:${color}22;color:${color}">${pct}%</span></td>
            </tr>`;
          });
          const tf=new Intl.NumberFormat('vi-VN').format(total);
          html+=`</tbody><tfoot><tr class="rp-cat-foot"><td class="fw-semibold">Tổng cộng</td><td></td>
            <td class="fw-bold ${tColor} text-end">${tf}đ</td>
            <td class="text-end fw-semibold">100%</td></tr></tfoot></table></div>`;
          catTableWrap.innerHTML=html;
        }).catch(()=>{
          catTableWrap.innerHTML='<p class="text-danger text-center py-3">Có lỗi khi tải dữ liệu.</p>';
        });
    });
  });
}
// Category row click (server-rendered, dùng data-* nhất quán với delegation)
document.querySelectorAll('.rp-cat-row').forEach(row=>{
  row.onclick=()=>_openTxModal(+row.dataset.catId, row.dataset.catName, row.dataset.type, row.dataset.color);
});


// ── Init charts ──
buildBalanceChart(curGran.balance);
buildIncExpChart(curGran.incExp);

// Add categoryIds to donut data before build (lấy từ server — nếu có)
if(ALL.incomeDonut)  buildDonut('incomeDonut','incomeDonutLegend',ALL.incomeDonut,'income');
if(ALL.expenseDonut) buildDonut('expenseDonut','expenseDonutLegend',ALL.expenseDonut,'expense');

// ── Date Range Picker ──
const trigger=document.getElementById('rpTrigger'),popup=document.getElementById('rpPopup'),
      backdrop=document.getElementById('rpBackdrop'),confirm=document.getElementById('rpConfirm'),
      selLabel=document.getElementById('rpSelectedLabel'),calsEl=document.getElementById('rpCalendars');
let selFrom=DF,selTo=DT,picking=null;
const MVN=['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
const p2=n=>String(n).padStart(2,'0');
const ymd=(y,m,d)=>`${y}-${p2(m)}-${p2(d)}`;
const fmtD=s=>{const[y,mo,d]=s.split('-');return`Th${mo} ${d}, ${y}`;};
let calY,calM;
function initCal(){const d=new Date(selFrom);calY=d.getFullYear();calM=d.getMonth()+1;}
function renderCals(){
  let m2=calM+1,y2=calY;if(m2>12){m2=1;y2++;}
  calsEl.innerHTML=`<div class="rp-cal-wrap"><div class="rp-cal-nav">
    <button id="calPrev"><i class="bi bi-chevron-left"></i></button>
    <span>${MVN[calM-1]} ${calY}</span><span></span><span>${MVN[m2-1]} ${y2}</span>
    <button id="calNext"><i class="bi bi-chevron-right"></i></button>
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
    h+=`<td><button class="${c}" data-d="${ds}">${d}</button></td>`;
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
  confirm.onclick=()=>{window.location.href=`${BASE}/report?date_from=${selFrom}&date_to=${selTo}&tab=<?=$tab?>`;};
}
})();
</script>