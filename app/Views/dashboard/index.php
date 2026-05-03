<?php
$pageTitle = 'Dashboard';
$extraCss  = BASE_URL . '/css/dashboard.css';
require BASE_PATH . '/app/Views/partials/layout.php';

$walletBal  = $walletBalance ?? 0;
$periodBal  = $summary['period_balance'] ?? 0;
$incomeAmt  = $summary['income']  ?? 0;
$expenseAmt = $summary['expense'] ?? 0;
?>

<!-- ── 4 Stat Cards ─────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Số dư ví hiện tại',      $walletBal,   $walletBal>=0?'success':'danger',   'bi-wallet2',           'income-card'],
    ['Tổng chênh lệch theo kỳ', $periodBal,  $periodBal>=0?'success':'danger',   'bi-arrow-left-right',  'balance-card-positive'],
    ['Tổng chi tiêu theo kỳ',   -$expenseAmt, 'danger',                           'bi-arrow-up-circle',   'expense-card'],
    ['Tổng thu nhập theo kỳ',   $incomeAmt,  'success',                          'bi-arrow-down-circle', 'income-card'],
  ];
  foreach ($cards as [$lbl,$val,$col,$ico,$cls]):
    $signed = ($val>=0?'+':'').number_format($val,0,',','.');
  ?>
  <div class="col-6 col-lg-3">
    <div class="card stat-card <?= $cls ?>">
      <div class="stat-label"><i class="bi <?= $ico ?> text-<?= $col ?>"></i><?= $lbl ?></div>
      <div class="stat-value text-<?= $col ?>"><?= $signed ?>đ</div>
      <div class="stat-period">Tháng <?= $month ?>/<?= $year ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Charts row ───────────────────────────────────────── -->
<div class="row g-4 mb-4">
  <!-- Bar chart 4 tuần -->
  <div class="col-12 col-lg-7">
    <div class="chart-card h-100">
      <div class="chart-header">
        <div>
          <div class="chart-title">Thống kê Thu &amp; Chi gần đây</div>
          <div class="chart-subtitle">4 tuần qua</div>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="barChart" style="max-height:280px;width:100%"></canvas>
      </div>
    </div>
  </div>

  <!-- 2 Donut charts stacked -->
  <div class="col-12 col-lg-5">
    <div class="row g-3 h-100">
      <!-- Thu nhập -->
      <div class="col-12">
        <div class="chart-card">
          <div class="chart-header">
            <div class="chart-title">Thu nhập theo kỳ</div>
            <div class="chart-subtitle">Tháng <?= $month ?>/<?= $year ?></div>
          </div>
          <div class="chart-body d-flex gap-3 align-items-center">
            <canvas id="incomeDonut" style="max-height:150px;max-width:150px;flex-shrink:0"></canvas>
            <div id="incomeDonutLegend" class="w-100 small"></div>
          </div>
        </div>
      </div>
      <!-- Chi tiêu -->
      <div class="col-12">
        <div class="chart-card">
          <div class="chart-header">
            <div class="chart-title">Chi tiêu theo kỳ</div>
            <div class="chart-subtitle">Tháng <?= $month ?>/<?= $year ?></div>
          </div>
          <div class="chart-body d-flex gap-3 align-items-center">
            <canvas id="expenseDonut" style="max-height:150px;max-width:150px;flex-shrink:0"></canvas>
            <div id="expenseDonutLegend" class="w-100 small"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Quick actions ─────────────────────────────────────── -->
<div class="dashboard-actions">
  <a href="<?= BASE_URL ?>/transactions/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Thêm giao dịch
  </a>
  <a href="<?= BASE_URL ?>/report" class="btn btn-light border">
    <i class="bi bi-bar-chart me-1"></i>Xem báo cáo chi tiết
  </a>
  <a href="<?= BASE_URL ?>/dashboard/export" class="btn btn-light border">
    <i class="bi bi-download me-1"></i>Xuất CSV tháng này
  </a>
</div>

<!-- Transaction popup modal -->
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

<?php ob_start(); ?>
<script>
(function(){
  const data = <?= $chartJson ?>;
  const fmt  = v => new Intl.NumberFormat('vi-VN').format(v)+'đ';
  const fmtC = v => new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v)+'đ';

  // Bar chart (4 tuần) — shadow chỉ hiện khi hover, ẩn khi rời chuột
  const barShadowPlugin = {
    id: 'barColumnShadow',
    afterEvent(chart, args) {
      const evt = args.event;
      if (evt.type === 'mouseout' || evt.type === 'mouseleave') {
        chart._hoverIndex = null;
        chart.draw();
      }
    },
    beforeDatasetsDraw(chart) {
      const idx = chart._hoverIndex;
      if (idx == null) return;
      let leftEdge = Infinity, rightEdge = -Infinity;
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
      const pad = 4;
      const { ctx, chartArea: { top, bottom } } = chart;
      ctx.save();
      ctx.fillStyle = 'rgba(100,116,139,0.09)';
      ctx.fillRect(leftEdge - pad, top, rightEdge - leftEdge + pad * 2, bottom - top);
      ctx.restore();
    }
  };
  const barCtx = document.getElementById('barChart');
  if(barCtx && data.bar?.labels?.length){
    const barChart = new Chart(barCtx,{
      type:'bar',
      plugins:[barShadowPlugin],
      data:data.bar,
      options:{
        responsive:true,
        interaction:{mode:'index',intersect:false},
        plugins:{legend:{position:'top'},tooltip:{callbacks:{label:ctx=>ctx.dataset.label+': '+fmt(ctx.raw)}}},
        scales:{y:{beginAtZero:true,ticks:{callback:fmtC},grid:{color:'#f1f5f9'}},x:{grid:{display:false}}},
        onHover:(evt,els)=>{
          barChart._hoverIndex = els.length ? els[0].index : null;
        }
      }
    });
  }

  // Donut helper
  const BASE = '<?= BASE_URL ?>';
  let txModal;
  function openTxModal(catId, catName, type, color){
    if(!txModal) txModal = new bootstrap.Modal(document.getElementById('txModal'));
    document.getElementById('txModalTitle').innerHTML =
      `<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${color};margin-right:6px"></span>${catName}`;
    document.getElementById('txModalBody').innerHTML =
      `<div class="text-center py-4"><div class="spinner-border text-success"></div></div>`;
    txModal.show();
    fetch(`${BASE}/dashboard/transactions?category_id=${catId}&type=${type}`)
      .then(r=>r.json()).then(rows=>{
        if(!rows.length){ document.getElementById('txModalBody').innerHTML='<p class="text-muted text-center py-3">Không có giao dịch nào.</p>'; return; }
        const tColor = type==='expense'?'text-danger':'text-success';
        let html=`<table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th class="tx-th">Ngày</th><th class="tx-th">Ghi chú</th><th class="tx-th text-end">Số tiền</th></tr></thead><tbody>`;
        rows.forEach(r=>{ html+=`<tr><td class="tx-td text-muted small">${r.date}</td><td class="tx-td small">${r.note||'—'}</td><td class="tx-td fw-semibold ${tColor} text-end">${fmt(r.amount)}</td></tr>`; });
        html+=`</tbody></table>`;
        document.getElementById('txModalBody').innerHTML=html;
      }).catch(()=>{ document.getElementById('txModalBody').innerHTML='<p class="text-danger">Lỗi tải dữ liệu.</p>'; });
  }

  function buildDonut(canvasId, legendId, donutData, colorClass, type){
    const ctx = document.getElementById(canvasId);
    if(!ctx||!donutData?.labels?.length){
      const wrap = ctx?.closest('.chart-body');
      if(wrap) wrap.innerHTML=`<p class="text-muted small my-2">Chưa có dữ liệu tháng này.</p>`;
      return;
    }
    const dChart = new Chart(ctx,{type:'doughnut', data:donutData,
      options:{responsive:true,cutout:'62%',
        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': '+fmt(c.raw)}}},
        onClick:(e,els)=>{
          if(!els.length) return;
          const i=els[0].index;
          openTxModal(donutData.categoryIds?.[i]??0, donutData.labels[i], type, donutData.datasets[0].backgroundColor[i]);
        }
      }
    });
    const lg=document.getElementById(legendId);
    if(lg){
      const colors=donutData.datasets[0].backgroundColor;
      const counts=donutData.txCounts||[];
      const catIds=donutData.categoryIds||[];
      lg.innerHTML=`<table style="width:100%;border-collapse:collapse"><tbody>`+donutData.labels.map((l,i)=>`
        <tr style="cursor:pointer" onclick="(function(){var m=document.getElementById('txModal');if(!window._txM)window._txM=new bootstrap.Modal(m);})()">
        </tr>`).join('')+`</tbody></table>`;
      // Simpler approach: build rows directly
      lg.innerHTML='';
      const tbl=document.createElement('table');
      tbl.style.cssText='width:100%;border-collapse:collapse';
      donutData.labels.forEach((l,i)=>{
        const tr=document.createElement('tr');
        tr.style.cursor='pointer';
        tr.title='Nhấn để xem giao dịch';
        const nameColor = type==='income' ? '#16a34a' : '#dc2626';
        const tdName = document.createElement('td');
        tdName.style.cssText='padding:4px 6px;font-size:.82rem;color:#1e293b;transition:color .15s,font-weight .1s';
        tdName.textContent=l;
        tr.innerHTML=`<td style="padding:4px 6px;width:14px"><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:${colors[i]}"></span></td>`;
        tr.appendChild(tdName);
        const tdCount=document.createElement('td');
        tdCount.style.cssText='padding:4px 6px;font-size:.75rem;color:#64748b;white-space:nowrap';
        tdCount.textContent=(counts[i]||0)+' GD';
        tr.appendChild(tdCount);
        const tdAmt=document.createElement('td');
        tdAmt.style.cssText=`padding:4px 6px;font-weight:600;text-align:right;white-space:nowrap`;
        tdAmt.className=colorClass;
        tdAmt.textContent=fmt(donutData.datasets[0].data[i]);
        tr.appendChild(tdAmt);
        tr.addEventListener('mouseenter',()=>{
          tr.style.background='#f8fafc';
          tdName.style.color=nameColor;
          tdName.style.fontWeight='600';
        });
        tr.addEventListener('mouseleave',()=>{
          tr.style.background='';
          tdName.style.color='#1e293b';
          tdName.style.fontWeight='';
        });
        tr.onclick=()=>openTxModal(catIds[i]??0, l, type, colors[i]);
        tbl.appendChild(tr);
      });
      lg.appendChild(tbl);
    }
  }

  buildDonut('incomeDonut',  'incomeDonutLegend',  data.incomeDonut,  'text-success', 'income');
  buildDonut('expenseDonut', 'expenseDonutLegend', data.expenseDonut, 'text-danger',  'expense');
})();
</script>
<?php
$extraJs = ob_get_clean();
require BASE_PATH . '/app/Views/partials/footer.php';
?>
