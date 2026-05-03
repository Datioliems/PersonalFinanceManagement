<?php
// app/Views/report/index.php — TV5 Ngày 4
$pageTitle = $pageTitle ?? 'Báo cáo';
require BASE_PATH . '/app/Views/partials/layout.php';

$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear  = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear  = $month == 12 ? $year + 1 : $year;
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h4 fw-semibold mb-1">Báo cáo tài chính</h2>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/report?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"
               class="btn btn-outline-secondary btn-sm py-0">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="fw-medium">Tháng <?= $month ?>/<?= $year ?></span>
            <a href="<?= BASE_URL ?>/report?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"
               class="btn btn-outline-secondary btn-sm py-0">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    <a href="<?= BASE_URL ?>/report/export?month=<?= $month ?>&year=<?= $year ?>"
       class="btn btn-outline-success">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>
        Xuất CSV tháng <?= $month ?>/<?= $year ?>
    </a>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Thu nhập', $summary['income'],  'success', 'arrow-down-circle', '+'],
        ['Chi tiêu', $summary['expense'], 'danger',  'arrow-up-circle',   '-'],
        ['Số dư',    $summary['balance'], $summary['balance'] >= 0 ? 'primary' : 'danger',
                     'wallet2', $summary['balance'] >= 0 ? '+' : ''],
    ];
    foreach ($cards as [$label, $val, $color, $icon, $prefix]): ?>
    <div class="col-12 col-sm-4">
        <div class="card">
            <div class="card-body text-center py-3">
                <div class="small text-muted mb-1">
                    <i class="bi bi-<?= $icon ?> me-1 text-<?= $color ?>"></i><?= $label ?>
                </div>
                <div class="h4 fw-semibold text-<?= $color ?> mb-0">
                    <?= $prefix . number_format($val, 0, ',', '.') ?>đ
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Biểu đồ -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-medium">Thu/Chi theo tuần</div>
            <div class="card-body">
                <canvas id="barChart" style="max-height:280px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-medium">Chi theo danh mục</div>
            <div class="card-body d-flex flex-column align-items-center">
                <canvas id="donutChart" style="max-height:240px;max-width:240px"></canvas>
                <div id="donutLegend" class="mt-3 w-100"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const data = <?= $chartJson ?>;

    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: data.bar,
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v =>
                                new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v)+'đ'
                        }
                    }
                }
            }
        });
    }

    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        const dd = data.donut;
        if (!dd.labels || !dd.labels.length) {
            donutCtx.closest('.card-body').innerHTML =
                '<p class="text-muted text-center my-4">Chưa có dữ liệu chi tiêu.</p>';
            return;
        }
        new Chart(donutCtx, {
            type: 'doughnut',
            data: dd,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label+': '+
                                new Intl.NumberFormat('vi-VN').format(ctx.raw)+'đ'
                        }
                    }
                }
            }
        });

        const legend = document.getElementById('donutLegend');
        legend.innerHTML = dd.labels.map((lbl, i) => {
            const c   = dd.datasets[0].backgroundColor[i];
            const val = new Intl.NumberFormat('vi-VN').format(dd.datasets[0].data[i]);
            return `<div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:12px;height:12px;border-radius:50%;background:${c};
                      flex-shrink:0;display:inline-block"></span>
                <span class="small">${lbl}: <strong>${val}đ</strong></span>
            </div>`;
        }).join('');
    }
})();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
